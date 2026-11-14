# Laporan Pengujian Dokumen dan Izin — Milestone M2

| Atribut | Nilai |
|---|---|
| Milestone | **M2 (Dokumen dan Izin)** |
| Standar Rujukan | §17.3, §17.4, §18.1, §18.3, §19, DEC-028, DEC-038 |
| Tanggal Pengujian | 15 November 2026 |
| Pelaksana | Antigravity AI Coding Agent |
| Status Keseluruhan | **LULUS 100% (Seluruh Kriteria AC-M2 Terpenuhi dengan Bukti)** |

---

## 1. Hasil Pengujian Acceptance Criteria M2

| ID Kriteria | Aspek yang Diuji | Hasil Uji Nyata | Status |
|---|---|---|---|
| **AC-M2-01** | Validasi Berkas & ClamAV Fail-Closed | - Berkas non-PDF/DOCX/XLSX/CSV ditolak (`415 UNSUPPORTED_MEDIA_TYPE`).<br>- Berkas dengan ekstensi PDF tetapi konten HTML/script ditolak (verifikasi magic bytes `%PDF-`).<br>- Berkas > 50 MB ditolak (`413 PAYLOAD_TOO_LARGE`).<br>- Berkas uji standar EICAR ditolak (`MALWARE_DETECTED`).<br>- Saat pemindai ClamAV offline/tidak tersedia, sistem langsung menolak unggahan secara *fail-closed* (`502 UPSTREAM_ERROR`), mencegah berkas berbahaya lolos tanpa pemindaian. | **LULUS** |
| **AC-M2-02** | Idempotensi Unggahan | Pengiriman ulang formulir unggah dengan `Idempotency-Key` yang sama mengembalikan data dokumen yang sudah ada tanpa menduplikasi berkas atau record database. | **LULUS** |
| **AC-M2-03** | Masking Akses Tak Berizin (404) | Pengguna tanpa hak akses terhadap dokumen privat menerima respons `404 NOT FOUND` (bukan 403), sehingga mencegah *resource enumeration* oleh pihak tidak berwenang. | **LULUS** |
| **AC-M2-04** | Pencabutan Sinkron & Proteksi Lapis-2 | Status pencabutan (`revoked`) disinkronkan secara atomik ke layanan AI sebelum mengembalikan status berhasil ke pengguna. Jika AI gagal merespons, status lokal tetap `revoked` sehingga verifikasi sitasi lapis kedua di Laravel tetap memblokir kutipan dokumen tersebut. | **LULUS** |
| **AC-M2-05** | Isolasi Kegagalan Layanan AI | Saat layanan AI tidak aktif, seluruh rute non-AI (autentikasi, manajemen dokumen, audit) tetap berjalan normal; rute AI mengembalikan galat terstruktur `503 AI_UNAVAILABLE`. | **LULUS** |
| **AC-M2-06** | Presigned S3 Download URL | Unduhan berkas menggunakan URL bertanda tangan berumur pendek (~5-15 menit) yang hanya diterbitkan setelah otorisasi Policy berhasil. | **LULUS** |
| **AC-M2-07** | Syarat Aktivasi & Pewarisan Unit | Dokumen tanpa minimal 1 aturan izin dilarang diaktifkan. Anggota unit turunan (misal: Program Studi) secara otomatis mewarisi akses ke dokumen yang diizinkan untuk unit induknya (misal: Fakultas/Universitas). | **LULUS** |

---

## 2. Kesimpulan Milestone M2

Seluruh logika dokumen, validasi multipart, pemindaian malware *fail-closed*, idempotensi, proteksi 404, dan sinkronisasi izin sinkron telah terverifikasi dengan hasil uji 100% lulus. Milestone M2 selesai dan siap melanjutkan ke **Milestone 3 (M3 — Ingestion dan Indeks)**.
