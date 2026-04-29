# Features Research — Payroll Deduction Management

**Context:** Laravel 11 + Filament v3 payroll system for Indonesian government/company context.  
**Users:** Finance team (admin panel), Employees (read-only self-service panel).  
**Scope:** Multi-provider loan deduction tracking, failed deduction handling, reconciliation, payslips, dashboard.

---

## Liability Management

### Table Stakes
| Feature | Complexity | Dependencies |
|---|---|---|
| Record monthly loan liability per employee per provider (tagihan bulanan) | Low | Employee, Provider, Billing Period |
| Set installment amount (pokok + bunga if applicable) | Low | Liability record |
| Track liability status: pending, deducted, failed, settled-cash, waived | Low | Deduction processing |
| Link liability to a billing period (periode penggajian) | Low | Billing Period model |
| Total outstanding principal per employee (across providers) | Medium | Liability aggregation |
| Bulk import liabilities via CSV per provider per period | Medium | File upload, validation |
| Prevent duplicate liability entry for same employee+provider+period | Low | Unique constraint + validation |

### Differentiators
| Feature | Complexity | Dependencies |
|---|---|---|
| Auto-carry forward unpaid liabilities to next period with audit trail | Medium | Period close logic |
| Soft cap check: flag liabilities where installment > configurable % of net gaji | Medium | Salary calculation |
| Multi-installment loan schedule view (full remaining term) | High | Loan term data |
| Provider portal or import webhook for automated tagihan ingestion | High | External integration |

### Anti-features (deliberately exclude in v1)
- Loan origination / approval workflow — system tracks deductions only, not the loan lifecycle
- Interest recalculation engine — amounts come from provider, not computed here
- Direct bank API integration for liability feeds

---

## Deduction Processing

### Table Stakes
| Feature | Complexity | Dependencies |
|---|---|---|
| Auto-generate deductions from confirmed liabilities when payroll is run | Medium | Payroll run trigger, Liability |
| Deduction amount = liability installment amount (as provided by lender) | Low | Liability record |
| Net salary floor check before applying deduction (prevent negative net gaji) | Medium | Salary + allowance calculation |
| Deduction status lifecycle: pending → processed / failed | Low | Status machine |
| Lock deductions once payroll period is closed (audit integrity) | Medium | Period close flag |
| Batch apply deductions for entire billing period in one action | Medium | Filament bulk action |
| Manual override: finance can force-fail or force-apply a deduction with reason | Low | Override log |

### Differentiators
| Feature | Complexity | Dependencies |
|---|---|---|
| Configurable deduction priority ordering when net salary is limited (e.g., koperasi before bank) | Medium | Provider priority config |
| Partial deduction: deduct what salary allows, remainder goes to failed-cash queue | High | Salary calc + split logic |
| Deduction simulation: preview outcome before committing payroll run | High | Read-only calculation pass |

### Anti-features
- Real-time payroll processing / instant disbursement — period-based batch only
- Automated bank transfer initiation from within this system
- Multi-currency handling

---

## Failed Deduction Handling

### Table Stakes
| Feature | Complexity | Dependencies |
|---|---|---|
| Mark deduction as failed with categorized reason (insufficient salary, status issue, manual override) | Low | Deduction status |
| Generate cash payment obligation record for failed deduction | Low | Failed deduction → cash liability |
| Record cash receipt from employee (full or partial) with date and receiver (finance staff) | Low | Cash receipt model |
| Track partial payment: how much received vs. outstanding for failed deduction | Low | Receipt aggregation |
| Cash receipt status: unpaid, partial, settled | Low | Derived from receipts |
| List all outstanding cash obligations per employee | Low | Filter + aggregate |
| List all cash collected per provider for remittance | Low | Group by provider |

### Differentiators
| Feature | Complexity | Dependencies |
|---|---|---|
| Cash receipt acknowledgment printout (bukti setor tunai) for employee | Medium | PDF generation |
| Carry-over: unresolved cash obligation auto-creates liability in next period | Medium | Period close logic |
| Finance dashboard alert: employees with unresolved failed deductions approaching next period close | Medium | Dashboard widget |
| Aging report: failed deductions outstanding 1/2/3+ months | Medium | Date diff calculation |

### Anti-features
- Online payment gateway integration for employee self-pay
- Automated debt collection workflows / reminders via email/SMS in v1
- Installment plans for failed deduction settlement

---

## Reports & Exports

### Table Stakes
| Feature | Complexity | Dependencies |
|---|---|---|
| Per-provider reconciliation report: employee list + deducted amount + cash amount + total to remit | Medium | Deduction + Receipt data |
| Export reconciliation to Excel/CSV per provider per period | Medium | Laravel Excel / Spatie |
| Payroll summary report: total gaji pokok, tunjangan, potongan, gaji bersih per period | Medium | Aggregation queries |
| Failed deduction register: all failures in a period with reasons | Low | Filtered deduction list |
| Cash collection ledger: all cash received in a period grouped by provider | Low | Receipt aggregation |
| Individual employee deduction history (usable for employee queries) | Low | Filter by employee |

