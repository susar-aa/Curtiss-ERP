<?php

namespace App\Models;

use PDO;

class UnproductiveVisit
{
    protected static $table = 'unproductive_visits';

    protected static function getDB()
    {
        $db = new \Database();
        return $db->getDbHandler();
    }

    /**
     * Create or update unproductive visit record by UUID
     */
    public static function createOrUpdate(array $data)
    {
        $db = static::getDB();

        $repUserId = $data['rep_user_id'] ?? $data['rep_id'] ?? null;
        $repRouteId = $data['rep_route_id'] ?? $data['route_id'] ?? null;

        // Check if uuid exists
        $stmt = $db->prepare("SELECT id FROM unproductive_visits WHERE uuid = :uuid");
        $stmt->execute([':uuid' => $data['uuid']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $updateStmt = $db->prepare("
                UPDATE unproductive_visits SET 
                    rep_user_id = :rep_user_id,
                    rep_route_id = :rep_route_id,
                    customer_id = :customer_id,
                    reason = :reason,
                    custom_reason = :custom_reason,
                    latitude = :latitude,
                    longitude = :longitude,
                    visit_time = :visit_time
                WHERE uuid = :uuid
            ");
            $updateStmt->execute([
                ':rep_user_id' => $repUserId,
                ':rep_route_id' => $repRouteId,
                ':customer_id' => $data['customer_id'],
                ':reason' => $data['reason'],
                ':custom_reason' => $data['custom_reason'] ?? '',
                ':latitude' => $data['latitude'] ?? null,
                ':longitude' => $data['longitude'] ?? null,
                ':visit_time' => $data['visit_time'],
                ':uuid' => $data['uuid']
            ]);
            return (int)$existing['id'];
        } else {
            $insertStmt = $db->prepare("
                INSERT INTO unproductive_visits (
                    uuid, rep_user_id, rep_route_id, customer_id, reason, custom_reason, latitude, longitude, visit_time, sync_status, created_at
                ) VALUES (
                    :uuid, :rep_user_id, :rep_route_id, :customer_id, :reason, :custom_reason, :latitude, :longitude, :visit_time, 'Synced', NOW()
                )
            ");
            $insertStmt->execute([
                ':uuid' => $data['uuid'],
                ':rep_user_id' => $repUserId,
                ':rep_route_id' => $repRouteId,
                ':customer_id' => $data['customer_id'],
                ':reason' => $data['reason'],
                ':custom_reason' => $data['custom_reason'] ?? '',
                ':latitude' => $data['latitude'] ?? null,
                ':longitude' => $data['longitude'] ?? null,
                ':visit_time' => $data['visit_time']
            ]);
            return (int)$db->lastInsertId();
        }
    }

    /**
     * Get unproductive visits by route ID
     */
    public static function getByRouteId($routeId)
    {
        $db = static::getDB();
        $stmt = $db->prepare("
            SELECT uv.*, c.name as customer_name, c.address as customer_address, u.username as rep_name
            FROM unproductive_visits uv
            LEFT JOIN customers c ON uv.customer_id = c.id
            LEFT JOIN users u ON uv.rep_user_id = u.id
            WHERE uv.rep_route_id = :route_id
            ORDER BY uv.visit_time ASC
        ");
        $stmt->execute([':route_id' => $routeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get unproductive visits count for a specific route
     */
    public static function getCountByRouteId($routeId)
    {
        $db = static::getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM unproductive_visits WHERE rep_route_id = :route_id");
        $stmt->execute([':route_id' => $routeId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get unproductive visits for a customer
     */
    public static function getByCustomerId($customerId)
    {
        $db = static::getDB();
        $stmt = $db->prepare("
            SELECT uv.*, dr.route_name, u.username as rep_name
            FROM unproductive_visits uv
            LEFT JOIN rep_daily_routes dr ON uv.rep_route_id = dr.id
            LEFT JOIN users u ON uv.rep_user_id = u.id
            WHERE uv.customer_id = :customer_id
            ORDER BY uv.visit_time DESC
        ");
        $stmt->execute([':customer_id' => $customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get analytical report data with filters
     */
    public static function getReportData(array $filters = [])
    {
        $db = static::getDB();
        $sql = "
            SELECT uv.*, 
                   c.name as customer_name, 
                   c.address as customer_address, 
                   c.territory as customer_territory,
                   u.username as rep_name,
                   dr.route_name
            FROM unproductive_visits uv
            LEFT JOIN customers c ON uv.customer_id = c.id
            LEFT JOIN users u ON uv.rep_user_id = u.id
            LEFT JOIN rep_daily_routes dr ON uv.rep_route_id = dr.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['start_date'])) {
            $sql .= " AND DATE(uv.visit_time) >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND DATE(uv.visit_time) <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['rep_id'])) {
            $sql .= " AND uv.rep_user_id = :rep_id";
            $params[':rep_id'] = $filters['rep_id'];
        }

        if (!empty($filters['customer_id'])) {
            $sql .= " AND uv.customer_id = :customer_id";
            $params[':customer_id'] = $filters['customer_id'];
        }

        if (!empty($filters['reason'])) {
            $sql .= " AND uv.reason = :reason";
            $params[':reason'] = $filters['reason'];
        }

        $sql .= " ORDER BY uv.visit_time DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

