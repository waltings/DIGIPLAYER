<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/MonitoringController.php';

$controller = new Controllers\MonitoringController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $deviceId = $_GET['device_id'] ?? null;
        if (!$deviceId) {
            $controller->errorResponse('Device ID required', 400);
        }
        $controller->getDeviceMetrics($deviceId);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->updateDeviceMetrics($data);
        break;
}
