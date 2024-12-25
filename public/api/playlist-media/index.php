<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       $stmt = $db->prepare("INSERT INTO playlist_media (playlist_id, media_id, order_number, duration) VALUES (?, ?, ?, ?)");
       $stmt->execute([$data["playlist_id"], $data["media_id"], $data["order_number"], $data["duration"]]);
       echo json_encode(["status" => "success"]);
       break;
       
   case "GET":
       $playlistId = $_GET["playlist_id"] ?? null;
       if ($playlistId) {
           $stmt = $db->prepare("
               SELECT m.*, pm.duration, pm.order_number 
               FROM media m 
               JOIN playlist_media pm ON m.id = pm.media_id 
               WHERE pm.playlist_id = ? 
               ORDER BY pm.order_number
           ");
           $stmt->execute([$playlistId]);
           echo json_encode(["media" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
       }
       break;
}
