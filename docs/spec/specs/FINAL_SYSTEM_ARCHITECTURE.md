# FINAL SYSTEM ARCHITECTURE — EKAA

| Atribut | Nilai |
|---|---|
| Versi dokumen | 1.0-draft (Gelombang 2) |
| Tanggal pembaruan | 8 November 2026 |
| Status dokumen | **READY FOR OWNER APPROVAL** |
| Landasan keputusan | DEC-001 s.d. DEC-023 (lihat `FINAL_DECISION_REGISTER.md`); keputusan tambahan DEC-024 s.d. DEC-030 berstatus PROPOSED |
| Dokumen terkait | `FINAL_TECHNOLOGY_STACK.md`, `FINAL_REQUIREMENT_SPECIFICATION.md`, `FINAL_DATABASE_DESIGN.md`, `FINAL_API_SPECIFICATION.md`, `FINAL_SECURITY_SPECIFICATION.md` |

## 1. Fakta, Asumsi, dan Batas Dokumen

**Fakta (dari keputusan Owner yang berstatus APPROVED):** Laravel 13 sebagai core backend dan identity provider tunggal; CodeIgniter 4.7.x sebagai Connector & Sync Service; Python FastAPI sebagai layanan AI; PHP 8.4; Keycloak ditunda.

**Asumsi baru (diberi label, belum dikonfirmasi Owner):**

| ID | Asumsi |
|---|---|
| AS-06 | Lingkungan demo: satu server lokal atau VPS dengan Docker Compose |
| AS-07 | Korpus golden dataset: dokumen kampus yang boleh dipublikasikan atau disintesis |
| AS-08 | Reverse proxy (Caddy atau Nginx) dipakai untuk TLS dan routing (DEC-024, PROPOSED) |

Dokumen ini mendefinisikan **rancangan**, bukan klaim bahwa sistem sudah dibangun atau diuji.

## 2. Prinsip Arsitektur

1. **Satu pemilik per fakta.** Identitas dan hak akses hanya dimiliki Laravel. Chunk dan vektor hanya dimiliki layanan AI. Konfigurasi dan status konektor hanya dimiliki CodeIgniter.
2. **Laravel adalah satu-satunya gerbang dari klien.** Browser tidak pernah memanggil CodeIgniter atau FastAPI.
3. **Pertahanan berlapis untuk akses data.** Filter akses diterapkan di retrieval (FastAPI) **dan** diverifikasi ulang oleh Laravel pada sitasi sebelum respons dikirim.
4. **LLM tidak menghitung angka.** Semua angka berasal dari fungsi metrik teruji atau hasil query tervalidasi.
5. **Kegagalan terisolasi.** Layanan AI atau konektor mati tidak menghentikan autentikasi, manajemen dokumen, dan audit.

## 3. System Context Diagram

```mermaid
flowchart LR
    SU["Standard User"]
    KM["Knowledge Manager"]
    DA["Data Analyst"]
    OA["Org Admin"]
    EKAA["EKAA<br/>Platform tunggal untuk satu organisasi"]
    LLM["Penyedia LLM<br/>API komersial atau model lokal"]
    SRC["Sumber berkas eksternal<br/>MVP: folder jaringan atau lokal<br/>Phase 2: Google Drive, SharePoint"]

    SU -->|"Bertanya, melihat sumber"| EKAA
    KM -->|"Kelola dokumen dan izin"| EKAA
    DA -->|"Impor dataset, kamus data"| EKAA
    OA -->|"Kelola pengguna dan konfigurasi"| EKAA
    EKAA -->|"Prompt dan konteks"| LLM
    EKAA -->|"Baca berkas, sinkronisasi"| SRC
```

## 4. Container Diagram

```mermaid
flowchart TB
    B["Browser<br/>Vue 3 SPA"]
    subgraph HOST["Server organisasi (Docker Compose)"]
        RP["Reverse proxy<br/>TLS"]
        subgraph LV["Laravel 13, PHP 8.4"]
            LA["app<br/>API publik dan internal"]
            LQ["queue-worker"]
            LS["scheduler"]
        end
        CI["CodeIgniter 4.7<br/>connector"]
        subgraph AI["Python FastAPI"]
            AA["ai-api"]
            AW["ai-worker"]
        end
        PG[("PostgreSQL<br/>schema core, connector, ai<br/>pgvector")]
        RD[("Redis")]
        MN[("MinIO")]
        DK[("Volume DuckDB<br/>berkas dataset")]
    end
    LLM["Penyedia LLM"]
    SRC["Sumber berkas eksternal"]

    B --> RP --> LA
    LA --> CI
    LA --> AA
    CI -->|"serahkan berkas"| LA
    AA -->|"callback status"| LA
    LA --- PG
    CI --- PG
    AA --- PG
    LA --- RD
    AA --- RD
    AW --- RD
    AW --- PG
    LA --- MN
    AA --- MN
    AW --- MN
    AW --- DK
    AA --- DK
    AA --> LLM
    AW --> LLM
    CI --> SRC
```

