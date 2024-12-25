<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       $action = $data["action"];
       
       switch($action) {
           case "create_group":
               $stmt = $db->prepare("INSERT INTO groups (name, description) VALUES (?, ?)");
               $stmt->execute([$data["name"], $data["description"]]);
               echo json_encode(["id" => $db->lastInsertId()]);
               break;
               
           case "add_devices":
               $stmt = $db->prepare("INSERT INTO device_group (device_id, group_id) VALUES (?, ?)");
               foreach ($data["device_ids"] as $deviceId) {
                   $stmt->execute([$deviceId, $data["group_id"]]);
               }
               echo json_encode(["status" => "success"]);
               break;
       }
       break;
}
