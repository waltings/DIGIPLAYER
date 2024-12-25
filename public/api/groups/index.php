<?php
require_once __DIR__ . '/../../../src/Controllers/BaseController.php';

class GroupsController extends \Controllers\BaseController {
    public function getGroups() {
        try {
            $stmt = $this->db->query("SELECT * FROM groups ORDER BY name");
            $groups = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get devices for each group
            foreach ($groups as &$group) {
                $stmt = $this->db->prepare("
                    SELECT d.* 
                    FROM devices d 
                    JOIN device_group dg ON d.id = dg.device_id 
                    WHERE dg.group_id = ?
                ");
                $stmt->execute([$group['id']]);
                $group['devices'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            
            $this->jsonResponse(['groups' => $groups ?: []]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch groups: " . $e->getMessage());
        }
    }

    public function getGroup($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM groups WHERE id = ?");
            $stmt->execute([$id]);
            $group = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$group) {
                $this->errorResponse("Group not found", 404);
            }
            
            $this->jsonResponse(['group' => $group, 'status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch group: " . $e->getMessage());
        }
    }

    public function createGroup($data) {
        if (!isset($data['name'])) {
            $this->errorResponse("Name is required");
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO groups (name, description) 
                VALUES (:name, :description)
            ");
            
            $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? ''
            ]);
            
            $this->jsonResponse([
                'status' => 'success',
                'id' => $this->db->lastInsertId()
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to create group: " . $e->getMessage());
        }
    }

    public function updateGroup($id, $data) {
        try {
            $stmt = $this->db->prepare("
                UPDATE groups 
                SET name = :name, description = :description 
                WHERE id = :id
            ");
            
            $stmt->execute([
                'id' => $id,
                'name' => $data['name'],
                'description' => $data['description'] ?? ''
            ]);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to update group: " . $e->getMessage());
        }
    }

    public function deleteGroup($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM groups WHERE id = ?");
            $stmt->execute([$id]);
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to delete group: " . $e->getMessage());
        }
    }
}

$controller = new GroupsController();

switch($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if(isset($_GET['id'])) {
            $controller->getGroup($_GET['id']);
        } else {
            $controller->getGroups();
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->createGroup($data);
        break;
        
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->updateGroup($data['id'], $data);
        break;
        
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $controller->deleteGroup($data['id']);
        break;
        
    default:
        $controller->errorResponse('Method not allowed', 405);
}