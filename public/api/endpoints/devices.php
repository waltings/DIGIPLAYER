<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/DeviceController.php';

header('Content-Type: application/json');
session_start();

// Check authentication
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$controller = new Controllers\DeviceController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['id'])) {
                $controller->getDevice($_GET['id']);
            } else {
                $controller->getDevices();
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->createDevice($data);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->updateDevice($data);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->deleteDevice($data['id']);
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
