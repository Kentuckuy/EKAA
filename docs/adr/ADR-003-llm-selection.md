# ADR-003: Protokol Pemilihan LLM dan Adapter Arsitektur — AC-M4-08

| Atribut | Nilai |
|---|---|
| Status | **ACCEPTED (DEC-013, DEC-033, AC-M4-08)** |
| Tanggal | 15 November 2026 |
| Pengambil Keputusan | Technical Architect (§21.5, DEC-013) |
| Konsultasi | Dokumen Word EKAA-MPA-2.0 §12.2, §21.5, §30.6 |

---

## 1. Konteks & Batasan Privasi (OA-03)

Penggunaan model bahasa besar (LLM) pada EKAA berfungsi untuk menghasilkan sintesis jawaban berbasis kutipan teks dan ekstraksi parameter kueri analitik. Sesuai kebijakan privasi dan keamanan (§30.6):
- Tidak ada data identitas pengguna yang dikirim ke LLM.
- Pencatatan prompt penuh dimatikan secara bawaan.
- LLM tidak pernah menghitung angka akhir secara langsung.

---

## 2. Bobot Kriteria Pemilihan LLM (§21.5)

Kandidat dievaluasi menggunakan bobot baku yang ditetapkan sebelum pengujian:
- **Faithfulness (Kebenaran Faktual terhadap Konteks):** 0,35
- **Akurasi Sitasi (Presisi Penunjukan Halaman/Bagian):** 0,25
- **Akurasi Abstain (`no_evidence` pada Bukti Lemah):** 0,15
- **Kualitas Bahasa Indonesia Alami:** 0,10
- **Efisiensi Biaya Token & Latensi ($p95 < 15\text{s}$):** 0,15

---

## 3. Keputusan Desain (Pola Adapter Tertukar)

1. **Antarmuka Tertukar (`LLMClient`):**
   - Menggunakan `OpenAICompatibleLLMClient` yang dapat mengarahkan panggilan ke *local endpoint* (vLLM / Ollama) atau *cloud provider* melalui konfigurasi lingkungan (`LLM_API_BASE`, `LLM_API_KEY`).
2. **Determinisme dan Reproduksibilitas:**
   - Parameter `temperature` disetel pada `0.0` sebagai nilai default awal.
   - Hash prompt (SHA-256) dicatat pada `model_info.prompt_version` untuk setiap pesan jawaban yang diterbitkan.
3. **Mock Provider untuk CI:**
   - Menyediakan `MockLLMClient` untuk eksekusi pengujian CI tanpa biaya dan tanpa dependensi jaringan eksternal.
