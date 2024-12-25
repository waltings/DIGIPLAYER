<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/AnalyticsController.php';

$controller = new Controllers\AnalyticsController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $controller->logPlayback();
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Playback Analytics API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
