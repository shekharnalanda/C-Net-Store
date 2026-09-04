#!/usr/bin/env bash
set -euo pipefail

store_url="${1:-https://cnetstore.mciedu.com}"
failures=0
body_file="$(mktemp)"
headers_file="$(mktemp)"
trap 'rm -f "$body_file" "$headers_file"' EXIT

pass() { echo "PASS $1"; }
fail() { echo "FAIL $1"; failures=$((failures + 1)); }

check_url() {
  label="$1"
  url="$2"
  expected="$3"
  status="$(curl --silent --show-error --location --output "$body_file" --write-out '%{http_code}' "$url" || true)"
  if [ "$status" = "$expected" ]; then pass "$label ($status)"; else fail "$label (expected $expected, received $status)"; fi
}

check_protected() {
  label="$1"
  url="$2"
  expected="$3"
  status="$(curl --silent --show-error --output "$body_file" --write-out '%{http_code}' "$url" || true)"
  if [ "$status" = "$expected" ]; then pass "$label protected ($status)"; else fail "$label protection (expected $expected, received $status)"; fi
}

check_api_protected() {
  label="$1"
  url="$2"
  status="$(curl --silent --show-error --header 'Accept: application/json' --output "$body_file" --write-out '%{http_code}' "$url" || true)"
  if [ "$status" = "401" ]; then pass "$label protected ($status)"; else fail "$label protection (expected 401, received $status)"; fi
}

check_apk_redirect() {
  label="$1"
  path="$2"
  asset="$3"
  curl --silent --show-error --head --output "$headers_file" "$store_url$path" || true
  status="$(awk 'toupper($1) ~ /^HTTP\// {code=$2} END {print code}' "$headers_file")"
  location="$(awk 'BEGIN{IGNORECASE=1} /^location:/ {sub(/\r$/,""); print $2}' "$headers_file" | tail -1)"
  if [ "$status" = "302" ] && [[ "$location" == *"github.com/shekharnalanda/C-Net-Store/releases/latest/download/$asset"* ]]; then
    pass "$label APK redirect"
  else
    fail "$label APK redirect (status=$status)"
  fi
}

check_url "Homepage" "$store_url/" "200"
check_url "Shop" "$store_url/shop" "200"
check_url "Cart" "$store_url/cart" "200"
check_url "Laravel health" "$store_url/up" "200"
check_url "API health" "$store_url/api/v1/health" "200"
check_url "Public customer catalog API" "$store_url/api/v1/customer/catalog" "200"
check_url "Admin login" "$store_url/admin/login" "200"

for policy in privacy-policy terms-and-conditions cancellation-refund-policy shipping-delivery-policy seller-marketplace-terms; do
  check_url "Policy $policy" "$store_url/pages/$policy" "200"
done

check_protected "Web admin products" "$store_url/admin/products" "302"
check_protected "Web admin orders" "$store_url/admin/orders" "302"
check_api_protected "Customer orders API" "$store_url/api/v1/customer/orders"
check_api_protected "Seller dashboard API" "$store_url/api/v1/seller/dashboard"
check_api_protected "Delivery assignments API" "$store_url/api/v1/delivery/assignments"
check_api_protected "Admin dashboard API" "$store_url/api/v1/admin/dashboard"

check_apk_redirect "Customer" "/app/customer" "C-Net-Store-Customer.apk"
check_apk_redirect "Seller" "/app/seller" "C-Net-Store-Seller.apk"
check_apk_redirect "Delivery Partner" "/app/delivery" "C-Net-Store-Delivery-Partner.apk"

if [ "$failures" -gt 0 ]; then
  echo "GO-LIVE AUDIT FAILED: $failures check(s) failed."
  exit 1
fi

echo "CNET_GO_LIVE_AUDIT=PASS"
