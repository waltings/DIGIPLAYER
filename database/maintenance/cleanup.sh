#!/bin/bash

DB_HOST="localhost"
DB_NAME="vhost15998s0"
DB_USER="vhost15998s0"
DB_PASS="Digiplayer1-401"

# Clean up old logs
echo "Cleaning up old logs..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME << EOF
    -- Delete logs older than 30 days
    DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    DELETE FROM connectivity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

    -- Delete performance metrics older than 90 days
    DELETE FROM performance_metrics WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY);

    -- Delete resolved alerts older than 30 days
    DELETE FROM alerts WHERE status = 'resolved' AND resolved_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

    -- Delete old playback logs
    DELETE FROM playback_logs WHERE start_time < DATE_SUB(NOW(), INTERVAL 90 DAY);

    -- Delete old sync logs
    DELETE FROM sync_logs WHERE completed_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

    -- Optimize tables
    OPTIMIZE TABLE system_logs, activity_logs, connectivity_logs, 
                 performance_metrics, alerts, playback_logs, sync_logs;
EOF

# Clean up unused media files
echo "Cleaning up unused media files..."
find /home/vhost15998ssh/htdocs/digiplayer/public/uploads/media -type f -mtime +90 -name "*.tmp" -delete

echo "Cleanup completed!"
