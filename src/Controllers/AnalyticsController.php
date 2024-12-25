<?php
namespace Controllers;

class AnalyticsController extends BaseController {
    public function getSystemOverview() {
        try {
            $this->checkPermission('read', 'analytics');

            // Get devices statistics
            $deviceStats = $this->db->query("
                SELECT 
                    COUNT(*) as total_devices,
                    SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online_devices,
                    SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline_devices,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_devices
                FROM devices
            ")->fetch();

            // Get content statistics
            $contentStats = $this->db->query("
                SELECT 
                    COUNT(*) as total_content,
                    SUM(CASE WHEN type = 'video' THEN 1 ELSE 0 END) as video_count,
                    SUM(CASE WHEN type = 'image' THEN 1 ELSE 0 END) as image_count,
                    SUM(size) as total_storage_used
                FROM media
            ")->fetch();

            // Get playback statistics for last 24 hours
            $playbackStats = $this->db->query("
                SELECT 
                    COUNT(*) as total_plays,
                    COUNT(DISTINCT device_id) as unique_devices,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_count
                FROM playback_logs
                WHERE start_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ")->fetch();

            // Calculate error rate
            $errorRate = $playbackStats['total_plays'] > 0 
                ? ($playbackStats['error_count'] / $playbackStats['total_plays']) * 100 
                : 0;

            $this->response([
                'device_stats' => $deviceStats,
                'content_stats' => $contentStats,
                'playback_stats' => $playbackStats,
                'error_rate' => round($errorRate, 2)
            ]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getDeviceStats($deviceId, $startDate = null, $endDate = null) {
        try {
            $this->checkPermission('read', 'analytics');

            if (!$startDate) $startDate = date('Y-m-d', strtotime('-7 days'));
            if (!$endDate) $endDate = date('Y-m-d');

            // Get device performance metrics
            $sql = "
                SELECT 
                    DATE(created_at) as date,
                    AVG(cpu_usage) as avg_cpu,
                    AVG(memory_usage) as avg_memory,
                    AVG(storage_usage) as avg_storage,
                    AVG(network_speed) as avg_network
                FROM device_stats
                WHERE device_id = :device_id
                AND DATE(created_at) BETWEEN :start_date AND :end_date
                GROUP BY DATE(created_at)
                ORDER BY date
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'device_id' => $deviceId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            $performanceMetrics = $stmt->fetchAll();

            // Get playback statistics
            $sql = "
                SELECT 
                    DATE(start_time) as date,
                    COUNT(*) as total_plays,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_plays,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_plays,
                    AVG(duration) as avg_duration
                FROM playback_logs
                WHERE device_id = :device_id
                AND DATE(start_time) BETWEEN :start_date AND :end_date
                GROUP BY DATE(start_time)
                ORDER BY date
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'device_id' => $deviceId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            $playbackStats = $stmt->fetchAll();

            // Get uptime statistics
            $sql = "
                SELECT 
                    DATE(created_at) as date,
                    SUM(CASE WHEN event_type = 'connect' THEN 1 ELSE 0 END) as connects,
                    SUM(CASE WHEN event_type = 'disconnect' THEN 1 ELSE 0 END) as disconnects
                FROM connectivity_logs
                WHERE device_id = :device_id
                AND DATE(created_at) BETWEEN :start_date AND :end_date
                GROUP BY DATE(created_at)
                ORDER BY date
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'device_id' => $deviceId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            $uptimeStats = $stmt->fetchAll();

            $this->response([
                'performance_metrics' => $performanceMetrics,
                'playback_stats' => $playbackStats,
                'uptime_stats' => $uptimeStats
            ]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function getContentStats($period = '7d') {
        try {
            $this->checkPermission('read', 'analytics');

            $startDate = $this->getPeriodStartDate($period);

            // Get most played content
            $sql = "
                SELECT 
                    m.id,
                    m.name,
                    m.type,
                    COUNT(*) as play_count,
                    COUNT(DISTINCT pl.device_id) as unique_devices,
                    AVG(pl.duration) as avg_duration,
                    SUM(CASE WHEN pl.status = 'error' THEN 1 ELSE 0 END) as error_count
                FROM playback_logs pl
                JOIN media m ON pl.media_id = m.id
                WHERE pl.start_time >= :start_date
                GROUP BY m.id, m.name, m.type
                ORDER BY play_count DESC
                LIMIT 10
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['start_date' => $startDate]);
            $topContent = $stmt->fetchAll();

            // Get content type distribution
            $sql = "
                SELECT 
                    type,
                    COUNT(*) as count,
                    SUM(size) as total_size
                FROM media
                GROUP BY type
            ";
            
            $typeDistribution = $this->db->query($sql)->fetchAll();

            // Get daily playback trends
            $sql = "
                SELECT 
                    DATE(start_time) as date,
                    COUNT(*) as total_plays,
                    COUNT(DISTINCT device_id) as unique_devices,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
                FROM playback_logs
                WHERE start_time >= :start_date
                GROUP BY DATE(start_time)
                ORDER BY date
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['start_date' => $startDate]);
            $dailyTrends = $stmt->fetchAll();

            $this->response([
                'top_content' => $topContent,
                'type_distribution' => $typeDistribution,
                'daily_trends' => $dailyTrends
            ]);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    public function generateReport($params) {
        try {
            $this->checkPermission('read', 'analytics');

            $startDate = $params['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $params['end_date'] ?? date('Y-m-d');
            $type = $params['type'] ?? 'system';

            $report = [
                'generated_at' => date('Y-m-d H:i:s'),
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ];

            switch ($type) {
                case 'system':
                    $report['data'] = $this->generateSystemReport($startDate, $endDate);
                    break;
                case 'device':
                    if (!isset($params['device_id'])) {
                        throw new \Exception("Device ID required for device report", 400);
                    }
                    $report['data'] = $this->generateDeviceReport(
                        $params['device_id'], 
                        $startDate, 
                        $endDate
                    );
                    break;
                case 'content':
                    $report['data'] = $this->generateContentReport($startDate, $endDate);
                    break;
                default:
                    throw new \Exception("Invalid report type", 400);
            }

            // Log report generation
            $this->logActivity('generate_report', 'analytics', null, [
                'type' => $type,
                'period' => $report['period']
            ]);

            $this->response($report);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    private function getPeriodStartDate($period) {
        switch ($period) {
            case '24h': return date('Y-m-d H:i:s', strtotime('-24 hours'));
            case '7d': return date('Y-m-d', strtotime('-7 days'));
            case '30d': return date('Y-m-d', strtotime('-30 days'));
            case '90d': return date('Y-m-d', strtotime('-90 days'));
            default: throw new \Exception("Invalid period", 400);
        }
    }

    private function generateSystemReport($startDate, $endDate) {
        // Implementation for system-wide report
        // ...
    }

    private function generateDeviceReport($deviceId, $startDate, $endDate) {
        // Implementation for device-specific report
        // ...
    }

    private function generateContentReport($startDate, $endDate) {
        // Implementation for content usage report
        // ...
    }
}
