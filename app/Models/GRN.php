<?php
class GRN {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getGRNsPaginated($search = '', $limit = 10, $offset = 0, $filters = []) {
        $sql = "SELECT g.*, v.name as vendor_name, u.username as creator_name, p.po_number,
                       u2.username as approver_name,
                       COALESCE((SELECT SUM(total) FROM grn_items WHERE grn_id = g.id), 0) as total_amount
                FROM goods_receipt_notes g 
                JOIN vendors v ON g.vendor_id = v.id 
                LEFT JOIN users u ON g.created_by = u.id
                LEFT JOIN users u2 ON g.approved_by = u2.id
                LEFT JOIN purchase_orders p ON g.po_id = p.id
                WHERE (g.grn_number LIKE :search OR g.receipt_number LIKE :search OR v.name LIKE :search OR p.po_number LIKE :search)";
        
        if (!empty($filters['vendor_id'])) { $sql .= " AND g.vendor_id = :vid"; }
        
        $sql .= " ORDER BY g.created_at DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($sql);
        $this->db->bind(':search', "%$search%");
        if (!empty($filters['vendor_id'])) { $this->db->bind(':vid', $filters['vendor_id']); }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getTotalGRNs($search = '', $filters = []) {
        $sql = "SELECT COUNT(*) as total FROM goods_receipt_notes g JOIN vendors v ON g.vendor_id = v.id LEFT JOIN purchase_orders p ON g.po_id = p.id WHERE (g.grn_number LIKE :search OR g.receipt_number LIKE :search OR v.name LIKE :search OR p.po_number LIKE :search)";
        if (!empty($filters['vendor_id'])) { $sql .= " AND g.vendor_id = :vid"; }
        
        $this->db->query($sql);
        $this->db->bind(':search', "%$search%");
        if (!empty($filters['vendor_id'])) { $this->db->bind(':vid', $filters['vendor_id']); }
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function getGRNById($id) {
        $this->db->query("SELECT g.*, v.name as vendor_name, v.email, v.phone, v.address, 
                                 u.username as creator_name, u.signature_path as creator_signature,
                                 p.po_number, u2.username as approver_name,
                                 COALESCE((SELECT SUM(total) FROM grn_items WHERE grn_id = g.id), 0) as total_amount
                          FROM goods_receipt_notes g 
                          JOIN vendors v ON g.vendor_id = v.id 
                          LEFT JOIN users u ON g.created_by = u.id
                          LEFT JOIN users u2 ON g.approved_by = u2.id
                          LEFT JOIN purchase_orders p ON g.po_id = p.id
                          WHERE g.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getGRNItems($id) {
        $this->db->query("SELECT * FROM grn_items WHERE grn_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->resultSet();
    }

    public function createGRN($grnData, $items, $userId) {
        try {
            $this->db->beginTransaction();

            $grandTotal = 0;
            foreach ($items as $item) {
                $grandTotal += floatval($item['qty']) * floatval($item['price']);
            }

            $this->db->query("INSERT INTO goods_receipt_notes (grn_number, receipt_number, po_id, vendor_id, grn_date, notes, created_by, is_approved, total_amount) 
                              VALUES (:num, :receipt, :pid, :vid, :gdate, :notes, :uid, 0, :total)");
            $this->db->bind(':num', $grnData['grn_number']);
            $this->db->bind(':receipt', $grnData['receipt_number'] ?? null);
            $this->db->bind(':pid', $grnData['po_id']);
            $this->db->bind(':vid', $grnData['vendor_id']);
            $this->db->bind(':gdate', $grnData['grn_date']);
            $this->db->bind(':notes', $grnData['notes']);
            $this->db->bind(':uid', $userId);
            $this->db->bind(':total', $grandTotal);
            $this->db->execute();
            $grnId = $this->db->lastInsertId();

            foreach ($items as $item) {
                // Insert Line Item
                $this->db->query("INSERT INTO grn_items (grn_id, item_id, item_variation_option_id, description, quantity, unit_cost, total, selling_price, wholesale_price, retail_margin, wholesale_margin) 
                                  VALUES (:gid, :iid, :vid, :desc, :qty, :cost, :total, :sprice, :wprice, :rmargin, :wmargin)");
                $this->db->bind(':gid', $grnId);
                $this->db->bind(':iid', $item['item_id']);
                $this->db->bind(':vid', $item['var_opt_id']);
                $this->db->bind(':desc', $item['desc']);
                $this->db->bind(':qty', $item['qty']);
                $this->db->bind(':cost', $item['price']);
                $this->db->bind(':total', ($item['qty'] * $item['price']));
                $this->db->bind(':sprice', $item['selling_price']);
                $this->db->bind(':wprice', $item['wholesale_price']);
                $this->db->bind(':rmargin', $item['retail_margin']);
                $this->db->bind(':wmargin', $item['wholesale_margin']);
                $this->db->execute();
            }

            $this->db->commit();
            return $grnId;
        } catch (PDOException $e) { $this->db->rollBack(); throw $e; }
    }

    public function deleteGRN($id) {
        try {
            $grn = $this->getGRNById($id);
            if (!$grn) { throw new Exception("GRN not found."); }
            if ($grn->is_approved) {
                throw new Exception("Approved Goods Receipt Notes cannot be deleted.");
            }

            $this->db->beginTransaction();

            $this->db->query("DELETE FROM grn_items WHERE grn_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->query("DELETE FROM goods_receipt_notes WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (PDOException $e) { $this->db->rollBack(); throw $e; }
    }

    public function updateGRN($grnId, $grnData, $items, $userId) {
        try {
            $grn = $this->getGRNById($grnId);
            if (!$grn) { throw new Exception("GRN not found."); }
            if ($grn->is_approved) { throw new Exception("Approved GRNs cannot be edited."); }

            $this->db->beginTransaction();

            // Delete old grn_items
            $this->db->query("DELETE FROM grn_items WHERE grn_id = :id");
            $this->db->bind(':id', $grnId);
            $this->db->execute();

            $grandTotal = 0;
            foreach ($items as $item) {
                $grandTotal += floatval($item['qty']) * floatval($item['price']);
            }

            // Update master GRN record
            $this->db->query("UPDATE goods_receipt_notes 
                              SET vendor_id = :vid, receipt_number = :receipt, grn_date = :gdate, notes = :notes, total_amount = :total 
                              WHERE id = :id");
            $this->db->bind(':vid', $grnData['vendor_id']);
            $this->db->bind(':receipt', $grnData['receipt_number'] ?? null);
            $this->db->bind(':gdate', $grnData['grn_date']);
            $this->db->bind(':notes', $grnData['notes']);
            $this->db->bind(':total', $grandTotal);
            $this->db->bind(':id', $grnId);
            $this->db->execute();

            // Insert new items
            foreach ($items as $item) {
                // Insert new line item
                $this->db->query("INSERT INTO grn_items (grn_id, item_id, item_variation_option_id, description, quantity, unit_cost, total, selling_price, wholesale_price, retail_margin, wholesale_margin) 
                                  VALUES (:gid, :iid, :vid, :desc, :qty, :cost, :total, :sprice, :wprice, :rmargin, :wmargin)");
                $this->db->bind(':gid', $grnId);
                $this->db->bind(':iid', $item['item_id']);
                $this->db->bind(':vid', $item['var_opt_id']);
                $this->db->bind(':desc', $item['desc']);
                $this->db->bind(':qty', $item['qty']);
                $this->db->bind(':cost', $item['price']);
                $this->db->bind(':total', ($item['qty'] * $item['price']));
                $this->db->bind(':sprice', $item['selling_price']);
                $this->db->bind(':wprice', $item['wholesale_price']);
                $this->db->bind(':rmargin', $item['retail_margin']);
                $this->db->bind(':wmargin', $item['wholesale_margin']);
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) { $this->db->rollBack(); throw $e; }
    }

    public function approveGRN($grnId, $userId) {
        try {
            $grn = $this->getGRNById($grnId);
            if (!$grn) { throw new Exception("GRN not found."); }
            if ($grn->is_approved) { throw new Exception("GRN is already approved."); }

            $this->db->beginTransaction();

            $items = $this->getGRNItems($grnId);

            require_once __DIR__ . '/FIFO.php';
            $fifo = new FIFO();

            foreach ($items as $item) {
                // 1. Record FIFO stock receipt batch
                $fifo->recordReceipt($item->item_id, $item->item_variation_option_id, $grnId, $item->quantity, $item->unit_cost);

                // 2. Update Master Item Stock, Cost, and Selling/Wholesale Prices
                $this->db->query("
                    UPDATE items 
                    SET quantity_on_hand = COALESCE(quantity_on_hand, 0) + :qty, 
                        cost_price = :cost,
                        price = CASE WHEN :sprice > 0.001 THEN :sprice ELSE price END,
                        wholesale_price = CASE WHEN :wprice > 0.001 THEN :wprice ELSE wholesale_price END,
                        retail_margin = CASE WHEN :sprice > 0.001 THEN :rmargin ELSE retail_margin END,
                        wholesale_margin = CASE WHEN :wprice > 0.001 THEN :wmargin ELSE wholesale_margin END
                    WHERE id = :iid
                ");
                $this->db->bind(':qty', $item->quantity);
                $this->db->bind(':cost', $item->unit_cost);
                $this->db->bind(':sprice', floatval($item->selling_price ?? 0));
                $this->db->bind(':wprice', floatval($item->wholesale_price ?? 0));
                $this->db->bind(':rmargin', floatval($item->retail_margin ?? 0));
                $this->db->bind(':wmargin', floatval($item->wholesale_margin ?? 0));
                $this->db->bind(':iid', $item->item_id);
                $this->db->execute();

                // 3. Update Specific Variation Stock, Cost, and Selling/Wholesale Prices (If applicable)
                if ($item->item_variation_option_id) {
                    $this->db->query("
                        UPDATE item_variation_options 
                        SET quantity_on_hand = COALESCE(quantity_on_hand, 0) + :qty, 
                            cost = :cost,
                            price = CASE WHEN :sprice > 0.001 THEN :sprice ELSE price END,
                            wholesale_price = CASE WHEN :wprice > 0.001 THEN :wprice ELSE wholesale_price END
                        WHERE id = :vid
                    ");
                    $this->db->bind(':qty', $item->quantity);
                    $this->db->bind(':cost', $item->unit_cost);
                    $this->db->bind(':sprice', floatval($item->selling_price ?? 0));
                    $this->db->bind(':wprice', floatval($item->wholesale_price ?? 0));
                    $this->db->bind(':vid', $item->item_variation_option_id);
                    $this->db->execute();
                }

                // 3.5 Log Stock Movement in Ledger
                require_once __DIR__ . '/StockLedger.php';
                $ledger = new StockLedger();
                $this->db->query("SELECT warehouse_id FROM items WHERE id = :id");
                $this->db->bind(':id', $item->item_id);
                $itemRow = $this->db->single();
                $whId = $itemRow ? $itemRow->warehouse_id : null;
                $ledger->logMovement($item->item_id, $item->item_variation_option_id, $item->quantity, 0, 'GRN', $grn->grn_number, $whId, $userId, 'GRN Approved Stock Receipt', $item->unit_cost);
            }

            // 4. Record Double-Entry Accounting Journal Entry
            $grandTotal = 0;
            foreach ($items as $item) {
                $grandTotal += floatval($item->quantity) * floatval($item->unit_cost);
            }

            if ($grandTotal > 0.001) {
                // Check if period is closed/locked
                $this->db->query("SELECT COUNT(*) as cnt FROM financial_years WHERE :entry_date BETWEEN start_date AND end_date");
                $this->db->bind(':entry_date', $grn->grn_date);
                $res = $this->db->single();
                if ($res && $res->cnt > 0) {
                    throw new Exception('Accounting Error: The period containing date ' . $grn->grn_date . ' is closed and locked.');
                }

                // Resolve accounts
                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1300' OR account_name LIKE '%Inventory Asset%' OR account_name LIKE '%Stock%' LIMIT 1");
                $invAccRow = $this->db->single();
                $inventoryAccountId = $invAccRow ? $invAccRow->id : null;

                $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '2000' OR account_name LIKE '%Accounts Payable%' OR account_name LIKE '%Creditor%' LIMIT 1");
                $apAccRow = $this->db->single();
                $apAccountId = $apAccRow ? $apAccRow->id : null;

                if ($inventoryAccountId && $apAccountId) {
                    $reference = 'GRN-' . $grn->grn_number;
                    $description = "GRN Approved Stock Receipt - Ref: " . $grn->grn_number;
                    
                    // Insert master journal entry
                    $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status, is_manual) 
                                      VALUES (:entry_date, :reference, :description, :created_by, 'Posted', 0)");
                    $this->db->bind(':entry_date', $grn->grn_date);
                    $this->db->bind(':reference', $reference);
                    $this->db->bind(':description', $description);
                    $this->db->bind(':created_by', $userId);
                    $this->db->execute();
                    $journalEntryId = $this->db->lastInsertId();

                    // 1. DEBIT: Inventory Asset
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) 
                                      VALUES (:journal_id, :account_id, :debit, 0, :desc)");
                    $this->db->bind(':journal_id', $journalEntryId);
                    $this->db->bind(':account_id', $inventoryAccountId);
                    $this->db->bind(':debit', $grandTotal);
                    $this->db->bind(':desc', "Stock receipt for GRN #" . $grn->grn_number);
                    $this->db->execute();
                    $this->db->updateAccountBalance($inventoryAccountId, $grandTotal, 0);

                    // 2. CREDIT: Accounts Payable
                    $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit, description) 
                                      VALUES (:journal_id, :account_id, 0, :credit, :desc)");
                    $this->db->bind(':journal_id', $journalEntryId);
                    $this->db->bind(':account_id', $apAccountId);
                    $this->db->bind(':credit', $grandTotal);
                    $this->db->bind(':desc', "Liability recorded for GRN #" . $grn->grn_number);
                    $this->db->execute();
                    $this->db->updateAccountBalance($apAccountId, 0, $grandTotal);
                } else {
                    throw new Exception("Accounting Configuration Error: Inventory Asset (1300) or Accounts Payable (2000) account not found in Chart of Accounts.");
                }
            }

            // 5. Update PO Status if linked (supporting Partial Receipts & Back-orders)
            if (!empty($grn->po_id)) {
                // Fetch PO items & ordered quantities
                $this->db->query("SELECT item_id, item_variation_option_id, SUM(quantity) as qty 
                                  FROM purchase_order_items 
                                  WHERE po_id = :id 
                                  GROUP BY item_id, COALESCE(item_variation_option_id, 0)");
                $this->db->bind(':id', $grn->po_id);
                $poItems = $this->db->resultSet() ?: [];

                // Fetch total received quantities including this GRN
                $this->db->query("SELECT item_id, item_variation_option_id, SUM(quantity) as qty 
                                  FROM grn_items 
                                  WHERE grn_id IN (SELECT id FROM goods_receipt_notes WHERE po_id = :id AND (is_approved = 1 OR id = :curr_id)) 
                                  GROUP BY item_id, COALESCE(item_variation_option_id, 0)");
                $this->db->bind(':id', $grn->po_id);
                $this->db->bind(':curr_id', $grnId);
                $grnItems = $this->db->resultSet() ?: [];

                $receivedMap = [];
                foreach ($grnItems as $gItem) {
                    $key = $gItem->item_id . '_' . intval($gItem->item_variation_option_id);
                    $receivedMap[$key] = floatval($gItem->qty);
                }

                $fullyReceived = true;
                $anyReceived = false;
                foreach ($poItems as $pItem) {
                    $key = $pItem->item_id . '_' . intval($pItem->item_variation_option_id);
                    $receivedQty = isset($receivedMap[$key]) ? $receivedMap[$key] : 0.0;
                    
                    if ($receivedQty < floatval($pItem->qty) - 0.001) {
                        $fullyReceived = false;
                    }
                    if ($receivedQty > 0.001) {
                        $anyReceived = true;
                    }
                }

                $newPoStatus = 'Sent';
                if ($fullyReceived) {
                    $newPoStatus = 'Received';
                } elseif ($anyReceived) {
                    $newPoStatus = 'Partially Received';
                }

                $this->db->query("UPDATE purchase_orders SET status = :status WHERE id = :id");
                $this->db->bind(':status', $newPoStatus);
                $this->db->bind(':id', $grn->po_id);
                $this->db->execute();
            }

            // 6. Mark GRN as approved and save total_amount
            $this->db->query("UPDATE goods_receipt_notes SET is_approved = 1, approved_by = :uid, approved_at = NOW(), total_amount = :total WHERE id = :id");
            $this->db->bind(':uid', $userId);
            $this->db->bind(':total', $grandTotal);
            $this->db->bind(':id', $grnId);
            $this->db->execute();

            $this->db->commit();
            return true;
        } catch (Throwable $e) { 
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e; 
        }
    }
}