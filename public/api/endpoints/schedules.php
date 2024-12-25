<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/ScheduleController.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$controller = new Controllers\ScheduleController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['device_id'])) {
                $controller->getDeviceSchedules($_GET['device_id']);
            } elseif(isset($_GET['playlist_id'])) {
                $controller->getPlaylistSchedules($_GET['playlist_id']);
            } else {
                $controller->getAllSchedules();
            }
            break;
            
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
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
