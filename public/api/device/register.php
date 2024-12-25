<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/DeviceController.php';

$controller = new Controllers\DeviceController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->registerDevice($data);
        break;
    case 'GET':
        $controller->getRegistrationStatus($_GET['device_id']);
        break;
}
