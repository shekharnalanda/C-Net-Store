#!/usr/bin/env bash
set -euo pipefail
umask 077

APPDIR="/home4/mcied45x/repositories/C-Net-Store/backend"
BACKUPROOT="/home4/mcied45x/backups/cnet-store"
STAMP="$(date +%Y-%m-%d_%H-%M-%S)"

mkdir -p "$BACKUPROOT/database" "$BACKUPROOT/storage"

env_value() {
  local key="$1" value
  value="$(sed -n "s/^$key=//p" "$APPDIR/.env" | tail -n 1 | tr -d '\r')"
  value="${value%\"}"
  value="${value#\"}"
  value="${value%\'}"
  value="${value#\'}"
  printf '%s' "$value"
}

DB_HOST="$(env_value DB_HOST)"
DB_PORT="$(env_value DB_PORT)"
DB_NAME="$(env_value DB_DATABASE)"
DB_USER="$(env_value DB_USERNAME)"
DB_PASS="$(env_value DB_PASSWORD)"

MYSQL_PWD="$DB_PASS" mysqldump --single-transaction --quick --lock-tables=false \
  -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USER" "$DB_NAME" \
  | gzip -9 > "$BACKUPROOT/database/cnetstore-db-$STAMP.sql.gz"

tar -C "$APPDIR" -czf "$BACKUPROOT/storage/cnetstore-storage-$STAMP.tar.gz" storage/app/public

find "$BACKUPROOT/database" -type f -name 'cnetstore-db-*.sql.gz' -mtime +14 -delete
find "$BACKUPROOT/storage" -type f -name 'cnetstore-storage-*.tar.gz' -mtime +14 -delete

test -s "$BACKUPROOT/database/cnetstore-db-$STAMP.sql.gz"
test -s "$BACKUPROOT/storage/cnetstore-storage-$STAMP.tar.gz"

echo "$(date -Is) CNET_STORE_BACKUP_OK" >> "$BACKUPROOT/backup.log"
