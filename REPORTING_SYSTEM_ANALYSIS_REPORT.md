# Curtiss ERP - Reporting System Comprehensive Analysis Report

**Date:** July 5, 2026  
**Analyst:** AI Code Review  
**Scope:** Curtiss-ERP Reporting Subsystem (Controllers, Engine, Models, Views)

---

## Executive Summary

The Curtiss ERP Reporting System is a custom-built, metadata-driven reporting engine with **30+ registered reports** across **9 categories**. It implements a dynamic query builder with server-side pagination, sorting, filtering, and multiple export formats (CSV, Excel, Word, JSON, XML). The system includes interactive drill-down capabilities, a Quick View side panel, and a dedicated accounting-grade print layout.

While the system demonstrates sophisticated architecture and feature breadth, it contains significant issues in **code duplication** (especially between ReportController.php methods vs. ReportEngine registry), **security vulnerabilities** (SQL injection risks in dynamic SQL construction), **performance problems** (no caching, no query optimization), and **maintainability concerns** (3,000+ line files, mixed concerns, no tests).

---

## System Architecture Overview

```
┌──────────────────────────────────────────────────────────────┐
│                   ReportController.php (806 lines)           │
│  ┌─────────┐ ┌──────────┐ ┌───────┐ ┌──────┐ ┌─────────┐  │
│  │ index() │ │ viewer() │ │fetch_ │ │export│ │print_   │  │
│  │         │ │          │ │data() │ │ ()  │ │report() │  │
│  └─────────┘ └──────────┘ └───────┘ └──────┘ └─────────┘  │
│  ┌───────────┐ ┌──────────┐ ┌──────────┐ ┌────────────┐  │
│  │trial_ │profit_  │balance_ │ cash_flow()│
│  │balance()│ │loss()   │ │sheet()  │            │
│  └───────────┘ └──────────┘ └──────────┘ └────────────┘  │
│  ┌───────────┐ ┌───────────┐                               │
│  │ar_aging()│ │fifo_     │                               │
│  │          │ │profit()  │                               │
│  └───────────┘ └───────────┘                               │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌──────────────────────────────────────────────────────────────┐
│                   ReportEngine.php (1158 lines)               │
│  ┌─────────────────────────┐  ┌───────────────────────────┐  │
│  │ getCategories()         │  │ getReportsRegistry()      │  │
│  │ (9 categories)          │  │ (30+ report definitions)  │  │
│  └─────────────────────────┘  └───────────────────────────┘  │
│  ┌───────────────────────────────────────────────────────┐   │
│  │  fetchData($reportKey, $filters, $page, $limit,      │   │
│  │           $sortCol, $sortDir)                         │   │
│  │  - Dynamic SQL construction                           │   │
│  │  - 16 filter types (customer, supplier, product, etc) │   │
│  │  - Server-side pagination with COUNT(*)               │   │
│  │  - Grand total calculation on full dataset            │   │
│  │  - Sorting with whitelist column validation           │   │
│  └───────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌──────────────────────────────────────────────────────────────┐
│                   Report.php Model (1051 lines)              │
│  - getAccountsByTypes()       - getARAging()                 │
│  - getTrialBalanceData()      - getFIFOSalesData()           │
│  - getComparativeBalances()   - getQuickViewData()           │
│  - getReportFiltersData()     - resolveEntityName()          │
│  - getCompanySettings()       - 10+ Quick View types         │
└──────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌──────────────────────────────────────────────────────────────┐
│                   Views (4 major files)                      │
│  ├─ index.php (250 lines) - Report Hub Dashboard            │
│  ├─ viewer.php (2231 lines) - Interactive Report Viewer     │
│  │    ├─ Filter sidebar (16 filter types)                   │
│  │    ├─ Interactive table with drill-down                  │
│  │    ├─ Server-side pagination controls                    │
│  │    ├─ Page subtotals + Grand totals                      │
│  │    ├─ Client-side row filtering                          │
│  │    ├─ Quick View side panel (10+ entity types)           │
│  │    ├─ Export dropdown (6 formats)                        │
│  │    ├─ Share functionality (link, email, WhatsApp)        │
│  │    └─ Print button                                       │
│  ├─ print.php (359 lines) - Accounting-grade print layout   │
│  └─ _header.php (60 lines) - Legacy header template         │
└──────────────────────────────────────────────────────────────┘
```

