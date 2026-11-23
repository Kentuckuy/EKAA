# FINAL PROJECT AGREEMENT — ENTERPRISE KNOWLEDGE AND ANALYTICS ASSISTANT (EKAA)

| Atribut | Nilai |
|---|---|
| Versi kesepakatan | 1.0 |
| Tanggal penyusunan | 8 November 2026 |
| **Status dokumen** | **READY FOR OWNER APPROVAL** |
| Tujuan | Menjadi acuan tunggal proyek bagi Project Owner, Claude AI, Antigravity AI Coding Agent, pengembang, dan peneliti |
| Sumber | `EKAA.docx`, instruksi proyek, Prompt 2, dan keputusan Owner yang tercatat pada `FINAL_DECISION_REGISTER.md` |

> **Pernyataan status.** Dokumen ini **belum disetujui** sebagai kesepakatan resmi. Beberapa keputusan individual telah disetujui Owner dan tercatat di Decision Register (bagian 7), tetapi persetujuan atas kesepakatan ini secara keseluruhan baru berlaku setelah Owner menyatakannya secara eksplisit pada halaman persetujuan (bagian 15). Sampai saat itu dokumen ini bukan dasar implementasi yang mengikat.

## 1. Apa Itu EKAA

EKAA adalah **asisten pengetahuan dan analitik privat** untuk satu institusi atau perusahaan (single-tenant, DEC-001). Pengguna menulis pertanyaan dalam bahasa sehari-hari; sistem menjawab dengan sumber yang dapat diperiksa, menghitung metrik angka dengan fungsi deterministik teruji, dan menampilkan hasilnya sebagai teks, tabel, atau grafik.

Dua kemampuan inti dihubungkan oleh Hybrid Router:

- **Knowledge Assistant (RAG):** tanya jawab dokumen dengan sitasi, kemampuan menyatakan bukti tidak ditemukan, dan kontrol akses per pengguna.
- **Analytics Assistant:** impor data tabular, kamus data, metrik deterministik, dan grafik dari data tervalidasi.

## 2. Masalah yang Diselesaikan

1. Informasi organisasi tersebar sehingga pencarian memakan waktu.
2. Layanan AI publik berisiko bagi data internal dan kepatuhan.
3. LLM tidak andal untuk aritmetika dan dapat mengarang jawaban.
4. Dashboard BI statis dan memerlukan analis untuk setiap pertanyaan baru.

## 3. Pengguna dan Peran

Org Admin, Knowledge Manager, Data Analyst, Standard User, dan Auditor (Phase 2) (DEC-019). Tidak ada Super Administrator dalam aplikasi; akses infrastruktur berada di luar aplikasi. Rincian hak akses: `FINAL_REQUIREMENT_SPECIFICATION.md` bagian 3.

## 4. Tujuan Produk

1. Satu pintu akses untuk dokumen dan data angka organisasi.
2. Setiap jawaban dapat diverifikasi: sitasi untuk teks, rumus dan sumber untuk angka.
3. Data tetap dalam kendali organisasi, termasuk opsi LLM lokal.
4. Mudah dipasang, dipelihara, dan dikonfigurasi.
5. Menghasilkan evaluasi empiris yang layak menjadi bahan publikasi ilmiah.

## 5. Cakupan

| Kelompok | Isi | Rujukan |
|---|---|---|
| **MVP (Must Have)** | Identitas dan peran, audit; manajemen dokumen, versi, dan izin; ingestion dan indeks (PDF berteks, DOCX, XLSX, CSV); retrieval hibrida berfilter akses dengan reranking; jawaban bersitasi dan abstain; chat; impor dataset, kamus data, metrik deterministik, grafik; Hybrid Router tiga kelas; guardrails dasar; umpan balik; konektor folder (CodeIgniter) | FR-001–006, 010–019, 020–025, 030–033 |
| **Phase 2** | Text-to-SQL read-only; pertanyaan hibrida majemuk; OCR dan tabel PDF; verifikasi sitasi dan angka; memori percakapan; konektor Google Drive/SharePoint; row-level security; peran Auditor; cache semantik; Prometheus/Grafana | FR-040–049 |
| **Phase 3** | SSO (Keycloak/OIDC); anomali dan peramalan; widget dan bot WhatsApp; domain pack; provisioning otomatis dan Helm | FR-050–054 |
| **Out of Scope** | Multi-tenant dalam satu instalasi; fine-tuning model sendiri; aplikasi mobile native; write-back ke database sumber | FR spec bagian 5 |

