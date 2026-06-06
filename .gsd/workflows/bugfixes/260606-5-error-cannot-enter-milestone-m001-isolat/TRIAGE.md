# Triage: Cannot enter milestone M001 — isolation degraded (Round 5)

## Root Cause

**GSD persists isolation state in its DB.** The initial plan-slice for M001 created a git worktree at `.gsd/worktrees/M001` for isolation. Even though:

1. A `git worktree prune` was run
2. `/gsd doctor fix` removed the merged worktree "M001" and deleted branch `milestone/M001`
3. All git-level artifacts are clean (no stale branches, worktrees, or lock files)

...GSD's internal DB still records that M001 uses worktree isolation and the expected worktree is missing. This is the persistent "isolation degraded" flag.

**Evidence from notifications.jsonl:**
- `03:28:44` — "Unit root is not the expected worktree root for M001. (repair skipped: revalidation failed)"
- `03:29:16` — "Worktree was expected at .gsd/worktrees/M001 but is missing. Continuing in project-root mode."
- `03:56:19` — Error still fires even after doctor cleaned all git artifacts at `03:56:03`
- Doctor at `03:56:24` reports "Clean" but the error persists — because it can only clean git-level state, not GSD DB isolation state

## Affected Artifacts

- **GSD DB** (gsd.db) — holds isolation state for M001 marking it as worktree-isolated
- **PREFERENCES.md** — currently has `git.isolation: branch` (but GSD originally used worktree)

## Proposed Fix

Two approaches:

**Option A (Recommended):** Change `git.isolation` from `branch` to `none` in `.gsd/PREFERENCES.md`. This tells GSD to skip isolation entirely, bypassing the degraded-state check. Safer, avoids DB surgery.

**Option B:** Milestone-level reset via DB — not directly available through GSD tooling. Could try recreating or reopening the milestone.
