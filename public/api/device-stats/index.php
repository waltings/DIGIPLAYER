<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       $stmt = $db->prepare("
           INSERT INTO device_stats (device_id, cpu_usage, memory_usage, temperature)
           VALUES (?, ?, ?, ?)
       ");
       $stmt->execute([
           $data["device_id"],
           $data["cpu_usage"],
           $data["memory_usage"],
           $data["temperature"]
       ]);
       echo json_encode(["status" => "success"]);
       break;
       
   case "GET":
       $deviceId = $_GET["device_id"] ?? null;
       if ($deviceId) {
           $stmt = $db->prepare("
               SELECT * FROM device_stats 
               WHERE device_id = ? 
               ORDER BY created_at DESC 
               LIMIT 100
           ");
           $stmt->execute([$deviceId]);
           echo json_encode(["stats" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
       }
       break;
}
