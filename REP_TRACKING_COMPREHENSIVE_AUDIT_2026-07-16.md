# Rep Tracking System — Comprehensive Technical, Operational & Financial Audit

**Audit date:** 16 July 2026  
**Scope:** `app/Views/rep-tracking/`, `RepTrackingController`, `RepTracking`, `Delivery`, `RepRouteService`, `RepVarianceService`, `StockLedger`, `RepDashboardController` mobile sync, and related invoice/payment flows.  
**Method:** static code trace and control design review. This is not a production-data audit: the repository does not contain the live schema/data, database engine configuration, server logs, or deployed access-control configuration. Findings marked **verified** are code-backed; live duplicate/orphan counts must be measured with production reconciliation queries before financial sign-off.

## Executive summary

The module has a substantial operational workflow and some recent safeguards: CSRF checks on most mutating desktop endpoints, delivery row locking, collection verification gates, invoice reservation status, and stock movement logging. It is nevertheless **not ready for unqualified financial-control reliance**. The largest concerns are insecure mobile synchronization, non-atomic cross-connection stock/GL posting, weak idempotency enforced only in application code, destructive deletion of financial history, and a route state machine that permits invalid jumps.

The user interface is feature-rich but unusually dense; it mixes dispatch, stock count, variance processing, account mapping, collections, printing, and irreversible actions in a long inline-script view. This raises operator-error risk in exactly the workflow that affects cash, receivables, inventory, and COGS.

### Overall scores (design assessment)

| Dimension | Score / 100 | Basis |
|---|---:|---|
| Overall system health | **51** | Broad functionality, but material control and integrity gaps |
| Code quality | 48 | Very large controller/view scripts; duplicated route-resolution logic |
| Workflow accuracy | 55 | Main flow is present; state transition and exception controls are incomplete |
| Accounting integrity | 43 | Double-entry shape exists, but duplicate/period/date/atomicity controls are insufficient |
| Stock management | 47 | Reservation/deduction flow exists; ledger is non-atomic and has no enforced uniqueness |
| Journal-entry accuracy | 45 | Lines are balanced per generated JE; duplicate and date/reference controls are weak |
| Database integrity | 40 | Repository migrations show additive columns but no keys/unique constraints for core idempotency |
| Audit-trail completeness | 42 | Some activity logging, but no immutable before/after trail for many financial changes |
| Security | 31 | Mobile sync caller identity is forgeable; authorization is inconsistent |
| Performance | 58 | Pagination exists, but repeated per-route/per-invoice queries and UI payloads will not scale well |
| UI/UX | 60 | Strong task coverage; cognitive load and destructive-action safety need work |
| Maintainability | 42 | Business rules distributed through 2,729-line controller and client-side code |

## Architecture and maintainability

### Observed structure

* UI is split into `index.php`, `index.js.php`, `index.css.php`, partials and print templates. This is a reasonable visual decomposition.
* Server logic is primarily in [`RepTrackingController.php`](app/Controllers/RepTrackingController.php), 2,729 lines, and [`RepTracking.php`](app/Models/RepTracking.php), with delivery, route, binding and variance services.
* The module reaches directly into invoices, payments, warehouse stock, FIFO, journal entries, audit logs and mobile synchronization. It is an orchestration domain, but its orchestration is not centralized.

### Design findings

