<?php

class DiscountController extends Controller {
    private $discountModel;
    private $itemModel;
    private $categoryModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . APP_URL . '/auth/login');
            exit;
        }
        $this->discountModel = $this->model('DiscountRule');
        $this->itemModel = $this->model('Item');
        $this->categoryModel = $this->model('Category');
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $ruleType = $_GET['rule_type'] ?? '';
        $status = $_GET['status'] ?? '';

        $filters = [
            'search' => $search,
            'rule_type' => $ruleType,
            'status' => $status
        ];

        $rules = $this->discountModel->getAllRules($filters);
        $items = $this->itemModel->getItems() ?: [];
        $categories = $this->categoryModel->getCategories() ?: [];

        // Summary metrics
        $allRules = $this->discountModel->getAllRules();
        $totalRules = count($allRules);
        $activeRules = 0;
        $itemWiseRules = 0;
        $billWiseRules = 0;
        $categoryWiseRules = 0;
        $expiredRules = 0;

        foreach ($allRules as $r) {
            if ($r->status === 'Active') $activeRules++;
            if ($r->rule_type === 'item_wise') $itemWiseRules++;
            if ($r->rule_type === 'bill_wise') $billWiseRules++;
            if ($r->rule_type === 'category_wise') $categoryWiseRules++;
            if ($r->is_expired) $expiredRules++;
        }

        $metrics = [
            'total' => $totalRules,
            'active' => $activeRules,
            'item_wise' => $itemWiseRules,
            'bill_wise' => $billWiseRules,
            'category_wise' => $categoryWiseRules,
            'expired' => $expiredRules
        ];

        $data = [
            'title' => 'Customizable Discount Feed',
            'content_view' => 'discounts/index',
            'rules' => $rules,
            'items' => $items,
            'categories' => $categories,
            'filters' => $filters,
            'metrics' => $metrics,
            'error' => '',
            'success' => ''
        ];

        if (isset($_GET['success'])) {
            if ($_GET['success'] == 'add') {
                $data['success'] = "New discount rule configured successfully!";
            } elseif ($_GET['success'] == 'update') {
                $data['success'] = "Discount rule updated successfully!";
            } elseif ($_GET['success'] == 'delete') {
                $data['success'] = "Discount rule deleted successfully!";
            } elseif ($_GET['success'] == 'toggle') {
                $data['success'] = "Discount rule status updated!";
            } elseif ($_GET['success'] == 'duplicate') {
                $data['success'] = "Discount rule duplicated successfully!";
            }
        }

        $this->view('layouts/main', $data);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/discount');
            exit;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $rule_type = isset($_POST['rule_type']) ? trim($_POST['rule_type']) : '';
        $reward_type = isset($_POST['reward_type']) ? trim($_POST['reward_type']) : 'free_issue';
        $target_item_id = isset($_POST['target_item_id']) && $_POST['target_item_id'] !== '' ? intval($_POST['target_item_id']) : null;
        $target_category_id = isset($_POST['target_category_id']) && $_POST['target_category_id'] !== '' ? intval($_POST['target_category_id']) : null;
        $start_date = isset($_POST['start_date']) && $_POST['start_date'] !== '' ? trim($_POST['start_date']) : null;
        $end_date = isset($_POST['end_date']) && $_POST['end_date'] !== '' ? trim($_POST['end_date']) : null;
        $discount_cap = isset($_POST['discount_cap']) && $_POST['discount_cap'] !== '' ? floatval($_POST['discount_cap']) : null;
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $status = isset($_POST['status']) ? trim($_POST['status']) : 'Active';

        if (empty($name) || empty($rule_type)) {
            $_SESSION['discount_error'] = "Rule Name and Rule Type are required.";
            header('Location: ' . APP_URL . '/discount');
            exit;
        }

        // Parse Tiers
        $tiers = $this->parseTiersFromPost();
        if (empty($tiers)) {
            $_SESSION['discount_error'] = "At least one valid discount tier must be configured.";
            header('Location: ' . APP_URL . '/discount');
            exit;
        }

        $addData = [
            'name' => $name,
            'rule_type' => $rule_type,
            'reward_type' => $reward_type,
            'target_item_id' => $target_item_id,
            'target_category_id' => $target_category_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'discount_cap' => $discount_cap,
            'description' => $description,
            'status' => $status
        ];

        $ruleId = $this->discountModel->addRule($addData, $tiers);
        if ($ruleId) {
            $this->logActivity('Create Discount', 'Billing', "Created discount rule ID {$ruleId}: {$name} ({$rule_type})");
            header('Location: ' . APP_URL . '/discount?success=add');
            exit;
        } else {
            $_SESSION['discount_error'] = "Failed to configure new discount rule in database.";
            header('Location: ' . APP_URL . '/discount');
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_URL . '/discount');
            exit;
        }

        $_POST = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);

        $id = isset($_POST['rule_id']) ? intval($_POST['rule_id']) : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $rule_type = isset($_POST['rule_type']) ? trim($_POST['rule_type']) : '';
        $reward_type = isset($_POST['reward_type']) ? trim($_POST['reward_type']) : 'free_issue';
        $target_item_id = isset($_POST['target_item_id']) && $_POST['target_item_id'] !== '' ? intval($_POST['target_item_id']) : null;
        $target_category_id = isset($_POST['target_category_id']) && $_POST['target_category_id'] !== '' ? intval($_POST['target_category_id']) : null;
        $start_date = isset($_POST['start_date']) && $_POST['start_date'] !== '' ? trim($_POST['start_date']) : null;
        $end_date = isset($_POST['end_date']) && $_POST['end_date'] !== '' ? trim($_POST['end_date']) : null;
        $discount_cap = isset($_POST['discount_cap']) && $_POST['discount_cap'] !== '' ? floatval($_POST['discount_cap']) : null;
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $status = isset($_POST['status']) ? trim($_POST['status']) : 'Active';

        if (!$id || empty($name) || empty($rule_type)) {
            $_SESSION['discount_error'] = "Valid Rule ID, Name, and Type are required.";
            header('Location: ' . APP_URL . '/discount');
            exit;
        }

        $tiers = $this->parseTiersFromPost();
        if (empty($tiers)) {
            $_SESSION['discount_error'] = "At least one valid discount tier must be configured.";
            header('Location: ' . APP_URL . '/discount');
            exit;
        }

        $updateData = [
            'name' => $name,
            'rule_type' => $rule_type,
            'reward_type' => $reward_type,
            'target_item_id' => $target_item_id,
            'target_category_id' => $target_category_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'discount_cap' => $discount_cap,
            'description' => $description,
            'status' => $status
        ];

        if ($this->discountModel->updateRule($id, $updateData, $tiers)) {
            $this->logActivity('Update Discount', 'Billing', "Updated discount rule ID {$id}: {$name}");
            header('Location: ' . APP_URL . '/discount?success=update');
            exit;
        } else {
            $_SESSION['discount_error'] = "Failed to update discount rule.";
            header('Location: ' . APP_URL . '/discount');
            exit;
        }
    }

    public function duplicate($id) {
        if ($this->discountModel->duplicateRule($id)) {
            $this->logActivity('Duplicate Discount', 'Billing', "Duplicated discount rule ID {$id}");
            header('Location: ' . APP_URL . '/discount?success=duplicate');
            exit;
        }
        $_SESSION['discount_error'] = "Failed to duplicate discount rule.";
        header('Location: ' . APP_URL . '/discount');
        exit;
    }

    public function delete($id) {
        $rule = $this->discountModel->getRuleById($id);
        if ($rule) {
            if ($this->discountModel->deleteRule($id)) {
                $this->logActivity('Delete Discount', 'Billing', "Deleted discount rule ID {$id}: {$rule->name}");
                header('Location: ' . APP_URL . '/discount?success=delete');
                exit;
            }
        }
        header('Location: ' . APP_URL . '/discount');
        exit;
    }

    public function toggle($id) {
        $rule = $this->discountModel->getRuleById($id);
        if ($rule) {
            $newStatus = ($rule->status === 'Active') ? 'Inactive' : 'Active';
            if ($this->discountModel->toggleRuleStatus($id, $newStatus)) {
                $this->logActivity('Toggle Discount Status', 'Billing', "Updated status of discount rule ID {$id} to {$newStatus}");
                header('Location: ' . APP_URL . '/discount?success=toggle');
                exit;
            }
        }
        header('Location: ' . APP_URL . '/discount');
        exit;
    }

    public function api_get_rule($id) {
        header('Content-Type: application/json');
        $rule = $this->discountModel->getRuleById($id);
        if ($rule) {
            echo json_encode(['status' => 'success', 'data' => $rule]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Rule not found']);
        }
        exit;
    }

    public function api_get_active_rules() {
        header('Content-Type: application/json');
        $rules = $this->discountModel->getActiveRules();
        echo json_encode($rules);
        exit;
    }

    public function api_test_rule() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $billSubtotal = floatval($input['bill_subtotal'] ?? 0);
        $itemQty = floatval($input['item_qty'] ?? 0);
        $targetItemId = isset($input['item_id']) ? intval($input['item_id']) : null;
        $targetCategoryId = isset($input['category_id']) ? intval($input['category_id']) : null;

        $activeRules = $this->discountModel->getActiveRules();
        $matchedRules = [];

        foreach ($activeRules as $rule) {
            if ($rule->rule_type === 'item_wise' && $targetItemId && intval($rule->target_item_id) === $targetItemId) {
                foreach ($rule->tiers as $t) {
                    if ($itemQty >= floatval($t->min_threshold)) {
                        $matchedRules[] = [
                            'rule_name' => $rule->name,
                            'rule_type' => $rule->rule_type,
                            'reward_type' => $rule->reward_type,
                            'matched_tier' => "Buy >= {$t->min_threshold}",
                            'reward' => ($rule->reward_type === 'free_issue') ? "{$t->reward_val} Free Units" : "{$t->reward_val}% Discount"
                        ];
                        break;
                    }
                }
            } elseif ($rule->rule_type === 'category_wise' && $targetCategoryId && intval($rule->target_category_id) === $targetCategoryId) {
                foreach ($rule->tiers as $t) {
                    if ($itemQty >= floatval($t->min_threshold)) {
                        $matchedRules[] = [
                            'rule_name' => $rule->name,
                            'rule_type' => $rule->rule_type,
                            'reward_type' => $rule->reward_type,
                            'matched_tier' => "Qty >= {$t->min_threshold}",
                            'reward' => "{$t->reward_val}% Category Discount"
                        ];
                        break;
                    }
                }
            } elseif ($rule->rule_type === 'bill_wise') {
                foreach ($rule->tiers as $t) {
                    $min = floatval($t->min_threshold);
                    $max = $t->max_threshold ? floatval($t->max_threshold) : INF;
                    if ($billSubtotal >= $min && $billSubtotal <= $max) {
                        $matchedRules[] = [
                            'rule_name' => $rule->name,
                            'rule_type' => $rule->rule_type,
                            'reward_type' => $rule->reward_type,
                            'matched_tier' => "Bill Rs " . number_format($min) . ($max !== INF ? " - " . number_format($max) : "+"),
                            'reward' => "{$t->reward_val}% Bill Discount"
                        ];
                        break;
                    }
                }
            }
        }

        echo json_encode([
            'status' => 'success',
            'matched_count' => count($matchedRules),
            'matched_rules' => $matchedRules
        ]);
        exit;
    }

    private function parseTiersFromPost() {
        $tiers = [];
        if (isset($_POST['min_threshold']) && is_array($_POST['min_threshold'])) {
            for ($i = 0; $i < count($_POST['min_threshold']); $i++) {
                $min = trim($_POST['min_threshold'][$i]);
                $max = isset($_POST['max_threshold'][$i]) ? trim($_POST['max_threshold'][$i]) : '';
                $reward = isset($_POST['reward_val'][$i]) ? trim($_POST['reward_val'][$i]) : '';

                if ($min !== '' && $reward !== '') {
                    $tiers[] = [
                        'min_threshold' => floatval($min),
                        'max_threshold' => $max !== '' ? floatval($max) : null,
                        'reward_val' => floatval($reward)
                    ];
                }
            }
        }
        return $tiers;
    }
}