Catatan: `ai-api` dan `ai-worker` memakai basis kode yang sama dan peran proses berbeda. Panah putus-nyambung `---` berarti akses data sesuai batas kepemilikan schema di bagian 8, bukan akses bebas.

## 5. Application Architecture

| Aplikasi | Pemilik | Tanggung jawab | Tidak boleh |
|---|---|---|---|
| Vue 3 SPA | Frontend | UI chat, panel admin, grafik ECharts | Menyimpan rahasia; memanggil layanan selain Laravel |
| Laravel (`app`) | Core | Autentikasi, otorisasi, metadata dokumen dan dataset, percakapan, audit, orkestrasi, penegakan akses | Menjalankan embedding, retrieval, atau perhitungan metrik |
| Laravel (`queue-worker`, `scheduler`) | Core | Tugas latar: kirim ke AI, rekonsiliasi, retensi | — |
| CodeIgniter (`connector`) | Connector | Konfigurasi konektor, jadwal, deteksi perubahan, penyerahan berkas ke Laravel | Mengelola pengguna, peran, izin, atau menjawab pertanyaan |
| FastAPI (`ai-api`) | AI | Router, retrieval, rerank, generasi, metric engine, validasi grafik | Menyimpan identitas atau memutuskan izin |
| FastAPI (`ai-worker`) | AI | Parsing, chunking, embedding, impor dataset | — |

## 6. Backend Architecture (Laravel)

Struktur lapisan: **Controller (tipis) → Form Request (validasi) → Service/Action (business logic) → Repository/Eloquent → Policy/Gate (otorisasi)**. Controller tidak memuat business logic.

```mermaid
flowchart LR
    subgraph LAR["Komponen Laravel"]
        AUTH["Auth dan Token"]
        RBAC["Policy: RBAC, izin dokumen, izin dataset"]
        DOC["Document Service"]
        DS["Dataset Service"]
        CONV["Conversation Service"]
        ORCH["Orchestrator<br/>klien ke AI dan Connector"]
        AUD["Audit Service"]
        ADM["Admin dan Settings"]
        SYNC["ACL Sync Service"]
    end
    CONV --> ORCH
    DOC --> SYNC
    DS --> SYNC
    ORCH --> RBAC
    DOC --> AUD
    CONV --> AUD
    ADM --> AUD
```

Aturan implementasi: setiap endpoint melewati middleware autentikasi, policy, validasi, dan pencatatan audit untuk aksi yang bersifat sensitif. Kode error mengikuti `FINAL_API_SPECIFICATION.md`.

## 7. AI Service Architecture

```mermaid
flowchart LR
    subgraph AIC["Komponen FastAPI"]
        RT["Router Intent"]
        RET["Retriever<br/>leksikal + dense + RRF"]
        RR["Reranker"]
        GEN["Generator bersitasi"]
        GR["Guardrails"]
        MET["Metric Engine<br/>fungsi deterministik"]
        PLAN["Metric Planner<br/>function calling"]
        CH["Chart Spec Validator"]
        ING["Ingestion Pipeline<br/>parse, chunk, embed"]
        DSI["Dataset Importer<br/>DuckDB"]
        LLMC["LLM Client<br/>antarmuka tertukar"]
    end
    RT --> RET --> RR --> GEN
    RT --> PLAN --> MET --> CH
    GEN --> GR
    GEN --> LLMC
    PLAN --> LLMC
    RT --> LLMC
```

Antarmuka LLM (DEC-013) berupa kelas abstrak dengan implementasi per penyedia; pemilihan lewat konfigurasi instalasi. Setiap panggilan LLM mencatat model, versi prompt, dan parameter (NFR-AUD-001, NFR-REP-001).

## 8. Database Architecture (Ringkasan)

