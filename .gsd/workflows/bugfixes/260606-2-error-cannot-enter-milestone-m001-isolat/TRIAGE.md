# Triage: Cannot Enter Milestone M001 - Isolation Degraded (Round 2)

## Root Cause

The `milestone/M001` branch has 2 commits ahead of main that add `.gsd/gsd.db*` (SQLite binary database files) which also exist as **untracked files** in the current working tree. When GSD tries `git checkout milestone/M001` (via its `git.isolation: worktree` mode), Git refuses because the untracked `.gsd/gsd.db*` files in the working tree would be overwritten by the checkout.

The previous fix deleted the stale branch, but the GSD engine's auto-recovery mechanism recreated it during the next session attempt. The root cause is that `.gsd/gsd.db*` files are:
- Machine-generated binary SQLite files that change every session
- Not in `.gitignore` (the doctor warned about this)
- Causing git checkout conflicts between main and milestone branches

## Reproduction Steps

1. Have a GSD project with `git.isolation: worktree` and `.gsd/gsd.db*` tracked or present untracked
2. GSD creates a milestone branch that includes `.gsd/gsd.db*` in its state
3. Later, the working tree also has `.gsd/gsd.db*` (untracked)
4. GSD tries `git checkout milestone/M***` — Git refuses due to untracked files overlap
5. Isolation marked as "degraded", M001 cannot be entered

## Affected Files

- `.gsd/gsd.db`, `.gsd/gsd.db-shm`, `.gsd/gsd.db-wal` — untracked binary files causing checkout conflicts
- `.gitignore` — missing GSD runtime patterns (gsd.db*, runtime/, activity/, etc.)
- `milestone/M001` branch — 2 commits ahead of main, diverged on gsd.db files

## Proposed Fix

1. **Add `.gsd/gsd.db*` + other GSD runtime patterns to `.gitignore`** — prevents future checkout conflicts
2. **Delete the stale `milestone/M001` branch** — its content is already preserved in main
3. The worktree isolation mode itself is fine once binary DB files don't cause checkout conflicts

This addresses the root cause (binary DB file conflicts) rather than just the symptom (stale branch).
