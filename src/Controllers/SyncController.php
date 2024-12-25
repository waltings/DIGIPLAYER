<?php
namespace Controllers;

class SyncController extends BaseController {
    public function getQueueItems($deviceId) {
        try {
            $stmt = $this->db->prepare("
                SELECT sq.*, m.file_path, m.type
                FROM sync_queue sq
                LEFT JOIN media m ON sq.media_id = m.id
                WHERE sq.device_id = :device_id 
                AND sq.status = 'pending'
                ORDER BY sq.priority DESC, sq.created_at ASC
            ");
            
            $stmt->execute(['device_id' => $deviceId]);
            $this->jsonResponse(['queue' => $stmt->fetchAll()]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch sync queue");
        }
    }

    public function addToQueue() {
        $data = $this->validateRequest(['device_id', 'action']);
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sync_queue 
                (device_id, action, data, media_id, priority) 
                VALUES (:device_id, :action, :data, :media_id, :priority)
            ");
            
            $stmt->execute([
                'device_id' => $data['device_id'],
                'action' => $data['action'],
                'data' => json_encode($data['data'] ?? []),
                'media_id' => $data['media_id'] ?? null,
                'priority' => $data['priority'] ?? 1
            ]);
            
            $this->jsonResponse([
                'status' => 'success',
                'id' => $this->db->lastInsertId()
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to add to sync queue");
        }
    }

    public function updateStatus() {
        $data = $this->validateRequest(['id', 'status']);
        try {
            $stmt = $this->db->prepare("
                UPDATE sync_queue 
                SET status = :status, 
                    completed_at = CASE 
                        WHEN :status IN ('completed', 'failed') THEN CURRENT_TIMESTAMP 
                        ELSE NULL 
                    END,
                    error_message = :error_message
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $data['id'],
                'status' => $data['status'],
                'error_message' => $data['error_message'] ?? null
            ]);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to update sync status");
        }
    }

    public function deviceHeartbeat($deviceId) {
        try {
            $stmt = $this->db->prepare("
                UPDATE devices 
                SET last_heartbeat = CURRENT_TIMESTAMP,
                    status = CASE 
                        WHEN status = 'offline' THEN 'online'
                        ELSE status 
                    END
                WHERE id = :device_id
            ");
            
            $stmt->execute(['device_id' => $deviceId]);
            
            // Get pending sync items count
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as pending_count 
                FROM sync_queue 
                WHERE device_id = :device_id AND status = 'pending'
            ");
            
            $stmt->execute(['device_id' => $deviceId]);
            $result = $stmt->fetch();
            
            $this->jsonResponse([
                'status' => 'success',
                'pending_syncs' => $result['pending_count']
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to update device heartbeat");
        }
    }

    public function bulkSync() {
        $data = $this->validateRequest(['device_ids', 'action']);
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare("
                INSERT INTO sync_queue 
                (device_id, action, data, priority) 
                VALUES (:device_id, :action, :data, :priority)
            ");
            
            foreach ($data['device_ids'] as $deviceId) {
                $stmt->execute([
                    'device_id' => $deviceId,
                    'action' => $data['action'],
                    'data' => json_encode($data['data'] ?? []),
                    'priority' => $data['priority'] ?? 1
                ]);
            }
            
            $this->db->commit();
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse("Failed to create bulk sync");
        }
    }
}
