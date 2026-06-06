# Ship: Error cannot enter milestone M001 — isolation degraded

## Summary
Cleaned a stale GSD runtime tracking file that was stuck on `phase: "dispatched"` for an already-completed task (T01). The stale state caused GSD's isolation subsystem to detect a "degraded isolation", blocking milestone M001 entry.

## Root Cause
The runtime unit tracking file `execute-task-M001-S01-T01.json` showed `phase: "dispatched"` even though T01 was successfully completed and recorded in gsd.db as `status: "complete"` with a valid `completed_at` timestamp. The runtime tracking subsystem did not transition to a terminal phase after `gsd_task_complete` wrote the DB record.

## Fix
Removed the stale runtime tracking file `.gsd/runtime/units/execute-task-M001-S01-T01.json`. The GSD DB already contained the authoritative completion record — the runtime file was redundant and its stale state was the sole cause of the isolation degradation error.

## Verification
- Stale runtime file removed; remaining runtime file (`plan-slice-M001-S01.json`) shows terminal `finalized` phase
- Milestone M001 status: `active`, S01: 5 tasks (1 complete, 4 pending) — all consistent
- `php artisan test`: 2/2 pass, no regressions
- `composer validate`: passes
