# FINAL TECHNOLOGY STACK — EKAA

| Atribut | Nilai |
|---|---|
| Versi dokumen | 1.0-draft (Gelombang 1) |
| Tanggal pembaruan | 8 November 2026 |
| Status dokumen | **READY FOR OWNER APPROVAL** |
| Dokumen terkait | `FINAL_DECISION_REGISTER.md` (DEC-002 s.d. DEC-018), `FINAL_REQUIREMENT_SPECIFICATION.md` |

## 1. Prinsip Pemilihan

Sederhana lebih dulu, tambahkan komponen hanya bila ada kebutuhan yang terukur. Setiap komponen yang ditunda dicatat di bagian 5 beserta pemicu untuk mengaktifkannya.

## 2. Keputusan Framework (Laravel, CodeIgniter, FastAPI)

Alternatif yang dianalisis: **A** Laravel + CodeIgniter sebagai aplikasi penuh terpisah + FastAPI; **B** Laravel inti + CodeIgniter untuk satu bounded context + FastAPI; **C** Laravel + FastAPI saja.

**Rekomendasi: B** (DEC-002). Fallback: C.

| Aspek | Laravel 13 | CodeIgniter 4.7.x | Python FastAPI |
|---|---|---|---|
| Peran dalam EKAA | Core backend dan pemilik identitas serta hak akses | Connector & Sync Service (bounded context terbatas) | AI service |
| Modul yang ditangani | Autentikasi, RBAC/ABAC, pengguna, peran, dokumen dan dataset (metadata), versi dokumen, percakapan, umpan balik, audit log, pengaturan sistem, API untuk frontend, orkestrasi permintaan ke layanan lain | Definisi sumber data eksternal, konektor (MVP: folder; Phase 2: Google Drive, SharePoint), jadwal sinkronisasi, deteksi perubahan berkas | Parsing, chunking, embedding, retrieval hibrida, reranking, generasi bersitasi, router, metric engine, impor dataset, guardrails, evaluasi |
| Keunggulan | Policy/Gate, queue, migrasi, pengujian, ekosistem matang | Ringan, cepat dibangun untuk service kecil, jejak memori kecil | Ekosistem ML dan NLP, async, validasi skema (Pydantic), OpenAPI otomatis |
| Keterbatasan | Lebih berat; harus menjaga batas dengan layanan lain | Ekosistem lebih kecil; menambah toolchain kedua | Perlu disiplin versi library ML; tidak mengelola identitas |
| Komunikasi antarlayanan | REST JSON ke CodeIgniter dan FastAPI; menerbitkan JWT layanan | REST ke Laravel (menyerahkan berkas/perubahan); tidak memanggil FastAPI langsung | REST; menerima permintaan dari Laravel; mengambil berkas dari MinIO |
| Keamanan | Satu-satunya titik autentikasi pengguna; menegakkan otorisasi dokumen; mengeluarkan filter akses ke FastAPI | Autentikasi layanan-ke-layanan (JWT); kredensial konektor terenkripsi; tidak punya sesi pengguna | Validasi JWT; menerapkan filter akses yang diterima; SQL hanya read-only; guardrails |
| Database ownership | Schema `core` (pemilik migrasi) | Schema `connector` (pemilik migrasi) | Schema `ai` (chunk, embedding, indeks) dan berkas DuckDB dataset |
| Deployment | Kontainer `app` + `queue-worker` + `scheduler` | Kontainer `connector` | Kontainer `ai-api` + `ai-worker` |
| Maintainability | Konvensi kuat, pengujian PHPUnit/Pest | Pengujian PHPUnit; kontrak API diuji bersama | pytest; OpenAPI sebagai kontrak |

**Larangan arsitektural yang ditetapkan:**

- Tidak ada business logic yang sama di dua framework.
- Tidak ada session sharing; autentikasi antarlayanan memakai JWT berumur pendek (satuan menit).
- Tidak ada tabel yang dimiliki dua layanan; tidak ada join lintas schema dari aplikasi.
- Migrasi hanya dijalankan oleh pemilik schema.
- CodeIgniter tidak membuat keputusan akses; ia hanya menyerahkan berkas dan metadata ke Laravel.

## 3. Verifikasi Versi

Diverifikasi lewat pencarian web pada 8 November 2026.

| Komponen | Temuan | Status verifikasi |
|---|---|---|
| Laravel | Versi mayor terkini 13, rilis 17 Maret 2026; PHP 8.3 sampai 8.5; perbaikan bug sampai Q3 2027 dan perbaikan keamanan sampai 17 Maret 2028. Laravel 14 diperkirakan Q1 2027 dengan syarat PHP 8.4. Laravel 12 tidak lagi menerima perbaikan bug sejak 13 Agustus 2026. | Terverifikasi (dokumentasi resmi dan sumber sekunder) |
| CodeIgniter 4 | Situs resmi menyebut versi 4.7.3 dan kebutuhan PHP 8.1+ (termasuk 8.4). | Terverifikasi pada situs resmi; **dukungan PHP 8.5 belum diverifikasi** |
| PHP | PHP 8.4 berada dalam rentang dukungan Laravel 13 (8.3–8.5) dan CodeIgniter 4.7 (8.1+ termasuk 8.4). Dipilih sebagai versi bersama. | Terverifikasi untuk kedua framework |
| Python, FastAPI, PostgreSQL, pgvector, Redis, DuckDB, MinIO, Vue, ECharts | Versi **belum diverifikasi** dalam dokumen ini | **Harus diverifikasi dan dikunci pada M0** |

