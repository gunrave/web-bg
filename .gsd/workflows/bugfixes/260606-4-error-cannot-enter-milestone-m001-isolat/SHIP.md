# Ship: Cannot enter milestone M001 — isolation degraded

## Summary
Resolved a GSD isolation degradation error preventing milestone M001 entry. Three compounding causes were identified and fixed across two bugfix rounds.

## Root Cause

Three issues compounded to produce the "isolation degraded" error:

1. **Stale runtime tracking file** — `execute-task-M001-S01-T01.json` was stuck at `phase: "dispatched"` even though T01 completed successfully. GSD's isolation check saw an incomplete dispatch.
2. **Merged git worktree "M001"** — The initial plan-slice execution created a git worktree at `M001` with branch `milestone/M001` for isolation. This worktree was merged into main but never cleaned up.
3. **Stale isolation branch** — The `milestone/M001` branch remained after the worktree was merged.

## Fix Approach (2 rounds)

**Round 1:** Removed stale runtime unit file (symptomatic fix).
**Round 2:** `/gsd doctor fix` cleaned the merged worktree "M001" and deleted the stale `milestone/M001` branch. Also documented full root cause, cleaned state, and switched to `main` branch.

## Final State
- **Branch:** `main` (merge commit at 009bf2f)
- **Milestone M001:** active, S01 pending, T01 complete, T02-T05 pending
- **Tests:** 2/2 pass
- **composer validate:** pass
- **Stale worktrees/branches:** none
- **Runtime tracking:** 1 file (plan-slice, terminal finalized phase)
