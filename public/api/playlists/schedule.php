<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = $_GET['id'];
        $stmt = $db->prepare("SELECT * FROM playlist_schedules WHERE playlist_id = ?");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    }
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $stmt = $db->prepare("REPLACE INTO playlist_schedules 
            (playlist_id, start_date, end_date, start_time, end_time, days) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['playlist_id'],
            $data['start_date'],
            $data['end_date'],
            $data['start_time'],
            $data['end_time'],
            implode(',', $data['days'] ?? [])
        ]);
        echo json_encode(['status' => 'success']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