---

## 🔴 CRITICAL ISSUES

### 1. SQL Injection Risk in Dynamic Query Construction

**Files:** `app/Services/ReportEngine.php` (Lines 1088-1137)

The `fetchData()` method dynamically builds SQL queries by concatenating the base SQL with filter conditions. While it uses prepared statements for parameter binding, the **column names** for sorting are insufficiently validated:

```php
// Line 1127 - Sort column validation
if (!empty($sortCol) && isset($metadata['columns'][$sortCol]) && preg_match('/^[a-zA-Z0-9_\.]+$/', $sortCol)) {
    $baseSql .= " ORDER BY " . $sortCol . " " . ($sortDir === 'DESC' ? 'DESC' : 'ASC');
}
```

**Issues:**
- `preg_match('/^[a-zA-Z0-9_\.]+$/', $sortCol)` - The dot character allows table.column syntax but could enable SQL injection via crafted column names that bypass regex
- The LIMIT/OFFSET values use proper parameter binding ✅
- The base SQL definitions in `getReportsRegistry()` contain raw SQL with no validation layer

**Risk:** **HIGH** - If an attacker can control `sort_col` parameter and bypass the regex, they could inject SQL

**Fix:** 
- Use a whitelist approach: only allow sort columns explicitly defined in the report metadata
- Remove dot from the regex pattern

### 2. Code Duplication: ReportController Methods vs. ReportEngine Registry

**Files:** `app/Controllers/ReportController.php` vs `app/Services/ReportEngine.php`

The reporting system has **two parallel implementations** for Balance Sheet, Cash Flow, and P&L:

| Feature | ReportController Method | ReportEngine Registry |
|---------|----------------------|---------------------|
| Balance Sheet | `balance_sheet()` (Lines 681-713) | `'balance_sheet' => ['custom_render' => true]` |
| Cash Flow | `cash_flow()` (Lines 715-759) | `'cash_flow' => ['custom_render' => true]` |
| P&L | `profit_loss()` (Lines 650-679) | No direct registry entry |
| Trial Balance | `trial_balance()` (Lines 618-648) | Has registry entry AND controller method |

**Issues:**
- **Balance Sheet** has THREE implementations: (1) in `ReportController::balance_sheet()`, (2) in `ReportController::viewer()` as a special case (Lines 58-116), (3) marked as custom in registry
- **Cash Flow** has THREE implementations: (1) in `ReportController::cash_flow()`, (2) in `ReportController::viewer()` as a special case (Lines 118-163), (3) marked as custom in registry
- **P&L** logic is duplicated between `ReportController::profit_loss()` and `Report::getAccountsByTypes()` 
- The `viewer()` method has massive switch cases (Lines 58-227) that should be separate methods

**Risk:** **HIGH** - Changes to financial logic must be made in 3+ places; bugs in one method won't surface until cross-validation

**Fix:**
- Remove the duplicate methods in ReportController and route all reports through `viewer()` using the registry
- Remove the special case code in `viewer()` for balance_sheet and cash_flow

### 3. No Input Validation or Sanitization

**File:** `app/Controllers/ReportController.php`

All filter values from `$_GET` are passed directly to the engine with no validation:

```php
// Line 273-292
$filters = [
    'start_date' => $_GET['start_date'] ?? null,
    'end_date' => $_GET['end_date'] ?? null,
    'customer' => $_GET['customer'] ?? null,
    // ... 13 more raw GET params
];
```

**Issues:**
- No validation that `start_date`/`end_date` are valid dates
- No validation that `customer`, `supplier`, `product` IDs exist
- No max length validation on any field
- No type coercion (IDs should be integers)

**Risk:** **MEDIUM-HIGH** - Undefined behavior with invalid inputs; potential for abuse

**Fix:**
- Add input validation in controller before passing to engine
- Validate date formats with `strtotime()` or `DateTime::createFromFormat()`
- Cast IDs to integers

---

## 🟠 HIGH PRIORITY ISSUES

### 4. No Caching Strategy

