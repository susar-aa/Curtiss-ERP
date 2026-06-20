# Curtiss ERP Reporting Engine — Comprehensive Analysis Report

**Date:** 2026-06-20  
**Scope:** `app/Views/reports/` (21 files), `app/Controllers/ReportController.php`, `app/Services/ReportEngine.php`  
**Total Files Analyzed:** 23 files (21 views + 1 controller + 1 service)

---

## Table of Contents
1. [Critical Bugs & Errors](#1-critical-bugs--errors)
2. [Security Vulnerabilities](#2-security-vulnerabilities)
3. [Code Quality Issues](#3-code-quality-issues)
4. [Performance Issues](#4-performance-issues)
5. [UX/UI Issues](#5-uxui-issues)
6. [Architecture & Design Issues](#6-architecture--design-issues)
7. [Productivity Improvements](#7-productivity-improvements)
8. [Summary & Priority Matrix](#8-summary--priority-matrix)

---

## 1. Critical Bugs & Errors

### 1.1 `print.php` — Hardcoded Mock Data in Production Template
**File:** `app/Views/reports/print.php` (lines ~280-310)  
**Severity:** 🔴 CRITICAL  
**Issue:** The print template contains hardcoded mock/placeholder data that is ALWAYS rendered regardless of actual database data:
```php
// This block runs for EVERY row when reportKey is 'general_ledger' or 'stock_ledger'
if (($reportKey === 'general_ledger' || $reportKey === 'stock_ledger') && $index % 3 === 0):
```
This injects fake "Contra AccCd", "NATIONAL TRADING CO - BRANCH", "VAT Payable (15%)" entries into real financial reports. This will produce **incorrect financial statements** that mix real data with fake data.

**Fix:** Remove this entire mock data injection block. If sub-ledger nesting is needed, implement it from actual database relations.

### 1.2 `ReportEngine.php` — Grand Totals Calculated on Paginated Data Only
**File:** `app/Services/ReportEngine.php` (lines 564-574)  
**Severity:** 🔴 CRITICAL  
**Issue:** Grand totals are calculated from only the current page's rows, not the entire dataset:
```php
foreach ($rows as $r) {  // $rows is only the current page (e.g., 50 rows)
    $sum += floatval($r->$colKey ?? 0);
}
```
This means if a report has 10,000 records and the user is on page 2, the "Grand Total" shown is only the sum of 50 rows, not the full 10,000. This is **financially misleading**.

**Fix:** Calculate grand totals from a separate aggregate SQL query on the full filtered dataset, not from the paginated subset.

### 1.3 `ReportEngine.php` — Simulation Mode Silently Returns Fake Data
**File:** `app/Services/ReportEngine.php` (lines 581-588, 595-671)  
**Severity:** 🔴 CRITICAL  
**Issue:** When a SQL query fails (table doesn't exist, column mismatch, etc.), the engine silently falls back to `generateSimulationData()` which returns completely fabricated data. The `simulation` flag is returned in the JSON but the viewer only shows it as a yellow warning banner. Users could easily mistake simulated data for real data and make business decisions based on it.

**Fix:** 
- Log the actual database error for debugging
- Show a prominent RED error banner that clearly states "NO REAL DATA AVAILABLE"
- Optionally disable simulation mode in production via a config flag
- Never silently fall back to fake data

### 1.4 `ReportEngine.php` — SQL Injection via Dynamic Sort Column
**File:** `app/Services/ReportEngine.php` (lines 551-553)  
**Severity:** 🔴 CRITICAL  
**Issue:** The sort column is directly interpolated into SQL without sanitization:
```php
$baseSql .= " ORDER BY " . $sortCol . " " . ($sortDir === 'DESC' ? 'DESC' : 'ASC');
```
While `$sortCol` is checked against `$metadata['columns']`, if a column key contains special characters or if the metadata is ever compromised, this is an SQL injection vector.

**Fix:** Use a whitelist approach — map column keys to actual safe column names/aliases, or validate against a strict regex pattern.

### 1.5 `ReportEngine.php` — Date Filter Applies Only to `invoice_date`
**File:** `app/Services/ReportEngine.php` (lines 522-528)  
**Severity:** 🟠 HIGH  
**Issue:** Date range filters are hardcoded to only apply when `invoice_date` exists in the SQL:
```php
if (isset($filters['start_date']) && !empty($filters['start_date']) && strpos($baseSql, 'invoice_date') !== false) {
```
This means reports like `general_ledger` (which uses `entry_date`), `stock_movement` (which uses `created_at`), and `grn_report` (which uses `grn_date`) will **ignore date filters entirely** because their SQL doesn't contain the string `invoice_date`.

**Fix:** Make date filtering configurable per report in the metadata (e.g., `'date_column' => 'entry_date'`).

### 1.6 `viewer.php` — CSS Animation Keyframe Syntax Error
**File:** `app/Views/reports/viewer.php` (line ~280)  
**Severity:** 🟠 HIGH  
**Issue:** The spinner keyframe has a typo that will cause the CSS animation to fail:
```css
@keyframes spin {
    0% { transform: translate(-50%, -50__) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}
```
`-50__` should be `-50%`. The spinner will not display correctly.

### 1.7 `collections.php` — Missing HTML Document Structure
**File:** `app/Views/reports/collections.php`  
**Severity:** 🟠 HIGH  
**Issue:** This file includes `_header.php` and `_footer.php` but the `_header.php` opens `<html>`, `<head>`, and `<body>` tags. However, `collections.php` itself has no wrapping structure — it's just raw HTML fragments. If `_header.php` or `_footer.php` ever change, this file could break or produce invalid HTML.

**Fix:** Ensure consistent structure. The file should work as a standalone view fragment, but currently depends entirely on the header/footer contract.

### 1.8 `print.php` — Auto-Print on Load Destroys UX
**File:** `app/Views/reports/print.php` (lines ~370-374)  
**Severity:** 🟠 HIGH  
**Issue:** The print template automatically triggers `window.print()` on load:
```javascript
window.onload = function() {
    window.print();
};
```
This means the print dialog opens immediately when the page loads, before the user has a chance to see the report on screen. If the user wants to preview before printing, they cannot.

**Fix:** Add a "Print" button instead of auto-triggering, or add a brief delay with a "Preparing print..." message.

### 1.9 `fifo_profit.php` — Hardcoded "↑ 100%" Trend Indicator
**File:** `app/Views/reports/fifo_profit.php` (line ~200)  
**Severity:** 🟡 MEDIUM  
**Issue:** The trend indicator is hardcoded:
```html
<span class="trend-up">↑ 100%</span> true inventory margins
```
This always shows "↑ 100%" regardless of actual performance. This is misleading.

**Fix:** Calculate actual trend percentage from data or remove the static indicator.

### 1.10 `ReportEngine.php` — Cron Frequency Thresholds Are Incorrect
**File:** `app/Services/ReportEngine.php` (lines 733-738)  
**Severity:** 🟡 MEDIUM  
**Issue:** The time thresholds for scheduled reports are incorrect:
- Daily: 86,000 seconds = ~23.9 hours (should be 86,400)
- Weekly: 600,000 seconds = ~6.9 days (should be 604,800)
- Monthly: 2,500,000 seconds = ~28.9 days (should be ~2,592,000 for 30 days)

These will cause reports to run slightly early, potentially missing data.

**Fix:** Use exact constants: `86400`, `604800`, `2592000`.

---

## 2. Security Vulnerabilities

### 2.1 No CSRF Protection on AJAX Endpoints
**Files:** `ReportController.php` — `save_view()`, `save_schedule()`  
**Severity:** 🟠 HIGH  
**Issue:** The POST endpoints for saving views and schedules have no CSRF token validation. An attacker could trick an authenticated user into saving malicious filter configurations or scheduling unwanted email reports.

**Fix:** Implement CSRF token validation on all state-changing POST endpoints.

### 2.2 No Rate Limiting on Export Endpoints
**File:** `ReportController.php` — `export()` method  
**Severity:** 🟡 MEDIUM  
**Issue:** The export endpoint can be called repeatedly to generate large datasets (up to 10,000 rows per call) with no rate limiting. This could be abused for data scraping or DoS attacks.

**Fix:** Add rate limiting per user/session on export endpoints.

### 2.3 No Input Validation on Email Recipient
**File:** `ReportController.php` — `save_schedule()` (line 169)  
**Severity:** 🟡 MEDIUM  
**Issue:** The email recipient is not validated before being stored and used for sending reports. Malformed or malicious email addresses could be stored.

**Fix:** Validate email format before saving.

### 2.4 XSS in Print Template Filter Labels
**File:** `app/Views/reports/print.php` (multiple locations)  
**Severity:** 🟡 MEDIUM  
**Issue:** While most output uses `htmlspecialchars()`, some filter label values are output directly. If a malicious filter value is passed via URL, it could execute XSS.

**Fix:** Ensure ALL user-influenced output uses `htmlspecialchars()` consistently.

---

## 3. Code Quality Issues

### 3.1 Massive Inconsistency: Two Rendering Systems
**Severity:** 🟠 HIGH  
**Issue:** There are TWO completely different rendering systems:
1. **Legacy standalone views** (`ar_aging.php`, `balance_sheet.php`, `cash_flow.php`, `profit_loss.php`, `trial_balance.php`) — Each is a complete HTML document with inline CSS, no shared layout.
2. **New viewer system** (`viewer.php`, `print.php`, `_header.php`, `_footer.php`) — Uses a metadata-driven dynamic table with shared components.

The legacy views duplicate massive amounts of code (CSS, HTML structure, print buttons, etc.) and are NOT accessible through the viewer system. They have their own separate data-fetching logic.

**Fix:** Migrate all legacy views to use the metadata-driven viewer system, or remove them if the viewer covers their functionality.

### 3.2 Massive CSS Duplication
**Severity:** 🟠 HIGH  
**Issue:** Every standalone view has its own copy of nearly identical CSS:
- `ar_aging.php` — 30+ lines of inline CSS
- `balance_sheet.php` — 25+ lines of inline CSS
- `cash_flow.php` — 25+ lines of inline CSS
- `profit_loss.php` — 25+ lines of inline CSS
- `trial_balance.php` — 25+ lines of inline CSS
- `fifo_profit.php` — 200+ lines of inline CSS
- `_header.php` — 50+ lines of inline CSS
- `viewer.php` — 200+ lines of inline CSS
- `print.php` — 100+ lines of inline CSS

**Total:** ~700+ lines of duplicated CSS across 9+ files.

**Fix:** Extract shared CSS into a dedicated stylesheet file (`public/css/reports.css`).

### 3.3 `_header.php` and `_footer.php` — Tight Coupling
**Severity:** 🟡 MEDIUM  
**Issue:** The header opens `<html>`, `<head>`, `<body>`, and `<div class="report-wrap">` but the footer closes them. Any view that includes both must not have its own HTML structure. This creates a fragile contract:
- `collections.php` — includes both, no own structure ✓
- `general_ledger.php` — includes both, no own structure ✓
- `inventory_valuation.php` — includes both, no own structure ✓
- `profit_loss_period.php` — includes both, no own structure ✓
- `purchases.php` — includes both, no own structure ✓
- `sales_by_customer.php` — includes both, no own structure ✓
- `sales_by_product.php` — includes both, no own structure ✓
- `sales_by_rep.php` — includes both, no own structure ✓
- `sales_summary.php` — includes both, no own structure ✓
- `tax_summary.php` — includes both, no own structure ✓

But the legacy standalone views (`ar_aging.php`, `balance_sheet.php`, etc.) do NOT use the header/footer and have their own complete HTML. This inconsistency is confusing.

**Fix:** Either make ALL views use the header/footer system, or eliminate it entirely in favor of a proper layout system.

### 3.4 `print.php` — Overly Complex with Mock Data Generation
**Severity:** 🟡 MEDIUM  
**Issue:** The print template is 370+ lines and contains:
- Mock data generation logic (lines 280-310)
- Complex conditional formatting
- Duplicate of the viewer's table rendering logic

This should be a simple template that just renders data, not generates fake data.

**Fix:** Remove all mock data generation. The print template should only render what's passed to it.

### 3.5 `fifo_profit.php` — Extremely Large Single File
**Severity:** 🟡 MEDIUM  
**Issue:** This file is ~400 lines with 200+ lines of CSS, complex HTML structure, and inline JavaScript. It's a standalone report that doesn't use the viewer system, the header/footer, or any shared components.

**Fix:** Refactor to use the viewer system or at minimum extract CSS and JS to separate files.

### 3.6 Magic Numbers and Hardcoded Values
**Severity:** 🟢 LOW  
**Issue:** Multiple hardcoded values throughout:
- `ReportEngine.php` line 597: `$totalRows = 45;` — hardcoded simulation size
- `ReportEngine.php` line 600: `for ($i = 1; $i <= 15; $i++)` — hardcoded rows per page
- `ReportEngine.php` line 661: `$grandTotals[$colKey] = $sum * 3;` — arbitrary multiplier
- `ReportController.php` line 206: `$this->engine->fetchData($reportKey, $filters, 1, 10000);` — hardcoded limit

**Fix:** Define these as constants or configuration values.

### 3.7 No Error Handling in Views
**Severity:** 🟢 LOW  
**Issue:** Most views assume `$data` contains all required keys and access them without null checks:
```php
<?= htmlspecialchars($data['company']->company_name) ?>
```
If `$data['company']` is null or missing, this will throw an error.

**Fix:** Add null coalescing operators: `$data['company']->company_name ?? ''`

---

## 4. Performance Issues

### 4.1 No Query Caching
**Severity:** 🟠 HIGH  
**Issue:** Every report request hits the database with a fresh query. There is no caching layer for:
- Frequently accessed reports (e.g., dashboard KPIs)
- Filter dropdown data (customers, suppliers, products)
- Report metadata (registry is rebuilt on every request)

**Fix:** Implement query result caching using:
- File-based cache for filter dropdowns (cache for 5-10 minutes)
- Database query cache for report data
- OPcache for the registry metadata

### 4.2 N+1 Query Pattern in Filter Label Resolution
**File:** `ReportController.php` (lines 376-416)  
**Severity:** 🟡 MEDIUM  
**Issue:** Each filter label is resolved with a separate database query:
```php
$db->query("SELECT name FROM customers WHERE id = :id");
$db->query("SELECT name FROM vendors WHERE id = :id");
// ... up to 7 separate queries
```
If a report has all filters active, this adds 7 extra queries per request.

**Fix:** Batch resolve all filter labels in a single query, or pass the label data from the viewer page.

### 4.3 No Database Indexing Strategy
**Severity:** 🟡 MEDIUM  
**Issue:** The SQL queries in `ReportEngine.php` use `WHERE 1=1` as a base pattern and add filters dynamically. Without proper database indexes on columns like `invoice_date`, `customer_id`, `vendor_id`, `status`, these queries will perform full table scans on large datasets.

**Fix:** Ensure database indexes exist on all filtered and sorted columns.

### 4.4 Large Export Limit
**File:** `ReportController.php` (line 206)  
**Severity:** 🟡 MEDIUM  
**Issue:** Exports fetch up to 10,000 rows in a single query without chunking. For large datasets, this could cause memory exhaustion.

**Fix:** Implement chunked/streamed exports using database cursors or paginated fetching.

### 4.5 No Lazy Loading for Filter Dropdowns
**Severity:** 🟢 LOW  
**Issue:** All filter dropdowns (customers, suppliers, products, etc.) are loaded on every viewer page load, even if the report doesn't use them. For large customer databases (10,000+), this adds significant overhead.

**Fix:** Only load filter options that are relevant to the current report, and use AJAX lazy-loading for large dropdowns.

---

## 5. UX/UI Issues

### 5.1 Inconsistent Visual Design
**Severity:** 🟡 MEDIUM  
**Issue:** The reporting system has THREE distinct visual styles:
1. **Legacy style** (`ar_aging.php`, `balance_sheet.php`, etc.) — Light gray background, white card, simple tables
2. **Header/footer style** (`_header.php` + view files) — Similar to legacy but with KPI cards and filter bars
3. **Dark modern style** (`fifo_profit.php`) — Dark theme with gradients, glow effects, modern cards
4. **Viewer style** (`viewer.php`) — Clean white cards with blue accents

Users switching between reports will experience visual whiplash.

**Fix:** Unify all reports under a single design system.

### 5.2 No Loading States for AJAX Operations
**File:** `viewer.php`  
**Severity:** 🟡 MEDIUM  
**Issue:** While there is a spinner for initial data load, there are no loading indicators for:
- Saving views
- Scheduling reports
- Exporting data
- Changing pages (the spinner exists but the overlay opacity change is barely noticeable)

**Fix:** Add loading states for all async operations.

### 5.3 No Error Feedback for Failed Operations
**File:** `viewer.php`  
**Severity:** 🟡 MEDIUM  
**Issue:** When save operations fail, the only feedback is a generic `alert()`:
```javascript
alert('Failed to save layout view.');
```
There's no user-friendly error message, no retry option, and no indication of what went wrong.

**Fix:** Implement toast notifications with meaningful error messages.

### 5.4 Print Button in Legacy Views Has No Print Styles
**Severity:** 🟢 LOW  
**Issue:** The legacy standalone views have print buttons but the print CSS is minimal. The `@media print` blocks only hide backgrounds and shadows but don't optimize for paper (e.g., no page breaks, no headers on each page).

**Fix:** Enhance print stylesheets with proper page break rules, repeating table headers, and footer information.

### 5.5 No Mobile Responsiveness
**Severity:** 🟢 LOW  
**Issue:** Most report views have no responsive design. Tables will overflow on mobile screens. The viewer has `overflow-x: auto` on the table container, but the filter sidebar takes 320px which is too wide for mobile.

**Fix:** Implement responsive breakpoints and collapsible sidebar for mobile.

---

## 6. Architecture & Design Issues

### 6.1 Mixed Concerns in ReportEngine
**Severity:** 🟠 HIGH  
**Issue:** `ReportEngine.php` handles:
- Report metadata registry (static)
- Data fetching and SQL generation
- Simulation/mock data generation
- Saved views CRUD
- Scheduled reports CRUD
- Email sending via cron

This violates the Single Responsibility Principle. The class has 787 lines and 6 distinct responsibilities.

**Fix:** Split into:
- `ReportRegistry.php` — metadata definitions
- `ReportDataFetcher.php` — SQL generation and data fetching
- `ReportSimulator.php` — simulation data generation
- `SavedViewManager.php` — saved views CRUD
- `ScheduledReportManager.php` — scheduling and email dispatch

### 6.2 No Repository/Data Layer Abstraction
**Severity:** 🟡 MEDIUM  
**Issue:** SQL queries are embedded directly in the registry metadata as strings. There's no separation between data access and business logic. Changing a table schema requires updating SQL in the registry.

**Fix:** Implement a repository pattern where each report type has a dedicated data access class.

### 6.3 No Unit Test Coverage
**Severity:** 🟡 MEDIUM  
**Issue:** There are no unit tests for:
- Report data fetching logic
- Filter application
- Grand total calculations
- Simulation mode
- Export formatting

**Fix:** Add PHPUnit tests for the ReportEngine, especially for the financial calculation methods.

### 6.4 No API Versioning
**Severity:** 🟢 LOW  
**Issue:** The AJAX endpoints (`fetch_data`, `save_view`, `save_schedule`, `export`) have no versioning. Future changes could break existing integrations.

**Fix:** Add URL prefix versioning (e.g., `/api/v1/report/fetch_data`).

### 6.5 No Event Logging/Audit Trail
**Severity:** 🟢 LOW  
**Issue:** There's no logging for:
- Report views
- Data exports
- Scheduled report dispatches
- Simulation mode activations (when real data fails)

**Fix:** Implement an audit log for all report-related actions.

---

## 7. Productivity Improvements

### 7.1 Implement Report Caching
**Priority:** HIGH  
**Description:** Add a caching layer for frequently accessed reports. Cache invalidation should trigger when underlying data changes (new invoice, payment, etc.).

**Implementation:**
```php
// Cache key based on report + filters + page
$cacheKey = md5($reportKey . json_encode($filters) . $page . $limit);
$cached = apcu_fetch($cacheKey);
if ($cached) return $cached;
// ... fetch from DB ...
apcu_store($cacheKey, $result, 300); // 5 minute cache
```

### 7.2 Add Report Comparison Feature
**Priority:** MEDIUM  
**Description:** Allow users to compare two periods side-by-side (e.g., current month vs previous month, or year-over-year). This is especially valuable for P&L, sales, and inventory reports.

**Implementation:** Add a "Compare with" date range selector that runs the same report twice and displays results in adjacent columns.

### 7.3 Add Chart/Visualization Integration
**Priority:** MEDIUM  
**Description:** Integrate a charting library (Chart.js, ApexCharts) to visualize report data. Key reports that would benefit:
- Sales Summary → Bar chart of daily sales
- Sales by Product → Pie chart of revenue by product
- Inventory Valuation → Bar chart of stock value by category
- Cash Flow → Waterfall chart

**Implementation:** Add a "Chart" toggle button in the viewer toolbar that renders a chart alongside the table.

### 7.4 Add Drill-Down Navigation
**Priority:** MEDIUM  
**Description:** The viewer already has `drilldown` column metadata but it's only partially implemented. Full drill-down would allow:
- Clicking an invoice number → opens invoice detail
- Clicking a customer name → opens customer profile
- Clicking a product → opens product detail
- Clicking a total → opens the underlying transactions

**Implementation:** Complete the drilldown links and add a "back to report" navigation.

### 7.5 Add Report Scheduling UI Improvements
**Priority:** MEDIUM  
**Description:** The current scheduling UI is basic. Improvements:
- Add a calendar picker for specific dates
- Allow multiple email recipients
- Add email preview before saving
- Add "Send test email" button
- Show schedule history (last sent, next send)

### 7.6 Add Export Format Improvements
**Priority:** MEDIUM  
**Description:** Current exports are basic. Improvements:
- **Excel:** Use PhpSpreadsheet instead of HTML table hack for proper .xlsx files with formatting
- **PDF:** Use DomPDF or TCPDF for proper PDF generation instead of browser print
- **CSV:** Handle encoding properly (UTF-8 BOM for Excel compatibility)
- **All formats:** Include report metadata (title, date range, filters) in the export

### 7.7 Add Report Favorites/Pinning
**Priority:** LOW  
**Description:** Allow users to pin frequently used reports to a "Favorites" section at the top of the reports hub.

### 7.8 Add Bulk Export / Report Batch
**Priority:** LOW  
**Description:** Allow users to select multiple reports and export them all at once as a batch (e.g., "End of Month Package" that exports P&L, Balance Sheet, and Sales Summary together).

### 7.9 Add Report Annotations
**Priority:** LOW  
**Description:** Allow users to add notes/annotations to report data (e.g., "This spike in sales was due to the promotional campaign"). Annotations should persist and be visible when the report is re-run.

### 7.10 Add Data Refresh Indicator
**Priority:** LOW  
**Description:** Show when the report data was last refreshed from the database. For cached reports, show the cache age and a "Refresh" button.

---

## 8. Summary & Priority Matrix

| Priority | Count | Key Items |
|----------|-------|-----------|
| 🔴 CRITICAL | 4 | Mock data in print.php, paginated grand totals, silent simulation mode, SQL injection via sort |
| 🟠 HIGH | 10 | Date filter bug, CSS animation typo, missing HTML structure, auto-print, two rendering systems, CSS duplication, no query caching, mixed concerns, no CSRF, inconsistent date thresholds |
| 🟡 MEDIUM | 15 | Hardcoded trend, export rate limiting, email validation, XSS, tight coupling, complex print.php, large fifo_profit.php, N+1 queries, no indexes, large exports, inconsistent design, no loading states, no error feedback, no repository layer, no tests |
| 🟢 LOW | 6 | Magic numbers, no null checks, print styles, mobile responsiveness, API versioning, audit trail |

### Recommended Immediate Actions (Next Sprint):
1. **Fix print.php** — Remove mock data injection (Critical)
2. **Fix grand totals** — Calculate from full dataset, not current page (Critical)
3. **Fix simulation mode** — Add prominent warning, disable in production (Critical)
4. **Fix SQL injection** — Whitelist sort columns (Critical)
5. **Fix date filters** — Make date column configurable per report (High)
6. **Fix CSS animation** — Correct the typo (High)
7. **Add query caching** — Start with filter dropdowns (High)

### Recommended Short-Term Improvements (Next 2 Sprints):
1. Consolidate the two rendering systems into one
2. Extract shared CSS into a single stylesheet
3. Refactor ReportEngine into smaller, focused classes
4. Add CSRF protection to POST endpoints
5. Add proper error handling and user feedback
6. Implement chart visualizations for key reports

### Recommended Long-Term Improvements (Next Quarter):
1. Add comprehensive unit test coverage
2. Implement proper PDF export with DomPDF
3. Add report comparison feature
4. Implement full drill-down navigation
5. Add audit logging for all report actions
6. Implement report annotations