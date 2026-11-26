<?php
require_once __DIR__ . '/../../../src/db_connection.php';
cors();

try {
    $sql = "SELECT id, reporter_name, contact_number, incident_type, description, barangay,
                   ST_Y(location::geometry) AS latitude, ST_X(location::geometry) AS longitude,
                   status, reported_at
            FROM incidents";
    $params = [];
    if (!empty($_GET['status'])) { $sql .= " WHERE status = :status"; $params[':status'] = $_GET['status']; }
    $sql .= " ORDER BY reported_at DESC LIMIT 100";

    $stmt = getConnection()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    jsonResponse(200, ['count' => count($rows), 'incidents' => $rows]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['error' => 'Database error.']);
}
