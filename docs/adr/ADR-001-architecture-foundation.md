# ADR-001: Penetapan Fondasi Arsitektur, Isolasi Tiga Schema, dan Toolchain EKAA

| Atribut | Nilai |
|---|---|
| Status | **ACCEPTED (FINAL APPROVED BY PROJECT OWNER)** |
| Tanggal | 14 November 2026 |
| Pengambil Keputusan | Technical Architect, berdasarkan mandat Project Owner (§41) |
| Konsultasi | Dokumen Word EKAA-MPA-2.0 (DEC-001 s.d. DEC-050) |

---

## 1. Konteks

Proyek EKAA mengintegrasikan tiga ekosistem perangkat lunak yang berbeda:
1. Core Backend: **Laravel 13 (PHP 8.4)**
2. Connector & Sync: **CodeIgniter 4.7 (PHP 8.4)**
3. AI Service: **Python 3.12 (FastAPI)**
4. Frontend: **Vue 3 SPA**

Perlu ditetapkan tata kelola repositori, struktur isolasi basis data, protokol antarlayanan, dan penggantian komponen yang diarsipkan (MinIO CE) serta lisensi yang berisiko (Redis 8).

---

## 2. Keputusan yang Ditetapkan

1. **Tata Letak Monorepo (DEC-043):**
   - Menggunakan satu monorepo `ekaa/` dengan subdirektori independen `apps/core`, `apps/connector`, `apps/web`, dan `services/ai`.
2. **Isolasi Basis Data 3 Schema & 3 Role (DEC-007, AC-M0-03):**
   - Satu klaster PostgreSQL 17 + pgvector 0.8 dengan schema `core`, `connector`, `ai`.
   - Role `ekaa_core` hanya memiliki hak pada schema `core`.
   - Role `ekaa_connector` hanya memiliki hak pada schema `connector`.
   - Role `ekaa_ai` hanya memiliki hak pada schema `ai`.
   - Tanpa foreign key lintas schema; integritas dijaga oleh UUID referensi dan rekonsiliasi harian.
3. **Penyimpanan Berkas S3-Compatible via SeaweedFS (DEC-034):**
   - MinIO Community Edition telah diarsipkan dan tidak lagi menerima rilis keamanan.
   - Dipilih **SeaweedFS 3.85** (Apache-2.0) sebagai default object store melalui antarmuka S3 abstraction.
4. **Cache & Queue Laravel Menggunakan Valkey (DEC-035):**
   - Valkey 8.0 (BSD-3-Clause) di bawah Linux Foundation digunakan menggantikan Redis untuk menghindari kewajiban lisensi ganda RSALv2/SSPL/AGPL.
5. **Antrean AI Worker Menggunakan Tabel Database (DEC-032):**
   - Antrean pekerjaan AI worker menggunakan tabel `ai.jobs` dengan kueri `FOR UPDATE SKIP LOCKED`.
6. **Autentikasi Antarlayanan Asimetris (DEC-030, AC-M0-05):**
   - Menggunakan RS256 JWT dengan masa berlaku < 5 menit, klaim `jti` unik untuk anti-replay, dan pembatasan `aud` per layanan.
7. **Baseline Pencarian Leksikal (DEC-029):**
   - Menggunakan PostgreSQL Full-Text Search `to_tsvector('simple', content)` sebagai baseline stabil dan bebas risiko lisensi AGPL.

---

## 3. Konsekuensi & Kepatuhan

- Semua invarian kritis (§4) terjaga.
- Seluruh keputusan terdokumentasi dan teruji pada Milestone 0.
