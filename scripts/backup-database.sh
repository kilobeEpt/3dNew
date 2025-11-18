#!/bin/bash

##############################################################################
# Database Backup Script
# 
# Creates compressed MySQL database backups with date-stamped filenames.
# Retains backups for 30 days, then automatically removes older backups.
# Sends email notification on failure.
#
# Usage: ./backup-database.sh
# Cron: 0 4 * * * cd /home/c/ch167436/3dPrint && bash scripts/backup-database.sh >> logs/backup.log 2>&1
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
BACKUP_DIR="$PROJECT_DIR/backups/database"
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_FILE="database_backup_$DATE.sql.gz"
RETENTION_DAYS=30

# Database credentials from .env
DB_HOST="${DB_HOST:-localhost}"
DB_NAME="${DB_NAME:-}"
DB_USER="${DB_USER:-}"
DB_PASS="${DB_PASS:-}"
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
    
    # Send email notification (requires mail command or PHP)
    if command -v mail &> /dev/null; then
        echo -e "Database backup failed on $(hostname)\n\nError: $error_msg\n\nTimestamp: $(date)" | \
            mail -s "BACKUP FAILURE: Database Backup Failed" "$ADMIN_EMAIL"
    elif command -v php &> /dev/null; then
        php -r "mail('$ADMIN_EMAIL', 'BACKUP FAILURE: Database Backup Failed', 'Database backup failed on ' . gethostname() . '\n\nError: $error_msg\n\nTimestamp: ' . date('Y-m-d H:i:s'));"
    fi
}

# Check if database credentials are set
if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ] || [ -z "$DB_PASS" ]; then
    send_error_notification "Database credentials not found in .env file"
    exit 1
fi

log "${YELLOW}Starting database backup...${NC}"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Check if directory is writable
if [ ! -w "$BACKUP_DIR" ]; then
    send_error_notification "Backup directory is not writable: $BACKUP_DIR"
    exit 1
fi

# Check disk space (require at least 100MB free)
AVAILABLE_SPACE=$(df "$BACKUP_DIR" | tail -1 | awk '{print $4}')
if [ "$AVAILABLE_SPACE" -lt 102400 ]; then
    send_error_notification "Insufficient disk space. Available: ${AVAILABLE_SPACE}KB"
    exit 1
fi

# Perform backup
log "Creating backup: $BACKUP_FILE"
if mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>/dev/null | gzip > "$BACKUP_DIR/$BACKUP_FILE"; then
    BACKUP_SIZE=$(du -h "$BACKUP_DIR/$BACKUP_FILE" | cut -f1)
    log "${GREEN}✓ Backup created successfully: $BACKUP_FILE (Size: $BACKUP_SIZE)${NC}"
else
    send_error_notification "mysqldump command failed"
    exit 1
fi

# Verify backup file exists and is not empty
if [ ! -s "$BACKUP_DIR/$BACKUP_FILE" ]; then
    send_error_notification "Backup file is empty or does not exist"
    exit 1
fi

# Remove old backups (older than RETENTION_DAYS)
log "Cleaning up old backups (retention: $RETENTION_DAYS days)..."
DELETED_COUNT=$(find "$BACKUP_DIR" -name "database_backup_*.sql.gz" -type f -mtime +$RETENTION_DAYS -delete -print | wc -l)
if [ "$DELETED_COUNT" -gt 0 ]; then
    log "${YELLOW}Deleted $DELETED_COUNT old backup(s)${NC}"
else
    log "No old backups to delete"
fi

# Count total backups
TOTAL_BACKUPS=$(find "$BACKUP_DIR" -name "database_backup_*.sql.gz" -type f | wc -l)
log "Total backups retained: $TOTAL_BACKUPS"

# Display backup directory usage
BACKUP_DIR_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
log "Backup directory size: $BACKUP_DIR_SIZE"

log "${GREEN}✓ Database backup completed successfully${NC}"

exit 0
