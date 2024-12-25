<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");
$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       $groups = $data["groups"] ?? [];
       $devices = $data["devices"] ?? [];
       $content = $data["content"];

       // Grupipõhine uuendamine
       if (!empty($groups)) {
           $stmt = $db->prepare("
               INSERT INTO sync_queue (device_id, action, data)
               SELECT d.id, update_content, ?
               FROM devices d
               JOIN device_group dg ON d.id = dg.device_id
               WHERE dg.group_id IN (" . implode(",", $groups) . ")
           ");
           $stmt->execute([json_encode($content)]);
       }

       // Seadmepõhine uuendamine
       if (!empty($devices)) {
           $stmt = $db->prepare("
               INSERT INTO sync_queue (device_id, action, data)
               VALUES (?, update_content, ?)
           ");
           foreach ($devices as $deviceId) {
               $stmt->execute([$deviceId, json_encode($content)]);
           }
       }
       
       echo json_encode(["status" => "success"]);
       break;
}
