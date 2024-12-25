<?php
namespace Controllers;

class MonitoringController extends BaseController {
    public function getSystemStatus() {
        try {
            // Get device status summary
            $deviceStats = $this->db->query("
                SELECT 
                    COUNT(*) as total_devices,
                    SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online_devices,
                    SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline_devices,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_devices
                FROM devices
            ")->fetch();

            // Get content stats
            $contentStats = $this->db->query("
                SELECT 
                    COUNT(*) as total_media,
                    SUM(CASE WHEN type = 'video' THEN 1 ELSE 0 END) as video_count,
                    SUM(CASE WHEN type = 'image' THEN 1 ELSE 0 END) as image_count
                FROM media
            ")->fetch();

            // Get active playlists
            $playlistStats = $this->db->query("
                SELECT COUNT(*) as active_playlists 
                FROM playlists 
                WHERE status = 'active'
            ")->fetch();

            $this->jsonResponse([
                'devices' => $deviceStats,
                'content' => $contentStats,
                'playlists' => $playlistStats
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch system status");
        }
    }

    public function getDeviceStats($deviceId) {
        try {
            $stmt = $this->db->prepare("
                SELECT ds.*, 
                       d.name as device_name,
                       d.status,
                       p.name as current_playlist,
                       m.name as current_media
                FROM device_stats ds
                JOIN devices d ON ds.device_id = d.id
                LEFT JOIN playlists p ON d.current_playlist_id = p.id
                LEFT JOIN media m ON d.current_media_id = m.id
                WHERE ds.device_id = :device_id
                ORDER BY ds.created_at DESC
                LIMIT 100
            ");
            
            $stmt->execute(['device_id' => $deviceId]);
            $this->jsonResponse(['stats' => $stmt->fetchAll()]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch device stats");
        }
    }

    public function updateDeviceStats() {
        $data = $this->validateRequest(['device_id']);
        try {
            $stmt = $this->db->prepare("
                INSERT INTO device_stats 
                (device_id, cpu_usage, memory_usage, storage_usage, temperature, 
                 network_speed, error_count, uptime)
                VALUES 
                (:device_id, :cpu_usage, :memory_usage, :storage_usage, :temperature,
                 :network_speed, :error_count, :uptime)
            ");
            
            $stmt->execute([
                'device_id' => $data['device_id'],
                'cpu_usage' => $data['cpu_usage'] ?? null,
                'memory_usage' => $data['memory_usage'] ?? null,
                'storage_usage' => $data['storage_usage'] ?? null,
                'temperature' => $data['temperature'] ?? null,
                'network_speed' => $data['network_speed'] ?? null,
                'error_count' => $data['error_count'] ?? 0,
                'uptime' => $data['uptime'] ?? null
            ]);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to update device stats");
        }
    }

    public function getAlerts() {
        try {
            $stmt = $this->db->query("
                SELECT a.*, 
                       d.name as device_name,
                       u.name as acknowledged_by_name
                FROM alerts a
                JOIN devices d ON a.device_id = d.id
                LEFT JOIN users u ON a.acknowledged_by = u.id
                WHERE a.resolved_at IS NULL
                ORDER BY a.severity DESC, a.created_at DESC
            ");
            
            $this->jsonResponse(['alerts' => $stmt->fetchAll()]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch alerts");
        }
    }

    public function createAlert() {
        $data = $this->validateRequest(['device_id', 'type', 'message']);
        try {
            $stmt = $this->db->prepare("
                INSERT INTO alerts 
                (device_id, type, message, severity, data)
                VALUES 
                (:device_id, :type, :message, :severity, :data)
            ");
            
            $stmt->execute([
                'device_id' => $data['device_id'],
                'type' => $data['type'],
                'message' => $data['message'],
                'severity' => $data['severity'] ?? 'warning',
                'data' => json_encode($data['data'] ?? [])
            ]);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to create alert");
        }
    }

    public function acknowledgeAlert($alertId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE alerts 
                SET acknowledged_at = CURRENT_TIMESTAMP,
                    acknowledged_by = :user_id 
                WHERE id = :alert_id
            ");
            
            $stmt->execute([
                'alert_id' => $alertId,
                'user_id' => $_SESSION['user']['id']
            ]);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to acknowledge alert");
        }
    }
}
