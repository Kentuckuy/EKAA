# FINAL DECISION REGISTER — EKAA

| Atribut | Nilai |
|---|---|
| Proyek | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Versi dokumen | 1.2-draft (Gelombang 1-2, persetujuan parsial) |
| Tanggal pembaruan | 8 November 2026 |
| Status dokumen | **READY FOR OWNER APPROVAL** — sebagian keputusan telah APPROVED (lihat bagian 3.1) |
| Dokumen terkait | `FINAL_TECHNOLOGY_STACK.md`, `FINAL_REQUIREMENT_SPECIFICATION.md` |

## 1. Tujuan dan Aturan Pembacaan

Dokumen ini mencatat seluruh keputusan penting EKAA agar dapat ditelusuri. Semua keputusan di bawah adalah **rekomendasi** yang disusun dari `EKAA.docx` dan instruksi Project Owner. Status APPROVED hanya diberikan setelah Project Owner menyatakannya secara eksplisit (dicatat di bagian 3.1).

Status yang dipakai: PROPOSED, RECOMMENDED, PENDING OWNER APPROVAL, APPROVED, REJECTED, DEFERRED.

Sumber yang tersedia saat penyusunan: `EKAA.docx`, teks Prompt 2, dan instruksi proyek. `PROJECT_CONTEXT.md` dan Decision Log sebelumnya **belum tersedia** dan tidak diasumsikan isinya.

## 2. Konflik dan Celah yang Ditemukan pada Dokumen Sumber

| ID | Temuan | Dampak | Penyelesaian yang diusulkan |
|---|---|---|---|
| CF-01 | `EKAA.docx` menyebut aplikasi inti "Laravel atau FastAPI" dan tidak menyebut CodeIgniter. Instruksi proyek mewajibkan CodeIgniter dianalisis. | Arsitektur tidak dapat ditetapkan tanpa keputusan ini. | DEC-002 s.d. DEC-004 |
| CF-02 | `EKAA.docx` mencantumkan Keycloak (SSO) di stack inti. | Dependency berat untuk MVP satu pengembang; berpotensi menduplikasi autentikasi. | DEC-006 (ditunda) |
| CF-03 | `EKAA.docx` menyebut "Celery atau RabbitMQ" sebagai antrean. | Dua opsi berbeda tingkat kompleksitas. | DEC-010 |
| CF-04 | Estimasi enam bulan untuk satu pengembang memuat OCR, konektor cloud, text-to-SQL, bot WhatsApp, anomali, dan peramalan sekaligus. | Risiko jadwal; bertentangan dengan arahan "jangan semua ide masuk MVP". | DEC-020 (pemangkasan MVP) |
| CF-05 | Target akurasi (Recall@10 > 0,9; faithfulness > 0,9) bersifat hipotesis. | Tidak boleh dipakai sebagai klaim sebelum baseline. | Ditetapkan sebagai target awal; divalidasi pada M1/M3 |
| CF-06 | Nama model (BGE-M3, bge-reranker-v2-m3, Qwen, dll.) hanya contoh di konsep awal. | Kualitas bahasa Indonesia belum teruji. | DEC-013, DEC-014 |

## 3. Register Keputusan

