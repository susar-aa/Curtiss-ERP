<?php
$f = 'app/Controllers/EstimateController.php';
$c = file_get_contents($f);

$editMethod = <<<'EOD'
    public function edit($id = null) {
        if (!$id) { header('Location: ' . APP_URL . '/estimate'); exit; }

        $estimate = $this->estimateModel->getEstimateById($id);
        if (!$estimate) { die("Estimate not found."); }
        if ($estimate->status === 'Invoiced') { die("Cannot edit an invoiced estimate."); }

        $estimateItems = $this->estimateModel->getEstimateItems($id);

        // Fetch all active items
        $this->db->query("SELECT id, name, item_code, sample_code, price, wholesale_price, cost_price, type, variations_json FROM items ORDER BY name ASC");
        $catalogItems = $this->db->resultSet() ?: [];

        // Fetch all variations in ONE batch
        $this->db->query("
            SELECT ivo.id as var_opt_id, ivo.item_id, ivo.sku, ivo.price, ivo.wholesale_price, 
                   v.name as variation_name, vv.value_name
            FROM item_variation_options ivo
            JOIN variations v ON ivo.variation_id = v.id
            JOIN variation_values vv ON ivo.variation_value_id = vv.id
            ORDER BY ivo.item_id ASC, vv.value_name ASC
        ");
        $allVariations = $this->db->resultSet() ?: [];

        $varMap = [];
        foreach ($allVariations as $var) {
            $varMap[$var->item_id][] = $var;
        }

        // Build a lean, clean list of products for frontend autocomplete
        $preparedProducts = [];
        foreach ($catalogItems as $item) {
            $basePrice = 0.00;
            if (isset($item->price) && floatval($item->price) > 0) {
                $basePrice = floatval($item->price);
            } elseif (isset($item->wholesale_price) && floatval($item->wholesale_price) > 0) {
                $basePrice = floatval($item->wholesale_price);
            } elseif (isset($item->cost_price) && floatval($item->cost_price) > 0) {
                $basePrice = floatval($item->cost_price);
            }

            // Add base item
            $preparedProducts[] = [
                'id' => (int)$item->id,
                'name' => (string)$item->name,
                'sku' => (string)($item->item_code ?? ''),
                'sample_code' => (string)($item->sample_code ?? ''),
                'price' => (float)$basePrice,
                'variation' => ''
            ];

            // Add variations if present
            $itemVars = $varMap[$item->id] ?? [];
            if (!empty($itemVars)) {
                foreach ($itemVars as $v) {
                    $vPrice = (isset($v->price) && floatval($v->price) > 0) ? floatval($v->price) : $basePrice;
                    $varLabel = ($v->variation_name ? $v->variation_name . ': ' : '') . $v->value_name;
                    $preparedProducts[] = [
                        'id' => (int)$item->id,
                        'name' => (string)($item->name . ' (' . $varLabel . ')'),
                        'sku' => (string)($v->sku ?: ($item->item_code ?? '')),
                        'sample_code' => (string)($item->sample_code ?? ''),
                        'price' => (float)$vPrice,
                        'variation' => $varLabel
                    ];
                }
            } elseif (!empty($item->variations_json)) {
                $decoded = json_decode($item->variations_json);
                if (is_array($decoded)) {
                    foreach ($decoded as $dec) {
                        $vPrice = (isset($dec->price) && floatval($dec->price) > 0) ? floatval($dec->price) : $basePrice;
                        $varLabel = $dec->attribute ?? ($dec->value_name ?? 'Option');
                        $preparedProducts[] = [
                            'id' => (int)$item->id,
                            'name' => (string)($item->name . ' (' . $varLabel . ')'),
                            'sku' => (string)($dec->sku ?? ($item->item_code ?? '')),
                            'sample_code' => (string)($item->sample_code ?? ''),
                            'price' => (float)$vPrice,
                            'variation' => $varLabel
                        ];
                    }
                }
            }
        }

        $data = [
            'title' => 'Edit Estimate',
            'content_view' => 'estimates/edit',
            'customers' => $this->customerModel->getAllCustomers(),
            'catalog_items' => $preparedProducts,
            'estimate' => $estimate,
            'estimate_items' => $estimateItems,
            'error' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $estimateData = [
                'customer_id' => $_POST['customer_id'],
                'date' => $_POST['estimate_date'],
                'expiry_date' => $_POST['expiry_date']
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
                if ($this->estimateModel->updateEstimate($id, $estimateData, $items)) {
                    header('Location: ' . APP_URL . '/estimate?success=1');
                    exit;
                } else {
                    $data['error'] = 'Database Error: Failed to update estimate.';
                }
            }
        }
        $this->view('layouts/main', $data);
    }
EOD;

$c = preg_replace('/public function show/', $editMethod . "\n\n    public function show", $c);
file_put_contents($f, $c);
echo "Added edit method.";
