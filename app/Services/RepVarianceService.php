<?php
class RepVarianceService {
    public static function adjustVarianceBilling($routeId, $adjustments, $userId, $force = false) {
        $db = new Database();

        // 1. Auto-apply any remaining pending product substitutions
        self::autoApplyRouteSubstitutions($db, $routeId, $userId);

        // 3. Validate that adjusted bills match the final loaded stock
        if (!$force) {
            // Resolve all bound/merged route IDs for validation
            $routeIds = [intval($routeId)];
            $db->query("SELECT route_binding_id FROM rep_daily_routes WHERE id = :rid LIMIT 1");
            $db->bind(':rid', $routeId);
            $routeRow = $db->single();
            if ($routeRow && $routeRow->route_binding_id) {
                $db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid");
                $db->bind(':bid', $routeRow->route_binding_id);
                $boundRoutes = $db->resultSet();
                foreach ($boundRoutes as $br) {
                    $routeIds[] = intval($br->id);
                }
            }
            $routeIds = array_unique($routeIds);

            require_once dirname(__DIR__) . '/Models/RepTracking.php';
            $trackingModel = new RepTracking();
            $routeFinalItems = $trackingModel->getRouteFinalLoadingItems($routeId);
            $adjMap = [];
            foreach ($adjustments as $adj) {
                $itemId = intval($adj['item_id']);
                $varOptId = isset($adj['variation_option_id']) && $adj['variation_option_id'] !== '' && $adj['variation_option_id'] !== 'null' ? intval($adj['variation_option_id']) : 0;
                $key = $itemId . '_' . $varOptId;
                $sum = 0.0;
                foreach ($adj['invoice_adjustments'] as $ia) {
                    $sum += floatval($ia['new_qty']);
                }
                $adjMap[$key] = $sum;
            }

            foreach ($routeFinalItems as $item) {
                if (floatval($item->variance) !== 0.0) {
                    $itemId = intval($item->item_id);
                    $varOptId = isset($item->variation_option_id) && $item->variation_option_id !== '' && $item->variation_option_id !== 'null' ? intval($item->variation_option_id) : 0;
                    $key = $itemId . '_' . $varOptId;
                    $expected = floatval($item->final_loaded_qty);
                    $allocated = $adjMap[$key] ?? null;
                    
                    if ($allocated === null) {
                        // Retrieve the current total quantity invoiced for that item/variation from the database
                        $placeholders = [];
                        foreach ($routeIds as $index => $rid) {
                            $placeholders[] = ":rid_val_" . $index;
                        }
                        $placeholdersStr = implode(',', $placeholders);
                        
                        $sql = "SELECT SUM(ii.quantity) as current_qty
                                FROM invoice_items ii
                                JOIN invoices i ON ii.invoice_id = i.id
                                WHERE i.rep_route_id IN ($placeholdersStr) 
                                  AND ii.item_id = :item_id 
                                  AND i.status != 'Voided'";
                        if ($varOptId > 0) {
                            $sql .= " AND ii.variation_option_id = :var_opt_id";
                        } else {
                            $sql .= " AND (ii.variation_option_id IS NULL OR ii.variation_option_id = 0)";
                        }
                        $db->query($sql);
                        foreach ($routeIds as $index => $rid) {
                            $db->bind(":rid_val_" . $index, intval($rid));
                        }
                        $db->bind(':item_id', $itemId);
                        if ($varOptId > 0) {
                            $db->bind(':var_opt_id', $varOptId);
                        }
                        $sumRow = $db->single();
                        $allocated = $sumRow ? floatval($sumRow->current_qty) : 0.0;
                    }
                    
                    if (abs($allocated - $expected) > 0.01) {
                        throw new Exception("Cannot complete Variance Audit. The adjusted bills do not match the final loaded stock for product '{$item->item_name}'. (Expected: {$expected}, Allocated: {$allocated})");
                    }
                }
            }
        }

        $db->beginTransaction();
        try {
            $db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1200' OR account_name LIKE '%Receivable%' LIMIT 1");
            $arAccRow = $db->single();
            $arAccId = $arAccRow ? intval($arAccRow->id) : null;

            $db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Revenue' AND (account_code = '4000' OR account_name LIKE '%Sales%' OR account_name LIKE '%Revenue%') LIMIT 1");
            $revAccRow = $db->single();
            if (!$revAccRow) {
                $db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Revenue' LIMIT 1");
                $revAccRow = $db->single();
            }
            $revAccId = $revAccRow ? intval($revAccRow->id) : null;

            $modifiedInvoices = [];

            foreach ($adjustments as $adj) {
                $itemId = intval($adj['item_id']);
                $varId = isset($adj['variation_option_id']) && $adj['variation_option_id'] !== '' && $adj['variation_option_id'] !== 'null' ? intval($adj['variation_option_id']) : null;
                $invoiceAdjs = $adj['invoice_adjustments'] ?? [];

                foreach ($invoiceAdjs as $ia) {
                    $invoiceId = intval($ia['invoice_id']);
                    $newQty = floatval($ia['new_qty']);

                    $sql = "SELECT ii.id, ii.quantity as old_qty, ii.unit_price, ii.discount_value, ii.discount_type, ii.variation_option_id, i.stock_status, i.invoice_number
                                FROM invoice_items ii
                                JOIN invoices i ON ii.invoice_id = i.id
                                WHERE ii.invoice_id = :iid AND ii.item_id = :item_id";
                    if ($varId !== null) {
                        $sql .= " AND ii.variation_option_id = :var_id";
                    } else {
                        $sql .= " AND (ii.variation_option_id IS NULL OR ii.variation_option_id = 0)";
                    }
                    $db->query($sql);
                    $db->bind(':iid', $invoiceId);
                    $db->bind(':item_id', $itemId);
                    if ($varId !== null) {
                        $db->bind(':var_id', $varId);
                    }
                    $line = $db->single();

                    if ($line) {
                        $oldQty = floatval($line->old_qty);
                        if ($oldQty === $newQty && $newQty !== 0.0) {
                            continue;
                        }

                        $unitPrice = floatval($line->unit_price);
                        $discVal = floatval($line->discount_value);
                        $discType = $line->discount_type;

                        $lineTotal = $newQty * $unitPrice;
                        if ($discType === '%') {
                            $lineTotal -= ($lineTotal * $discVal / 100);
                        } else {
                            $lineTotal -= ($discVal * $newQty);
                        }

                        if ($newQty === 0.0) {
                            $db->query("DELETE FROM invoice_items WHERE id = :id");
                            $db->bind(':id', $line->id);
                            $db->execute();
                        } else {
                            $db->query("UPDATE invoice_items SET quantity = :qty, loaded_quantity = :qty, total = :total WHERE id = :id");
                            $db->bind(':qty', $newQty);
                            $db->bind(':total', $lineTotal);
                            $db->bind(':id', $line->id);
                            $db->execute();
                        }

                        $diff = $newQty - $oldQty;
                        if ($line->stock_status === 'reserved') {
                            if ($varId !== null) {
                                $db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) + :diff) WHERE id = :var_id");
                                $db->bind(':diff', $diff);
                                $db->bind(':var_id', $varId);
                                $db->execute();

                                $db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) + :diff) WHERE id = :item_id");
                                $db->bind(':diff', $diff);
                                $db->bind(':item_id', $itemId);
                                $db->execute();
                            } else {
                                $db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) + :diff) WHERE id = :item_id");
                                $db->bind(':diff', $diff);
                                $db->bind(':item_id', $itemId);
                                $db->execute();
                            }
                        } else {
                            if ($varId !== null) {
                                $db->query("UPDATE item_variation_options SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :diff) WHERE id = :var_id");
                                $db->bind(':diff', $diff);
                                $db->bind(':var_id', $varId);
                                $db->execute();

                                require_once dirname(__DIR__) . '/Models/Item.php';
                                $itemModel = new Item();
                                $itemModel->updateStockDelta($itemId, -$diff);
                            } else {
                                require_once dirname(__DIR__) . '/Models/Item.php';
                                $itemModel = new Item();
                                $itemModel->updateStockDelta($itemId, -$diff);
                            }

                            require_once dirname(__DIR__) . '/Models/StockLedger.php';
                            $ledger = new StockLedger();
                            $db->query("SELECT warehouse_id, cost_price FROM items WHERE id = :id");
                            $db->bind(':id', $itemId);
                            $itemMeta = $db->single();
                            $whId = $itemMeta ? $itemMeta->warehouse_id : null;
                            $itemCost = $itemMeta ? floatval($itemMeta->cost_price > 0 ? $itemMeta->cost_price : 0.00) : 0.00;
                            if ($diff > 0) {
                                $ledger->logMovement($itemId, $varId, 0, $diff, 'Sales Invoice Variance Increase', $line->invoice_number, $whId, $userId, 'Variance Audit Adjust', $itemCost);
                            } else {
                                $ledger->logMovement($itemId, $varId, abs($diff), 0, 'Sales Invoice Variance Decrease', $line->invoice_number, $whId, $userId, 'Variance Audit Adjust', $itemCost);
                            }
                        }

                        if (!in_array($invoiceId, $modifiedInvoices)) {
                            $modifiedInvoices[] = $invoiceId;
                        }
                    } else if ($newQty > 0.0) {
                        $db->query("SELECT name, price, cost_price, warehouse_id FROM items WHERE id = :id");
                        $db->bind(':id', $itemId);
                        $itemRow = $db->single();
                        if ($itemRow) {
                            $itemName = $itemRow->name;
                            $unitPrice = floatval($itemRow->price);
                            $discVal = 0.0;
                            $discType = '%';

                            $varOptionIdBind = null;
                            if ($varId !== null) {
                                // Fetch variation details
                                $db->query("
                                    SELECT ivo.price, v.name AS variation_name, vv.value_name
                                    FROM item_variation_options ivo
                                    JOIN variations v ON ivo.variation_id = v.id
                                    JOIN variation_values vv ON ivo.variation_value_id = vv.id
                                    WHERE ivo.id = :var_id LIMIT 1
                                ");
                                $db->bind(':var_id', $varId);
                                $varRow = $db->single();
                                if ($varRow) {
                                    $itemName .= " (" . $varRow->variation_name . ": " . $varRow->value_name . ")";
                                    if (floatval($varRow->price) > 0) {
                                        $unitPrice = floatval($varRow->price);
                                    }
                                    $varOptionIdBind = $varId;
                                }
                            }

                            $lineTotal = $newQty * $unitPrice;

                            $db->query("INSERT INTO invoice_items (invoice_id, item_id, variation_option_id, description, quantity, unit_price, discount_value, discount_type, total)
                                        VALUES (:iid, :item_id, :var_id, :desc, :qty, :unit_price, :disc_val, :disc_type, :total)");
                            $db->bind(':iid', $invoiceId);
                            $db->bind(':item_id', $itemId);
                            $db->bind(':var_id', $varOptionIdBind);
                            $db->bind(':desc', $itemName);
                            $db->bind(':qty', $newQty);
                            $db->bind(':unit_price', $unitPrice);
                            $db->bind(':disc_val', $discVal);
                            $db->bind(':disc_type', $discType);
                            $db->bind(':total', $lineTotal);
                            $db->execute();

                            $db->query("SELECT invoice_number, stock_status FROM invoices WHERE id = :iid");
                            $db->bind(':iid', $invoiceId);
                            $invMeta = $db->single();
                            $invNum = $invMeta ? $invMeta->invoice_number : '';
                            $stockStatus = $invMeta ? $invMeta->stock_status : 'picked';

                            if ($stockStatus === 'reserved') {
                                if ($varId !== null) {
                                    $db->query("UPDATE item_variation_options SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) + :qty) WHERE id = :var_id");
                                    $db->bind(':qty', $newQty);
                                    $db->bind(':var_id', $varId);
                                    $db->execute();

                                    $db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) + :qty) WHERE id = :item_id");
                                    $db->bind(':qty', $newQty);
                                    $db->bind(':item_id', $itemId);
                                    $db->execute();
                                } else {
                                    $db->query("UPDATE items SET quantity_reserved = GREATEST(0, CAST(quantity_reserved AS SIGNED) + :qty) WHERE id = :item_id");
                                    $db->bind(':qty', $newQty);
                                    $db->bind(':item_id', $itemId);
                                    $db->execute();
                                }
                            } else {
                                if ($varId !== null) {
                                    $db->query("UPDATE item_variation_options SET quantity_on_hand = GREATEST(0, CAST(quantity_on_hand AS SIGNED) - :qty) WHERE id = :var_id");
                                    $db->bind(':qty', $newQty);
                                    $db->bind(':var_id', $varId);
                                    $db->execute();

                                    require_once dirname(__DIR__) . '/Models/Item.php';
                                    $itemModel = new Item();
                                    $itemModel->updateStockDelta($itemId, -$newQty);
                                } else {
                                    require_once dirname(__DIR__) . '/Models/Item.php';
                                    $itemModel = new Item();
                                    $itemModel->updateStockDelta($itemId, -$newQty);
                                }

                                require_once dirname(__DIR__) . '/Models/StockLedger.php';
                                $ledger = new StockLedger();
                                $whId = $itemRow->warehouse_id;
                                $itemCost = floatval($itemRow->cost_price > 0 ? $itemRow->cost_price : 0.00);
                                $ledger->logMovement($itemId, $varId, 0, $newQty, 'Sales Invoice Variance Increase', $invNum, $whId, $userId, 'Variance Audit Adjust', $itemCost);
                            }

                            if (!in_array($invoiceId, $modifiedInvoices)) {
                                $modifiedInvoices[] = $invoiceId;
                            }
                        }
                    }
                }
            }

            foreach ($modifiedInvoices as $invId) {
                $db->query("SELECT SUM(total) as subtotal FROM invoice_items WHERE invoice_id = :id");
                $db->bind(':id', $invId);
                $subrow = $db->single();
                $subtotal = $subrow ? floatval($subrow->subtotal) : 0.0;

                $db->query("SELECT total_amount, global_discount_val, global_discount_type, tax_rate_id, tax_amount, journal_entry_id FROM invoices WHERE id = :id");
                $db->bind(':id', $invId);
                $invRow = $db->single();

                if ($invRow) {
                    $oldSub = floatval($invRow->total_amount);
                    $oldDiscVal = floatval($invRow->global_discount_val);
                    $oldDiscType = $invRow->global_discount_type;
                    $oldDisc = ($oldDiscType === '%') ? ($oldSub * $oldDiscVal / 100) : $oldDiscVal;
                    $oldGrand = ($oldSub - $oldDisc) + floatval($invRow->tax_amount);

                    $disc = ($oldDiscType === '%') ? ($subtotal * $oldDiscVal / 100) : $oldDiscVal;
                    $taxVal = 0.0;

                    if ($invRow->tax_rate_id) {
                        $db->query("SELECT rate_percentage FROM tax_rates WHERE id = :tid");
                        $db->bind(':tid', $invRow->tax_rate_id);
                        $taxRateRow = $db->single();
                        if ($taxRateRow) {
                            $taxVal = ($subtotal - $disc) * floatval($taxRateRow->rate_percentage) / 100;
                        }
                    }

                    $grandTotal = max(0.0, ($subtotal - $disc) + $taxVal);

                    $db->query("UPDATE invoices SET total_amount = :sub, tax_amount = :tax WHERE id = :id");
                    $db->bind(':sub', $subtotal);
                    $db->bind(':tax', $taxVal);
                    $db->bind(':id', $invId);
                    $db->execute();

                    $jid = $invRow->journal_entry_id;
                    if ($jid) {
                        if ($arAccId) {
                            $db->query("UPDATE transactions SET debit = :grand WHERE journal_entry_id = :jid AND account_id = :aid AND debit > 0");
                            $db->bind(':grand', $grandTotal);
                            $db->bind(':jid', $jid);
                            $db->bind(':aid', $arAccId);
                            $db->execute();
                        }
                        if ($revAccId) {
                            $db->query("UPDATE transactions SET credit = :grand WHERE journal_entry_id = :jid AND account_id = :aid AND credit > 0");
                            $db->bind(':grand', $grandTotal);
                            $db->bind(':jid', $jid);
                            $db->bind(':aid', $revAccId);
                            $db->execute();
                        }

                        $diffGrand = $grandTotal - $oldGrand;
                        if (abs($diffGrand) > 0.001) {
                            if ($arAccId) {
                                // ACCT-2 FIX: AR (Asset): positive diff = debit, negative = credit
                                $db->updateAccountBalance($arAccId, ($diffGrand > 0 ? $diffGrand : 0), ($diffGrand < 0 ? abs($diffGrand) : 0));
                            }
                            if ($revAccId) {
                                // ACCT-2 FIX: Revenue: positive diff = credit increase, negative = debit decrease
                                $db->updateAccountBalance($revAccId, ($diffGrand < 0 ? abs($diffGrand) : 0), ($diffGrand > 0 ? $diffGrand : 0));
                            }
                        }
                    }
                }
            }

            // Resolve all bound/merged route IDs to transition them together
            $routeIds = [intval($routeId)];
            $db->query("SELECT route_binding_id FROM rep_daily_routes WHERE id = :rid LIMIT 1");
            $db->bind(':rid', $routeId);
            $routeRow = $db->single();
            if ($routeRow && $routeRow->route_binding_id) {
                $db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid");
                $db->bind(':bid', $routeRow->route_binding_id);
                $boundRoutes = $db->resultSet();
                foreach ($boundRoutes as $br) {
                    $routeIds[] = intval($br->id);
                }
            }
            $routeIds = array_unique($routeIds);
            
            $placeholders = [];
            foreach ($routeIds as $index => $id) {
                $placeholders[] = ":rid_fin_" . $index;
            }
            $placeholdersStr = implode(',', $placeholders);

            $db->query("UPDATE rep_daily_routes SET status = 'Finalizing' WHERE id IN ($placeholdersStr)");
            foreach ($routeIds as $index => $id) {
                $db->bind(":rid_fin_" . $index, intval($id));
            }
            $db->execute();

            $db->commit();
            return ['status' => 'success', 'message' => 'Variances reconciled and bills adjusted successfully!'];
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function autoApplyRouteSubstitutions($db, $routeId, $userId) {
        $db->query("SELECT id FROM product_substitutions WHERE route_id = :rid AND status = 'Pending Bill Update'");
        $db->bind(':rid', $routeId);
        $subs = $db->resultSet() ?: [];
        foreach ($subs as $sub) {
            try {
                self::executeApplySubstitution($db, intval($sub->id), 'replacement', $userId);
            } catch (Exception $e) {
                error_log("Auto-applying substitution ID {$sub->id} failed: " . $e->getMessage());
            }
        }
    }

    public static function executeApplySubstitution($db, $subId, $pricingChoice, $userId) {
        // Fetch substitution details
        $db->query("SELECT * FROM product_substitutions WHERE id = :id AND status = 'Pending Bill Update'");
        $db->bind(':id', $subId);
        $sub = $db->single();

        if (!$sub) {
            throw new Exception("Pending substitution not found or already applied");
        }

        // Find affected invoices on this route that contain the original product
        $routeIds = [intval($sub->route_id)];
        $db->query("SELECT route_binding_id FROM rep_daily_routes WHERE id = :rid LIMIT 1");
        $db->bind(':rid', $sub->route_id);
        $routeRow = $db->single();
        if ($routeRow && $routeRow->route_binding_id) {
            $db->query("SELECT id FROM rep_daily_routes WHERE route_binding_id = :bid");
            $db->bind(':bid', $routeRow->route_binding_id);
            $boundRoutes = $db->resultSet();
            foreach ($boundRoutes as $br) {
                $routeIds[] = intval($br->id);
            }
        }
        $routeIds = array_unique($routeIds);
        
        $placeholders = [];
        foreach ($routeIds as $index => $id) {
            $placeholders[] = ":rid_sub_" . $index;
        }
        $placeholdersStr = implode(',', $placeholders);

        // Fetch affected invoices
        $sql = "
            SELECT DISTINCT i.id, i.invoice_number, i.total_amount
            FROM invoices i
            JOIN invoice_items ii ON i.id = ii.invoice_id
            WHERE i.rep_route_id IN ($placeholdersStr) AND ii.item_id = :oid AND i.status != 'Voided'
        ";
        if ($sub->original_variation_option_id) {
            $sql .= " AND ii.variation_option_id = :ovar_id";
        } else {
            $sql .= " AND (ii.variation_option_id IS NULL OR ii.variation_option_id = 0)";
        }
        $db->query($sql);
        foreach ($routeIds as $index => $id) {
            $db->bind(":rid_sub_" . $index, intval($id));
        }
        $db->bind(':oid', $sub->original_item_id);
        if ($sub->original_variation_option_id) {
            $db->bind(':ovar_id', $sub->original_variation_option_id);
        }
        $invoices = $db->resultSet() ?: [];

        if (empty($invoices)) {
            throw new Exception("No active invoices on this route contain the original product.");
        }

        // Fetch original and replacement items
        $db->query("SELECT name, price AS selling_price FROM items WHERE id = :id");
        $db->bind(':id', $sub->original_item_id);
        $origProduct = $db->single();

        if (!$origProduct) {
            $db->query("SELECT description as name FROM invoice_items WHERE item_id = :oid LIMIT 1");
            $db->bind(':oid', $sub->original_item_id);
            $iiRow = $db->single();
            
            $origName = $iiRow ? $iiRow->name : 'Deleted Product';
            $origProduct = (object)[
                'name' => $origName,
                'selling_price' => 0.00
            ];
        }

        $db->query("SELECT name, price AS selling_price FROM items WHERE id = :id");
        $db->bind(':id', $sub->replacement_item_id);
        $replProduct = $db->single();
        if (!$replProduct) {
            throw new Exception("Replacement product not found in the master data.");
        }

        if ($sub->replacement_variation_option_id) {
            $db->query("SELECT price FROM item_variation_options WHERE id = :rvar_id LIMIT 1");
            $db->bind(':rvar_id', $sub->replacement_variation_option_id);
            $rvarRow = $db->single();
            if ($rvarRow && floatval($rvarRow->price) > 0) {
                $replPrice = floatval($rvarRow->price);
            } else {
                $replPrice = floatval($replProduct->selling_price);
            }
        } else {
            $replPrice = floatval($replProduct->selling_price);
        }

        if ($sub->original_variation_option_id) {
            $db->query("SELECT price FROM item_variation_options WHERE id = :ovar_id LIMIT 1");
            $db->bind(':ovar_id', $sub->original_variation_option_id);
            $ovarRow = $db->single();
            if ($ovarRow && floatval($ovarRow->price) > 0) {
                $origPrice = floatval($ovarRow->price);
            } else {
                $origPrice = floatval($origProduct->selling_price);
            }
        } else {
            $origPrice = floatval($origProduct->selling_price);
        }

        $priceChoice = ($pricingChoice === 'original') ? $origPrice : $replPrice;

        $replDescription = $replProduct->name;
        if ($sub->replacement_variation_option_id) {
            $db->query("
                SELECT v.name AS variation_name, vv.value_name
                FROM item_variation_options ivo
                JOIN variations v ON ivo.variation_id = v.id
                JOIN variation_values vv ON ivo.variation_value_id = vv.id
                WHERE ivo.id = :rvar_id LIMIT 1
            ");
            $db->bind(':rvar_id', $sub->replacement_variation_option_id);
            $vRow = $db->single();
            if ($vRow) {
                $replDescription .= " (" . $vRow->variation_name . ": " . $vRow->value_name . ")";
            }
        }

        // Fetch accounts for billing adjustments
        $db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1200' OR account_name LIKE '%Receivable%' LIMIT 1");
        $arAccRow = $db->single();
        $arAccId = $arAccRow ? intval($arAccRow->id) : null;

        $db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Revenue' AND (account_code = '4000' OR account_name LIKE '%Sales%' OR account_name LIKE '%Revenue%') LIMIT 1");
        $revAccRow = $db->single();
        if (!$revAccRow) {
            $db->query("SELECT id FROM chart_of_accounts WHERE account_type = 'Revenue' LIMIT 1");
            $revAccRow = $db->single();
        }
        $revAccId = $revAccRow ? intval($revAccRow->id) : null;

        foreach ($invoices as $invoice) {
            // Find the item details in this invoice
            $sql = "SELECT id, quantity, discount_value, discount_type, total FROM invoice_items WHERE invoice_id = :iid AND item_id = :item_id";
            if ($sub->original_variation_option_id) {
                $sql .= " AND variation_option_id = :ovar_id";
            } else {
                $sql .= " AND (variation_option_id IS NULL OR variation_option_id = 0)";
            }
            $db->query($sql);
            $db->bind(':iid', $invoice->id);
            $db->bind(':item_id', $sub->original_item_id);
            if ($sub->original_variation_option_id) {
                $db->bind(':ovar_id', $sub->original_variation_option_id);
            }
            $invoiceItem = $db->single();

            if ($invoiceItem) {
                $qty = floatval($invoiceItem->quantity);
                $discVal = floatval($invoiceItem->discount_value);
                $discType = $invoiceItem->discount_type;

                $newTotal = $qty * $priceChoice;
                if ($discType === '%') {
                    $newTotal -= ($newTotal * $discVal / 100);
                } else {
                    $newTotal -= ($discVal * $qty);
                }

                // Update the invoice item to refer to the new product description and ID
                $db->query("UPDATE invoice_items 
                            SET item_id = :new_item_id, variation_option_id = :new_var_id, description = :desc, unit_price = :price, total = :total
                            WHERE id = :id");
                $db->bind(':new_item_id', $sub->replacement_item_id);
                $db->bind(':new_var_id', $sub->replacement_variation_option_id);
                $db->bind(':desc', $replDescription);
                $db->bind(':price', $priceChoice);
                $db->bind(':total', $newTotal);
                $db->bind(':id', $invoiceItem->id);
                $db->execute();

                // Recalculate invoice totals
                $db->query("SELECT SUM(total) as subtotal FROM invoice_items WHERE invoice_id = :id");
                $db->bind(':id', $invoice->id);
                $subrow = $db->single();
                $subtotal = $subrow ? floatval($subrow->subtotal) : 0.0;

                $db->query("SELECT total_amount, global_discount_val, global_discount_type, tax_rate_id, tax_amount, journal_entry_id FROM invoices WHERE id = :id");
                $db->bind(':id', $invoice->id);
                $invRow = $db->single();

                if ($invRow) {
                    $oldSub = floatval($invRow->total_amount);
                    $oldDiscVal = floatval($invRow->global_discount_val);
                    $oldDiscType = $invRow->global_discount_type;
                    $oldDisc = ($oldDiscType === '%') ? ($oldSub * $oldDiscVal / 100) : $oldDiscVal;
                    $oldGrand = ($oldSub - $oldDisc) + floatval($invRow->tax_amount);

                    $disc = ($oldDiscType === '%') ? ($subtotal * $oldDiscVal / 100) : $oldDiscVal;
                    $taxVal = 0.0;

                    if ($invRow->tax_rate_id) {
                        $db->query("SELECT rate_percentage FROM tax_rates WHERE id = :tid");
                        $db->bind(':tid', $invRow->tax_rate_id);
                        $taxRateRow = $db->single();
                        if ($taxRateRow) {
                            $taxVal = ($subtotal - $disc) * floatval($taxRateRow->rate_percentage) / 100;
                        }
                    }

                    $grandTotal = max(0.0, ($subtotal - $disc) + $taxVal);

                    $db->query("UPDATE invoices SET total_amount = :sub, tax_amount = :tax WHERE id = :id");
                    $db->bind(':sub', $subtotal);
                    $db->bind(':tax', $taxVal);
                    $db->bind(':id', $invoice->id);
                    $db->execute();

                    $jid = $invRow->journal_entry_id;
                    if ($jid) {
                        if ($arAccId) {
                            $db->query("UPDATE transactions SET debit = :grand WHERE journal_entry_id = :jid AND account_id = :aid AND debit > 0");
                            $db->bind(':grand', $grandTotal);
                            $db->bind(':jid', $jid);
                            $db->bind(':aid', $arAccId);
                            $db->execute();
                        }
                        if ($revAccId) {
                            $db->query("UPDATE transactions SET credit = :grand WHERE journal_entry_id = :jid AND account_id = :aid AND credit > 0");
                            $db->bind(':grand', $grandTotal);
                            $db->bind(':jid', $jid);
                            $db->bind(':aid', $revAccId);
                            $db->execute();
                        }

                        $diffGrand = $grandTotal - $oldGrand;
                        if (abs($diffGrand) > 0.001) {
                            if ($arAccId) {
                                // ACCT-2 FIX: AR (Asset): positive diff = debit, negative = credit
                                $db->updateAccountBalance($arAccId, ($diffGrand > 0 ? $diffGrand : 0), ($diffGrand < 0 ? abs($diffGrand) : 0));
                            }
                            if ($revAccId) {
                                // ACCT-2 FIX: Revenue: positive diff = credit increase, negative = debit decrease
                                $db->updateAccountBalance($revAccId, ($diffGrand < 0 ? abs($diffGrand) : 0), ($diffGrand > 0 ? $diffGrand : 0));
                            }
                        }
                    }
                }
            }
        }

        // Update status of the substitution record
        $db->query("UPDATE product_substitutions SET status = 'Applied', pricing_choice = :choice WHERE id = :id");
        $db->bind(':choice', $pricingChoice);
        $db->bind(':id', $subId);
        $db->execute();
    }
}
