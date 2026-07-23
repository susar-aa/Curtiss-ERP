<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/Models/JournalEntry.php';
require_once dirname(__DIR__) . '/Models/PettyCashTransaction.php';
require_once dirname(__DIR__) . '/Models/AuditLog.php';

class FuelService {
    private Database $db;
    private JournalEntry $journal;
    private PettyCashTransaction $pettyCash;
    private AuditLog $audit;

    public function __construct() {
        $this->db = new Database();
        $this->journal = new JournalEntry();
        $this->pettyCash = new PettyCashTransaction();
        $this->audit = new AuditLog();
    }

    /**
     * Get or create a unique expense sub-account for the vehicle
     */
    public function getOrCreateVehicleExpenseAccount(string $vehicleNumber): int {
        // 1. Search for parent "6700 - Travel Transport (Fuel)"
        $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code = '6700' OR account_name LIKE '%Travel%Transport%' LIMIT 1");
        $parent = $this->db->single();
        if (!$parent) {
            $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_type = 'Expense' AND (account_name LIKE '%Fuel%' OR account_name LIKE '%Travel%') LIMIT 1");
            $parent = $this->db->single();
        }

        if (!$parent) {
            $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id) VALUES ('6700', 'Travel Transport (Fuel)', 'Expense', NULL)");
            $this->db->execute();
            $parentId = $this->db->lastInsertId();
            $parentCode = '6700';
        } else {
            $parentId = intval($parent->id);
            $parentCode = $parent->account_code;
        }

        // 2. Search for dynamic sub-account for the vehicle
        $this->db->query("SELECT id FROM chart_of_accounts WHERE account_name = :name LIMIT 1");
        $this->db->bind(':name', "Vehicle Expense - " . $vehicleNumber);
        $acc = $this->db->single();
        if ($acc) {
            return intval($acc->id);
        }

        // Create new child account
        $this->db->query("SELECT MAX(CAST(account_code AS UNSIGNED)) as max_code FROM chart_of_accounts WHERE parent_id = :pid");
        $this->db->bind(':pid', $parentId);
        $maxRow = $this->db->single();
        $maxCode = $maxRow ? intval($maxRow->max_code) : 0;
        if ($maxCode > 0) {
            $subAccountCode = strval($maxCode + 1);
        } else {
            $subAccountCode = strval(intval($parentCode) + 1);
        }

