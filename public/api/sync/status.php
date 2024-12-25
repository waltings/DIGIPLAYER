<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/SyncController.php';

$controller = new Controllers\SyncController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->getSyncStatus($_GET['device_id'] ?? null);
}