The entire reporting system executes full database queries on every request:
- **No query result caching** - Same report with same filters hits the database every time
- **No Redis/Memcached integration**
- **No HTTP caching headers** (ETag, Last-Modified)
- **No report result caching** - Great for large reports or frequently accessed dashboards

**Impact:**
- Repeated access to the same report with same filters = wasted database overhead
- Dashboard/report hub loads `getReportFiltersData()` which runs 12+ separate SQL queries **on every page load** (see Report.php Lines 463-506)

**Fix:**
- Cache filter dropdown data (customers, suppliers, products) - it rarely changes
- Cache report results with TTL (e.g., 5 minutes for real-time, 1 hour for historical)
- Use Redis for session storage and caching

### 5. Performance: N+1 Query Patterns & Unoptimized SQL

**File:** `app/Models/Report.php`

**5a. `getReportFiltersData()` runs 12 separate SQL queries** (Lines 463-506):
```php
$data['customers'] = $this->db->resultSet();  // Query 1
$data['suppliers'] = $this->db->resultSet();  // Query 2
$data['products'] = $this->db->resultSet();   // Query 3
// ... 9 more queries
```

**5b. `getAccountsByTypes()` uses HAVING balance != 0** which prevents MySQL index usage and requires a full temp table scan.

**5c. Correlated subqueries in Customer Statement** (Lines 514-539):
```sql
COALESCE((SELECT CONCAT(' #', c.cheque_number) FROM cheques c WHERE c.customer_id = cp.customer_id AND c.amount = cp.amount AND ABS(TIMESTAMPDIFF(SECOND, c.created_at, cp.created_at)) < 60 ORDER BY c.id DESC LIMIT 1), '')
```
This subquery runs for **every payment row** - extremely inefficient for large datasets.

**5d. `grand_totals` calculation runs the full query twice** (Lines 1088-1124):
```php
$countSql = "SELECT COUNT(*) as cnt FROM (" . $baseSql . ") as temp_table";
// ...
$totalsSql = "SELECT " . implode(', ', $totalSelects) . " FROM (" . $baseSql . ") as totals_table";
// ...
$this->db->query($baseSql);  // Third execution with LIMIT/OFFSET
```
The same base SQL query is executed **three times** - once for count, once for totals, once for data. With large datasets this is 3x the load.

**Fix:**
- Use `SQL_CALC_FOUND_ROWS` or window functions to get count + data in one query
- Calculate grand totals in application code when paginating
- Merge filter queries into fewer combined queries
- Add proper indexes on `invoice_date`, `customer_id`, `vendor_id`, `item_id`, `status`

### 6. Mixed Concerns: 2,200+ Line Viewer File

**File:** `app/Views/reports/viewer.php` (2,231 lines)

This single file contains:
- CSS styles (500+ lines)
- HTML template structure (300+ lines)
- JavaScript for table rendering, pagination, filtering (700+ lines)
- JavaScript for Quick View panel with 10+ entity templates (800+ lines)
- JavaScript for drill-down, export, share, breadcrumbs

**Issues:**
- Impossible to unit test JavaScript logic
- CSS is inline - no separation of concerns
- Same Quick View rendering templates duplicated in PHP (`Report::getQuickViewData()`) and JavaScript
- Maintenance nightmare

**Fix:**
- Extract CSS to separate file
- Extract JavaScript to separate files (report-viewer.js, quick-view.js)
- Consider rendering Quick View templates server-side and sending as JSON

### 7. Export Logic Mixed with Controller

**File:** `app/Controllers/ReportController.php` (Lines 348-488)

The `export()` method contains HTML table generation for Excel export inline:
```php
echo '<html><head><meta charset="utf-8"></head><body>';
echo '<h2>' . htmlspecialchars($metadata['title']) . '</h2>';
echo '<table border="1">';
// ... 40+ lines of HTML generation
```

This should be in a dedicated export service class.

**Fix:**
- Create `app/Exporters/CsvExporter.php`, `ExcelExporter.php`, `JsonExporter.php`, etc.
- Use a library like PhpSpreadsheet for proper Excel files instead of HTML tables
- Use a streaming approach for large exports instead of loading 10,000 rows into memory