| ID | Severity | Verified finding / root cause | Business and technical impact | Recommendation |
|---|---|---|---|---|
| ARC-01 | High | `RepTrackingController` combines HTTP parsing, authorization, SQL, workflow state, accounting drafts, reporting, printing and deletion. | Regression risk; hard to test and segregate duties. | Extract `RouteWorkflowService`, `CollectionPostingService`, `DeliveryFinalizationService`, query repositories and policy objects. Add request DTO validation. |
| ARC-02 | High | Route-binding expansion is reimplemented in controller/model methods instead of one authoritative resolver. | Bound routes can be treated differently by reporting, loading, collections and finalization. | Provide one `RouteGroupResolver::routeIdsFor(routeId)` with tests. |
| ARC-03 | Medium | UI contains a very large inline JavaScript workflow with HTML string composition and inline handlers. | Difficult to test/accessibly maintain; raises injection and user-error risk. | Split by workflow, use components/event listeners, typed API clients, server-rendered escaping or DOM APIs. |
| ARC-04 | Medium | `StockLedger` creates/migrates its own table at runtime and silently suppresses errors. | Production schema drift and missing-ledger failures can be invisible. | Move DDL to versioned migration; fail and alert on ledger write failure. |
| ARC-05 | Medium | Route and delivery status vocabulary overlaps (`Finalizing`, `Finalized`, `Delivery Arranged`, `Completed`) without a single state model. | Reporting and downstream action rules can disagree. | Adopt an explicit state machine and immutable status-history table. |

## End-to-end workflow assessment

### Route planning, assignment, start and completion

Mobile `sync_push` creates/updates `rep_daily_routes`; manual creation inserts an `Active` route. Route completion may become `Pending GL` when collections exist, otherwise `Adjustments`. Delivery finalization marks delivery `Finalized` and routes `Finalized`; the controller then separately marks routes `Completed`.

* **Verified gap (WF-01, High):** [`api_update_route_status`](app/Controllers/RepTrackingController.php:1047) accepts any allowed target status; it does not validate the *current → next* transition, route ownership, completion timestamps, required stock/reconciliation evidence, or whether the update affected a row. An Active route can be pushed to Completed by any authenticated desktop user who can reach the endpoint.
* **Verified gap (WF-02, High):** [`finalize`](app/Controllers/RepTrackingController.php:953) finalizes delivery inside one transaction and updates route status later through another `Database` instance. If the second step fails, dispatch is final but route status is stale. Make route completion part of the delivery-finalization transaction.
* **Verified gap (WF-03, Medium):** [`create_route_manual`](app/Controllers/RepTrackingController.php:2563) validates presence only. It does not validate employee eligibility, route/rep/date duplication, valid timestamp/meter/GPS range, or create an audit entry.
* **Verified gap (WF-04, Medium):** Route planning is a route-name/manual-entry process, not true plan/schedule/sequence management. No confirmed stop schedule, SLA, planned distance, capacity or supervisor approval exists in this scope.

### GPS, visits, check-in/out

The route map builds waypoints from route start/end and invoice/customer data ([`RepTracking.php`](app/Models/RepTracking.php:203)). There is no code in this module that persists a GPS breadcrumb stream, check-in/out event, geofence validation, visit duration, reason code, photo/signature, or missed-visit record.

* **WF-05, High:** Do not label this as live GPS tracking. It is a route visualization based on sparse coordinates. Implement `rep_visit_events` and `rep_location_pings` with device time, server receive time, accuracy, GPS provenance, geofence result, and offline idempotency key.
* **WF-06, High:** Customer visit is represented largely by invoice processing; a no-sale, failed, postponed or skipped visit is not a first-class reconciled transaction. Add required outcome codes and supervisor exception workflow.

### Sales/orders, loading, delivery, returns and variances

Invoices are attached to a route, reserved, then delivery finalization deducts stock and writes ledger movements. Delivery visits can update or return invoice lines. Returns are entered as JSON and verified before finalization.

