<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");
$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
       $locations = $db->query("
           SELECT dl.*, d.name as device_name, d.status
           FROM device_locations dl
           JOIN devices d ON dl.device_id = d.id
       ")->fetchAll(PDO::FETCH_ASSOC);
       echo json_encode(["locations" => $locations]);
       break;

   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       $stmt = $db->prepare("
           INSERT INTO device_locations (device_id, location_name, floor, coordinates)
           VALUES (?, ?, ?, ?)
       ");
       $stmt->execute([
           $data["device_id"],
           $data["location_name"],
           $data["floor"],
           json_encode($data["coordinates"])
       ]);
       echo json_encode(["status" => "success"]);
       break;
}
