<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/AnalyticsController.php';

$controller = new Controllers\AnalyticsController();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $controller->errorResponse('Method not allowed', 405);
    }

    $period = $_GET['period'] ?? 'day';
    
    if (isset($_GET['device_id'])) {
        $controller->getDeviceAnalytics($_GET['device_id'], $period);
    } else if (isset($_GET['playlist_id'])) {
        $controller->getPlaylistAnalytics($_GET['playlist_id'], $period);
    } else {
        $controller->getSystemAnalytics($period);
    }
} catch (Exception $e) {
    error_log("Analytics Reports API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
