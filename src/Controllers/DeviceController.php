<?php
namespace Controllers;

class DeviceController extends BaseController {
    public function getDevices() {
    try {
        $sql = "SELECT d.*, 
                GROUP_CONCAT(DISTINCT g.name) as group_names,
                p.name as playlist_name,
                dl.location_name,
                ds.last_heartbeat,
                d.current_playlist_id
            FROM devices d
            LEFT JOIN device_group dg ON d.id = dg.device_id
            LEFT JOIN groups g ON dg.group_id = g.id
            LEFT JOIN playlists p ON d.current_playlist_id = p.id
            LEFT JOIN device_locations dl ON d.id = dl.device_id
            LEFT JOIN device_stats ds ON d.id = ds.device_id
            WHERE 1=1";

        $params = [];

        if (!empty($_GET['search'])) {
            $sql .= " AND (d.name LIKE ? OR d.ip_address LIKE ?)";
            $searchTerm = "%{$_GET['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($_GET['status'])) {
            $sql .= " AND d.status = ?";
            $params[] = $_GET['status'];
        }

        if (!empty($_GET['group_id'])) {
            $sql .= " AND dg.group_id = ?";
            $params[] = $_GET['group_id'];
        }

        $sql .= " GROUP BY d.id ORDER BY d.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $this->jsonResponse(['devices' => $stmt->fetchAll()]);
    } catch (\Exception $e) {
        $this->errorResponse("Failed to fetch devices: " . $e->getMessage());
    }
}

    public function createDevice() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['name']) || !isset($data['ip_address']) || !isset($data['location'])) {
                throw new \Exception('Name, IP address and location are required');
            }
            
            $this->db->beginTransaction();
            
            // Insert device
            $stmt = $this->db->prepare("
                INSERT INTO devices (
                    name, ip_address, status, device_key
                ) VALUES (
                    ?, ?, 'pending', UUID()
                )
            ");
            $stmt->execute([$data['name'], $data['ip_address']]);
            
            $deviceId = $this->db->lastInsertId();
            
            // Add location
            $stmt = $this->db->prepare("
                INSERT INTO device_locations (device_id, location_name)
                VALUES (?, ?)
            ");
            $stmt->execute([$deviceId, $data['location']]);
            
            // Add to group if specified
            if (!empty($data['group_id'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO device_groups (device_id, group_id)
                    VALUES (?, ?)
                ");
                $stmt->execute([$deviceId, $data['group_id']]);
            }
            
            $this->db->commit();
            $this->jsonResponse(['status' => 'success', 'id' => $deviceId]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse("Failed to create device: " . $e->getMessage());
        }
    }

    public function deviceAction() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['device_id']) || !isset($data['action'])) {
                throw new \Exception('Device ID and action are required');
            }
            
            switch($data['action']) {
                case 'restart':
                    $this->queueCommand($data['device_id'], 'restart');
                    break;
                case 'update':
                    $this->queueCommand($data['device_id'], 'update');
                    break;
                case 'configure':
                    // Handle device configuration
                    break;
                default:
                    throw new \Exception('Invalid action');
            }
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to execute device action: " . $e->getMessage());
        }
    }

    private function queueCommand($deviceId, $command) {
        $stmt = $this->db->prepare("
            INSERT INTO scheduled_commands (device_id, command, status)
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$deviceId, $command]);
    }
}
