<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/GroupController.php';

$controller = new Controllers\GroupController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            switch($data['action']) {
                case 'move_devices':
                    $controller->moveDevicesToGroup($data);
                    break;
                case 'copy_devices':
                    $controller->copyDevicesToGroup($data);
                    break;
                case 'remove_devices':
                    $controller->removeDevicesFromGroup($data);
                    break;
                default:
                    $controller->errorResponse('Invalid action', 400);
            }
            break;
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Group Bulk Actions API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
