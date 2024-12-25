<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
       $deviceId = $_GET["device_id"] ?? null;
       if ($deviceId) {
           $stmt = $db->prepare("
               SELECT mq.*, m.name, m.type 
               FROM media_queue mq
               JOIN media m ON mq.media_id = m.id
               WHERE mq.device_id = ?
               ORDER BY mq.priority DESC, mq.order_number
           ");
           $stmt->execute([$deviceId]);
           echo json_encode(["queue" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
       }
       break;

   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       $stmt = $db->prepare("
           INSERT INTO media_queue 
           (device_id, media_id, priority, order_number, start_time, end_time)
           VALUES (?, ?, ?, ?, ?, ?)
       ");
       $stmt->execute([
           $data["device_id"],
           $data["media_id"],
           $data["priority"],
           $data["order_number"],
           $data["start_time"],
           $data["end_time"]
       ]);
       echo json_encode(["status" => "success"]);
       break;
}
