# AGENTS.md — Pedoman Kerja AI Coding Agent (EKAA)

> [!IMPORTANT]
> **BACA `docs/PROGRESS.md` SEBELUM BEKERJA.**
> Setiap sesi baru WAJIB membaca `AGENTS.md`, `docs/PROGRESS.md`, ADR terbaru di `docs/adr/`, dan bagian Dokumen Word untuk fase aktif, serta memverifikasi status nyata repositori sebelum mempercayai ledger.

---

## 1. Sumber Kebenaran dan Hierarki Keputusan

1. **Instruksi terbaru Project Owner** adalah sumber kebenaran tertinggi.
2. **Dokumen Word (`docs/spec/EKAA_MASTER_PROJECT_AGREEMENT.docx` / `docs/spec/EKAA_MASTER_PROJECT_AGREEMENT.extracted.md`)**: mencakup Decision Register §40 (DEC-001 s.d. DEC-050) berstatus FINAL APPROVED BY PROJECT OWNER.
3. **ADR (Architectural Decision Records)** di `docs/adr/`.
4. **Master Prompt (`docs/spec/ANTIGRAVITY_MASTER_PROMPT.md`)**.
5. **Asumsi berlabel** (diberi tag `ASUMSI` pada `docs/PROGRESS.md`).

---

## 2. Invarian Kritis (Dilarang Dilanggar)

1. **Framework Final:**
   - Core backend & Single Identity Provider: **Laravel 13 (PHP 8.4)**
   - Connector & Sync Service: **CodeIgniter 4.7.x (PHP 8.4)** (hanya bounded context ini)
   - Layanan AI: **Python + FastAPI**
   - Frontend: **Vue 3 SPA + Vite + Tailwind + ECharts**
2. **Satu Pemilik per Fakta & Isolasi Schema:**
   - `core` schema (dan migrasi) dimiliki Laravel.
   - `connector` schema (dan migrasi) dimiliki CodeIgniter.
   - `ai` schema (dan migrasi) dimiliki FastAPI (Alembic) + DuckDB (read-only).
   - Tidak ada tabel yang dimiliki dua layanan; tidak ada join lintas schema dari aplikasi.
3. **Browser Hanya Berbicara dengan Laravel:**
   - Semua endpoint internal `/internal/v1` tidak dipublikasikan ke reverse proxy.
4. **Tanpa Session Sharing:**
   - Komunikasi antarlayanan menggunakan asimetris JWT (< 5 menit) dengan klaim `iss`, `aud`, `sub`/`act`, `jti`, `exp`, `scope`.
5. **Penegakan Hak Akses Berlapis (Zero Cross-Access Leak):**
   - Laravel membentuk `principals` (`all`, `user:<id>`, `role:<id>`, `unit:<id>` dan seluruh unit induk).
   - FastAPI memfilter retrieval dengan `acl_principals && :principals AND status='active' AND is_current` pada kedua jalur (leksikal & dense).
   - Laravel memverifikasi Policy untuk setiap `document_id` pada sitasi sebelum mengirim respons ke klien.
   - Kebocoran lintas hak akses = 0 pada seluruh uji.
6. **Pencabutan Izin Sinkron:**
   - Pencabutan izin/status dicabut disinkronkan ke AI secara sinkron sebelum mengembalikan respon berhasil ke pengguna.
7. **LLM Tidak Pernah Menghitung Angka Akhir:**
   - Seluruh angka berasal dari fungsi metrik deterministik teruji (`cagr`, `yoy`, `mom`, `moving_average`, `share`, `rank`, `aggregate`, `delta`).
   - SQL metrik dibangun dari parameterized template dengan identifier whitelist. Text-to-SQL adalah Phase 2.
8. **RAG Berbasis Sitasi Nyata & Abstain:**
   - Sitasi wajib menyertakan dokumen, versi, bagian, dan halaman.
   - Jika bukti lemah, abstain dengan status `no_evidence`.
   - Chunk teks identik dengan sumber (hanya normalisasi spasi).
9. **Penyimpanan Berkas & Komponen Infrastruktur:**
   - Object Store: S3-compatible via abstraksi, default **SeaweedFS** (MinIO CE dilarang).
   - Cache / Queue Laravel: **Valkey** (bukan Redis).
   - AI Worker Queue: PostgreSQL table `ai.jobs` dengan `FOR UPDATE SKIP LOCKED`.
   - Reverse Proxy: **Caddy** (TLS otomatis, hanya port 443 dan 80 redirect).
   - Malware Scanner: **ClamAV (clamd)** fail-closed.
10. **Model Token SPA:**
    - Access token opaque (~15 menit) **hanya disimpan di memori**.
    - Refresh token berputar disimpan di cookie `HttpOnly; Secure; SameSite=Strict; Path=/api/v1/auth`.
    - Dilarang menyimpan token di `localStorage` atau `sessionStorage`.
11. **Spesifikasi & Keamanan:**
    - Kunci primer: **UUIDv7** dibangkitkan aplikasi.
    - Timestamp: `timestamptz` (UTC).
    - Constraint unik kolom nullable memakai `NULLS NOT DISTINCT`.
    - Tanpa tabel `organizations` (single-tenant).
    - Tanpa peran Super Admin di dalam aplikasi.
    - Output model disanitasi (DOMPurify).
    - Tidak ada rahasia di repositori.
    - Lisensi: PyMuPDF (AGPL) dilarang.
    - Docker-first: Seluruh layanan berjalan via Docker Compose.

---

## 3. Perintah Standar

Semua perintah standar dipusatkan pada `Makefile` monorepo:
- `make up`: Menjalankan seluruh kontainer layanan.
- `make down`: Menghentikan seluruh kontainer.
- `make migrate`: Menjalankan migrasi database masing-masing schema.
- `make seed`: Mengisi seed data awal (roles, default settings, test users).
- `make test`: Menjalankan seluruh pengujian unit dan integrasi.
- `make ci`: Menjalankan seluruh pipeline CI lokal (lint, analisis statis, uji, gitleaks, audit).
- `make eval`: Menjalankan evaluation harness RAG & Metrik (`python -m ekaa_ai.eval`).
- `make backup`: Menjalankan backup terenkripsi (PostgreSQL, SeaweedFS, DuckDB).
- `make restore`: Menjalankan uji restore cadangan ke lingkungan bersih.

---

## 4. Definition of Done (DoD) Global

1. Kode tersimpan di repositori dengan commit atomic dan pesan deskriptif.
2. Berjalan di Docker Compose.
3. Terintegrasi dengan backend nyata (bukan mock UI), kecuali jika berstatus `STUB` terdaftar.
4. Uji otomatis relevan lulus 100%.
5. Validasi, otorisasi, penanganan error (`correlation_id`), dan audit log diterapkan.
6. Migrasi database memiliki `up` dan `down` yang teruji.
7. OpenAPI spec dan dokumentasi diperbarui; ADR dibuat bila ada keputusan baru.
8. Pemindaian rahasia (`gitleaks`) dan audit dependensi bersih.
9. Bukti nyata (log/laporan) tercatat di `docs/PROGRESS.md` dan disimpan di `tests/reports/` atau `eval/reports/`.