Konsekuensi: Laravel 14 (Q1 2027) akan hadir saat pengembangan berjalan. Rekomendasi: **mulai dengan Laravel 13 dan jangan migrasi mayor selama MVP**, karena Laravel 13 mendapat dukungan keamanan sampai Maret 2028.

## 4. Stack per Lapisan

| Lapisan | Pilihan (rekomendasi) | Alasan | Alternatif | Risiko | Kebutuhan integrasi |
|---|---|---|---|---|---|
| Backend inti | Laravel 13, PHP 8.4 | DEC-003 | FastAPI sebagai inti (opsi konsep awal) | Beban jika semua fitur dimasukkan | Kontrak OpenAPI ke layanan lain |
| Layanan konektor | CodeIgniter 4.7.x | DEC-004 | Konektor di dalam Laravel (fallback C) | Beban dua toolchain | JWT layanan, kontrak REST ke Laravel |
| Layanan AI | Python + FastAPI | DEC-005 | Modul dalam Laravel (tidak disarankan) | Konflik versi library ML | MinIO, PostgreSQL schema `ai`, Redis |
| Frontend | Vue 3 + Vite, ECharts | DEC-012 | React; Vega-Lite | Dua ekosistem JS bila React dipilih nanti | API Laravel; token autentikasi |
| Basis data | PostgreSQL | DEC-007 | MySQL/MariaDB | Pemisahan schema harus ditegakkan | Role per layanan |
| Penyimpanan vektor | pgvector | DEC-008 | Qdrant | Skala korpus sangat besar | Indeks HNSW atau IVFFlat; filter hak akses |
| Analitik tabular | DuckDB (read-only) | DEC-009 | PostgreSQL langsung | Konkurensi tulis terbatas | Berkas dataset di MinIO |
| Cache dan antrean | Redis | DEC-010 | RabbitMQ, Celery | Redis menjadi titik kegagalan tunggal | Queue Laravel; worker Python |
| Penyimpanan berkas | MinIO | DEC-011 | Disk lokal | Operasional tambahan | Kredensial per layanan; presigned URL |
| LLM | Antarmuka yang dapat ditukar; API komersial dan opsi lokal (vLLM/Ollama) | DEC-013 | Satu penyedia tetap | Kualitas bahasa Indonesia; biaya; privasi | Konfigurasi per instalasi |
| Embedding dan reranker | Kandidat BGE-M3 / multilingual-e5; bge-reranker-v2-m3 | DEC-014 | Model berbayar | Kebutuhan GPU/CPU | Dievaluasi pada golden dataset |
| Autentikasi | Laravel (Sanctum atau setara), JWT antarlayanan | DEC-006 | Keycloak | Implementasi keamanan sendiri | Penerbitan dan validasi JWT |
| Visualisasi | ECharts dengan spesifikasi JSON tervalidasi skema | Angka hanya dari hasil query | Vega-Lite | Skema grafik harus dijaga | Validasi di FastAPI dan Laravel |
| Monitoring | Log terstruktur + correlation ID; Langfuse opsional (MVP) | DEC-016 | Prometheus, Grafana, OpenTelemetry | Visibilitas terbatas pada MVP | Format log seragam |
| Deployment | Docker Compose | DEC-015 | Kubernetes/Helm | Skala terbatas | Berkas `.env` per instalasi; manajemen rahasia |
| Pengujian | PHPUnit/Pest (Laravel), PHPUnit (CodeIgniter), pytest (FastAPI), Playwright (UI), k6 atau Locust (beban) | Sesuai `FINAL_REQUIREMENT_SPECIFICATION.md` bagian 7 | — | — | CI pada GitHub Actions |

## 5. Komponen yang Ditunda (DEFERRED) dan Pemicunya

| Komponen | Alasan ditunda | Pemicu pengaktifan |
|---|---|---|
| Keycloak (SSO) | Menduplikasi autentikasi di MVP | Klien membutuhkan SSO |
| Qdrant | pgvector cukup untuk skala menengah | Latensi atau recall pgvector tidak memenuhi target pada korpus besar yang terukur |
| RabbitMQ | Redis cukup | Kebutuhan routing pesan kompleks atau jaminan pengiriman yang tidak terpenuhi |
| Kubernetes/Helm | Belum ada kebutuhan skala | Klien dengan beban tinggi |
| Prometheus dan Grafana | Pemantauan MVP cukup lewat log | Phase 2 |
| LlamaIndex/LangChain | Mengurangi ketergantungan | Hanya bila komponen tertentu terbukti lebih baik daripada kode sendiri |

## 6. Prasyarat Sebelum Implementasi (Gerbang M0)

1. Owner menyetujui DEC-002, DEC-004, DEC-006, DEC-017.
2. Versi Python, FastAPI, PostgreSQL, pgvector, Redis, DuckDB, MinIO, Vue, dan ECharts diverifikasi dan dikunci.
3. Dukungan PHP 8.4 oleh seluruh paket pihak ketiga yang dipakai diperiksa.
4. Kontrak OpenAPI Laravel–CodeIgniter dan Laravel–FastAPI disepakati.

## 7. Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0-draft | 2026-11-08 | Penyusunan awal; verifikasi versi Laravel dan CodeIgniter |
