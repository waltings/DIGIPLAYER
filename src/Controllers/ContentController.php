<?php
namespace Controllers;

class ContentController extends BaseController {
    private $uploadDir = '/home/vhost15998ssh/htdocs/digiplayer/public/uploads/media/';
    private $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm'
    ];
    private $maxFileSize = 104857600; // 100MB

    public function getAllMedia($params = []) {
        try {
            $this->checkPermission('read', 'media');

            $pagination = $this->getPaginationParams();
            $whereConditions = [];
            $queryParams = [];

            if (!empty($params['type'])) {
                $whereConditions[] = "type = :type";
                $queryParams[':type'] = $params['type'];
            }

            $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

            // Get total count
            $countSql = "SELECT COUNT(*) as total FROM media $whereClause";
            $stmt = $this->db->prepare($countSql);
            $stmt->execute($queryParams);
            $total = $stmt->fetch()['total'];

            // Get media files
            $sql = "
                SELECT 
                    m.*,
                    u.name as uploaded_by_name
                FROM media m
                LEFT JOIN users u ON m.uploaded_by = u.id
                $whereClause
                ORDER BY m.created_at DESC
                LIMIT :offset, :limit
            ";

            $queryParams[':offset'] = $pagination['offset'];
            $queryParams[':limit'] = $pagination['limit'];

            $stmt = $this->db->prepare($sql);
            $stmt->execute($queryParams);
            $media = $stmt->fetchAll();

            $this->response([
                'media' => $media,
                'pagination' => [
                    'page' => $pagination['page'],
                    'limit' => $pagination['limit'],
                    'total' => $total,
                    'pages' => ceil($total / $pagination['limit'])
                ]
            ]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function uploadMedia($file, $data) {
        try {
            $this->checkPermission('create', 'media');

            if (!isset($this->allowedTypes[$file['type']])) {
                throw new \Exception("Invalid file type", 400);
            }

            if ($file['size'] > $this->maxFileSize) {
                throw new \Exception("File too large", 400);
            }

            // Generate unique filename
            $extension = $this->allowedTypes[$file['type']];
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $targetPath = $this->uploadDir . $filename;

            // Create upload directory if it doesn't exist
            if (!file_exists($this->uploadDir)) {
                mkdir($this->uploadDir, 0755, true);
            }

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new \Exception("Failed to save file", 500);
            }

            // Get file info
            $fileInfo = $this->getFileInfo($targetPath, $file['type']);

            $this->db->beginTransaction();

            // Insert media record
            $stmt = $this->db->prepare("
                INSERT INTO media (
                    name, type, file_path, size, duration, resolution,
                    uploaded_by, checksum
                ) VALUES (
                    :name, :type, :file_path, :size, :duration, :resolution,
                    :uploaded_by, :checksum
                )
            ");

            $stmt->execute([
                'name' => $data['name'] ?? pathinfo($file['name'], PATHINFO_FILENAME),
                'type' => strpos($file['type'], 'video') !== false ? 'video' : 'image',
                'file_path' => '/uploads/media/' . $filename,
                'size' => $file['size'],
                'duration' => $fileInfo['duration'] ?? null,
                'resolution' => $fileInfo['resolution'] ?? null,
                'uploaded_by' => $this->user['id'],
                'checksum' => hash_file('sha256', $targetPath)
            ]);

            $mediaId = $this->db->lastInsertId();

            // Store metadata if any
            if (!empty($fileInfo['metadata'])) {
                $stmt = $this->db->prepare("
                    INSERT INTO media_metadata (media_id, metadata)
                    VALUES (?, ?)
                ");
                $stmt->execute([$mediaId, json_encode($fileInfo['metadata'])]);
            }

            $this->db->commit();

            // Log activity
            $this->logActivity('upload', 'media', $mediaId);

            $this->response([
                'status' => 'success',
                'id' => $mediaId,
                'message' => 'File uploaded successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            // Clean up file if uploaded
            if (isset($targetPath) && file_exists($targetPath)) {
                unlink($targetPath);
            }
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    private function getFileInfo($path, $mimeType) {
        $info = [
            'resolution' => null,
            'duration' => null,
            'metadata' => []
        ];

        if (strpos($mimeType, 'image') !== false) {
            $imageInfo = getimagesize($path);
            if ($imageInfo) {
                $info['resolution'] = $imageInfo[0] . 'x' . $imageInfo[1];
                $info['metadata'] = [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'bits' => $imageInfo['bits'] ?? null,
                    'channels' => $imageInfo['channels'] ?? null
                ];
            }
        } elseif (strpos($mimeType, 'video') !== false && extension_loaded('ffmpeg')) {
            $ffmpeg = \FFMpeg\FFMpeg::create();
            $video = $ffmpeg->open($path);
            
            // Get video duration
            $duration = $video->getDuration();
            $info['duration'] = $duration;

            // Get video dimensions
            $dimensions = $video->getStreams()->videos()->first()->getDimensions();
            $info['resolution'] = $dimensions->getWidth() . 'x' . $dimensions->getHeight();
            
            $info['metadata'] = [
                'width' => $dimensions->getWidth(),
                'height' => $dimensions->getHeight(),
                'duration' => $duration,
                'format' => $video->getFormat()->all()
            ];
        }

        return $info;
    }

    public function deleteMedia($id) {
        try {
            $this->checkPermission('delete', 'media');

            // Get media info
            $stmt = $this->db->prepare("SELECT file_path FROM media WHERE id = ?");
            $stmt->execute([$id]);
            $media = $stmt->fetch();

            if (!$media) {
                throw new \Exception("Media not found", 404);
            }

            $this->db->beginTransaction();

            // Delete database record
            $stmt = $this->db->prepare("DELETE FROM media WHERE id = ?");
            $stmt->execute([$id]);

            // Delete file
            $filePath = $_SERVER['DOCUMENT_ROOT'] . $media['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $this->db->commit();

            // Log activity
            $this->logActivity('delete', 'media', $id);

            $this->response([
                'status' => 'success',
                'message' => 'Media deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
