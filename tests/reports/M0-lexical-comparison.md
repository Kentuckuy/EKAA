# Laporan Perbandingan Mesin Pencarian Leksikal — AC-M0-07

| Atribut | Nilai |
|---|---|
| Milestone | M0 (Fondasi dan Kontrak) |
| Komponen | Leksikal Search Engine (DEC-029, §16.6) |
| Pilihan Baseline | PostgreSQL Full-Text Search (FTS) `to_tsvector('simple', content)` + GIN Index |
| Alternatif Dievaluasi | `pg_search` (ParadeDB/Tantivy), `pg_bm25`, Ekstensi Khusus |
| Tanggal | 14 November 2026 |
| Status | **LULUS (Baseline Ditetapkan & Catatan Lisensi Tercatat)** |

---

## 1. Analisis Perbandingan Kandidat Mesin Leksikal

| Kriteria | PostgreSQL FTS (`simple`) | `pg_search` (ParadeDB / Tantivy) | `pg_bm25` (C Extension) |
|---|---|---|---|
| **Formula Ranking** | TF-IDF / Frekuensi Term (`ts_rank_cd`) | BM25 Sejati (*Okapi BM25*) | BM25 Sejati |
| **Lisensi** | **PostgreSQL License (Permisif)** | **AGPL-3.0** (Perlu perhatian khusus, OA-07) | Apache-2.0 / MIT |
| **Dukungan PostgreSQL 17** | Asli bawaan engine (*Zero Dependency*) | Perlu kompilasi & instalasi binary tambahan | Kestabilan pada PG 17 belum terjamin |
| **Tokenisasi Bahasa Indonesia** | Aman dengan konfigurasi `'simple'` (tanpa stemming bahasa Inggris yang merusak afiks Indonesia: *meng-*, *di-*, *-kan*) | Memerlukan konfigurasi tokenizer Tantivy | Memerlukan tokenizer eksternal |
| **Pemeliharaan & Rantai Pasok** | Sangat matang, terpelihara bersama PostgreSQL | Aktif, namun membawa risiko dependensi dan lisensi AGPL | Pemeliharaan komunitas terbatas |

---

## 2. Isu Lisensi AGPL (DEC-039, OA-07)

Dokumen Word invarian #19 dan Perhatian Owner OA-07 secara tegas menyatakan:
> *"Komponen AGPL/GPL tidak masuk kode aplikasi tanpa ADR dan catatan perhatian Owner."*

Jika ekstensi `pg_search` yang berlisensi AGPL-3.0 dipasang di dalam PostgreSQL, hal ini dapat memicu kewajiban membuka source code bila sistem didistribusikan ke organisasi klien.

---

## 3. Keputusan Final untuk M0 & Fase MVP (DEC-029)

1. **Baseline Resmi M0 s.d. M3:**
   - Menggunakan PostgreSQL Full-Text Search dengan parser `'simple'`:
     ```sql
     content_tsv TSVECTOR GENERATED ALWAYS AS (to_tsvector('simple', content)) STORED
     CREATE INDEX idx_chunks_content_tsv ON ai.chunks USING GIN(content_tsv);
     ```
   - Alasan: 100% bawaan PostgreSQL, tanpa dependensi eksternal, bebas risiko lisensi AGPL, dan terbukti stabil untuk korpus peraturan/SK institusi berbahasa Indonesia.

2. **Jalur Evaluasi Riset (Ablasi C0 vs C1 pada Golden Dataset):**
   - Performa nDCG@10 FTS `simple` akan diukur pada dev split golden dataset di Milestone 4.
   - Jika diperlukan optimasi BM25 lebih lanjut tanpa melanggar batas lisensi AGPL, implementasi BM25 berbasis Python di layer layanan AI (`services/ai`) dapat dievaluasi sebagai alternatif murni tanpa mencemari basis data PostgreSQL.
