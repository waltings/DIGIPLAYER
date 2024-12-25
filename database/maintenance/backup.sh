#!/bin/bash

# Database connection details
DB_HOST="localhost"
DB_NAME="vhost15998s0"
DB_USER="vhost15998s0"
DB_PASS="Digiplayer1-401"

# Backup directories
BACKUP_DIR="/home/vhost15998ssh/backups"
MEDIA_DIR="/home/vhost15998ssh/htdocs/digiplayer/public/uploads/media"
DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_PATH="${BACKUP_DIR}/${DATE}"

# Create backup directories
mkdir -p "${BACKUP_PATH}"
mkdir -p "${BACKUP_PATH}/media"

# Database backup
echo "Creating database backup..."
mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > "${BACKUP_PATH}/database.sql"

# Media files backup
echo "Creating media files backup..."
rsync -av $MEDIA_DIR/ "${BACKUP_PATH}/media/"

# Create backup archive
tar -czf "${BACKUP_PATH}.tar.gz" -C "${BACKUP_DIR}" "${DATE}"

# Cleanup temporary files
rm -rf "${BACKUP_PATH}"

# Keep only last 7 daily backups
find "${BACKUP_DIR}" -name "*.tar.gz" -mtime +7 -delete

echo "Backup completed: ${BACKUP_PATH}.tar.gz"
