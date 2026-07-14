<?php
class DepositController extends Controller {
    private $depositModel;
    private $coaModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->depositModel = $this->model('Deposit');
        $this->coaModel = $this->model('ChartOfAccount');
    }



    public function index() {
        $this->checkPermission('accounting', 'view');

        $deposits = $this->depositModel->getAllDeposits();

        $data = [
            'title' => 'Bank Deposits Management',
            'content_view' => 'deposit/index',
            'deposits' => $deposits,
            'success' => $_GET['success'] ?? '',
            'error' => $_GET['error'] ?? ''
        ];

        $this->view('layouts/main', $data);
    }

    public function create() {
        $this->checkPermission('accounting', 'create_edit');

        $parentId = $this->coaModel->selfHealBankAccounts();
        $bankAccounts = $this->coaModel->getBankAccounts($parentId);
        $pendingCheques = $this->depositModel->getPendingCheques();

        $data = [
            'title' => 'New Bank Deposit',
            'content_view' => 'deposit/create',
            'bank_accounts' => $bankAccounts,
            'pending_cheques' => $pendingCheques,
            'deposit' => null,
            'deposit_cheques' => [],
            'error' => $_GET['error'] ?? ''
        ];

        $this->view('layouts/main', $data);
    }

    public function store() {
        $this->checkPermission('accounting', 'create_edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/deposit');
            exit;
        }

        $depositData = [
            'deposit_date' => $_POST['deposit_date'] ?? date('Y-m-d'),
            'destination_bank_account_id' => intval($_POST['destination_bank_account_id'] ?? 0),
            'cash_5000' => intval($_POST['cash_5000'] ?? 0),
            'cash_2000' => intval($_POST['cash_2000'] ?? 0),
            'cash_1000' => intval($_POST['cash_1000'] ?? 0),
            'cash_500' => intval($_POST['cash_500'] ?? 0),
            'cash_100' => intval($_POST['cash_100'] ?? 0),
            'cash_50' => intval($_POST['cash_50'] ?? 0),
            'cash_20' => intval($_POST['cash_20'] ?? 0),
            'cheques' => $_POST['cheques'] ?? []
        ];

        if (empty($depositData['destination_bank_account_id'])) {
            header('Location: ' . APP_URL . '/deposit/create?error=Please select a destination bank account.');
            exit;
        }

        $depositId = $this->depositModel->createDeposit($depositData, $_SESSION['user_id']);
        if ($depositId) {
            header('Location: ' . APP_URL . '/deposit?success=Deposit draft created successfully!');
        } else {
            header('Location: ' . APP_URL . '/deposit/create?error=Failed to save deposit draft.');
        }
        exit;
    }

    public function edit($id) {
        $this->checkPermission('accounting', 'create_edit');

        $deposit = $this->depositModel->getDepositById($id);
        if (!$deposit || $deposit->status !== 'Draft') {
            header('Location: ' . APP_URL . '/deposit?error=Only Draft deposits can be edited.');
            exit;
        }

        $items = $this->depositModel->getDepositItems($id);
        
        // Extract cheques that are currently in this deposit
        $depositCheques = [];
        foreach ($items as $item) {
            if ($item->cheque_id !== null) {
                $depositCheques[] = $item;
            }
        }

        $parentId = $this->coaModel->selfHealBankAccounts();
        $bankAccounts = $this->coaModel->getBankAccounts($parentId);
        
        // Pending cheques list should include both the general pending cheques and the cheques in this deposit so we can display them checkmarked.
        $pendingCheques = $this->depositModel->getPendingCheques();

        $data = [
            'title' => 'Edit Deposit - ' . $deposit->deposit_number,
            'content_view' => 'deposit/create',
            'bank_accounts' => $bankAccounts,
            'pending_cheques' => $pendingCheques,
            'deposit' => $deposit,
            'deposit_cheques' => $depositCheques,
            'error' => $_GET['error'] ?? ''
        ];

        $this->view('layouts/main', $data);
    }

    public function update($id) {
        $this->checkPermission('accounting', 'create_edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/deposit');
            exit;
        }

        $depositData = [
            'deposit_date' => $_POST['deposit_date'] ?? date('Y-m-d'),
            'destination_bank_account_id' => intval($_POST['destination_bank_account_id'] ?? 0),
            'cash_5000' => intval($_POST['cash_5000'] ?? 0),
            'cash_2000' => intval($_POST['cash_2000'] ?? 0),
            'cash_1000' => intval($_POST['cash_1000'] ?? 0),
            'cash_500' => intval($_POST['cash_500'] ?? 0),
            'cash_100' => intval($_POST['cash_100'] ?? 0),
            'cash_50' => intval($_POST['cash_50'] ?? 0),
            'cash_20' => intval($_POST['cash_20'] ?? 0),
            'cheques' => $_POST['cheques'] ?? []
        ];

        if (empty($depositData['destination_bank_account_id'])) {
            header('Location: ' . APP_URL . '/deposit/edit/' . $id . '?error=Please select a destination bank account.');
            exit;
        }

        $success = $this->depositModel->updateDeposit($id, $depositData, $_SESSION['user_id']);
        if ($success) {
            header('Location: ' . APP_URL . '/deposit?success=Deposit draft updated successfully!');
        } else {
            header('Location: ' . APP_URL . '/deposit/edit/' . $id . '?error=Failed to update deposit draft.');
        }
        exit;
    }

    public function delete($id) {
        $this->checkPermission('accounting', 'delete');

        $success = $this->depositModel->deleteDeposit($id);
        if ($success) {
            header('Location: ' . APP_URL . '/deposit?success=Deposit draft deleted successfully.');
        } else {
            header('Location: ' . APP_URL . '/deposit?error=Failed to delete deposit draft.');
        }
        exit;
    }

    public function send($id) {
        $this->checkPermission('accounting', 'create_edit');

        $result = $this->depositModel->sendToBank($id, $_SESSION['user_id']);
        if ($result === true) {
            header('Location: ' . APP_URL . '/deposit?success=Deposit sent to bank successfully. Transit entries posted!');
        } else {
            header('Location: ' . APP_URL . '/deposit?error=' . urlencode($result));
        }
        exit;
    }

    public function process($id) {
        $this->checkPermission('accounting', 'view');

        $deposit = $this->depositModel->getDepositById($id);
        if (!$deposit || $deposit->status === 'Draft') {
            header('Location: ' . APP_URL . '/deposit?error=Invalid deposit selected.');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->checkPermission('accounting', 'create_edit');

            if ($deposit->status !== 'Sent to Bank') {
                header('Location: ' . APP_URL . '/deposit?error=Only deposits Sent to Bank can be processed.');
                exit;
            }

            $processedData = [
                'accepted_cash_amount' => floatval($_POST['accepted_cash_amount'] ?? 0),
                'cheque_action' => $_POST['cheque_action'] ?? [],
                'rejection_reason' => $_POST['rejection_reason'] ?? [],
                'approval_remarks' => $_POST['approval_remarks'] ?? ''
            ];

            $result = $this->depositModel->processDeposit($id, $processedData, $_SESSION['user_id']);
            if ($result === true) {
                header('Location: ' . APP_URL . '/deposit?success=Deposit processed and realized successfully!');
            } else {
                header('Location: ' . APP_URL . '/deposit/process/' . $id . '?error=' . urlencode($result));
            }
            exit;
        }

        $items = $this->depositModel->getDepositItems($id);

        $data = [
            'title' => 'Process Deposit - ' . $deposit->deposit_number,
            'content_view' => 'deposit/process',
            'deposit' => $deposit,
            'items' => $items,
            'error' => $_GET['error'] ?? ''
        ];

        $this->view('layouts/main', $data);
    }

    public function printSlip($id) {
        $this->checkPermission('accounting', 'view');

        $deposit = $this->depositModel->getDepositById($id);
        if (!$deposit) {
            die("Deposit not found.");
        }

        $items = $this->depositModel->getDepositItems($id);
        $companyModel = $this->model('Company');
        $company = $companyModel->getSettings();

        $data = [
            'title' => 'Deposit Slip - ' . $deposit->deposit_number,
            'deposit' => $deposit,
            'items' => $items,
            'company' => $company
        ];

        $this->view('deposit/print', $data);
    }
}
