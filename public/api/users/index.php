<?php
require "../../vendor/autoload.php";
header("Content-Type: application/json");
$db = new PDO("mysql:host=localhost;dbname=vhost15998s0", "vhost15998s0", "Digiplayer1-401");

switch ($_SERVER["REQUEST_METHOD"]) {
   case "GET":
       $users = $db->query("
           SELECT u.*, r.name as role_name 
           FROM users u 
           JOIN user_roles r ON u.role_id = r.id
       ")->fetchAll(PDO::FETCH_ASSOC);
       echo json_encode(["users" => $users]);
       break;

   case "POST":
       $data = json_decode(file_get_contents("php://input"), true);
       $stmt = $db->prepare("
           INSERT INTO users (email, password, name, role_id)
           VALUES (?, ?, ?, ?)
       ");
       $stmt->execute([
           $data["email"],
           password_hash($data["password"], PASSWORD_DEFAULT),
           $data["name"],
           $data["role_id"]
       ]);
       echo json_encode(["status" => "success"]);
       break;
}
