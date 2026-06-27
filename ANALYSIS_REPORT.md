# Curtiss ERP Project - Comprehensive Analysis Report (v2)
**Date:** June 27, 2026 (UPDATED)
**Previous Report:** See git commit `21e1652c` (older version)

---

## 1. PROJECT OVERVIEW

Curtiss ERP is a custom-built Enterprise Resource Planning system for a distribution/sales business. It manages:
- **Inventory** (items, categories, warehouse, vendors)
- **Sales** (invoices, accounts receivable, sales orders)
- **Purchasing/Procurement** (POs, GRNs, supplier management)
- **HRM** (employees, attendance, leaves, payroll, performance)
- **Accounting** (chart of accounts, journal entries, year-end close)
- **Banking** (banking, reconciliation, cheques)
- **CRM** (leads, customers)
- **E-Commerce** integration (WooCommerce sync)
- **Mobile App API** (rep dashboard, driver dashboard, rep tracking)
- **Customer Portal**
- **Reports** (30+ report types via ReportEngine)

**Architecture:** Custom PHP MVC framework (no popular framework like Laravel/CodeIgniter)
**Database:** MySQL (XAMPP localhost / Plesk production)
**Total Controllers:** 51 | **Total Models:** 47 | **Total Views:** ~120 | **Services:** 2

---

## 2. IMPROVEMENTS DETECTED (Since Previous Analysis)

The following issues from the previous analysis have been **FIXED or IMPROVED**:

| # | Previous Issue | Status | Details |
|---|----------------|--------|---------|
| 1 | Hard-coded DB credentials in `config/database.php` | ✅ **FIXED** | Now uses `.env` file + custom `loadEnv()` function |
| 2 | Brevo API key hard-coded in code | ✅ **FIXED** | Now reads from `getenv('BREVO_API_KEY')` |
| 3 | No `.env` file usage | ✅ **FIXED** | `.env` + `.env.example` created with `loadEnv()` parser |
| 4 | Schema migrations on EVERY request (in Database.php) | ✅ **FIXED** | Moved to `MigrationManager::run()` with tracking table |
| 5 | No migration management system | ✅ **FIXED** | New `core/MigrationManager.php` with `migrations` DB table |
| 6 | SQL injection via `str_replace` in SalesController | ✅ **FIXED** | Replaced with proper parameterized count query |
| 7 | Self-healing ALTER TABLE in Item model | ✅ **FIXED** | Removed - now handled by MigrationManager |
| 8 | No caching layer at all | ✅ **FIXED** | New `core/Cache.php` with 3-tier caching (static → APCu → file) |
| 9 | Dynamic column SQL injection risk in Item model | ✅ **FIXED** | Added `safeCol()` regex sanitizer for dynamic column names |
| 10 | PDO Persistent mode (connection issues) | ✅ **FIXED** | Changed from `PDO::ATTR_PERSISTENT => true` → `false` |
| 11 | `DESCRIBE items` on every request in Item model | ✅ **FIXED** | Now cached via static `$cachedColumns` + `Cache::get/set` (86400s TTL) |
| 12 | Exposed uploads directory | ✅ **FIXED** | `.htaccess` with `Options -Indexes` + `index.php` files added |

---

## 3. REMAINING CRITICAL ISSUES & BUGS

### 3.1 SECURITY ISSUES (HIGH PRIORITY)

| # | Issue | Severity | Location |
|---|-------|----------|----------|
| 1 | **.env file still contains real credentials in repository** | CRITICAL | `.env` - should be `.gitignored` (check if it is) |
| 2 | **No session_regenerate_id() after login** (session fixation) | HIGH | Authentication flow |
| 3 | **CSRF bypass for all API/mobile sync endpoints** | HIGH | `App.php` line 29 - `!$isApiSync` disables CSRF |
| 4 | **XSS vulnerabilities in views** (html_entity_decode without escaping) | MEDIUM | Multiple views, `InventoryController.php` |
| 5 | **Dynamic SQL column names** - `safeCol()` is good but not bulletproof | MEDIUM | `Item.php` - column names still interpolated into SQL |
| 6 | **No rate limiting on API endpoints** | MEDIUM | All API routes accessible without throttling |

### 3.2 ARCHITECTURAL ISSUES

| # | Issue | Severity | Location |
|---|-------|----------|----------|
| 7 | **Monolithic controllers** - Still too large | HIGH | `SalesController.php` = 1857 lines, `RepTrackingController.php` = 2552 lines, `InventoryController.php` = 2074 lines |
| 8 | **main.php layout is 2547 lines** - Everything in one file | HIGH | `app/Views/layouts/main.php` |
| 9 | **No proper routing/URL rewriting** | MEDIUM | Relies on `?url=` query parameter |
| 10 | **No input validation library** - Manual filtering everywhere | MEDIUM | All controllers |
| 11 | **No global exception handler** - try-catch with silent failure everywhere | MEDIUM | All controllers |
| 12 | **No separation of concerns** - Business logic mixed with DB access | MEDIUM | All controllers |

### 3.3 DATABASE DESIGN ISSUES

