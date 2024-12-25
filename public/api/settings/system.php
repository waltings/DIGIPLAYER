<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/SettingsController.php';

$controller = new Controllers\SettingsController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $controller->getSystemSettings();
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->updateSystemSettings($data);
        break;
}
