# Laporan Pengujian Konektor dan Sinkronisasi — Milestone M7

| Atribut | Nilai |
|---|---|
| Milestone | **M7 (Connector & Sync)** |
| Standar Rujukan | §20, §21, §22, DEC-031, DEC-037 |
| Tanggal Pengujian | 24 November 2026 |
| Pelaksana | Antigravity AI Coding Agent |
| Berkas Uji | [`tests/security/test_m7_connector.js`](file:///D:/EKAA/tests/security/test_m7_connector.js) |
| Status Keseluruhan | **LULUS 100% (17/17 Uji Lulus, Seluruh Kriteria AC-M7 Terpenuhi dengan Bukti)** |

---

## 1. Hasil Pengujian Acceptance Criteria M7

| ID Kriteria | Aspek yang Diuji | Hasil Uji Nyata | Status |
|---|---|---|---|
| **AC-M7-01** | Pemindaian Folder & Deteksi Perubahan | - Pemindai direktori rekursif berhasil mendeteksi berkas baru, berkas yang diubah (perubahan nilai *hash* SHA-256), berkas yang dihapus dari disk, dan berkas tanpa perubahan (*unchanged*).<br>- Berkas tersembunyi (awalan `.`) dan berkas sementara Office (awalan `~$`) diabaikan secara otomatis. | **LULUS** |
| **AC-M7-02** | Status Terstruktur per Berkas & Ringkasan | Setiap berkas yang diproses memperoleh status biner terstruktur (`success`, `failed`, `skipped`) disertai ringkasan kuantitatif (`total_scanned`, `created`, `updated`, `deleted`, `unchanged`, `failed`). | **LULUS** |
| **AC-M7-03** | Sinkronisasi Penghapusan ke Core | Berkas yang terhapus pada sistem sumber dipancarkan ke Laravel Core dengan aksi `action: 'delete'` beserta *hash* lama agar status dokumen di Core diperbarui menjadi nonaktif/terhapus tanpa menghapus riwayat audit. | **LULUS** |
| **AC-M7-04** | Isolasi Kegagalan (*Fault Isolation*) | Kegagalan I/O atau transmisi pada satu berkas (contoh: berkas rusak) tidak menghentikan pemrosesan berkas-berkas lain dalam *batch*. Berkas yang bermasalah dicatat dengan status `failed`, sementara berkas sesudahnya tetap berhasil disinkronkan. | **LULUS** |
| **AC-M7-05** | Keamanan Handoff & Kunci Idempotensi | - Transmisi ke endpoint Core diamankan dengan *Service JWT* asimetris (RS256, TTL <= 5 menit, *audience* `ekaa-core`, *identifier* anti-replay `jti`).<br>- Header `Idempotency-Key` bernilai deterministik berbasis `sha256(source_id + path + hash + action)` untuk menjamin tidak terjadinya duplikasi data saat percobaan ulang jaringan. | **LULUS** |
| **AC-M7-06** | Riwayat & Ketertelusuran Eksekusi | Setiap siklus sinkronisasi menerbitkan pengidentifikasi unik `run_id` dan mencatat stempel waktu awal/akhir untuk kebutuhan ketertelusuran operasional. | **LULUS** |
| **AC-M7-07** | Pembatasan Laju & Jeda Debounce | Permintaan sinkronisasi berulang dalam rentang waktu jeda (*debounce interval* < 60 detik) ditolak dengan respons `429 TOO_MANY_REQUESTS` dan menyertakan header/properti `retry_after`. Permintaan setelah interval jeda diizinkan berjalan normal. | **LULUS** |

---

## 2. Kesimpulan Milestone M7

Milestone M7 (Connector & Sync) telah selesai 100%:
1. Pemindai folder lokal bekerja deterministik dengan deteksi konten berbasis SHA-256.
2. Isolasi kegagalan menjamin kestabilan pemrosesan *batch* berkelanjutan tanpa *cascade failure*.
3. Handoff ke Laravel Core mematuhi standar keamanan ketat dengan *Service JWT* dan proteksi idempotensi.

Proyek kini siap melangkah ke tahap akhir: **Milestone 8 (M8 — Pengerasan Keamanan dan Rilis MVP)**.
