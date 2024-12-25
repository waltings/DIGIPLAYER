<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/GroupController.php';

$controller = new Controllers\GroupController();

try {
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $controller->getHierarchy();
            break;
        default:
            $controller->errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    error_log("Group Hierarchy API Error: " . $e->getMessage());
    $controller->errorResponse('Server error occurred');
}
