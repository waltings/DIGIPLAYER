<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new PDO(
        "mysql:host=localhost;dbname=vhost15998s0",
        "vhost15998s0",
        "Digiplayer1-401",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $stmt = $db->query("
        SELECT d.id, d.name, d.ip_address, d.created_at, d.status 
        FROM devices d 
        ORDER BY d.created_at DESC
    ");

    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['devices' => $devices]);

} catch (Exception $e) {
    error_log("Status API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