### 8. No Access Control or Permissions

**File:** `app/Controllers/ReportController.php`

The constructor only checks for authenticated session (Line 11-14):
```php
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . APP_URL . '/auth/login');
    exit;
}
```

- No role-based access control (who can see financial reports vs. inventory reports)
- No data-level security (rep should only see their own customers)
- No reporting access audit trail

**Fix:**
- Implement report-level permissions based on user role
- Implement data-level filtering based on user territory/route assignment

---

## 🟡 MEDIUM PRIORITY ISSUES

### 9. Duplicate Filter Extraction Logic

The same 16-line filter extraction array appears in **three methods**:

1. `fetch_data()` (Lines 273-292)
2. `export()` (Lines 362-381)  
3. `print_report()` (Lines 506-525)

**Fix:** Extract to a private method `getFiltersFromRequest()` in the controller.

### 10. No CSV Header Injection Protection

**File:** `app/Controllers/ReportController.php` (Lines 467-484)

The CSV export uses `fputcsv()` with raw values. If a report column contains `=`, `+`, `-`, or `@` at the start, it could trigger formula injection in Excel/Google Sheets.

**Fix:** Prefix cells starting with `=`, `+`, `-`, `@` with a single quote to prevent formula injection.

### 11. Inconsistent Financial Report Architecture

The financial reports have an inconsistent structure:

| Report | Controller Method | Viewer Special Case | Registry Entry | View File |
|--------|------------------|-------------------|----------------|-----------|
| Profit & Loss | Yes (`profit_loss()`) | No | Yes | `profit_loss.php` |
| Balance Sheet | Yes (`balance_sheet()`) | Yes (Lines 58-116) | Yes | `balance_sheet.php` |
| Cash Flow | Yes (`cash_flow()`) | Yes (Lines 118-163) | Yes | `cash_flow.php` |
| Trial Balance | Yes (`trial_balance()`) | No | Yes | `trial_balance.php` |
| Multi-Period | No | Yes (Lines 167-227) | Yes | `multi_period_comparison.php` |
| AR Aging | Yes (`ar_aging()`) | No | No | `ar_aging.php` |
| FIFO Profit | Yes (`fifo_profit()`) | No | No | `fifo_profit.php` |
| Budget vs Actual | No | No | Yes (no SQL?) | Uses default viewer |

**Note:** `fifo_profit` and `ar_aging` have dedicated controller methods and separate view files but are **not registered in the ReportEngine registry**, meaning they won't appear in the Central Reporting Hub.

**Impact:** Users may not know these reports exist unless they know the direct URL.

### 12. Hardcoded Values in Metrics Layer

**File:** `app/Services/ReportEngine.php` (Lines 789-803)

The Monthly KPI report has hardcoded target values:
```php
SELECT 'Monthly Sales Revenue' as metric, 'Rs. 1,000,000.00' as target, ...
SELECT 'New Customers Registered' as metric, '50' as target, ...
SELECT 'Outstanding Receivables' as metric, 'Rs. 200,000.00' as target, ...
SELECT 'Procurement (GRN) Value' as metric, 'Rs. 500,000.00' as target, ...
```

**Fix:** Store KPI targets in a database table with a configuration UI.

### 13. No Audit Logging for Report Access

No logging of:
- Who accessed which report
- When they accessed it
- What filters they used
- What exports they generated

**Fix:** Implement audit logging for report access, especially financial reports.

### 14. Large Dataset Handling Issues

**File:** `app/Services/ReportEngine.php` (Lines 1126-1130)

```php
// Line 1130 - LIMIT/OFFSET for pagination
$baseSql .= " LIMIT :limit OFFSET :offset";
```

Using `LIMIT/OFFSET` for pagination is inefficient for large datasets because MySQL still scans all skipped rows. For the export endpoint, `limit=10000` means fetching 10K rows into memory.

**Fix:**
- Use keyset pagination (WHERE id > :last_id LIMIT :limit) for better performance
- Implement streaming exports for large datasets (yield rows instead of loading all at once)

### 15. Multi-Period Comparison Using Datediff Incorrectly

**File:** `app/Controllers/ReportController.php` (Lines 176-180)

