# PROGRESS.md — Ledger Kemajuan Proyek EKAA

| Atribut | Nilai |
|---|---|
| Proyek | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Fase Aktif | **M5 — Analitik Deterministik** (§23, §24, §28.4) |
| Status Proyek | **M0 s.d. M4 SELESAI (100% Bukti Terverifikasi)** -> Masuk ke M5 |
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
| **M4** | Retrieval, Jawaban, Chat | 4 minggu | **DONE** | AC-M4-01 s.d. AC-M4-09 ([`tests/reports/M4-baseline-rag-report.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M4-baseline-rag-report.md)) |
| **M5** | Analitik Deterministik | 4 minggu | **IN_PROGRESS** | AC-M5-01 s.d. AC-M5-09 (Aktif) |
| **M6** | Router, Guardrails, Feedback | 3 minggu | `TODO` | AC-M6-01 s.d. AC-M6-06 |
| **M7** | Connector & Sync | 2 minggu | `TODO` | AC-M7-01 s.d. AC-M7-07 |
| **M8** | Pengerasan dan Rilis MVP | 3 minggu | `TODO` | AC-M8-01 s.d. AC-M8-06 |

---

## 2. Matriks Acceptance Criteria Milestone M4 (Selesai)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M4-01** | Laporan baseline Recall@k, MRR, nDCG, faithfulness, akurasi sitasi, ablasi C0 s.d. C3 | **DONE** | [`tests/reports/M4-baseline-rag-report.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M4-baseline-rag-report.md) |
| **AC-M4-02** | Kebocoran lintas hak akses = 0 pada seluruh skenario (pencabutan, versi lama, non-izin) | **DONE** | [`tests/security/test_m4_rag.js`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/security/test_m4_rag.js) (0 kebocoran) |
| **AC-M4-03** | Setiap klaim penting memiliki sitasi yang dapat dibuka (dokumen, bagian, halaman) | **DONE** | [`services/ai/ekaa_ai/domain/rag/generator.py`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/services/ai/ekaa_ai/domain/rag/generator.py) |
| **AC-M4-04** | Pertanyaan tanpa bukti menghasilkan `no_evidence` (tingkat abstain terukur) | **DONE** | [`services/ai/ekaa_ai/domain/rag/sufficiency.py`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/services/ai/ekaa_ai/domain/rag/sufficiency.py) |
| **AC-M4-05** | Sitasi gagal Policy di Laravel dibuang dan dicatat anomali keamanan | **DONE** | [`apps/core/app/Domain/Conversations/CitationVerifier.php`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/apps/core/app/Domain/Conversations/CitationVerifier.php) |
| **AC-M4-06** | Riwayat percakapan hanya dapat diakses oleh pemiliknya | **DONE** | [`apps/core/app/Policies/ConversationPolicy.php`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/apps/core/app/Policies/ConversationPolicy.php) |
| **AC-M4-07** | Target awal Recall@10 > 0.9 & faithfulness > 0.9 diukur baseline-nya | **DONE** | Checkpoint G2 divalidasi (C3: Recall@10=0.961, Faithfulness=0.952) |
| **AC-M4-08** | Pemilihan LLM dicatat dengan dasar evaluasi kualitas, biaya, latensi | **DONE** | [`docs/adr/ADR-003-llm-selection.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/docs/adr/ADR-003-llm-selection.md) |
| **AC-M4-09** | Evaluation harness CLI (`python -m ekaa_ai.eval`) menghasilkan hasil deterministik | **DONE** | [`services/ai/ekaa_ai/eval/harness.py`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/services/ai/ekaa_ai/eval/harness.py) |

---

## 3. Matriks Acceptance Criteria Milestone M5 (Fase Aktif)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M5-01** | Seluruh kasus uji metrik lulus 100% termasuk kasus tepi | `IN_PROGRESS` | `services/ai/ekaa_ai/domain/analytics/metric_engine.py` |
| **AC-M5-02** | CAGR memakai jumlah periode n; nilai awal <= 0 dan satuan berbeda ditolak | `IN_PROGRESS` | Unit test fungsi metrik |
| **AC-M5-03** | Tidak ada jalur kode yang membuat LLM menghasilkan angka akhir | `IN_PROGRESS` | Arsitektur deterministic metric engine |
| **AC-M5-04** | Nilai grafik identik dengan hasil hitung; spesifikasi tidak valid ditolak | `IN_PROGRESS` | `services/ai/ekaa_ai/domain/analytics/chart_validator.py` |
| **AC-M5-05** | Setiap hasil menampilkan rumus, parameter, dan sumber dataset | `IN_PROGRESS` | Kontrak hasil analitik |
| **AC-M5-06** | Pengguna tanpa izin dataset tidak dapat menanyakan atau melihatnya | `IN_PROGRESS` | Matriks otorisasi dataset |
| **AC-M5-07** | Impor dengan baris galat melaporkan baris bermasalah tanpa data separuh; format angka Indonesia terbaca | `IN_PROGRESS` | DuckDB tabular importer |
| **AC-M5-08** | Akurasi numerik dari bahasa alami dilaporkan (exact match dengan toleransi) | `IN_PROGRESS` | Subset analitik golden dataset |
| **AC-M5-09** | Angka pada narasi yang tidak cocok dengan hasil hitung ditolak validator | `IN_PROGRESS` | `services/ai/ekaa_ai/domain/analytics/narrative_validator.py` |
