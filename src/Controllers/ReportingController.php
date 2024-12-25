<?php
namespace Controllers;

class ReportingController extends BaseController {
    public function generateDeviceReport($deviceId, $startDate = null, $endDate = null) {
        try {
            // Validate dates or set defaults
            $startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $endDate ?? date('Y-m-d');

            $stmt = $this->db->prepare("
                SELECT 
                    d.name as device_name,
                    d.ip_address,
                    COUNT(DISTINCT pl.playlist_id) as total_playlists,
                    COUNT(DISTINCT pl.media_id) as total_media,
                    SUM(CASE WHEN pl.action = 'play' THEN 1 ELSE 0 END) as total_plays,
                    SUM(CASE WHEN pl.action = 'error' THEN 1 ELSE 0 END) as total_errors,
                    ROUND(AVG(ds.cpu_usage), 2) as avg_cpu,
                    ROUND(AVG(ds.memory_usage), 2) as avg_memory,
                    COUNT(DISTINCT DATE(pl.timestamp)) as active_days,
                    MAX(pl.timestamp) as last_activity
                FROM devices d
                LEFT JOIN playback_logs pl ON d.id = pl.device_id
                LEFT JOIN device_stats ds ON d.id = ds.device_id
                WHERE d.id = :device_id
                AND DATE(pl.timestamp) BETWEEN :start_date AND :end_date
                GROUP BY d.id, d.name, d.ip_address
            ");

            $stmt->execute([
                'device_id' => $deviceId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            $deviceStats = $stmt->fetch();

            // Get daily playback data
            $stmt = $this->db->prepare("
                SELECT 
                    DATE(timestamp) as date,
                    COUNT(*) as play_count,
                    COUNT(DISTINCT media_id) as unique_media,
                    SUM(duration) as total_duration
                FROM playback_logs
                WHERE device_id = :device_id
                AND DATE(timestamp) BETWEEN :start_date AND :end_date
                GROUP BY DATE(timestamp)
                ORDER BY date
            ");

            $stmt->execute([
                'device_id' => $deviceId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            $dailyStats = $stmt->fetchAll();

            $this->jsonResponse([
                'device_summary' => $deviceStats,
                'daily_stats' => $dailyStats,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to generate device report");
        }
    }

    public function generatePlaylistReport($playlistId, $startDate = null, $endDate = null) {
        try {
            $startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $endDate ?? date('Y-m-d');

            // Get playlist overview
            $stmt = $this->db->prepare("
                SELECT 
                    p.name as playlist_name,
                    COUNT(DISTINCT pl.device_id) as total_devices,
                    COUNT(DISTINCT pl.media_id) as total_media,
                    SUM(CASE WHEN pl.action = 'play' THEN 1 ELSE 0 END) as total_plays,
                    SUM(pl.duration) as total_duration,
                    COUNT(DISTINCT DATE(pl.timestamp)) as active_days
                FROM playlists p
                LEFT JOIN playback_logs pl ON p.id = pl.playlist_id
                WHERE p.id = :playlist_id
                AND DATE(pl.timestamp) BETWEEN :start_date AND :end_date
                GROUP BY p.id, p.name
            ");

            $stmt->execute([
                'playlist_id' => $playlistId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            $playlistStats = $stmt->fetch();

            // Get media performance within playlist
            $stmt = $this->db->prepare("
                SELECT 
                    m.name as media_name,
                    COUNT(*) as play_count,
                    SUM(pl.duration) as total_duration,
                    COUNT(CASE WHEN pl.action = 'error' THEN 1 ELSE 0 END) as error_count,
                    COUNT(DISTINCT pl.device_id) as unique_devices
                FROM playback_logs pl
                JOIN media m ON pl.media_id = m.id
                WHERE pl.playlist_id = :playlist_id
                AND DATE(pl.timestamp) BETWEEN :start_date AND :end_date
                GROUP BY pl.media_id, m.name
                ORDER BY play_count DESC
            ");

            $stmt->execute([
                'playlist_id' => $playlistId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            $mediaStats = $stmt->fetchAll();

            $this->jsonResponse([
                'playlist_summary' => $playlistStats,
                'media_stats' => $mediaStats,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to generate playlist report");
        }
    }

    public function generateSystemReport($startDate = null, $endDate = null) {
        try {
            $startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $endDate ?? date('Y-m-d');

            // System overview
            $systemStats = $this->db->query("
                SELECT 
                    COUNT(DISTINCT d.id) as total_devices,
                    COUNT(DISTINCT pl.playlist_id) as active_playlists,
                    COUNT(DISTINCT pl.media_id) as active_media,
                    SUM(CASE WHEN pl.action = 'play' THEN 1 ELSE 0 END) as total_plays,
                    SUM(CASE WHEN pl.action = 'error' THEN 1 ELSE 0 END) as total_errors,
                    ROUND(AVG(ds.cpu_usage), 2) as avg_system_cpu,
                    ROUND(AVG(ds.memory_usage), 2) as avg_system_memory
                FROM devices d
                LEFT JOIN playback_logs pl ON d.id = pl.device_id
                LEFT JOIN device_stats ds ON d.id = ds.device_id
                WHERE DATE(pl.timestamp) BETWEEN '$startDate' AND '$endDate'
            ")->fetch();

            // Device performance ranking
            $deviceRanking = $this->db->query("
                SELECT 
                    d.name as device_name,
                    COUNT(*) as play_count,
                    COUNT(DISTINCT pl.media_id) as unique_media,
                    SUM(pl.duration) as total_duration,
                    COUNT(CASE WHEN pl.action = 'error' THEN 1 ELSE 0 END) as error_count
                FROM devices d
                JOIN playback_logs pl ON d.id = pl.device_id
                WHERE DATE(pl.timestamp) BETWEEN '$startDate' AND '$endDate'
                GROUP BY d.id, d.name
                ORDER BY play_count DESC
            ")->fetchAll();

            $this->jsonResponse([
                'system_summary' => $systemStats,
                'device_ranking' => $deviceRanking,
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to generate system report");
        }
    }

    public function scheduleReport() {
        $data = $this->validateRequest(['report_type', 'frequency', 'recipients']);
        try {
            $stmt = $this->db->prepare("
                INSERT INTO scheduled_reports 
                (type, frequency, recipients, parameters, next_run)
                VALUES (:type, :frequency, :recipients, :parameters, :next_run)
            ");
            
            $nextRun = $this->calculateNextRun($data['frequency']);
            
            $stmt->execute([
                'type' => $data['report_type'],
                'frequency' => $data['frequency'],
                'recipients' => json_encode($data['recipients']),
                'parameters' => json_encode($data['parameters'] ?? []),
                'next_run' => $nextRun
            ]);
            
            $this->jsonResponse([
                'status' => 'success',
                'next_run' => $nextRun
            ]);
        } catch (\Exception $e) {
            $this->errorResponse("Failed to schedule report");
        }
    }

    private function calculateNextRun($frequency) {
        $now = new \DateTime();
        switch($frequency) {
            case 'daily':
                return $now->modify('+1 day')->setTime(0, 0);
            case 'weekly':
                return $now->modify('next monday')->setTime(0, 0);
            case 'monthly':
                return $now->modify('first day of next month')->setTime(0, 0);
            default:
                throw new \Exception("Invalid frequency");
        }
    }
}
