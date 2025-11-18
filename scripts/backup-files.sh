#!/bin/bash

##############################################################################
# File Backup Script
# 
# Creates compressed archive of uploads directory with date-stamped filename.
# Retains backups for 56 days (8 weeks), then automatically removes older backups.
# Sends email notification on failure.
#
# Usage: ./backup-files.sh
# Cron: 0 5 * * 0 cd /home/c/ch167436/3dPrint && bash scripts/backup-files.sh >> logs/backup.log 2>&1
##############################################################################

set -e

# Load environment variables
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Source .env file if it exists
if [ -f "$PROJECT_DIR/.env" ]; then
    export $(grep -v '^#' "$PROJECT_DIR/.env" | xargs)
fi

# Configuration
BACKUP_DIR="$PROJECT_DIR/backups/files"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_FILE="uploads_backup_$DATE.tar.gz"
SOURCE_DIR="$PROJECT_DIR/uploads"
RETENTION_DAYS=56  # 8 weeks

ADMIN_EMAIL="${ADMIN_EMAIL:-admin@example.com}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# Error notification function
send_error_notification() {
    local error_msg="$1"
    log "${RED}ERROR: $error_msg${NC}"
    
    # Send email notification
    if command -v mail &> /dev/null; then
        echo -e "File backup failed on $(hostname)\n\nError: $error_msg\n\nTimestamp: $(date)" | \
            mail -s "BACKUP FAILURE: File Backup Failed" "$ADMIN_EMAIL"
    elif command -v php &> /dev/null; then
        php -r "mail('$ADMIN_EMAIL', 'BACKUP FAILURE: File Backup Failed', 'File backup failed on ' . gethostname() . '\n\nError: $error_msg\n\nTimestamp: ' . date('Y-m-d H:i:s'));"
    fi
}

log "${YELLOW}Starting file backup...${NC}"

# Check if source directory exists
if [ ! -d "$SOURCE_DIR" ]; then
    send_error_notification "Source directory does not exist: $SOURCE_DIR"
    exit 1
fi

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Check if directory is writable
if [ ! -w "$BACKUP_DIR" ]; then
    send_error_notification "Backup directory is not writable: $BACKUP_DIR"
    exit 1
fi

# Check disk space (require at least 500MB free)
AVAILABLE_SPACE=$(df "$BACKUP_DIR" | tail -1 | awk '{print $4}')
if [ "$AVAILABLE_SPACE" -lt 512000 ]; then
    send_error_notification "Insufficient disk space. Available: ${AVAILABLE_SPACE}KB"
    exit 1
fi

# Count files to backup
FILE_COUNT=$(find "$SOURCE_DIR" -type f | wc -l)
log "Files to backup: $FILE_COUNT"

# Calculate source directory size
SOURCE_SIZE=$(du -sh "$SOURCE_DIR" | cut -f1)
log "Source directory size: $SOURCE_SIZE"

# Perform backup
log "Creating backup: $BACKUP_FILE"
if tar -czf "$BACKUP_DIR/$BACKUP_FILE" -C "$PROJECT_DIR" "uploads" 2>/dev/null; then
    BACKUP_SIZE=$(du -h "$BACKUP_DIR/$BACKUP_FILE" | cut -f1)
    log "${GREEN}✓ Backup created successfully: $BACKUP_FILE (Size: $BACKUP_SIZE)${NC}"
else
    send_error_notification "tar command failed"
    exit 1
fi

# Verify backup file exists and is not empty
if [ ! -s "$BACKUP_DIR/$BACKUP_FILE" ]; then
    send_error_notification "Backup file is empty or does not exist"
    exit 1
fi

# Remove old backups (older than RETENTION_DAYS)
log "Cleaning up old backups (retention: $RETENTION_DAYS days)..."
DELETED_COUNT=$(find "$BACKUP_DIR" -name "uploads_backup_*.tar.gz" -type f -mtime +$RETENTION_DAYS -delete -print | wc -l)
if [ "$DELETED_COUNT" -gt 0 ]; then
    log "${YELLOW}Deleted $DELETED_COUNT old backup(s)${NC}"
else
    log "No old backups to delete"
fi

# Count total backups
TOTAL_BACKUPS=$(find "$BACKUP_DIR" -name "uploads_backup_*.tar.gz" -type f | wc -l)
log "Total backups retained: $TOTAL_BACKUPS"

# Display backup directory usage
BACKUP_DIR_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
log "Backup directory size: $BACKUP_DIR_SIZE"

log "${GREEN}✓ File backup completed successfully${NC}"

exit 0
