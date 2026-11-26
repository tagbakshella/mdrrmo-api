<?php
require_once __DIR__ . '/../../../src/db_connection.php';
cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(405, ['error' => 'Method not allowed. Use POST.']);
}

// 1. RECEIVE: accept JSON or regular form data
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

// 2. VALIDATE
$required = ['reporter_name', 'incident_type', 'latitude', 'longitude'];
$missing = array_values(array_filter($required,
    fn($f) => !isset($data[$f]) || trim((string)$data[$f]) === ''));
if ($missing) jsonResponse(422, ['error' => 'Missing required fields', 'fields' => $missing]);

$types = ['Fire','Flood','Earthquake','Landslide','Typhoon','Vehicular Accident','Medical Emergency','Other'];
if (!in_array($data['incident_type'], $types, true)) {
    jsonResponse(422, ['error' => 'Invalid incident_type', 'allowed' => $types]);
}
if (!is_numeric($data['latitude']) || !is_numeric($data['longitude'])
    || abs($data['latitude']) > 90 || abs($data['longitude']) > 180) {
    jsonResponse(422, ['error' => 'Invalid coordinates']);
}

// 3. PROCESS + 4. STORE
try {
    $sql = "INSERT INTO incidents
              (reporter_name, contact_number, incident_type, description, barangay, location)
            VALUES
              (:name, :contact, :type, :descr, :brgy,
               ST_SetSRID(ST_MakePoint(:lng, :lat), 4326)::geography)
            RETURNING id, status, reported_at";
    $stmt = getConnection()->prepare($sql);
    $stmt->execute([
        ':name'    => trim($data['reporter_name']),
        ':contact' => trim($data['contact_number'] ?? ''),
        ':type'    => $data['incident_type'],
        ':descr'   => trim($data['description'] ?? ''),
        ':brgy'    => trim($data['barangay'] ?? ''),
        ':lng'     => (float)$data['longitude'],
        ':lat'     => (float)$data['latitude'],
    ]);
    jsonResponse(201, ['message' => 'Report submitted successfully', 'incident' => $stmt->fetch()]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    jsonResponse(500, ['error' => 'Database error. Please try again.']);
}
