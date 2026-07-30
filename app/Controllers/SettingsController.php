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
        $data = [
            'title' => 'Rep KPI Targets Settings',
            'content_view' => 'settings/rep_targets',
            'kpi_configs' => $this->perfModel->getKpiConfigs(),
            'active_tab' => 'rep_targets',
            'csrf_token' => $this->generateCsrfToken(),
            'error' => '',
            'success' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $configs = $_POST['configs'] ?? [];
            if ($this->perfModel->updateKpiConfigs($configs)) {
                $this->logActivity('Update KPI Settings', 'Analytics', 'Updated performance targets and scoring weights.');
                $data['success'] = 'KPI configurations and weights saved successfully.';
            } else {
                $data['error'] = 'Failed to update KPI weights settings.';
            }
            // reload config list
            $data['kpi_configs'] = $this->perfModel->getKpiConfigs();
        }

        $this->view('layouts/main', $data);
    }
}