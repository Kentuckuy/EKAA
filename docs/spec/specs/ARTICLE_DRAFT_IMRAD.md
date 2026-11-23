# ARTICLE DRAFT (IMRAD) — KERANGKA

**Versi 0.1 — 10 Nov 2026 — DRAFT. HASIL: PENDING EXPERIMENTAL RESULTS.**
Target ±2.500 kata (sesuaikan jurnal). Bahasa isi: Indonesia. Sitasi IEEE merujuk `REFERENCES_IEEE.md`.

## Title (sementara)
Controlled Evaluation of Hybrid Retrieval and Neural Reranking for Indonesian Institutional Document Question Answering

## Abstrak (templat; jangan diisi angka sebelum eksperimen)
*Latar belakang:* … *Tujuan:* … *Metode:* … *Hasil utama:* **[PENDING EXPERIMENTAL RESULTS]** *Kontribusi:* …

## 1. Pendahuluan (≈450 kata; draf awal)

Retrieval-Augmented Generation (RAG) memadukan model bahasa dengan pengambilan bukti eksternal sehingga jawaban dapat ditelusuri ke sumbernya [1]. Dalam organisasi, mutu jawaban bergantung pada mutu retrieval: dokumen yang tidak terambil tidak dapat dikutip. Studi pada teks dan tabel berbahasa Inggris melaporkan bahwa gabungan retrieval hibrida dan reranker saraf mengungguli metode satu tahap, tetapi juga bahwa BM25 dapat mengungguli retrieval dense tertentu pada domain tertentu [4]. Hasil ini menunjukkan bahwa manfaat tiap komponen perlu diuji pada korpus dan bahasa sasaran.

Penelitian berbahasa Indonesia telah menerapkan retrieval hibrida pada chatbot layanan kampus dan domain khusus [10][12][9]. Berdasarkan cuplikan yang dapat ditelaah, studi tersebut umumnya berfokus pada implementasi atau evaluasi terbatas; satu studi melaporkan peningkatan cakupan relevan pada peringkat menengah dengan ambiguitas peringkat atas yang masih tersisa [10]. **[Klaim ini harus diperiksa ulang terhadap full text dan hasil pencarian Scopus/SINTA sebelum submit.]**

Tulisan ini [akan] mengevaluasi, secara terkontrol, kontribusi masing-masing komponen (BM25, dense, fusi RRF, dan reranking) terhadap Recall@k, MRR, dan nDCG@10 pada korpus dokumen institusi berbahasa Indonesia, serta biaya latensinya. Kontribusi yang diusulkan: (i) golden dataset beranotasi dengan protokol terdokumentasi; (ii) ablasi komponen dengan uji signifikansi; (iii) pelaporan hasil negatif.

## 2. Kajian Pustaka (≈500 kata) — kerangka
2.1 RAG dan dokumen organisasi [1]. 2.2 Retrieval leksikal, dense, hibrida, RRF [4]. 2.3 Reranking [4]. 2.4 Studi RAG berbahasa Indonesia [9][10][12]. 2.5 Gap: *(isi setelah pencarian lanjutan selesai)*.

## 3. Metodologi (≈700 kata) — rujuk `RESEARCH_METHODOLOGY.md` dan `EXPERIMENTAL_DESIGN.md`
3.1 Desain. 3.2 Korpus dan golden dataset. 3.3 Kondisi C0–C5. 3.4 Metrik. 3.5 Analisis statistik. 3.6 Lingkungan dan reprodusibilitas.

## 4. Hasil dan Pembahasan (≈700 kata)
**PENDING EXPERIMENTAL RESULTS.** Tabel 1: metrik per kondisi. Tabel 2: latensi p50/p95. Gambar 1: Recall@k. Uji signifikansi. Perbandingan dengan [4] dan studi lokal. Hasil negatif dilaporkan.

## 5. Kesimpulan (≈150 kata)
Hanya temuan yang didukung data; keterbatasan (korpus tunggal, anotasi, bahasa); penelitian lanjutan (kontrol akses, komputasi deterministik).

## Daftar Pustaka
Lihat `REFERENCES_IEEE.md`.
