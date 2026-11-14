#!/usr/bin/env bash
# =============================================================================
# EKAA Installation & Provisioning Script (§32.7)
# =============================================================================
set -euo pipefail

echo "=== EKAA System Provisioning ==="

# 1. Ensure .env exists
if [ ! -f .env ]; then
    echo "[+] Creating .env from .env.example..."
    cp .env.example .env
fi

# 2. Generate RSA Keypairs for Inter-Service JWT (DEC-030)
KEYS_DIR="deploy/keys"
mkdir -p "${KEYS_DIR}"

if [ ! -f "${KEYS_DIR}/core_private.pem" ]; then
    echo "[+] Generating Core Service RSA keypair..."
    openssl genrsa -out "${KEYS_DIR}/core_private.pem" 2048
    openssl rsa -in "${KEYS_DIR}/core_private.pem" -pubout -out "${KEYS_DIR}/core_public.pem"
fi

if [ ! -f "${KEYS_DIR}/ai_private.pem" ]; then
    echo "[+] Generating AI Service RSA keypair..."
    openssl genrsa -out "${KEYS_DIR}/ai_private.pem" 2048
    openssl rsa -in "${KEYS_DIR}/ai_private.pem" -pubout -out "${KEYS_DIR}/ai_public.pem"
fi

if [ ! -f "${KEYS_DIR}/connector_private.pem" ]; then
    echo "[+] Generating Connector Service RSA keypair..."
    openssl genrsa -out "${KEYS_DIR}/connector_private.pem" 2048
    openssl rsa -in "${KEYS_DIR}/connector_private.pem" -pubout -out "${KEYS_DIR}/connector_public.pem"
fi

chmod 600 ${KEYS_DIR}/*_private.pem || true
chmod 644 ${KEYS_DIR}/*_public.pem || true

echo "[+] Keys successfully generated in ${KEYS_DIR}."
echo "[+] Starting Docker Compose infrastructure..."
if command -v docker &> /dev/null; then
    docker compose up -d
    echo "[+] Waiting for database to be ready..."
    docker compose exec -T postgres pg_isready -U postgres -d ekaa
    echo "[+] Running migrations..."
    docker compose exec -T app php artisan migrate --force
    echo "[+] EKAA Installation Completed Successfully!"
else
    echo "[!] Docker not detected on PATH. Manifests generated; run 'docker compose up -d' when Docker is available."
fi
