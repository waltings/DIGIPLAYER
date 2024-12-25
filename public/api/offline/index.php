<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
       $deviceId = $_GET["device_id"];
       $stmt = $db->prepare("
           SELECT type, data, last_sync 
           FROM offline_cache 
           WHERE device_id = ?
       ");
       $stmt->execute([$deviceId]);
       echo json_encode(["cache" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
       break;

   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       $stmt = $db->prepare("
           INSERT INTO offline_cache (device_id, type, data, last_sync)
           VALUES (?, ?, ?, NOW())
           ON DUPLICATE KEY UPDATE 
               data = VALUES(data),
               last_sync = NOW()
       ");
       $stmt->execute([
           $data["device_id"],
           $data["type"],
           json_encode($data["data"])
       ]);
       echo json_encode(["status" => "success"]);
       break;
}