Satu PostgreSQL, tiga schema, tiga role. Rincian ada di `FINAL_DATABASE_DESIGN.md`.

| Schema | Pemilik | Isi |
|---|---|---|
| `core` | Laravel | Identitas, peran, dokumen, dataset (metadata), percakapan, audit, pengaturan |
| `connector` | CodeIgniter | Konfigurasi sumber, sinkronisasi, item sumber |
| `ai` | FastAPI | Chunk, embedding, status indeks, jejak retrieval, evaluasi |

Aplikasi tidak melakukan join lintas schema. Referensi lintas layanan memakai UUID tanpa foreign key dan dijaga oleh rekonsiliasi terjadwal.

## 9. Data Flow Diagram (Level 1)

```mermaid
flowchart LR
    U["Pengguna"] -->|"D1 pertanyaan"| LAV["Laravel"]
    LAV -->|"D2 konteks akses dan pertanyaan"| AIS["FastAPI"]
    AIS -->|"D3 kueri berfilter akses"| PGV[("PostgreSQL ai")]
    AIS -->|"D4 kueri dataset read-only"| DDB[("DuckDB")]
    AIS -->|"D5 prompt dengan konteks terpilih"| LLMX["LLM"]
    AIS -->|"D6 jawaban, sitasi, hasil metrik"| LAV
    LAV -->|"D7 verifikasi izin sitasi"| PGC[("PostgreSQL core")]
    LAV -->|"D8 jawaban terverifikasi"| U
    LAV -->|"D9 audit"| PGC
    CIX["CodeIgniter"] -->|"D10 berkas dan metadata"| LAV
    LAV -->|"D11 berkas"| MIO[("MinIO")]
    LAV -->|"D12 perintah ingestion"| AIS
    AIS -->|"D13 chunk dan vektor"| PGV
```

## 10. RAG Pipeline

### 10.1 Alur ingestion

```mermaid
sequenceDiagram
    participant KM as Knowledge Manager
    participant LV as Laravel
    participant MN as MinIO
    participant AI as FastAPI ai-api
    participant W as ai-worker
    participant PG as PostgreSQL ai

    KM->>LV: Unggah berkas
    LV->>LV: Validasi tipe, ukuran, MIME
    LV->>MN: Simpan berkas
    LV->>LV: Catat dokumen, versi, izin
    LV->>AI: POST ingestions (202 Accepted)
    AI->>W: Antrekan pekerjaan (Redis)
    W->>MN: Ambil berkas
    W->>W: Parsing dan chunking berbasis struktur
    W->>W: Embedding
    W->>PG: Simpan chunk, vektor, acl_principals
    W->>LV: Callback status (selesai atau gagal)
    LV->>LV: Perbarui status dokumen, audit
```

### 10.2 Alur kueri dokumen

```mermaid
flowchart TB
    Q["Pertanyaan + konteks akses"] --> RW["Penulisan ulang pertanyaan<br/>Phase 2"]
    RW --> RETR["Retrieval hibrida<br/>filter: acl, status, versi berlaku"]
    Q --> RETR
    RETR --> LEX["Leksikal"]
    RETR --> DEN["Dense (pgvector)"]
    LEX --> RRF["Reciprocal Rank Fusion"]
    DEN --> RRF
    RRF --> RRK["Reranker<br/>20 sampai 50 kandidat"]
    RRK --> ENOUGH{"Bukti cukup?"}
    ENOUGH -->|"Tidak"| ABS["Abstain: bukti tidak ditemukan"]
    ENOUGH -->|"Ya"| GEN2["Generasi bersitasi"]
    GEN2 --> VER["Verifikasi format sitasi<br/>entailment: Phase 2"]
    VER --> OUT["Jawaban + sitasi"]
    ABS --> OUT
```

Ketentuan: filter akses, status, dan versi berlaku diterapkan sebagai klausa `WHERE` pada kedua jalur pencarian. **Risiko yang harus diuji:** pencarian vektor perkiraan (ANN) dengan filter dapat mengembalikan lebih sedikit hasil dari yang diminta. Mitigasi (iterative scan pada versi pgvector yang mendukung, atau pencarian dengan kandidat lebih besar) diverifikasi pada M0 sesuai versi pgvector yang dikunci.

Pencarian leksikal pada PostgreSQL bawaan memakai `ts_rank`, **bukan BM25 murni**, dan tidak ada konfigurasi bahasa Indonesia bawaan. Lihat DEC-029.

