<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/SyncController.php';

$controller = new Controllers\SyncController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (!isset($_GET['device_id'])) {
                $controller->errorResponse('Device ID required', 400);
            }
            $controller->getQueueItems($_GET['device_id']);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (isset($data['bulk'])) {
                $controller->bulkSync();
            } else {
                $controller->addToQueue();
            }
            break;
            
        case 'PUT':
            $controller->updateStatus();
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Sync API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
