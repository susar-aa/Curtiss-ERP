<?php
class Supplier {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllSuppliers() {
        // Calculates the billed (GRNs), paid (Expenses), and returned (Supplier Returns) to determine outstanding balance
        $this->db->query("
            SELECT v.*,
                   (SELECT COALESCE(SUM(gri.total), 0) 
                    FROM grn_items gri 
                    JOIN goods_receipt_notes grn ON gri.grn_id = grn.id 
                    WHERE grn.vendor_id = v.id) as total_billed,
                   (SELECT COALESCE(SUM(amount), 0) 
                    FROM expenses 
                    WHERE vendor_id = v.id) as total_paid,
                   (SELECT COALESCE(SUM(total_amount), 0) 
                    FROM supplier_returns 
                    WHERE vendor_id = v.id) as total_returned
            FROM vendors v
            ORDER BY v.name ASC
        ");
        $suppliers = $this->db->resultSet();
        foreach ($suppliers as $s) {
            $s->outstanding_balance = $s->total_billed - $s->total_paid - $s->total_returned;
        }
        return $suppliers;
    }

    public function getSupplierById($id) {
        $this->db->query("SELECT * FROM vendors WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addSupplier($data) {
        $this->db->query("INSERT INTO vendors (name, email, phone, address) VALUES (:name, :email, :phone, :address)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        return $this->db->execute();
    }

    public function updateSupplier($data) {
        $this->db->query("UPDATE vendors SET name = :name, email = :email, phone = :phone, address = :address WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        return $this->db->execute();
    }

    public function getSupplierStats($id) {
        // Total POs count and total PO amount
        $this->db->query("SELECT COUNT(id) as total_pos, COALESCE(SUM(total_amount), 0) as total_po_amount FROM purchase_orders WHERE vendor_id = :id");
        $this->db->bind(':id', $id);
        $poStats = $this->db->single();
        
        // Total Billed (GRNs)
        $this->db->query("
            SELECT COALESCE(SUM(gri.total), 0) as total_billed 
             FROM grn_items gri 
             JOIN goods_receipt_notes grn ON gri.grn_id = grn.id 
             WHERE grn.vendor_id = :id
        ");
        $this->db->bind(':id', $id);
        $billed = $this->db->single();

        // Total Paid (Expenses)
        $this->db->query("SELECT COALESCE(SUM(amount), 0) as total_paid FROM expenses WHERE vendor_id = :id");
        $this->db->bind(':id', $id);
        $paid = $this->db->single();

        // Total Returned (Supplier Returns)
        $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total_returned FROM supplier_returns WHERE vendor_id = :id");
        $this->db->bind(':id', $id);
        $returned = $this->db->single();

        $stats = new stdClass();
        $stats->total_pos = $poStats->total_pos;
        $stats->total_po_amount = $poStats->total_po_amount;
        $stats->total_billed = $billed->total_billed;
        $stats->total_paid = $paid->total_paid;
        $stats->total_returned = $returned->total_returned;
        $stats->outstanding = $stats->total_billed - $stats->total_paid - $stats->total_returned;

        return $stats;
    }

    public function getActivityLedger($id) {
        $sql = "
            SELECT 'GRN' as type, grn.id, grn.grn_number as ref, grn.grn_date as date,
                   0 as debit, 
                   (SELECT COALESCE(SUM(total), 0) FROM grn_items WHERE grn_id = grn.id) as credit, 
                   grn.created_at
            FROM goods_receipt_notes grn
            WHERE grn.vendor_id = :vid1
            UNION ALL
            SELECT 'Expense' as type, e.id, CONCAT(e.reference, ' - ', e.description) as ref, e.expense_date as date,
                   e.amount as debit, 
                   0 as credit, 
                   e.created_at
            FROM expenses e
            WHERE e.vendor_id = :vid2
            UNION ALL
            SELECT 'Supplier Return' as type, sr.id, sr.return_number as ref, sr.return_date as date,
                   sr.total_amount as debit, 
                   0 as credit, 
                   sr.created_at
            FROM supplier_returns sr
            WHERE sr.vendor_id = :vid3
            ORDER BY date ASC, created_at ASC
        ";
        $this->db->query($sql);
        $this->db->bind(':vid1', $id);
        $this->db->bind(':vid2', $id);
        $this->db->bind(':vid3', $id);
        $ledger = $this->db->resultSet();

        $balance = 0;
        foreach($ledger as $row) {
            $balance += $row->credit;
            $balance -= $row->debit;
            $row->balance = $balance;
        }
        return array_reverse($ledger);
    }

    public function getSupplierPOs($id) {
        $this->db->query("SELECT * FROM purchase_orders WHERE vendor_id = :vid ORDER BY po_date DESC");
        $this->db->bind(':vid', $id);
        return $this->db->resultSet();
    }

    public function getSupplierProducts($id) {
        $this->db->query("
            SELECT i.id as item_id, ivo.id as var_opt_id,
                   CASE 
                       WHEN ivo.id IS NOT NULL THEN CONCAT(i.name, ' - ', v.name, ': ', vv.value_name)
                       ELSE i.name 
                   END as product_name,
                   i.sku,
                   COALESCE(ivo.price, i.price) as price,
                   COALESCE(ivo.cost, i.cost) as cost,
                   COALESCE(ivo.quantity_on_hand, i.quantity_on_hand) as quantity_on_hand
            FROM items i
            LEFT JOIN item_variation_options ivo ON ivo.item_id = i.id
            LEFT JOIN variations v ON ivo.variation_id = v.id
            LEFT JOIN variation_values vv ON ivo.variation_value_id = vv.id
            WHERE i.vendor_id = :vid
            ORDER BY product_name ASC
        ");
        $this->db->bind(':vid', $id);
        return $this->db->resultSet();
    }

    // --- Legacy Vendor Model Aliases for System-wide Compatibility ---
    public function getAllVendors() {
        return $this->getAllSuppliers();
    }

    public function getVendorById($id) {
        return $this->getSupplierById($id);
    }

    public function addVendor($data) {
        return $this->addSupplier($data);
    }

    public function updateVendor($data) {
        return $this->updateSupplier($data);
    }

    public function getVendorExpenses($vendorId) {
        $this->db->query("SELECT * FROM expenses WHERE vendor_id = :vid ORDER BY expense_date DESC");
        $this->db->bind(':vid', $vendorId);
        return $this->db->resultSet();
    }

    public function getVendorPOs($vendorId) {
        return $this->getSupplierPOs($vendorId);
    }
}