| Decision ID | Topik | Alternatif | Rekomendasi | Alasan | Status |
|---|---|---|---|---|---|
| DEC-001 | Model deployment | Single-tenant; multi-tenant | Single-tenant, satu instalasi per organisasi | Konsep inti EKAA; isolasi data sederhana dan kuat; selaras UU PDP. Konsekuensi: biaya dan pembaruan per instalasi. | PENDING OWNER APPROVAL |
| DEC-002 | Pola arsitektur framework | A: Laravel + CodeIgniter sebagai aplikasi penuh terpisah + FastAPI; B: Laravel inti + CodeIgniter untuk satu bounded context sempit + FastAPI; C: Laravel + FastAPI saja | **B** | Memenuhi keinginan Owner memakai kedua framework tanpa duplikasi business logic. A menggandakan auth, admin, dan migrasi. C paling sederhana secara teknis dan menjadi **alternatif cadangan** (lihat catatan 4.1). | APPROVED |
| DEC-003 | Peran Laravel | Inti; satu dari beberapa setara | Laravel 13 sebagai core backend: autentikasi, RBAC/ABAC, manajemen dokumen dan dataset (metadata), percakapan, audit log, panel admin API, orkestrasi ke layanan lain | Satu pemilik identitas dan hak akses; ekosistem queue, policy, migrasi matang. | APPROVED |
| DEC-004 | Peran CodeIgniter | Connector & Sync; Reporting/Export; Intake dokumen | **Connector & Sync Service** (CodeIgniter 4): konektor sumber data, penjadwalan sinkronisasi, deteksi perubahan, penyerahan berkas ke Laravel lewat API. MVP: satu konektor (folder jaringan/lokal). Konektor Google Drive dan SharePoint pada Phase 2. | Konteks terbatas, stateless terhadap hak akses, mudah dipisah, ringan. Tidak menyentuh autentikasi pengguna maupun business logic RAG. | APPROVED |
| DEC-005 | Layanan AI | Di dalam Laravel; FastAPI terpisah | Python FastAPI terpisah: parsing, chunking, embedding, retrieval, reranking, generasi, router, metric engine, guardrails | Ekosistem ML Python; isolasi beban komputasi. | PENDING OWNER APPROVAL |
| DEC-006 | Identitas dan SSO | Laravel sebagai satu-satunya identity provider; Keycloak; dua-duanya | Laravel sebagai identity provider tunggal. Layanan internal memvalidasi JWT berumur pendek dari Laravel. **Keycloak ditunda** ke Phase 3. | Mencegah duplikasi autentikasi dan session sharing. Keycloak menambah operasional berat. | APPROVED (Keycloak: DEFERRED) |
| DEC-007 | Basis data utama | Satu PostgreSQL dengan schema terpisah; database terpisah per layanan | Satu instans PostgreSQL, **schema per layanan** (`core`, `connector`, `ai`) dengan role database berprivilege minimum. Hanya pemilik schema yang menjalankan migrasi. | Kepemilikan data jelas tanpa beban operasional banyak instans. Tidak ada join lintas schema dari aplikasi. | PENDING OWNER APPROVAL |
| DEC-008 | Penyimpanan vektor | pgvector; Qdrant | **pgvector** untuk MVP. **Qdrant ditunda** hingga ada bukti beban korpus yang melampaui kemampuan pgvector. | Satu mesin untuk metadata, filter hak akses, dan vektor; filter sebelum pencarian lebih sederhana. | PENDING OWNER APPROVAL (Qdrant: DEFERRED) |
| DEC-009 | Mesin analitik | PostgreSQL; DuckDB | **DuckDB** untuk eksekusi pada dataset hasil impor (mode read-only). Text-to-SQL ditunda ke Phase 2; MVP memakai pustaka metrik teruji. | Isolasi eksekusi data pengguna dari database operasional; cocok untuk file tabular. | PENDING OWNER APPROVAL |
| DEC-010 | Cache dan antrean | Redis saja; Redis + RabbitMQ; Celery | **Redis** untuk cache dan antrean (antrean Laravel dan worker Python). **RabbitMQ ditunda.** | Satu komponen cukup untuk MVP. | PENDING OWNER APPROVAL (RabbitMQ: DEFERRED) |
| DEC-011 | Penyimpanan berkas | Disk lokal; MinIO/S3-compatible | MinIO (S3-compatible) | Portabel on-premise dan cloud; antarmuka standar. | PENDING OWNER APPROVAL |
| DEC-012 | Frontend | Vue; React; Blade/Livewire | Vue 3 (Vite) sebagai SPA terhadap API Laravel; ECharts untuk grafik | Cukup ringan, selaras ekosistem Laravel. React tetap alternatif sah. | PENDING OWNER APPROVAL |
| DEC-013 | Penyedia LLM | Komersial; lokal (vLLM/Ollama); keduanya | Antarmuka LLM yang dapat ditukar. MVP mendukung satu API komersial dan satu opsi lokal. **Pemilihan model ditentukan oleh evaluasi baseline**, bukan oleh asumsi. | Kebutuhan data tidak keluar bagi sebagian klien; kualitas bahasa Indonesia perlu diuji. | PENDING OWNER APPROVAL |
| DEC-014 | Embedding dan reranker | BGE-M3; multilingual-e5; reranker bge-reranker-v2-m3 | Kandidat diuji pada golden dataset (RQ1); pemenang dikunci di Decision Log | Hanya contoh pada konsep awal. | PROPOSED |
| DEC-015 | Deployment | Docker Compose; Kubernetes/Helm | Docker Compose untuk MVP dan Phase 2. **Helm ditunda.** | Sesuai skala klien awal. | PENDING OWNER APPROVAL (Helm: DEFERRED) |
| DEC-016 | Observabilitas | Log terstruktur; Prometheus/Grafana; Langfuse | MVP: log terstruktur dengan correlation ID, dan tracing LLM (Langfuse opsional). Prometheus/Grafana pada Phase 2. | Menjaga MVP ramping tetapi tetap dapat diaudit. | PENDING OWNER APPROVAL |
| DEC-017 | Versi platform | Laravel 12; Laravel 13; CodeIgniter 4.x; PHP 8.3 atau 8.4 | Laravel 13, CodeIgniter 4.7.x, PHP 8.4 untuk kedua aplikasi PHP. Versi minor dikunci lewat lockfile saat M0. | Lihat bukti verifikasi di `FINAL_TECHNOLOGY_STACK.md` bagian 3. | APPROVED |
| DEC-018 | Orkestrasi RAG | Kode sendiri; LlamaIndex; LangChain | Kode sendiri yang ringan; library hanya untuk komponen tertentu bila terbukti perlu | Mengurangi ketergantungan dan mempermudah eksperimen ilmiah yang direproduksi. | PENDING OWNER APPROVAL |
| DEC-019 | Model peran | Banyak peran; peran minimal | Org Admin, Knowledge Manager, Data Analyst, Standard User; Auditor pada Phase 2. Tanpa Super Admin dalam aplikasi (akun operator infrastruktur berada di luar aplikasi). | Setiap peran punya fungsi yang jelas. | PENDING OWNER APPROVAL |
| DEC-020 | Cakupan MVP | Semua fitur konsep awal; MVP ramping | MVP ramping (daftar di `FINAL_REQUIREMENT_SPECIFICATION.md` bagian 5). Bot WhatsApp, widget, anomali, peramalan, SSO, OCR lanjutan, dan konektor cloud berada di fase berikutnya. | Mengurangi risiko jadwal dan menjaga kualitas. | PENDING OWNER APPROVAL |
| DEC-021 | Router hibrida | Klasifikasi LLM few-shot; classifier ringan | MVP: tiga kelas (dokumen, analitik, di luar cakupan) dengan ambang keyakinan dan permintaan klarifikasi. Pertanyaan hibrida majemuk pada Phase 2. | Inti identitas EKAA tetap ada di MVP namun dibatasi. | PENDING OWNER APPROVAL |
| DEC-022 | Komunikasi antarlayanan | REST + OpenAPI; gRPC; message bus | REST (JSON) dengan OpenAPI, JWT antarlayanan, correlation ID, idempotency key untuk operasi tulis | Sederhana, mudah diuji dan didokumentasikan. | PENDING OWNER APPROVAL |
| DEC-023 | Pemisahan Track C | Satu dokumen; dokumen terpisah | Dokumen keputusan proyek dan klaim penelitian dipisah. Klaim hasil hanya setelah eksperimen. | Integritas akademik. | PENDING OWNER APPROVAL |
| DEC-024 | Reverse proxy | Caddy; Nginx; tanpa proxy | Caddy atau Nginx untuk TLS dan routing; hanya port 443 terbuka | Satu titik masuk; layanan internal tidak terekspos. Pilihan produk dikunci pada M0 | PROPOSED |
| DEC-025 | Sinkronisasi hak akses ke retrieval | Kirim daftar `document_id` per kueri; denormalisasi `acl_principals` pada chunk; AI membaca schema `core` | Denormalisasi `acl_principals` pada chunk; Laravel sumber kebenaran; pencabutan sinkron; verifikasi ulang sitasi di Laravel | Skala lebih baik daripada daftar ID; tidak melanggar kepemilikan schema. Risiko: ACL basi, dimitigasi pencabutan sinkron dan verifikasi lapis kedua | PROPOSED |
| DEC-026 | Kunci primer | bigint identity; UUIDv4; UUIDv7 | UUIDv7 dibangkitkan aplikasi | Referensi lintas layanan; urutan waktu baik untuk indeks | PROPOSED |
| DEC-027 | Tabel `organizations` | Dibuat; tidak dibuat | Tidak dibuat; profil di `system_settings` | Single-tenant tidak butuh `organization_id` di setiap tabel | PROPOSED |
| DEC-028 | Kontrak ingestion Laravel–AI | Antrean Redis dibagi lintas bahasa; HTTP 202 + callback | HTTP 202 + callback status | Menghindari kontrak format pesan antar-framework | PROPOSED |
| DEC-029 | Pencarian leksikal | FTS PostgreSQL `simple`; ekstensi BM25; mesin pencari terpisah | FTS PostgreSQL sebagai baseline; evaluasi ekstensi BM25 pada M0/M1 | FTS bawaan memakai `ts_rank`, bukan BM25 murni, dan tanpa konfigurasi bahasa Indonesia. Konsep awal menyebut BM25, sehingga perlu diuji (bahan RQ1) | PROPOSED |
| DEC-030 | JWT antarlayanan | Satu penerbit pusat; penerbit per layanan; mTLS | Tiap penerbit menandatangani JWT sendiri dengan kunci asimetris; `aud` spesifik; umur di bawah 5 menit | Tanpa rahasia bersama; mudah dirotasi | PROPOSED |

