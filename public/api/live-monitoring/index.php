<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");
header("Cache-Control: no-cache");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

$devices = $db->query("
   SELECT d.*, 
          ds.cpu_usage, 
          ds.memory_usage, 
          ds.temperature,
          ds.created_at as last_update
   FROM devices d
   LEFT JOIN device_stats ds ON d.id = ds.device_id
   WHERE ds.id IN (
       SELECT MAX(id)
       FROM device_stats
       GROUP BY device_id
   )
")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["devices" => $devices]);
