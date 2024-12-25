<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/GroupController.php';

$controller = new Controllers\GroupController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            switch($data['operation']) {
                case 'move_device':
                    $controller->moveDevice($data);
                    break;
                case 'schedule':
                    $controller->scheduleGroupContent($data);
                    break;
                case 'status_update':
                    $controller->updateGroupStatus($data);
                    break;
                default:
                    $controller->errorResponse('Invalid operation', 400);
            }
            break;
            
        case 'GET':
            if (isset($_GET['status'])) {
                $controller->getGroupStatus();
            } else if (isset($_GET['settings'])) {
                $controller->getGroupSettings();
            } else {
                $controller->errorResponse('Invalid request', 400);
            }
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Group Operations API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
