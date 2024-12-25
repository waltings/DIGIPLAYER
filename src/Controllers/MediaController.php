<?php
namespace Controllers;

class MediaController extends BaseController {
    private $uploadDir = 'public/uploads/media/';
    
    public function getMedia() {
        try {
            $stmt = $this->db->query("SELECT * FROM media ORDER BY uploaded_date DESC");
            $this->jsonResponse(['media' => $stmt->fetchAll()]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch media");
        }
    }

    public function uploadMedia() {
        if (!isset($_FILES['file'])) {
            $this->errorResponse("No file uploaded", 400);
        }

        $file = $_FILES['file'];
        if (!$this->validateFile($file)) {
            $this->errorResponse("Invalid file type", 400);
        }

        try {
            $fileName = time() . '_' . basename($file['name']);
            $targetPath = $this->uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new \Exception("Failed to move uploaded file");
            }

            $fileInfo = $this->getFileInfo($targetPath, $file['type']);
            $stmt = $this->db->prepare(
                "INSERT INTO media (name, type, file_path, resolution, size, duration) 
                VALUES (:name, :type, :file_path, :resolution, :size, :duration)"
            );
            
            $stmt->execute([
                'name' => $_POST['name'] ?? pathinfo($file['name'], PATHINFO_FILENAME),
                'type' => $fileInfo['type'],
                'file_path' => '/digiplayer/public/uploads/media/' . $fileName,
                'resolution' => $fileInfo['resolution'],
                'size' => $file['size'],
                'duration' => $fileInfo['duration'] ?? null
            ]);

            $this->jsonResponse([
                'status' => 'success',
                'id' => $this->db->lastInsertId()
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Upload failed: " . $e->getMessage());
        }
    }

    private function validateFile($file) {
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif',
            'video/mp4', 'video/webm'
        ];
        return in_array($file['type'], $allowedTypes);
    }

    private function getFileInfo($path, $mimeType) {
        $info = [
            'type' => strpos($mimeType, 'video') !== false ? 'video' : 'image',
            'resolution' => null,
            'duration' => null
        ];

        if ($info['type'] === 'image') {
            $imageInfo = getimagesize($path);
            if ($imageInfo) {
                $info['resolution'] = $imageInfo[0] . 'x' . $imageInfo[1];
            }
        }

        return $info;
    }

    public function deleteMedia($id) {
        try {
            $stmt = $this->db->prepare("SELECT file_path FROM media WHERE id = ?");
            $stmt->execute([$id]);
            $media = $stmt->fetch();
            
            if ($media && file_exists($_SERVER['DOCUMENT_ROOT'] . $media['file_path'])) {
                unlink($_SERVER['DOCUMENT_ROOT'] . $media['file_path']);
            }

            $stmt = $this->db->prepare("DELETE FROM media WHERE id = ?");
            $stmt->execute([$id]);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to delete media");
        }
    }
}
