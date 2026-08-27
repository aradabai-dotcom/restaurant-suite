(() => {
  const config = window.CRS_QUICK_VIEW_CONFIG || {};
  let lastTrigger = null;
  let previousOverflow = '';

  const labels = {
    close: 'Fermer l’aperçu rapide',
    loading: 'Chargement de l’aperçu…',
    error: 'L’aperçu rapide est momentanément indisponible.',
    ...(config.labels || {}),
  };

  const getFocusable = (root) =>
    Array.from(
      root.querySelectorAll(
        'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])',
      ),
    );

  const createDialog = () => {
    let root = document.querySelector('[data-crs-quickview]');
    if (root) return root;

    root = document.createElement('div');
    root.className = 'crs-quickview';
    root.dataset.crsQuickview = '';
    root.hidden = true;
    root.setAttribute('aria-hidden', 'true');
    root.innerHTML = `
      <div class="crs-quickview__backdrop" data-crs-quickview-close></div>
      <section class="crs-quickview__dialog" role="dialog" aria-modal="true" aria-labelledby="crs-quickview-title" tabindex="-1">
        <button class="crs-quickview__close" type="button" data-crs-quickview-close aria-label="${labels.close}">×</button>
        <div class="crs-quickview__body" data-crs-quickview-body>
          <p class="crs-quickview__status" role="status" aria-live="polite"></p>
        </div>
      </section>`;
    document.body.appendChild(root);
    return root;
  };

  const setStatus = (root, message) => {
    const status = root.querySelector('.crs-quickview__status');
    if (status) status.textContent = message;
  };

  const close = () => {
    const root = document.querySelector('[data-crs-quickview]');
    if (!root || root.hidden) return;

    root.hidden = true;
    root.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('crs-quickview-open');
    document.body.style.overflow = previousOverflow;
    document.dispatchEvent(new CustomEvent('crs:quickview:close'));
    if (lastTrigger && typeof lastTrigger.focus === 'function') lastTrigger.focus();
  };

  window.CRS_QUICK_VIEW_CLOSE = close;

  const trapFocus = (event, root) => {
    if (event.key !== 'Tab') return;
    const focusable = getFocusable(root);
    if (!focusable.length) {
      event.preventDefault();
      root.querySelector('.crs-quickview__dialog')?.focus();
      return;
    }
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

  const open = async (trigger) => {
    const productId = Number.parseInt(trigger.dataset.productId || '', 10);
    const fallbackUrl = trigger.dataset.productUrl || trigger.closest('.crs-menu__item')?.querySelector('.crs-menu__media')?.href || '#';
    if (!Number.isInteger(productId) || productId < 1 || !config.restUrl) {
      window.location.href = fallbackUrl;
      return;
    }

    lastTrigger = trigger;
    const root = createDialog();
    const dialog = root.querySelector('.crs-quickview__dialog');
    const body = root.querySelector('[data-crs-quickview-body]');
    if (!dialog || !body) return;

    root.hidden = false;
    root.setAttribute('aria-hidden', 'false');
    previousOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    document.body.classList.add('crs-quickview-open');
    body.innerHTML = '';
    setStatus(root, labels.loading);
    dialog.focus();
    document.dispatchEvent(new CustomEvent('crs:quickview:open', { detail: { productId } }));

    try {
      const response = await fetch(`${config.restUrl}${productId}`, {
        credentials: 'same-origin',
        headers: { 'X-WP-Nonce': config.nonce || '' },
      });
      if (!response.ok) throw new Error(`HTTP ${response.status}`);
      const payload = await response.json();
      if (typeof payload.html !== 'string') throw new Error('Invalid fragment');
      const template = document.createElement('template');
      template.innerHTML = payload.html;
      body.replaceChildren(template.content.cloneNode(true));
      const title = body.querySelector('.crs-quickview__product-title');
      if (title) title.id = 'crs-quickview-title';
      const focusable = getFocusable(root);
      (focusable[0] || dialog).focus();
    } catch {
      body.innerHTML = `<p class="crs-quickview__error" role="alert">${labels.error}</p><a class="crs-quickview__permalink" href="${fallbackUrl}">Voir la fiche produit</a>`;
      setStatus(root, labels.error);
    }
  };

  document.addEventListener('crs:quickview:close-request', close);

  document.addEventListener('click', (event) => {
    const trigger = event.target.closest?.('[data-crs-quickview-trigger]');
    if (trigger) {
      event.preventDefault();
      void open(trigger);
      return;
    }
    if (event.target.closest?.('[data-crs-quickview-close]')) close();
  });

  document.addEventListener('keydown', (event) => {
    const root = document.querySelector('[data-crs-quickview]');
    if (!root || root.hidden) return;
    if (event.key === 'Escape') close();
    trapFocus(event, root);
  });

  document.addEventListener('DOMContentLoaded', createDialog, { once: true });
})();