## 11. Analytics Pipeline

```mermaid
flowchart TB
    Q2["Pertanyaan angka + daftar dataset yang diizinkan"] --> PL["Metric Planner (LLM function calling)"]
    PL --> V1{"Metrik dan parameter valid<br/>terhadap skema dan kamus data?"}
    V1 -->|"Tidak"| CLR["Minta klarifikasi atau tolak"]
    V1 -->|"Ya"| ME["Metric Engine<br/>fungsi deterministik teruji"]
    ME --> DDB2[("DuckDB read-only")]
    ME --> RES["Hasil: nilai, rumus, query, sumber"]
    RES --> CS["LLM menyusun spesifikasi grafik JSON"]
    CS --> V2{"Valid terhadap skema JSON<br/>dan nilai identik dengan hasil?"}
    V2 -->|"Tidak"| ERR["Tolak spesifikasi; tampilkan tabel"]
    V2 -->|"Ya"| OUT2["Tabel, grafik, rumus"]
    CLR --> OUT2
    ERR --> OUT2
```

Text-to-SQL (FR-040) **tidak ada di MVP**. Pada Phase 2 ia menjadi jalur cadangan dengan validasi parser (hanya `SELECT`), eksekusi read-only, batas waktu, dan koreksi diri.

## 12. Hybrid Routing

```mermaid
flowchart TB
    IN["Pertanyaan"] --> CLS["Klasifikasi niat + skor keyakinan"]
    CLS --> TH{"Keyakinan >= ambang?"}
    TH -->|"Tidak"| ASK["Minta klarifikasi pengguna"]
    TH -->|"Ya"| ROUTE{"Kelas"}
    ROUTE -->|"Dokumen"| DOC["Jalur RAG"]
    ROUTE -->|"Analitik"| ANA["Jalur Metric Engine"]
    ROUTE -->|"Hibrida (Phase 2)"| DEC["Dekomposisi lalu jalankan kedua jalur"]
    ROUTE -->|"Di luar cakupan"| OOS["Tolak dengan penjelasan"]
    DEC --> DOC
    DEC --> ANA
    DOC --> MERGE["Gabung hasil + sumber"]
    ANA --> MERGE
```

Pada MVP hanya tiga kelas (dokumen, analitik, di luar cakupan). Ambang keyakinan ditetapkan dari baseline pada golden dataset, bukan angka tebakan.

## 13. Authentication and Authorization

- **Pengguna → Laravel:** kredensial; kata sandi di-hash dengan algoritma yang kuat (Argon2id); token akses berumur pendek dan token penyegar (refresh) yang dapat dicabut; pembatasan percobaan login.
- **Laravel → CodeIgniter/FastAPI:** JWT layanan berumur pendek (DEC-030). Konteks pengguna dikirim sebagai klaim `principals`, bukan kredensial pengguna.
- **CodeIgniter → Laravel:** JWT layanan yang ditandatangani CodeIgniter sendiri.
- **Otorisasi dokumen:** sumber kebenaran adalah `core.document_permissions`. Salinan denormalisasi `acl_principals` pada chunk di schema `ai` disinkronkan oleh Laravel (DEC-025). Pencabutan izin diproses **sinkron sebelum Laravel mengembalikan sukses ke pengguna**; pemberian izin boleh asinkron.
- **Pertahanan berlapis:** sebelum jawaban dikirim, Laravel memeriksa setiap `document_id` pada sitasi terhadap Policy. Sitasi yang tidak lolos dibuang dan kejadian dicatat sebagai anomali keamanan.

```mermaid
sequenceDiagram
    participant U as Pengguna
    participant LV as Laravel
    participant AI as ai-api
    U->>LV: Pertanyaan (token akses)
    LV->>LV: Autentikasi, muat peran dan unit
    LV->>LV: Bentuk principals dan allowed_dataset_ids
    LV->>AI: Ask (JWT layanan, principals)
    AI->>AI: Retrieval berfilter, generasi
    AI-->>LV: Jawaban, sitasi, hasil metrik
    LV->>LV: Verifikasi izin tiap sitasi
    LV->>LV: Audit
    LV-->>U: Jawaban terverifikasi
```

## 14. File Processing

