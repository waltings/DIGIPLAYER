<?php
namespace Controllers;

class ContentDistributionController extends BaseController {
    public function queueContentDistribution() {
        $data = $this->validateRequest(['device_ids', 'content_type']);
        try {
            $this->db->beginTransaction();
            
            // Get relevant content based on type
            $content = $this->getContentForDistribution($data['content_type'], $data);
            
            // Create distribution tasks for each device
            $stmt = $this->db->prepare("
                INSERT INTO distribution_queue 
                (device_id, content_type, content_id, priority, status)
                VALUES (:device_id, :content_type, :content_id, :priority, 'pending')
            ");
            
            foreach ($data['device_ids'] as $deviceId) {
                foreach ($content as $item) {
                    $stmt->execute([
                        'device_id' => $deviceId,
                        'content_type' => $data['content_type'],
                        'content_id' => $item['id'],
                        'priority' => $data['priority'] ?? 1
                    ]);
                }
            }
            
            $this->db->commit();
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse("Failed to queue content distribution");
        }
    }

    private function getContentForDistribution($type, $data) {
        switch ($type) {
            case 'playlist':
                return $this->getPlaylistContent($data['playlist_id']);
            case 'media':
                return $this->getMediaContent($data['media_ids']);
            case 'update':
                return $this->getSystemUpdate($data['version']);
            default:
                throw new \Exception("Invalid content type");
        }
    }

    private function getPlaylistContent($playlistId) {
        $stmt = $this->db->prepare("
            SELECT m.*, pm.order_number 
            FROM playlist_media pm
            JOIN media m ON pm.media_id = m.id
            WHERE pm.playlist_id = ?
            ORDER BY pm.order_number
        ");
        $stmt->execute([$playlistId]);
        return $stmt->fetchAll();
    }

    public function getDistributionStatus($deviceId) {
        try {
            $stmt = $this->db->prepare("
                SELECT dq.*, 
                       m.name as content_name,
                       m.file_path,
                       m.size
                FROM distribution_queue dq
                LEFT JOIN media m ON dq.content_id = m.id
                WHERE dq.device_id = :device_id
                AND dq.status != 'completed'
                ORDER BY dq.priority DESC, dq.created_at ASC
            ");
            
            $stmt->execute(['device_id' => $deviceId]);
            $this->jsonResponse(['queue' => $stmt->fetchAll()]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to get distribution status");
        }
    }

    public function updateDistributionStatus() {
        $data = $this->validateRequest(['queue_id', 'status']);
        try {
            $stmt = $this->db->prepare("
                UPDATE distribution_queue 
                SET status = :status,
                    progress = :progress,
                    error_message = :error_message,
                    completed_at = CASE 
                        WHEN :status IN ('completed', 'failed') THEN CURRENT_TIMESTAMP 
                        ELSE NULL 
                    END
                WHERE id = :queue_id
            ");
            
            $stmt->execute([
                'queue_id' => $data['queue_id'],
                'status' => $data['status'],
                'progress' => $data['progress'] ?? null,
                'error_message' => $data['error_message'] ?? null
            ]);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to update distribution status");
        }
    }

    public function validateContentDistribution() {
        $data = $this->validateRequest(['device_id', 'content_checksums']);
        try {
            $missingContent = [];
            foreach ($data['content_checksums'] as $contentId => $checksum) {
                $stmt = $this->db->prepare("
                    SELECT checksum FROM media WHERE id = ?
                ");
                $stmt->execute([$contentId]);
                $result = $stmt->fetch();
                
                if (!$result || $result['checksum'] !== $checksum) {
                    $missingContent[] = $contentId;
                }
            }
            
            $this->jsonResponse([
                'status' => 'success',
                'missing_content' => $missingContent
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to validate content");
        }
    }

    public function getContentChunked($contentId, $chunkSize = 1048576) {
        try {
            $stmt = $this->db->prepare("
                SELECT file_path, size FROM media WHERE id = ?
            ");
            $stmt->execute([$contentId]);
            $content = $stmt->fetch();
            
            if (!$content) {
                $this->errorResponse("Content not found", 404);
            }
            
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $content['file_path'];
            if (!file_exists($filePath)) {
                $this->errorResponse("Content file not found", 404);
            }
            
            $start = isset($_GET['start']) ? intval($_GET['start']) : 0;
            $end = min($start + $chunkSize, $content['size']);
            
            $fp = fopen($filePath, 'rb');
            fseek($fp, $start);
            $data = fread($fp, $end - $start);
            fclose($fp);
            
            header('Content-Type: application/octet-stream');
            header('Content-Length: ' . ($end - $start));
            header('Content-Range: bytes ' . $start . '-' . ($end - 1) . '/' . $content['size']);
            echo $data;
            exit;
        } catch (\Exception $e) {
            $this->errorResponse("Failed to get content chunk");
        }
    }
}
