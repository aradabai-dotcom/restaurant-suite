import { describe, expect, it } from 'vitest';
import statuses from '../../docs/contracts/statuses.json';
import events from '../../docs/contracts/events.json';

describe('phase 0.0 contracts', () => {
  it('keeps stable status identifiers', () => {
    expect(statuses.statuses.map((status) => status.id)).toEqual([
      'crs_pending_confirmation',
      'crs_confirmed',
      'crs_preparing',
      'crs_ready',
      'crs_out_for_delivery',
      'crs_completed',
      'crs_rejected',
    ]);
  });

  it('keeps unique event names', () => {
    const names = events.events.map((event) => event.name);
    expect(new Set(names).size).toBe(names.length);
    expect(names).toContain('crs:order:created');
  });
});
