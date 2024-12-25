<?php
$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");
$tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo "Existing tables:\n";
print_r($tables);