* **WF-07, Critical:** See INV-01 below: the stock ledger and its inventory GL post on another connection from delivery finalization. The all-or-nothing delivery promise is false.
* **WF-08, High:** Return stock is stored as mutable `return_stock_json` ([`api_save_return_stock`](app/Controllers/RepTrackingController.php:2258)). It has no normalized rows, quantity bounds/serial/batch/warehouse destination validation shown, or immutable approval history. A JSON draft is unsuitable as the system of record for stock reconciliation.
* **WF-09, High:** Attach/detach endpoints update `invoices.rep_route_id` directly ([`api_detach_invoice`](app/Controllers/RepTrackingController.php:2538)). No verified check prevents reassignment after loading, delivery, stock deduction, posted cash, or finalization. Enforce status guards and write an auditable transfer event.
* **WF-10, Medium:** Delivery finalization calculates stock by invoice `quantity` but releases reservation by `loaded_quantity` ([`Delivery.php`](app/Models/Delivery.php:444)). The distinction may be intended, but it must be validated: delivered quantity cannot exceed loaded/reserved quantity, and un-delivered stock must reconcile to a named van/warehouse location.

### Collections and synchronization

Mobile sync inserts pending collections; desktop verifies them and finalization creates the payment journal, customer payment and cheque record.

* **WF-11, Critical:** See SEC-01: sync identity can be supplied as `X-User-ID` or JSON `user_id` ([`RepDashboardController.php`](app/Controllers/RepDashboardController.php:579)). A caller can impersonate another active user.
* **WF-12, Critical:** [`finalizePayments`](app/Models/RepTracking.php:350) does not begin/commit its own transaction, despite `api_finalize_collections` explicitly removing an outer transaction. It locks/creates JE/transactions/customer payment/cheque/settlement/collection status sequentially; an exception can persist a partial payment posting. It must always use an atomic transaction on the same connection.
* **WF-13, High:** Collection verification accepts arbitrary IDs, `is_verified`, adjusted amount and account IDs without checking the record belongs to the selected route, is still pending, an approver is authorized, adjusted amount is non-negative/within tolerance, or accounts are valid and permitted ([`api_verify_collections`](app/Controllers/RepTrackingController.php:1462)).
* **WF-14, High:** Sync handles customers/routes/invoices/payments without a single enclosing transaction. A mid-payload exception produces partial synchronization and retry semantics are not transactionally protected.

## Accounting and journal integrity

### What is correct in the design

`finalizePayments` constructs two lines: debit asset/cash/bank/cheque and credit AR. `StockLedger::logMovement` constructs debit/credit inventory/COGS style entries for movements where mapping exists. These individual pairs are arithmetically balanced when they complete.

### Material issues

| ID | Severity | Finding | Effect | Required correction |
|---|---|---|---|---|
| ACC-01 | Critical | Payment posting can be partially committed (WF-12). | JE or account balance can exist without customer payment/settlement or pending collection finalization. | One DB transaction, `FOR UPDATE`, fail closed, and an outbox/reconciliation record. |
| ACC-02 | Critical | No database unique key is evidenced for `journal_entries.reference`; `FINAL-PMT-{id}` is inserted without a duplicate check ([`RepTracking.php`](app/Models/RepTracking.php:416)). | Retries/concurrent calls can duplicate cash/AR journal postings. | Unique `(source_type, source_id, posting_version)` or immutable posting table; reject duplicate source posting. |
| ACC-03 | High | Posting dates use `CURDATE()` rather than collection date/delivery/invoice accounting date. | Wrong accounting periods, cash cut-off and reconciliation differences. | Store event date and approved posting date; validate open period and record period override. |
| ACC-04 | High | Account mappings are caller-provided and only presence-checked; no type/active/permission validation is shown. | Cash may post to revenue/incorrect control account; reporting can be materially misstated. | Configuration-driven account mapping with approved override policy and account-type validation. |
| ACC-05 | High | Draft invoice JEs can be created by [`api_save_accounting_entries`](app/Controllers/RepTrackingController.php:2319) even though comments say invoice JEs are posted at creation; cleanup deletes drafts by reference. | Conflicting sources of truth and opportunity for duplicate revenue postings. | Remove ad hoc revenue-draft path or formalize it as a separately controlled, non-posting preview. |
| ACC-06 | High | Hard deletion of route/payment journals in `RepRouteService::deleteRoute` ([`RepRouteService.php`](app/Services/RepRouteService.php:116)) removes transactions, JEs and customer payments. | Violates auditability; changes a closed financial history rather than reversing it. | Prohibit physical delete once any financial/stock event exists. Use void/reversal JEs and immutable audit records. |
| ACC-07 | Medium | `StockLedger` skips stock JE creation if no financial year match; it still creates a stock ledger row. | Inventory quantity and GL valuation can diverge silently. | Reject closed/undefined periods before any movement, or queue controlled posting with a visible exception. |

