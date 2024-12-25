<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/MediaController.php';

$controller = new Controllers\MediaController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['id'])) {
                $controller->getMediaById($_GET['id']);
            } else {
                $controller->getMedia();
            }
            break;
            
        case 'POST':
            $controller->uploadMedia();
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id'])) {
                $controller->errorResponse('Media ID required', 400);
            }
            $controller->deleteMedia($data['id']);
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Media API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
