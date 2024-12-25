<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/PlaylistController.php';

$controller = new Controllers\PlaylistController();

try {
    $playlistId = $_GET['playlist_id'] ?? null;
    if (!$playlistId) {
        $controller->errorResponse('Playlist ID required', 400);
    }

    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $controller->getPlaylistMedia($playlistId);
            break;
            
        case 'POST':
            $controller->addMedia($playlistId);
            break;
            
        case 'PUT':
            $controller->updateMediaOrder($playlistId);
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Playlist Media API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
