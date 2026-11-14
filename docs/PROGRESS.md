# PROGRESS.md — Ledger Kemajuan Proyek EKAA

| Atribut | Nilai |
|---|---|
| Proyek | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Fase Aktif | **M3 — Ingestion dan Indeks** (§20, §21.5, §16.5) |
| Status Proyek | **M0, M1, M2 SELESAI (100% Bukti Terverifikasi)** -> Masuk ke M3 |
| Tanggal | 15 November 2026 |
| Terakhir Diperbarui | 15 November 2026 |

---

## 1. Status Milestone M0 s.d. M8

| Milestone | Nama | Durasi Estimasi | Status | Bukti Keluar |
|---|---|---|---|---|
| **M0** | Fondasi dan Kontrak | 3 minggu | **DONE** | AC-M0-01 s.d. AC-M0-09 (Commit `bea5234`) |
| **M1** | Identitas, Peran, Audit | 3 minggu | **DONE** | AC-M1-01 s.d. AC-M1-09 ([`tests/reports/M1-auth-audit-report.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M1-auth-audit-report.md)) |
| **M2** | Dokumen dan Izin | 3 minggu | **DONE** | AC-M2-01 s.d. AC-M2-07 ([`tests/reports/M2-documents-permissions-report.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M2-documents-permissions-report.md)) |
| **M3** | Ingestion dan Indeks | 4 minggu | **IN_PROGRESS** | AC-M3-01 s.d. AC-M3-09 (Aktif) |
| **M4** | Retrieval, Jawaban, Chat | 4 minggu | `TODO` | AC-M4-01 s.d. AC-M4-09 |
| **M5** | Analitik Deterministik | 4 minggu | `TODO` | AC-M5-01 s.d. AC-M5-09 |
| **M6** | Router, Guardrails, Feedback | 3 minggu | `TODO` | AC-M6-01 s.d. AC-M6-06 |
| **M7** | Connector & Sync | 2 minggu | `TODO` | AC-M7-01 s.d. AC-M7-07 |
| **M8** | Pengerasan dan Rilis MVP | 3 minggu | `TODO` | AC-M8-01 s.d. AC-M8-06 |

---

## 2. Matriks Acceptance Criteria Milestone M2 (Selesai)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M2-01** | Validasi berkas, magic bytes, ClamAV fail-closed (EICAR ditolak, scanner down ditolak) | **DONE** | [`tests/reports/M2-documents-permissions-report.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M2-documents-permissions-report.md) |
| **AC-M2-02** | Unggah menghasilkan entri terversi, audit, dan job; unggah ulang idempoten | **DONE** | [`apps/core/app/Domain/Documents/DocumentService.php`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/apps/core/app/Domain/Documents/DocumentService.php) |
| **AC-M2-03** | Pengguna tanpa izin menerima respons 404 NOT FOUND (masking sumber daya) | **DONE** | [`apps/core/app/Domain/Documents/DocumentPolicy.php`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/apps/core/app/Domain/Documents/DocumentPolicy.php) |
| **AC-M2-04** | Pencabutan izin & status dicabut sinkron ke AI sebelum sukses | **DONE** | [`apps/core/app/Domain/Documents/AclSyncService.php`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/apps/core/app/Domain/Documents/AclSyncService.php) |
| **AC-M2-05** | Layanan AI mati: rute non-AI tetap hidup, rute AI balas 503 AI_UNAVAILABLE | **DONE** | [`apps/core/app/Domain/Documents/AIClient.php`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/apps/core/app/Domain/Documents/AIClient.php) |
| **AC-M2-06** | URL unduhan presigned S3 berumur pendek dan terproteksi Policy | **DONE** | [`tests/security/test_m2_documents.js`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/security/test_m2_documents.js) |
| **AC-M2-07** | Dokumen tanpa izin dilarang aktif; izin unit induk berlaku bagi anggota unit turunan | **DONE** | [`tests/security/test_m2_documents.js`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/security/test_m2_documents.js) |

---

## 3. Matriks Acceptance Criteria Milestone M3 (Fase Aktif)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M3-01** | PDF berteks, DOCX, XLSX, CSV menghasilkan chunk dengan metadata dokumen, versi, halaman, bagian | `IN_PROGRESS` | `services/ai/ekaa_ai/domain/ingestion/` |
| **AC-M3-02** | Teks chunk identik dengan teks sumber (tanpa peringkasan / pengubahan angka) | `IN_PROGRESS` | `services/ai/ekaa_ai/domain/ingestion/chunker.py` |
| **AC-M3-03** | Setiap chunk membawa acl_principals, status, is_current sesuai izin Laravel | `IN_PROGRESS` | Skema `ai.chunks` |
| **AC-M3-04** | Penggantian versi baru membuat chunk versi lama `is_current = false` secara atomik | `IN_PROGRESS` | Transaksi penggantian versi |
| **AC-M3-05** | Kegagalan worker menghasilkan `failed` dengan pesan ringkas tanpa isi dokumen dan dapat diulang; tidak ada chunk separuh | `IN_PROGRESS` | `services/ai/ekaa_ai/worker/job_processor.py` |
| **AC-M3-06** | Ingestion tidak memblokir UI; status proses terlihat | `IN_PROGRESS` | `POST /internal/v1/ingestions` (202 Accepted) |
| **AC-M3-07** | Model embedding & dimensi vektor dikunci berdasarkan evaluasi golden dataset | `IN_PROGRESS` | `docs/adr/ADR-002-embedding-selection.md` |
| **AC-M3-08** | Reindex mengganti indeks secara atomik tanpa kehilangan dokumen (FR-038) | `IN_PROGRESS` | Prosedur reindex |
| **AC-M3-09** | Pustaka parsing PDF dipilih dengan uji perbandingan dan lisensi dicatat (DEC-039) | `IN_PROGRESS` | `tests/reports/M3-pdf-parser-comparison.md` |
