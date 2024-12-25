<?php
header('Content-Type: application/json');

try {
    $db = new PDO(
        "mysql:host=localhost;dbname=vhost15998s0",
        "vhost15998s0",
        "Digiplayer1-401",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['action'])) {
        throw new Exception('Device ID and action are required');
    }

    // Verify device exists
    $stmt = $db->prepare("SELECT id FROM devices WHERE id = ?");
    $stmt->execute([$data['id']]);
    if (!$stmt->fetch()) {
        throw new Exception('Device not found');
    }

    // Handle different actions
    switch($data['action']) {
        case 'softRestart':
        case 'hardRestart':
            $stmt = $db->prepare("
                INSERT INTO device_commands (device_id, command, parameters, status)
                VALUES (?, 'restart', ?, 'pending')
            ");
            $stmt->execute([
                $data['id'],
                json_encode(['type' => $data['action'] === 'softRestart' ? 'soft' : 'hard'])
            ]);
            break;

        case 'updateContent':
            $stmt = $db->prepare("
                INSERT INTO sync_queue (device_id, action, priority, status)
                VALUES (?, 'content_sync', 1, 'pending')
            ");
            $stmt->execute([$data['id']]);
            break;

        case 'clearCache':
            $stmt = $db->prepare("
                INSERT INTO device_commands (device_id, command, status)
                VALUES (?, 'clear_cache', 'pending')
            ");
            $stmt->execute([$data['id']]);
            break;

        case 'configure':
            if (!isset($data['settings'])) {
                throw new Exception('Settings data required for configure action');
            }
            $stmt = $db->prepare("
                UPDATE devices 
                SET config = JSON_MERGE_PATCH(COALESCE(config, '{}'), ?)
                WHERE id = ?
            ");
            $stmt->execute([
                json_encode($data['settings']),
                $data['id']
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
