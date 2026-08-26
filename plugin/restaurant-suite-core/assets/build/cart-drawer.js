(() => {
  const config = window.CRS_CART_CONFIG || {};
  const labels = {
    cart: 'Panier',
    close: 'Fermer le panier',
    continue: 'Continuer mes achats',
    empty: 'Votre panier est vide.',
    viewCart: 'Voir mon panier',
    checkout: 'Valider la commande',
    loading: 'Mise à jour du panier…',
    error: 'Le panier n’a pas pu être mis à jour.',
    ...(config.labels || {}),
  };
  const queues = new Map();
  let lastTrigger = null;
  let previousOverflow = '';
  let currentSnapshot = { count: 0, lines: '', subtotal: '', notices: [], errors: [] };

  const dispatch = (name, detail = {}) => {
    document.dispatchEvent(new CustomEvent(name, { detail }));
  };

  const escapeHtml = (value) =>
    String(value)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');

  const getFocusable = (root) =>
    Array.from(
      root.querySelectorAll(
        'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])',
      ),
    );

  const createDrawer = () => {
    let root = document.querySelector('[data-crs-cart-drawer]');
    if (root) return root;

    root = document.createElement('div');
    root.className = 'crs-cart-drawer';
    root.dataset.crsCartDrawer = '';
    root.hidden = true;
    root.setAttribute('aria-hidden', 'true');
    root.innerHTML = `
      <div class="crs-cart-drawer__backdrop" data-crs-cart-close></div>
      <aside class="crs-cart-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="crs-cart-drawer-title" tabindex="-1">
        <header class="crs-cart-drawer__header">
          <h2 id="crs-cart-drawer-title">${escapeHtml(labels.cart)}</h2>
          <button class="crs-cart-drawer__close" type="button" data-crs-cart-close aria-label="${escapeHtml(labels.close)}">×</button>
        </header>
        <p class="crs-cart-drawer__status" role="status" aria-live="polite"></p>
        <div class="crs-cart-drawer__notices" data-crs-cart-notices></div>
        <div class="crs-cart-drawer__lines" data-crs-cart-lines></div>
        <footer class="crs-cart-drawer__footer">
          <p class="crs-cart-drawer__subtotal"><span>${escapeHtml(labels.subtotal || 'Sous-total')}</span> <strong data-crs-cart-subtotal></strong></p>
          <div class="crs-cart-drawer__actions">
            <button type="button" data-crs-cart-close>${escapeHtml(labels.continue)}</button>
            <a data-crs-cart-link href="${escapeHtml(config.cartUrl || '#')}">${escapeHtml(labels.viewCart)}</a>
            <a data-crs-cart-checkout href="${escapeHtml(config.checkoutUrl || '#')}">${escapeHtml(labels.checkout)}</a>
          </div>
        </footer>
      </aside>`;
    document.body.appendChild(root);

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'crs-cart-drawer__trigger';
    trigger.dataset.crsCartOpen = '';
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.innerHTML = `${escapeHtml(labels.cart)} <span data-crs-cart-count>0</span>`;
    document.body.appendChild(trigger);
    return root;
  };

  const setStatus = (root, message) => {
    const status = root.querySelector('.crs-cart-drawer__status');
    if (status) status.textContent = message || '';
  };

  const setBusy = (root, busy) => {
    root.classList.toggle('is-loading', busy);
    root.querySelectorAll('button[data-crs-cart-increase],button[data-crs-cart-decrease],button[data-crs-cart-remove]').forEach((button) => {
      button.disabled = busy;
    });
  };

  const updateView = (snapshot) => {
    currentSnapshot = { ...currentSnapshot, ...snapshot };
    document.querySelectorAll('[data-crs-cart-count]').forEach((element) => {
      element.textContent = String(currentSnapshot.count || 0);
    });
    const root = document.querySelector('[data-crs-cart-drawer]');
    if (!root) return;
    const lines = root.querySelector('[data-crs-cart-lines]');
    const subtotal = root.querySelector('[data-crs-cart-subtotal]');
    const notices = root.querySelector('[data-crs-cart-notices]');
    if (lines) lines.innerHTML = currentSnapshot.lines || `<p class="crs-cart__empty" role="status">${escapeHtml(labels.empty)}</p>`;
    if (subtotal) subtotal.innerHTML = currentSnapshot.subtotal || '';
    if (notices) notices.innerHTML = (currentSnapshot.notices || []).map((notice) => `<p role="status">${notice}</p>`).join('');
    dispatch('crs:cart:refresh', { snapshot: currentSnapshot });
  };

  const request = async (action, data = {}) => {
    if (!config.restUrl) throw new Error('Cart endpoint unavailable');
    const response = await fetch(`${config.restUrl}cart/${action}`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce || '' },
      body: JSON.stringify({ action, ...data }),
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok || payload.code) throw new Error(payload.message || labels.error);
    return payload;
  };

  const runSerialized = (key, task) => {
    const previous = queues.get(key) || Promise.resolve();
    const next = previous.catch(() => undefined).then(task).finally(() => {
      if (queues.get(key) === next) queues.delete(key);
    });
    queues.set(key, next);
    return next;
  };

  const apply = (action, data, key = 'cart') =>
    runSerialized(key, async () => {
      const root = createDrawer();
      setBusy(root, true);
      setStatus(root, labels.loading);
      dispatch(`crs:cart:${action}`, { action, ...data });
      try {
        const snapshot = await request(action, data);
        updateView(snapshot);
        setStatus(root, '');
        return snapshot;
      } catch (error) {
        setStatus(root, error.message || labels.error);
        dispatch('crs:cart:error', { action, message: error.message || labels.error });
        throw error;
      } finally {
        setBusy(root, false);
      }
    });

  const open = (trigger) => {
    const root = createDrawer();
    lastTrigger = trigger || document.activeElement;
    root.hidden = false;
    root.setAttribute('aria-hidden', 'false');
    previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    document.body.classList.add('crs-cart-drawer-open');
    root.querySelector('.crs-cart-drawer__close')?.focus();
  };

  const close = () => {
    const root = document.querySelector('[data-crs-cart-drawer]');
    if (!root || root.hidden) return;
    root.hidden = true;
    root.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('crs-cart-drawer-open');
    document.body.style.overflow = previousOverflow;
    if (lastTrigger && typeof lastTrigger.focus === 'function') lastTrigger.focus();
  };

  const trapFocus = (event, root) => {
    if (event.key !== 'Tab') return;
    const focusable = getFocusable(root);
    if (!focusable.length) return;
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  };

  const formDataToCartItem = (form) => {
    const data = new FormData(form);
    const variation = {};
    let productId = form.closest('[data-product-id]')?.dataset.productId || data.get('product_id') || data.get('add-to-cart');
    let variationId = data.get('variation_id') || 0;
    let quantity = data.get('quantity') || 1;
    data.forEach((value, key) => {
      if (key.startsWith('attribute_')) variation[key] = String(value);
      if (key === 'product_id' || key === 'add-to-cart') productId = value;
      if (key === 'variation_id') variationId = value;
      if (key === 'quantity') quantity = value;
    });
    return { product_id: Number.parseInt(String(productId), 10), variation_id: Number.parseInt(String(variationId), 10) || 0, quantity: Number.parseInt(String(quantity), 10) || 1, variation };
  };

  const store = {
    getState: () => ({ ...currentSnapshot }),
    add: (item) => apply('add', item, `add:${item.product_id}:${item.variation_id || 0}`),
    update: (cartItemKey, quantity) => apply('update', { cart_item_key: cartItemKey, quantity }, `line:${cartItemKey}`),
    remove: (cartItemKey) => apply('remove', { cart_item_key: cartItemKey }, `line:${cartItemKey}`),
    refresh: () => apply('refresh', {}, 'refresh'),
    open,
    close,
  };
  window.CRS_CART_STORE = store;

  document.addEventListener('click', (event) => {
    const openTrigger = event.target.closest?.('[data-crs-cart-open]');
    if (openTrigger) {
      event.preventDefault();
      open(openTrigger);
      return;
    }
    const closeTrigger = event.target.closest?.('[data-crs-cart-close]');
    if (closeTrigger) {
      event.preventDefault();
      close();
      return;
    }
    const addTrigger = event.target.closest?.('[data-crs-cart-add]');
    if (addTrigger) {
      event.preventDefault();
      void store.add({ product_id: Number.parseInt(addTrigger.dataset.productId || '', 10), quantity: Number.parseInt(addTrigger.dataset.quantity || '1', 10) || 1 }).then(() => open(addTrigger)).catch(() => undefined);
      return;
    }
    const line = event.target.closest?.('[data-crs-cart-line]');
    if (!line) return;
    const key = line.dataset.cartItemKey || '';
    const currentQuantity = Number.parseInt(line.querySelector('[data-crs-cart-quantity]')?.textContent || '0', 10);
    if (event.target.closest('[data-crs-cart-increase]')) void store.update(key, currentQuantity + 1).catch(() => undefined);
    if (event.target.closest('[data-crs-cart-decrease]')) void store.update(key, Math.max(0, currentQuantity - 1)).catch(() => undefined);
    if (event.target.closest('[data-crs-cart-remove]')) void store.remove(key).catch(() => undefined);
  });

  document.addEventListener('submit', (event) => {
    if (!event.target.matches?.('[data-crs-quickview] form, .crs-quickview__fragment form')) return;
    event.preventDefault();
    const item = formDataToCartItem(event.target);
    void store.add(item).then(() => {
      close();
      open(document.querySelector('[data-crs-quickview-trigger][data-product-id="' + item.product_id + '"]'));
    }).catch(() => undefined);
  });

  document.addEventListener('keydown', (event) => {
    const root = document.querySelector('[data-crs-cart-drawer]');
    if (!root || root.hidden) return;
    if (event.key === 'Escape') close();
    trapFocus(event, root);
  });

  const init = () => {
    createDrawer();
    void store.refresh().catch(() => undefined);
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
