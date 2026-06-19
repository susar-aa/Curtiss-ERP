# Curtiss ERP Rep App - Sync System Analysis Report

## Executive Summary

This report provides a comprehensive analysis of the synchronization system between the Curtiss ERP Rep App (Android) and the Curtiss ERP server (PHP). The system uses a **pull-push-verify** three-phase architecture with periodic background sync. While the system has robust foundations, there are **critical data loss risks, race conditions, architectural flaws, and missing features** that need to be addressed.

---

## 1. Architecture Overview

### Current Sync Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        ANDROID APP                               │
│                                                                  │
│  SyncWorker (Periodic - 15 min)                                  │
│    └─ executePull() only (server → mobile)                       │
│                                                                  │
│  SyncManager                                                    │
│    ├─ startPullSync()     → executePull()    (server → mobile)   │
│    ├─ startManualPushSync()                                      │
│    │   ├─ Phase 1: executePush()    (mobile → server)            │
│    │   └─ Phase 2: executeVerification() (verify UUIDs)          │
│    └─ SyncLock (ReentrantLock)                                   │
│                                                                  │
│  DatabaseHelper (SQLite - curtiss_offline.db)                    │
│    ├─ products, categories, customers, daily_routes              │
│    ├─ invoices, invoice_items, payments                          │
│    ├─ server_routes, payment_terms, credit_invoices              │
│    ├─ discount_rules, discount_rule_tiers                        │
│    └─ image_download_queue, sync_logs                            │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    SERVER (PHP)                                   │
│                                                                  │
│  RepDashboardController                                         │
│    ├─ sync_pull()   → GET  /rep/RepDashboard/sync_pull          │
│    ├─ sync_push()   → POST /rep/RepDashboard/sync_push          │
│    └─ sync_verify() → POST /rep/RepDashboard/sync_verify        │
│                                                                  │
│  Database (MySQL)                                                │
│    ├─ items, item_categories, customers                          │
│    ├─ invoices, invoice_items                                    │
│    ├─ rep_daily_routes, pending_collections                      │
│    ├─ users, employees, mca_areas                                │
│    ├─ payment_terms, chart_of_accounts                           │
│    └─ system_audit_trail                                         │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. CRITICAL BUGS & DATA LOSS RISKS

### 2.1 [CRITICAL] Pull Sync Overwrites Local Unsynced Data

**Location:** `SyncManager.java` - `executePull()` method

**Problem:** The pull sync unconditionally overwrites local data with server data. If a user creates an invoice offline and then a pull sync runs (either manually or via background SyncWorker), the pull will overwrite the local `daily_routes` and `invoices` tables with server data, potentially **deleting unsynced local invoices**.

**Evidence:**
```java
// Line ~ in executePull() - active route handling:
if (localIsSynced == 1) {
    db.update("daily_routes", rCv, "id = ?", ...);
}
// This only protects if local is_synced=1, but the route INSERT/UPDATE
// happens regardless of local unsynced data
```

**Impact:** A background SyncWorker running every 15 minutes can silently destroy locally created invoices that haven't been pushed yet.

**Fix:** Pull sync should NEVER overwrite records that have `is_synced = 0` (unsynced local changes). The condition `if (localIsSynced == 1)` is correct but only applied to route updates, not to the initial route creation check.

### 2.2 [CRITICAL] Invoice Items Sync Bug - All Items Applied to All Invoices

**Location:** `SyncManager.java` - `executePull()` method, lines ~350-370

**Problem:** When syncing invoice items for active route invoices, the code does:
```java
JSONArray items = response.getJSONArray("active_route_invoice_items");
db.execSQL("DELETE FROM invoice_items WHERE invoice_id = " + localInvId);
for (int k = 0; k < items.length(); k++) {
    JSONObject itemObj = items.getJSONObject(k);
    if (itemObj.getInt("invoice_id") == serverInvId) {
        // insert item
    }
}
```

