<?php
class CollectionsController extends Controller {

    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->db = new Database();
    }

    // Display pending GL collections for finalization
    public function pending_collections() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Fetch pending collections
        $this->db->query("SELECT pc.*, c.name as customer_name, r.route_name, u.username
                          FROM pending_collections pc
                          LEFT JOIN customers c ON pc.customer_id = c.id
                          LEFT JOIN rep_daily_routes r ON pc.route_id = r.id
                          LEFT JOIN users u ON pc.created_by = u.id
                          WHERE pc.status = 'Pending'
                          ORDER BY pc.created_at DESC
                          LIMIT :offset, :limit");
        $this->db->bind(':offset', $offset);
        $this->db->bind(':limit', $perPage);
        $collections = $this->db->resultSet();

        // Get total count
        $this->db->query("SELECT COUNT(*) as total FROM pending_collections WHERE status = 'Pending'");
        $countResult = $this->db->single();
        $totalCount = $countResult->total ?? 0;
        $totalPages = ceil($totalCount / $perPage);

        // Calculate totals
        $totals = $this->calculatePendingTotals();

        $data = [
            'title' => 'Pending GL Collections',
            'collections' => $collections,
            'totals' => $totals,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount
        ];

        $this->view('accounting/pending_collections', $data);
    }

    // View details of a specific pending collection entry
    public function view_collection($collectionId) {
        $this->db->query("SELECT pc.*, c.name as customer_name, c.phone, c.address,
                                 r.route_name, u.username, u.first_name, u.last_name
                          FROM pending_collections pc
                          LEFT JOIN customers c ON pc.customer_id = c.id
                          LEFT JOIN rep_daily_routes r ON pc.route_id = r.id
                          LEFT JOIN users u ON pc.created_by = u.id
                          WHERE pc.id = :id");
        $this->db->bind(':id', $collectionId);
        $collection = $this->db->single();

        if (!$collection) {
            header('Location: ' . APP_URL . '/accounting/pending_collections?error=Collection not found');
            exit;
        }

        $data = [
            'title' => 'Collection Details',
            'collection' => $collection
        ];

        $this->view('accounting/view_collection', $data);
    }

    // Finalize (approve) a pending collection
    public function finalize_collection() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $collectionId = $_POST['collection_id'] ?? null;
            $notes = $_POST['notes'] ?? '';

            if (!$collectionId) {
                $_SESSION['error'] = 'Collection ID is required.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Fetch the pending collection
            $this->db->query("SELECT * FROM pending_collections WHERE id = :id");
            $this->db->bind(':id', $collectionId);
            $collection = $this->db->single();

            if (!$collection) {
                $_SESSION['error'] = 'Collection not found.';
                header('Location: ' . APP_URL . '/accounting/pending_collections');
                exit;
            }

            try {
                $this->db->beginTransaction();

                // 1. Create GL Entry for Collection
                $this->createCollectionGLEntry($collection);

                // 2. Update customer outstanding balance
                $this->updateCustomerBalance($collection->customer_id, $collection->amount);

                // 3. Mark collection as finalized
                $this->db->query("UPDATE pending_collections 
                                  SET status = 'Finalized', finalized_by = :uid, finalized_at = NOW(), notes = :notes
                                  WHERE id = :id");
                $this->db->bind(':id', $collectionId);
                $this->db->bind(':uid', $_SESSION['user_id']);
                $this->db->bind(':notes', $notes);
                $this->db->execute();

                $this->db->commit();
                $_SESSION['success'] = 'Collection finalized successfully!';
                header('Location: ' . APP_URL . '/accounting/pending_collections');
                exit;

            } catch (Exception $e) {
                $this->db->rollBack();
                $_SESSION['error'] = 'Failed to finalize collection: ' . $e->getMessage();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        }
    }

    // Reject a pending collection
    public function reject_collection() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $collectionId = $_POST['collection_id'] ?? null;
            $reason = $_POST['reason'] ?? '';

            if (!$collectionId) {
                $_SESSION['error'] = 'Collection ID is required.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            $this->db->query("UPDATE pending_collections 
                              SET status = 'Rejected', rejected_by = :uid, rejected_at = NOW(), notes = :reason
                              WHERE id = :id");
            $this->db->bind(':id', $collectionId);
            $this->db->bind(':uid', $_SESSION['user_id']);
            $this->db->bind(':reason', $reason);
            $this->db->execute();

            $_SESSION['success'] = 'Collection rejected.';
            header('Location: ' . APP_URL . '/accounting/pending_collections');
            exit;
        }
    }

    // Helper: Create GL entry for a finalized collection
    private function createCollectionGLEntry($collection) {
        // Get relevant account IDs from chart of accounts
        $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code IN ('1000', '1010', '1600', '1200')");
        $accounts = $this->db->resultSet();
        $accMap = [];
        foreach ($accounts as $a) {
            $accMap[$a->account_code] = $a->id;
        }

        $cashAcc = $accMap['1000'] ?? null;
        $chequeAcc = $accMap['1010'] ?? null;
        $bankAcc = $accMap['1600'] ?? null;
        $arAcc = $accMap['1200'] ?? null;

        // Create Journal Entry
        $method = $collection->payment_method;
        $description = "Collection - $method: Customer ID {$collection->customer_id}";
        if ($method === 'Cheque' && !empty($collection->cheque_number)) {
            $description .= " (Cheque #" . $collection->cheque_number . ")";
        }

        $this->db->query("INSERT INTO journal_entries (entry_date, reference, description, created_by, status)
                          VALUES (NOW(), :ref, :desc, :uid, 'Posted')");
        $this->db->bind(':ref', 'COLL-' . $collection->id);
        $this->db->bind(':desc', $description);
        $this->db->bind(':uid', $_SESSION['user_id']);
        $this->db->execute();
        $journalId = $this->db->lastInsertId();

        // Determine debit account based on payment method
        $debitAcc = null;
        switch ($method) {
            case 'Cash':
                $debitAcc = $cashAcc;
                break;
            case 'Cheque':
                $debitAcc = $chequeAcc;
                break;
            case 'Bank Transfer':
                $debitAcc = $bankAcc;
                break;
        }

        if (!$debitAcc || !$arAcc) {
            throw new Exception('Required chart of accounts not found');
        }

        // Double Entry: Debit Asset (Cash/Cheque/Bank), Credit AR
        $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit)
                          VALUES (:jid, :aid, :deb, 0)");
        $this->db->bind(':jid', $journalId);
        $this->db->bind(':aid', $debitAcc);
        $this->db->bind(':deb', $collection->amount);
        $this->db->execute();

        $this->db->query("UPDATE chart_of_accounts SET balance = balance + :amt WHERE id = :aid");
        $this->db->bind(':amt', $collection->amount);
        $this->db->bind(':aid', $debitAcc);
        $this->db->execute();

        $this->db->query("INSERT INTO transactions (journal_entry_id, account_id, debit, credit)
                          VALUES (:jid, :aid, 0, :cred)");
        $this->db->bind(':jid', $journalId);
        $this->db->bind(':aid', $arAcc);
        $this->db->bind(':cred', $collection->amount);
        $this->db->execute();

        $this->db->query("UPDATE chart_of_accounts SET balance = balance - :amt WHERE id = :aid");
        $this->db->bind(':amt', $collection->amount);
        $this->db->bind(':aid', $arAcc);
        $this->db->execute();
    }

    // Helper: Update customer outstanding balance
    private function updateCustomerBalance($customerId, $amount) {
        $this->db->query("UPDATE customers SET outstanding = outstanding - :amt WHERE id = :id");
        $this->db->bind(':amt', $amount);
        $this->db->bind(':id', $customerId);
        $this->db->execute();
    }

    // Helper: Calculate totals for pending collections
    private function calculatePendingTotals() {
        $this->db->query("SELECT 
                            payment_method,
                            COUNT(*) as count,
                            SUM(amount) as total
                          FROM pending_collections
                          WHERE status = 'Pending'
                          GROUP BY payment_method");
        $results = $this->db->resultSet();

        $totals = [
            'cash' => 0,
            'bank' => 0,
            'cheque' => 0,
            'total' => 0,
            'count' => 0
        ];

        foreach ($results as $row) {
            $amount = floatval($row->total ?? 0);
            $totals['total'] += $amount;
            $totals['count'] += $row->count ?? 0;

            if ($row->payment_method === 'Cash') {
                $totals['cash'] = $amount;
            } elseif ($row->payment_method === 'Bank Transfer') {
                $totals['bank'] = $amount;
            } elseif ($row->payment_method === 'Cheque') {
                $totals['cheque'] = $amount;
            }
        }

        return $totals;
    }
}
