<?php
class VariationController extends Controller {
    private $variationModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->variationModel = $this->model('Variation');
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $totalVars = $this->variationModel->getTotalVariations($search);
        $totalPages = ceil($totalVars / $limit);

        $data = [
            'title' => 'Product Variations',
            'content_view' => 'variations/index',
            'variations' => $this->variationModel->getVariationsPaginated($search, $limit, $offset),
            'search' => $search,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_vars' => $totalVars,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            $name = trim($_POST['name']);
            $desc = trim($_POST['description'] ?? '');
            $valuesArray = explode(',', $_POST['values']); // Comma separated values from UI

            if ($_POST['action'] == 'add_variation') {
                if ($this->variationModel->addVariation($name, $desc, $valuesArray)) {
                    header("Location: " . APP_URL . "/variation?success=Variation added successfully"); exit;
                } else { $data['error'] = 'Variation name already exists or database error.'; }
            } 
            elseif ($_POST['action'] == 'edit_variation') {
                if ($this->variationModel->updateVariation($_POST['variation_id'], $name, $desc, $valuesArray)) {
                    header("Location: " . APP_URL . "/variation?page=$page&search=$search&success=Variation updated successfully"); exit;
                } else { $data['error'] = 'Failed to update variation.'; }
            }
            elseif ($_POST['action'] == 'delete_variation') {
                if ($this->variationModel->deleteVariation($_POST['variation_id'])) {
                    header("Location: " . APP_URL . "/variation?success=Variation deleted successfully"); exit;
                } else { $data['error'] = 'Failed to delete variation.'; }
            }
        }

        if (isset($_GET['success'])) { $data['success'] = $_GET['success']; }
        $this->view('layouts/main', $data);
    }
}