## 6. Arsitektur dan Teknologi (Ringkasan)

| Aspek | Keputusan | Status |
|---|---|---|
| Pola framework | Laravel inti + CodeIgniter untuk satu bounded context + FastAPI (DEC-002) | APPROVED |
| Laravel 13 | Core backend, identity provider tunggal, pemilik izin, audit, orkestrasi (DEC-003) | APPROVED |
| CodeIgniter 4.7.x | Connector & Sync Service; tidak memegang autentikasi, izin, atau logika RAG (DEC-004) | APPROVED |
| FastAPI | Layanan AI: parsing, embedding, retrieval, rerank, generasi, router, metric engine, guardrails (DEC-005) | PENDING OWNER APPROVAL |
| Autentikasi | Laravel satu-satunya IdP; Keycloak ditunda ke Phase 3 (DEC-006) | APPROVED (Keycloak: DEFERRED) |
| Versi platform | Laravel 13, CodeIgniter 4.7.x, PHP 8.4 (DEC-017) | APPROVED |
| Database | PostgreSQL satu instans, schema per layanan (DEC-007) | PENDING OWNER APPROVAL |
| Vektor | pgvector; Qdrant ditunda (DEC-008) | PENDING OWNER APPROVAL |
| Analitik | DuckDB read-only; text-to-SQL di Phase 2 (DEC-009) | PENDING OWNER APPROVAL |
| Cache dan antrean | Redis; RabbitMQ ditunda (DEC-010) | PENDING OWNER APPROVAL |
| Berkas | MinIO (DEC-011) | PENDING OWNER APPROVAL |
| Frontend | Vue 3 + ECharts (DEC-012) | PENDING OWNER APPROVAL |
| LLM, embedding, reranker | Antarmuka tertukar; pemilihan berdasarkan evaluasi (DEC-013, DEC-014) | PENDING OWNER APPROVAL / PROPOSED |
| Deployment | Docker Compose (DEC-015) | PENDING OWNER APPROVAL |
| Reverse proxy, ACL sync, UUIDv7, tanpa tabel organizations, kontrak ingestion, pencarian leksikal, JWT antarlayanan | DEC-024 s.d. DEC-030 | APPROVED |

Larangan arsitektural yang mengikat: tidak ada business logic yang sama di dua framework; tidak ada session sharing; tidak ada tabel dimiliki dua layanan; migrasi hanya oleh pemilik schema; CodeIgniter tidak menentukan izin.

Rincian: `FINAL_TECHNOLOGY_STACK.md`, `FINAL_SYSTEM_ARCHITECTURE.md`, `FINAL_DATABASE_DESIGN.md`, `FINAL_API_SPECIFICATION.md`.

## 7. Status Keputusan

Sumber kebenaran: `FINAL_DECISION_REGISTER.md` (versi 1.3-draft). Ringkasan saat dokumen ini disusun:

| Status | Keputusan |
|---|---|
| APPROVED | DEC-002, 003, 004, 006, 017, 024, 025, 026, 027, 028, 029, 030 |
| PENDING OWNER APPROVAL | DEC-001, 005, 007–016 (DEC-014 berstatus PROPOSED), 018–023 |
| DEFERRED (bagian dari keputusan) | Keycloak, Qdrant, RabbitMQ, Helm, Prometheus/Grafana |
| REJECTED | Tidak ada |

Asumsi yang dikonfirmasi Owner: AS-06 (demo di server lokal atau VPS dengan Docker Compose) dan AS-07 (korpus dokumen kampus yang boleh dipublikasikan atau disintesis). Asumsi lain (AS-01 sampai AS-05) belum dikonfirmasi eksplisit.

## 8. Keamanan dan Privasi (Ringkasan)

