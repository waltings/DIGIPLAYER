<?php
namespace Controllers;

class BaseController {
    protected $db;
    protected $user;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
        
        $this->initDatabase();
        $this->user = $_SESSION['user'];
    }

    protected function initDatabase() {
        try {
            $config = require __DIR__ . '/../../config/database.php';
            
            $this->db = new \PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (\PDOException $e) {
            $this->jsonResponse(['error' => "Database connection failed"], 500);
            exit;
        }
    }

    protected function jsonResponse($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function errorResponse($message, $code = 400) {
        $this->jsonResponse(['error' => $message], $code);
    }

    protected function validateRequest($required = []) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                $this->errorResponse("Missing required field: $field");
            }
        }
        
        return $data;
    }
}