        $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id) VALUES (:code, :name, 'Expense', :pid)");
        $this->db->bind(':code', $subAccountCode);
        $this->db->bind(':name', "Vehicle Expense - " . $vehicleNumber);
        $this->db->bind(':pid', $parentId);
        $this->db->execute();
        return intval($this->db->lastInsertId());
    }

    /**
     * Record a fuel transaction
     */
    public function recordFuelEntry(array $data, int $userId): string|bool {
        $vehicleId = intval($data['vehicle_id']);
        $driverId = !empty($data['driver_id']) ? intval($data['driver_id']) : null;
        $odometerReading = intval($data['odometer_reading']);
        $fuelTypeId = intval($data['fuel_type_id']);
        $quantity = floatval($data['quantity']);
        $pricePerLiter = floatval($data['price_per_liter']);
        $totalAmount = floatval($data['total_amount']);
        $fuelStation = $data['fuel_station'] ?? '';
        $paymentSource = $data['payment_source'] ?? 'Petty Cash';
        $bankAccountId = !empty($data['bank_account_id']) ? intval($data['bank_account_id']) : null;
        $repRouteId = !empty($data['rep_route_id']) ? intval($data['rep_route_id']) : null;
        $remarks = $data['remarks'] ?? '';

        // Fetch vehicle number and details
        $this->db->query("SELECT vehicle_number, current_odometer FROM vehicles WHERE id = :id");
        $this->db->bind(':id', $vehicleId);
        $vehicle = $this->db->single();
        if (!$vehicle) {
            return "Vehicle not found.";
        }

        // Validate odometer reading (must be >= current odometer)
        if ($odometerReading < intval($vehicle->current_odometer)) {
            return "Odometer reading cannot be less than the vehicle's current odometer (" . $vehicle->current_odometer . " km).";
        }

        // Resolve expense account for this vehicle
        $vehicleExpenseAccountId = $this->getOrCreateVehicleExpenseAccount($vehicle->vehicle_number);

        // Resolve source account ID
        $sourceAccountId = null;
        if ($paymentSource === 'Petty Cash') {
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1020' OR account_name LIKE '%Petty Cash%' LIMIT 1");
            $coa = $this->db->single();
            if (!$coa) return "Petty Cash chart of account not found.";
            $sourceAccountId = intval($coa->id);

            // Validate Petty Cash Balance
            $avail = $this->pettyCash->getAvailableBalance();
            if ($avail < $totalAmount) {
                return "Insufficient funds in Petty Cash. Available: Rs. " . number_format($avail, 2);
            }
        } elseif ($paymentSource === 'Cash in Hand') {
            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_code = '1100' OR account_name LIKE '%Cash in Hand%' LIMIT 1");
            $coa = $this->db->single();
            if (!$coa) return "Cash in Hand chart of account not found.";
            $sourceAccountId = intval($coa->id);
        } elseif ($paymentSource === 'Bank Account') {
            if (!$bankAccountId) {
                return "Please select a bank account.";
            }
            $this->db->query("SELECT chart_of_account_id FROM bank_accounts WHERE id = :id");
            $this->db->bind(':id', $bankAccountId);
            $ba = $this->db->single();
            if (!$ba) return "Selected Bank Account not found.";
            $sourceAccountId = intval($ba->chart_of_account_id);
        } else {
            return "Invalid payment source selected.";
        }

        // Fetch driver name for references
        $driverName = 'Driver';
        if ($driverId) {
            $this->db->query("SELECT first_name, last_name FROM employees WHERE id = :id");
            $this->db->bind(':id', $driverId);
            $drvObj = $this->db->single();
            if ($drvObj) {
                $driverName = $drvObj->first_name . ' ' . $drvObj->last_name;
            }
        }

        $date = date('Y-m-d H:i:s');
        $ref = 'FUEL-' . time() . '-' . rand(10, 99);
        $journalDesc = "Fuel filling: " . $vehicle->vehicle_number . " (" . $quantity . " L at Rs. " . $pricePerLiter . ") by " . $driverName . ". Payment Source: " . $paymentSource;

        // Construct Journal Lines
        $lines = [
            [
                'account_id' => $vehicleExpenseAccountId,
                'debit' => $totalAmount,
                'credit' => 0.00,
                'description' => "Debit: Vehicle Fuel Expense (" . $vehicle->vehicle_number . ")"
            ],
            [
                'account_id' => $sourceAccountId,
                'debit' => 0.00,
                'credit' => $totalAmount,
                'description' => "Credit: Payment from " . $paymentSource
            ]
        ];

        // 1. Post Journal Entry
        $postRes = $this->journal->postEntry(date('Y-m-d', strtotime($date)), $ref, $journalDesc, $lines, $userId);
        if ($postRes !== true) {
            return $postRes ?: "Failed to post journal entry.";
        }

        // Get Journal Entry ID
        $this->db->query("SELECT id FROM journal_entries WHERE reference = :ref LIMIT 1");
        $this->db->bind(':ref', $ref);
        $je = $this->db->single();
        $journalEntryId = $je ? intval($je->id) : null;

        // 2. Create Petty Cash Transaction if source is Petty Cash
        $pettyCashTxId = null;
        if ($paymentSource === 'Petty Cash') {
            try {
                $this->db->beginTransaction();
                $this->db->query("INSERT INTO petty_cash_transactions (transaction_date, type, amount, reference, description, paid_to, account_id, status, created_by, approved_by, approved_at, journal_entry_id) 
                                  VALUES (NOW(), 'expense', :amount, :ref, :desc, :paid_to, :acc_id, 'Approved', :uid, :uid, NOW(), :jid)");
                $this->db->bind(':amount', $totalAmount);
                $this->db->bind(':ref', $ref);
                $this->db->bind(':desc', $journalDesc);
                $this->db->bind(':paid_to', $driverName);
                $this->db->bind(':acc_id', $vehicleExpenseAccountId);
                $this->db->bind(':uid', $userId);
                $this->db->bind(':jid', $journalEntryId);
                $this->db->execute();
                
                $pettyCashTxId = intval($this->db->lastInsertId());
                $this->db->commit();
            } catch (PDOException $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                return "Petty Cash recording error: " . $e->getMessage();
            }
        }

        // 3. Create Fuel Record
        try {
            $this->db->beginTransaction();
            $this->db->query("INSERT INTO fuel_records (vehicle_id, driver_id, odometer_reading, fuel_type_id, quantity, price_per_liter, total_amount, fuel_station, payment_source, bank_account_id, petty_cash_transaction_id, journal_entry_id, rep_route_id, remarks, created_by) 
                              VALUES (:vehicle_id, :driver_id, :odometer_reading, :fuel_type_id, :quantity, :price_per_liter, :total_amount, :fuel_station, :payment_source, :bank_account_id, :petty_cash_transaction_id, :journal_entry_id, :rep_route_id, :remarks, :created_by)");
            $this->db->bind(':vehicle_id', $vehicleId);
            $this->db->bind(':driver_id', $driverId);
            $this->db->bind(':odometer_reading', $odometerReading);
            $this->db->bind(':fuel_type_id', $fuelTypeId);
            $this->db->bind(':quantity', $quantity);
            $this->db->bind(':price_per_liter', $pricePerLiter);
            $this->db->bind(':total_amount', $totalAmount);
            $this->db->bind(':fuel_station', $fuelStation);
            $this->db->bind(':payment_source', $paymentSource);
            $this->db->bind(':bank_account_id', $bankAccountId);
            $this->db->bind(':petty_cash_transaction_id', $pettyCashTxId);
            $this->db->bind(':journal_entry_id', $journalEntryId);
            $this->db->bind(':rep_route_id', $repRouteId);
            $this->db->bind(':remarks', $remarks);
            $this->db->bind(':created_by', $userId);
            $this->db->execute();

            $fuelRecordId = intval($this->db->lastInsertId());

            // Update Vehicle current odometer
            $this->db->query("UPDATE vehicles SET current_odometer = :odo WHERE id = :id");
            $this->db->bind(':odo', $odometerReading);
            $this->db->bind(':id', $vehicleId);
            $this->db->execute();

            // Log event in vehicle history
            $historyDesc = "Fuel filled: " . $quantity . " Liters of " . $pricePerLiter . "/L. Odometer updated to " . $odometerReading . " km.";
            $this->db->query("INSERT INTO vehicle_history (vehicle_id, event_type, description, created_by) 
                              VALUES (:vid, 'Fuel Refill', :desc, :uid)");
            $this->db->bind(':vid', $vehicleId);
            $this->db->bind(':desc', $historyDesc);
            $this->db->bind(':uid', $userId);
            $this->db->execute();

            $this->db->commit();

            // Log audit
            $this->audit->log('Record Fuel Refill', 'Vehicle', $historyDesc, $fuelRecordId);
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return "Failed to save fuel record: " . $e->getMessage();
        }
    }

    /**
     * Delete/void a fuel record
     */
    public function deleteFuelEntry(int $fuelRecordId, int $userId): string|bool {
        // Fetch fuel record details
        $this->db->query("SELECT * FROM fuel_records WHERE id = :id");
        $this->db->bind(':id', $fuelRecordId);
        $record = $this->db->single();
        if (!$record) {
            return "Fuel record not found.";
        }

        try {
            $this->db->beginTransaction();

            // 1. Void Journal Entry
            if (!empty($record->journal_entry_id)) {
                $voidRes = $this->journal->voidEntry(intval($record->journal_entry_id));
                if (!$voidRes) {
                    $this->db->rollBack();
                    return "Failed to void journal entry linked to this fuel record. The financial period may be closed.";
                }
            }

            // 2. Delete Petty Cash Transaction if exists
            if (!empty($record->petty_cash_transaction_id)) {
                $this->db->query("DELETE FROM petty_cash_transactions WHERE id = :id");
                $this->db->bind(':id', $record->petty_cash_transaction_id);
                $this->db->execute();
            }

            // 3. Delete Fuel Record
            $this->db->query("DELETE FROM fuel_records WHERE id = :id");
            $this->db->bind(':id', $fuelRecordId);
            $this->db->execute();

            // Log event in vehicle history
            $historyDesc = "Deleted fuel refill record of " . $record->quantity . " Liters (Odo: " . $record->odometer_reading . "). Linked accounting entries reversed.";
            $this->db->query("INSERT INTO vehicle_history (vehicle_id, event_type, description, created_by) 
                              VALUES (:vid, 'Delete Fuel Refill', :desc, :uid)");
            $this->db->bind(':vid', $record->vehicle_id);
            $this->db->bind(':desc', $historyDesc);
            $this->db->bind(':uid', $userId);
            $this->db->execute();

            // Recompute vehicle odometer (revert to previous highest odometer reading from remaining fuel records)
            $this->db->query("SELECT MAX(odometer_reading) as max_odo FROM fuel_records WHERE vehicle_id = :vid");
            $this->db->bind(':vid', $record->vehicle_id);
            $maxRow = $this->db->single();
            $newOdo = $maxRow && $maxRow->max_odo ? intval($maxRow->max_odo) : 0;

            $this->db->query("UPDATE vehicles SET current_odometer = :odo WHERE id = :id");
            $this->db->bind(':odo', $newOdo);
            $this->db->bind(':id', $record->vehicle_id);
            $this->db->execute();

            $this->db->commit();

            // Log audit
            $this->audit->log('Void Fuel Refill', 'Vehicle', $historyDesc, $fuelRecordId);
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return "Failed to delete fuel record: " . $e->getMessage();
        }
    }
}
