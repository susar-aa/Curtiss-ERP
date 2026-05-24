<?php

class WooCommerceService {
    private $apiUrl;
    private $consumerKey;
    private $consumerSecret;
    private $logFile;

    public function __construct() {
        $this->apiUrl = rtrim(WC_STORE_URL, '/') . '/';
        $this->consumerKey = WC_CONSUMER_KEY;
        $this->consumerSecret = WC_CONSUMER_SECRET;
        $this->logFile = dirname(__DIR__, 2) . '/public/uploads/woocommerce_sync.log';
    }

    /**
     * Send a secure request to WooCommerce REST API using cURL
     */
    private function sendRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->apiUrl . $endpoint;
        $ch = curl_init();

        $credentials = base64_encode($this->consumerKey . ':' . $this->consumerSecret);
        $headers = [
            'Authorization: Basic ' . $credentials,
            'Content-Type: application/json',
            'User-Agent: Curtiss-ERP-Sync/1.0'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            $this->log("CURL Error to URL ($url): " . $error);
            return null;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $this->log("HTTP Error $httpCode on $method $url. Response: " . $response);
            return null;
        }

        return json_decode($response);
    }

    /**
     * Create or Update a category on WooCommerce
     */
    public function syncCategory($name, $description = '', $wooCategoryId = null) {
        $data = [
            'name' => $name,
            'description' => $description
        ];

        if ($wooCategoryId) {
            // Update Category
            $result = $this->sendRequest('products/categories/' . $wooCategoryId, 'PUT', $data);
            if ($result) {
                $this->log("SUCCESS: Updated WooCommerce Category ID {$wooCategoryId} ('{$name}')");
                return $wooCategoryId;
            }
        } else {
            // Create Category
            $result = $this->sendRequest('products/categories', 'POST', $data);
            if ($result) {
                $this->log("SUCCESS: Created WooCommerce Category ID {$result->id} ('{$name}')");
                return $result->id;
            }
        }
        return null;
    }

    /**
     * Delete a category on WooCommerce
     */
    public function deleteCategory($wooCategoryId) {
        if (!$wooCategoryId) return false;
        $result = $this->sendRequest('products/categories/' . $wooCategoryId . '?force=true', 'DELETE');
        if ($result) {
            $this->log("SUCCESS: Deleted WooCommerce Category ID {$wooCategoryId}");
            return true;
        }
        return false;
    }

    /**
     * Fetch active WooCommerce categories
     */
    public function getCategories() {
        $endpoint = 'products/categories?per_page=100&hide_empty=false';
        $results = $this->sendRequest($endpoint, 'GET');
        return is_array($results) ? $results : [];
    }

    /**
     * Fetch global attributes taxonomy list (pa_color, pa_size, etc.)
     */
    public function getGlobalAttributes() {
        $endpoint = 'products/attributes?per_page=100';
        $results = $this->sendRequest($endpoint, 'GET');
        return is_array($results) ? $results : [];
    }

    /**
     * Fetch terms list associated with a specific attribute ID (A3, A4, Red, Blue, Pack of 12)
     */
    public function getAttributeTerms($attributeId) {
        $endpoint = "products/attributes/{$attributeId}/terms?per_page=100";
        $results = $this->sendRequest($endpoint, 'GET');
        return is_array($results) ? $results : [];
    }

    /**
     * Extract WholesaleX B2B Price from meta fields dynamically
     */
    public function extractWholesalePrice($wcProduct) {
        if (!empty($wcProduct->meta_data) && is_array($wcProduct->meta_data)) {
            foreach ($wcProduct->meta_data as $meta) {
                $key = strtolower($meta->key);
                if ($key === '_wholesalex_wholesale_price' || 
                    $key === 'wholesalex_wholesale_price' || 
                    $key === '_wholesale_price' || 
                    strpos($key, 'wholesalex_b2b_price') !== false ||
                    strpos($key, 'wholesale_price') !== false) {
                    return floatval($meta->value);
                }
            }
        }
        return 0;
    }

    /**
     * Fetch all products from WooCommerce (paginated, status=any)
     */
    public function getAllProducts($page = 1, $perPage = 100) {
        $endpoint = "products?page={$page}&per_page={$perPage}&status=any";
        return $this->sendRequest($endpoint, 'GET');
    }

    /**
     * Retrieve variations of a variable product
     */
    public function getProductVariations($productId) {
        $endpoint = "products/{$productId}/variations?per_page=100";
        return $this->sendRequest($endpoint, 'GET');
    }

    /**
     * Search WooCommerce for a product by SKU
     */
    public function getProductBySku($sku) {
        if (empty($sku)) return null;
        $endpoint = 'products?sku=' . urlencode($sku);
        $results = $this->sendRequest($endpoint, 'GET');
        
        if (is_array($results) && !empty($results)) {
            return $results[0];
        }
        return null;
    }

    /**
     * Synchronize ERP item with WooCommerce
     */
    public function syncItem($item, $base64Image = null) {
        $sku = !empty($item->item_code) ? $item->item_code : ($item->sku ?? '');
        if (empty($sku)) {
            $this->log("Sync skipped: Item ID {$item->id} has no SKU.");
            return false;
        }

        // Parent Meta Data
        $metaData = [];
        if (!empty($item->wholesale_price) && floatval($item->wholesale_price) > 0) {
            $metaData[] = [
                'key' => '_wholesalex_wholesale_price',
                'value' => (string)$item->wholesale_price
            ];
            $metaData[] = [
                'key' => '_wholesale_price',
                'value' => (string)$item->wholesale_price
            ];
        }
        if (!empty($item->cost_price) && floatval($item->cost_price) > 0) {
            $metaData[] = [
                'key' => '_cost_price',
                'value' => (string)$item->cost_price
            ];
            $metaData[] = [
                'key' => '_wc_cog_cost',
                'value' => (string)$item->cost_price
            ];
        }

        $categoriesPayload = [];
        if (!empty($item->category_id)) {
            $categoriesPayload[] = ['id' => (int)$item->category_id];
        }

        $has_variations = false;
        $variations_list = [];
        if (!empty($item->variations_json)) {
            $decoded_vars = json_decode($item->variations_json, true);
            if (is_array($decoded_vars) && !empty($decoded_vars)) {
                $has_variations = true;
                $variations_list = $decoded_vars;
            }
        }

        $productData = [
            'name'          => $item->name ?? $item->title ?? '',
            'type'          => $has_variations ? 'variable' : 'simple',
            'regular_price' => $has_variations ? '' : (string)($item->selling_price ?? $item->price ?? '0.00'),
            'description'   => $item->description ?? '',
            'manage_stock'  => !$has_variations,
            'stock_quantity' => $has_variations ? null : (int)($item->qty ?? 0),
            'sku'           => $sku,
            'status'        => 'publish',
            'categories'    => $categoriesPayload,
            'meta_data'     => $metaData
        ];

        if ($has_variations) {
            $options = [];
            foreach ($variations_list as $v) {
                if (!empty($v['attribute'])) {
                    $options[] = $v['attribute'];
                }
            }
            $options = array_unique($options);
            if (empty($options)) {
                $options = ['Default'];
            }

            $productData['attributes'] = [
                [
                    'name' => 'Option',
                    'position' => 0,
                    'visible' => true,
                    'variation' => true,
                    'options' => array_values($options)
                ]
            ];
        }

        $existingProduct = $this->getProductBySku($sku);
        $productId = null;

        if ($existingProduct) {
            $endpoint = 'products/' . $existingProduct->id;
            $result = $this->sendRequest($endpoint, 'PUT', $productData);
            if ($result) {
                $productId = $existingProduct->id;
            }
        } else {
            $endpoint = 'products';
            $result = $this->sendRequest($endpoint, 'POST', $productData);
            if ($result) {
                $productId = $result->id;
            }
        }

        if ($productId && $has_variations) {
            $this->syncVariations($productId, $variations_list);
        }

        return $productId;
    }

    /**
     * Bulk-synchronizes child variations of a variable product.
     * Automatically updates existing variants, creates new ones, and purges orphans.
     */
    public function syncVariations($productId, $variationsList) {
        if (!$productId || !is_array($variationsList)) return false;

        $existingVars = $this->getProductVariations($productId);
        $matchedSkus = [];

        foreach ($variationsList as $v) {
            $vSku = $v['sku'] ?? '';
            if (empty($vSku)) continue;

            $matchedSkus[] = $vSku;

            // Build Variation Payload with Cost, Retail and WholesaleX B2B Prices
            $variationData = [
                'sku' => $vSku,
                'regular_price' => (string)($v['price'] ?? '0.00'),
                'manage_stock' => true,
                'stock_quantity' => (int)($v['qty'] ?? 0),
                'attributes' => [
                    [
                        'name' => 'Option',
                        'option' => $v['attribute'] ?? 'Default'
                    ]
                ],
                'meta_data' => [
                    [
                        'key' => '_wholesalex_wholesale_price',
                        'value' => (string)($v['wholesale_price'] ?? '0.00')
                    ],
                    [
                        'key' => '_wholesale_price',
                        'value' => (string)($v['wholesale_price'] ?? '0.00')
                    ],
                    [
                        'key' => '_cost_price',
                        'value' => (string)($v['cost_price'] ?? '0.00')
                    ],
                    [
                        'key' => '_wc_cog_cost',
                        'value' => (string)($v['cost_price'] ?? '0.00')
                    ]
                ]
            ];

            // Find matching existing variation
            $matchedVarId = null;
            if (is_array($existingVars)) {
                foreach ($existingVars as $ev) {
                    if (($ev->sku ?? '') === $vSku) {
                        $matchedVarId = $ev->id;
                        break;
                    }
                }
            }

            if ($matchedVarId) {
                $result = $this->sendRequest("products/{$productId}/variations/{$matchedVarId}", 'PUT', $variationData);
                if ($result) {
                    $this->log("SUCCESS: Updated WooCommerce Variation ID {$matchedVarId} (SKU: {$vSku}) under Parent #{$productId}");
                }
            } else {
                $result = $this->sendRequest("products/{$productId}/variations", 'POST', $variationData);
                if ($result) {
                    $this->log("SUCCESS: Created WooCommerce Variation ID {$result->id} (SKU: {$vSku}) under Parent #{$productId}");
                }
            }
        }

        // Purge Orphan Variations on WooCommerce (Those deleted in the ERP form)
        if (is_array($existingVars)) {
            foreach ($existingVars as $ev) {
                $evSku = $ev->sku ?? '';
                if (!empty($evSku) && !in_array($evSku, $matchedSkus)) {
                    $deleteResult = $this->sendRequest("products/{$productId}/variations/{$ev->id}?force=true", 'DELETE');
                    if ($deleteResult) {
                        $this->log("SUCCESS: Purged Orphan Variation ID {$ev->id} (SKU: {$evSku}) under Parent #{$productId}");
                    }
                }
            }
        }

        return true;
    }

    /**
     * Quickly update WooCommerce stock status by SKU
     */
    public function updateStock($sku, $newStockQty) {
        if (empty($sku)) return false;

        $existingProduct = $this->getProductBySku($sku);
        if ($existingProduct) {
            $endpoint = 'products/' . $existingProduct->id;
            $data = [
                'manage_stock' => true,
                'stock_quantity' => (int)$newStockQty
            ];
            $result = $this->sendRequest($endpoint, 'PUT', $data);
            if ($result) {
                $this->log("SUCCESS: Stock updated to {$newStockQty} for WooCommerce Product ID {$existingProduct->id} (SKU: $sku)");
                return true;
            }
        } else {
            $this->log("SKU $sku not found on WooCommerce. Stock update skipped.");
        }
        return false;
    }

    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";
        $dir = dirname($this->logFile);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }
}