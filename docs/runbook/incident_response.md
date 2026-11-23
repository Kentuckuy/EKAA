# Prosedur Respons Insiden (Incident Response Runbook) — docs/runbook/incident_response.md

| Atribut | Nilai |
|---|---|
| Sistem | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Klasifikasi | Operasional & Keamanan (§28, §30, AC-M8-06) |
| Versi | 1.0 (Rilis MVP) |
| Terakhir Diperbarui | 24 November 2026 |

---

## 1. Klasifikasi Tingkat Keparahan (Severity Levels)

| Level | Kriteria | Waktu Respons Maksimal | Tindakan Awal |
|---|---|---|---|
| **P0 (Kritis)** | Kebocoran lintas hak akses dokumen/dataset, kebocoran Service JWT, basis data offline. | < 15 menit | Putus akses eksternal di reverse proxy, cabut Service JWT di Valkey, isolasi kontainer. |
| **P1 (Tinggi)** | Layanan AI tidak tersedia (503), antrean ingestion macet, serangan brute-force login terdeteksi. | < 1 jam | Aktifkan fallback Core fail-closed, periksa log antrean worker AI, blokir IP penyerang di firewall. |
| **P2 (Sedang)** | Penurunan performa RAG (> 5 detik p95), galat parsial sinkronisasi konektor. | < 4 jam | Periksa alokasi RAM/VRAM model embedding, jalankan ulang sinkronisasi parsial. |
| **P3 (Rendah)** | Umpan balik negatif pengguna meningkat pada dokumen tertentu. | < 24 jam | Evaluasi kelengkapan chunk dokumen dan perbarui metadata. |

---

## 2. Prosedur Darurat Kebocoran Kredensial / Token (P0)

Jika kunci privat Service JWT atau token otentikasi terindikasi bocor:
1. **Cabut Kunci Segera:**
   ```bash
   # Hapus ID kunci yang terkompromi dari cache validasi Valkey
   valkey-cli DEL "security:keys:connector-service-key-1"
   ```
2. **Terbitkan Pasangan Kunci Baru:**
   ```bash
   openssl genrsa -out /secrets/jwt/service_private_new.pem 2048
   openssl rsa -in /secrets/jwt/service_private_new.pem -pubout -out /secrets/jwt/service_public_new.pem
   ```
3. **Perbarui Variabel Lingkungan & Muat Ulang Layanan:**
   ```bash
   docker compose restart core ai connector
   ```

---

## 3. Prosedur Darurat Pemulihan Basis Data (Disaster Recovery)

Jika basis data PostgreSQL mengalami kerusakan data atau kegagalan perangkat keras:
1. **Hentikan Layanan Tulis:**
   ```bash
   docker compose stop core ai connector
   ```
2. **Eksekusi Skrip Pemulihan Snapshot:**
   ```bash
   # Jalankan skrip restore terverifikasi (AC-M8-04)
   ./deploy/scripts/restore.sh /backups/postgres/latest_verified_backup.sql.gz
   ```
3. **Verifikasi Integritas:**
   - Periksa keutuhan schema `core`, `connector`, `ai`.
   - Pastikan pemicu `trg_immutable_audit_logs` aktif.
   - Uji coba query dokumen dan login administrator.
4. **Mulai Ulang Seluruh Layanan:**
   ```bash
   docker compose up -d
   ./deploy/scripts/healthcheck.sh
   ```
