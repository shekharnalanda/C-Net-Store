#!/usr/bin/env bash
set -euo pipefail

store_url="${1:-https://cnetstore.mciedu.com}"
failures=0

check_url() {
  label="$1"
  url="$2"
  expected="$3"
  status="$(curl --silent --show-error --location --output /tmp/cnet-store-smoke-body --write-out '%{http_code}' "$url" || true)"
  if [ "$status" = "$expected" ]; then
    echo "PASS $label ($status)"
  else
    echo "FAIL $label (expected $expected, received $status)"
    failures=$((failures + 1))
  fi
}

check_url "Homepage" "$store_url/" "200"
check_url "Laravel health" "$store_url/up" "200"
check_url "API health" "$store_url/api/v1/health" "200"
check_url "Admin login" "$store_url/admin/login" "200"

if [ "$failures" -gt 0 ]; then
  echo "SMOKE FAILED: $failures check(s) failed."
  exit 1
fi

echo "SMOKE PASSED: public website and administration are reachable."
