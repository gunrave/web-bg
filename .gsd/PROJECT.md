# PROJECT.md — Sistem Manajemen Gaji (Salary Management System)

## What This Is

A brownfield expansion of an existing Laravel 11 + Filament v3 salary management application for an organization that processes monthly payroll with multi-provider loan deductions — enabling finance teams to record liabilities, compute deductions, handle failed deductions with alternative payment methods, and generate per-provider reconciliation reports and employee payslips.

---

## Problem Being Solved

Finance teams currently struggle to:
- Track monthly loan liabilities per employee from multiple providers (koperasi + external banks)
- Carry forward unpaid/failed deductions across months
- Record alternative payment methods (cash) when payroll deduction fails
- Reconcile total payments forwarded to each provider (payroll cut + cash)
- Produce audit-grade monthly reports for management and providers

---

## Who Uses It

| Role | Panel | Responsibilities |
|------|-------|-----------------|
| Finance / Admin | `/admin` | Full access: input liabilities, salaries, override deductions, record cash payments, generate reports |
| Employee | `/app` | Read-only: view own monthly payslip and deduction history |

---

## Core Value

**The ONE thing that must work:** Finance team can finalize monthly payroll — computing net salary after all provider deductions — and produce a per-provider report that reconciles exactly what was deducted vs paid via cash, so every provider can be settled accurately.

---

## Existing Capabilities (Validated)

The codebase already has working Filament resources for:

- ✓ Employee (Pegawai) management with soft deletes — existing
- ✓ Salary (Gaji) recording with Excel import (GajiImporter) — existing
- ✓ Performance allowance / Tunjangan Kinerja (Tunker) recording — existing
- ✓ Billing provider (Penagih) management — existing
- ✓ Billing period (PeriodeTagihan) management — existing
- ✓ Liability/invoice (Tagihan) per employee per period — existing
- ✓ Deduction (Potong) recording — existing
- ✓ Two Filament panels: admin (/admin) and employee app (/app) — existing
- ✓ Excel import pipeline (Maatwebsite + Filament native importers) — existing

---

## What Needs to Be Built

### Monthly Liability Workflow
- Finance inputs loan liabilities per employee per provider each month
- Carry-forward: automatically populate this month from last month's data as a starting point
- Manual override of any liability amount before finalizing

### Payroll Computation
- Input base salary (Gaji Pokok) + performance allowance (Tunker) per employee per month
- Compute gross salary = base + allowances
- Compute all planned deductions from active liabilities
- Compute net salary = gross - all deductions

### Deduction Status & Failure Handling
- Deduction can fail for multiple reasons: insufficient net salary, employee status (leave/terminated), manual flag by finance
- Failed deduction is tracked with: reason, amount, provider, period
- Finance can log an alternative payment method for a failed deduction:
  - **Cash (full):** employee paid the full installment in cash to finance → deduction succeeds with status "cash"
  - **Cash (partial):** employee paid partial amount → recorded as partial, shortfall reported to provider
  - **Other:** extensible for future payment types

### Per-Provider Reconciliation
- For each provider, for each period: list all employees + their deduction status (success/failed/cash/partial)
- Total to forward = sum of successful payroll deductions + cash received
- Partial payments itemized separately

### Reports & Exports
- **PDF payslip** per employee: gross, deductions by provider, net salary, deduction statuses
- **Excel export**: payroll summary for finance team (all employees, all deductions)
- **Per-provider rekap**: what was deducted from salary + what was paid in cash → total to forward
- **Monthly dashboard**: summary of total payroll, total deductions, failed deductions, pending cash payments

### Employee Self-Service
- Employee logs into `/app` panel and views their own payslip history
- Payslip shows: gross salary, deductions by provider (with status), net pay

---

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Build on existing Filament resources (Tagihan, Potong, Penagih, PeriodeTagihan) | Core data model is mostly correct, avoid rework | Extend existing, refactor where needed |
| Carry-forward = auto-copy last month's liabilities as draft | Finance described this as "based on previous month" — reduces data entry | Confirmed |
| Cash payment creates a sibling record on Potong with payment_method = 'cash' | Keeps deduction history unified per provider per period | Pending |
| Failed deduction does not auto-carry-forward (tracked separately) | Finance decides alternative handling per case | Pending |
| Employee panel (`/app`) is read-only payslip view only | No self-service editing required | Confirmed |
| PDF payslip generated via Laravel (likely DomPDF or similar) | Standard approach for Laravel PDF | Pending |

---

## Tech Stack (Existing)

- **PHP ^8.2** + **Laravel ^11.9**
- **Filament v3.2** (Livewire-based admin panel)
- **Eloquent ORM** with existing models: Pegawai, Gaji, Tunker, Penagih, PeriodeTagihan, Tagihan, Potong
- **Maatwebsite/Excel ^3.1** (import/export)
- **Pest** for testing
- **Vite 5** for frontend assets

---

## Constraints

- Must extend existing models and migrations — do not break existing data
- Filament v3 patterns throughout (Resources, Relation Managers, Importers, Actions)
- Finance team is the primary user; UI must be practical, not pretty
- No external API integrations with loan providers (all manual entry)
- No automated payroll approval workflow in v1 (finance runs reports manually)

---

## Out of Scope (v1)

- Automated bank/provider API integration — manual entry only
- Payroll approval workflow (multi-level sign-off) — future
- Tax calculation (PPh 21) — not mentioned
- Leave/attendance integration — not mentioned
- Mobile app — web only
- Email delivery of payslips — employee logs in to view

---

_Last updated: 2026-02-25 after initialization_
