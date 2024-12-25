<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
       $period = $_GET["period"] ?? "day";
       $deviceId = $_GET["device_id"] ?? null;
       
       $sql = "SELECT 
                   d.name as device_name,
                   p.name as playlist_name,
                   m.name as media_name,
                   SUM(da.play_duration) as total_duration,
                   SUM(da.play_count) as total_plays,
                   SUM(da.error_count) as total_errors,
                   DATE(da.recorded_at) as date
               FROM device_analytics da
               JOIN devices d ON da.device_id = d.id
               JOIN playlists p ON da.playlist_id = p.id
               JOIN media m ON da.media_id = m.id";
       
       if ($deviceId) {
           $sql .= " WHERE da.device_id = " . intval($deviceId);
       }
       
       $sql .= " GROUP BY DATE(da.recorded_at), da.device_id";
       
       $analytics = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
       echo json_encode(["analytics" => $analytics]);
       break;
}
