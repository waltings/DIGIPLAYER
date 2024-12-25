<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
       $groupId = $_GET["group_id"];
       $stmt = $db->prepare("
           SELECT 
               d.name as device_name,
               ds.cpu_usage,
               ds.memory_usage,
               ds.temperature,
               ds.created_at
           FROM devices d
           JOIN device_group dg ON d.id = dg.device_id
           JOIN device_stats ds ON d.id = ds.device_id
           WHERE dg.group_id = ?
           ORDER BY ds.created_at DESC
           LIMIT 100
       ");
       $stmt->execute([$groupId]);
       echo json_encode(["stats" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
       break;
}
