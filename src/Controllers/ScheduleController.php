<?php
namespace Controllers;

class ScheduleController extends BaseController {
    public function getAllSchedules() {
        try {
            $this->checkPermission('read', 'schedules');
            
            $dateRange = $this->getDateRangeParams();
            $pagination = $this->getPaginationParams();

            // Get total count
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total 
                FROM schedules 
                WHERE (DATE(start_time) BETWEEN :start_date AND :end_date)
                OR days_of_week != ''
            ");
            
            $stmt->execute([
                ':start_date' => $dateRange['start_date'],
                ':end_date' => $dateRange['end_date']
            ]);
            $total = $stmt->fetch()['total'];

            // Get schedules with related data
            $sql = "
                SELECT 
                    s.*,
                    d.name as device_name,
                    p.name as playlist_name,
                    g.name as group_name
                FROM schedules s
                LEFT JOIN devices d ON s.device_id = d.id
                LEFT JOIN playlists p ON s.playlist_id = p.id
                LEFT JOIN groups g ON s.group_id = g.id
                WHERE (DATE(s.start_time) BETWEEN :start_date AND :end_date)
                OR s.days_of_week != ''
                ORDER BY s.priority DESC, s.start_time ASC
                LIMIT :offset, :limit
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':start_date' => $dateRange['start_date'],
                ':end_date' => $dateRange['end_date'],
                ':offset' => $pagination['offset'],
                ':limit' => $pagination['limit']
            ]);
            
            $schedules = $stmt->fetchAll();

            $this->response([
                'schedules' => $schedules,
                'pagination' => [
                    'page' => $pagination['page'],
                    'limit' => $pagination['limit'],
                    'total' => $total,
                    'pages' => ceil($total / $pagination['limit'])
                ]
            ]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getActiveSchedules($deviceId = null) {
        try {
            $this->checkPermission('read', 'schedules');

            $now = new \DateTime();
            $currentTime = $now->format('H:i:s');
            $currentDay = strtolower($now->format('l'));

            $params = [
                ':current_time' => $currentTime,
                ':current_day' => "%$currentDay%"
            ];

            $deviceCondition = "";
            if ($deviceId) {
                $deviceCondition = "AND s.device_id = :device_id";
                $params[':device_id'] = $deviceId;
            }

            $sql = "
                SELECT 
                    s.*,
                    d.name as device_name,
                    p.name as playlist_name
                FROM schedules s
                JOIN devices d ON s.device_id = d.id
                JOIN playlists p ON s.playlist_id = p.id
                WHERE (
                    (s.start_time <= :current_time AND s.end_time >= :current_time)
                    AND (
                        s.days_of_week = '*' 
                        OR s.days_of_week LIKE :current_day
                    )
                    OR (
                        s.is_exception = 1 
                        AND s.exception_date = CURRENT_DATE
                    )
                )
                $deviceCondition
                ORDER BY s.priority DESC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $schedules = $stmt->fetchAll();

            $this->response(['schedules' => $schedules]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function createSchedule($data) {
        try {
            $this->checkPermission('create', 'schedules');
            
            $data = $this->validateRequest([
                'device_id', 'playlist_id', 'start_time', 'end_time'
            ]);
            $data = $this->sanitizeInput($data);

            // Validate time format and device/playlist existence
            $this->validateScheduleData($data);

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO schedules (
                    device_id, playlist_id, start_time, end_time,
                    days_of_week, priority, is_exception, exception_date,
                    timezone
                ) VALUES (
                    :device_id, :playlist_id, :start_time, :end_time,
                    :days_of_week, :priority, :is_exception, :exception_date,
                    :timezone
                )
            ");

            $stmt->execute([
                'device_id' => $data['device_id'],
                'playlist_id' => $data['playlist_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'days_of_week' => $data['days_of_week'] ?? '*',
                'priority' => $data['priority'] ?? 1,
                'is_exception' => $data['is_exception'] ?? false,
                'exception_date' => $data['exception_date'] ?? null,
                'timezone' => $data['timezone'] ?? 'UTC'
            ]);

            $scheduleId = $this->db->lastInsertId();

            $this->db->commit();

            // Log activity
            $this->logActivity('create', 'schedule', $scheduleId, $data);

            $this->response([
                'status' => 'success',
                'id' => $scheduleId,
                'message' => 'Schedule created successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    private function validateScheduleData($data) {
        // Check if device exists
        $stmt = $this->db->prepare("SELECT id FROM devices WHERE id = ?");
        $stmt->execute([$data['device_id']]);
        if (!$stmt->fetch()) {
            throw new \Exception("Device not found", 404);
        }

        // Check if playlist exists
        $stmt = $this->db->prepare("SELECT id FROM playlists WHERE id = ?");
        $stmt->execute([$data['playlist_id']]);
        if (!$stmt->fetch()) {
            throw new \Exception("Playlist not found", 404);
        }

        // Validate time format
        if (!strtotime($data['start_time']) || !strtotime($data['end_time'])) {
            throw new \Exception("Invalid time format", 400);
        }

        // Check for schedule conflicts
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as conflict_count
            FROM schedules
            WHERE device_id = :device_id
            AND (
                (start_time <= :end_time AND end_time >= :start_time)
                OR (start_time <= :end_time AND end_time >= :start_time)
            )
            AND (
                days_of_week = '*'
                OR days_of_week LIKE :days_of_week
            )
            AND id != :schedule_id
        ");

        $stmt->execute([
            'device_id' => $data['device_id'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'days_of_week' => $data['days_of_week'] ?? '*',
            'schedule_id' => $data['id'] ?? 0
        ]);

        if ($stmt->fetch()['conflict_count'] > 0) {
            throw new \Exception("Schedule conflict detected", 409);
        }
    }

    public function updateSchedule($data) {
        try {
            $this->checkPermission('update', 'schedules');
            
            $data = $this->validateRequest(['id']);
            $data = $this->sanitizeInput($data);

            // Validate schedule exists
            $stmt = $this->db->prepare("SELECT id FROM schedules WHERE id = ?");
            $stmt->execute([$data['id']]);
            if (!$stmt->fetch()) {
                throw new \Exception("Schedule not found", 404);
            }

            // Validate update data
            if (isset($data['start_time']) && isset($data['end_time'])) {
                $this->validateScheduleData($data);
            }

            $this->db->beginTransaction();

            $updateFields = [];
            $params = ['id' => $data['id']];

            // Build dynamic update query
            foreach ([
                'device_id', 'playlist_id', 'start_time', 'end_time',
                'days_of_week', 'priority', 'is_exception', 'exception_date',
                'timezone'
            ] as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
            }

            if (!empty($updateFields)) {
                $sql = "UPDATE schedules SET " . implode(', ', $updateFields) . 
                       " WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($params);
            }

            $this->db->commit();

            // Log activity
            $this->logActivity('update', 'schedule', $data['id'], $data);

            $this->response([
                'status' => 'success',
                'message' => 'Schedule updated successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function deleteSchedule($id) {
        try {
            $this->checkPermission('delete', 'schedules');

            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM schedules WHERE id = ?");
            $stmt->execute([$id]);

            $this->db->commit();

            // Log activity
            $this->logActivity('delete', 'schedule', $id);

            $this->response([
                'status' => 'success',
                'message' => 'Schedule deleted successfully'
            ]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}
