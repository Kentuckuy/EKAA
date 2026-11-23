# Panduan Checklist Rilis Produksi (Production Deployment Checklist) — docs/runbook/deployment_checklist.md

| Atribut | Nilai |
|---|---|
| Sistem | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Klasifikasi | Standar Prosedur Operasional (§28, §29, AC-M8-06) |
| Versi | 1.0 (Rilis MVP) |
| Terakhir Diperbarui | 24 November 2026 |

---

## 1. Verifikasi Pra-Rilis (Pre-Deployment Gates)

Sebelum mempublikasikan versi rilis ke lingkungan produksi, operator wajib memeriksa checklist berikut:

- [x] **Audit Log Immutability (AC-M8-03):** Role `ekaa_core` dicabut hak `UPDATE`, `DELETE`, `TRUNCATE` pada tabel `core.audit_logs`. Trigger `trg_immutable_audit_logs` aktif.
- [x] **Zero Secret Leaks (AC-M8-05):** Pemindaian gitleaks / git log bebas dari kunci privat, token API, dan kata sandi asli. Berkas `.env` tidak ter-commit.
- [x] **Zero Cross-Access Leakage (INV-04):** Pengujian otorisasi dokumen dan dataset selalu mengembalikan `404 NOT FOUND` bagi pihak yang tidak berhak.
- [x] **Deterministic Analytics (INV-07):** Tidak ada jalur kode yang mengizinkan LLM menghasilkan angka akhir. Semua angka berasal dari `metric_engine.py`.
- [x] **ClamAV Antivirus Fail-Closed (AC-M2-01):** Jika daemon pemindai malware mati, sistem menolak unggahan secara *fail-closed* (`502 UPSTREAM_ERROR`).
- [x] **HTTP Security Headers (AC-M8-01):** Caddy reverse proxy dan Core middleware mengirimkan CSP ketat, HSTS, X-Frame-Options DENY, dan X-Content-Type-Options nosniff.
- [x] **Rate Limiting Aktif (AC-M8-02):** Login dibatasi maksimal 5 percobaan per menit per kombinasi IP + akun.

---

## 2. Langkah-Langkah Penerapan Produksi (Deployment Steps)

1. **Persiapan Kunci & Kredensial:**
   ```bash
   # Pastikan berkas .env terisi dengan nilai produksi aman
   cp .env.example .env
   # Generate JWT RSA keypairs jika belum ada
   ./deploy/scripts/generate_keys.sh
   ```

2. **Inisialisasi Volume & Jaringan:**
   ```bash
   docker compose build
   docker compose up -d postgres valkey seaweedfs
   ```

3. **Verifikasi Kesiapan Basis Data:**
   ```bash
   # Jalankan skrip inisialisasi skema dan ekstensi pgvector
   docker compose exec postgres psql -U ekaa_admin -d ekaa -f /docker-entrypoint-initdb.d/01-init-schemas-roles.sql
   docker compose exec postgres psql -U ekaa_admin -d ekaa -f /docker-entrypoint-initdb.d/02-base-tables.sql
   docker compose exec postgres psql -U ekaa_admin -d ekaa -f /docker-entrypoint-initdb.d/03-audit-immutability-trigger.sql
   ```

4. **Jalankan Layanan Aplikasi & AI:**
   ```bash
   docker compose up -d core ai connector web caddy
   ```

5. **Pengujian Kesehatan Layanan (Healthcheck):**
   ```bash
   ./deploy/scripts/healthcheck.sh
   ```
   Pastikan seluruh layanan (`caddy`, `core`, `ai`, `connector`, `postgres`, `valkey`, `seaweedfs`) berstatus `HEALTHY`.
