<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/MonitoringController.php';

$controller = new Controllers\MonitoringController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (!isset($_GET['device_id'])) {
                $controller->errorResponse('Device ID required', 400);
            }
            $controller->getDeviceStats($_GET['device_id']);
            break;
            
        case 'POST':
            $controller->updateDeviceStats();
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Device Stats API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
