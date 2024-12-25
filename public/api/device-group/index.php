<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/GroupController.php';

$controller = new Controllers\GroupController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $controller->addDeviceToGroup();
            break;
            
        case 'DELETE':
            $controller->removeDeviceFromGroup();
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Device-Group API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
