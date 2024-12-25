<?php
try {
    $db = new PDO("mysql:unix_socket=/var/lib/mysql/mysql.sock;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");
    echo "Database connection successful!\n";
    
    $stmt = $db->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "Users in database: " . $count . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
