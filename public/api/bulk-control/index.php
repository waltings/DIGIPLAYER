<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       foreach ($data["device_ids"] as $deviceId) {
           $stmt = $db->prepare("INSERT INTO device_commands (device_id, command, params) VALUES (?, ?, ?)");
           $stmt->execute([$deviceId, $data["command"], json_encode($data["params"])]);
       }
       echo json_encode(["status" => "success"]);
       break;
}
