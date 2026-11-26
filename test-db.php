<?php
require_once __DIR__ . '/src/db_connection.php';
cors();
try {
    $db = getConnection();
    jsonResponse(200, [
        'status'   => 'Connected',
        'postgres' => $db->query('SELECT version()')->fetchColumn(),
        'postgis'  => $db->query('SELECT PostGIS_Version()')->fetchColumn(),
    ]);
} catch (Throwable $e) {
    jsonResponse(500, ['status' => 'Failed', 'error' => $e->getMessage()]);
}
