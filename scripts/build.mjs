import { mkdir } from 'node:fs/promises';
await mkdir('plugin/restaurant-suite-core/assets/build', { recursive: true });
await mkdir('theme/restaurant-base-theme/assets/build', { recursive: true });
console.log('Phase 0.0 build: contract scaffold ready; public modules are not built yet.');
