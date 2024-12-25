<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");
$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
       $deviceId = $_GET["device_id"] ?? null;
       $period = $_GET["period"] ?? "24h";
       
       $sql = "SELECT l.*, d.name as device_name 
               FROM device_logs l
               JOIN devices d ON l.device_id = d.id
               WHERE l.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
       
       if ($deviceId) {
           $sql .= " AND l.device_id = " . intval($deviceId);
       }
       
       $logs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
       echo json_encode(["logs" => $logs]);
       break;
}
