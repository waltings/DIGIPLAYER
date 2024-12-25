<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/ReportingController.php';

$controller = new Controllers\ReportingController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $controller->scheduleReport();
            break;
            
        case 'GET':
            $controller->getScheduledReports();
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Report Scheduling API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
