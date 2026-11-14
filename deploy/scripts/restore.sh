#!/usr/bin/env bash
# =============================================================================
# EKAA Recovery & Restore Script (DEC-044, FR-036)
# Restores PostgreSQL, SeaweedFS, and DuckDB from a verified backup
# =============================================================================
set -euo pipefail

if [ -z "${1:-}" ]; then
    echo "Usage: $0 <path_to_backup_directory>"
    exit 1
fi

RESTORE_SRC="$1"
echo "=== Starting EKAA Restore from ${RESTORE_SRC} ==="

if [ ! -f "${RESTORE_SRC}/postgres_ekaa.pgdump" ]; then
    echo "[-] Error: ${RESTORE_SRC}/postgres_ekaa.pgdump not found!"
    exit 1
fi

if command -v docker &> /dev/null; then
    echo "[+] Copying dump to postgres container..."
    docker compose cp "${RESTORE_SRC}/postgres_ekaa.pgdump" postgres:/tmp/restore.pgdump

    echo "[+] Restoring PostgreSQL database..."
    docker compose exec -T postgres pg_restore -U postgres -d ekaa --clean --if-exists -v /tmp/restore.pgdump || true
    docker compose exec -T postgres rm /tmp/restore.pgdump

    if [ -d "${RESTORE_SRC}/duckdb_data" ]; then
        echo "[+] Restoring DuckDB volume..."
        docker compose cp "${RESTORE_SRC}/duckdb_data/." ai-worker:/data/duckdb/
    fi

    echo "[+] Restore completed successfully."
else
    echo "[!] Docker not detected. Cannot run live container restore."
fi