**Accounting conclusion:** No statement of “correct double-entry accounting” can be made until live data is reconciled. The code has balanced line construction but lacks the idempotency, atomicity, period, reversal and approval controls required for reliable ERP posting.

## Inventory and stock-ledger audit

| ID | Severity | Finding / root cause | Risk and fix |
|---|---|---|---|
| INV-01 | Critical | `Delivery::finalizeDelivery` opens a transaction, then creates `StockLedger`, which opens another `Database` connection ([`StockLedger.php`](app/Models/StockLedger.php:1), [`Delivery.php`](app/Models/Delivery.php:444)). The ledger/JEs can commit independently. | Failed finalization can leave orphan stock movements, COGS/inventory JEs and changed balances. Inject the existing transaction/connection into ledger/FIFO; commit once. |
| INV-02 | Critical | `StockLedger::logMovement` catches all errors and only logs them ([`StockLedger.php`](app/Models/StockLedger.php:80)); caller continues. | Physical stock can change without ledger and accounting evidence. Throw within business transaction; alert and block finalization. |
| INV-03 | High | No uniqueness is defined for a source stock movement. `STK-{reference}` can be repeated for each item and no `source_type/source_line_id` unique constraint exists. | Retry/double-click/reprocessing can duplicate ledger and GL. Add immutable movement source keys and a unique constraint. |
| INV-04 | High | Quantities use `GREATEST(0, ...)` on on-hand/reserved balances. | It hides underflow rather than rejecting it, so a requested quantity of 10 against 4 removes 4 while the invoice/ledger may report 10. | Lock inventory row; explicitly fail if available/reserved stock is insufficient; allow negative only via approved exception. |
| INV-05 | High | Stock ledger running balance is calculated by selecting last row without locking the item/variation ledger sequence. | Concurrent movements can calculate identical predecessor balances and corrupt running balance. | Use per-item/warehouse balance row with `SELECT … FOR UPDATE`; derive reports from immutable movements or rebuild balances. |
| INV-06 | High | Return adjustment posts only the difference between expected and actual return to the warehouse item balance. There is no represented van/rep-stock location or full load-out/return-in transfer. | Van/rep stock cannot be independently reconciled; shortages are conflated with stock adjustment. | Model locations and movements: warehouse → van/rep, van → customer, van → warehouse, discrepancy/damage. |
| INV-07 | Medium | Item lookup falls back to product name in finalization. | Duplicate names can consume/deduct the wrong SKU. | Require immutable item/variation IDs; prohibit name fallback. |

## Database integrity and duplicate controls

The supplied migrations add `mobile_local_id`, `mobile_rep_id`, `uuid` and verification fields but do **not** add unique indexes or foreign keys for them ([`pending_collections_idempotency.sql`](pending_collections_idempotency.sql)). Application-level “check then insert” is vulnerable to concurrent requests.

Required database controls (verify actual production schema before applying):

| Entity | Required unique / referential rule |
|---|---|
| Rep route | `uuid` unique; optionally `(user_id, route_name, service_date)` unique according to business rule; user FK |
| Pending collection | unique non-null `uuid`; unique `(mobile_rep_id,mobile_local_id)`; FKs to customer/route/user/accounts |
| Customer payment | unique `pending_collection_id` (add field); FK to JE; no delete after posting |
| Journal | unique posting-source key, not free-text reference alone; transactions FK and debit/credit check |
| Stock ledger | unique `(source_type,source_line_id,movement_type)`; FKs to item/variant/location/user/JE |
| Delivery | route/date uniqueness policy; FK to routes; finalized records immutable |
| Invoice attachment | status-guarded route assignment and an attachment history table |

