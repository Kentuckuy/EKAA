# RESEARCH GAP ANALYSIS — EKAA

**Versi 0.1 — 10 Nov 2026 — DRAFT PROVISIONAL.** Semua "gap" di bawah berstatus **HIPOTESIS GAP** sampai pencarian di Scopus/SINTA/IEEE/ACM dilakukan.

| Penelitian terdahulu | Metode | Kelebihan | Keterbatasan | Research gap (hipotesis) | Peluang |
|---|---|---|---|---|---|
| RAG dasar [1] | Retriever dense + generator | Fondasi, SOTA QA | Wikipedia Inggris; tanpa akses | Tidak menjawab dokumen organisasi berizin | Konteks organisasi |
| Studi hibrida kampus Indonesia [9–13] | Hibrida/RRF/rerank dalam chatbot | Relevan bahasa dan domain | Rigor evaluasi belum diketahui; kemungkinan implementasi | Ablasi terkontrol + dataset anotasi terdokumentasi + signifikansi pada korpus institusi Indonesia | Evaluasi metodologis ketat |
| Benchmark teks+tabel [4] | 10 strategi, bootstrap | Rigor tinggi | Inggris, keuangan | Replikasi pada Indonesia belum terlihat | Replikasi + adaptasi |
| PAL/Calc-X [3] | Eksekusi program/kalkulator | Menekan galat aritmetika | Soal matematika | Metrik analitik organisasi (agregasi, persentase, periode) berbahasa Indonesia belum terlihat | Studi RQ2 |
| Adaptive-RAG / router [2] | Klasifier kompleksitas | Efisiensi | Kelas bukan jenis sumber | Router dokumen vs analitik belum terlihat | Router tiga kelas |
| ConfusedPilot [8] | Analisis ancaman | Memetakan risiko | Konseptual, kuantifikasi tidak terbaca | Pengukuran kebocoran kuantitatif (leakage rate) belum terbukti ada/tidak | Studi RQ3, perlu cek ulang |

## Status verifikasi gap yang disebut di Prompt 3 §8

| Dugaan gap | Status |
|---|---|
| Kurang evaluasi hibrida pada dokumen institusi Indonesia | **MELEMAH.** Ada ≥4 studi lokal; yang tersisa hanya rigor/dataset |
| Kurang kajian integrasi retrieval + reranking | **MELEMAH** ([4], [12]); perlu cek full text |
| Ketepatan angka LLM terbatas | **TERDUKUNG** secara umum [3], tetapi spesifik analitik organisasi belum diverifikasi |
| Perlu pemrosesan deterministik di analitik organisasi | **Hipotesis**; belum ada sumber ilmiah organisasi |
| Penggabungan pertanyaan dokumen + tabular | **BELUM DICARI** |
| Pengendalian hak akses dalam RAG | **Hipotesis**; sumber ilmiah terbaca hanya [8] |