| Tahap | Pemilik | Kontrol |
|---|---|---|
| Penerimaan | Laravel | Batas ukuran, daftar tipe yang diizinkan, cek MIME dan magic bytes di server, nama berkas dinormalisasi, penyimpanan di bucket privat |
| Pemindaian | Laravel | Pemindaian malware (kandidat ClamAV; **keputusan komponen dan versi diverifikasi pada M0**) |
| Parsing | ai-worker | Berjalan terisolasi, batas waktu dan memori; tidak mengeksekusi makro; PDF berteks, DOCX, XLSX, CSV pada MVP |
| Chunking | ai-worker | Berbasis struktur (judul, pasal, ayat) dengan overlap; metadata halaman dan bagian wajib |
| Indeks | ai-worker | Chunk dengan `acl_principals`, `status`, `is_current` |
| Dataset | ai-worker | Tipe kolom divalidasi; disimpan di DuckDB; laporan baris galat |
| Konektor | CodeIgniter | Hash isi untuk deteksi perubahan; kunci idempoten; penyerahan ke Laravel melalui API |

Isi dokumen diperlakukan sebagai **data tidak tepercaya** (lihat `FINAL_SECURITY_SPECIFICATION.md` bagian 7).

## 15. Security Architecture (Ringkasan)

Zona jaringan: **publik** (hanya reverse proxy), **aplikasi** (Laravel, CodeIgniter, FastAPI), **data** (PostgreSQL, Redis, MinIO). Hanya reverse proxy yang menerima trafik dari luar; layanan lain berada di jaringan internal Docker. Detail kontrol, threat model, dan UU PDP ada di `FINAL_SECURITY_SPECIFICATION.md`.

## 16. Deployment Architecture

```mermaid
flowchart TB
    INET["Internet atau jaringan organisasi"] --> FW["Firewall: hanya port 443"]
    FW --> RP2["Reverse proxy"]
    subgraph NET1["Jaringan aplikasi (internal)"]
        APP["laravel-app"]
        QW["laravel-queue"]
        SCH["laravel-scheduler"]
        CONN["connector"]
        AIA["ai-api"]
        AIW["ai-worker"]
    end
    subgraph NET2["Jaringan data (internal)"]
        PG2[("PostgreSQL")]
        RD2[("Redis")]
        MN2[("MinIO")]
        VOL[("Volume DuckDB")]
    end
    RP2 --> APP
    APP --> CONN
    APP --> AIA
    NET1 --> NET2
    AIA --> EXTL["LLM eksternal (egress dibatasi)<br/>atau LLM lokal pada jaringan sama"]
    CONN --> EXTS["Sumber berkas eksternal"]
```

Ketentuan deployment (DEC-015): Docker Compose; konfigurasi per instalasi melalui berkas `.env` dan rahasia terpisah; migrasi dijalankan per layanan oleh pemiliknya; versi image dikunci; egress keluar hanya untuk LLM dan sumber konektor yang dikonfigurasi. Opsi LLM lokal memerlukan perangkat keras yang belum ditentukan (bergantung DEC-013 dan lingkungan demo).

## 17. Ketergantungan dan Keputusan Baru yang Diusulkan

| ID | Topik | Rekomendasi | Status |
|---|---|---|---|
| DEC-024 | Reverse proxy | Caddy atau Nginx untuk TLS dan routing | PROPOSED |
| DEC-025 | Sinkronisasi hak akses ke retrieval | Denormalisasi `acl_principals` pada chunk; pencabutan sinkron | PROPOSED |
| DEC-026 | Kunci primer | UUIDv7 dibangkitkan aplikasi | PROPOSED |
| DEC-027 | Tabel `organizations` | Tidak dibuat (single-tenant); profil organisasi di `system_settings` | PROPOSED |
| DEC-028 | Kontrak ingestion | HTTP 202 ke AI, status kembali lewat callback ke Laravel; bukan antrean yang dibagi lintas bahasa | PROPOSED |
| DEC-029 | Pencarian leksikal | PostgreSQL FTS (konfigurasi `simple`) sebagai baseline; ekstensi BM25 dievaluasi pada M0 atau M1 | PROPOSED |
| DEC-030 | JWT antarlayanan | Penerbit menandatangani JWT sendiri dengan kunci asimetris; penerima menyimpan kunci publik; `aud` dan masa berlaku di bawah 5 menit | PROPOSED |

## 18. Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0-draft | 2026-11-08 | Penyusunan awal Gelombang 2 |
