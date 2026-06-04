<?php
class EcommerceController extends Controller {
    private $db;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        // Admin or Manager access only
        if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Manager') {
            die("Access Denied: You do not have permission to access E-Commerce operations.");
        }
        $this->db = new Database();
    }

    public function requests() {
        $success = '';
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            $requestId = intval($_POST['request_id']);

            if ($action === 'approve') {
                $linkAction = $_POST['link_action'] ?? 'create_new';
                $username = trim($_POST['approve_username'] ?? '');
                $password = $_POST['approve_password'] ?? '';

                if (empty($username) || empty($password)) {
                    $error = 'Username and Password are required to approve wholesaler profiles.';
                } else {
                    try {
                        $this->db->beginTransaction();

                        // Fetch request details
                        $this->db->query("SELECT * FROM wholesaler_requests WHERE id = :id");
                        $this->db->bind(':id', $requestId);
                        $req = $this->db->single();

                        if (!$req) {
                            throw new Exception("Wholesaler request not found.");
                        }

                        $customerId = null;

                        if ($linkAction === 'link_existing') {
                            $customerId = intval($_POST['existing_customer_id']);
                            
                            // Check if customer exists
                            $this->db->query("SELECT id FROM customers WHERE id = :cid");
                            $this->db->bind(':cid', $customerId);
                            if (!$this->db->single()) {
                                throw new Exception("Selected ERP customer profile does not exist.");
                            }

                            // Update existing customer credentials
                            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                            $this->db->query("UPDATE customers SET username = :username, password = :pass, email = :email WHERE id = :id");
                            $this->db->bind(':username', $username);
                            $this->db->bind(':pass', $hashedPassword);
                            $this->db->bind(':email', $req->email_address);
                            $this->db->bind(':id', $customerId);
                            $this->db->execute();

                        } else {
                            // Create new customer profile
                            $this->db->query("INSERT INTO customers (name, email, username, password, phone, address, territory) 
                                              VALUES (:name, :email, :username, :pass, :phone, :address, :territory)");
                            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                            $this->db->bind(':name', $req->business_name);
                            $this->db->bind(':email', $req->email_address);
                            $this->db->bind(':username', $username);
                            $this->db->bind(':pass', $hashedPassword);
                            $this->db->bind(':phone', $req->contact_number);
                            $this->db->bind(':address', $req->address);
                            $this->db->bind(':territory', $req->city);
                            $this->db->execute();
                            $customerId = $this->db->lastInsertId();
                        }

                        // Update wholesaler request status
                        $this->db->query("UPDATE wholesaler_requests SET status = 'approved', linked_customer_id = :cid WHERE id = :id");
                        $this->db->bind(':cid', $customerId);
                        $this->db->bind(':id', $requestId);
                        $this->db->execute();

                        $this->db->commit();
                        $success = "Wholesaler request approved successfully. Credentials linked to ERP Customer profile.";
                    } catch (Exception $e) {
                        $this->db->rollBack();
                        $error = "Error: " . $e->getMessage();
                    }
                }
            } elseif ($action === 'decline') {
                $this->db->query("UPDATE wholesaler_requests SET status = 'declined' WHERE id = :id");
                $this->db->bind(':id', $requestId);
                if ($this->db->execute()) {
                    $success = "Wholesaler request declined.";
                } else {
                    $error = "Failed to update wholesaler request status.";
                }
            }
        }

        // Fetch requests
        $this->db->query("SELECT r.*, c.name as linked_customer_name FROM wholesaler_requests r 
                          LEFT JOIN customers c ON r.linked_customer_id = c.id 
                          ORDER BY r.created_at DESC");
        $requests = $this->db->resultSet() ?: [];

        // Fetch all ERP customers for linking dropdown
        $this->db->query("SELECT id, name, email, phone FROM customers ORDER BY name ASC");
        $erpCustomers = $this->db->resultSet() ?: [];

        $data = [
            'title' => 'Wholesaler Requests',
            'content_view' => 'ecommerce/requests',
            'requests' => $requests,
            'erp_customers' => $erpCustomers,
            'success' => $success,
            'error' => $error
        ];

        $this->view('layouts/main', $data);
    }

    public function retail() {
        // Fetch retail customers directory
        $this->db->query("SELECT * FROM ecommerce_retail_customers ORDER BY name ASC");
        $retailCustomers = $this->db->resultSet() ?: [];

        $data = [
            'title' => 'Retail Customers',
            'content_view' => 'ecommerce/retail',
            'customers' => $retailCustomers
        ];

        $this->view('layouts/main', $data);
    }
}
