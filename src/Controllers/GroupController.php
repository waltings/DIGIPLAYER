<?php
namespace Controllers;

class GroupController extends BaseController {
    public function getGroups() {
        try {
            $groups = $this->db->query("SELECT * FROM groups ORDER BY name")->fetchAll();
            
            // Get devices for each group
            foreach ($groups as &$group) {
                $stmt = $this->db->prepare("
                    SELECT d.* 
                    FROM devices d 
                    JOIN device_group dg ON d.id = dg.device_id 
                    WHERE dg.group_id = ?
                ");
                $stmt->execute([$group['id']]);
                $group['devices'] = $stmt->fetchAll();
            }
            
            $this->jsonResponse(['groups' => $groups]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to fetch groups");
        }
    }

    public function createGroup() {
        $data = $this->validateRequest(['name']);
        try {
            $stmt = $this->db->prepare("
                INSERT INTO groups (name, description) 
                VALUES (:name, :description)
            ");
            
            $stmt->execute([
                'name' => $data['name'],
                'description' => $data['description'] ?? ''
            ]);
            
            $groupId = $this->db->lastInsertId();

            // Add devices if provided
            if (!empty($data['device_ids'])) {
                $this->assignDevicesToGroup($groupId, $data['device_ids']);
            }

            $this->jsonResponse([
                'status' => 'success',
                'id' => $groupId
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to create group");
        }
    }

    public function updateGroup($id) {
        $data = $this->validateRequest(['name']);
        try {
            $this->db->beginTransaction();

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

            // Update device assignments if provided
            if (isset($data['device_ids'])) {
                // Remove existing assignments
                $stmt = $this->db->prepare("DELETE FROM device_group WHERE group_id = ?");
                $stmt->execute([$id]);

                // Add new assignments
                if (!empty($data['device_ids'])) {
                    $this->assignDevicesToGroup($id, $data['device_ids']);
                }
            }

            $this->db->commit();
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse("Failed to update group");
        }
    }

    public function deleteGroup($id) {
        try {
            $this->db->beginTransaction();

            // Remove device associations
            $stmt = $this->db->prepare("DELETE FROM device_group WHERE group_id = ?");
            $stmt->execute([$id]);

            // Delete group
            $stmt = $this->db->prepare("DELETE FROM groups WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse("Failed to delete group");
        }
    }

    private function assignDevicesToGroup($groupId, $deviceIds) {
        $stmt = $this->db->prepare("
            INSERT INTO device_group (device_id, group_id) 
            VALUES (?, ?)
        ");
        
        foreach ($deviceIds as $deviceId) {
            $stmt->execute([$deviceId, $groupId]);
        }
    }

    public function addDeviceToGroup() {
        $data = $this->validateRequest(['device_id', 'group_id']);
        try {
            $stmt = $this->db->prepare("
                INSERT INTO device_group (device_id, group_id) 
                VALUES (:device_id, :group_id)
            ");
            
            $stmt->execute([
                'device_id' => $data['device_id'],
                'group_id' => $data['group_id']
            ]);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to add device to group");
        }
    }

    public function removeDeviceFromGroup() {
        $data = $this->validateRequest(['device_id', 'group_id']);
        try {
            $stmt = $this->db->prepare("
                DELETE FROM device_group 
                WHERE device_id = :device_id AND group_id = :group_id
            ");
            
            $stmt->execute([
                'device_id' => $data['device_id'],
                'group_id' => $data['group_id']
            ]);
            
            $this->jsonResponse(['status' => 'success']);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to remove device from group");
        }
    }
}
