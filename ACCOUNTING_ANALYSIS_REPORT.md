# Curtiss ERP — Accounting Module: Remaining Issues Report

**Report Date:** July 2, 2026  
**Scope:** Open items still requiring attention

---

## Summary

All critical bugs, security vulnerabilities, and database schema problems have been resolved. The following **14 minor issues remain**.

| Category | Count | Severity |
|----------|-------|----------|
| Code Quality | 2 | LOW |
| Missing Features | 2 | LOW-MEDIUM |
| UX/UI | 3 | LOW |
| Performance | 2 | LOW |
| Integration Gaps | 5 | MEDIUM-HIGH |
| **Total** | **14** | |

---

## 1. Code Quality Issues

### CODE-01: Some Controllers Still Use Direct DB Access
**Severity:** LOW  
**Files:** `app/Controllers/ReportController.php`, `app/Controllers/RepTrackingController.php`

ReportController contains extensive inline SQL for quick_view, fetch_data, and other AJAX endpoints. RepTrackingController also uses `new Database()` directly. BankingController was already refactored into the ChartOfAccount model.

---

### CODE-02: Hardcoded Account Category Validation Arrays
**Severity:** LOW  
**Files:** `app/Controllers/AccountingController.php`

Account category validation arrays are inline strings (e.g., `['Current Asset', 'Fixed Asset', 'Non-current Asset']`). These could be extracted to constants or a config file.

---

## 2. Missing Features

### MISS-10: No Journal Entry Approval Workflow
**Severity:** LOW

All posted entries are immediately finalized without an approval/review step.

---

### MISS-11: No Multi-Currency Support
**Severity:** MEDIUM

No currency field on accounts or transactions. All entries assumed to be in a single currency (Rs).

---

## 3. UX/UI Issues

### UX-01: COA Search Ignores Hierarchy
**Severity:** LOW  
**File:** `app/Views/accounting/coa.php`

The search filter hides non-matching rows but doesn't handle hierarchical visibility. If a child matches but parent doesn't, the child becomes orphaned.

---

### UX-02: Journal Template Loads Without Feedback
**Severity:** LOW  
**File:** `app/Views/accounting/journal.php`

Template loading clears rows and fills accounts with no confirmation or undo.

---

### UX-03: Empty Journal Form Not Warned
**Severity:** LOW  
**File:** `app/Views/accounting/journal.php`

If user removes all journal entry lines, the submit button stays disabled but no warning message is shown.

---

## 4. Performance

### PERF-01: COA Returns All Accounts Without Pagination
**Severity:** LOW  
**File:** `app/Models/ChartOfAccount.php`

For large COAs (500+ accounts), returns all records at once. Journal entries were already paginated.

---

### PERF-02: Customer AR Rendered Inline in COA Table
**Severity:** LOW  
**File:** `app/Views/accounting/coa.php`

Customer AR data rendered inline in the COA table. For 1000+ customers this creates a large page.

---

## 5. Integration Gaps

### INT-01: Invoices Don't Auto-Post to GL
**Severity:** HIGH  
**File:** `app/Controllers/SalesController.php`

The `invoices` table has `journal_entry_id` column but invoices don't automatically post double-entry to the general ledger.

---

### INT-02: Customer Payments Don't Use JournalEntry Model
**Severity:** HIGH  
**File:** `app/Controllers/CustomerPaymentController.php`

Customer payments have `journal_entry_id` in schema but the payment controller handles accounting separately.

---

### INT-04: Stock Ledger Not Linked to Accounting (COGS)
**Severity:** HIGH

Inventory value changes (purchases, sales, adjustments) in `stock_ledger` are not double-posted to COGS or Inventory accounts.

---

### INT-05: Payroll Not Auto-Posted to GL
**Severity:** MEDIUM  
**File:** `app/Controllers/PayrollController.php`

Payroll runs have `journal_entry_id` field but no automated payroll-to-GL posting.

---

### INT-06: Rep Tracking Entries Stored as JSON
**Severity:** HIGH  
**File:** `app/Controllers/RepTrackingController.php`

Accounting entries from the rep mobile app are saved as JSON in `deliveries.accounting_entries_json` instead of being posted to `transactions` and `journal_entries`.

---

## 6. Previously Fixed Items (For Reference)

| Category | Count | Status |
|----------|-------|--------|
| Critical Bugs (CRIT-01 through CRIT-07) | 7 | ✅ |
| Security Vulnerabilities (SEC-01 through SEC-04) | 4 | ✅ |
| Database Schema Problems (DB-01 through DB-05) | 5 | ✅ |
| Code Quality - Constants & Type Declarations | 3 | ✅ |
| Missing Features - Recurring JEs, Year Reversal, Budget vs Actual | 3 | ✅ |
| Missing Reports (Trial Balance, P&L, Balance Sheet, Cash Flow, GL, AR/AP Aging, Customer Statement, Multi-Period) | 8 | ✅ |
| UX - Close Year Preview | 1 | ✅ |
| Performance - Journal Pagination | 1 | ✅ |
| Banking - Model Refactoring | 1 | ✅ |
| Database.php Reference Override | 1 | ✅ |
| Trial Balance Date Range Filter | 1 | ✅ |
| **Total Fixed** | **35** | ✅ |

---

## Recommendations

| Priority | Issue | Effort |
|----------|-------|--------|
| **High** | INT-01: Auto-post invoices to GL | 16 hrs |
| **High** | INT-04: Inventory-to-GL (COGS) integration | 20 hrs |
| **High** | INT-06: Post Rep Tracking entries properly | 8 hrs |
| **Medium** | INT-02: Unify customer payment posting | 8 hrs |
| **Medium** | INT-05: Auto-post payroll to GL | 8 hrs |
| **Low** | MISS-11: Multi-currency support | 16 hrs |
| **Low** | UX-01: Fix COA hierarchical search | 4 hrs |
| **Low** | PERF-01: Add COA pagination | 3 hrs |

---

*This report was generated through automated codebase analysis. All findings should be verified manually before acting on them.*