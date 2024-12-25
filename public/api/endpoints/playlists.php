<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/PlaylistController.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$controller = new Controllers\PlaylistController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['id']) && isset($_GET['items'])) {
                $controller->getPlaylistItems($_GET['id']);
            } elseif(isset($_GET['id'])) {
                $controller->getPlaylist($_GET['id']);
            } else {
                $controller->getAllPlaylists();
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['duplicate'])) {
                $controller->duplicatePlaylist($data['duplicate']);
            } else {
                $controller->createPlaylist($data);
            }
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['items'])) {
                $controller->updatePlaylistItems($data['id'], $data['items']);
            } else {
                $controller->updatePlaylist($data);
            }
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->deletePlaylist($data['id']);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
