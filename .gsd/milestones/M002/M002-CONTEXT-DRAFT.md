# M002: Security Hardening (DRAFT)

**Gathered:** 2026-06-06
**Status:** Draft — discuss before execution

## Project Description

Apply general security hardening to the payroll/billing admin panel after the M001 dependency upgrade. Focus on the obvious surface area: authentication, import validation, environment hygiene, and Filament/Laravel security defaults — not a deep penetration test.

## Why This Milestone

The app handles financial/salary data behind a login. Basic security posture — rate limiting, input validation, debug hygiene — hasn't been examined. M001 gets the foundation current; M002 locks it down before M003 and M004 touch the data layer and import pipeline.

## Draft Scope

### In Scope

- Add Laravel throttle middleware to Filament `/admin/login` route (5 attempts/min default)
- Verify Filament's `acceptedFileTypes` on import file uploads rejects non-xlsx/xls files
- Scan for and remove any `dd()`, `dump()`, `var_dump`, `print_r` debug calls in source
- Set `APP_DEBUG=false` in `.env` (or verify it's already false)
- Review `.env.example` for any exposed defaults or secrets
- Enable CSRF protection (verify it's not disabled anywhere)
- Review Filament auth middleware chain in `AdminPanelProvider.php`
- Ensure Filament's login is the only auth entry point (no unauthenticated resource access)

### Out of Scope / Non-Goals

- Adding 2FA or MFA
- IP whitelisting/blocking
- Full penetration test
- Audit logging
- Password policy enforcement
- HTTPS enforcement (local Herd, not applicable)

## Draft Acceptance Criteria

- Login returns 429 after 5 failed attempts within 1 minute
- Uploading a non-xlsx file to import returns a user-visible rejection
- `rg 'dd\(|dump\(|var_dump\(|print_r\(' app/` returns zero results
- `APP_DEBUG` is `false` in `.env`
- `.env.example` contains no real secrets or overly permissive defaults
- No Filament resource page is accessible without authentication

## Open Questions

- Should the rate limit be the default 5/min or a different value?
- Any specific import file size limits needed beyond Filament defaults?
- Should `.env.example` be re-generated from current `.env` (sanitized)?