### 3.1 Catatan Persetujuan Project Owner

| Tanggal | Pernyataan Owner | Keputusan yang diubah menjadi APPROVED |
|---|---|---|
| 2026-11-08 | Menyetujui tiga pertanyaan keputusan: (1) pola B dan peran Connector & Sync untuk CodeIgniter; (2) Keycloak ditunda ke Phase 3; (3) Laravel 13 dan PHP 8.4 selama MVP tanpa migrasi ke Laravel 14. Pernyataan: "ya saya setuju semuanya". | DEC-002, DEC-003, DEC-004, DEC-006 (Keycloak tetap DEFERRED), DEC-017 |

Keputusan lain (DEC-001, DEC-005, DEC-007 s.d. DEC-016, DEC-018 s.d. DEC-023) tetap **PENDING OWNER APPROVAL** sampai Owner mengonfirmasinya secara eksplisit. Pertanyaan 4 (lingkungan demo) dan 5 (korpus golden dataset) belum dijawab.

## 4. Catatan Analisis Tambahan

### 4.1 Penilaian jujur atas penggunaan dua framework PHP

Secara teknis, **satu framework PHP (Laravel) lebih sederhana dipelihara** daripada dua. Penambahan CodeIgniter menaikkan biaya: dua toolchain, dua pipeline pengujian, dua pola dependensi, dan disiplin kontrak API. Rekomendasi B hanya layak jika:

