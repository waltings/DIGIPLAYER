<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/AnalyticsController.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$controller = new Controllers\AnalyticsController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            switch($_GET['type']) {
                case 'overview':
                    $controller->getSystemOverview();
                    break;
                    
                case 'device-stats':
                    $controller->getDeviceStats(
                        $_GET['device_id'],
                        $_GET['start_date'] ?? null,
                        $_GET['end_date'] ?? null
                    );
                    break;
                    
                case 'content-stats':
                    $controller->getContentStats(
                        $_GET['content_id'] ?? null,
                        $_GET['period'] ?? '7d'
                    );
                    break;
                    
                case 'playback-logs':
                    $controller->getPlaybackLogs([
                        'device_id' => $_GET['device_id'] ?? null,
                        'playlist_id' => $_GET['playlist_id'] ?? null,
                        'start_date' => $_GET['start_date'] ?? null,
                        'end_date' => $_GET['end_date'] ?? null,
                        'page' => $_GET['page'] ?? 1,
                        'limit' => $_GET['limit'] ?? 20
                    ]);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid analytics type']);
                    break;
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            switch($data['action']) {
                case 'log-playback':
                    $controller->logPlayback($data);
                    break;
                    
                case 'generate-report':
                    $controller->generateReport($data);
                    break;
                    
                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid action']);
                    break;
            }
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
