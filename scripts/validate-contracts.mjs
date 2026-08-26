import { readFile } from 'node:fs/promises';
for (const name of ['data-contract.json', 'statuses.json', 'events.json', 'permissions.json']) {
  JSON.parse(await readFile(`docs/contracts/${name}`, 'utf8'));
}
console.log('Contracts valid.');