Isolasi single-tenant; Laravel sebagai satu-satunya pemegang kredensial pengguna; JWT antarlayanan berumur pendek; otorisasi dokumen dengan filter pada retrieval dan verifikasi ulang sitasi di Laravel; pencabutan izin sinkron; validasi berkas; pertahanan berlapis terhadap prompt injection; SQL read-only; audit append-only; masking pada log; pencadangan terenkripsi dengan uji restore. Pemetaan UU PDP bersifat teknis dan **tidak** menjadi klaim kepatuhan sebelum tinjauan hukum. Rincian: `FINAL_SECURITY_SPECIFICATION.md`.

## 9. Pengujian dan Ukuran Keberhasilan

- Strategi dan kriteria penerimaan: `FINAL_ACCEPTANCE_CRITERIA.md`.
- Target awal (hipotesis, ditetapkan ulang setelah baseline): Recall@10 > 0,9; faithfulness > 0,9; kebenaran fungsi metrik 100% pada kasus uji; kebocoran lintas hak akses = 0.
- Keberhasilan **bukan** ditentukan oleh tampilan aplikasi. MVP dinyatakan siap hanya jika kriteria rilis (AC bagian 7) terpenuhi dengan bukti dan Owner menyetujui pada gerbang G4.

## 10. Roadmap (Ringkasan)

Sembilan milestone MVP (M0–M8), estimasi hipotesis sekitar 29 minggu untuk satu pengembang dengan kalibrasi ulang pada gerbang G1. Lima gerbang keputusan (G0–G4). Rincian: `FINAL_IMPLEMENTATION_ROADMAP.md`.

## 11. Jalur Penelitian

EKAA diposisikan sebagai objek penelitian dengan tiga pertanyaan riset yang diturunkan dari konsep awal:

- RQ1: seberapa besar pencarian hibrida dan reranking meningkatkan recall dibanding dense retrieval saja pada dokumen institusi berbahasa Indonesia?
- RQ2: apakah routing hibrida dengan alat hitung deterministik meningkatkan akurasi numerik dibanding LLM tanpa alat?
- RQ3: bagaimana retrieval yang sadar hak akses memengaruhi kualitas jawaban dan risiko kebocoran?

Aturan: dokumen keputusan proyek dan klaim penelitian dipisah (DEC-023); tidak ada hasil, angka, atau referensi yang dikarang; klaim hanya dibuat dari eksperimen tercatat; sitasi IEEE; verifikasi DOI, penerbit, dan indeksasi Scopus/SINTA; judul artikel berbahasa Inggris, isi berbahasa Indonesia, struktur IMRAD, sekitar 2.500 kata, disesuaikan dengan jurnal tujuan. Pagar klaim: `FINAL_ACCEPTANCE_CRITERIA.md` bagian 8. Judul usulan dari konsep awal bersifat sementara.

## 12. Risiko Utama

Risiko jadwal (RISK-01), beban dua framework (RISK-02), kebocoran lintas hak akses (RISK-05), kualitas bahasa Indonesia (RISK-08), dan klaim penelitian (RISK-14) menjadi perhatian utama. Seluruh 29 risiko dan mitigasinya: `PROJECT_RISK_REGISTER.md`.

## 13. Tata Kelola

### 13.1 Hierarki sumber kebenaran

1. Instruksi eksplisit terbaru Project Owner.
2. Final Project Agreement yang telah disetujui.
3. Decision Log dan perubahan yang telah disetujui.
4. Dokumen spesifikasi dan konteks proyek.
5. `EKAA.docx`.
6. Rekomendasi dan asumsi Claude.

Konflik antar-dokumen tidak diselesaikan diam-diam: konflik diidentifikasi, dampaknya dijelaskan, rekomendasi diberikan, dan keputusan dicatat setelah persetujuan Owner.

### 13.2 Kontrol perubahan

1. Permintaan perubahan dicatat (apa, mengapa, dampak pada cakupan, jadwal, keamanan, dan penelitian).
2. Claude menyusun analisis dampak dan rekomendasi.
3. Owner memutuskan; keputusan dicatat di Decision Register dengan versi baru.
4. Dokumen terkait diperbarui dan riwayat perubahan ditulis.
5. Perubahan yang menyentuh visi inti, fitur inti, atau teknologi final **tidak** dilakukan sepihak.