```php
if ($comparisonType === 'yoy') {
    $compStartDate = date('Y-m-d', strtotime('-1 year', strtotime($startDate)));
    $compEndDate = date('Y-m-d', strtotime('-1 year', strtotime($endDate)));
} else {
    $compStartDate = date('Y-m-d', strtotime('-1 month', strtotime($startDate)));
    $compEndDate = date('Y-m-d', strtotime('-1 month', strtotime($endDate)));
}
```

**Issue:** Month-over-Month comparison should use the **previous month's full range**, not simply subtract 1 month from start/end. For example, if the current period is Apr 1-30, the comparison should be Mar 1-31.

### 16. Export Format - Excel is Actually HTML

The "Excel" export (Lines 390-415) generates an HTML table with `.xls` extension. While Excel opens these, it's not a true Excel format:
- No proper formatting
- No formulas
- No sheets
- May trigger security warnings in modern Excel

**Fix:** Use PhpSpreadsheet to generate proper `.xlsx` files.

### 17. Word Export Missing Content Types

The "Word" export (Lines 418-436) generates HTML with `.doc` extension. Modern Word expects `.docx` format and may show conversion warnings.

### 18. JSON Export Exposes Internal Field Names

**File:** `app/Controllers/ReportController.php` (Line 442)
```php
echo json_encode($rows, JSON_PRETTY_PRINT);
```

JSON keys are database column names (`item_code`, `qty_on_hand`, `val_cost`, etc.) rather than user-friendly labels. If this is meant for external API use, the field names are inconsistent.

### 19. No Error Handling for Missing Database Tables

Several report SQL queries reference tables that may not exist (e.g., `stock_batches`, `goods_receipt_notes`). If a table is missing, the query throws an unhandled PDOException that only logs the error and returns empty data. No user-friendly error message.

### 20. Inline JavaScript Event Handlers

**Files:** `app/Views/reports/viewer.php`, `app/Views/reports/index.php`

Using `onclick="loadReportData(1)"`, `onkeyup="filterReports()"` as inline HTML attributes:
- Pollutes global namespace
- Harder to maintain
- Cannot easily attach event listeners
- Potential XSS vector

---

## 🔵 LOW PRIORITY / ENHANCEMENTS

### 21. Filter State Not Preserved in URL

When users apply filters and sort columns, the state is maintained only in memory. Refreshing the page resets all filters. There's also no "share filtered view" functionality.

### 22. Quick View Panel Has No Loading Timeout

The Quick View fetch has no timeout or abort mechanism. If a request hangs, the panel shows a spinner indefinitely.

### 23. No Report Scheduling

No ability to:
- Schedule reports to run at specific times
- Email reports automatically
- Generate recurring reports (daily sales summary, weekly collection report)

### 24. No Chart/Visualization Integration

All reports are tabular. No chart or graph visualizations despite having all the data needed for:
- Sales trends (line charts)
- AR aging distribution (bar charts)
- Product category breakdown (pie charts)
- Route performance comparison

### 25. No Report Comparison or Side-by-Side View

Users cannot compare two reports side by side (e.g., this month vs last month sales).

### 26. Print Layout Lacks Page Numbering

**File:** `app/Views/reports/print.php`

The `@page` CSS uses fixed margins but the `page-footer-right::after { content: "Page " counter(page); }` only works in print mode. The screen preview shows `_` instead of pagination info.

### 27. No Custom Report Builder

Users cannot create custom reports with their own SQL queries or column selections. Everything is hard-coded in the registry.

### 28. No Drill-Through to Source Documents

While Quick View shows entity details, there's no "View Original Invoice", "View Purchase Order" links within the Quick View popup for most entity types.

### 29. Incomplete Breadcrumb Implementation

The breadcrumb logic (Lines 2183-2230 of viewer.php) uses `sessionStorage` for breadcrumb trails but only works within the same browser tab. Opening a report in a new tab resets the trail.

### 30. No Report Favorites / Bookmarks

No ability for users to mark frequently used reports as favorites for quick access.

---

## 📊 Report Inventory

