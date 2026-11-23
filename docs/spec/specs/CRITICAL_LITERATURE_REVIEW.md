# CRITICAL LITERATURE REVIEW — EKAA (PROVISIONAL)

| Atribut | Nilai |
|---|---|
| Versi | 0.1 — 10 Nov 2026 |
| **Status** | **DRAFT PROVISIONAL.** Berdasar 15 entri di `LITERATURE_REVIEW_MATRIX.md`, hanya cuplikan/abstrak. Bukan tinjauan lengkap |

Nomor [n] merujuk `REFERENCES_IEEE.md`. Tanda **[BELUM DICARI]** berarti topik belum didukung literatur.

## 1. Perkembangan RAG
RAG berawal dari penggabungan memori parametrik dan non-parametrik untuk tugas intensif-pengetahuan [1]. Karya ini berfokus pada korpus Wikipedia dan tidak membahas kontrol akses atau dokumen organisasi. Evaluasi RAG modern (faithfulness, citation accuracy) **[BELUM DICARI]** sebagai artikel primer.

## 2. Sparse vs dense vs hibrida
Pracetak yang terbaca [4][6][7] konsisten melaporkan bahwa hibrida ≥ satu-satunya metode, tetapi [4] juga melaporkan BM25 dapat mengungguli dense SOTA pada dokumen teks+tabel. Artinya **arah efek tidak universal**; bergantung domain. Ini mendukung perlunya eksperimen RQ1 pada korpus sendiri, bukan asumsi. Seluruhnya pracetak berbobot B.

## 3. Reranking
Pipeline dua tahap hibrida+neural reranker melampaui metode satu tahap pada [4] (Recall@5 0,816; MRR@3 0,605). Studi lokal [10] menandai ambiguitas peringkat atas pada hibrida, yang secara logis memotivasi reranking, tetapi ini interpretasi saya, bukan klaim [10].

## 4. Dokumen berbahasa Indonesia
Ada banyak implementasi chatbot RAG kampus dan layanan [9][10][11][12][13]. Dari cuplikan, mayoritas **implementasi atau studi kasus**; ketersediaan ablasi terkontrol, golden dataset publik, dan statistik signifikansi **belum dapat dipastikan** tanpa full text. [11] (BM25 vs multilingual-E5 vs RRF) adalah pesaing terdekat dan wajib dibaca penuh.

## 5. Keamanan retrieval organisasi
Sumber ilmiah yang terbaca hanya [8] (confused deputy, konseptual). Selebihnya praktik industri (blog), tidak dapat dikutip. Dugaan bahwa evaluasi kuantitatif kebocoran akses minim di literatur ilmiah **belum terbukti**: pencarian saya sempit dan tidak menjangkau Scopus/ACM/IEEE.

## 6. Text-to-SQL — **[BELUM DICARI]**
(Phase 2 EKAA; tidak menjadi fokus artikel pertama.)

## 7–9. Akurasi numerik LLM dan komputasi deterministik
PAL [3] dan Calc-X [14] menunjukkan bahwa LLM rentan galat pada perhitungan eksak dan bahwa delegasi ke interpreter/kalkulator membantu pada tugas matematika. Keterbatasan: tidak menyelesaikan salah-tafsir pertanyaan; domain soal matematika, bukan metrik analitik organisasi berbahasa Indonesia. Karena perhitungan EKAA berupa fungsi metrik teruji (bukan kode bebas buatan LLM), temuan ini hanya analog, bukan pembanding langsung.

## 10. Routing
Adaptive-RAG [2] merutekan berdasarkan kompleksitas; [5] menunjukkan fitur TF-IDF cukup kompetitif pada klasifikasi tipe kueri. Kelas router EKAA (dokumen/analitik/di luar cakupan) berbeda dari kelas pada keduanya; ini **peluang**, tetapi pencarian "multi-source QA dokumen+tabular" belum dilakukan.

## 11. Ringkasan kekuatan bukti

| Topik | Bukti | Kesimpulan sementara |
|---|---|---|
| Hibrida > tunggal | Pracetak konsisten, tidak universal | Perlu diuji, tidak boleh diasumsikan |
| Reranking meningkatkan | 1 pracetak kuat | Perlu diuji |
| Studi Indonesia | Banyak implementasi, rigor belum diketahui | Gap harus disempitkan |
| Kebocoran akses | Minim sumber ilmiah terbaca | Belum bisa disimpulkan |
| Hitungan deterministik | Mapan pada matematika | Gap ada di konteks analitik organisasi (hipotesis) |
