# RESEARCH SEARCH PROTOCOL — EKAA

| Atribut | Nilai |
|---|---|
| Versi | 0.1 |
| Tanggal pencarian | 10 November 2026 |
| **Status** | **DRAFT — pencarian awal (parsial), bukan SLR lengkap** |
| Hubungan | Track C; terpisah dari dokumen keputusan proyek (DEC-023) |

## 1. Yang benar-benar diakses

| Sumber | Status akses |
|---|---|
| Mesin pencari web umum (hasil: arXiv, ACL Anthology, NeurIPS, repositori kampus, portal jurnal OJS, blog teknis) | **DIAKSES** |
| Scopus / Scimago Source List | **TIDAK DIAKSES** |
| Portal SINTA | **TIDAK DIAKSES** |
| IEEE Xplore, ACM DL, ScienceDirect, SpringerLink, Wiley | **TIDAK DIAKSES langsung** (hanya muncul bila ada di hasil web) |
| Crossref / DOI resolver | **TIDAK DIAKSES** (DOI hanya dicatat jika tampil di sumber) |

Konsekuensi: **semua status Scopus/SINTA/quartile = BELUM TERVERIFIKASI.** Dokumen ini tidak boleh dipakai sebagai bukti indeksasi.

## 2. Kueri yang dijalankan (10 Nov 2026)

1. `Retrieval-Augmented Generation for Knowledge-Intensive NLP Tasks Lewis 2020 NeurIPS`
2. `hybrid retrieval BM25 dense reciprocal rank fusion reranking evaluation RAG`
3. `retrieval-augmented generation bahasa Indonesia dokumen institusi hybrid retrieval jurnal`
4. `access control RAG permission-aware retrieval enterprise leakage evaluation`
5. `LLM numerical reasoning tool-augmented calculator program-of-thoughts PAL Toolformer arithmetic errors`
6. `Adaptive-RAG query complexity classifier routing; BGE M3-Embedding multilingual; RAGAS evaluation arXiv`

Rentang tahun: 2020–2026. Jumlah record yang disaring tidak dihitung secara sistematis, sehingga **tidak ada diagram PRISMA** (tidak ada angka seleksi yang dibuat-buat).

## 3. Topik wajib yang BELUM dicari

Text-to-SQL dan Table QA (kelompok E); evaluasi embedding multibahasa dan cross-encoder sebagai artikel primer (kelompok C); benchmark IR Indonesia (mis. mMARCO, IndoNLU, dan sejenisnya); RAGAS dan metrik faithfulness sebagai artikel primer; prompt injection (kelompok D); knowledge management enterprise (kelompok D); SPLADE/learned sparse. Pencarian lanjutan wajib dilakukan sebelum Fase 4 dinyatakan final.

## 4. Kriteria inklusi dan eksklusi (diterapkan sejauh pencarian ini)

| Keputusan | Kriteria |
|---|---|
| Inklusi inti | Artikel/prosiding dengan metadata terbaca (judul, venue, tahun) yang relevan ke RQ1–RQ3 |
| Inklusi terbatas | Pracetak arXiv (diberi label PRACETAK) |
| Eksklusi dari rujukan ilmiah | Blog teknis, halaman vendor, tulisan DEV Community, ringkasan agregator, skripsi/tesis (BAB I), tugas kuliah. Dipakai hanya sebagai petunjuk arah pencarian |
| Eksklusi | Tidak ada artikel dikeluarkan karena kualitas isi; seleksi berbasis kelayakan sumber |

## 5. Keterbatasan

- Analisis hanya berdasarkan abstrak/cuplikan yang tampil di hasil pencarian; **full text tidak dibaca**.
- Angka hasil hanya dicatat bila tampil di cuplikan.
- Cakupan jauh di bawah target 25–40 artikel (lihat `LITERATURE_REVIEW_MATRIX.md`).