| # | Report Key | Category | Type | Filters | Has Custom View? | Has DB? |
|---|-----------|----------|------|---------|-----------------|--------|
| 1 | `stock_summary` | Inventory | SQL | product, category, warehouse, brand | No | Yes |
| 2 | `stock_balance` | Inventory | SQL | product, warehouse | No | Yes |
| 3 | `stock_movement` | Inventory | SQL | date_range, product, warehouse | No | Yes |
| 4 | `stock_ledger` | Inventory | SQL | date_range, product, warehouse | No | Yes |
| 5 | `stock_aging` | Inventory | SQL | product, category, brand | No | Yes |
| 6 | `inventory_valuation` | Inventory | SQL | product, category, brand | No | Yes |
| 7 | `reorder_level` | Inventory | SQL | product, category | No | Yes |
| 8 | `negative_stock` | Inventory | SQL | warehouse | No | Yes |
| 9 | `damaged_stock` | Inventory | SQL | date_range, product, warehouse | No | Yes |
| 10 | `batch_lot` | Inventory | SQL | product, status | No | Yes |
| 11 | `product_movement_analysis` | Inventory | SQL | product, category | No | Yes |
| 12 | `fast_moving` | Inventory | SQL | category | No | Yes |
| 13 | `slow_moving` | Inventory | SQL | category | No | Yes |
| 14 | `dead_stock` | Inventory | SQL | category | No | Yes |
| 15 | `warehouse_stock` | Inventory | SQL | warehouse | No | Yes |
| 16 | `stock_transfer` | Inventory | SQL | date_range, warehouse | No | Yes |
| 17 | `sales_report` | Sales | SQL | date_range, customer, rep, route, payment_method, status, vehicle, driver, partner, territory | No | Yes |
| 18 | `sales_summary` | Sales | SQL | date_range, rep, route | No | Yes |
| 19 | `sales_by_customer` | Sales | SQL | date_range, customer, rep, route, territory, group | No | Yes |
| 20 | `sales_by_item` | Sales | SQL | date_range, product, category, brand | No | Yes |
| 21 | `purchase_order_report` | Procurement | SQL | date_range, supplier, status | No | Yes |
| 22 | `grn_report` | Procurement | SQL | date_range, supplier, status | No | Yes |
| 23 | `customer_aging` | Customer | SQL | customer, rep, route, territory, group | No | Yes |
| 24 | `customer_statement` | Customer | SQL | date_range, customer | No | Yes |
| 25 | `supplier_statement` | Supplier | SQL | date_range, supplier | No | Yes |
| 26 | `supplier_aging` | Supplier | SQL | supplier | No | Yes |
| 27 | `budget_vs_actual` | Finance | SQL | None | No | Yes |
| 28 | `trial_balance` | Finance | SQL | date_range, tb_type | Controller Method | Yes |
| 29 | `profit_loss` | Finance | SQL | date_range | Controller Method | Yes |
| 30 | `general_ledger` | Finance | SQL | date_range | No | Yes |
| 31 | `balance_sheet` | Finance | Custom | None | Custom Render | Yes |
| 32 | `cash_flow` | Finance | Custom | None | Custom Render | Yes |
| 33 | `multi_period_comparison` | Finance | Custom | None | Custom Render | Yes |
| 34 | `credit_collection` | Collection | SQL | date_range, customer, rep, route, payment_method | No | Yes |
| 35 | `route_performance` | Route | SQL | date_range, route, rep, vehicle, driver | No | Yes |
| 36 | `monthly_kpi` | Management | SQL | None | No | Yes |

### Unregistered Reports (not in Registry but have Controller Methods)
| Report | Controller Method | View File |
|--------|-----------------|-----------|
| AR Aging | `ar_aging()` | `ar_aging.php` |
| FIFO Profit | `fifo_profit()` | `fifo_profit.php` |

---

## 🔒 Security Issues

| # | Issue | Severity | Location |
|---|-------|----------|----------|
| 1 | SQL Injection via sort_col parameter | **CRITICAL** | ReportEngine.php:1127 |
| 2 | No input validation for GET parameters | **HIGH** | ReportController.php:273-292 |
| 3 | No CSRF protection on export/print endpoints | **MEDIUM** | ReportController.php:348 |
| 4 | Excel/CSV formula injection in exports | **MEDIUM** | ReportController.php:480 |
| 5 | JSON/XML endpoints have no auth check beyond session | **MEDIUM** | ReportController.php:263 |
| 6 | No rate limiting on report data API | **LOW** | ReportController.php:263 |
| 7 | No CORS headers on API endpoints | **LOW** | ReportController.php:264 |

