# EXPERIMENTAL DESIGN — Artikel 1

**Versi 0.1 — 10 Nov 2026 — DRAFT. Tidak ada hasil. Semua angka di bawah adalah rencana.**

| Komponen | Rencana |
|---|---|
| Kondisi | C0 FTS `ts_rank` · C1 BM25 sejati · C2 dense · C3 hibrida RRF · C4 hibrida + rerank · C5 = C4 + filter akses pra-pencarian (pengukuran overhead/kesetaraan) |
| Pembagian data | Dev (tuning k RRF, kedalaman kandidat) dan test (dikunci, dijalankan sekali per konfigurasi final) |
| Ground truth | Chunk/dokumen relevan per kueri (graded 0/1/2 untuk nDCG) |
| Metrik relevansi | Recall@{5,10,20}, MRR, nDCG@10 |
| Metrik biaya | Latensi p50/p95 per tahap dan end-to-end, throughput (k6/Locust, M8) |
| Metrik generasi (sekunder, setelah retrieval) | Faithfulness, answer relevancy, citation accuracy — alat dan prosedur penilaian **belum dipilih**; bila memakai LLM-judge, validasi dengan sampel manual |
| Pengulangan | Retrieval deterministik: 1 putaran; latensi: ≥30 putaran setelah pemanasan; generasi: ≥3 putaran/seed |
| Lingkungan | Docker Compose, server lokal/VPS (AS-06); spesifikasi CPU/GPU/RAM dicatat saat run |
| Parameter | k RRF (grid mis. {20, 60, 100}), top-N kandidat sebelum rerank, ukuran chunk (tetap) |
| Artefak | Laporan baseline per Roadmap M4 dengan konfigurasi dan seed |

**Kriteria hasil negatif:** jika C3 ≤ C2 atau C4 ≤ C3 secara tidak signifikan, hasil dilaporkan apa adanya dan manuskrip dibingkai ulang (AC §8).

**Metrik Artikel 2/3 (ditunda):** Access Leakage Rate, Unauthorized Retrieval Rate; Exact Match, galat numerik, Execution Accuracy; Precision/Recall/F1 router.