**Data-quality queries to run in production before release:**

1. Duplicate collection UUIDs and duplicate `(mobile_rep_id,mobile_local_id)` pairs.
2. More than one posted JE for a pending collection, invoice, stock source line or delivery.
3. Journal headers whose `SUM(debit) <> SUM(credit)` and transaction rows with missing header/account.
4. Customer payments without one posted payment JE; finalized pending collections without one customer payment.
5. Stock movements without JE (where policy requires one), JEs without stock movement, and item balance versus ledger reconstructed balance by item/variation/location.
6. Finalized deliveries/routes with unverified collections, null return verification, reserved invoice stock, or incomplete ledger movements.
7. Orphaned invoices/deliveries/payments/cheques/audit logs after route deletion.

## Security review

| ID | Severity | Finding | Recommendation |
|---|---|---|---|
| SEC-01 | Critical | `sync_push` authenticates using caller-controlled `X-User-ID`/payload `user_id`; it only verifies that account exists/active ([`RepDashboardController.php`](app/Controllers/RepDashboardController.php:579)). | Require authenticated bearer token/session tied to device/user, rotate keys, verify signature/expiry, enforce device enrollment and rate limiting. Never accept identity from body/header alone. |
| SEC-02 | High | Rep Tracking constructor only requires any session user. Most methods do not make a server-side permission/role decision. | Add endpoint-level policies: view, plan, bind, attach, verify collection, approve override, finalize, void/delete. Enforce route/branch scope. |
| SEC-03 | High | Sensitive read APIs (`api_get_route_path`, route details, collections) have no explicit route-scope authorization. | Apply authorization to every route/delivery/invoice lookup, not just UI visibility. |
| SEC-04 | Medium | Client code renders API data into HTML (`innerHTML` template construction in `index.js.php`). | Treat all names/notes/descriptions as untrusted; use `textContent`/DOM creation or robust escaping. CSP should disallow unsafe inline script where feasible. |
| SEC-05 | Medium | Password “descrambling” is reversible XOR with CSRF token (`descramblePassword`). | Remove it. Submit over HTTPS and use normal password verification plus re-auth token/step-up challenge. |
| SEC-06 | Medium | Client-side CAPTCHA is a simple session arithmetic question; destructive API behavior still relies on code-level authorization. | Keep a server-side, single-use step-up confirmation; never rely on CAPTCHA for authorization. |

Prepared SQL prevents the main SQL-injection vector in inspected code. This does not offset the authorization/sync risks.

## Audit trail review

Activity logging exists for several route/mobile events and return-stock draft save. It is incomplete for a financial audit:

* Manual route creation, invoice attach/detach, invoice deletion, route notes, collection verification, account-map edits, payment finalization, status overrides, stock deduction, and draft JE creation do not consistently record user, timestamp, before/after values, reason, source device/session and correlation ID.
* `RepRouteService::deleteRoute` logs a deletion after deleting core records; the audit record lacks a preserved snapshot of each affected invoice/payment/JE/stock movement.
* Audit logs appear mutable ordinary database rows; no immutability/hash chain/export retention policy is evidenced.

Implement an append-only `audit_events` table with actor, impersonation/device/session/IP, correlation/request ID, entity/version, action, before JSON, after JSON, reason/approval ID, timestamp (UTC) and retention/access policy. Log every posting/reversal and approval.

## Performance and reporting

