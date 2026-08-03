<?php
class SettingsController extends Controller {
    private $companyModel;
    private $perfModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . APP_URL . '/auth/login'); exit; }
        // Ensure only Admins or Managers can change company settings
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') {
            die("Access Denied: You do not have permission to view this module.");
        }
        $this->companyModel = $this->model('Company');
        $this->perfModel = $this->model('RepPerformance');
    }

    public function index() {
        $data = [
            'title' => 'Company Settings',
            'content_view' => 'settings/index',
            'settings' => $this->companyModel->getSettings(),
            'active_tab' => 'company',
            'csrf_token' => $this->generateCsrfToken(),
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Handle Profile Text Update
            if (isset($_POST['update_profile'])) {
                $postData = [
                    'company_name' => trim($_POST['company_name']),
                    'email' => trim($_POST['email']),
                    'phone' => trim($_POST['phone']),
                    'address' => trim($_POST['address']),
                    'tax_number' => trim($_POST['tax_number']),
                    'ecommerce_store_url' => trim($_POST['ecommerce_store_url'] ?? ''),
                    'facebook_page_id' => trim($_POST['facebook_page_id'] ?? ''),
                    'facebook_access_token' => trim($_POST['facebook_access_token'] ?? '')
                ];

                if (!empty($postData['company_name'])) {
                    $this->companyModel->updateSettings($postData);
                    $data['success'] = "Company profile updated successfully.";
                } else {
                    $data['error'] = "Company Name is required.";
                }
            }

            // Handle Logo Upload
            if (isset($_POST['upload_logo']) && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['logo']['tmp_name'];
                $fileName = $_FILES['logo']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    // Create secure unique filename
                    $newFileName = 'logo_' . time() . '.' . $fileExtension;
                    // Note: Ensure the "uploads" folder exists in your public directory!
                    $destPath = '../public/uploads/' . $newFileName;
                    
                    if(move_uploaded_file($fileTmpPath, $destPath)) {
                        $this->companyModel->updateLogo($newFileName);
                        $data['success'] = "Logo uploaded successfully.";
                    } else {
                        $data['error'] = "Failed to move uploaded file. Check folder permissions.";
                    }
                } else {
                    $data['error'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
                }
            }
            
            // Refresh settings data after updates
            $data['settings'] = $this->companyModel->getSettings();
        }

        $this->view('layouts/main', $data);
    }

    public function rep_targets() {
        $db = new Database();
        $db->query("SELECT u.id, u.username, e.first_name, e.last_name 
                    FROM users u
                    LEFT JOIN employees e ON u.employee_id = e.id
                    WHERE u.role = 'Rep (Sales Representative)'
                    ORDER BY u.username ASC");
        $reps = $db->resultSet() ?: [];

        $selectedRepId = isset($_GET['rep_user_id']) ? intval($_GET['rep_user_id']) : 0;
        if ($selectedRepId === 0 && !empty($reps)) {
            $selectedRepId = intval($reps[0]->id);
        }

        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        $data = [
            'title' => 'Rep Targets & KPI Weights',
            'content_view' => 'settings/rep_targets',
            'reps' => $reps,
            'selected_rep_id' => $selectedRepId,
            'month' => $month,
            'year' => $year,
            'rep_targets' => $this->perfModel->getRepTargets($selectedRepId, $month, $year),
            'kpi_configs' => $this->perfModel->getKpiConfigs(),
            'active_tab' => 'rep_targets',
            'csrf_token' => $this->generateCsrfToken(),
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check which form was submitted (Save Targets or Save Global Weights)
            if (isset($_POST['save_targets'])) {
                $postData = [
                    'user_id' => $selectedRepId,
                    'month' => $month,
                    'year' => $year,
                    'sales_target' => floatval($_POST['sales_target'] ?? 0.00),
                    'productive_visits_target' => intval($_POST['productive_visits_target'] ?? 0),
                    'total_visits_target' => intval($_POST['total_visits_target'] ?? 0),
                    'working_days_target' => intval($_POST['working_days_target'] ?? 0),
                    'credit_limit' => floatval($_POST['credit_limit'] ?? 0.00)
                ];
                $applyAll = !empty($_POST['apply_all_months']);
                
                $success = true;
                if ($applyAll) {
                    for ($m = 1; $m <= 12; $m++) {
                        $postData['month'] = str_pad($m, 2, '0', STR_PAD_LEFT);
                        if (!$this->perfModel->saveRepTargets($postData)) {
                            $success = false;
                        }
                    }
                } else {
                    $success = $this->perfModel->saveRepTargets($postData);
                }

                if ($success) {
                    $this->logActivity('Update Rep Targets', 'Analytics', "Updated targets for Rep User ID {$selectedRepId} for {$year}" . ($applyAll ? " (All Months)" : "-{$month}"));
                    $data['success'] = 'Representative targets updated successfully.';
                } else {
                    $data['error'] = 'Failed to save representative targets.';
                }
            } elseif (isset($_POST['save_weights'])) {
                $configs = $_POST['configs'] ?? [];
                if ($this->perfModel->updateKpiConfigs($configs)) {
                    $this->logActivity('Update KPI Settings', 'Analytics', 'Updated global performance weights.');
                    $data['success'] = 'Global KPI weights and constraints updated successfully.';
                } else {
                    $data['error'] = 'Failed to update global KPI weights.';
                }
            }

            // Refresh configurations and targets data
            $data['rep_targets'] = $this->perfModel->getRepTargets($selectedRepId, $month, $year);
            $data['kpi_configs'] = $this->perfModel->getKpiConfigs();
        }

        $this->view('layouts/main', $data);
    }
}