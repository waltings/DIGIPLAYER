<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/ScheduleController.php';

$controller = new Controllers\ScheduleController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['device_id']) && isset($_GET['active'])) {
                $controller->getActiveSchedule($_GET['device_id']);
            } else if(isset($_GET['device_id'])) {
                $controller->getSchedules($_GET['device_id']);
            } else {
                $controller->getSchedules();
            }
            break;
            
        case 'POST':
            $controller->createSchedule();
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id'])) {
                $controller->errorResponse('Schedule ID required', 400);
            }
            $controller->updateSchedule($data['id']);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['id'])) {
                $controller->errorResponse('Schedule ID required', 400);
            }
            $controller->deleteSchedule($data['id']);
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Schedule API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
