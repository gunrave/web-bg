# M003: Performance Optimization (DRAFT)

**Gathered:** 2026-06-06
**Status:** Draft — discuss before execution

## Project Description

Eliminate N+1 query problems across Filament table views, add database indexes on foreign key columns, and optimize import query patterns. The app currently has known N+1 issues — relationship columns like `pegawai.nama`, `periode.penagih.nama` in table views trigger additional queries per row.

## Why This Milestone

Table views with hundreds of employee/salary records will degrade significantly without eager loading. The import pipeline does per-row Pegawai lookups by NIK. This milestone makes the app fast enough to be usable at scale. Depends on M001 (needs current Laravel/Filament to apply best-practice eager loading patterns).

## Draft Scope

### In Scope

- Add eager loading to all Filament table views where relationship columns are used:
  - PegawaiResource: check if any relation columns need eager loading
  - GajiResource: `pegawai.nama` column
  - TunkerResource: `pegawai.nama` column
  - TagihanResource: `periode.periode`, `periode.penagih.nama`, `pegawai.nama` columns
  - PotongResource: `tagihan.periode.periode`, `tagihan.periode.penagih.nama`, `tagihan.pegawai.nama`, `tagihan.jumlah` columns
- Add database indexes via migration for foreign key columns:
  - `gajis.pegawai_id`
  - `tunkers.pegawai_id`
  - `tagihans.pegawai_id`, `tagihans.periode_tagihan`
  - `potongs.tagihan_id`
  - `periode_tagihans.penagih_id`
- Optimize import pipeline: pre-load Pegawai lookup map before import loop (eliminate per-row `WHERE nik = ?` queries)
- Verify zero N+1 queries via Laravel Debugbar query log on every Filament table page

### Out of Scope / Non-Goals

- Query caching (Redis/Memcached)
- Pagination optimization beyond eager loading
- Asset optimization (images, CSS/JS minification — handled by Vite)
- Database engine migration (stays SQLite)
- Queue workers for imports

## Draft Acceptance Criteria

- Laravel Debugbar shows zero duplicate/relationship queries on all 5 Filament resource table pages
- Foreign key indexes exist on all specified columns (verified via `PRAGMA index_list` or schema inspection)
- Import of 100-row Excel file completes without per-row individual database lookups
- Existing 2 Pest tests still pass

## Open Questions

- Should we add compound indexes for commonly filtered/sorted column combinations?
- Any specific resource page that's already noticeably slow and needs priority attention?
