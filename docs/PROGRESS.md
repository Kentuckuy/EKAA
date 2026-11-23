# PROGRESS.md — Ledger Kemajuan Proyek EKAA

| Atribut | Nilai |
|---|---|
| Proyek | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Fase Aktif | **M7 — Connector & Sync** (§20, §21, §22) |
| Status Proyek | **M0 s.d. M6 SELESAI (100% Bukti Terverifikasi)** -> Masuk ke M7 |
| Tanggal | 24 November 2026 |
| Terakhir Diperbarui | 24 November 2026 |

---

## 1. Status Milestone M0 s.d. M8

| Milestone | Nama | Durasi Estimasi | Status | Bukti Keluar |
|---|---|---|---|---|
| **M0** | Fondasi dan Kontrak | 3 minggu | **DONE** | AC-M0-01 s.d. AC-M0-09 (Commit `bea5234`) |
| **M1** | Identitas, Peran, Audit | 3 minggu | **DONE** | AC-M1-01 s.d. AC-M1-09 ([`tests/reports/M1-auth-audit-report.md`](file:///D:/EKAA/tests/reports/M1-auth-audit-report.md)) |
| **M2** | Dokumen dan Izin | 3 minggu | **DONE** | AC-M2-01 s.d. AC-M2-07 ([`tests/reports/M2-documents-permissions-report.md`](file:///D:/EKAA/tests/reports/M2-documents-permissions-report.md)) |
| **M3** | Ingestion dan Indeks | 4 minggu | **DONE** | AC-M3-01 s.d. AC-M3-09 ([`tests/reports/M3-ingestion-index-report.md`](file:///D:/EKAA/tests/reports/M3-ingestion-index-report.md)) |
| **M4** | Retrieval, Jawaban, Chat | 4 minggu | **DONE** | AC-M4-01 s.d. AC-M4-09 ([`tests/reports/M4-baseline-rag-report.md`](file:///D:/EKAA/tests/reports/M4-baseline-rag-report.md)) |
| **M5** | Analitik Deterministik | 4 minggu | **DONE** | AC-M5-01 s.d. AC-M5-09 ([`tests/reports/M5-analytics-deterministic-report.md`](file:///D:/EKAA/tests/reports/M5-analytics-deterministic-report.md)) |
| **M6** | Router, Guardrails, Feedback | 3 minggu | **DONE** | AC-M6-01 s.d. AC-M6-06 ([`tests/reports/M6-router-guardrails-report.md`](file:///D:/EKAA/tests/reports/M6-router-guardrails-report.md)) |
| **M7** | Connector & Sync | 2 minggu | **IN_PROGRESS** | AC-M7-01 s.d. AC-M7-07 (Aktif) |
| **M8** | Pengerasan dan Rilis MVP | 3 minggu | `TODO` | AC-M8-01 s.d. AC-M8-06 |

---

## 2. Matriks Acceptance Criteria Milestone M4 (Selesai)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M4-01** | Laporan baseline Recall@k, MRR, nDCG, faithfulness, akurasi sitasi, ablasi C0 s.d. C3 | **DONE** | [`tests/reports/M4-baseline-rag-report.md`](file:///D:/EKAA/tests/reports/M4-baseline-rag-report.md) |
| **AC-M4-02** | Kebocoran lintas hak akses = 0 pada seluruh skenario (pencabutan, versi lama, non-izin) | **DONE** | [`tests/security/test_m4_rag.js`](file:///D:/EKAA/tests/security/test_m4_rag.js) (0 kebocoran) |
| **AC-M4-03** | Setiap klaim penting memiliki sitasi yang dapat dibuka (dokumen, bagian, halaman) | **DONE** | [`services/ai/ekaa_ai/domain/rag/generator.py`](file:///D:/EKAA/services/ai/ekaa_ai/domain/rag/generator.py) |
| **AC-M4-04** | Pertanyaan tanpa bukti menghasilkan `no_evidence` (tingkat abstain terukur) | **DONE** | [`services/ai/ekaa_ai/domain/rag/sufficiency.py`](file:///D:/EKAA/services/ai/ekaa_ai/domain/rag/sufficiency.py) |
| **AC-M4-05** | Sitasi gagal Policy di Laravel dibuang dan dicatat anomali keamanan | **DONE** | [`apps/core/app/Domain/Conversations/CitationVerifier.php`](file:///D:/EKAA/apps/core/app/Domain/Conversations/CitationVerifier.php) |
| **AC-M4-06** | Riwayat percakapan hanya dapat diakses oleh pemiliknya | **DONE** | [`apps/core/app/Policies/ConversationPolicy.php`](file:///D:/EKAA/apps/core/app/Policies/ConversationPolicy.php) |
| **AC-M4-07** | Target awal Recall@10 > 0.9 & faithfulness > 0.9 diukur baseline-nya | **DONE** | Checkpoint G2 divalidasi (C3: Recall@10=0.961, Faithfulness=0.952) |
| **AC-M4-08** | Pemilihan LLM dicatat dengan dasar evaluasi kualitas, biaya, latensi | **DONE** | [`docs/adr/ADR-003-llm-selection.md`](file:///D:/EKAA/docs/adr/ADR-003-llm-selection.md) |
| **AC-M4-09** | Evaluation harness CLI (`python -m ekaa_ai.eval`) menghasilkan hasil deterministik | **DONE** | [`services/ai/ekaa_ai/eval/harness.py`](file:///D:/EKAA/services/ai/ekaa_ai/eval/harness.py) |

---

## 3. Matriks Acceptance Criteria Milestone M5 (Selesai)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M5-01** | Seluruh kasus uji metrik lulus 100% termasuk kasus tepi | **DONE** | [`services/ai/ekaa_ai/domain/analytics/metric_engine.py`](file:///D:/EKAA/services/ai/ekaa_ai/domain/analytics/metric_engine.py) |
| **AC-M5-02** | CAGR memakai jumlah periode n; nilai awal <= 0 dan satuan berbeda ditolak | **DONE** | Unit test fungsi metrik ([`tests/security/test_m5_analytics.js`](file:///D:/EKAA/tests/security/test_m5_analytics.js)) |
| **AC-M5-03** | Tidak ada jalur kode yang membuat LLM menghasilkan angka akhir | **DONE** | Arsitektur deterministic metric engine (`INV-07`) |
| **AC-M5-04** | Nilai grafik identik dengan hasil hitung; spesifikasi tidak valid ditolak | **DONE** | [`services/ai/ekaa_ai/domain/analytics/chart_validator.py`](file:///D:/EKAA/services/ai/ekaa_ai/domain/analytics/chart_validator.py) |
| **AC-M5-05** | Setiap hasil menampilkan rumus, parameter, dan sumber dataset | **DONE** | Amplop kontrak hasil analitik deterministik |
| **AC-M5-06** | Pengguna tanpa izin dataset tidak dapat menanyakan atau melihatnya | **DONE** | [`apps/core/app/Policies/DatasetPolicy.php`](file:///D:/EKAA/apps/core/app/Policies/DatasetPolicy.php) (404 Masking) |
| **AC-M5-07** | Impor dengan baris galat melaporkan baris bermasalah tanpa data separuh; format angka Indonesia terbaca | **DONE** | [`services/ai/ekaa_ai/domain/analytics/dataset_importer.py`](file:///D:/EKAA/services/ai/ekaa_ai/domain/analytics/dataset_importer.py) |
| **AC-M5-08** | Akurasi numerik dari bahasa alami dilaporkan (exact match dengan toleransi) | **DONE** | [`tests/reports/M5-analytics-deterministic-report.md`](file:///D:/EKAA/tests/reports/M5-analytics-deterministic-report.md) |
| **AC-M5-09** | Angka pada narasi yang tidak cocok dengan hasil hitung ditolak validator | **DONE** | [`services/ai/ekaa_ai/domain/analytics/narrative_validator.py`](file:///D:/EKAA/services/ai/ekaa_ai/domain/analytics/narrative_validator.py) |

---

## 4. Matriks Acceptance Criteria Milestone M6 (Selesai)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M6-01** | Evaluasi router pada test set menghasilkan akurasi routing terukur | **DONE** | [`services/ai/ekaa_ai/domain/router/hybrid_router.py`](file:///D:/EKAA/services/ai/ekaa_ai/domain/router/hybrid_router.py) |
| **AC-M6-02** | Uji injeksi prompt dari test set tertolak tanpa membocorkan instruksi sistem atau data | **DONE** | [`services/ai/ekaa_ai/domain/guardrails/injection_guard.py`](file:///D:/EKAA/services/ai/ekaa_ai/domain/guardrails/injection_guard.py) |
| **AC-M6-03** | Feedback thumbs up/down tersimpan dengan referensi pesan yang benar | **DONE** | [`apps/core/app/Domain/Feedback/FeedbackService.php`](file:///D:/EKAA/apps/core/app/Domain/Feedback/FeedbackService.php) |
| **AC-M6-04** | Alasan feedback negatif terstruktur (tidak akurat, tidak lengkap, sitasi salah) | **DONE** | Enum alasan terstruktur kanonikal (`FeedbackService`) |
| **AC-M6-05** | Pertanyaan klarifikasi diajukan bila maksud ambigu | **DONE** | Deteksi ambiguitas proaktif (< 0.60 confidence) |
| **AC-M6-06** | Evaluasi gabungan RAG + Router + Guardrails dilaporkan | **DONE** | [`tests/reports/M6-router-guardrails-report.md`](file:///D:/EKAA/tests/reports/M6-router-guardrails-report.md) |

---

## 5. Matriks Acceptance Criteria Milestone M7 (Fase Aktif)

| ID | Deskripsi Kriteria Penerimaan | Status | Tautan / Jalur Bukti |
|---|---|---|---|
| **AC-M7-01** | Sumber lokal memindai folder dan mendeteksi berkas baru/berubah/dihapus | `IN_PROGRESS` | `apps/connector/app/Domain/Sync/FolderScanner.php` |
| **AC-M7-02** | Sinkronisasi menghasilkan status per berkas (success, failed, skipped) dengan ringkasan | `IN_PROGRESS` | `apps/connector/app/Controllers/SyncRuns.php` |
| **AC-M7-03** | Berkas yang dihapus di sumber ditandai nonaktif/terhapus di Core | `IN_PROGRESS` | Sinkronisasi status penghapusan |
| **AC-M7-04** | Berkas yang gagal diproses tidak menghentikan sinkronisasi berkas lain | `IN_PROGRESS` | Isolasi kegagalan per berkas (*fault isolation*) |
| **AC-M7-05** | Handoff ke Core memakai service JWT dan idempotent key | `IN_PROGRESS` | `apps/connector/app/Domain/Sync/CoreHandoffClient.php` |
| **AC-M7-06** | Riwayat sinkronisasi tersimpan dan dapat dilihat | `IN_PROGRESS` | Schema `connector.sync_runs` & `connector.sync_run_items` |
| **AC-M7-07** | Batas laju sinkronisasi dihormati | `IN_PROGRESS` | Rate limiting & debounce interval sinkronisasi |


