# PREVIOUS RESEARCH COMPARISON — EKAA

**Versi 0.1 — 10 Nov 2026 — DRAFT PROVISIONAL** (cuplikan saja; sel `?` = perlu full text)

| Dimensi | Lewis [1] | Studi hibrida Indonesia [9–13] | Akarsu dkk. [4] | PAL [3] | Adaptive-RAG [2] | Usulan EKAA |
|---|---|---|---|---|---|---|
| Bahasa | Inggris | Indonesia | Inggris | Inggris | Inggris | Indonesia |
| Dokumen institusi | Tidak | Ya (kampus/layanan) | Keuangan | Tidak | Tidak | Ya (korpus kampus, AS-07) |
| BM25 + dense + RRF | Dense saja | Ya (≥1 studi) | Ya | — | — | Ya |
| Reranker cross-encoder | Tidak | Ya (≥1 studi) | Ya | — | — | Ya |
| Ablasi terkontrol per komponen | — | ? | Ya | — | — | Direncanakan |
| Golden dataset terbuka dan anotasi terdokumentasi | — | ? | benchmark publik | — | — | Direncanakan (belum ada) |
| Uji signifikansi | — | ? | Ya (bootstrap berpasangan) | — | — | Direncanakan |
| Filter hak akses sebelum pencarian | Tidak | ? | Tidak | — | — | Ya |
| Metrik deterministik | — | — | — | Interpreter | — | Fungsi metrik teruji |
| Router sumber data | — | — | Adaptif | — | Kompleksitas | Dokumen/analitik/luar cakupan |

**Pembacaan jujur:** kombinasi arsitektur "hibrida + RRF + cross-encoder" untuk chatbot kampus Indonesia **sudah ada** dalam literatur lokal [12]. Arsitektur sendiri bukan novelty. Sisa peluang terletak pada rigor evaluasi (ablasi, dataset, statistik, reproduksibilitas) dan pada kombinasi dengan kontrol akses dan komputasi deterministik, dan itu pun harus diverifikasi terhadap full text.
