<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';

class PlaylistsController extends \Controllers\BaseController {
    public function getPlaylists() {
        try {
            $stmt = $this->db->query("
                SELECT p.*, 
                       COUNT(pm.id) as items_count,
                       SUM(COALESCE(pm.duration, 0)) as total_duration
                FROM playlists p
                LEFT JOIN playlist_media pm ON p.id = pm.playlist_id
                GROUP BY p.id
                ORDER BY p.name
            ");
            
            $playlists = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Ensure we always return an array even if empty
            $this->jsonResponse(['playlists' => $playlists ?: []]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch playlists: " . $e->getMessage());
        }
    }
}

$controller = new PlaylistsController();
$controller->getPlaylists();
EOFcat > public/api/playlists/index.php << 'EOF'
<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';

class PlaylistsController extends \Controllers\BaseController {
    public function getPlaylists() {
        try {
            $stmt = $this->db->query("
                SELECT p.*, 
                       COUNT(pm.id) as items_count,
                       SUM(COALESCE(pm.duration, 0)) as total_duration
                FROM playlists p
                LEFT JOIN playlist_media pm ON p.id = pm.playlist_id
                GROUP BY p.id
                ORDER BY p.name
            ");
            
            $playlists = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Ensure we always return an array even if empty
            $this->jsonResponse(['playlists' => $playlists ?: []]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch playlists: " . $e->getMessage());
        }
    }
}

$controller = new PlaylistsController();
$controller->getPlaylists();
