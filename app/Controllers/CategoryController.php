<?php
class CategoryController extends Controller {
    private $categoryModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        $this->categoryModel = $this->model('Category');
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $totalCats = $this->categoryModel->getTotalCategories($search);
        $totalPages = ceil($totalCats / $limit);

        $data = [
            'title' => 'Product Categories',
            'content_view' => 'categories/index',
            'categories' => $this->categoryModel->getCategoriesPaginated($search, $limit, $offset),
            'search' => $search,
            'page' => $page,
            'total_pages' => $totalPages,
            'total_cats' => $totalCats,
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] == 'add_category') {
                if ($this->categoryModel->addCategory(trim($_POST['name']), trim($_POST['description'] ?? ''))) {
                    header("Location: " . APP_URL . "/category?success=Category added successfully"); exit;
                } else { $data['error'] = 'Category name already exists.'; }
            } 
            elseif ($_POST['action'] == 'edit_category') {
                if ($this->categoryModel->updateCategory($_POST['category_id'], trim($_POST['name']), trim($_POST['description'] ?? ''))) {
                    header("Location: " . APP_URL . "/category?page=$page&search=$search&success=Category updated successfully"); exit;
                } else { $data['error'] = 'Failed to update category.'; }
            }
            elseif ($_POST['action'] == 'delete_category') {
                if ($this->categoryModel->deleteCategory($_POST['category_id'])) {
                    header("Location: " . APP_URL . "/category?success=Category deleted successfully"); exit;
                } else { $data['error'] = 'Failed to delete category.'; }
            }
        }

        if (isset($_GET['success'])) { $data['success'] = $_GET['success']; }
        $this->view('layouts/main', $data);
    }
}