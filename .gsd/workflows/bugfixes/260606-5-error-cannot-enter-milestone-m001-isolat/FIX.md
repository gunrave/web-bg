# Fix Plan

## Approach
Change `git.isolation` from `branch` to `none` in `.gsd/PREFERENCES.md`. This tells GSD to skip isolation entirely for milestone entry, bypassing the persistent "isolation degraded" flag in GSD's DB.

## Steps
1. Edit `.gsd/PREFERENCES.md`: change `isolation: branch` to `isolation: none`
2. Verify the change

## Why This Works
The GSD DB has persistent isolation state marking M001 as worktree-isolated. This state was set when plan-slice ran and created a git worktree. Even after cleaning all git-level artifacts (worktree, branch), the DB still expects the worktree to exist. Changing isolation to `none` bypasses the isolation check entirely.

## Why Not Worktree
Using worktree isolation again would also fail since the worktree was already created and removed. `none` is the cleanest reset.

## Verification
- GSD should be able to enter M001 without isolation errors
- Tests should still pass (isolation mode doesn't affect application code)
