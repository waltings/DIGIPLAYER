#!/bin/bash

DB_HOST="localhost"
DB_NAME="vhost15998s0"
DB_USER="vhost15998s0"
DB_PASS="Digiplayer1-401"

# Optimize database tables
echo "Optimizing database tables..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME << EOF
    ANALYZE TABLE users, devices, media, playlists, schedules, groups;
    OPTIMIZE TABLE users, devices, media, playlists, schedules, groups;
EOF

# Clean up temporary files
echo "Cleaning up temporary files..."
find /home/vhost15998ssh/htdocs/digiplayer/public/uploads/temp -type f -mtime +1 -delete

# Reset failed login attempts
echo "Resetting failed login attempts..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME << EOF
    UPDATE users SET failed_attempts = 0 
    WHERE last_failed_login < DATE_SUB(NOW(), INTERVAL 24 HOUR);
EOF

echo "Optimization completed!"