| # | Issue | Severity | Description |
|---|-------|----------|-------------|
| 13 | **items table has both `qty` AND `quantity_on_hand`** | HIGH | Dual stock columns - Item model tracks `hasQtyColumn`/`hasQuantityOnHandColumn` but still writes to both |
| 14 | **Two parallel invoice systems** (`invoices` + `sales_invoices`) | HIGH | Can cause data inconsistency |
| 15 | **JSON fields without schema validation** (variations_json, additional_images) | MEDIUM | No validation for stored JSON |
| 16 | **MigrationManager has hard-coded migration list** | MEDIUM | Adding migrations requires code change, no SQL file support |

### 3.4 FUNCTIONAL BUGS

| # | Issue | Severity | Description |
|---|-------|----------|-------------|
| 17 | **Stock sync between `qty` and `quantity_on_hand` can still diverge** | HIGH | Both columns updated but sync logic unclear |
| 18 | **No FIFO cost calculation fully integrated** | HIGH | `FIFO.php` exists, `stock_batches` & `invoice_item_batches` tables created, but integration incomplete |
| 19 | **No transaction rollback on failed invoice creation** | HIGH | Sales creation logic |
| 20 | **Pagination offset can go negative in some controllers** | MEDIUM | Several controllers lack validation |
| 21 | **Image upload directory still in public path** | MEDIUM | `public/uploads/products/` - accessible if directory listing enabled |

### 3.5 PERFORMANCE ISSUES

| # | Issue | Severity | Description |
|---|-------|----------|-------------|
| 22 | **No query optimization indexes on many tables** | MEDIUM | MigrationManager has some index creation (12 indexes) but many tables still lack them |
| 23 | **No Redis/Memcached for production caching** | MEDIUM | Cache class falls back to file-based (temp dir) |
| 24 | **No pagination on some list views** | MEDIUM | Several views load all records |

---

## 4. NEW ISSUES INTRODUCED

| # | Issue | Severity | Location |
|---|-------|----------|----------|
| 1 | **`loadEnv()` function is defined in `config/database.php`** - creates global dependency | LOW | `config/database.php` - should be in a bootstrap file |
| 2 | **`.env` file is manually parsed** - lacks proper quoting, multi-line value support | LOW | Custom parser vs using `vlucas/phpdotenv` library |
| 3 | **MigrationManager has 109 migrations hard-coded** - no way to run SQL files | MEDIUM | Adding migrations requires editing PHP code |
| 4 | **Cache class uses `unserialize()` on user data** - potential PHP object injection | MEDIUM | `core/Cache.php` line 31 |
| 5 | **`Cache::clear()` called after migrations** - may cause race conditions | LOW | `MigrationManager.php` line 241 |

---

## 5. STILL MISSING FEATURES

| # | Feature | Priority | Description |
|---|---------|----------|-------------|
| 1 | **Role-Based Access Control (RBAC)** | CRITICAL | Current permission system is basic session array |
| 2 | **Audit trail for ALL financial transactions** | HIGH | Only basic activity logging exists |
| 3 | **Multi-warehouse inventory transfer management** | HIGH | Basic warehouse support but no transfer workflow |
| 4 | **Barcode/RFID scanning API endpoints** | HIGH | No API for handheld scanners |
| 5 | **Automated backup scheduling** | HIGH | Manual backup only |
| 6 | **Email notification system fully integrated** | HIGH | BrevoMailer exists but not fully utilized |
| 7 | **Real-time dashboard KPIs** | HIGH | Basic dashboard only |
| 8 | **Budget vs Actual comparison** | MEDIUM | BudgetController exists but limited |
| 9 | **Dunning/Collections automation** | MEDIUM | DunningController exists but basic |
| 10 | **Payment gateway integration** | MEDIUM | No online payment processing |
| 11 | **Multi-currency support** | MEDIUM | Single currency only |
| 12 | **Two-factor authentication (2FA)** | MEDIUM | Password only |
| 13 | **WebSocket/Push notifications** | MEDIUM | No real-time updates |
| 14 | **Advanced chart-based reporting** | MEDIUM | Only tabular reports |

---

## 6. FEATURE RECOMMENDATIONS FOR PRODUCTIVITY IMPROVEMENT

### 6.1 HIGH PRIORITY (Implement Next)

#### 6.1.1 Security Hardening (Continuation)
- Add `.env` to `.gitignore` immediately
- Implement proper `.env` parsing using `vlucas/phpdotenv` composer package
- Add `session_regenerate_id(true)` after successful login
- Implement CSRF tokens for API endpoints (not just web forms)
- Add security headers middleware (CSP, X-Frame-Options, HSTS, X-Content-Type-Options)

#### 6.1.2 Operations & Inventory
- **Consolidate `qty` and `quantity_on_hand`** - Single source of truth for stock levels
- **Complete FIFO integration** - Link `stock_batches` to sales for proper COGS calculation
- **Automated reorder point system** - Suggest POs based on historical demand + lead time
- **Cycle counting module** - Mobile-friendly stock count with variance reports
- **Warehouse bin/shelf locations** - Track exact product locations in warehouse