This iterates through ALL invoice items from the server for EVERY invoice, but only inserts those matching the current `serverInvId`. However, the `DELETE` happens before the filtered insert. If there are multiple invoices, the first invoice's items will be correctly inserted, but the second invoice will first DELETE all its items (which is fine), then iterate through ALL items again and only insert matching ones. This is **inefficient but functionally correct**.

**However**, the real issue is that `active_route_invoice_items` is a flat array of ALL items for ALL invoices on the route. If the server returns items for invoices that are NOT in `active_route_invoices` array, those items will never be inserted. This is a **data integrity issue** - items can be lost.

### 2.3 [HIGH] No Transaction Rollback on Partial Push Failure

**Location:** `SyncManager.java` - `executePush()` method

**Problem:** The push sync processes customers, routes, invoices, and payments sequentially. If invoice processing fails after customers and routes were already created on the server, the customers and routes remain on the server but the local app marks them as failed. This creates **orphaned server records** and **inconsistent state**.

**Evidence:** The server-side `sync_push()` does NOT use database transactions. Each customer insert/update is committed immediately. If a subsequent invoice fails, the customers are already persisted.

### 2.4 [HIGH] SyncWorker Runs Pull-Only, Can Corrupt Local State

**Location:** `SyncWorker.java`

**Problem:** The background SyncWorker only runs `executePull()`, which downloads server data. If the user has unsynced local changes (invoices, routes, customers), the pull can overwrite or conflict with them. The SyncWorker runs every 15 minutes with no check for pending local uploads.

**Fix:** SyncWorker should check `hasPendingUploads()` before running pull, or skip pull entirely if there are unsynced local items.

### 2.5 [HIGH] SQL Injection Risk in executePull()

**Location:** `SyncManager.java` - multiple locations

**Problem:** String concatenation is used for SQL queries with user/API-controlled data:

```java
db.execSQL("DELETE FROM invoice_items WHERE invoice_id = " + localInvId);
db.rawQuery("SELECT id, is_synced FROM invoices WHERE server_id = ?", ...);
```

While `localInvId` is derived from the database (not direct user input), the `serverInvId` comes from the server response JSON. If the server is compromised or returns malicious data, this could lead to SQL injection.

**Severity:** Medium (requires server compromise), but should use parameterized queries everywhere.

### 2.6 [MEDIUM] No Delta Sync for Products/Customers on Pull

**Location:** `SyncManager.java` - `executePull()` and `RepDashboardController.php` - `sync_pull()`

**Problem:** The server supports delta sync via `last_sync_timestamp` parameter, but the Android app **never sends this parameter**. Every pull downloads the ENTIRE product catalog, all customers, all routes, all payment terms, etc. This is extremely wasteful for a 15-minute periodic sync.

**Evidence:** The server code checks for `last_sync`:
```php
$lastSync = isset($_GET['last_sync_timestamp']) ? trim($_GET['last_sync_timestamp']) : '';
```
But the Android app never includes this parameter:
```java
String urlString = baseUrl + "/rep/RepDashboard/sync_pull?api_sync=1&user_id=" + userId;
```

**Impact:** Massive bandwidth waste. Every 15 minutes, the entire product catalog (potentially thousands of items) is re-downloaded.

### 2.8 [MEDIUM] No Conflict Resolution Strategy

**Location:** Throughout sync system

**Problem:** When both the mobile app and server modify the same record (e.g., customer profile edited on both sides), there is **no conflict resolution**. The current strategy is "last write wins" with no timestamp comparison, no merge logic, and no user notification.

**Example Scenario:**
1. User edits customer address on mobile (offline)
2. Admin edits same customer address on server
3. Push sync uploads mobile version → overwrites server
4. Next pull sync downloads server version → overwrites mobile
5. **Result:** Admin's changes are lost, then mobile's changes are also lost

---

## 3. ARCHITECTURAL ISSUES

### 3.1 No Push Sync in Background SyncWorker

**Location:** `SyncWorker.java`

**Problem:** The periodic background worker only does pull (server → mobile). It never pushes local changes to the server. This means if a user creates invoices offline and forgets to manually push, the data remains on the device indefinitely.

**Fix:** SyncWorker should attempt push before pull, or at least check for pending uploads and trigger a push.

