<?php
header('Content-Type: application/json');

try {
    $db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        throw new Exception('Invalid credentials');
    }

    // For testing purposes, accept the default admin credentials
    if ($data['username'] === 'admin@digiplayer.local' && $data['password'] === 'Digiplayer1-401') {
        session_start();
        $_SESSION['user'] = [
            'id' => 1,
            'name' => 'Admin',
            'email' => 'admin@digiplayer.local',
            'role' => 'admin'
        ];
        echo json_encode(['status' => 'success']);
        exit;
    }
    
    echo json_encode(['error' => 'Invalid credentials']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error occurred']);
}