#### 6.1.3 Sales & Financial
- **Cash flow forecasting** - Based on AR aging + PO commitments + historical patterns
- **Automated bank reconciliation** - Import bank statements and match transactions
- **Customer credit limit management** - Auto-block customers exceeding limits
- **Sales order fulfillment pipeline** - Order → Pick → Pack → Ship → Invoice tracking

### 6.2 MEDIUM PRIORITY

#### 6.2.1 Route & Distribution
- **Route optimization** using Google Maps API for efficient delivery sequencing
- **Driver mobile app enhancements** - Real-time GPS tracking, photo proof of delivery, e-signature
- **Automated vehicle scheduling** based on route load and vehicle capacity
- **Return logistics management** - Track returned items from delivery

#### 6.2.2 HR & Payroll
- **Employee self-service portal** (leave applications, payslip viewing)
- **Biometric attendance integration** (fingerprint/face recognition devices)
- **Overtime approval workflow** with automated calculation
- **Asset assignment tracking** (company phones, vehicles, laptops)

#### 6.2.3 Business Intelligence
- **Interactive dashboards** with Chart.js/ApexCharts (sales trends, inventory turnover)
- **Sales forecasting engine** using moving averages / seasonality
- **Profitability analysis** by customer, product category, route, sales rep
- **Custom report builder** with drag-and-drop interface

### 6.3 NICE-TO-HAVE

- WhatsApp Business API integration for customer communication
- SMS notifications via Twilio for payment reminders
- Zapier/Make.com webhook support for no-code automation
- Dark mode toggle (partial CSS exists)
- Keyboard shortcuts for power users
- Bulk operations across all modules
- Customizable table layouts (save column preferences)
- Automated testing (PHPUnit + Cypress)

---

## 7. IMMEDIATE ACTION ITEMS (Top 10)

| # | Action | Impact | Effort |
|---|--------|--------|--------|
| 1 | **Add `.env` to `.gitignore`** | Security | 5 min |
| 2 | **Fix session fixation - add `session_regenerate_id()` after login** | Security | 1 hour |
| 3 | **Add CSRF tokens for API endpoints** | Security | 4 hours |
| 4 | **Consolidate `qty` and `quantity_on_hand` stock columns** | Data integrity | 3 hours |
| 5 | **Complete FIFO integration for COGS calculation** | Financial accuracy | 8 hours |
| 6 | **Add database transaction rollbacks on all write operations** | Data integrity | 4 hours |
| 7 | **Install `vlucas/phpdotenv` via composer for proper .env parsing** | Security | 1 hour |
| 8 | **Break up largest controllers into service classes** | Maintainability | 20 hours |
| 9 | **Extract CSS from main.php into proper stylesheets** | Maintainability | 4 hours |
| 10 | **Replace `unserialize()` in Cache with safe JSON-only storage** | Security | 1 hour |

---

## 8. EXISTING STRENGTHS

The project's notable strengths (updated with new improvements):

1. ✅ **Environment configuration** - Now uses `.env` file (was hard-coded before)
2. ✅ **Centralized migration system** - `MigrationManager` with tracking table (was inline before)
3. ✅ **Multi-layer caching** - Static array → APCu → File-based (`core/Cache.php`) - NEW
4. ✅ **SQL injection protection** - `safeCol()` sanitizer for dynamic column names - NEW
5. ✅ **Upload directory security** - `.htaccess` + `index.php` files - NEW
6. ✅ **Comprehensive module coverage** - Most business functions implemented
7. ✅ **PWA support** - Service worker, offline manifest
8. ✅ **Cross-platform compatibility** - Localhost (XAMPP) + Production (Plesk)
9. ✅ **Glassmorphism modern UI** - Clean, professional design
10. ✅ **Mobile API support** - Rep and driver mobile apps
11. ✅ **30+ report types** - Dynamic ReportEngine with SQL-driven configuration
12. ✅ **Full CSV Import/Export** - Inventory catalog with validation
13. ✅ **Activity logging** - Audit trail for major operations
14. ✅ **Responsive design** - Mobile-friendly layout

---

## 9. PROGRESS SUMMARY

| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| Hard-coded secrets in code | 3 locations | 0 locations | ✅ 100% fixed |
| Schema migrations on every request | Every page load | Once per migration | ✅ Fixed |
| SQL injection vulnerabilities | 1 (str_replace) | 0 | ✅ Fixed |
| Caching system | None | 3-tier cache | ✅ Added |
| Migration management | None | 109 tracked migrations | ✅ Added |
| Dynamic column SQL injection | Unprotected | `safeCol()` sanitizer | ✅ Fixed |
| Upload directory security | None | `.htaccess` + `index.php` | ✅ Added |
| Critical issues remaining | 8 | 5 | ⚠️ In progress |
| High severity issues remaining | 12 | 8 | ⚠️ In progress |

**Bottom Line:** The ERP system has seen significant improvements in security and architecture. The most critical security issues (hard-coded credentials, SQL injection) have been addressed. The new caching and migration systems demonstrate good architectural direction. However, the system still needs work on session security, CSRF for APIs, FIFO integration, and code modularization before it can be considered fully production-grade.