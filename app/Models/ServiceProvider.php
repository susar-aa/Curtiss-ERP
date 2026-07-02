<?php
class ServiceProvider {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllServiceProviders() {
        $this->db->query("
            SELECT sp.*,
                   (SELECT COALESCE(SUM(gri.total), 0) 
                    FROM grn_items gri 
                    JOIN goods_receipt_notes grn ON gri.grn_id = grn.id 
                    WHERE grn.service_provider_id = sp.id) as total_billed,
                   (SELECT COALESCE(SUM(amount), 0) 
                    FROM expenses 
                    WHERE service_provider_id = sp.id) 
                   + 
                   (SELECT COALESCE(SUM(amount), 0) 
                    FROM supplier_payments 
                    WHERE service_provider_id = sp.id AND status = 'Active') as total_paid,
                   (SELECT COALESCE(SUM(total_amount), 0) 
                    FROM supplier_returns 
                    WHERE service_provider_id = sp.id) as total_returned
            FROM service_providers sp
            ORDER BY sp.name ASC
        ");
        $serviceProviders = $this->db->resultSet();
        foreach ($serviceProviders as $s) {
            $s->outstanding_balance = $s->total_billed - $s->total_paid - $s->total_returned;
        }
        return $serviceProviders;
    }

    public function getServiceProviderById($id) {
        $this->db->query("SELECT * FROM service_providers WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addServiceProvider($data) {
        $this->db->query("INSERT INTO service_providers (name, email, phone, address, service_category, status) VALUES (:name, :email, :phone, :address, :service_category, :status)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':service_category', $data['service_category']);
        $this->db->bind(':status', $data['status'] ?? 'Active');
        return $this->db->execute();
    }

    public function updateServiceProvider($data) {
        $this->db->query("UPDATE service_providers SET name = :name, email = :email, phone = :phone, address = :address, service_category = :service_category, status = :status WHERE id = :id");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':address', $data['address']);
        $this->db->bind(':service_category', $data['service_category']);
        $this->db->bind(':status', $data['status']);
        return $this->db->execute();
    }

    public function getServiceProviderStats($id) {
        // Total POs count and total PO amount
        $this->db->query("SELECT COUNT(id) as total_pos, COALESCE(SUM(total_amount), 0) as total_po_amount FROM purchase_orders WHERE service_provider_id = :id");
        $this->db->bind(':id', $id);
        $poStats = $this->db->single();
        
        // Total Billed (GRNs)
        $this->db->query("
            SELECT COALESCE(SUM(gri.total), 0) as total_billed 
             FROM grn_items gri 
             JOIN goods_receipt_notes grn ON gri.grn_id = grn.id 
             WHERE grn.service_provider_id = :id
        ");
        $this->db->bind(':id', $id);
        $billed = $this->db->single();

        // Total Paid (Expenses + Active Payments)
        $this->db->query("
            SELECT 
                (SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE service_provider_id = :id1) 
                + 
                (SELECT COALESCE(SUM(amount), 0) FROM supplier_payments WHERE service_provider_id = :id2 AND status = 'Active') 
                as total_paid
        ");
        $this->db->bind(':id1', $id);
        $this->db->bind(':id2', $id);
        $paid = $this->db->single();

        // Total Returned (Supplier Returns)
        $this->db->query("SELECT COALESCE(SUM(total_amount), 0) as total_returned FROM supplier_returns WHERE service_provider_id = :id");
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
            WHERE grn.service_provider_id = :vid1
            UNION ALL
            SELECT 'Expense' as type, e.id, CONCAT(e.reference, ' - ', e.description) as ref, e.expense_date as date,
                   e.amount as debit, 
                   0 as credit, 
                   e.created_at
            FROM expenses e
            WHERE e.service_provider_id = :vid2
            UNION ALL
            SELECT 'Supplier Return' as type, sr.id, sr.return_number as ref, sr.return_date as date,
                   sr.total_amount as debit, 
                   0 as credit, 
                   sr.created_at
            FROM supplier_returns sr
            WHERE sr.service_provider_id = :vid3
            UNION ALL
            SELECT 'Payment' as type, sp.id, CONCAT('Pay: ', sp.payment_method, IF(sp.reference != '', CONCAT(' (', sp.reference, ')'), '')) as ref, sp.payment_date as date,
                   sp.amount as debit, 
                   0 as credit, 
                   sp.created_at
            FROM supplier_payments sp
            WHERE sp.service_provider_id = :vid4 AND sp.status = 'Active'
            ORDER BY date ASC, created_at ASC
        ";
        $this->db->query($sql);
        $this->db->bind(':vid1', $id);
        $this->db->bind(':vid2', $id);
        $this->db->bind(':vid3', $id);
        $this->db->bind(':vid4', $id);
        $ledger = $this->db->resultSet();

        $balance = 0;
        foreach($ledger as $row) {
            $balance += $row->credit;
            $balance -= $row->debit;
            $row->balance = $balance;
        }
        return array_reverse($ledger);
    }

    public function getServiceProviderPOs($id) {
        $this->db->query("SELECT * FROM purchase_orders WHERE service_provider_id = :vid ORDER BY po_date DESC");
        $this->db->bind(':vid', $id);
        return $this->db->resultSet();
    }

    public function getServiceProviderProducts($id) {
        return [];
    }
}
