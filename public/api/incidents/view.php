<?php
require_once __DIR__ . '/../../../src/db_connection.php';
cors();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) jsonResponse(400, ['error' => 'A valid numeric id is required, e.g. ?id=1']);

try {
    $stmt = getConnection()->prepare(
        "SELECT id, reporter_name, contact_number, incident_type, description, barangay,
                ST_Y(location::geometry) AS latitude, ST_X(location::geometry) AS longitude,
                status, reported_at
         FROM incidents WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    $row ? jsonResponse(200, $row) : jsonResponse(404, ['error' => 'Incident not found']);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['error' => 'Database error.']);
}
