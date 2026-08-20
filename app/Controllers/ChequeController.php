<?php
class ChequeController extends Controller {
    private $chequeModel;
    private $customerModel;
    private $supplierModel;
    private $companyModel;

    public function __construct() {
        // auth bypassed
        $this->chequeModel = $this->model('Cheque');
        $this->customerModel = $this->model('Customer');
        $this->supplierModel = $this->model('Supplier');
        $this->companyModel = $this->model('Company');
        $this->coaModel = $this->model('ChartOfAccount');
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $cheques = $this->chequeModel->getAllCheques($search);
        $company = $this->companyModel->getSettings();
        $parentId = $this->coaModel->selfHealBankAccounts();
        $bankAccounts = $this->coaModel->getBankAccounts($parentId);
        
        // Calculate KPIs
        $totalPending = 0;
        $totalCleared = 0;
        $nextBankingDate = null;
        $nextBankingAmount = 0;

        $groupedReceivedCheques = [];
        $groupedIssuedCheques = [];

        foreach ($cheques as $chk) {
            // Group by Date for UI display
            $dateKey = date('Y-m-d', strtotime($chk->banking_date));
            
            if (!empty($chk->bank_account_id)) {
                // If it has a drawn bank_account_id, it's a payment/issued cheque.
                $groupedIssuedCheques[$dateKey][] = $chk;
            } else {
                // Collections don't have a drawn bank_account_id.
                $groupedReceivedCheques[$dateKey][] = $chk;
            }

            if ($chk->status == 'Pending') {
                $totalPending += $chk->amount;
                
                // Find nearest future/today date
                if ($nextBankingDate === null && strtotime($chk->banking_date) >= strtotime('today')) {
                    $nextBankingDate = $chk->banking_date;
                    $nextBankingAmount = $chk->amount;
                } elseif ($nextBankingDate === $chk->banking_date) {
                    $nextBankingAmount += $chk->amount;
                }
            } elseif ($chk->status == 'Cleared') {
                $totalCleared += $chk->amount;
            }
        }

        $data = [
            'title' => 'Cheque Management',
            'content_view' => 'cheques/index',
            'grouped_received_cheques' => $groupedReceivedCheques,
            'grouped_issued_cheques' => $groupedIssuedCheques,
            'search' => $search,
            'customers' => $this->customerModel->getAllCustomers() ?: [],
            'suppliers' => $this->supplierModel->getAllSuppliers() ?: [],
            'bank_accounts' => $bankAccounts,
            'company_name' => $company->company_name,
            'kpi_pending' => $totalPending,
            'kpi_cleared' => $totalCleared,
            'kpi_next_date' => $nextBankingDate,
            'kpi_next_amount' => $nextBankingAmount,
            'expense_accounts' => $this->coaModel->getExpenseAccounts(),
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] == 'add_cheque') {
                $chkData = [
                    'payee_name' => trim($_POST['payee_name']),
                    'bank_name' => trim($_POST['bank_name']),
                    'cheque_number' => trim($_POST['cheque_number']),
                    'amount' => floatval($_POST['amount']),
                    'banking_date' => $_POST['banking_date'],
                    'bank_account_id' => ($_POST['cheque_type'] == 'issued' && !empty($_POST['bank_account_id'])) ? intval($_POST['bank_account_id']) : null,
                    'created_by' => $_SESSION['user_id']
                ];
                if ($this->chequeModel->addCheque($chkData)) {
                    header('Location: ' . APP_URL . '/cheque?success=added'); exit;
                } else {
                    $data['error'] = 'Failed to add cheque.';
                }
            } elseif ($_POST['action'] == 'edit_cheque') {
                $chkData = [
                    'id' => $_POST['cheque_id'],
                    'payee_name' => trim($_POST['payee_name']),
                    'bank_name' => trim($_POST['bank_name']),
                    'cheque_number' => trim($_POST['cheque_number']),
                    'amount' => floatval($_POST['amount']),
                    'banking_date' => $_POST['banking_date'],
                    'bank_account_id' => ($_POST['cheque_type'] == 'issued' && !empty($_POST['bank_account_id'])) ? intval($_POST['bank_account_id']) : null,
                    'status' => $_POST['status']
                ];
                if ($this->chequeModel->updateCheque($chkData)) {
                    header('Location: ' . APP_URL . '/cheque?success=updated'); exit;
                } else {
                    $data['error'] = 'Failed to update cheque.';
                }
            } elseif ($_POST['action'] == 'delete_cheque') {
                if ($this->chequeModel->deleteCheque($_POST['delete_id'])) {
                    header('Location: ' . APP_URL . '/cheque?success=deleted'); exit;
                } else {
                    header('Location: ' . APP_URL . '/cheque?error=delete_failed'); exit;
                }
            } elseif ($_POST['action'] == 'process_return') {
                $cheque_id = $_POST['cheque_id'];
                $return_reason = $_POST['return_reason'] === 'Other' ? trim($_POST['other_reason']) : $_POST['return_reason'];
                $return_date = $_POST['return_date'];
                $return_charge = !empty($_POST['return_charge']) ? floatval($_POST['return_charge']) : 0;
                $charge_account_id = !empty($_POST['charge_account_id']) ? intval($_POST['charge_account_id']) : null;
                $returned_by = $_SESSION['user_id'];

                if ($this->chequeModel->returnCustomerCheque($cheque_id, $return_reason, $return_date, $return_charge, $charge_account_id, $returned_by)) {
                    header('Location: ' . APP_URL . '/cheque?success=Cheque marked as returned and customer payment reversed.'); exit;
                } else {
                    header('Location: ' . APP_URL . '/cheque?error=Failed to process cheque return.'); exit;
                }
            }
        }

        if (isset($_GET['success'])) {
            $data['success'] = "Cheque record " . htmlspecialchars($_GET['success']) . " successfully!";
        }

        if (isset($_GET['export']) && $_GET['export'] == 'true') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=cheques_export_' . date('Ymd_His') . '.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Cheque Number', 'Bank Name', 'Payee / Customer', 'Amount', 'Banking Date', 'Status', 'Type']);
            
            foreach ($cheques as $chk) {
                $type = !empty($chk->bank_account_id) ? 'Issued' : 'Received';
                fputcsv($output, [
                    $chk->cheque_number,
                    $chk->bank_name,
                    $chk->payee_name,
                    number_format((float)$chk->amount, 2, '.', ''),
                    $chk->banking_date,
                    $chk->status,
                    $type
                ]);
            }
            fclose($output);
            exit;
        }

        $this->view('layouts/main', $data);
    }
}
