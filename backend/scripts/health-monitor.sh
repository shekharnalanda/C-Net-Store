#!/usr/bin/env bash
set -euo pipefail

LOG="/home4/mcied45x/repositories/C-Net-Store/backend/storage/logs/health-monitor.log"
BASE="https://cnetstore.mciedu.com"
failed=0

for path in / /up /api/v1/health /admin/login /shop /cart; do
  status="$(curl -sS -L -o /dev/null -w '%{http_code}' "$BASE$path" || true)"
  if [ "$status" != "200" ]; then
    echo "$(date -Is) FAIL $path HTTP=$status" >> "$LOG"
    failed=1
  fi
done

if [ "$failed" -eq 0 ]; then
  echo "$(date -Is) CNET_STORE_HEALTH_OK" >> "$LOG"
fi

tail -n 1000 "$LOG" > "$LOG.tmp" && mv "$LOG.tmp" "$LOG"
exit "$failed"
