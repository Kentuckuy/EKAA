# EKAA — Enterprise Knowledge and Analytics Assistant

[![CI](https://github.com/organization/ekaa/actions/workflows/ci.yml/badge.svg)](https://github.com/organization/ekaa/actions)
[![License](https://img.shields.io/badge/License-MIT%20%2F%20Apache--2.0-blue.svg)](LICENSE)

EKAA adalah platform asisten pengetahuan dan analitik privat untuk satu organisasi (*single-tenant*):
- **Knowledge Assistant (RAG):** Menjawab pertanyaan dokumen organisasi dengan sitasi yang dapat diverifikasi (dokumen, versi, bagian, halaman). Jika bukti tidak ditemukan, sistem secara eksplisit menyatakan `no_evidence`.
- **Analytics Assistant:** Menjawab pertanyaan kuantitatif menggunakan **fungsi metrik deterministik teruji** (`cagr`, `yoy`, `mom`, `moving_average`, `share`, `rank`, `aggregate`, `delta`). LLM tidak pernah menghitung angka akhir.
- **Hybrid Router:** Mengklasifikasikan niat pengguna ke jalur dokumen, analitik, atau klarifikasi.
- **Penegakan Hak Akses Berlapis:** Filter izin (`acl_principals`) diterapkan langsung pada kedua jalur retrieval (leksikal & dense), diikuti verifikasi Policy di Laravel sebelum respons dikirim ke pengguna. Target kebocoran hak akses = 0.

---

## 1. Arsitektur Teknologi (§12, §13)

| Layanan | Teknologi | Tanggung Jawab Utama |
|---|---|---|
| **Reverse Proxy** | Caddy 2.9 (TLS otomatis) | Gateway tunggal port 443; isolasi API internal |
| **Core Backend** | Laravel 13 (PHP 8.4) | Single Identity Provider, RBAC, Policy, Audit, Orchestrator |
| **Connector** | CodeIgniter 4.7 (PHP 8.4) | Bounded context Connector & Sync (MVP: Folder) |
| **AI Service** | Python 3.12 + FastAPI | RAG, Hybrid Router, Deterministic Metric Engine, Workers |
| **Frontend** | Vue 3 + TypeScript + Vite + Tailwind | SPA, ECharts visualizer |
| **Basis Data** | PostgreSQL 17 + pgvector 0.8.0 | 3 schema terisolasi (`core`, `connector`, `ai`) |
| **Tabular Engine** | DuckDB (read-only) | Pemrosesan analitik dataset CSV/XLSX |
| **Cache & Queue** | Valkey 8.0 (BSD-3-Clause) | Cache & Antrean Laravel (pengganti Redis) |
| **Object Storage** | SeaweedFS (S3 API, Apache-2.0) | Penyimpanan berkas dokumen & dataset privat |
| **Antivirus** | ClamAV 1.4 (clamd) | Pemindaian malware fail-closed sebelum ingestion |

---

## 2. Struktur Monorepo (§15.2)

```
ekaa/
  AGENTS.md                  # Pedoman kerja agen & invarian arsitektur
  README.md                  # Dokumentasi utama proyek
  Makefile                   # make up | down | test | ci | migrate | seed | eval | backup
  docker-compose.yml         # Konfigurasi container produksi/demo
  docker-compose.dev.yml     # Override pengembangan lokal
  .env.example               # Template variabel lingkungan
  apps/
    core/                    # Laravel 13 (app, queue-worker, scheduler)
    connector/               # CodeIgniter 4.7 (Connector & Sync)
    web/                     # Vue 3 SPA
  services/
    ai/                      # FastAPI (ai-api + ai-worker)
  contracts/
    openapi/                 # Kontrak OpenAPI spec-first
    schemas/                 # JSON Schemas (chart_spec, ask_request, ask_response)
  deploy/
    caddy/ postgres/ seaweedfs/ clamav/ scripts/
  eval/
    datasets/ protocols/ reports/
  tests/
    e2e/ security/ load/
  docs/
    spec/ adr/ runbook/ PROGRESS.md VERSIONS.md KNOWN_LIMITATIONS.md
```

---

## 3. Prasyarat & Pemasangan Cepat (§32.7)

### Prasyarat
- Docker Engine & Docker Compose v2
- Git
- Make (opsional, dapat menjalankan skrip shell langsung)

### Langkah Pemasangan:
1. **Salin konfigurasi lingkungan:**
   ```bash
   cp .env.example .env
   ```
2. **Jalankan inisialisasi dan kontainer:**
   ```bash
   ./deploy/scripts/install.sh
   # atau menggunakan Makefile:
   make up
   make migrate
   make seed
   ```
3. **Buka aplikasi:**
   - Akses antarmuka pengguna: `https://localhost`
   - Gateway API publik: `https://localhost/api/v1`
   - Dokumentasi internal AI (internal network): `http://ai-api:8000/internal/v1/docs`

---

## 4. Perintah Operasional Standar (`Makefile`)

- `make up` : Menjalankan seluruh 9 layanan kontainer.
- `make down` : Menghentikan seluruh kontainer.
- `make test` : Menjalankan pengujian unit dan integrasi.
- `make ci` : Menjalankan pipeline CI lokal (linting, contract check, database isolation test).
- `make eval` : Menjalankan evaluation harness RAG & Metrik pada golden dataset.
- `make backup` : Membuat backup terenkripsi (PostgreSQL, SeaweedFS, DuckDB).
- `make restore PATH_ARG=<dir>` : Menguji pemulihan cadangan ke lingkungan bersih.

---

## 5. Pedoman Pengembang & Kontribusi

- **Wajib Membaca:** `AGENTS.md` dan ledger kemajuan `docs/PROGRESS.md`.
- **Dilarang Keras:** Memasukkan kredensial/rahasia ke dalam repositori, menghapus schema isolation, atau mengubah logika deterministik perhitungan metrik ke LLM.
