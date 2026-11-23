# LITERATURE REVIEW MATRIX — EKAA

| Atribut | Nilai |
|---|---|
| Versi | 0.1 — 10 Nov 2026 |
| **Status** | **DRAFT PARSIAL: 15 entri terbaca metadata dasarnya; target 25–40 BELUM tercapai** |
| Aturan | Isi kolom hanya dari cuplikan yang terbaca. `—` = tidak terbaca. Scopus/SINTA = **BELUM TERVERIFIKASI** untuk semua baris |

Bobot bukti: **A** = prosiding/jurnal dengan venue terbaca; **B** = pracetak arXiv; **C** = jurnal lokal (status SINTA belum diverifikasi).

| No. | Penulis | Tahun | Judul | Venue | Scopus/SINTA | Metode | Dataset | Hasil (sebagaimana terbaca) | Keterbatasan | Relevansi EKAA | Bobot |
|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | P. Lewis dkk. | 2020 | Retrieval-Augmented Generation for Knowledge-Intensive NLP Tasks | NeurIPS 2020 (arXiv:2005.11401) | N/A (konferensi) | Memori parametrik + indeks vektor Wikipedia; dua formulasi RAG | Tugas QA domain terbuka (rinci: —) | SOTA pada tiga tugas QA domain terbuka; generasi lebih spesifik dan faktual dari baseline parametrik | Korpus Wikipedia berbahasa Inggris; tanpa kontrol akses | Fondasi RAG | A |
| 2 | S. Jeong dkk. | 2024 | Adaptive-RAG: Learning to Adapt Retrieval-Augmented LLMs through Question Complexity | NAACL 2024 (ACL Anthology 2024.naacl-long.389) | N/A (konferensi) | Pengklasifikasi kompleksitas (T5-Large) memilih tanpa/satu/multi-langkah retrieval | QA domain terbuka single-/multi-hop | Efisiensi dan akurasi lebih baik dari baseline adaptif | Rute berdasar kompleksitas, bukan jenis sumber (dokumen vs data tabular) | Pembanding router (RQ-router) | A |
| 3 | L. Gao dkk. | 2022 | PAL: Program-aided Language Models | arXiv:2211.10435; ICML (dilaporkan, **verifikasi prosiding**) | N/A | LLM menulis program, interpreter Python menghitung | Tugas penalaran/aritmetika (GSM8K dkk.) | Lebih akurat dari CoT; +15 poin absolut pada GSM8K (dilaporkan ringkasan sekunder) | Tidak memperbaiki salah paham soal; domain soal matematika, bukan analitik organisasi | Dasar RQ2 | B/A |
| 4 | — (belum terbaca) | 2023 | Calc-X / Calcformers (arXiv:2305.15017) | arXiv | N/A | Kalkulator terintegrasi + fine-tuning | >300.000 sampel aritmetika | Model berkalkulator "sekitar dua kali" akurasi (cuplikan terpotong) | Penulis/judul lengkap belum diverifikasi | RQ2 (LLM lemah pada hitungan eksak) | B |
| 5 | M. Akarsu, R. K. Karaman, C. Mierbach | 2026 | From BM25 to Corrective RAG: Benchmarking Retrieval Strategies for Text-and-Table Documents | arXiv:2604.01733 | N/A | 10 strategi (sparse, dense, hibrida, rerank, dll.) | QA keuangan teks+tabel | Hibrida+rerank dua tahap: Recall@5 0,816; MRR@3 0,605; dilaporkan BM25 mengungguli dense SOTA (cuplikan terpotong) | Pracetak; Inggris; domain keuangan | RQ1; dokumen+tabel | B |
| 6 | — | 2026 | StratRAG: Multi-Hop Retrieval Evaluation Dataset for RAG | arXiv:2604.22757 | N/A | BM25 vs Dense (MiniLM) vs Hibrida | Dataset multi-hop (penulis tunggal) | Hibrida > BM25 dan dense pada 5 metrik (mis. Recall@5 0,9050 vs 0,8600 dense) | Pracetak, skala kecil, Inggris | RQ1 (arah efek) | B (bobot rendah) |
| 7 | — | 2024 | The Chronicles of RAG: The Retriever, the Chunk and the Generator | arXiv:2401.07883 | N/A | BM25 (Pyserini) + embedding ADA-002/kustom, RRF | — | Kombinasi hibrida terbaik: BM25 + ADA-002 kustom | Penulis belum diverifikasi; sistem berbasis API komersial | RQ1 | B |
| 8 | — | 2024 | ConfusedPilot: Confused Deputy Risks in RAG-based LLMs | arXiv:2408.04870 | N/A | Analisis ancaman | — | RAG rentan "confused deputy"; kurang analisis insider threat | Penulis belum diverifikasi; konseptual | RQ3 | B |
| 9 | P. Bansal, S. Agarwal | 2026 | Lightweight Query Routing for Adaptive RAG: A Baseline Study on RAGRouter-Bench | arXiv:2604.03455 | N/A | 15 pasang klasifier–fitur, hanya teks kueri | RAGRouter-Bench (7.727 kueri, 4 domain) | Fitur TF-IDF mengungguli embedding sebesar 3,1 poin macro-F1 | Kelas = tipe kueri (faktual/penalaran/ringkasan), bukan sumber data | Router | B |
| 10 | Asmaidin, C. B. Santoso | 2026 | Evaluasi Metode Retrieval pada Chatbot Domain Khusus Berbasis RAG | J. Sci. Appl. Informat. 9(1):105–111 (ISSN 2614-3054) | **BELUM TERVERIFIKASI** | Evaluasi metode retrieval, pendekatan hibrida | Dokumen domain khusus (rinci: —) | Hibrida meningkatkan cakupan relevan di peringkat menengah, tetapi ambiguitas peringkat atas tetap | Angka tidak terbaca | RQ1 Indonesia; menyiratkan kebutuhan reranking | C |
| 11 | M. S. M. Fadha, Y. Ardila, N. Wahyudi | — | Comparing BM25, Multilingual-E5, and Reciprocal Rank Fusion for RAG | JAIC (e-ISSN 2548-6861), tahun/volume — | **BELUM TERVERIFIKASI** | BM25 vs multilingual-E5 vs RRF | — | — | Hasil tidak terbaca | **RQ1 sangat dekat** — wajib dibaca full text | C |
| 12 | M. D. Salman | 2026 | Implementation of RAG on LLM for Campus Service and Information Chatbot | INOVTEK Polbeng Seri Informatika 11(1) | **BELUM TERVERIFIKASI** | Hibrida dense+BM25 (RRF) + cross-encoder rerank | Informasi layanan kampus USTI | Implementasi; hasil kuantitatif tidak terbaca | Kemungkinan implementasi, tanpa ablasi (belum dipastikan) | **RQ1 sangat dekat** — arsitektur identik dengan EKAA | C |
| 13 | M. R. S. Rizqullah | — | Analisis Performa Chatbot IT Support Berbasis RAG dengan Optimasi Retrieval Bertahap | AUTOMATA (UII) | **BELUM TERVERIFIKASI** | Hibrida + reranking + cache Redis | Layanan TI kampus UII | Hasil tidak terbaca | Konteks satu unit layanan | RQ1, latensi | C |
| 14 | S. H. Firdaus | 2026 | Chatbot Hybrid Fatwa MUI Menggunakan RAG dan LLM | JUPTIK 4(1):123–129 (ISSN 2987-7016) | **BELUM TERVERIFIKASI** | Hybrid retrieval bertingkat, fallback ke 12.370 hadis | Fatwa MUI | Hasil tidak terbaca | Domain keagamaan | RQ1 Indonesia (pendukung) | C |
| 15 | — | 2026 | RAG pada domain budaya Jambi (JAIC, e-ISSN 2548-6861, hlm. ~5076) | JAIC | **BELUM TERVERIFIKASI** | RAG + evaluasi ID/OOC | Korpus budaya Jambi | Hasil tidak terbaca | Judul/penulis belum terbaca | Pendukung | C |

## Referensi sekunder (disebut dalam sumber lain, BELUM dibaca langsung)

- Cormack, Clarke, Buettcher, SIGIR 2009 — Reciprocal Rank Fusion (k = 60 pada studi pilot). **BELUM TERVERIFIKASI langsung.**
- Schick dkk., 2023 — Toolformer. **BELUM TERVERIFIKASI langsung.**
- BGE-M3 dan bge-reranker-v2-m3 — hanya terlihat sebagai nama model pada sumber lain.
- Pembahasan "contextual retrieval" (Anthropic) hanya terlihat via blog sekunder → **tidak dipakai sebagai rujukan ilmiah.**

## Material yang dikesampingkan sebagai rujukan

Blog dan halaman vendor tentang permission-aware RAG (mis. tulisan praktisi di DEV Community, blog konsultan) menunjukkan praktik industri, **bukan bukti ilmiah**. Tidak dikutip di manuskrip.