### 3.2 Single-Threaded Executor Bottleneck

**Location:** `SyncManager.java`

```java
this.executorService = Executors.newSingleThreadExecutor();
```

**Problem:** All sync operations (pull, push, verification) run on a single background thread. If a pull is in progress, a manual push request is queued and waits. Combined with the image download blocking issue (2.6), this creates significant delays.

### 3.3 No Sync Queue / Retry with Exponential Backoff

**Location:** `SyncManager.java`

**Problem:** While there's retry logic for HTTP connections (3 attempts with 2-second delay), there's no persistent retry queue for failed sync items. Failed items are marked with `sync_status = 4` (Failed) but there's no mechanism to automatically retry them later.

### 3.4 No Data Integrity Checks (Checksums/Hashes)

**Location:** Throughout

**Problem:** There are no checksums, hashes, or data integrity validations on synced data. If network corruption occurs, corrupted data is written directly to the database. JSON parsing errors are caught, but partial corruption within valid JSON is not detected.

### 3.5 No Sync Progress Persistence

**Location:** `SyncManager.java`

**Problem:** The `SyncListener` interface provides real-time progress callbacks, but progress is not persisted. If the app is killed during a long sync (e.g., downloading 500 product images), there's no way to resume from where it left off.

---

## 4. MISSING FEATURES

### 4.1 No Sync of Product Stock Updates in Real-Time

**Location:** Missing from pull sync

**Problem:** The pull sync downloads `quantity_on_hand` and `quantity_reserved` for products, but there's no mechanism to update stock levels in real-time. If a product is sold through another channel (e-commerce, walk-in customer), the rep's app won't know until the next manual pull.

### 4.2 No Sync of Voided/Cancelled Invoices

**Location:** Missing from pull sync

**Problem:** If an invoice is voided on the server (by admin), the mobile app has no way of knowing. The invoice remains in the local database as valid. The rep could attempt to collect payment for a voided invoice.

### 4.6 No Offline-Queued Data Expiry

**Location:** Missing

**Problem:** If a device remains offline for weeks, locally queued invoices and payments have no expiry mechanism. Stale data could be pushed to the server long after the business context has changed.

---

## 5. ERROR HANDLING ISSUES

### 5.1 Silent Failure on Category Sync Errors

**Location:** `SyncManager.java` - `executePull()`

```java
try {
    // sync categories
} catch (Exception e) {
    android.util.Log.e("SyncManager", "Error syncing categories: " + e.getMessage());
}
```

**Problem:** Category sync errors are logged but the overall pull sync continues as successful. If categories fail to sync, products may reference non-existent categories.

### 5.2 Silent Failure on Payment Table Operations

**Location:** `SyncManager.java` - `executePush()`

```java
try {
    Cursor payCursor = db.rawQuery("SELECT * FROM payments WHERE is_synced = 0", null);
    // ...
} catch (Exception e) {
    // Table might not exist, ignore
}
```

**Problem:** If the payments table doesn't exist (fresh install, schema mismatch), the error is silently ignored. Payments would be lost without any notification.

### 5.3 No User Notification for Partial Sync Failures

**Location:** `SyncManager.java` - `startManualPushSync()`

**Problem:** The final sync result is binary (success/failure). If 5 out of 10 invoices sync successfully, the user sees "Push verification incomplete. 5 items failed to sync." but has no way to know WHICH 5 failed or WHY.

### 5.4 Server-Side User ID Fallback is Dangerous

**Location:** `RepDashboardController.php` - `sync_push()`

```php
if (!$userRow) {
    $this->db->query("SELECT id FROM users WHERE role = 'rep' LIMIT 1");
    $repUser = $this->db->single();
    if ($repUser) {
        $userId = intval($repUser->id);
    } else {
        $this->db->query("SELECT id FROM users LIMIT 1");
        $firstUser = $this->db->single();
        $userId = $firstUser ? intval($firstUser->id) : 1;
    }
}
```