* Route listing uses aggregate subqueries and pagination, a good baseline, but it repeats similar expensive route/bound-route queries in several methods. Index `rep_daily_routes(status, route_binding_id, start_time, user_id)`, `invoices(rep_route_id,status,customer_id)`, `pending_collections(route_id,status,is_verified)`, and source/UUID columns.
* Per-invoice/per-item queries in finalization create N+1 load. Preload invoice lines, item/variant/location/cost data and batch writes while retaining locks.
* `StockLedger` initialization probes/possibly migrates on object construction; remove DDL from request path.
* Reports should calculate from immutable source transactions rather than current route/invoice fields, and expose “as-of” date, void/reversal status and reconciliation exceptions.
* No evidence supports report reconciliation in live data. Each route/visit/sales/collection/expense/stock/journal/ledger report needs a signed reconciliation test suite and export control totals.

## UX and productivity recommendations

The current screen exposes many tasks in one workspace and has loading/empty states, print/export and working tabs. The next improvements should reduce costly operator decisions:

1. Separate roles into focused workspaces: Rep mobile route/visits; supervisor exceptions; warehouse load/return count; finance collection approval/posting.
2. Display a mandatory completion checklist with blocking reasons, source totals and clear “draft / approved / posted / reversed” badges.
3. Use a reconciliation grid: loaded, delivered, returned, damaged, expected, counted, variance, approver and photo/scan evidence per SKU.
4. Add barcode/QR scanning, batch/serial support, signature/photo attachment, offline queue status and retry-safe sync receipts.
5. Add planned stop schedule, route optimization, geofence/check-in controls, missed-visit alerts, customer visit history and supervisor live exception dashboard.
6. Add KPI alerts for cash overdue, unverified collections, negative/low stock, route duration, visit completion, returns/shortage rate and duplicate/retry detection.

## Prioritized roadmap

| Priority | Recommendation | Effort | Business impact |
|---|---|---:|---|
| Critical | Replace mobile sync identity with signed authenticated device/user tokens and server authorization. | 1–2 weeks | Prevents impersonation and fraudulent data posting |
| Critical | Make payment, delivery, stock ledger, FIFO and GL posting one atomic transaction; make ledger errors fail closed. | 2–3 weeks | Prevents financial/stock corruption |
| Critical | Add database-enforced idempotency/source keys for routes, collections, stock movements and journals; repair duplicates. | 1–2 weeks | Prevents duplicate cash, AR, inventory and GL entries |
| Critical | Ban physical deletion after operational/financial posting; implement controlled void/reversal and immutable audit history. | 2 weeks | Supports auditability and statutory records |
| High | Implement state machine with guards, approvals, state history and one finalization boundary. | 2 weeks | Prevents bypassed/inconsistent workflows |
| High | Normalize return stock, rep/van locations and stock movement sources; reconcile on-hand to ledger. | 3–5 weeks | Enables true inventory accountability |
| High | Add authorization policies and route/branch ownership enforcement to every endpoint. | 1–2 weeks | Segregation of duties and data protection |
| High | Introduce automated reconciliation jobs/dashboards and production data repair plan. | 2–3 weeks | Detects legacy corruption and prevents recurrence |
| Medium | Extract services/repositories/DTO validation and test workflows. | 4–6 weeks | Maintainability and safer change velocity |
| Medium | Split UX by role; add guided checklist and exception dashboard. | 3–4 weeks | Faster execution, fewer operator errors |
| Medium | Add GPS event/visit model, scheduling/geofencing and offline sync outbox. | 4–6 weeks | Verifiable field activity and better coverage |
| Low | Optimize query batching, indexes and report materialization after baseline measurement. | 1–3 weeks | Better performance at scale |

## Release gate

Do **not** treat Rep Tracking as financially controlled until Critical items are resolved, production data has been reconciled, and the following tests pass: repeated sync/retry; concurrent finalization; mid-transaction database failure; rejected stock underflow; duplicate source submission; route-status bypass; unauthorized route access; period-close posting; reversal/void; and route-to-invoice-to-stock-to-COGS-to-GL-to-customer-payment reconciliation.

