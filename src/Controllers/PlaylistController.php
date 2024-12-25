<?php
namespace Controllers;

class PlaylistController extends BaseController {
    public function getAllPlaylists() {
        try {
            $this->checkPermission('read', 'playlists');
            
            $pagination = $this->getPaginationParams();

            // Get total count
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM playlists");
            $stmt->execute();
            $total = $stmt->fetch()['total'];

            // Get playlists with additional info
            $sql = "
                SELECT 
                    p.*,
                    u.name as created_by_name,
                    COUNT(DISTINCT pm.media_id) as items_count,
                    SUM(pm.duration) as total_duration
                FROM playlists p
                LEFT JOIN users u ON p.created_by = u.id
                LEFT JOIN playlist_media pm ON p.id = pm.playlist_id
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT :offset, :limit
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':offset' => $pagination['offset'],
                ':limit' => $pagination['limit']
            ]);
            
            $playlists = $stmt->fetchAll();

            $this->response([
                'playlists' => $playlists,
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

    public function getPlaylistItems($playlistId) {
        try {
            $this->checkPermission('read', 'playlists');

            $sql = "
                SELECT 
                    m.*,
                    pm.order_number,
                    pm.duration,
                    pm.transition_type
                FROM playlist_media pm
                JOIN media m ON pm.media_id = m.id
                WHERE pm.playlist_id = ?
                ORDER BY pm.order_number
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$playlistId]);
            $items = $stmt->fetchAll();

            $this->response(['items' => $items]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function createPlaylist($data) {
        try {
            $this->checkPermission('create', 'playlists');
            
            $data = $this->validateRequest(['name']);
            $data = $this->sanitizeInput($data);

            $this->db->beginTransaction();

            // Insert playlist
            $stmt = $this->db->prepare("
                INSERT INTO playlists (
                    name, description, status, schedule_type, created_by
                ) VALUES (
                    :name, :description, :status, :schedule_type, :created_by
                )
            ");

            $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'schedule_type' => $data['schedule_type'] ?? 'always',
                'created_by' => $this->user['id']
            ]);

            $playlistId = $this->db->lastInsertId();

            // Add media items if provided
            if (!empty($data['media_items'])) {
                $this->addMediaToPlaylist($playlistId, $data['media_items']);
            }

            $this->db->commit();

            // Log activity
            $this->logActivity('create', 'playlist', $playlistId, $data);

            $this->response([
                'status' => 'success',
                'id' => $playlistId,
                'message' => 'Playlist created successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function updatePlaylist($data) {
        try {
            $this->checkPermission('update', 'playlists');
            
            $data = $this->validateRequest(['id', 'name']);
            $data = $this->sanitizeInput($data);

            $this->db->beginTransaction();

            // Update playlist
            $stmt = $this->db->prepare("
                UPDATE playlists SET
                    name = :name,
                    description = :description,
                    status = :status,
                    schedule_type = :schedule_type
                WHERE id = :id
            ");

            $stmt->execute([
                'id' => $data['id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'active',
                'schedule_type' => $data['schedule_type'] ?? 'always'
            ]);

            // Update media items if provided
            if (isset($data['media_items'])) {
                // Clear existing items
                $stmt = $this->db->prepare("DELETE FROM playlist_media WHERE playlist_id = ?");
                $stmt->execute([$data['id']]);

                // Add new items
                if (!empty($data['media_items'])) {
                    $this->addMediaToPlaylist($data['id'], $data['media_items']);
                }
            }

            $this->db->commit();

            // Log activity
            $this->logActivity('update', 'playlist', $data['id'], $data);

            $this->response([
                'status' => 'success',
                'message' => 'Playlist updated successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    private function addMediaToPlaylist($playlistId, $mediaItems) {
        $stmt = $this->db->prepare("
            INSERT INTO playlist_media (
                playlist_id, media_id, order_number, duration, transition_type
            ) VALUES (
                ?, ?, ?, ?, ?
            )
        ");

        foreach ($mediaItems as $index => $item) {
            $stmt->execute([
                $playlistId,
                $item['media_id'],
                $index + 1,
                $item['duration'] ?? null,
                $item['transition_type'] ?? 'none'
            ]);
        }
    }

    public function duplicatePlaylist($playlistId) {
        try {
            $this->checkPermission('create', 'playlists');

            $this->db->beginTransaction();

            // Get original playlist
            $stmt = $this->db->prepare("SELECT * FROM playlists WHERE id = ?");
            $stmt->execute([$playlistId]);
            $originalPlaylist = $stmt->fetch();

            if (!$originalPlaylist) {
                throw new \Exception("Playlist not found", 404);
            }

            // Create new playlist
            $stmt = $this->db->prepare("
                INSERT INTO playlists (
                    name, description, status, schedule_type, created_by
                ) VALUES (
                    :name, :description, :status, :schedule_type, :created_by
                )
            ");

            $stmt->execute([
                'name' => $originalPlaylist['name'] . ' (Copy)',
                'description' => $originalPlaylist['description'],
                'status' => 'inactive',
                'schedule_type' => $originalPlaylist['schedule_type'],
                'created_by' => $this->user['id']
            ]);

            $newPlaylistId = $this->db->lastInsertId();

            // Copy media items
            $stmt = $this->db->prepare("
                INSERT INTO playlist_media (
                    playlist_id, media_id, order_number, duration, transition_type
                )
                SELECT 
                    ?, media_id, order_number, duration, transition_type
                FROM playlist_media
                WHERE playlist_id = ?
                ORDER BY order_number
            ");

            $stmt->execute([$newPlaylistId, $playlistId]);

            $this->db->commit();

            // Log activity
            $this->logActivity('duplicate', 'playlist', $newPlaylistId, [
                'original_id' => $playlistId
            ]);

            $this->response([
                'status' => 'success',
                'id' => $newPlaylistId,
                'message' => 'Playlist duplicated successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function deletePlaylist($id) {
        try {
            $this->checkPermission('delete', 'playlists');

            $this->db->beginTransaction();

            // Delete playlist
            $stmt = $this->db->prepare("DELETE FROM playlists WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            // Log activity
            $this->logActivity('delete', 'playlist', $id);

            $this->response([
                'status' => 'success',
                'message' => 'Playlist deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
