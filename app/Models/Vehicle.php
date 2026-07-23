<?php
class Vehicle {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllVehicles() {
        $this->db->query("SELECT v.*, ft.fuel_type as fuel_type_name, e.first_name as driver_first, e.last_name as driver_last
                          FROM vehicles v
                          LEFT JOIN fuel_types ft ON v.fuel_type_id = ft.id
                          LEFT JOIN employees e ON v.assigned_driver_id = e.id
                          ORDER BY v.vehicle_number ASC");
        return $this->db->resultSet() ?: [];
    }

    public function getVehiclesPaginated($search = '', $limit = 10, $offset = 0) {
        $this->db->query("SELECT v.*, ft.fuel_type as fuel_type_name, e.first_name as driver_first, e.last_name as driver_last
                          FROM vehicles v
                          LEFT JOIN fuel_types ft ON v.fuel_type_id = ft.id
                          LEFT JOIN employees e ON v.assigned_driver_id = e.id
                          WHERE v.vehicle_number LIKE :search OR v.model LIKE :search OR v.type LIKE :search 
                          ORDER BY v.vehicle_number ASC 
                          LIMIT :limit OFFSET :offset");
        $this->db->bind(':search', "%$search%");
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->resultSet() ?: [];
    }

    public function getTotalVehicles($search = '') {
        $this->db->query("SELECT COUNT(*) as total FROM vehicles WHERE vehicle_number LIKE :search OR model LIKE :search OR type LIKE :search");
        $this->db->bind(':search', "%$search%");
        $row = $this->db->single();
        return $row->total ?? 0;
    }

    public function getVehicleById($id) {
        $this->db->query("SELECT v.*, ft.fuel_type as fuel_type_name, e.first_name as driver_first, e.last_name as driver_last
                          FROM vehicles v
                          LEFT JOIN fuel_types ft ON v.fuel_type_id = ft.id
                          LEFT JOIN employees e ON v.assigned_driver_id = e.id
                          WHERE v.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addVehicle($data, $userId = null) {
        try {
            $this->db->beginTransaction();

            $this->db->query("INSERT INTO vehicles (vehicle_number, registration_number, chassis_number, engine_number, model, type, status, assigned_driver_id, fuel_type_id, fuel_tank_capacity, avg_fuel_consumption, current_odometer, next_service_mileage, insurance_expiry, license_expiry) 
                              VALUES (:vehicle_number, :registration_number, :chassis_number, :engine_number, :model, :type, :status, :assigned_driver_id, :fuel_type_id, :fuel_tank_capacity, :avg_fuel_consumption, :current_odometer, :next_service_mileage, :insurance_expiry, :license_expiry)");
            $this->db->bind(':vehicle_number', $data['vehicle_number']);
            $this->db->bind(':registration_number', !empty($data['registration_number']) ? $data['registration_number'] : null);
            $this->db->bind(':chassis_number', !empty($data['chassis_number']) ? $data['chassis_number'] : null);
            $this->db->bind(':engine_number', !empty($data['engine_number']) ? $data['engine_number'] : null);
            $this->db->bind(':model', $data['model']);
            $this->db->bind(':type', $data['type']);
            $this->db->bind(':status', $data['status'] ?? 'Active');
            $this->db->bind(':assigned_driver_id', !empty($data['assigned_driver_id']) ? intval($data['assigned_driver_id']) : null);
            $this->db->bind(':fuel_type_id', !empty($data['fuel_type_id']) ? intval($data['fuel_type_id']) : null);
            $this->db->bind(':fuel_tank_capacity', !empty($data['fuel_tank_capacity']) ? floatval($data['fuel_tank_capacity']) : null);
            $this->db->bind(':avg_fuel_consumption', !empty($data['avg_fuel_consumption']) ? floatval($data['avg_fuel_consumption']) : null);
            $this->db->bind(':current_odometer', !empty($data['current_odometer']) ? intval($data['current_odometer']) : 0);
            $this->db->bind(':next_service_mileage', !empty($data['next_service_mileage']) ? intval($data['next_service_mileage']) : null);
            $this->db->bind(':insurance_expiry', !empty($data['insurance_expiry']) ? $data['insurance_expiry'] : null);
            $this->db->bind(':license_expiry', !empty($data['license_expiry']) ? $data['license_expiry'] : null);
            $this->db->execute();

            $vehicleId = intval($this->db->lastInsertId());

            // Add history record
            $this->db->query("INSERT INTO vehicle_history (vehicle_id, event_type, description, created_by) 
                              VALUES (:vehicle_id, 'Registration', 'Vehicle registered in system.', :uid)");
            $this->db->bind(':vehicle_id', $vehicleId);
            $this->db->bind(':uid', $userId);
            $this->db->execute();

            if (!empty($data['assigned_driver_id'])) {
                // Fetch driver name
                $this->db->query("SELECT first_name, last_name FROM employees WHERE id = :id");
                $this->db->bind(':id', $data['assigned_driver_id']);
                $drv = $this->db->single();
                $drvName = $drv ? $drv->first_name . ' ' . $drv->last_name : 'Unknown';
                
                $this->db->query("INSERT INTO vehicle_history (vehicle_id, event_type, description, created_by) 
                                  VALUES (:vehicle_id, 'Driver Assignment', :desc, :uid)");
                $this->db->bind(':vehicle_id', $vehicleId);
                $this->db->bind(':desc', "Driver assigned: " . $drvName);
                $this->db->bind(':uid', $userId);
                $this->db->execute();
            }

            // AUTOMATIC ACCOUNT CREATION FOR VEHICLE (Fuel/Maintenance Expenses)
            // 1. Search for parent "6700 - Travel Transport (Fuel)"
            $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_code = '6700' OR account_name LIKE '%Travel%Transport%' LIMIT 1");
            $parent = $this->db->single();
            if (!$parent) {
                // Search for any Travel/Fuel account as fallback
                $this->db->query("SELECT id, account_code FROM chart_of_accounts WHERE account_type = 'Expense' AND (account_name LIKE '%Fuel%' OR account_name LIKE '%Travel%') LIMIT 1");
                $parent = $this->db->single();
            }

            if (!$parent) {
                // If still missing, create the 6700 parent account
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id) VALUES ('6700', 'Travel Transport (Fuel)', 'Expense', NULL)");
                $this->db->execute();
                $parentId = $this->db->lastInsertId();
                $parentCode = '6700';
            } else {
                $parentId = $parent->id;
                $parentCode = $parent->account_code;
            }

            // 2. Insert dynamic sub-account for the registered vehicle under the parent
            $this->db->query("SELECT MAX(CAST(account_code AS UNSIGNED)) as max_code FROM chart_of_accounts WHERE parent_id = :pid");
            $this->db->bind(':pid', $parentId);
            $maxRow = $this->db->single();
            $maxCode = $maxRow ? intval($maxRow->max_code) : 0;
            if ($maxCode > 0) {
                $subAccountCode = strval($maxCode + 1);
            } else {
                $subAccountCode = strval(intval($parentCode) + 1);
            }

            $this->db->query("SELECT id FROM chart_of_accounts WHERE account_name = :name");
            $this->db->bind(':name', "Vehicle Expense - " . $data['vehicle_number']);
            if (!$this->db->single()) {
                $this->db->query("INSERT INTO chart_of_accounts (account_code, account_name, account_type, parent_id) VALUES (:code, :name, 'Expense', :pid)");
                $this->db->bind(':code', $subAccountCode);
                $this->db->bind(':name', "Vehicle Expense - " . $data['vehicle_number']);
                $this->db->bind(':pid', $parentId);
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function updateVehicle($id, $data, $userId = null) {
        // Fetch current vehicle state to detect changes for history
        $this->db->query("SELECT * FROM vehicles WHERE id = :id");
        $this->db->bind(':id', $id);
        $old = $this->db->single();

        try {
            $this->db->beginTransaction();

            $this->db->query("UPDATE vehicles 
                              SET vehicle_number = :vehicle_number, registration_number = :registration_number, chassis_number = :chassis_number, engine_number = :engine_number, model = :model, type = :type, status = :status, assigned_driver_id = :assigned_driver_id, fuel_type_id = :fuel_type_id, fuel_tank_capacity = :fuel_tank_capacity, avg_fuel_consumption = :avg_fuel_consumption, current_odometer = :current_odometer, next_service_mileage = :next_service_mileage, insurance_expiry = :insurance_expiry, license_expiry = :license_expiry 
                              WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->bind(':vehicle_number', $data['vehicle_number']);
            $this->db->bind(':registration_number', !empty($data['registration_number']) ? $data['registration_number'] : null);
            $this->db->bind(':chassis_number', !empty($data['chassis_number']) ? $data['chassis_number'] : null);
            $this->db->bind(':engine_number', !empty($data['engine_number']) ? $data['engine_number'] : null);
            $this->db->bind(':model', $data['model']);
            $this->db->bind(':type', $data['type']);
            $this->db->bind(':status', $data['status'] ?? 'Active');
            $this->db->bind(':assigned_driver_id', !empty($data['assigned_driver_id']) ? intval($data['assigned_driver_id']) : null);
            $this->db->bind(':fuel_type_id', !empty($data['fuel_type_id']) ? intval($data['fuel_type_id']) : null);
            $this->db->bind(':fuel_tank_capacity', !empty($data['fuel_tank_capacity']) ? floatval($data['fuel_tank_capacity']) : null);
            $this->db->bind(':avg_fuel_consumption', !empty($data['avg_fuel_consumption']) ? floatval($data['avg_fuel_consumption']) : null);
            $this->db->bind(':current_odometer', !empty($data['current_odometer']) ? intval($data['current_odometer']) : 0);
            $this->db->bind(':next_service_mileage', !empty($data['next_service_mileage']) ? intval($data['next_service_mileage']) : null);
            $this->db->bind(':insurance_expiry', !empty($data['insurance_expiry']) ? $data['insurance_expiry'] : null);
            $this->db->bind(':license_expiry', !empty($data['license_expiry']) ? $data['license_expiry'] : null);
            $this->db->execute();

            // Track changes in history
            if ($old) {
                if ($old->status !== $data['status']) {
                    $this->db->query("INSERT INTO vehicle_history (vehicle_id, event_type, description, created_by) 
                                      VALUES (:vid, 'Status Change', :desc, :uid)");
                    $this->db->bind(':vid', $id);
                    $this->db->bind(':desc', "Status changed from " . $old->status . " to " . $data['status']);
                    $this->db->bind(':uid', $userId);
                    $this->db->execute();
                }
                if (intval($old->assigned_driver_id) !== (!empty($data['assigned_driver_id']) ? intval($data['assigned_driver_id']) : 0)) {
                    $drvName = 'Unassigned';
                    if (!empty($data['assigned_driver_id'])) {
                        $this->db->query("SELECT first_name, last_name FROM employees WHERE id = :id");
                        $this->db->bind(':id', $data['assigned_driver_id']);
                        $drv = $this->db->single();
                        $drvName = $drv ? $drv->first_name . ' ' . $drv->last_name : 'Unknown';
                    }
                    $this->db->query("INSERT INTO vehicle_history (vehicle_id, event_type, description, created_by) 
                                      VALUES (:vid, 'Driver Assignment', :desc, :uid)");
                    $this->db->bind(':vid', $id);
                    $this->db->bind(':desc', "Assigned driver changed to: " . $drvName);
                    $this->db->bind(':uid', $userId);
                    $this->db->execute();
                }
                if (intval($old->current_odometer) !== intval($data['current_odometer'])) {
                    $this->db->query("INSERT INTO vehicle_history (vehicle_id, event_type, description, created_by) 
                                      VALUES (:vid, 'Odometer Update', :desc, :uid)");
                    $this->db->bind(':vid', $id);
                    $this->db->bind(':desc', "Odometer updated from " . $old->current_odometer . " km to " . $data['current_odometer'] . " km");
                    $this->db->bind(':uid', $userId);
                    $this->db->execute();
                }
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function deleteVehicle($id) {
        $this->db->query("DELETE FROM vehicles WHERE id = :id");
        $this->db->bind(':id', $id);
        try {
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getDrivers() {
        $this->db->query("SELECT id, first_name, last_name, email FROM employees WHERE LOWER(job_title) LIKE '%driver%' OR job_title IS NULL OR job_title = '' ORDER BY first_name ASC");
        return $this->db->resultSet() ?: [];
    }

    public function getFuelTypes() {
        $this->db->query("SELECT * FROM fuel_types ORDER BY fuel_type ASC");
        return $this->db->resultSet() ?: [];
    }

    public function getFuelTypeById($id) {
        $this->db->query("SELECT * FROM fuel_types WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addFuelType($data) {
        $this->db->query("INSERT INTO fuel_types (fuel_type, price_per_liter) VALUES (:type, :price)");
        $this->db->bind(':type', $data['fuel_type']);
        $this->db->bind(':price', $data['price_per_liter']);
        return $this->db->execute();
    }

    public function updateFuelType($id, $data) {
        try {
            $this->db->beginTransaction();
            // Fetch old price to detect change
            $this->db->query("SELECT price_per_liter FROM fuel_types WHERE id = :id");
            $this->db->bind(':id', $id);
            $old = $this->db->single();

            $this->db->query("UPDATE fuel_types SET fuel_type = :type, price_per_liter = :price WHERE id = :id");
            $this->db->bind(':id', $id);
            $this->db->bind(':type', $data['fuel_type']);
            $this->db->bind(':price', $data['price_per_liter']);
            $this->db->execute();

            // Record history if price changed
            if ($old && floatval($old->price_per_liter) !== floatval($data['price_per_liter'])) {
                $this->db->query("INSERT INTO fuel_price_history (fuel_type_id, price_per_liter) VALUES (:id, :price)");
                $this->db->bind(':id', $id);
                $this->db->bind(':price', $data['price_per_liter']);
                $this->db->execute();
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            return false;
        }
    }

    public function deleteFuelType($id) {
        $this->db->query("DELETE FROM fuel_types WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getFuelRecords($vehicleId = null) {
        $sql = "SELECT fr.*, v.vehicle_number, ft.fuel_type as fuel_type_name, e.first_name as driver_first, e.last_name as driver_last
                FROM fuel_records fr
                JOIN vehicles v ON fr.vehicle_id = v.id
                LEFT JOIN fuel_types ft ON fr.fuel_type_id = ft.id
                LEFT JOIN employees e ON fr.driver_id = e.id";
        if ($vehicleId !== null) {
            $sql .= " WHERE fr.vehicle_id = :vid";
        }
        $sql .= " ORDER BY fr.created_at DESC";
        $this->db->query($sql);
        if ($vehicleId !== null) {
            $this->db->bind(':vid', $vehicleId);
        }
        return $this->db->resultSet() ?: [];
    }

    public function getOdometerHistory($vehicleId) {
        $this->db->query("SELECT odometer_reading, quantity, total_amount, created_at 
                          FROM fuel_records 
                          WHERE vehicle_id = :vid 
                          ORDER BY odometer_reading DESC, created_at DESC");
        $this->db->bind(':vid', $vehicleId);
        return $this->db->resultSet() ?: [];
    }

    public function getFuelConsumptionHistory($vehicleId) {
        $this->db->query("SELECT fr.*, ft.fuel_type as fuel_type_name, e.first_name as driver_first, e.last_name as driver_last 
                          FROM fuel_records fr
                          LEFT JOIN fuel_types ft ON fr.fuel_type_id = ft.id
                          LEFT JOIN employees e ON fr.driver_id = e.id
                          WHERE fr.vehicle_id = :vid
                          ORDER BY fr.odometer_reading ASC, fr.created_at ASC");
        $this->db->bind(':vid', $vehicleId);
        $records = $this->db->resultSet() ?: [];
        
        $history = [];
        $prevOdo = null;
        foreach ($records as $r) {
            $distance = 0;
            $consumption = 0;
            if ($prevOdo !== null && $r->odometer_reading > $prevOdo) {
                $distance = $r->odometer_reading - $prevOdo;
                if ($r->quantity > 0) {
                    $consumption = $distance / floatval($r->quantity); // Km / L
                }
            }
            $r->distance_traveled = $distance;
            $r->calculated_consumption = $consumption;
            $history[] = $r;
            
            $prevOdo = intval($r->odometer_reading);
        }
        return array_reverse($history);
    }

    public function getRelatedTransactions($vehicleId) {
        $this->db->query("SELECT vehicle_number FROM vehicles WHERE id = :id");
        $this->db->bind(':id', $vehicleId);
        $v = $this->db->single();
        if (!$v) return [];

        $this->db->query("SELECT t.*, je.entry_date, je.reference, je.description as je_desc, coa.account_name
                          FROM transactions t
                          JOIN journal_entries je ON t.journal_entry_id = je.id
                          JOIN chart_of_accounts coa ON t.account_id = coa.id
                          WHERE coa.account_name = :name
                          ORDER BY je.entry_date DESC, je.id DESC");
        $this->db->bind(':name', "Vehicle Expense - " . $v->vehicle_number);
        return $this->db->resultSet() ?: [];
    }

    public function getVehicleHistory($vehicleId) {
        $this->db->query("SELECT vh.*, u.username 
                          FROM vehicle_history vh
                          LEFT JOIN users u ON vh.created_by = u.id
                          WHERE vh.vehicle_id = :vid
                          ORDER BY vh.created_at DESC");
        $this->db->bind(':vid', $vehicleId);
        return $this->db->resultSet() ?: [];
    }

    public function getVehicleRouteAssignments($vehicleId) {
        // Query rep routes assigned to this vehicle
        // In Curtiss ERP, daily routes are linked to vehicles. Let's check table rep_daily_routes.
        // Let's check if rep_daily_routes has a vehicle_id or vehicle_number column.
        // Yes, let's find out how daily routes are linked to vehicles.
        // Usually daily routes have vehicle_id. Let's search rep_daily_routes structure!
        // We can check if a column like vehicle_id exists.
        $this->db->query("SHOW COLUMNS FROM rep_daily_routes LIKE 'vehicle_id'");
        $hasVehId = !empty($this->db->resultSet());

        $this->db->query("SHOW COLUMNS FROM rep_daily_routes LIKE 'vehicle_number'");
        $hasVehNum = !empty($this->db->resultSet());

        if ($hasVehId) {
            $this->db->query("SELECT r.*, e.first_name, e.last_name 
                              FROM rep_daily_routes r
                              LEFT JOIN employees e ON r.user_id = e.id
                              WHERE r.vehicle_id = :vid
                              ORDER BY r.start_time DESC");
            $this->db->bind(':vid', $vehicleId);
            return $this->db->resultSet() ?: [];
        } elseif ($hasVehNum) {
            $this->db->query("SELECT vehicle_number FROM vehicles WHERE id = :id");
            $this->db->bind(':id', $vehicleId);
            $v = $this->db->single();
            $num = $v ? $v->vehicle_number : '';

            $this->db->query("SELECT r.*, u.username 
                              FROM rep_daily_routes r
                              LEFT JOIN users u ON r.user_id = u.id
                              WHERE r.vehicle_number = :num
                              ORDER BY r.start_time DESC");
            $this->db->bind(':num', $num);
            return $this->db->resultSet() ?: [];
        }
        return [];
    }
}
