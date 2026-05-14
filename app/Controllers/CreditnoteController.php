<?php
class CreditNoteController extends Controller {
    private $customerModel;
    private $creditNoteModel;
    private $coaModel;
    private $companyModel;
    private $itemModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->customerModel = $this->model('Customer');
        $this->creditNoteModel = $this->model('CreditNote');
        $this->coaModel = $this->model('ChartOfAccount');
        $this->companyModel = $this->model('Company');
        $this->itemModel = $this->model('Item');
    }

    public function index() {
        $data = [
            'title' => 'Credit Notes & Refunds',
            'content_view' => 'credit_notes/index',
            'credit_notes' => $this->creditNoteModel->getAllCreditNotes(),
            'error' => '',
            'success' => ''
        ];

        $this->view('layouts/main', $data);
    }

    public function show($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/creditnote'); exit; }

        $creditNote = $this->creditNoteModel->getCreditNoteById($id);
        if (!$creditNote) { die("Credit Note not found."); }

        $data = [
            'credit_note' => $creditNote,
            'items' => $this->creditNoteModel->getCreditNoteItems($id),
            'company' => $this->companyModel->getSettings()
        ];

        $this->view('credit_notes/show', $data);
    }

    public function create() {
        $accounts = $this->coaModel->getAccounts();
        $assets = array_filter($accounts, function($a) { return $a->account_type == 'Asset'; });
        $revenues = array_filter($accounts, function($a) { return $a->account_type == 'Revenue'; });

        $data = [
            'title' => 'Issue Credit Note',
            'content_view' => 'credit_notes/create',
            'customers' => $this->customerModel->getAllCustomers(),
            'assets' => $assets,
            'revenues' => $revenues,
            'catalog_items' => $this->itemModel->getAllItems(), 
            'credit_note_number' => 'CN-' . time(),
            'error' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cnData = [
                'customer_id' => $_POST['customer_id'],
                'credit_note_number' => $_POST['credit_note_number'],
                'date' => $_POST['note_date']
            ];
            $arAccount = $_POST['ar_account'];
            $revAccount = $_POST['revenue_account'];
            
            $items = [];
            for ($i = 0; $i < count($_POST['desc']); $i++) {
                if (!empty($_POST['desc'][$i]) && $_POST['qty'][$i] > 0 && $_POST['price'][$i] >= 0) {
                    $items[] = [
                        'desc' => $_POST['desc'][$i],
                        'qty' => $_POST['qty'][$i],
                        'price' => $_POST['price'][$i]
                    ];
                }
            }

            if (empty($items)) {
                $data['error'] = 'You must add at least one item.';
            } else {
                if ($this->creditNoteModel->createCreditNoteWithAccounting($cnData, $items, $arAccount, $revAccount, $_SESSION['user_id'])) {
                    header('Location: ' . APP_URL . '/creditnote?success=1');
                    exit;
                } else {
                    $data['error'] = 'Database Error: Failed to issue credit note and post ledger entries.';
                }
            }
        }

        $this->view('layouts/main', $data);
    }
}