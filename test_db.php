<?php
try {
    // Using direct MySQL connection settings from server config
    $db = new PDO("mysql:host=127.0.0.1;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");
    echo "Database connection successful!\n";
    
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "Users in database: " . $count . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
