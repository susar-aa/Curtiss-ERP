<?php
class GRNController extends Controller {
    private $vendorModel;
    private $grnModel;
    private $poModel;
    private $itemModel;
    private $companyModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->vendorModel = $this->model('Vendor');
        $this->grnModel = $this->model('GRN');
        $this->poModel = $this->model('PurchaseOrder');
        $this->itemModel = $this->model('Item');
        $this->companyModel = $this->model('Company');
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $filters = ['vendor_id' => $_GET['vendor_id'] ?? ''];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_grn') {
            if ($this->grnModel->deleteGRN($_POST['grn_id'])) {
                header("Location: " . APP_URL . "/grn?success=GRN deleted and Inventory Stock reversed successfully"); exit;
            }
        }

        $totalGRNs = $this->grnModel->getTotalGRNs($search, $filters);
        
        $data = [
            'title' => 'Goods Receipt Notes (GRN)',
            'content_view' => 'grns/index',
            'grns' => $this->grnModel->getGRNsPaginated($search, $limit, $offset, $filters),
            'vendors' => $this->vendorModel->getAllVendors(),
            'search' => $search,
            'filters' => $filters,
            'page' => $page,
            'total_pages' => ceil($totalGRNs / $limit),
            'error' => '',
            'success' => $_GET['success'] ?? ''
        ];
        $this->view('layouts/main', $data);
    }

    public function show($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/grn'); exit; }
        $grn = $this->grnModel->getGRNById($id);
        if (!$grn) { die("GRN not found."); }

        $data = [
            'grn' => $grn,
            'items' => $this->grnModel->getGRNItems($id),
            'company' => $this->companyModel->getSettings()
        ];
        $this->view('grns/view', $data);
    }

    public function create() {
        $poId = $_GET['po_id'] ?? null;
        $prefilledVendor = '';
        $prefilledItems = [];
        $linkedPO = null;

        if ($poId) {
            $linkedPO = $this->poModel->getPOById($poId);
            $prefilledVendor = $linkedPO->vendor_id;
            $prefilledItems = $this->poModel->getPOItems($poId);
        }

        $catalogItems = $this->itemModel->getAllItems();
        foreach($catalogItems as $item) {
            $item->variations = $this->itemModel->getItemVariations($item->id);
        }

        $data = [
            'title' => 'Create Goods Receipt Note (GRN)',
            'content_view' => 'grns/create',
            'vendors' => $this->vendorModel->getAllVendors(),
            'catalog_items' => $catalogItems,
            'grn_number' => 'GRN-' . time(),
            'prefilled_vendor' => $prefilledVendor,
            'linked_po' => $linkedPO,
            'prefilled_items' => $prefilledItems,
            'error' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_grn') {
            $grnData = [
                'vendor_id' => $_POST['vendor_id'],
                'po_id' => !empty($_POST['po_id']) ? $_POST['po_id'] : null,
                'grn_number' => $_POST['grn_number'],
                'grn_date' => $_POST['grn_date'],
                'notes' => trim($_POST['notes'])
            ];
            
            $items = [];
            if (isset($_POST['item_selection'])) {
                for ($i = 0; $i < count($_POST['item_selection']); $i++) {
                    $selection = $_POST['item_selection'][$i];
                    if (!empty($selection) && $_POST['qty'][$i] > 0 && $_POST['price'][$i] >= 0) {
                        list($itemId, $varOptId) = explode('|', $selection);
                        $items[] = [
                            'item_id' => $itemId,
                            'var_opt_id' => ($varOptId === '0') ? null : $varOptId,
                            'desc' => $_POST['desc'][$i],
                            'qty' => $_POST['qty'][$i],
                            'price' => $_POST['price'][$i],
                            'selling_price' => floatval($_POST['selling_price'][$i] ?? 0)
                        ];
                    }
                }
            }

            if (empty($items)) { $data['error'] = 'You must add at least one item to receive.'; } 
            else {
                if ($this->grnModel->createGRN($grnData, $items, $_SESSION['user_id'])) { 
                    header('Location: ' . APP_URL . '/grn?success=GRN Created and Inventory Updated'); exit; 
                } else { $data['error'] = 'Database Error: Failed to create GRN.'; }
            }
        }
        $this->view('layouts/main', $data);
    }
}