1. CodeIgniter dibatasi pada Connector & Sync (DEC-004) dan tidak memegang autentikasi, hak akses, maupun logika RAG.
2. Kontrak API Laravel–CodeIgniter ditulis dan diuji lebih dulu (milestone M0).
3. Pemisahan schema dan role database ditegakkan (DEC-007).

Jika setelah M0 beban pemeliharaan terbukti tidak sebanding, **fallback C** (Laravel + FastAPI, konektor di Laravel) dapat diterapkan tanpa mengubah antarmuka layanan AI. Perubahan itu memerlukan persetujuan Owner.

Faktor non-teknis yang sah (pilihan Owner, nilai portofolio polyglot) dicatat sebagai pertimbangan, bukan sebagai pembenaran teknis.

### 4.2 Hal yang belum diputuskan dan membutuhkan jawaban Owner

1. Setuju dengan pola B dan peran Connector & Sync untuk CodeIgniter (DEC-002, DEC-004)?
2. Setuju Keycloak ditunda (DEC-006)?
3. Lingkungan deployment target demo (server lokal, VPS, atau cloud)? Ini memengaruhi pilihan LLM (DEC-013).
4. Domain korpus untuk golden dataset (misalnya dokumen kampus), karena memengaruhi studi kasus dan penelitian.

## 5. Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0-draft | 2026-11-08 | Penyusunan awal dari `EKAA.docx` dan instruksi proyek |
| 1.1-draft | 2026-11-08 | Persetujuan Owner atas DEC-002, DEC-003, DEC-004, DEC-006, DEC-017 |
| 1.2-draft | 2026-11-08 | Penambahan DEC-024 s.d. DEC-030 (PROPOSED) dari Gelombang 2; asumsi AS-06 s.d. AS-08 dicatat di `FINAL_SYSTEM_ARCHITECTURE.md` |
