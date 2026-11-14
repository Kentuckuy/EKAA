#!/usr/bin/env bash
# =============================================================================
# EKAA Healthcheck Script (§17.2, §32.2)
# =============================================================================
set -euo pipefail

echo "=== EKAA System Healthcheck ==="

if ! command -v docker &> /dev/null; then
    echo "[!] Docker not found. Skipping live container check."
    exit 0
fi

SERVICES=("proxy" "web" "app" "connector" "ai-api" "postgres" "valkey" "objectstore" "clamav")

for s in "${SERVICES[@]}"; do
    STATUS=$(docker compose ps --format json "${s}" 2>/dev/null | grep -o '"Health":"[^"]*"' || echo '"Health":"unknown"')
    echo "[+] Service ${s}: ${STATUS}"
done
