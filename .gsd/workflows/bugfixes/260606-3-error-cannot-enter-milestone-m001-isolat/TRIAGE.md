# Triage: Cannot enter milestone M001 — isolation degraded

## Root Cause

The runtime tracking file `.gsd/runtime/units/execute-task-M001-S01-T01.json` still shows `phase: "dispatched"` even though T01 was successfully completed and recorded in gsd.db as `status: "complete"`. This stale runtime tracking state causes GSD's isolation subsystem to detect an incomplete dispatch, interpreting it as a "degraded isolation" from a prior failure.

Key evidence:
- `state-manifest.json` shows T01 as `"status": "complete"` with a valid `completed_at` timestamp
- Runtime unit file `execute-task-M001-S01-T01.json` shows `"phase": "dispatched"`, `"progressCount": 0` — never transitioned to a terminal phase
- No `.lock` files or stale processes exist
- `git worktree prune` runs clean (isolation mode is `branch`, not worktree)

## Reproduction Steps

1. Complete T01 via `gsd_task_complete` (DB records success)
2. GSD runtime tracking does not update the unit tracking file to a terminal phase
3. On next session start, GSD finds the stale `phase: "dispatched"` runtime unit
4. GSD interprets this as isolation degradation and blocks milestone entry

## Affected Files

- `.gsd/runtime/units/execute-task-M001-S01-T01.json` — stale, shows `phase: "dispatched"`
- No code files affected — this is a GSD runtime metadata issue

## Proposed Fix Approach

Two options:

**Option A (Recommended):** Clean the stale runtime unit file — remove or reset the stale dispatch tracking record so GSD's isolation check passes.

**Option B:** Update the runtime unit file's phase from `"dispatched"` to a terminal value matching the DB state.

Option A is simpler and cleaner — the DB already has the authoritative completion record.