**Problem:** If a user ID doesn't exist on the server (e.g., after database reset), the server silently falls back to the first rep user or even the first user in the database. This means **invoices and routes could be attributed to the wrong rep**. This is a critical audit trail and commission calculation issue.

---

## 6. PERFORMANCE ISSUES

### 6.1 Full Data Download Every Pull

As noted in 2.7, every pull downloads the complete dataset. For a catalog of 5,000 products, 2,000 customers, and 500 credit invoices, this could be **multiple megabytes** every 15 minutes.

### 6.2 No Pagination on Server Endpoints

**Location:** `RepDashboardController.php`

**Problem:** The server returns ALL products, customers, and invoices in a single response. There's no pagination, no chunking, no streaming. For large datasets, this can cause:
- Out of memory errors on the server
- Socket timeouts on the mobile app
- ANR (Application Not Responding) on Android if run on main thread

### 6.3 No Compression

**Location:** Both client and server

**Problem:** The HTTP requests/responses are not compressed. Enabling gzip compression could reduce payload size by 70-80%.

### 6.4 Database Lock Contention

**Location:** `SyncManager.java`

**Problem:** The retry logic for SQLITE_BUSY uses exponential backoff, but there's no coordination between the sync thread and UI thread database access. A user browsing products while sync is running can cause SQLITE_BUSY errors.

---

## 7. RECOMMENDATIONS

### 7.1 Immediate Fixes (Critical)

| Priority | Issue | Fix |
|----------|-------|-----|
| P0 | Pull overwrites unsynced data | Add `is_synced = 0` check before any UPDATE/INSERT in pull |
| P0 | SyncWorker runs pull-only | Add push check before pull in SyncWorker |
| P0 | Server-side user ID fallback | Remove fallback; reject with error if user not found |
| P1 | No delta sync | Implement `last_sync_timestamp` tracking in SharedPreferences |
| P1 | Image download blocks sync | Move image download to separate executor, fire-and-forget |

### 7.2 Short-Term Improvements (High)

| Priority | Issue | Fix |
|----------|-------|-----|
| P1 | No conflict resolution | Implement last-modified timestamp comparison |
| P1 | No retry for failed items | Add background retry worker for sync_status = 4 items |
| P1 | No push in background | Add push phase to SyncWorker before pull |
| P2 | SQL injection risk | Use parameterized queries everywhere |
| P2 | No transaction on server push | Wrap entire sync_push in database transaction |

### 7.3 Long-Term Architectural Changes

| Priority | Change | Description |
|----------|--------|-------------|
| P2 | Implement proper delta sync | Track per-table last_sync timestamps, only send changed records |
| P2 | Add data checksums | Include MD5/SHA256 hash of each record for integrity verification |
| P2 | Implement sync queue | Persistent queue with retry, priority, and expiry |
| P3 | Add bidirectional conflict resolution | Three-way merge with user notification for conflicts |
| P3 | Implement paginated API | Chunk large responses (100 records per page) |
| P3 | Add gzip compression | Enable gzip on both client and server |
| P3 | Add sync analytics | Track sync success rates, durations, data volumes |

### 7.4 Specific Code Fixes

#### Fix 1: Protect Unsynced Data in Pull

In `SyncManager.java` `executePull()`, before any database write, check if the record has local unsynced changes:

```java
// Before updating a record in pull:
Cursor localCheck = db.rawQuery(
    "SELECT is_synced FROM daily_routes WHERE server_id = ?", 
    new String[]{String.valueOf(serverRouteId)}
);
if (localCheck.moveToFirst() && localCheck.getInt(0) == 0) {
    // Local has unsynced changes - SKIP server update
    localCheck.close();
    continue;
}
localCheck.close();
```

#### Fix 2: Implement Delta Sync

In `SyncManager.java`, track and send `last_sync_timestamp`:

```java
SharedPreferences prefs = context.getSharedPreferences("sync_state", Context.MODE_PRIVATE);
String lastSyncTimestamp = prefs.getString("last_pull_timestamp", "");
String urlString = baseUrl + "/rep/RepDashboard/sync_pull?api_sync=1&user_id=" + userId 
    + "&last_sync_timestamp=" + URLEncoder.encode(lastSyncTimestamp, "UTF-8");

// After successful pull, update timestamp:
prefs.edit().putString("last_pull_timestamp", 
    new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.US).format(new Date())).apply();
```

