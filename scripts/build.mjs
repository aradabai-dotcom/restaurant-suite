import { copyFile, mkdir } from 'node:fs/promises';

await mkdir('plugin/restaurant-suite-core/assets/build', { recursive: true });
await mkdir('theme/restaurant-base-theme/assets/build', { recursive: true });
await copyFile(
  'plugin/restaurant-suite-core/assets/src/quick-view.js',
  'plugin/restaurant-suite-core/assets/build/quick-view.js',
);
console.log('Restaurant Suite assets built: Quick View bundle copied.');