### 13.3 Tanggung jawab

| Pihak | Tanggung jawab |
|---|---|
| Project Owner | Keputusan akhir; persetujuan dokumen dan rilis; penyediaan informasi yang menghambat (misalnya anggaran, korpus) |
| Claude AI | Analisis, rekomendasi berbasis bukti, penyusunan dokumen dan spesifikasi, audit hasil, pencatatan keputusan; tidak mengklaim persetujuan, hasil uji, atau referensi tanpa bukti |
| Antigravity AI Coding Agent | Mengimplementasikan hanya berdasarkan spesifikasi yang telah disetujui; setiap instruksi dibuat spesifik per modul (teknologi, struktur, alur, integrasi, keamanan, pengujian, acceptance criteria) |
| Pengembang | Implementasi, pengujian, dan bukti sesuai Definition of Done |
| Peneliti | Eksperimen, tinjauan pustaka terverifikasi, dan manuskrip dengan pagar klaim |

### 13.4 Aturan untuk Antigravity (setelah kesepakatan disetujui)

1. Instruksi disusun per milestone dan modul, merujuk ID FR/NFR, kontrak API, skema, dan AC yang relevan.
2. Tidak ada penambahan dependency, penggantian teknologi, atau perubahan skema/kontrak tanpa persetujuan.
3. Fitur dianggap selesai hanya setelah berjalan dan diuji (DoD global); hasil audit Claude dicatat jujur.
4. Tidak ada UI yang tampak berfungsi tanpa integrasi backend nyata.

## 14. Daftar Dokumen Resmi

| No | Dokumen | Status |
|---|---|---|
| 1 | `FINAL_PROJECT_AGREEMENT.md` (dokumen ini) | READY FOR OWNER APPROVAL |
| 2 | `FINAL_REQUIREMENT_SPECIFICATION.md` | READY FOR OWNER APPROVAL |
| 3 | `FINAL_SYSTEM_ARCHITECTURE.md` | READY FOR OWNER APPROVAL |
| 4 | `FINAL_TECHNOLOGY_STACK.md` | READY FOR OWNER APPROVAL |
| 5 | `FINAL_DATABASE_DESIGN.md` | READY FOR OWNER APPROVAL |
| 6 | `FINAL_API_SPECIFICATION.md` | READY FOR OWNER APPROVAL |
| 7 | `FINAL_SECURITY_SPECIFICATION.md` | READY FOR OWNER APPROVAL |
| 8 | `FINAL_IMPLEMENTATION_ROADMAP.md` | READY FOR OWNER APPROVAL |
| 9 | `FINAL_ACCEPTANCE_CRITERIA.md` | READY FOR OWNER APPROVAL |
| 10 | `FINAL_DECISION_REGISTER.md` | READY FOR OWNER APPROVAL (persetujuan parsial tercatat) |
| 11 | `PROJECT_RISK_REGISTER.md` | READY FOR OWNER APPROVAL |

Keterbatasan penyusunan yang perlu diketahui: `PROJECT_CONTEXT.md` dan Decision Log sebelumnya tidak tersedia saat penyusunan; diagram Mermaid belum dirender untuk pemeriksaan visual; versi Python, FastAPI, PostgreSQL, pgvector, Redis, DuckDB, MinIO, Vue, dan ECharts belum diverifikasi dan dikunci pada M0; kajian hukum UU PDP belum dilakukan.

## 15. Halaman Persetujuan

**Project Owner:** [Menunggu pengisian/konfirmasi]

**Project Name:** Enterprise Knowledge and Analytics Assistant (EKAA)

**Agreement Version:** 1.0

**Document Status:** READY FOR OWNER APPROVAL

**Approval Date:** Pending

Setelah Project Owner memberikan persetujuan eksplisit, status seluruh dokumen yang disetujui diperbarui menjadi APPROVED, dan kesepakatan ini ditetapkan sebagai dasar implementasi.

## 16. Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 2026-11-08 | Penyusunan awal, READY FOR OWNER APPROVAL |
