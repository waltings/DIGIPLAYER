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
    
    if (!isset($data['devices']) || !isset($data['action'])) {
        throw new Exception('Missing required parameters');
    }

    $db->beginTransaction();

    foreach ($data['devices'] as $deviceId) {
        switch ($data['action']) {
            case 'restart':
                // Queue restart command
                $stmt = $db->prepare("
                    INSERT INTO scheduled_commands (device_id, command, status)
                    VALUES (?, 'restart', 'pending')
                ");
                $stmt->execute([$deviceId]);
                break;

            case 'update':
                // Queue update command
                $stmt = $db->prepare("
                    INSERT INTO scheduled_commands (device_id, command, status)
                    VALUES (?, 'update', 'pending')
                ");
                $stmt->execute([$deviceId]);
                break;

            case 'change_group':
                if (!isset($data['group_id'])) {
                    throw new Exception('Group ID required for group change');
                }
                $stmt = $db->prepare("
                    UPDATE device_groups 
                    SET group_id = ? 
                    WHERE device_id = ?
                ");
                $stmt->execute([$data['group_id'], $deviceId]);
                break;
        }
    }

    $db->commit();
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
