<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/SyncController.php';

$controller = new Controllers\SyncController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $deviceId = $_GET['device_id'] ?? null;
    if (!$deviceId) {
        $controller->errorResponse('Device ID required', 400);
    }
    $controller->getDeviceSyncStatus($deviceId);
}
