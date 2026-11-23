# Keterbatasan Sistem yang Diketahui (Known Limitations) — docs/KNOWN_LIMITATIONS.md

| Atribut | Nilai |
|---|---|
| Proyek | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Terakhir Diperbarui | 24 November 2026 (Milestone M8 — Rilis MVP) |
| Prinsip | Transparansi Penuh (§3.4, §38, AC-M8-06) |

Dokumen ini mencatat secara jujur seluruh keterbatasan fungsional dan teknis yang sengaja belum atau tidak diimplementasikan pada fase MVP sesuai keputusan arsitektur (DEC-020):

---

## 1. Keterbatasan Pemrosesan Dokumen & Ingestion

1. **Belum Ada OCR (Optical Character Recognition):**
   - Halaman PDF hasil pemindaian (*scanned images*) tanpa lapisan teks tidak dapat diekstraksi pada MVP. Sistem menandainya sebagai peringatan `needs_ocr` dan melewati halaman tersebut. Fitur OCR ditunda ke Phase 2 (FR-042).
2. **Tabel PDF Sangat Kompleks:**
   - Ekstraksi tabel PDF dengan sel bertingkat (*multi-row merge*) atau garis batas tidak beraturan dapat menghasilkan teks linier. Untuk analitik kuantitatif presisi, data harus diimpor sebagai dataset CSV/XLSX ke jalur Analytics Assistant (Bagian 23).
3. **Format Berkas Terbatas pada MVP:**
   - Format yang didukung pada MVP adalah PDF berteks, DOCX, XLSX, dan CSV. Format dokumen lain (PPTX, EPUB, RTF) belum didukung.

---

## 2. Keterbatasan Jalur Analitik (Analytics Assistant)

1. **Text-to-SQL Tidak Ada pada MVP:**
   - Pertanyaan analitik dijawab secara eksklusif menggunakan katalog fungsi metrik teruji (`cagr`, `yoy`, `mom`, `moving_average`, `share`, `rank`, `aggregate`, `delta`).
   - Sistem menolak sintesis kueri SQL sembarang dari LLM untuk mencegah injeksi dan kesalahan numerik halusinasi. Text-to-SQL read-only ditunda ke Phase 2 (FR-040).
2. **Izin Data Berada pada Tingkat Dataset:**
   - Izin akses dataset diberlakukan pada level tabel/dataset utuh. Pembatasan hak akses berbasis baris (*row-level security*) ditunda ke Phase 2 (FR-046).

---

## 3. Keterbatasan Infrastruktur & Jaringan

1. **Host Docker Lokal:**
   - Lingkungan host pengembang Windows saat ini belum memiliki Docker runtime aktif. Manifest kontainer dirancang *Docker-first* dan siap dieksekusi saat Docker Desktop / WSL2 / CI runner aktif.
2. **TLS Internal Antarlayanan Ditunda:**
   - Pada MVP, komunikasi internal antarlayanan di dalam jaringan Docker internal mengandalkan isolasi bridge `data-net` dan verifikasi asimetris JWT RS256 (DEC-030). TLS mTLS internal ditunda ke Phase 2 (FR-049).
3. **SSO / Keycloak Ditunda:**
   - Sistem menggunakan Laravel sebagai Identity Provider (IdP) mandiri dengan token memori dan refresh cookie HttpOnly (DEC-006, DEC-031). Integrasi OIDC/Keycloak ditunda ke Phase 3 (FR-050).
