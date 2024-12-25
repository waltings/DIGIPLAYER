<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/DeviceController.php';

$controller = new Controllers\DeviceController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (!isset($_GET['device_id'])) {
                $controller->errorResponse('Device ID required', 400);
            }
            $controller->getDeviceSettings($_GET['device_id']);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->updateDeviceSettings($data);
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Device Settings API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
