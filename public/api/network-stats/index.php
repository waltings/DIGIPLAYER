<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");
$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
       $deviceId = $_GET["device_id"];
       $stmt = $db->prepare("
           SELECT * FROM network_stats 
           WHERE device_id = ? 
           ORDER BY recorded_at DESC 
           LIMIT 100
       ");
       $stmt->execute([$deviceId]);
       echo json_encode(["stats" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
       break;
}
