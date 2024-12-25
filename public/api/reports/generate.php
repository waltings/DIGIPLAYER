<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/ReportingController.php';

$controller = new Controllers\ReportingController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->generateReport($data);
        break;
    case 'GET':
        $controller->getReportsList();
        break;
}
