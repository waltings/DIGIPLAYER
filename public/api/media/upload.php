<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';
require_once __DIR__ . '/../../../src/Controllers/MediaController.php';

$controller = new Controllers\MediaController();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $controller->errorResponse('Method not allowed', 405);
    }

    if (!isset($_FILES['file'])) {
        $controller->errorResponse('No file uploaded', 400);
    }

    $controller->uploadMedia($_FILES['file'], $_POST);
} catch (Exception $e) {
    error_log("Media Upload Error: " . $e->getMessage());
    $controller->errorResponse('Upload failed: ' . $e->getMessage());
}
