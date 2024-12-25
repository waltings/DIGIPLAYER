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

    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['id'])) {
                $stmt = $db->prepare("SELECT * FROM devices WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $device = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$device) {
                    throw new Exception('Device not found', 404);
                }
                
                echo json_encode(['device' => $device]);
            } else {
                $sql = "SELECT id, name, status, ip_address, last_seen, created_at 
                        FROM devices 
                        ORDER BY created_at DESC";
                
                $stmt = $db->query($sql);
                $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode(['devices' => $devices]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['name']) || !isset($data['ip_address'])) {
                throw new Exception('Name and IP address are required');
            }
            
            $stmt = $db->prepare("
                INSERT INTO devices (name, ip_address, status)
                VALUES (?, ?, 'pending')
            ");
            
            $stmt->execute([$data['name'], $data['ip_address']]);
            
            echo json_encode([
                'status' => 'success',
                'id' => $db->lastInsertId()
            ]);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['name']) || !isset($data['ip_address'])) {
                throw new Exception('ID, name and IP address are required');
            }
            
            $stmt = $db->prepare("
                UPDATE devices 
                SET name = ?,
                    ip_address = ?,
                    status = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['name'],
                $data['ip_address'],
                $data['status'] ?? 'pending',
                $data['id']
            ]);
            
            echo json_encode(['status' => 'success']);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id'])) {
                throw new Exception('Device ID required');
            }
            
            $stmt = $db->prepare("DELETE FROM devices WHERE id = ?");
            $stmt->execute([$data['id']]);
            
            echo json_encode(['status' => 'success']);
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    error_log("Error in devices/index.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
