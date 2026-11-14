# Protokol Eksperimen Retrieval & Jawaban Bersitasi — eval/protocols/experiment_protocol.md

| Atribut | Nilai |
|---|---|
| Proyek | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Riset Terkait | Artikel 1 Track C (RQ1: Nilai Retrieval Hibrida & Cross-Encoder Reranking) |
| Dokumen Rujukan | §21.5, §35.3 Dokumen Word |

---

## 1. Kondisi Eksperimen (Ablasi C0 s.d. C3)

Untuk menguji kontribusi setiap tahapan pipeline secara empiris dan ilmiah, sistem dievaluasi pada 4 kondisi ablasi yang terkontrol ketat:

| Kondisi | Deskripsi Konfigurasi | Tujuan Pengujian |
|---|---|---|
| **C0** | **Dense Retrieval Saja** (HNSW pgvector, cosine distance) | Mengukur performa retrieval semantik murni |
| **C1** | **Leksikal Retrieval Saja** (PostgreSQL FTS `simple`) | Mengukur performa retrieval berbasis kata kunci eksak |
| **C2** | **Hibrida RRF** (Fusi Dense + Leksikal dengan $k=60$) | Mengukur efektivitas fusi peringkat timbal balik (*Reciprocal Rank Fusion*) |
| **C3** | **Hibrida + Reranker** (C2 + Cross-Encoder Reranker pada top-30) | Mengukur dampak reranker terhadap nDCG@10 dan faithfulness jawaban |

---

## 2. Metrik Pengukuran Baku (§35.3)

1. **Retrieval Performance:**
   - $\text{Recall@}k$ ($k \in \{5, 10, 20\}$)
   - Mean Reciprocal Rank ($\text{MRR}$)
   - Normalized Discounted Cumulative Gain ($\text{nDCG@10}$)
2. **Generation Quality:**
   - **Faithfulness:** Proporsi klaim jawaban yang sepenuhnya didukung oleh sitasi terverifikasi.
   - **Citation Accuracy:** Presisi dan recall penunjukan halaman/bagian dokumen yang tepat.
   - **Abstain Accuracy:** Akurasi pengembalian status `no_evidence` pada pertanyaan tanpa bukti di korpus (kategori `no_evidence`).
3. **Efisiensi Operasional:**
   - Latensi pencarian dan generasi ($p50$ dan $p95$ dalam milidetik).
   - Biaya token per pertanyaan.

---

## 3. Protokol Eksekusi & Reproduksibilitas (§35.4, NFR-REP-001)

- Setiap eksekusi evaluasi wajib menggunakan CLI runner:
  ```bash
  python -m ekaa_ai.eval --dataset eval/datasets/golden_dataset_v0.json --split dev --condition C3 --seed 42
  ```
- Seluruh keluaran mentah (*raw outputs*), versi model, versi prompt (SHA-256 hash), dan skor dicatat dalam tabel `ai.eval_runs` dan `ai.eval_results`.
