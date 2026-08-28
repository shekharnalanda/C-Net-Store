#!/usr/bin/env bash
set -euo pipefail

command -v flutter >/dev/null 2>&1 || {
  echo "Flutter SDK is required." >&2
  exit 1
}

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

for app in customer seller delivery; do
  APP_DIR="${ROOT_DIR}/apps/${app}"
  echo "Preparing C-Net Store ${app} app"
  (
    cd "${APP_DIR}"
    flutter create \
      --platforms=android,ios \
      --org com.mciedu.cnetstore \
      --project-name "cnet_store_${app}" \
      .
    flutter pub get
    dart format lib test
    flutter analyze --fatal-infos
    flutter test
  )
done

echo "CNET_STORE_MOBILE_PREPARATION_COMPLETED"
