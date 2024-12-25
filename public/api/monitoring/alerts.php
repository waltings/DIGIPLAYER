<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/MonitoringController.php';

$controller = new Controllers\MonitoringController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $controller->getAlerts();
            break;
            
        case 'POST':
            $controller->createAlert();
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['alert_id'])) {
                $controller->errorResponse('Alert ID required', 400);
            }
            $controller->acknowledgeAlert($data['alert_id']);
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Alerts API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
