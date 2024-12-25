<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/MonitoringController.php';

$controller = new Controllers\MonitoringController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $controller->getSystemStatus();
        break;
}
