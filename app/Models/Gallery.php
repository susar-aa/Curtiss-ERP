<?php

class Gallery {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get a map of all used images in the system.
     * Returns an associative array where the key is the file basename 
     * and the value is an array of usage details (e.g., product name, sku).
     */
    public function getAllUsedImages() {
        $usedImages = [];

        // 1. Check primary and additional images in items table
        $this->db->query("SELECT id, name, sku, image_path, additional_images, variations_json FROM items");
        $items = $this->db->resultSet();

        foreach ($items as $item) {
            $productInfo = [
                'type' => 'Product',
                'item_id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku
            ];

            // Primary image
            if (!empty($item->image_path)) {
                $basename = basename($item->image_path);
                if (!isset($usedImages[$basename])) {
                    $usedImages[$basename] = [];
                }
                $usedImages[$basename][] = $productInfo;
            }

            // Additional images (JSON)
            if (!empty($item->additional_images)) {
                $additional = json_decode($item->additional_images, true);
                if (is_array($additional)) {
                    foreach ($additional as $addPath) {
                        if (!empty($addPath)) {
                            $basename = basename($addPath);
                            if (!isset($usedImages[$basename])) {
                                $usedImages[$basename] = [];
                            }
                            $usedImages[$basename][] = $productInfo;
                        }
                    }
                }
            }

            // Variations JSON images
            if (!empty($item->variations_json)) {
                $variations = json_decode($item->variations_json, true);
                if (is_array($variations)) {
                    foreach ($variations as $var) {
                        if (isset($var['options']) && is_array($var['options'])) {
                            foreach ($var['options'] as $opt) {
                                if (!empty($opt['image_path'])) {
                                    $basename = basename($opt['image_path']);
                                    if (!isset($usedImages[$basename])) {
                                        $usedImages[$basename] = [];
                                    }
                                    $usedImages[$basename][] = [
                                        'type' => 'Variation',
                                        'item_id' => $item->id,
                                        'name' => $item->name . ' - ' . ($opt['value_name'] ?? 'Variation'),
                                        'sku' => $opt['sku'] ?? $item->sku
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        // 2. Check item_images table
        $this->db->query("SELECT ii.image_path, i.id as item_id, i.name, i.sku FROM item_images ii LEFT JOIN items i ON ii.item_id = i.id");
        $itemImages = $this->db->resultSet();
        
        foreach ($itemImages as $imgRow) {
            if (!empty($imgRow->image_path)) {
                $basename = basename($imgRow->image_path);
                
                // Only add if not already added by the primary/variation checks to avoid duplicates
                $alreadyAdded = false;
                if (isset($usedImages[$basename])) {
                    foreach ($usedImages[$basename] as $existing) {
                        if ($existing['item_id'] == $imgRow->item_id) {
                            $alreadyAdded = true;
                            break;
                        }
                    }
                }
                
                if (!$alreadyAdded) {
                    if (!isset($usedImages[$basename])) {
                        $usedImages[$basename] = [];
                    }
                    $usedImages[$basename][] = [
                        'type' => 'Product Image (DB)',
                        'item_id' => $imgRow->item_id,
                        'name' => $imgRow->name ?? 'Unknown',
                        'sku' => $imgRow->sku ?? 'Unknown'
                    ];
                }
            }
        }

        return $usedImages;
    }

    /**
     * Remove all references to a specific image path from the database safely.
     * This ensures no broken images appear on the frontend.
     */
    public function removeImageReferences($imagePath) {
        $basename = basename($imagePath);
        
        // 1. Remove from item_images
        $this->db->query("DELETE FROM item_images WHERE image_path LIKE :path OR image_path LIKE :basename");
        $this->db->bind(':path', '%' . $imagePath . '%');
        $this->db->bind(':basename', '%' . $basename);
        $this->db->execute();

        // 2. Remove primary image from items
        $this->db->query("UPDATE items SET image_path = NULL WHERE image_path LIKE :path OR image_path LIKE :basename");
        $this->db->bind(':path', '%' . $imagePath . '%');
        $this->db->bind(':basename', '%' . $basename);
        $this->db->execute();

        // 3. Update JSON arrays in items (additional_images & variations_json)
        // Since SQL REPLACE on JSON is complex across MariaDB/MySQL versions without knowing exactly,
        // it's safer to fetch rows containing the string, manipulate in PHP, and save back.
        
        $this->db->query("SELECT id, additional_images, variations_json FROM items WHERE additional_images LIKE :basename OR variations_json LIKE :basename");
        $this->db->bind(':basename', '%' . $basename . '%');
        $itemsToUpdate = $this->db->resultSet();
        
        foreach ($itemsToUpdate as $item) {
            $updated = false;
            
            // Handle additional_images
            $newAdditional = $item->additional_images;
            if (!empty($item->additional_images) && strpos($item->additional_images, $basename) !== false) {
                $addArr = json_decode($item->additional_images, true);
                if (is_array($addArr)) {
                    $addArr = array_filter($addArr, function($path) use ($basename) {
                        return basename($path) !== $basename;
                    });
                    $newAdditional = json_encode(array_values($addArr));
                    $updated = true;
                }
            }
            
            // Handle variations_json
            $newVariations = $item->variations_json;
            if (!empty($item->variations_json) && strpos($item->variations_json, $basename) !== false) {
                $varArr = json_decode($item->variations_json, true);
                if (is_array($varArr)) {
                    foreach ($varArr as &$var) {
                        if (isset($var['options']) && is_array($var['options'])) {
                            foreach ($var['options'] as &$opt) {
                                if (isset($opt['image_path']) && basename($opt['image_path']) === $basename) {
                                    $opt['image_path'] = '';
                                    $updated = true;
                                }
                            }
                        }
                    }
                    $newVariations = json_encode($varArr);
                }
            }
            
            if ($updated) {
                $this->db->query("UPDATE items SET additional_images = :add, variations_json = :var WHERE id = :id");
                $this->db->bind(':add', $newAdditional);
                $this->db->bind(':var', $newVariations);
                $this->db->bind(':id', $item->id);
                $this->db->execute();
            }
        }
        
        return true;
    }
}
