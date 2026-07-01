# Curtiss ERP — Accounting Module: Remaining Issues Report

**Report Date:** July 1, 2026  
**Scope:** Open items still requiring attention

---

## Summary

All critical bugs and security vulnerabilities have been resolved. The following **18 minor issues remain** requiring attention.

| Category | Count | Severity |
|----------|-------|----------|
| Code Quality | 2 | LOW-MEDIUM |
| Missing Features | 3 | MEDIUM |
| UX/UI | 3 | LOW |
| Performance | 2 | LOW-MEDIUM |
| Integration Gaps | 6 | MEDIUM-HIGH |
| New Issues | 2 | MEDIUM |
| **Total** | **18** | |

---

## 1. Code Quality Issues

### CODE-01: Some Controllers Still Use Direct DB Access
**Severity:** LOW  
**Files:** `app/Controllers/ReportController.php`, `app/Controllers/RepTrackingController.php`

ReportController contains extensive inline SQL for quick_view, fetch_data, and other AJAX endpoints. RepTrackingController also uses `new Database()` directly. While BankingController was refactored into ChartOfAccount model methods, some controllers still bypass models.

---

### CODE-02: Hardcoded Account Category Strings
**Severity:** LOW  
**Files:** `app/Controllers/AccountingController.php`

Account category validation arrays are inline strings (e.g., `['Current Asset', 'Fixed Asset', 'Non-current Asset']`). These could be extracted to constants or a config file for easier management.

---

## 2. Missing Features

### MISS-08: No Budget vs Actual Report
**Severity:** MEDIUM

The `budgets` table exists but there is no comparison report showing budget vs actual spending.

---

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

### INT-03: Expenses GL Posting Unverified
**Severity:** MEDIUM  
**File:** `app/Controllers/ExpensesController.php`

Expenses have `journal_entry_id` in schema but posting to GL is not fully verified.

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

## 6. New Issues

### NEW-01: Database.php Overrides User-Entered Journal References
**Severity:** MEDIUM  
**File:** `core/Database.php` (lines 72-116)

The `execute()` method auto-generates a sequential reference (e.g., "2026001") for ALL journal entry inserts, overriding user input. The manual reference is preserved only in the description field (e.g., "[INV-1001] Memo"). Users who type "INV-1001" will find "2026001" in the reference column instead.

**Suggested Fix:** Only auto-generate reference when no user reference is provided.

---

### NEW-02: Trial Balance Has No Date Range Filter in ReportEngine
**Severity:** LOW  
**File:** `app/Services/ReportEngine.php` (lines 562-579)

The ReportEngine's Trial Balance has no `filters` or `date_column` defined, showing ALL transactions regardless of date. The custom Trial Balance view in `ReportController::viewer()` handles this correctly via the `Report` model, but the ReportEngine version does not.

---

## 7. Implemented Reports (For Reference)

The following financial reports are **already implemented** in the Report Hub:

| Report | Status |
|--------|--------|
| Trial Balance | ✅ |
| Profit & Loss | ✅ |
| Balance Sheet | ✅ |
| Cash Flow | ✅ |
| General Ledger | ✅ |
| Multi-Period Comparison (YoY/MoM) | ✅ |
| Customer Aging (AR) | ✅ |
| Supplier Aging (AP) | ✅ |
| Customer Statement | ✅ |
| Sales Summary, Sales by Customer/Item | ✅ |
| Stock Summary, Stock Movement | ✅ |

---

## 8. Previously Fixed Items

All previously reported issues have been resolved:

| Category | Count | Status |
|----------|-------|--------|
| Critical Bugs (CRIT-01 through CRIT-07) | 7 | ✅ FIXED |
| Security Vulnerabilities (SEC-01 through SEC-04) | 4 | ✅ FIXED |
| Schema Problems (DB-01 through DB-05) | 5 | ✅ FIXED |
| Code Quality - Constants (CODE-02/03) | 2 | ✅ FIXED |
| Code Quality - Type Declarations (CODE-04) | 1 | ✅ FIXED |
| Missing Features - Recurring JEs (MISS-09) | 1 | ✅ FIXED |
| Missing Features - Year Reversal (MISS-15) | 1 | ✅ FIXED |
| Missing Features - Reports (8 reports) | 8 | ✅ FIXED |
| UX - Close Year Preview (UX-04) | 1 | ✅ FIXED |
| Performance - Journal Pagination (PERF-03) | 1 | ✅ FIXED |
| Banking - Model Refactoring | 1 | ✅ FIXED |
| Auto-Reference on Close Year entries | 1 | ✅ FIXED |
| **Total Fixed** | **33** | ✅ |

---

## Recommendations

| Priority | Issue | Effort |
|----------|-------|--------|
| **High** | INT-01: Auto-post invoices to GL | 16 hrs |
| **High** | INT-04: Inventory-to-GL (COGS) integration | 20 hrs |
| **High** | INT-06: Post Rep Tracking entries properly | 8 hrs |
| **Medium** | NEW-01: Fix Database.php reference override | 2 hrs |
| **Medium** | MISS-08: Budget vs Actual report | 8 hrs |
| **Medium** | INT-02: Unify customer payment posting | 8 hrs |
| **Medium** | INT-05: Auto-post payroll to GL | 8 hrs |
| **Low** | MISS-11: Multi-currency support | 16 hrs |
| **Low** | UX-01: Fix COA hierarchical search | 4 hrs |
| **Low** | PERF-01: Add COA pagination | 3 hrs |

---

*This report was generated through automated codebase analysis. All findings should be verified manually before acting on them.*