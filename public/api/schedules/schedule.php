<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/ScheduleController.php';

$controller = new Controllers\ScheduleController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->createSchedule($data);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->updateSchedule($data);
            break;
            
        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            $controller->deleteSchedule($data['id']);
            break;
            
        case 'GET':
            if (isset($_GET['device_id'])) {
                $controller->getDeviceSchedules($_GET['device_id']);
            } else if (isset($_GET['playlist_id'])) {
                $controller->getPlaylistSchedules($_GET['playlist_id']);
            } else {
                $controller->getAllSchedules();
            }
            break;
            
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Schedule API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
