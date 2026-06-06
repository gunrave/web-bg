# M001: Dependency Modernization

**Vision:** All dependencies — PHP, Composer, and npm — are on their latest stable versions. The Filament admin panel renders and functions identically to before the upgrade: all 5 resource tables work, Excel imports complete, login/auth still functions, and both existing Pest tests pass. `composer outdated` returns empty; `vite build` succeeds.

## Success Criteria

- `composer outdated` returns empty — zero packages behind latest compatible
- `php artisan filament:upgrade` completes without errors or remaining changes
- `vite build` exits with code 0
- `php artisan test` passes both existing Pest tests
- Manual smoke test: login to /admin, browse all 5 resources, create Pegawai, import Gaji — all relationship columns render correctly

## Slices

- [ ] **S01: Full-Stack Dependency Upgrade** `risk:High — touches every layer at once (PHP, Composer, npm, Tailwind CSS, Filament API, Vite). A single-point failure in any subsystem blocks the whole milestone. Mitigated by sequenced task order, git stash rollback capability, and verification at each step.` `depends:[]`
  > After this: `composer outdated` shows zero packages at latest; `vite build` exits 0; visit /admin, log in, browse all 5 Filament resources, create a Pegawai, import a Gaji Excel file — all works identically to pre-upgrade behavior.

## Boundary Map

Not provided.
