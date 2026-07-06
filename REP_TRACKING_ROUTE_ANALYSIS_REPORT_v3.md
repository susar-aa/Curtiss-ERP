### HIGH-4: Debug Logs Still Exposed in API Response
**Severity:** 🟠 HIGH  
**Location:** `api_save_return_stock()`

The `$debugLogs` array is returned in both success and error JSON responses, exposing:
- Internal file paths
- SQL query details
- Item IDs and quantities
- Transaction state information

**Fix:** Remove `$debugLogs` from JSON response. Log to `error_log()` instead.

### HIGH-5: No Variation Handling in Variance Adjustment
**Severity:** 🟠 HIGH  
**Location:** `RepVarianceService.php`

The variance adjustment service updates `items.qty` and `items.quantity_reserved` but does NOT update `item_variation_options`. If an item has variations, the variation-level stock is not adjusted.

**Fix:** Add `item_variation_options` updates alongside the `items` table updates.



## 3. 🟡 Medium Priority Issues

### MED-1: MasterRouteController Missing CSRF on api_detach_invoice()
**Location:** MasterRouteController.php line 762-784

The `api_detach_invoice()` method in MasterRouteController does NOT call `$this->validateCsrf()`, while the RepTrackingController version does. This endpoint can detach invoices from routes without CSRF protection.

### MED-2: MasterRouteController Missing Pagination
**Location:** MasterRouteController.php

`getUnifiedRoutes()` in MasterRouteController has no pagination — returns ALL routes in a single query. Will timeout with thousands of routes.

### MED-3: Cash Denominations Not Validated
**Location:** DriverRoute.php endTrip()

Cash denominations are stored as JSON in a TEXT column. No validation that the JSON is well-formed or contains expected fields. No ability to query or aggregate cash denomination data.

### MED-4: Debug Scripts Still in Production
**Location:** `/scratch/find_*.php`, `check_*.php`

These files expose internal file paths and source code analysis. Should be excluded from production deployment.

### MED-5: N+1 Query Pattern
Route list queries still use 5 correlated subqueries per route:
```sql
(SELECT COUNT(*) FROM invoices WHERE rep_route_id = r.id) as bill_count
(SELECT SUM(...) FROM invoices WHERE rep_route_id = r.id) as total_sales
(SELECT COUNT(*) FROM pending_collections WHERE route_id = r.id) as unfinalized_count
(SELECT COUNT(*) FROM delivery_picking_items WHERE delivery_id = d.id) as total_items
(SELECT COUNT(*) FROM delivery_picking_items WHERE delivery_id = d.id AND is_picked = 1) as picked_items
```
For 50 routes = 250+ additional queries beyond the main SELECT.

### MED-6: Full Data Transfer in Mobile Sync
`sync_pull()` returns ALL products, ALL customers, ALL unpaid invoices, ALL payment terms, ALL MCA areas with every sync — even with `lastSync` filter on customers, most data is unfiltered.

### MED-7: RepTracking.php getRouteBills() Still Has SQL Injection Risk
Line 91: `implode(',', $routeIds)` without `array_map('intval', ...)` — same as CRIT-2.

### MED-8: Variance Adjustment Uses Wrong Variable for Invoice ID (BUG)
**Location:** RepVarianceService.php line 207

```php
$db->bind(':id', $invoice->id);
```

But `$invoice` is not defined in this scope — it should be `$invId` (the loop variable from line 173). This is a **bug** — the invoice ID used in the UPDATE is undefined/wrong, which will cause a PHP notice and potentially update the wrong record.

### MED-9: Picking Population Logic Still Duplicated 3+ Times
The logic to populate `delivery_picking_items` from invoice items is duplicated in:
1. `api_get_route_variances()` (lines 699-733)
2. `api_update_route_status()` (lines 1137-1171)
3. `api_get_route_variances()` in MasterRouteController

### MED-10: Exact-Match Variance Requirement Blocks Progress
**Location:** RepVarianceService.php lines 23-31

The validation requires EXACT match between adjusted quantities and final loaded stock (tolerance 0.01). If variance audit is triggered without adjustments, it blocks progress. No way to skip or force-approve small variances.

---

