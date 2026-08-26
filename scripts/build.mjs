import { copyFile, mkdir } from 'node:fs/promises';

await mkdir('plugin/restaurant-suite-core/assets/build', { recursive: true });
await mkdir('theme/restaurant-base-theme/assets/build', { recursive: true });
await copyFile(
  'plugin/restaurant-suite-core/assets/src/quick-view.js',
  'plugin/restaurant-suite-core/assets/build/quick-view.js',
);
await copyFile(
  'plugin/restaurant-suite-core/assets/src/cart-drawer.js',
  'plugin/restaurant-suite-core/assets/build/cart-drawer.js',
);
console.log('Restaurant Suite assets built: Quick View and Cart Drawer bundles copied.');