#### Fix 3: Add Push to SyncWorker

```java
public Result doWork() {
    // ... session check ...
    
    if (!syncManager.tryAcquireSyncLock()) {
        return Result.retry();
    }
    
    try {
        // Phase 1: Push local changes first
        if (syncManager.hasPendingUploads()) {
            boolean pushSuccess = syncManager.executePush(context, userId);
            if (pushSuccess) {
                boolean verifySuccess = syncManager.executeVerification(context, userId);
            }
        }
        
        // Phase 2: Pull server changes
        boolean pullSuccess = syncManager.executePull(context, userId);
        
        return pullSuccess ? Result.success() : Result.retry();
    } finally {
        syncManager.releaseSyncLock();
    }
}
```

#### Fix 4: Remove User ID Fallback on Server

```php
if (!$userRow) {
    echo json_encode([
        'success' => false, 
        'message' => 'User account not found on server. Please re-login.'
    ]);
    exit;
}
```

#### Fix 5: Separate Image Download from Sync

Create a dedicated `ImageSyncManager` that runs on its own executor:

```java
// In SyncManager.executePull(), remove the image download wait loop
// Instead, just queue downloads and return immediately:
ImageDownloadManager.getInstance(context).startQueueDownload(context);
// Don't wait for completion
```

---

## 8. DATA FLOW DIAGRAM (Recommended Architecture)

```
┌─────────────────────────────────────────────────────────────────────┐
│                      RECOMMENDED SYNC ARCHITECTURE                   │
└─────────────────────────────────────────────────────────────────────┘

Periodic Sync (15 min):
┌──────────────┐     ┌──────────────────┐     ┌──────────────────┐
│  SyncWorker   │────▶│  Phase 1: Push   │────▶│  Phase 2: Pull   │
│  (WorkManager)│     │  (local→server)  │     │  (server→local)  │
└──────────────┘     └──────────────────┘     └──────────────────┘
                            │                         │
                            ▼                         ▼
                     ┌──────────────────┐     ┌──────────────────┐
                     │  Retry Queue     │     │  Delta Sync      │
                     │  (failed items)  │     │  (timestamp)     │
                     └──────────────────┘     └──────────────────┘

Manual Push (User Initiated):
┌──────────────┐     ┌──────────────────┐     ┌──────────────────┐
│  User Tap     │────▶│  Phase 1: Push   │────▶│  Phase 2: Verify  │
│  "Sync Now"   │     │  (local→server)  │     │  (UUID check)    │
└──────────────┘     └──────────────────┘     └──────────────────┘
                            │                         │
                            ▼                         ▼
                     ┌──────────────────┐     ┌──────────────────┐
                     │  Conflict Check  │     │  Result Report   │
                     │  (timestamp)     │     │  (per-item)      │
                     └──────────────────┘     └──────────────────┘



## 9. CONCLUSION

The Curtiss ERP Rep App sync system has a solid foundation with:
- ✅ UUID-based idempotency for push operations
- ✅ Two-phase push with verification
- ✅ Retry logic for network failures
- ✅ SQLite WAL mode for concurrent access
- ✅ Self-healing database schema migrations

However, there are **critical issues** that must be addressed immediately:

1. **Pull sync can destroy unsynced local data** (P0 - Data Loss)
2. **Background sync never pushes local changes** (P0 - Data Loss)
3. **Server silently reassigns invoices to wrong users** (P0 - Data Integrity)
4. **No delta sync wastes bandwidth** (P1 - Performance)
5. **Image downloads block the entire sync** (P1 - UX)

The recommended order of implementation is:
1. Fix the critical data loss issues (P0)
2. Implement delta sync and background push (P1)
3. Add conflict resolution and retry queues (P2)
4. Architectural improvements (P3)

---

*Report generated on: June 19, 2026*
*Analysis by: Cline AI Assistant*