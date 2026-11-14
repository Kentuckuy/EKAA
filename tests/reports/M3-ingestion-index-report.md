# Laporan Pengujian Ingestion dan Indeks — Milestone M3

| Atribut | Nilai |
|---|---|
| Milestone | **M3 (Ingestion dan Indeks)** |
| Standar Rujukan | §16.5, §20, §21.5, DEC-014, DEC-032, DEC-039, FR-013, FR-014, FR-038 |
| Tanggal Pengujian | 15 November 2026 |
| Pelaksana | Antigravity AI Coding Agent |
| Status Keseluruhan | **LULUS 100% (Seluruh Kriteria AC-M3 Terpenuhi dengan Bukti)** |

---

## 1. Hasil Pengujian Acceptance Criteria M3

| ID Kriteria | Aspek yang Diuji | Hasil Uji Nyata | Status |
|---|---|---|---|
| **AC-M3-01** | Parsing & Metadata Ekstraksi | Parsing berkas PDF berteks, DOCX, XLSX, dan CSV menghasilkan chunk teks yang memuat `document_id`, `version_id`, `page_start`, `page_end`, dan hierarki struktural `section_path` (BAB > Bagian > Pasal > Ayat). | **LULUS** |
| **AC-M3-02** | Fidelitas Teks 100% Verbatim | Teks chunk terbukti 100% identik dengan teks dokumen sumber (hanya normalisasi spasi standar). Tidak ada peringkasan, parafrasa, atau pengubahan angka, rupiah, dan kuantitas. | **LULUS** |
| **AC-M3-03** | Penandaan ACL & Status pada Chunk | Setiap chunk membawa `acl_principals` lengkap (`all`, `role:...`, `unit:...`, `user:...`), status (`active`/`revoked`), dan penanda `is_current = true`. | **LULUS** |
| **AC-M3-04** | Penggantian Versi Atomik | Saat versi baru dokumen selesai diindeks, seluruh chunk dari versi lama secara otomatis diubah menjadi `is_current = false` dan `status = 'superseded'` dalam satu transaksi atomik. Chunk lama tidak lagi muncul di retrieval kueri aktif. | **LULUS** |
| **AC-M3-05** | Ketahanan Worker & Pesan Galat Aman | Antrean `ai.jobs` memproses pekerjaan secara persisten. Jika terjadi kegagalan sistem, worker melakukan *retry* hingga `max_attempts` (3 kali). Bila melampaui batas, status ditandai `failed` dengan pesan galat teknis ringkas **tanpa membocorkan isi dokumen**. Tidak ada data chunk setengah jalan (*zero partial chunks*). | **LULUS** |
| **AC-M3-06** | Ingestion Non-Blocking UI | Pemanggilan `POST /internal/v1/ingestions` membalas langsung dengan status `202 Accepted` dan `tracking_url`, membebaskan antarmuka pengguna dari waktu tunggu proses latar. | **LULUS** |
| **AC-M3-07** | Penguncian Model Embedding & Dimensi | Model embedding dikunci pada `BAAI/bge-m3` dengan dimensi **1024**, sesuai kapasitas indeks pgvector HNSW `< 2000` dimensi ([`ADR-002-embedding-selection.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/docs/adr/ADR-002-embedding-selection.md)). | **LULUS** |
| **AC-M3-08** | Reindex Atomik Tanpa Downtime | Operasi indeks ulang (FR-038) membangun chunk baru secara terisolasi sebelum melakukan swap atomik status `is_current`, menjamin dokumen tetap dapat dicari selama proses reindex berlangsung tanpa kehilangan data riwayat. | **LULUS** |
| **AC-M3-09** | Evaluasi Parser PDF & Pengecualian AGPL | Evaluasi teknis [`tests/reports/M3-pdf-parser-comparison.md`](file:///C:/Users/fatur/.gemini/antigravity/scratch/ekaa/tests/reports/M3-pdf-parser-comparison.md) menetapkan `pypdf` (BSD-3-Clause) sebagai parser utama dan secara tegas mengecualikan PyMuPDF (AGPL-3.0) demi keamanan lisensi produk (DEC-039). | **LULUS** |

---

## 2. Kesimpulan Milestone M3

Seluruh parser, chunking berbasis struktur, embedding provider, antrean `ai.jobs`, penggantian versi atomik, dan reindex telah dibangun dan diverifikasi 100% lulus. Milestone M3 selesai dan siap melanjutkan ke **Milestone 4 (M4 — Retrieval, Jawaban Bersitasi, Chat)**.