---

## 📈 Performance Issues

| # | Issue | Impact | Location |
|---|-------|--------|----------|
| 1 | Same SQL executed 3x per report (count, totals, data) | **HIGH** | ReportEngine.php:1088-1138 |
| 2 | 12+ DB queries per report hub page load | **MEDIUM** | Report.php:463-506 |
| 3 | Correlated subquery in customer statement | **HIGH** | Report.php:528 |
| 4 | LIMIT/OFFSET for pagination on large datasets | **MEDIUM** | ReportEngine.php:1130 |
| 5 | No query caching anywhere | **HIGH** | System-wide |
| 6 | 10,000 rows loaded into memory for exports | **MEDIUM** | ReportController.php:384 |
| 7 | HAVING clause preventing index usage | **LOW** | Report.php:36 |

---

## 🏗️ Code Quality Issues

| # | Issue | Location |
|---|-------|----------|
| 1 | 2,231 line view file (viewer.php) - impossible to maintain | viewer.php |
| 2 | 1,158 line service file mixing registry + query builder | ReportEngine.php |
| 3 | 1,051 line model file with 15+ public methods | Report.php |
| 4 | 806 line controller with 10+ public methods | ReportController.php |
| 5 | Filter extraction duplicated in 3 methods | ReportController.php |
| 6 | Financial logic duplicated in controller + engine | ReportController.php + ReportEngine.php |
| 7 | Inline CSS in all view files | viewer.php, index.php, print.php |
| 8 | Inline JavaScript with onclick handlers | viewer.php, index.php |
| 9 | Mixed PHP/HTML/JS in single files | All view files |
| 10 | No PSR-4 autoloading | System-wide |
| 11 | No type hints or strict types | System-wide |
| 12 | No unit/integration tests | System-wide |

---

## 🎯 Action Plan

### Immediate (Fix Now) - 1-2 Days
1. **Fix SQL injection vector** in `sort_col` parameter - use whitelist approach
2. **Add input validation** for all filter parameters in `fetch_data()`, `export()`, `print_report()`
3. **Add CSV formula injection protection** in export method
4. **Extract duplicate filter extraction** into a private method

### Short-term (1-2 Weeks)
5. **Refactor financial reports** - Remove duplication between controller methods and viewer special cases
6. **Split viewer.php** into separate CSS, JS, and template files
7. **Cache filter dropdown data** (customers, suppliers, products) - reduces DB queries by 12 per page load
8. **Fix grand totals calculation** - calculate in app code instead of running query 3x
9. **Register AR Aging & FIFO Profit** in ReportEngine registry so they appear in the hub

### Medium-term (1-2 Months)
10. **Implement export service classes** (CsvExporter, ExcelExporter, etc.)
11. **Add Redis caching** for report results and filter data
12. **Replace LIMIT/OFFSET** with keyset pagination
13. **Add role-based access control** to reports
14. **Implement report audit logging**
15. **Replace HAVING clauses** with proper WHERE conditions and add indexes

### Long-term (3-6 Months)
16. **Create dedicated Report API** (separate from the web controller)
17. **Add chart visualizations** (Chart.js, D3.js)
18. **Implement report scheduling and auto-emailing**
19. **Build custom report builder** UI
20. **Add report comparison** and side-by-side views
21. **Rewrite as a proper framework** module (Laravel/Symfony)

---

## 📋 Summary Statistics

| Metric | Count |
|--------|-------|
| Total registered reports | 36 |
| Report categories | 9 |
| Custom-rendered reports | 3 |
| Unregistered reports (orphaned) | 2 |
| Filter types supported | 16 |
| Export formats | 6 |
| Quick View entity types | 10 |
| Lines of code (reporting subsystem) | ~5,800 |
| Critical issues | 3 |
| High priority issues | 5 |
| Medium priority issues | 12 |
| Low priority/enhancements | 10 |

---

*Report generated by automated code analysis on July 5, 2026*