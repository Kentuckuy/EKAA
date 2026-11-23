# RESEARCH QUESTIONS AND HYPOTHESES — EKAA

**Versi 0.1 — 10 Nov 2026 — DRAFT (rekomendasi, belum disetujui Owner)**

## 1. Keputusan cakupan (rekomendasi)

Tiga RQ kandidat **tidak layak** dalam satu artikel ±2.500 kata: masing-masing membutuhkan dataset, baseline, dan metrik berbeda. Rekomendasi: **tiga artikel berurutan.**

| Artikel | Fokus | Prasyarat sistem | Perkiraan data siap |
|---|---|---|---|
| **1 (utama)** | RQ1: hibrida + reranking pada dokumen institusi Indonesia | M4 selesai (baseline) | Setelah G2 |
| 2 | RQ3: retrieval sadar hak akses (kebocoran, overhead) | M7 keamanan | Setelah M7 |
| 3 | RQ2: metric engine deterministik + router | M5–M6 | Setelah M6 |

## 2. Artikel 1 — RQ dan hipotesis

**RQ1a.** Seberapa besar Recall@10, MRR, dan nDCG@10 berubah ketika retrieval dense saja diganti hibrida (BM25 + dense, RRF), dan ketika ditambah cross-encoder reranker, pada korpus dokumen institusi berbahasa Indonesia?
**RQ1b.** Berapa biaya latensi (p50/p95) tiap komponen tambahan?
**RQ1c (kontrol).** Apakah penerapan filter hak akses sebelum pencarian mengubah metrik relevansi dan latensi dibanding tanpa filter pada kueri yang berhak?

| Hipotesis | Rumusan | Catatan |
|---|---|---|
| H1 | nDCG@10 hibrida > dense saja | Arah didukung pracetak [4][6][7], tetapi tidak universal [4]; **diuji, bukan diasumsikan** |
| H2 | nDCG@10 hibrida+rerank > hibrida | Didukung sebagian [4] |
| H3 | Filter akses pra-pencarian tidak menurunkan metrik relevansi untuk kueri berhak (selisih ≤ ε, ditetapkan sebelum eksperimen) | Hipotesis kesetaraan; ε ditetapkan di pra-registrasi |
| H0 | Tidak ada perbedaan bermakna | Hasil nol dilaporkan |

Target Recall@10 > 0,9 (Project Agreement §9) bersifat **hipotesis kerja**, bukan klaim (CF-05).

## 3. Artikel 2 dan 3 (ringkas, ditunda)
- RQ3: kebocoran (Access Leakage Rate = 0 pada uji matriks peran×dokumen), efek pada jawaban, overhead.
- RQ2: exact match/galat numerik: LLM tanpa alat vs function calling vs metric engine; router P/R/F1.
Rumusan detail menunggu literatur Text-to-SQL, tool-use, dan keamanan yang belum dicari.

## 4. Pemilihan fokus (Fase 5) — skor kualitatif, rekomendasi

Skala: T tinggi, S sedang, R rendah. Penilaian berdasar bukti parsial; **bukan keputusan final.**

| Kriteria | A. Hibrida+rerank (Indonesia) | B. Komputasi deterministik | C. Retrieval sadar akses | D. Gabungan |
|---|---|---|---|---|
| Originality | S (arsitektur sudah ada lokal [12]) | S | S–T (belum terbukti) | S |
| Kekuatan gap (bukti saat ini) | S | S | Belum terbukti | S |
| Feasibility | **T** | S | S | R |
| Kebutuhan eksperimen | Sedang | Sedang | Tinggi (uji adversarial) | Sangat tinggi |
| Ketersediaan dataset | Dapat disusun (AS-07) | Perlu dataset tabular | Perlu skenario peran | Semua |
| Kompleksitas teknis | Sedang | Sedang | Tinggi | Sangat tinggi |
| Potensi publikasi | S (bergantung rigor dan dataset) | S | S–T | R untuk 2.500 kata |
| Waktu data siap | Paling awal (M4/G2) | M6 | M7 | M8 |

**Rekomendasi: Alternatif A** sebagai artikel utama, dibingkai sebagai evaluasi terkontrol berdasarkan dataset beranotasi (bukan "arsitektur baru"). C dan B menjadi artikel lanjutan. **D ditolak** untuk satu artikel ±2.500 kata. Judul sementara (Inggris): *Controlled Evaluation of Hybrid Retrieval and Neural Reranking for Indonesian Institutional Document Question Answering* — **sementara**, mengikuti hasil.
