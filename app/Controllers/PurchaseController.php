<?php
class PurchaseController extends Controller {
    private $vendorModel;
    private $poModel;
    private $itemModel;
    private $companyModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->vendorModel = $this->model('Vendor');
        $this->poModel = $this->model('PurchaseOrder');
        $this->itemModel = $this->model('Item');
        $this->companyModel = $this->model('Company');
    }

    public function index() {
        $data = [
            'title' => 'Procurement & POs',
            'content_view' => 'purchases/index',
            'pos' => $this->poModel->getAllPOs(),
            'error' => '',
            'success' => ''
        ];
        $this->view('layouts/main', $data);
    }

    public function show($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/purchase'); exit; }

        $po = $this->poModel->getPOById($id);
        if (!$po) { die("Purchase Order not found."); }

        $data = [
            'po' => $po,
            'items' => $this->poModel->getPOItems($id),
            'company' => $this->companyModel->getSettings()
        ];
        
        $this->view('purchases/po_view', $data);
    }

    public function create() {
        $data = [
            'title' => 'Create Purchase Order',
            'content_view' => 'purchases/create',
            'vendors' => $this->vendorModel->getAllVendors(),
            'catalog_items' => $this->itemModel->getAllItems(),
            'po_number' => 'PO-' . time(),
            'error' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $poData = [
                'vendor_id' => $_POST['vendor_id'],
                'po_number' => $_POST['po_number'],
                'po_date' => $_POST['po_date'],
                'expected_date' => $_POST['expected_date']
            ];
            
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
                if ($this->poModel->createPO($poData, $items, $_SESSION['user_id'])) {
                    header('Location: ' . APP_URL . '/purchase?success=1');
                    exit;
                } else {
                    $data['error'] = 'Database Error: Failed to create Purchase Order.';
                }
            }
        }
        $this->view('layouts/main', $data);
    }
}