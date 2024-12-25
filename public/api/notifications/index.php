<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");

$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
       $sql = "SELECT 
               n.*, 
               d.name as device_name,
               u.name as acknowledged_by_name
           FROM notifications n
           JOIN devices d ON n.device_id = d.id
           LEFT JOIN users u ON n.acknowledged_by = u.id
           WHERE n.is_acknowledged = 0
           ORDER BY n.severity DESC, n.created_at DESC";
       
       $notifications = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
       echo json_encode(["notifications" => $notifications]);
       break;

   case "PUT":
       $data = json_decode(file_get_contents("php://input"), true);
       if ($data["action"] === "acknowledge") {
           $stmt = $db->prepare("
               UPDATE notifications 
               SET is_acknowledged = TRUE,
                   acknowledged_by = ?,
                   acknowledged_at = NOW()
               WHERE id = ?
           ");
           $stmt->execute([$_SESSION["user"]["id"], $data["notification_id"]]);
       }
       echo json_encode(["status" => "success"]);
       break;
}
