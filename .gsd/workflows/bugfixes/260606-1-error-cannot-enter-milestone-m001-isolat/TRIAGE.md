# Triage: Cannot Enter Milestone M001 - Isolation Degraded

## Root Cause

The GSD engine uses `git.isolation: worktree` mode (configured in `.gsd/PREFERENCES.md`), which creates
a separate git branch per milestone for isolation. A previous session created branch `milestone/M001`
during the research phase. That branch was based on an earlier state of `main` (before `composer.lock`,
`package-lock.json`, and frontend asset changes were committed to main).

When the system later attempted to re-enter M001, it tried to `git checkout milestone/M001`, but:
- The `milestone/M001` branch's working tree diverges significantly from `main` (30 files differ)
- Several `.gsd/` files on main would be overwritten by the checkout
- Git refused the checkout with "untracked working tree files would be overwritten"

This left the isolation state as **degraded**, and subsequent attempts to enter M001 were blocked
by the engine's safety check.

## Reproduction Steps

1. Have a GSD project with `git.isolation: worktree` in PREFERENCES.md
2. Start a milestone, let it create its branch and partial planning
3. Make changes on main that diverge from the milestone branch (e.g., commit updates to
   `composer.lock`, `package-lock.json`, or `.gsd/*` files)
4. Stop and restart the auto-mode session
5. GSD will try to recover the stranded work and fail with checkout conflict
6. The engine will emit: "Cannot enter milestone M001: isolation is degraded from a prior worktree failure"

## Affected Files/Functions

- `.gsd/runtime/units/plan-milestone-M001.json` — unit stuck in phase `"dispatched"` (never completed)
- `.gsd/runtime/units/research-milestone-M001.json` — research unit also stuck in `"dispatched"`
- `milestone/M001` branch — divergent from main, caused checkout conflict
- `.gsd/PREFERENCES.md` — `git.isolation: worktree` mode enabled

## Blast Radius

- Only M001 is affected (other milestones were queued but never started execution)
- No code or data loss — all M001 planning artifacts exist correctly in `.gsd/milestones/M001/` on main
- No other branches or worktrees exist

## Proposed Fix

1. **Delete stale `milestone/M001` branch** — already done; its content is preserved in main's `.gsd/` tree
2. **Clear stale runtime unit state** — remove the stuck `plan-milestone-M001.json` and
   `research-milestone-M001.json` from `.gsd/runtime/units/` so the engine can start fresh
3. No PREFERENCES.md changes needed — `worktree` isolation mode is fine; the issue was a stale branch,
   not a broken mode

After these changes, the engine should be able to re-enter M001 and proceed with execution normally.
