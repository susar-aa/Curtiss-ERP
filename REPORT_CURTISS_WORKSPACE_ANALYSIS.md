# CURTISS WORKSPACE - COMPREHENSIVE ANALYSIS REPORT
## Super Admin System & Delivery/Logistics Modularization Strategy

**Date:** July 1, 2026  
**Author:** System Analysis Report  
**Purpose:** Analyze the Curtiss ecosystem for modular deployment without delivery/logistics functionality

---

## TABLE OF CONTENTS

1. [Executive Summary](#1-executive-summary)
2. [Workspace Overview](#2-workspace-overview)
3. [Complete Module Inventory](#3-complete-module-inventory)
4. [Delivery & Logistics Components - The "Delivery Module"](#4-delivery--logistics-components---the-delivery-module)
5. [Existing RBAC & Permission System](#5-existing-rbac--permission-system)
6. [Super Admin Implementation Strategy](#6-super-admin-implementation-strategy)
7. [Modular Hiding Implementation Plan](#7-modular-hiding-implementation-plan)
8. [Database Schema Impact](#8-database-schema-impact)
9. [Mobile App Considerations](#9-mobile-app-considerations)
10. [File-Level Impact Analysis](#10-file-level-impact-analysis)
11. [Implementation Roadmap](#11-implementation-roadmap)
12. [Risk Assessment](#12-risk-assessment)
13. [Conclusion](#13-conclusion)

---

## 1. EXECUTIVE SUMMARY

The Curtiss Workspace is a comprehensive **Enterprise Resource Planning (ERP)** ecosystem comprising:

| Component | Technology | Purpose |
|---|---|---|
| **Curtiss-ERP** | PHP (Custom MVC) | Main web-based ERP system |
| **Curtiss E Commerce** | PHP Web App | E-commerce frontend/API |
| **Curtiss Portal** | PWA (HTML/JS/CSS) | Customer/Staff portal |
| **Curtiss ERP Driver App** | Android (Java/Kotlin) | Driver mobile application |
| **Curtiss ERP Rep App** | Android (Java/Kotlin) | Sales Rep mobile application |

The system already has a **Role-Based Access Control (RBAC)** system with module-level permissions, but it is **not complete** for fully hiding all delivery/logistics functionality. The "Admin" role currently bypasses all permission checks, meaning there's no way to selectively hide delivery features from certain admin users.

### Key Finding: The system CAN be adapted for a company WITHOUT delivery operations, but requires:

1. A **Super Admin configuration layer** (feature flags/module toggles)
2. **Enhanced RBAC** to hide navigation, backend controllers, and API endpoints
3. **Database-level isolation** (optional schema segmentation)
4. **Conditional code compilation/deployment** for mobile apps

---

## 2. WORKSPACE OVERVIEW

### 2.1 Directory Structure

```
CURTISS/
├── Curtiss-ERP/                    # MAIN ERP WEB APPLICATION (PHP)
│   ├── app/
│   │   ├── Controllers/            # 52 Controller files
│   │   ├── Models/                 # 47 Model files  
│   │   ├── Views/                  # 85+ View files
│   │   ├── Services/               # 2 Service files
│   │   └── Core/                   # 1 Helper file
│   ├── core/                       # MVC Framework Core
│   │   ├── App.php                 # Application bootstrap/router
│   │   ├── Controller.php          # Base controller
│   │   ├── Database.php            # Database abstraction
│   │   ├── RbacService.php         # RBAC implementation
│   │   ├── RbacInterface.php       # RBAC interface
│   │   ├── Cache.php               # Caching system
│   │   └── MigrationManager.php    # DB migration manager
│   ├── config/                     # Configuration
│   └── public/                     # Public entry point & assets
│
├── Curtiss E Commerce/             # E-COMMERCE FRONTEND
│   ├── app/Controllers/
│   ├── config/
│   └── core/
│
├── Curtiss Portal/                 # PWA PORTAL
│   ├── index.html
│   ├── app.js
│   ├── app.css
│   └── sw.js (Service Worker)
│
├── Curtiss ERP Driver App/         # ANDROID DRIVER APP
│   └── app/src/main/java/com/example/curtissdriver/
│       ├── MainActivity.java
│       ├── SyncManager.java
│       ├── DeliveryActivity.java
│       ├── CheckoutActivity.java
│       ├── CatalogActivity.java
│       ├── LoginActivity.java
│       ├── VehicleStockActivity.java
│       ├── DatabaseHelper.java
│       └── ImageDownloadManager.java
│
└── Curtiss ERP Rep App/            # ANDROID REP APP
    └── app/src/main/java/com/example/curtiss/
        ├── MainActivity.java
        ├── DashboardActivity.java
        ├── BillingActivity.java
        ├── CustomerActivity.java
        ├── CatalogActivity.java
        ├── LoginActivity.java
        ├── SyncManager.java
        ├── SyncWorker.java
        ├── LocationHelper.java
        ├── HistoryActivity.java
        ├── StatsActivity.java
        └── DatabaseHelper.java
```

---

## 3. COMPLETE MODULE INVENTORY

### 3.1 ERP Modules by Functional Area

| Area | Module | Permission Key | Delivery-Related? |
|---|---|---|---|
| **Sales & CRM** | Leads/CRM | `crm` | No |
| | Customers | `customer` | No |
| | Estimates | `estimate` | No |
| | Sales Orders | `sales` (shared) | No |
| | Invoices/AR | `sales` (shared) | No |
| | Credit Notes | `creditnote` | No |
| | Dunning | `dunning` | No |
| | Discount Feed | `discount` | No |
| | **Master Route Control** | **`reptracking`** | **YES** |
| | **Territory & Routing** | **`territory`** | **YES** |
| | **Deleted Invoices** | `sales` | No |
| **Supply Chain** | Products/Inventory | `inventory` | No |
| | Suppliers | `supplier` | No |
| | Categories | `category` | No |
| | Variations | `variation` | No |
| | Warehouses | `warehouse` | No |
| | Purchase Orders | `purchase` | No |
| | GRN | `grn` | No |
| | Supplier Returns | `supplier_return` | No |
| | Expenses/AP | `expenses` | No |
| **Operations** | HRM/Employees | `hrm` | No |
| | **Vehicle Management** | **`vehicle`** | **YES** |
| | **Cheque Management** | **`cheque`** | **YES** |
| | Payroll | `hrm` (shared) | No |
| | Leave | `hrm` (shared) | No |
| | Attendance | `hrm` (shared) | No |
| | Performance | `hrm` (shared) | No |
| | Projects/Tasks | `project` | No |
| **Accounting** | Chart of Accounts | `accounting` | No |
| | Journal Entries | `accounting` | No |
| | Banking | `accounting` | No |
| | Customer Payments | `customerpayment` | No |
| | Supplier Payments | `supplierpayment` | No |
| | Fixed Assets | `asset` | No |
| **Reports** | Reports Hub | `report` | No |
| | Budgets | `budget` | No |
| **E-Commerce** | Wholesaler Requests | `ecommerce` | No |
| | Retail Customers | `ecommerce` | No |
| **Admin** | Settings | `settings` | No |
| | Users & Roles | `user` | No |
| | Tax Rates | `tax` | No |
| | Payment Terms | `paymentterm` | No |
| | Audit Trail | `audit` | No |
| | Backup | `settings` (shared) | No |
| | System Health | `settings` (shared) | No |

### 3.2 Delivery-Specific Controllers (Must Hide)

| Controller | File | Purpose |
|---|---|---|
| `DeliveryController.php` | `/app/Controllers/` | Create/arrange/finalize deliveries |
| `DriverDashboardController.php` | `/app/Controllers/` | Driver app backend API & web dashboard |
| `RepTrackingController.php` | `/app/Controllers/` | Master route control panel (2732 lines) |
| `VehicleController.php` | `/app/Controllers/` | Fleet/vehicle management |
| `MasterRouteController.php` | `/app/Controllers/` | Route creation & management |
| `RepDashboardController.php` | `/app/Controllers/` | Rep dashboard (may be needed sans delivery) |
| `PickingController.php` | `/app/Controllers/` | Picking/loading operations |
| `ChequeController.php` | `/app/Controllers/` | Cheque management (may be needed) |

### 3.3 Delivery-Specific Models (Must Hide)

| Model | File | Purpose |
|---|---|---|
| `Delivery.php` | `/app/Models/` | Delivery CRUD, finalization, balancing (745 lines) |
| `DriverRoute.php` | `/app/Models/` | Driver route tracking |
| `DriverInvoice.php` | `/app/Models/` | Driver-specific invoice operations |
| `RepTracking.php` | `/app/Models/` | Rep tracking data |
| `RepCatalog.php` | `/app/Models/` | Rep visual catalog |
| `Vehicle.php` | `/app/Models/` | Vehicle/fleet data |

### 3.4 Delivery-Specific Views (Must Hide)

| View Directory | Views | Purpose |
|---|---|---|
| `/app/Views/deliveries/` | `index.php`, `balancing_report.php`, `spreadsheet.php` | Delivery web interfaces |
| `/app/Views/rep-tracking/` | `index.php`, `print_loading.php`, `print_route_invoices.php` | Route control panel |
| `/app/Views/vehicles/` | `index.php` | Vehicle management |

### 3.5 Cross-Cutting Dependencies

| Module | Depends On | If Delivery Hidden |
|---|---|---|
| **Invoices/AR** | `rep_route_id` field | Remove route references from invoice forms |
| **Customer Payments** | `rep_route_id` field | Remove route from payment forms |
| **Sales (Billing)** | Route selection | Remove route selection in billing creator |
| **Dashboard** | Route stats | Remove route widgets |
| **Stock Ledger** | "Delivery Finalized" references | Remove delivery references |

---

## 4. DELIVERY & LOGISTICS COMPONENTS - THE "DELIVERY MODULE"

### 4.1 Complete Delivery Workflow

```
┌─────────────────────────────────────────────────────────┐
│                 DELIVERY WORKFLOW                         │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Rep creates route ──► Route becomes Active               │
│       │                                                   │
│       ▼                                                   │
│  Pending GL (accounting verification)                     │
│       │                                                   │
│       ▼                                                   │
│  Adjustments (invoice corrections)                        │
│       │                                                   │
│       ▼                                                   │
│  Loading (warehouse picks items)                          │
│       │                                                   │
│       ▼                                                   │
│  Delivery Arranged (vehicle+driver assigned)              │
│       │                                                   │
│       ▼                                                   │
│  Driver App Sync (mobile app)                             │
│  ├── Accept Delivery                                      │
│  ├── Start Trip (odometer)                                │
│  ├── Deliver to Shops                                     │
│  ├── Collect Payments (Cash/Cheque/Bank)                  │
│  └── End Trip (odometer, cash denoms)                     │
│       │                                                   │
│       ▼                                                   │
│  Variance Adjustment (warehouse checks returns)            │
│       │                                                   │
│       ▼                                                   │
│  Finalizing (admin reviews & posts)                       │
│  ├── Stock Deductions (FIFO)                              │
│  ├── Journal Entries (Double-entry)                       │
│  └── Payment Clearance                                    │
│       │                                                   │
│       ▼                                                   │
│  Completed/Finalized                                      │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### 4.2 Database Tables in the Delivery Module

| Table | Purpose | If Hiding |
|---|---|---|
| `deliveries` | Delivery arrangements | Can remain but unused |
| `rep_daily_routes` | Daily route management | Can remain but unused |
| `route_bindings` | Route grouping/binding | Can remain but unused |
| `delivery_picking_items` | Picking/loading items | Can remain but unused |
| `product_substitutions` | Product replacements during loading | Can remain but unused |
| `driver_routes` | Driver-assigned routes | Can remain but unused |
| `vehicles` | Fleet vehicles | Can remain but unused |
| `customer_payments` | (has `rep_route_id` FK) | Needs schema adjustment |
| `invoices` | (has `rep_route_id` FK) | Needs schema adjustment |

### 4.3 Mobile App Functionality

#### Curtiss ERP Driver App
- **SyncManager.java** - Bi-directional sync with server API
- **DeliveryActivity.java** - View and manage deliveries
- **CheckoutActivity.java** - Collect payments from customers
- **CatalogActivity.java** - Visual product catalog
- **VehicleStockActivity.java** - View vehicle stock levels
- **LoginActivity.java** - Authentication
- **DatabaseHelper.java** - Local SQLite database

#### Curtiss ERP Rep App
- **SyncManager.java** / SyncWorker.java - Background sync
- **BillingActivity.java** - Create invoices/sales
- **CustomerActivity.java** - Customer management
- **CatalogActivity.java** - Product catalog
- **DashboardActivity.java** - Sales stats & KPIs
- **LocationHelper.java** - GPS tracking
- **HistoryActivity.java** - Past transactions
- **StatsActivity.java** - Performance stats

---

## 5. EXISTING RBAC & PERMISSION SYSTEM

### 5.1 Current Architecture

```php
// core/RbacService.php
class RbacService implements RbacInterface {
    public function check($userId, $module, $action = 'view') {
        // Admin ROLE ALWAYS BYPASSES all constraints
        if ($this->hasRole($userId, 'Admin')) {
            return true;  // <-- PROBLEM: Admin can't be restricted
        }
        // ... checks role_permissions table
    }
}
```

### 5.2 Current Permission Resolution

```
User ──► User_Roles ──► Role_Permissions ──► Module+Action
                └── (Fallback to User_Permissions table)
```

**Permission Modules currently defined:**
- `crm`, `customer`, `estimate`, `sales`, `creditnote`, `dunning`, `discount`
- `reptracking`, `territory` ← Delivery-related
- `inventory`, `supplier`, `category`, `variation`, `warehouse`
- `purchase`, `grn`, `supplier_return`, `expenses`
- `hrm`, `project`, `vehicle`, `cheque` ← Delivery-related
- `accounting`, `customerpayment`, `supplierpayment`, `asset`
- `report`, `budget`, `ecommerce`
- `settings`, `user`, `tax`, `paymentterm`, `audit`

### 5.3 Gaps in Current RBAC

1. **Admin Override**: The Admin role bypasses ALL checks - cannot restrict Admin users
2. **No Module Toggles**: There is no "feature flag" system to disable modules entirely
3. **No UI Hiding**: Navigation is permission-gated, but direct URL access is only gated at controller level (not all controllers check permissions)
4. **No API Gate**: Mobile app sync endpoints (`api_sync_pull`, `api_sync_push`) only check `user_id`, not module permissions

---

## 6. SUPER ADMIN IMPLEMENTATION STRATEGY

### 6.1 Proposed Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                   SUPER ADMIN SYSTEM                          │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  settings table (key-value store)                             │
│  ┌──────────────────────────────────────┐                    │
│  │ feature_delivery_module = 0/1        │                    │
│  │ feature_vehicle_module = 0/1         │                    │
│  │ feature_rep_tracking_module = 0/1    │                    │
│  │ feature_mobile_driver_app = 0/1      │                    │
│  │ feature_cheque_module = 0/1          │                    │
│  │ feature_ecommerce_module = 0/1       │                    │
│  └──────────────────────────────────────┘                    │
│                                                               │
│  RbacService Enhancement:                                    │
│  ┌──────────────────────────────────────┐                    │
│  │ Admin role = Super Admin (config)    │                    │
│  │ Module check = Feature flag + Perm   │                    │
│  │ New: hasModuleAccess($module)        │                    │
│  └──────────────────────────────────────┘                    │
│                                                               │
│  new core/FeatureManager.php                                 │
│  ┌──────────────────────────────────────┐                    │
│  │ isEnabled('delivery_module')         │                    │
│  │ disableModule('delivery_module')     │                    │
│  │ getEnabledModules()                  │                    │
│  └──────────────────────────────────────┘                    │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### 6.2 Super Admin vs Regular Admin

| Capability | Regular Admin | Super Admin |
|---|---|---|
| Access all modules | ✓ | ✓ |
| Configure feature toggles | ✗ | ✓ |
| Enable/disable delivery module | ✗ | ✓ |
| Manage user roles & permissions | ✓ | ✓ |
| Override feature flags | ✗ | ✓ |
| Access system settings | ✓ | ✓ |
| View audit logs | ✓ | ✓ |
| Deploy mobile app updates | ✗ | ✓ |

### 6.3 Settings Table Schema (New Row)

```sql
-- Add to settings table or create new module_configs table
CREATE TABLE IF NOT EXISTS module_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_key VARCHAR(50) UNIQUE NOT NULL,
    is_enabled TINYINT(1) DEFAULT 1,
    config_data JSON DEFAULT NULL,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed with default configurations
INSERT INTO module_configs (module_key, is_enabled) VALUES
('delivery_module', 1),
('vehicle_module', 1),
('rep_tracking_module', 1),
('mobile_driver_app', 1),
('mobile_rep_app', 1),
('cheque_module', 1),
('ecommerce_module', 1);
```

---

## 7. MODULAR HIDING IMPLEMENTATION PLAN

### 7.1 Four-Layer Hiding Strategy

```
Layer 1: Feature Flags (settings table)
    ↓
Layer 2: Navigation Hiding (UI menu gating)
    ↓
Layer 3: Controller Gating (URL access prevention)
    ↓
Layer 4: Business Logic Bypass (Data layer)
```

### 7.2 Layer 1: Feature Manager Service

Create a new core service:

```php
// core/FeatureManager.php
class FeatureManager {
    private static $cache = [];
    
    public static function isEnabled($moduleKey) {
        // 1. Check if super admin override
        if (self::isSuperAdmin() && isset($_SESSION['admin_override'])) {
            return $_SESSION['admin_override'][$moduleKey] ?? true;
        }
        
        // 2. Check cache
        if (isset(self::$cache[$moduleKey])) {
            return self::$cache[$moduleKey];
        }
        
        // 3. Query database
        $db = new Database();
        $db->query("SELECT is_enabled FROM module_configs WHERE module_key = :key");
        $db->bind(':key', $moduleKey);
        $row = $db->single();
        
        $result = $row ? (bool)$row->is_enabled : true; // Default enabled
        self::$cache[$moduleKey] = $result;
        return $result;
    }
    
    public static function getEnabledModules() {
        $db = new Database();
        $db->query("SELECT module_key, is_enabled FROM module_configs");
        return $db->resultSet();
    }
    
    public static function setEnabled($moduleKey, $enabled, $userId) {
        $db = new Database();
        $db->query("INSERT INTO module_configs (module_key, is_enabled, updated_by, updated_at) 
                    VALUES (:key, :enabled, :uid, NOW())
                    ON DUPLICATE KEY UPDATE is_enabled = :enabled2, updated_by = :uid2, updated_at = NOW()");
        $db->bind(':key', $moduleKey);
        $db->bind(':enabled', $enabled ? 1 : 0);
        $db->bind(':uid', $userId);
        $db->bind(':enabled2', $enabled ? 1 : 0);
        $db->bind(':uid2', $userId);
        $db->execute();
        self::$cache = []; // Clear cache
    }
}
```

### 7.3 Layer 2: Navigation Hiding

Enhance the `hasPermission()` function in `main.php`:

```php
function hasPermission($module, $action = 'view') {
    // Super Admin check (can see everything if override enabled)
    if (isset($_SESSION['is_super_admin']) && $_SESSION['is_super_admin']) {
        return true;
    }
    
    // Check if module is enabled via feature flags
    $moduleFeatureMap = [
        'reptracking' => 'rep_tracking_module',
        'delivery' => 'delivery_module',
        'vehicle' => 'vehicle_module',
        'cheque' => 'cheque_module',
        'ecommerce' => 'ecommerce_module',
    ];
    
    if (isset($moduleFeatureMap[$module])) {
        $featureKey = $moduleFeatureMap[$module];
        if (!FeatureManager::isEnabled($featureKey)) {
            return false; // Module is disabled
        }
    }
    
    // Original RBAC check
    if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') {
        return true;
    }
    // ... rest of existing logic
}
```

### 7.4 Layer 3: Controller Gating

Enhance the Controller base class to check feature flags:

```php
// core/Controller.php - Add to base class
protected function requireModuleEnabled($moduleKey) {
    if (!FeatureManager::isEnabled($moduleKey)) {
        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Module is not enabled']);
            exit;
        }
        $this->view('auth/access_denied', [
            'message' => 'This module is not available in your system configuration.'
        ]);
        exit;
    }
}
```

Then add at the top of each delivery-related controller:

```php
// DeliveryController constructor
public function __construct() {
    $this->requireModuleEnabled('delivery_module');
    // ... existing checks
}
```

### 7.5 Layer 4: Business Logic Bypass

For modules that have cross-cutting dependencies (like invoices having `rep_route_id`):

1. **Invoice creation**: Remove route selection dropdown when delivery module is disabled
2. **Customer payments**: Remove route assignment fields
3. **Dashboard**: Remove route status widgets and delivery alerts
4. **Reports**: Hide delivery-related report options

---

## 8. DATABASE SCHEMA IMPACT

### 8.1 Optional Schema Cleanup (If Hiding Delivery)

The following can remain in schema but become **unused columns**:

```sql
-- These fields exist but become irrelevant
ALTER TABLE invoices MODIFY rep_route_id INT NULL;  -- Already nullable
ALTER TABLE customer_payments MODIFY rep_route_id INT NULL;  -- Already nullable

-- These tables exist but remain dormant:
-- deliveries, rep_daily_routes, route_bindings, delivery_picking_items,
-- product_substitutions, driver_routes, vehicles
```

### 8.2 Recommended Cleanup Approach

**Option A: Soft Hiding (Recommended for initial deployment)**
- Keep all tables intact
- Feature flags prevent UI/API access
- Data remains for potential future enablement
- Zero risk of data loss

**Option B: Hard Separation**  
- Move delivery tables to a separate database or schema
- Requires significant migration effort
- Higher risk but cleaner separation

**Option C: Conditional Code Paths**
- Use feature flags to skip delivery-related operations in shared code
- E.g., invoice creation skips `rep_route_id` assignment
- Most maintainable approach

---

## 9. MOBILE APP CONSIDERATIONS

### 9.1 Curtiss ERP Driver App (Complete Hide)

If delivery is disabled, the **Driver App is entirely unnecessary**. Strategy:
- Remove from Google Play Store / app distribution
- Remove APK download links from web settings page
- The server-side `api_sync_pull` and `api_sync_push` endpoints will return "Module disabled" when gated

Files that become irrelevant:
```
Curtiss ERP Driver App/
├── DeliveryActivity.java
├── CheckoutActivity.java
├── VehicleStockActivity.java
├── SyncManager.java (partially)
└── MainActivity.java (functionality reduced)
```

### 9.2 Curtiss ERP Rep App (Partial Impact)

The Rep App has **non-delivery features** (billing, customer management, catalog) but also references route/delivery data. Strategy:
- Remove route-related screens
- Remove "collect payments" delivery-specific workflows
- Keep billing, catalog, customer management features
- The sync manager should skip delivery-related sync payloads

### 9.3 API Endpoints to Hide

| Endpoint | Controller | Mobile App |
|---|---|---|
| `/DriverDashboard/api_sync_pull` | DriverDashboardController | Driver App |
| `/DriverDashboard/api_sync_push` | DriverDashboardController | Driver App |
| `/DriverDashboard/*` | DriverDashboardController | Driver Web |
| `/Delivery/*` | DeliveryController | Admin Web |
| `/RepTracking/*` | RepTrackingController | Admin Web |
| `/RepDashboard/*` | RepDashboardController | Rep App |

---

## 10. FILE-LEVEL IMPACT ANALYSIS

### 10.1 Files to Modify (Not Delete - For Soft Hiding)

| File | Change Required |
|---|---|
| `core/App.php` | Add FeatureManager initialization |
| `core/Controller.php` | Add `requireModuleEnabled()` method |
| `core/RbacService.php` | Add feature flag check to `check()` method |
| `public/index.php` | Load FeatureManager on bootstrap |
| `app/Views/layouts/main.php` | Enhance `hasPermission()` to check feature flags |
| `app/Controllers/AuthController.php` | Store feature flags in session on login |

### 10.2 Files to Gate (Add Feature Check in Constructor)

| File | Feature Key |
|---|---|
| `app/Controllers/DeliveryController.php` | `delivery_module` |
| `app/Controllers/DriverDashboardController.php` | `delivery_module` |
| `app/Controllers/RepTrackingController.php` | `rep_tracking_module` |
| `app/Controllers/VehicleController.php` | `vehicle_module` |
| `app/Controllers/MasterRouteController.php` | `rep_tracking_module` |
| `app/Controllers/PickingController.php` | `delivery_module` |
| `app/Controllers/ChequeController.php` | `cheque_module` |
| `app/Controllers/RepDashboardController.php` | `rep_tracking_module` |

### 10.3 Files with Conditional Logic Changes

| File | Change |
|---|---|
| `app/Controllers/SalesController.php` | Conditionally skip route data in invoice forms |
| `app/Controllers/DashboardController.php` | Remove delivery/route widgets if disabled |
| `app/Controllers/CustomerPaymentController.php` | Hide route assignment if disabled |
| `app/Models/Delivery.php` | Entire model unused when disabled |
| `app/Views/sales/index.php` | Hide rep_route_id references |
| `app/Views/dashboard/index.php` | Hide delivery status widgets |
| `app/Views/reports/index.php` | Hide delivery-specific reports |

### 10.4 Files Unchanged (Core System)

These files remain **completely untouched**:
- All Accounting controllers/models/views
- Inventory management (except route references)
- Purchasing system
- HRM/Payroll system
- Settings/Users/Roles
- Reports (except delivery-specific)
- E-commerce module

---

## 11. IMPLEMENTATION ROADMAP

### Phase 1: Foundation (2-3 days)

```
Week 1:
├── Day 1: Create module_configs table & FeatureManager service
│   ├── [ ] Create SQL migration for module_configs table
│   ├── [ ] Create core/FeatureManager.php
│   ├── [ ] Add FeatureManager to App.php bootstrap
│   └── [ ] Test: FeatureManager::isEnabled() works
│
├── Day 2: Enhance RBAC with feature flags
│   ├── [ ] Modify RbacService::check() to include feature check
│   ├── [ ] Enhance hasPermission() in main.php layout
│   ├── [ ] Add requireModuleEnabled() to base Controller
│   └── [ ] Test: Navigation hides correctly for non-Admin users
│
└── Day 3: Super Admin interface
    ├── [ ] Create admin/settings view for module toggles
    ├── [ ] Add SettingsController method for module config
    ├── [ ] Implement super admin flag in users table
    └── [ ] Test: Super Admin can toggle modules
```

### Phase 2: Controller Gating (1-2 days)

```
Week 2:
├── Day 1: Gate delivery controllers
│   ├── [ ] Add feature checks to DeliveryController
│   ├── [ ] Add feature checks to DriverDashboardController
│   ├── [ ] Add feature checks to RepTrackingController
│   ├── [ ] Add feature checks to VehicleController
│   ├── [ ] Add feature checks to MasterRouteController
│   ├── [ ] Add feature checks to PickingController
│   └── [ ] Add feature checks to ChequeController
│   └── [ ] Test: Direct URL access blocked for disabled modules
│
└── Day 2: Gate API endpoints
    ├── [ ] Gate /driver/* API endpoints
    ├── [ ] Gate sync endpoints (api_sync_pull/push)
    ├── [ ] Test: Mobile apps receive "Module disabled" response
    └── [ ] Test: All existing functionality works for enabled modules
```

### Phase 3: UI/UX Hiding (2-3 days)

```
Week 3:
├── Day 1: Navigation cleanup
│   ├── [ ] Main menu hides delivery items when disabled
│   ├── [ ] Quick search hides delivery routes
│   ├── [ ] Dashboard widgets conditional on delivery module
│   └── [ ] Test: No delivery references in UI
│
├── Day 2: Invoice/payment forms cleanup
│   ├── [ ] Invoice creator hides route selection
│   ├── [ ] Customer payment form hides route assignment
│   ├── [ ] Reports hide delivery-specific options
│   └── [ ] Test: Clean forms without delivery fields
│
└── Day 3: Cross-reference cleanup
    ├── [ ] Search across all views for delivery-related text
    ├── [ ] Remove/hide route references in customer forms
    ├── [ ] Update stock ledger views
    └── [ ] Final UI audit
```

### Phase 4: Mobile App Updates (2-3 days per app)

```
Week 4:
├── Day 1-2: Rep App updates
│   ├── [ ] Remove delivery-specific screens from navigation
│   ├── [ ] Update SyncManager to skip delivery payloads
│   ├── [ ] Update DashboardActivity to hide route stats
│   ├── [ ] Build & test APK
│   └── [ ] Deploy update
│
└── Day 3: Driver App removal (if applicable)
    ├── [ ] Remove APK download links from web settings
    ├── [ ] Update release/version management
    └── [ ] Remove from app store listing
```

### Phase 5: Testing & Deployment (2-3 days)

```
Week 5:
├── Day 1: Comprehensive testing
│   ├── [ ] Test with delivery module ENABLED (regression)
│   ├── [ ] Test with delivery module DISABLED
│   ├── [ ] Test mixed permissions (some users see, some don't)
│   ├── [ ] Test API access for disabled modules
│   └── [ ] Test mobile app sync with disabled modules
│
├── Day 2: Security audit
│   ├── [ ] Verify no direct URL bypass possible
│   ├── [ ] Verify no API bypass possible
│   ├── [ ] Verify no database injection via disabled features
│   └── [ ] Verify session/permission caching works correctly
│
└── Day 3: Deployment
    ├── [ ] Deploy database migrations
    ├── [ ] Deploy backend code changes
    ├── [ ] Deploy mobile app updates
    └── [ ] Final verification on production
```

---

## 12. RISK ASSESSMENT

### 12.1 Risks & Mitigations

| Risk | Severity | Mitigation |
|---|---|---|
| **Data loss** if tables are dropped | HIGH | Use soft hiding (keep tables, disable UI only) |
| **Invoice creation breaks** without route context | MEDIUM | Make `rep_route_id` fully optional, not required |
| **Performance impact** from feature flag DB queries | LOW | Cache feature flags in session/APCu |
| **Mobile app sync failures** if server rejects | MEDIUM | Return graceful "module disabled" messages |
| **Rep app functionality reduced** | MEDIUM | Keep core billing/catalog features, hide only delivery |
| **Existing reports break** if no delivery data | LOW | Remove delivery-specific reports from menus only |
| **User confusion** if features disappear | LOW | Show clear "not available" messages |
| **Session cache stale** after admin toggles feature | LOW | Clear session flags on login; allow force-refresh |

### 12.2 Rollback Strategy

1. **Feature flags**: Simply re-enable the module via Super Admin settings
2. **Code changes**: Revert specific commits if needed
3. **Database**: No destructive migrations; all data preserved
4. **Mobile apps**: Previous APK versions remain available for sideloading

---

## 13. CONCLUSION

### 13.1 Feasibility Assessment

| Aspect | Rating | Notes |
|---|---|---|
| **Technical Feasibility** | ✅ HIGH | RBAC already exists; feature flags are straightforward |
| **Effort Required** | 🟡 MEDIUM | ~15 days for complete implementation |
| **Risk Level** | 🟢 LOW | Soft hiding approach preserves all data |
| **Maintainability** | ✅ HIGH | Feature flags are easy to manage |
| **Scalability** | ✅ HIGH | Can add more modules later |

### 13.2 Recommended Approach

**The recommended strategy is a "Hybrid Soft Hiding" approach:**

1. **Keep all database tables intact** - No schema changes that could cause data loss
2. **Implement FeatureManager service** - Clean, centralized feature flag management
3. **Enhance existing RBAC** - Feature flags + permissions = complete access control
4. **Gate controllers at constructor level** - Prevents any direct/indirect access
5. **Hide navigation elements** - Clean UI without delivery references
6. **Update mobile apps** - Conditional compilation or build variants
7. **Super Admin interface** - Simple toggle to enable/disable modules

### 13.3 Key Files to Create

| File | Purpose |
|---|---|
| `core/FeatureManager.php` | Central feature flag management service |
| `core/SuperAdminMiddleware.php` | Middleware for super admin routes |
| `app/Views/admin/module_config.php` | Super Admin UI for toggling modules |
| `migrations/add_module_configs.sql` | Database migration for module configs |

### 13.4 System Architecture After Changes

```
┌─────────────────────────────────────────────────────────────┐
│                    CURTISS ERP SYSTEM                         │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌───────────────────┐     ┌──────────────────┐             │
│  │  SUPER ADMIN UI   │────▶│ FeatureManager   │             │
│  │  (Module Toggles) │     │ (core service)   │             │
│  └───────────────────┘     └────────┬─────────┘             │
│                                      │                        │
│  ┌───────────────────────────────────▼──────────────────┐   │
│  │                  RBAC LAYER                            │   │
│  │  ┌────────────────────────────────────────────────┐   │   │
│  │  │ hasPermission(module, action)                   │   │   │
│  │  │   → Checks feature flag                         │   │   │
│  │  │   → Checks user role + permissions              │   │   │
│  │  │   → Returns true/false                          │   │   │
│  │  └────────────────────────────────────────────────┘   │   │
│  └───────────────────────────────────────────────────────┘   │
│                                      │                        │
│  ┌───────────────────────────────────▼──────────────────┐   │
│  │              MODULE ROUTER                             │   │
│  │                                                       │   │
│  │  Core Modules (Always Enabled):  Optional Modules:    │   │
│  │  ├── Accounting                 ├── Delivery Module   │   │
│  │  ├── Inventory                  ├── Vehicle Mgmt      │   │
│  │  ├── Purchasing                 ├── Rep Tracking      │   │
│  │  ├── Sales/CRM                  ├── Driver App API    │   │
│  │  ├── HRM/Payroll                ├── E-Commerce        │   │
│  │  ├── Reports                    └── Cheque Mgmt       │   │
│  │  ├── Admin/Settings                                    │   │
│  │  └── Users/Roles                                       │   │
│  └───────────────────────────────────────────────────────┘   │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

### 13.5 Final Recommendation

✅ **Proceed with implementation** using the Hybrid Soft Hiding approach.  
✅ **Budget ~15 development days** for complete implementation.  
✅ **Risk is LOW** - feature flags are non-destructive and fully reversible.  
✅ **No data loss** - all existing tables remain intact.  
✅ **Client-ready** - clean system without any delivery/logistics references.  
✅ **Future-proof** - can re-enable delivery at any time without migration.

---

## APPENDIX A: Complete File Manifest for Delivery Module

### Controllers to Gate (7 files)
```
Curtiss-ERP/app/Controllers/DeliveryController.php
Curtiss-ERP/app/Controllers/DriverDashboardController.php
Curtiss-ERP/app/Controllers/MasterRouteController.php
Curtiss-ERP/app/Controllers/PickingController.php
Curtiss-ERP/app/Controllers/RepDashboardController.php
Curtiss-ERP/app/Controllers/RepTrackingController.php
Curtiss-ERP/app/Controllers/VehicleController.php
```

### Models to Gate (6 files)  
```
Curtiss-ERP/app/Models/Delivery.php
Curtiss-ERP/app/Models/DriverInvoice.php
Curtiss-ERP/app/Models/DriverRoute.php
Curtiss-ERP/app/Models/RepCatalog.php
Curtiss-ERP/app/Models/RepTracking.php
Curtiss-ERP/app/Models/Vehicle.php
```

### Views to Gate/Remove (8+ files)
```
Curtiss-ERP/app/Views/deliveries/balancing_report.php
Curtiss-ERP/app/Views/deliveries/index.php
Curtiss-ERP/app/Views/deliveries/spreadsheet.php
Curtiss-ERP/app/Views/rep-tracking/index.php
Curtiss-ERP/app/Views/rep-tracking/print_loading.php
Curtiss-ERP/app/Views/rep-tracking/print_route_invoices.php
Curtiss-ERP/app/Views/vehicles/index.php
Curtiss-ERP/app/Views/dashboard/index.php (conditional)
```

### Core Files to Create (3 files)
```
Curtiss-ERP/core/FeatureManager.php          ← NEW
Curtiss-ERP/core/SuperAdminMiddleware.php    ← NEW  
migrations/add_module_configs.sql            ← NEW
```

### Core Files to Modify (4 files)
```
Curtiss-ERP/core/App.php          ← Add FeatureManager bootstrap
Curtiss-ERP/core/Controller.php   ← Add requireModuleEnabled()
Curtiss-ERP/core/RbacService.php  ← Add feature flag checks
Curtiss-ERP/public/index.php      ← Load FeatureManager
```

### Mobile Apps (2 projects)
```
Curtiss ERP Driver App/    ← Entire app becomes optional
Curtiss ERP Rep App/       ← Partial gating needed
```

---

## APPENDIX B: Sample Code for FeatureManager

```php
<?php
// core/FeatureManager.php
class FeatureManager {
    private static $cache = [];
    private static $loaded = false;
    
    /**
     * Check if a module feature is enabled.
     */
    public static function isEnabled($moduleKey) {
        self::loadIfNeeded();
        
        if (isset(self::$cache[$moduleKey])) {
            return self::$cache[$moduleKey];
        }
        
        // Default: enabled
        return true;
    }
    
    /**
     * Get the module-to-feature mapping for permission checks.
     */
    public static function getPermissionFeatureMap() {
        return [
            'reptracking' => 'rep_tracking_module',
            'delivery'    => 'delivery_module',
            'vehicle'     => 'vehicle_module',
            'cheque'      => 'cheque_module',
            'ecommerce'   => 'ecommerce_module',
        ];
    }
    
    /**
     * Load configuration from session or database.
     */
    private static function loadIfNeeded() {
        if (self::$loaded) return;
        
        if (isset($_SESSION['module_config'])) {
            self::$cache = $_SESSION['module_config'];
        } else {
            $db = new Database();
            $db->query("SELECT module_key, is_enabled FROM module_configs");
            $rows = $db->resultSet();
            foreach ($rows as $row) {
                self::$cache[$row->module_key] = (bool)$row->is_enabled;
            }
            $_SESSION['module_config'] = self::$cache;
        }
        
        self::$loaded = true;
    }
    
    /**
     * Refresh config (call after admin changes feature flags).
     */
    public static function refresh() {
        self::$cache = [];
        self::$loaded = false;
        unset($_SESSION['module_config']);
    }
}
```

---

*End of Report*