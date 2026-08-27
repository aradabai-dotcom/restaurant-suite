(() => {
  const config = window.CRS_ORDER_SIMULATION_CONFIG || {};
  const labels = {
    loading: 'Simulation en cours…',
    success: 'Simulation acceptée : aucune commande n’a été créée.',
    error: 'La simulation n’a pas pu être validée. Vérifiez les champs et le panier.',
    ...(config.labels || {}),
  };

  const createIdempotencyKey = () => {
    if (window.crypto?.randomUUID) return `crs-${window.crypto.randomUUID()}`;
    return `crs-${Date.now()}-${Math.random().toString(36).slice(2)}`;
  };

  const requestSimulation = async (payload) => {
    if (!config.restUrl) throw new Error(labels.error);
    const response = await fetch(config.restUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': config.nonce || '' },
      body: JSON.stringify(payload),
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok || result.decision === 'rejected' || result.would_create_order !== false) throw new Error(labels.error);
    return result;
  };

  const showResult = (root, result, message) => {
    const output = root.querySelector('[data-crs-order-simulation-result]');
    if (!output) return;
    output.hidden = false;
    output.textContent = message;
    if (result?.snapshot?.total) output.textContent += ` Total simulé : ${result.snapshot.total}.`;
  };

  const bind = (root) => {
    const form = root.querySelector('[data-crs-order-simulation-form]');
    const submit = root.querySelector('[data-crs-order-simulation-submit]');
    if (!form || !submit || root.dataset.crsOrderSimulationBound) return;
    root.dataset.crsOrderSimulationBound = 'true';

    form.addEventListener('submit', (event) => {
      event.preventDefault();
      if (submit.disabled) return;
      submit.disabled = true;
      form.setAttribute('aria-busy', 'true');
      submit.textContent = labels.loading;
      const data = new FormData(form);
      const payload = {
        idempotency_key: createIdempotencyKey(),
        customer_name: String(data.get('customer_name') || ''),
        phone: String(data.get('phone') || ''),
        fulfillment_method: String(data.get('fulfillment_method') || ''),
        delivery_zone: String(data.get('delivery_zone') || ''),
        address: String(data.get('address') || ''),
        note: String(data.get('note') || ''),
      };
      void requestSimulation(payload)
        .then((result) => {
          showResult(root, result, labels.success);
          document.dispatchEvent(new CustomEvent('crs:order:simulated', {
            detail: { attemptId: result.attempt_id, snapshot: result.snapshot },
          }));
        })
        .catch(() => showResult(root, null, labels.error))
        .finally(() => {
          submit.disabled = false;
          form.removeAttribute('aria-busy');
          submit.textContent = 'Lancer la simulation';
        });
    });
  };

  const init = () => document.querySelectorAll('[data-crs-order-simulation]').forEach(bind);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
