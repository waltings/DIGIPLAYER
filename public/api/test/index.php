<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");
$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

// Test andmebaasi ühendust
try {
   $db->query("SELECT 1");
   $dbStatus = "OK";
} catch (Exception $e) {
   $dbStatus = "Error: " . $e->getMessage();
}

// Test failide õigusi
$uploadDir = "../uploads/media";
$writeTest = is_writable($uploadDir);

echo json_encode([
   "database" => $dbStatus,
   "upload_permissions" => $writeTest,
   "php_version" => PHP_VERSION,
   "memory_limit" => ini_get("memory_limit")
]);