### Differentiators
| Feature | Complexity | Dependencies |
|---|---|---|
| SPT-compatible export format for government reporting | High | Regulatory knowledge |
| Per-period variance report: compare deduction plan vs. actual per provider | Medium | Liability vs. Deduction diff |
| Provider remittance advice letter (PDF, formal surat setor) | Medium | PDF template |
| Audit trail export: who changed what, when, for compliance | Medium | Activity log (Spatie) |

### Anti-features
- Business intelligence / OLAP pivot reports in v1
- Automated email delivery of reports to providers
- Real-time report streaming for large datasets

---

## Employee Self-Service

### Table Stakes
| Feature | Complexity | Dependencies |
|---|---|---|
| Employee can view their own payslip (slip gaji) for current and past periods | Low | Auth + Payslip model |
| Payslip shows: gaji pokok, tunjangan (itemized), potongan (itemized per provider), gaji bersih | Low | Salary + deduction data |
| PDF download of payslip | Medium | PDF generation (DomPDF/Browsershot) |
| Employee can see their loan liabilities per provider (tagihan aktif) | Low | Liability filter by employee |
| Employee can see deduction history and failed deduction status | Low | Deduction filter by employee |
| Employee can see outstanding cash obligations | Low | Failed deduction + receipt |

### Differentiators
| Feature | Complexity | Dependencies |
|---|---|---|
| Payslip digital signature / QR code for verification authenticity | High | Signing library |
| Notification to employee when payslip is available (in-app or email) | Medium | Notifications, queue |
| Employee can view cash receipt history (proof of cash payments made) | Low | Receipt filter by employee |
| Multi-period payslip comparison view | Medium | UI complexity |

### Anti-features
- Employee can edit/dispute payslip data directly — disputes go through finance admin
- Employee loan application through self-service portal in v1
- Mobile app (web-responsive Filament panel is sufficient for v1)

---

## Dashboard

### Table Stakes
| Feature | Complexity | Dependencies |
|---|---|---|
| Current period status: open / processing / closed | Low | Billing period state |
| Total employees processed vs. pending in current period | Low | Count queries |
| Total deductions collected this period (by channel: potong gaji vs. cash) | Low | Aggregation |
| Count of failed deductions requiring attention (cash collection pending) | Low | Failed + unsettled filter |
| Per-provider summary widget: expected vs. collected vs. outstanding | Medium | Provider + deduction grouping |
| Quick-access actions: run deductions, view pending cash, export provider report | Low | Navigation links / actions |

### Differentiators
| Feature | Complexity | Dependencies |
|---|---|---|
| Month-over-month trend: deduction totals, failure rates by provider | Medium | Historical aggregation |
| Employee risk flags: employees consistently failing deductions (>2 months) | Medium | Failure history query |
| Cash collection progress bar per provider approaching period close deadline | Medium | Period close date config |
| Remittance readiness checklist: all providers have complete reconciliation before close | Medium | Validation rules per provider |

### Anti-features
- Real-time websocket updates on deduction processing
- Predictive analytics / ML-based failure prediction in v1
- Cross-period financial forecasting

---

## Summary: Complexity Distribution

| Category | Table Stakes Items | Differentiator Items | Avg Complexity |
|---|---|---|---|
| Liability Management | 7 | 4 | Low–Medium |
| Deduction Processing | 7 | 3 | Medium |
| Failed Deduction Handling | 7 | 4 | Low–Medium |
| Reports & Exports | 6 | 4 | Medium |
| Employee Self-Service | 6 | 4 | Low–Medium |
| Dashboard | 6 | 4 | Low–Medium |

**Total table stakes:** 39 features — core system is well-bounded and implementable.  
**Total differentiators:** 23 features — prioritize by finance team pain, defer the rest to v2.

---

## Key Behavioral Expectations (Finance Team)

1. **Period is sacred** — payroll period must lock cleanly; no edits after close without explicit override + audit log
2. **Provider handoff is the output** — everything leads to "how much do I send to each provider and via what channel?"
3. **Cash is manual and messy** — the system must make cash collection trackable and auditable without assuming it's automated
4. **Failures need names** — failed deductions must be tied to a human cause (reason code), not just a flag
5. **Excel is non-negotiable** — finance will always want to export and cross-check in Excel regardless of what the system shows

## Key Behavioral Expectations (Employees)

1. **Payslip = source of truth** — employees care most about the net figure and that deductions are itemized clearly
2. **Cash payment = anxiety** — when they pay cash, they want proof immediately (bukti setor)
3. **Self-service is read-only** — employees do not expect to change data; they expect to understand what happened
