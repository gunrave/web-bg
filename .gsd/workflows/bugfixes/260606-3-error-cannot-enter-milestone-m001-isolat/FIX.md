# Fix Plan

## Approach
Remove/clean the stale runtime unit tracking file that shows `phase: "dispatched"` for an already-completed task (T01). The GSD DB already has the authoritative completion record.

## Steps
1. Remove the stale runtime unit file: `.gsd/runtime/units/execute-task-M001-S01-T01.json`
2. Verify M001 can be entered by checking the GSD state is consistent
3. Commit the cleanup

## Verification
- Runtime unit file no longer shows stale dispatched state
- GSD milestone entry should not be blocked by isolation check
