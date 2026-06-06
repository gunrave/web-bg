# S01: Full-Stack Dependency Upgrade — UAT

**Milestone:** M001
**Written:** 2026-06-06T04:22:58.150Z

## UAT: Full-Stack Dependency Upgrade (S01)

### Preconditions
- Local development server running (`php artisan serve`)
- Admin user exists (admin@admin.com / password)
- SQLite database with sample data seeded

### Test 1: Automated Build Pipeline
| Step | Action | Expected Outcome |
|------|--------|-----------------|
| 1 | Run `composer validate` | Exit code 0, composer.json is valid |
| 2 | Run `vite build` | Exit code 0, builds in <5s |
| 3 | Run `php artisan test` | 2/2 tests pass |
| 4 | Run `composer outdated --direct` | No packages outdated within target major versions |

**UAT Type:** Contract

### Test 2: Admin Panel Login
| Step | Action | Expected Outcome |
|------|--------|-----------------|
| 1 | Navigate to /admin | Login page loads with correct Filament 4 styling |
| 2 | Enter email: admin@admin.com, password: password | Redirected to admin dashboard |
| 3 | Dashboard renders with Filament widgets | No JS/CSS errors in browser console |

**UAT Type:** Integration

### Test 3: Resource Browsing
| Step | Action | Expected Outcome |
|------|--------|-----------------|
| 1 | Navigate to Pegawai resource | Table loads with data, relationship columns render |
| 2 | Navigate to Gaji resource | Table loads, import button visible |
| 3 | Navigate to Tunker resource | Table loads correctly |
| 4 | Navigate to Tagihan resource | Table loads with correct columns |
| 5 | Navigate to Potong resource | Table loads correctly |
| 6 | Navigate to Periode resource | Table loads with period data |

**UAT Type:** Integration

### Test 4: CRUD Operations
| Step | Action | Expected Outcome |
|------|--------|-----------------|
| 1 | Pegawai: Click Create, fill form, save | New pegawai created, redirected to list |
| 2 | Gaji: Click Create, fill form, save | New gaji created successfully |
| 3 | Any resource: Click Edit on a record | Edit form loads, save updates record |
| 4 | Any resource: Click Delete | Confirmation modal appears, deletion succeeds |

**UAT Type:** Integration

### Test 5: Excel Import (Gaji)
| Step | Action | Expected Outcome |
|------|--------|-----------------|
| 1 | Navigate to Gaji resource | Import button renders in correct Filament 4 styling |
| 2 | Upload valid Excel file | Import completes, records appear in table |

**UAT Type:** UAT

### Test 6: Edge Cases
| Step | Action | Expected Outcome |
|------|--------|-----------------|
| 1 | Run `php artisan route:list` | All 28+ routes registered, no missing Filament routes |
| 2 | Run `php artisan filament:upgrade` | Reports "Successfully upgraded!" with no errors |
| 3 | Check storage/logs/laravel.log | No deprecation warnings or error entries |
| 4 | Open /admin in browser with DevTools | No 404s for CSS/JS/font assets |

**UAT Type:** Operational

### Session Notes
- Pass: All checks pass with above conditions
- Fail: Any HTTP 500, missing CSS/JS, or CLI non-zero exit
