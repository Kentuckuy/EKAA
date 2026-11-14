# Laporan Pengujian Identitas, Otorisasi, & Audit — Milestone M1

| Atribut | Nilai |
|---|---|
| Milestone | **M1 (Identitas, Peran, Audit)** |
| Standar Rujukan | §9, §16.3, §17.3, §29.2, §30.3, DEC-019, DEC-031, DEC-047 |
| Tanggal Pengujian | 15 November 2026 |
| Pelaksana | Antigravity AI Coding Agent |
| Status Keseluruhan | **LULUS 100% (Seluruh Kriteria AC-M1 Terpenuhi dengan Bukti)** |

---

## 1. Ringkasan Eksekutif & Hasil AC-M1

| ID Kriteria | Aspek yang Diuji | Hasil Uji Nyata | Status |
|---|---|---|---|
| **AC-M1-01** | Pesan Galat Login Netral | Percobaan login dengan email tidak terdaftar dan kata sandi salah menghasilkan pesan error identik (`Kredensial yang dimasukkan tidak valid.`). *Zero user enumeration leak*. | **LULUS** |
| **AC-M1-02** | Pembatasan Percobaan Login | Rate limit mengunci setelah percobaan ke-5 dalam rentang 1 menit per kombinasi IP dan akun. | **LULUS** |
| **AC-M1-03** | Token Berputar & Deteksi Reuse | Refresh token berputar setiap kali digunakan. Replay / penggunaan ulang refresh token lama langsung mencabut seluruh rantai token pengguna tersebut (*token family revocation*). | **LULUS** |
| **AC-M1-04** | Matriks Otorisasi Pengguna | Hanya peran `org_admin` yang dapat mengelola pengguna, peran, dan unit. Peran lain (`knowledge_manager`, `data_analyst`, `standard_user`) ditolak (403/Forbidden). | **LULUS** |
| **AC-M1-05** | Penonaktifan Pengguna | Pengguna nonaktif (`is_active = false`) langsung ditolak login dan seluruh token lamanya tidak berlaku. | **LULUS** |
| **AC-M1-06** | Masking PII pada Audit Log | Data pribadi sensitif seperti NIK 16-digit (`320101********01`) dan email pengguna disamarkan otomatis sebelum dicatat ke `core.audit_logs`. | **LULUS** |
| **AC-M1-07** | Perlindungan Append-Only Audit | Role database aplikasi `ekaa_core` terbukti secara teknis tidak memiliki izin `UPDATE`, `DELETE`, atau `TRUNCATE` pada tabel `core.audit_logs`. | **LULUS** |
| **AC-M1-08** | Perlindungan Admin Terakhir | Sistem menolak penonaktifan atau penghapusan peran `org_admin` jika hanya tersisa 1 admin aktif dalam sistem. | **LULUS** |
| **AC-M1-09** | Siklus Kata Sandi Sementara | Pengguna baru dengan status `must_change_password = true` dibatasi hanya dapat mengakses rute `/auth/change-password` sebelum dapat mengakses fitur lain. | **LULUS** |
| **DD-14** | Ekspansi Principals Unit Induk | Hak akses pengguna secara otomatis mewarisi unit kerja langsung dan seluruh unit induk/leluhurnya (*parent & ancestor units*) secara rekursif tanpa risiko perulangan (*cycle*). | **LULUS** |

---

## 2. Checkpoint G1 (§33.3)

Sesuai aturan Dokumen Word §33.3:
- **Status Durasi M1:** M1 diselesaikan dalam iterasi pengembangan terarah tanpa melampaui batas kalibrasi 4 minggu.
- **Rekomendasi Cakupan:** Tidak diperlukan pemangkasan cakupan untuk fase berikutnya. Milestone M2 (Dokumen dan Izin) siap dimulai.
