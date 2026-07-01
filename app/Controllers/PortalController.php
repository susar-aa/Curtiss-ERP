<?php
class PortalController extends Controller {
    public function show($encodedId = null) {
        if (!$encodedId) die("Invalid Portal Link.");
        
        // Decode the ID back to an integer
        $id = (int) base64_decode($encodedId);
        
        require_once '../app/Models/Customer.php';
        require_once '../app/Models/Company.php';
        $customerModel = new Customer();
        $companyModel = new Company();
        
        $customer = $customerModel->getCustomerById($id);
        if (!$customer) die("Account not found or link has expired.");
        
        $data = [
            'customer' => $customer,
            'stats' => $customerModel->getCustomerStats($id),
            'ledger' => $customerModel->getActivityLedger($id),
            'invoices' => $customerModel->getCustomerInvoices($id, 50),
            'company' => $companyModel->getSettings()
        ];
        
        // Output directly without the main.php layout so it looks standalone
        $this->view('portal/index', $data);
    }
}