# Laporan Evaluasi Perbandingan Pustaka Parser PDF — AC-M3-09

| Atribut | Nilai |
|---|---|
| Milestone | M3 (Ingestion dan Indeks) |
| Standar Rujukan | §20.2, DEC-039, AC-M3-09 |
| Pustaka Dievaluasi | `pypdf` (v5.3.0) vs `pdfplumber` (pdfminer.six) |
| Pustaka Dikecualikan | `PyMuPDF` / `fitz` (**DILARANG KARENA LISENSI AGPL-3.0**, DEC-039) |
| Tanggal | 15 November 2026 |
| Status | **LULUS (Pustaka Terpilih & Dicatat)** |

---

## 1. Analisis Perbandingan Teknis & Lisensi

| Kriteria | `pypdf` (v5.x) | `pdfplumber` (pdfminer.six) | `PyMuPDF` (fitz) |
|---|---|---|---|
| **Lisensi** | **BSD-3-Clause** (Aman Enterprise) | **MIT** (Aman Enterprise) | **AGPL-3.0** (Dilarang tanpa persetujuan Owner, OA-07) |
| **Karakteristik Lisensi** | Bebas copyleft, sangat aman untuk single-tenant deployment | Bebas copyleft | *Viral copyleft* — mewajibkan rilis kode sumber aplikasi jika terintegrasi langsung |
| **Preservasi Urutan Baca** | Baik pada dokumen teks terstruktur, perbaikan hyphenation | Sangat baik pada layout kolom rumit | Sangat baik |
| **Kecepatan Parsing** | Sangat cepat (~5-10x dibanding pdfminer) | Cenderung lebih lambat karena analisis spasial bounding box | Cepat (C library) |
| **Konsumsi Memori** | Rendah (~15-30 MB per berkas standar) | Sedang hingga tinggi pada dokumen > 100 halaman | Rendah |
| **Deteksi Halaman Kosong (OCR Needed)** | Mudah dihitung dari panjang teks halaman (`len(text) < 10`) | Mudah melalui analisis karakter | Mudah |

---

## 2. Keputusan Pemilihan Parser PDF (DEC-039)

1. **Parser Utama Ingestion:**
   - Menggunakan **`pypdf`** sebagai engine ekstraksi teks PDF utama di `services/ai/ekaa_ai/domain/ingestion/parsers/pdf_parser.py`.
   - Alasan: Lisensi BSD-3-Clause yang permisif, kecepatan ekstraksi tinggi, konsumsi memori rendah, serta kemampuan membersihkan sambungan kata (*hyphenation*) antar baris bahasa Indonesia.
2. **Penanganan Halaman Non-Teks:**
   - Halaman dengan karakter teks < 10 karakter ditandai dengan flag `needs_ocr = True` dan dilaporkan ke pengguna sebagai peringatan. Fitur OCR ditunda ke Phase 2 (FR-042).
3. **Pengecualian PyMuPDF:**
   - PyMuPDF secara tegas dikecualikan dari basis kode untuk menjamin integritas lisensi monorepo.
