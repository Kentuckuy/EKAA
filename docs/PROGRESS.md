# PROGRESS.md — Ledger Kemajuan Proyek EKAA

| Atribut | Nilai |
|---|---|
| Proyek | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Fase Aktif | **M4 — Retrieval, Jawaban Bersitasi, Chat** (§21, §22, §35.3) |
| Status Proyek | **M0, M1, M2, M3 SELESAI (100% Bukti Terverifikasi)** -> Masuk ke M4 |
| Tanggal | 15 November 2026 |
| Terakhir Diperbarui | 15 November 2026 |

---

## 1. Status Milestone M0 s.d. M8

| Milestone | Nama | Durasi Estimasi | Status | Bukti Keluar |
|---|---|---|---|---|
| **M0** | Fondasi dan Kontrak | 3 minggu | **DONE** | AC-M0-01 s.d. AC-M0-09 (Commit `bea5234`) |
| **M1** | Identitas, Peran, Audit | 3 minggu | **DONE** | AC-M1-01 s.d. AC-M1-09 ([`tests/reports/M1-auth-audit-report.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M1-auth-audit-report.md)) |
| **M2** | Dokumen dan Izin | 3 minggu | **DONE** | AC-M2-01 s.d. AC-M2-07 ([`tests/reports/M2-documents-permissions-report.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M2-documents-permissions-report.md)) |
| **M3** | Ingestion dan Indeks | 4 minggu | **DONE** | AC-M3-01 s.d. AC-M3-09 ([`tests/reports/M3-ingestion-index-report.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M3-ingestion-index-report.md)) |
| **M4** | Retrieval, Jawaban, Chat | 4 minggu | **IN_PROGRESS** | AC-M4-01 s.d. AC-M4-09 (Aktif) |
| **M5** | Analitik Deterministik | 4 minggu | `TODO` | AC-M5-01 s.d. AC-M5-09 |
| **M6** | Router, Guardrails, Feedback | 3 minggu | `TODO` | AC-M6-01 s.d. AC-M6-06 |
| **M7** | Connector & Sync | 2 minggu | `TODO` | AC-M7-01 s.d. AC-M7-07 |
| **M8** | Pengerasan dan Rilis MVP | 3 minggu | `TODO` | AC-M8-01 s.d. AC-M8-06 |

---

## 2. Matriks Acceptance Criteria Milestone M3 (Selesai)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M3-01** | Parsing & ekstraksi metadata chunk (PDF, DOCX, XLSX, CSV) | **DONE** | [`services/ai/ekaa_ai/domain/ingestion/`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/services/ai/ekaa_ai/domain/ingestion/) |
| **AC-M3-02** | Fidelitas teks 100% verbatim tanpa pengubahan angka/fakta | **DONE** | [`services/ai/ekaa_ai/domain/ingestion/chunker.py`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/services/ai/ekaa_ai/domain/ingestion/chunker.py) |
| **AC-M3-03** | Setiap chunk membawa acl_principals, status, dan is_current | **DONE** | [`services/ai/ekaa_ai/domain/ingestion/pipeline.py`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/services/ai/ekaa_ai/domain/ingestion/pipeline.py) |
| **AC-M3-04** | Penggantian versi atomik (versi lama is_current = false) | **DONE** | [`tests/security/test_m3_ingestion.js`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/security/test_m3_ingestion.js) |
| **AC-M3-05** | Worker retry di ai.jobs dan pesan error tanpa bocor isi dokumen | **DONE** | [`services/ai/ekaa_ai/worker/job_processor.py`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/services/ai/ekaa_ai/worker/job_processor.py) |
| **AC-M3-06** | Ingestion non-blocking membalas 202 Accepted | **DONE** | [`services/ai/ekaa_ai/api/main.py`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/services/ai/ekaa_ai/api/main.py) |
| **AC-M3-07** | Model embedding BAAI/bge-m3 dan dimensi 1024 dikunci | **DONE** | [`docs/adr/ADR-002-embedding-selection.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/docs/adr/ADR-002-embedding-selection.md) |
| **AC-M3-08** | Reindex korpus atomik tanpa kehilangan dokumen (FR-038) | **DONE** | [`services/ai/ekaa_ai/domain/ingestion/reindexer.py`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/services/ai/ekaa_ai/domain/ingestion/reindexer.py) |
| **AC-M3-09** | Pustaka PDF pypdf dipilih dan PyMuPDF AGPL dikecualikan | **DONE** | [`tests/reports/M3-pdf-parser-comparison.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M3-pdf-parser-comparison.md) |

---

## 3. Matriks Acceptance Criteria Milestone M4 (Fase Aktif)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M4-01** | Laporan baseline Recall@k, MRR, nDCG, faithfulness, akurasi sitasi, ablasi C0 s.d. C3 | `IN_PROGRESS` | `tests/reports/M4-baseline-rag-report.md` |
| **AC-M4-02** | Kebocoran lintas hak akses = 0 pada seluruh skenario (pencabutan, versi lama, non-izin) | `IN_PROGRESS` | Matriks Uji Kebocoran M4 |
| **AC-M4-03** | Setiap klaim penting memiliki sitasi yang dapat dibuka (dokumen, bagian, halaman) | `IN_PROGRESS` | `services/ai/ekaa_ai/domain/rag/` |
| **AC-M4-04** | Pertanyaan tanpa bukti menghasilkan `no_evidence` (tingkat abstain terukur) | `IN_PROGRESS` | Logika abstain M4 |
| **AC-M4-05** | Sitasi gagal Policy di Laravel dibuang dan dicatat anomali keamanan | `IN_PROGRESS` | Verifikasi sitasi lapis kedua |
| **AC-M4-06** | Riwayat percakapan hanya dapat diakses oleh pemiliknya | `IN_PROGRESS` | `apps/core/app/Policies/ConversationPolicy.php` |
| **AC-M4-07** | Target awal Recall@10 > 0.9 & faithfulness > 0.9 diukur baseline-nya | `IN_PROGRESS` | Checkpoint G2 |
| **AC-M4-08** | Pemilihan LLM dicatat dengan dasar evaluasi kualitas, biaya, latensi | `IN_PROGRESS` | `docs/adr/ADR-003-llm-selection.md` |
| **AC-M4-09** | Evaluation harness CLI (`python -m ekaa_ai.eval`) menghasilkan hasil deterministik | `IN_PROGRESS` | `services/ai/ekaa_ai/eval/` |
