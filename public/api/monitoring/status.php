<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/MonitoringController.php';

$controller = new Controllers\MonitoringController();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $controller->errorResponse('Method not allowed', 405);
    }
    
    $controller->getSystemStatus();
} catch (Exception $e) {
    error_log("Monitoring Status API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
