<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       $command = $data["command"];
       $deviceId = $data["device_id"];
       
       $stmt = $db->prepare("INSERT INTO device_commands (device_id, command, params) VALUES (?, ?, ?)");
       $stmt->execute([$deviceId, $command, json_encode($data["params"] ?? [])]);
       
       echo json_encode(["status" => "success", "command_id" => $db->lastInsertId()]);
       break;
       
   case "GET":
       $deviceId = $_GET["device_id"];
       $stmt = $db->prepare("SELECT * FROM device_commands WHERE device_id = ? AND status = pending");
       $stmt->execute([$deviceId]);
       echo json_encode(["commands" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
       break;
}
