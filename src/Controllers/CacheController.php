<?php
namespace Controllers;

class CacheController extends BaseController {
    private $cacheDir = 'cache/';
    private $maxCacheSize = 10737418240; // 10GB in bytes
    
    public function __construct() {
        parent::__construct();
        $this->ensureCacheDirectory();
    }

    private function ensureCacheDirectory() {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function getCacheStatus($deviceId = null) {
        try {
            if ($deviceId) {
                $stmt = $this->db->prepare("
                    SELECT 
                        c.*,
                        m.name as content_name,
                        m.type as content_type
                    FROM cache_entries c
                    JOIN media m ON c.content_id = m.id
                    WHERE c.device_id = :device_id
                    ORDER BY c.last_accessed DESC
                ");
                $stmt->execute(['device_id' => $deviceId]);
                $cacheEntries = $stmt->fetchAll();

                $totalSize = array_sum(array_column($cacheEntries, 'size'));
                $cacheUsage = [
                    'total_entries' => count($cacheEntries),
                    'total_size' => $totalSize,
                    'entries' => $cacheEntries
                ];
            } else {
                // System-wide cache status
                $stmt = $this->db->query("
                    SELECT 
                        COUNT(*) as total_entries,
                        SUM(size) as total_size,
                        COUNT(DISTINCT device_id) as devices_count
                    FROM cache_entries
                ");
                $cacheUsage = $stmt->fetch();
            }

            $this->jsonResponse(['cache_status' => $cacheUsage]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to get cache status");
        }
    }

    public function validateCache() {
        $data = $this->validateRequest(['device_id', 'cache_entries']);
        try {
            $invalidEntries = [];
            foreach ($data['cache_entries'] as $entry) {
                $stmt = $this->db->prepare("
                    SELECT checksum 
                    FROM cache_entries 
                    WHERE device_id = :device_id 
                    AND content_id = :content_id
                ");
                
                $stmt->execute([
                    'device_id' => $data['device_id'],
                    'content_id' => $entry['content_id']
                ]);
                
                $result = $stmt->fetch();
                if (!$result || $result['checksum'] !== $entry['checksum']) {
                    $invalidEntries[] = $entry['content_id'];
                }
            }

            $this->jsonResponse([
                'status' => 'success',
                'invalid_entries' => $invalidEntries
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to validate cache");
        }
    }

    public function updateCache() {
        $data = $this->validateRequest(['device_id', 'content_id']);
        try {
            $stmt = $this->db->prepare("
                REPLACE INTO cache_entries 
                (device_id, content_id, checksum, size, last_accessed)
                VALUES (:device_id, :content_id, :checksum, :size, CURRENT_TIMESTAMP)
            ");
            
            $stmt->execute([
                'device_id' => $data['device_id'],
                'content_id' => $data['content_id'],
                'checksum' => $data['checksum'],
                'size' => $data['size']
            ]);
            
            // Check if cache cleanup is needed
            $this->cleanupCacheIfNeeded($data['device_id']);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to update cache");
        }
    }

    public function clearCache($deviceId = null) {
        try {
            $this->db->beginTransaction();
            
            if ($deviceId) {
                $stmt = $this->db->prepare("
                    DELETE FROM cache_entries 
                    WHERE device_id = :device_id
                ");
                $stmt->execute(['device_id' => $deviceId]);
            } else {
                $this->db->exec("DELETE FROM cache_entries");
            }
            
            $this->db->commit();
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse("Failed to clear cache");
        }
    }

    private function cleanupCacheIfNeeded($deviceId) {
        $stmt = $this->db->prepare("
            SELECT SUM(size) as total_size 
            FROM cache_entries 
            WHERE device_id = :device_id
        ");
        $stmt->execute(['device_id' => $deviceId]);
        $result = $stmt->fetch();

        if ($result['total_size'] > $this->maxCacheSize) {
            // Remove oldest entries until under limit
            $stmt = $this->db->prepare("
                DELETE FROM cache_entries 
                WHERE device_id = :device_id
                AND id IN (
                    SELECT id 
                    FROM cache_entries 
                    WHERE device_id = :device_id
                    ORDER BY last_accessed ASC 
                    LIMIT 10
                )
            ");
            $stmt->execute(['device_id' => $deviceId]);
        }
    }

    public function preloadCache() {
        $data = $this->validateRequest(['device_id', 'playlist_id']);
        try {
            // Get media files from playlist
            $stmt = $this->db->prepare("
                SELECT m.* 
                FROM playlist_media pm
                JOIN media m ON pm.media_id = m.id
                WHERE pm.playlist_id = :playlist_id
                ORDER BY pm.order_number
            ");
            
            $stmt->execute(['playlist_id' => $data['playlist_id']]);
            $mediaFiles = $stmt->fetchAll();

            // Queue files for caching
            $stmt = $this->db->prepare("
                INSERT INTO cache_queue 
                (device_id, content_id, priority)
                VALUES (:device_id, :content_id, :priority)
                ON DUPLICATE KEY UPDATE priority = :priority
            ");

            foreach ($mediaFiles as $index => $media) {
                $stmt->execute([
                    'device_id' => $data['device_id'],
                    'content_id' => $media['id'],
                    'priority' => count($mediaFiles) - $index // Higher priority for earlier items
                ]);
            }

            $this->jsonResponse([
                'status' => 'success',
                'queued_files' => count($mediaFiles)
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to preload cache");
        }
    }
}
