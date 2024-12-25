<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/ContentController.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$controller = new Controllers\ContentController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['id'])) {
                $controller->getMedia($_GET['id']);
            } else {
                $params = [
                    'type' => $_GET['type'] ?? null,
                    'page' => $_GET['page'] ?? 1,
                    'limit' => $_GET['limit'] ?? 20
                ];
                $controller->getAllMedia($params);
            }
            break;
            
        case 'POST':
            if (!isset($_FILES['file'])) {
                throw new Exception('No file uploaded');
            }
            $controller->uploadMedia($_FILES['file'], $_POST);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->deleteMedia($data['id']);
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
