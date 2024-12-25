#!/bin/bash

DB_HOST="localhost"
DB_NAME="vhost15998s0"
DB_USER="vhost15998s0"
DB_PASS="Digiplayer1-401"

# Check database connection
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -e "SELECT 1;" $DB_NAME > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo "ERROR: Database connection failed"
    exit 1
fi

# Check disk space
DISK_USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 90 ]; then
    echo "WARNING: Disk usage is above 90%"
fi

# Check media directory permissions
MEDIA_DIR="/home/vhost15998ssh/htdocs/digiplayer/public/uploads/media"
if [ ! -w "$MEDIA_DIR" ]; then
    echo "ERROR: Media directory is not writable"
fi

# Check for offline devices
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME << EOF
    SELECT COUNT(*) as offline_count FROM devices 
    WHERE status = 'offline' 
    AND last_heartbeat < DATE_SUB(NOW(), INTERVAL 5 MINUTE);
EOF

# Check system load
LOAD=$(uptime | awk -F'load average:' '{ print $2 }' | cut -d, -f1)
if [ $(echo "$LOAD > 5" | bc) -eq 1 ]; then
    echo "WARNING: High system load: $LOAD"
fi

# Generate health report
REPORT_DIR="/home/vhost15998ssh/logs/health"
mkdir -p $REPORT_DIR
REPORT_FILE="$REPORT_DIR/health_$(date +%Y%m%d).log"

{
    echo "=== Health Check Report $(date) ==="
    echo "Database Status: OK"
    echo "Disk Usage: ${DISK_USAGE}%"
    echo "System Load: $LOAD"
    
    # Get some statistics
    mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME << EOF
        SELECT 
            (SELECT COUNT(*) FROM devices WHERE status = 'online') as online_devices,
            (SELECT COUNT(*) FROM alerts WHERE status = 'active') as active_alerts,
            (SELECT COUNT(*) FROM sync_queue WHERE status = 'pending') as pending_syncs;
EOF
} > "$REPORT_FILE"

echo "Health check completed. Report saved to: $REPORT_FILE"
