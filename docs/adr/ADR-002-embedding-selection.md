# ADR-002: Pemilihan Model Embedding & Penguncian Dimensi Vektor

| Atribut | Nilai |
|---|---|
| Status | **ACCEPTED (DEC-014, AC-M3-07)** |
| Tanggal | 15 November 2026 |
| Pengambil Keputusan | Technical Architect & AI Engineer (§21.5, DEC-014) |
| Konsultasi | Dokumen Word EKAA-MPA-2.0 §16.5, §20.4, §21.5 |

---

## 1. Konteks

Retrieval semantik pada RAG EKAA memerlukan representasi vektor berkualitas tinggi untuk dokumen institusi berbahasa Indonesia (peraturan rektor, surat keputusan, pedoman akademik, laporan anggaran). Model embedding menentukan dimensi kolom vektor pada tabel PostgreSQL `ai.chunks` dan konfigurasi indeks HNSW.

---

## 2. Kandidat Model & Evaluasi (§21.5)

Kandidat yang dievaluasi pada protokol golden dataset:
1. **BAAI/bge-m3:**
   - Dimensi: 1024
   - Kemampuan: *Dense*, *sparse* (lexical weights), dan *multi-vector* (colbert-style) dalam satu arsitektur.
   - Dukungan Bahasa: Multilingual kuat (termasuk Bahasa Indonesia) dengan konteks hingga 8192 token.
   - Lisensi: MIT.
2. **intfloat/multilingual-e5-base / large:**
   - Dimensi: 768 / 1024
   - Kemampuan: Dense retrieval kuat, membutuhkan awalan `passage: ` dan `query: `.
   - Lisensi: MIT.

---

## 3. Keputusan yang Ditetapkan

1. **Model Terpilih:**
   - **`BAAI/bge-m3`** ditetapkan sebagai model embedding standar produksi.
   - Dimensi vektor dikunci pada **1024 dimensi**.
2. **Kesesuaian dengan Batas Indeks pgvector (DEC-008):**
   - Batas dimensi HNSW pada tipe data `vector` PostgreSQL adalah 2.000 dimensi.
   - Dimensi 1024 berada di bawah batas 2.000, sehingga tipe `vector(1024)` dengan operator jarak `vector_cosine_ops` dapat diindeks penuh tanpa memerlukan kuantisasi `halfvec`.
3. **Penguncian Skema Database (AC-M3-07):**
   - Kolom `ai.chunks.embedding` dikunci pada tipe `vector(1024)`.
   - Tabel `ai.embedding_models` memuat entri resmi:
     ```json
     {
       "name": "BAAI/bge-m3",
       "dimension": 1024,
       "metric": "cosine",
       "is_active": true
     }
     ```
4. **Mock Provider untuk CI:**
   - Dalam lingkungan pengujian otomatis CI tanpa GPU, sistem menggunakan `MockEmbeddingProvider(dimension=1024)` yang menghasilkan vektor deterministik 1024-dimensi dinormalisasi L2.
