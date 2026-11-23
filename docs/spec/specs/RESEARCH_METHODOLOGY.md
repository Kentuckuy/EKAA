# RESEARCH METHODOLOGY — Artikel 1 (RQ1)

**Versi 0.1 — 10 Nov 2026 — DRAFT. Fokus artikel belum disetujui Owner.**

1. **Research design:** eksperimen komparatif terkontrol, ablasi komponen, desain within-subject (kueri yang sama dijalankan pada semua kondisi).
2. **Dataset:** golden dataset dari korpus dokumen kampus yang boleh dipublikasikan atau disintesis (AS-07, dikonfirmasi Owner). Ukuran dokumen/kueri **belum ditetapkan**; usulan awal: ≥200 kueri teranotasi, divalidasi dengan analisis daya (power) setelah pilot. Jenis kueri: kata kunci/identifier (pasal, nomor SK), parafrase, multi-dokumen, tak terjawab.
3. **Pengumpulan/anotasi:** pedoman anotasi tertulis; minimal dua anotator independen pada subset; ukur Cohen's kappa; sengketa diselesaikan adjudikasi. Golden dataset v0 (M0) → v1 (M4) sesuai Roadmap.
4. **Pra-pemrosesan:** parsing, chunking (ukuran/overlap dicatat, diperlakukan sebagai parameter tetap pada ablasi inti), normalisasi teks Indonesia; dokumen sumber tidak berubah antar kondisi.
5. **Baseline:**
   - B1 leksikal. **Catatan penting:** DEC-029 menetapkan FTS PostgreSQL (`ts_rank`) yang **bukan BM25 murni** dan tanpa konfigurasi bahasa Indonesia. Kondisi berlabel "BM25" harus memakai implementasi BM25 sebenarnya (mis. pustaka BM25 atau ekstensi yang dievaluasi di M0/M1); FTS `ts_rank` dilaporkan terpisah sebagai B1b.
   - B2 dense (kandidat embedding: BGE-M3, multilingual-e5; DEC-014 PROPOSED).
6. **Metode usulan:** P1 = BM25 + dense dengan RRF; P2 = P1 + cross-encoder reranker (kandidat bge-reranker-v2-m3). Parameter RRF k dan kedalaman kandidat dicatat; k=60 dari rujukan sekunder, **diverifikasi ke makalah asli**, lalu diuji sensitivitasnya.
7. **Statistik:** paired bootstrap atau uji Wilcoxon signed-rank per metrik; ukuran efek dan interval kepercayaan; koreksi perbandingan ganda (Holm).
8. **Reprodusibilitas:** versi model dan pustaka dikunci (lockfile), seed, konfigurasi eksperimen tersimpan, dataset dan skrip evaluasi dirilis jika lisensi korpus mengizinkan.
9. **Etika:** tidak memakai data pribadi riil; korpus dipublikasikan/sintesis; anotator diberi tahu; tidak ada data pengguna. Pemetaan UU PDP belum dikaji hukum.
10. **Ancaman validitas:** korpus tunggal (generalisasi terbatas), bias anotator, kebocoran data pelatihan model ke korpus publik, pemilihan hyperparameter pada data uji (dicegah dengan dev/test split).
