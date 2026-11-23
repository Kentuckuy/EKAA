# PROJECT RISK REGISTER — EKAA

| Atribut | Nilai |
|---|---|
| Versi dokumen | 1.0-draft (Gelombang 3) |
| Tanggal pembaruan | 8 November 2026 |
| Status dokumen | **READY FOR OWNER APPROVAL** |
| Dokumen terkait | `FINAL_IMPLEMENTATION_ROADMAP.md`, `FINAL_ACCEPTANCE_CRITERIA.md`, `FINAL_SECURITY_SPECIFICATION.md`, `FINAL_DECISION_REGISTER.md` |

## 1. Cara Membaca

- **Probabilitas (P)** dan **Dampak (D)**: Rendah, Sedang, Tinggi, Kritis. Penilaian ini adalah **pendapat analis** berdasarkan dokumen sumber, bukan data historis, dan ditinjau ulang pada setiap gerbang keputusan.
- **Skor** kasar: tinggi jika P Tinggi dan D Tinggi atau Kritis.
- **Pemilik:** PO = Project Owner; DEV = pengembang utama; SEC = peran keamanan (dijalankan oleh pengembang pada proyek satu orang); RES = peran peneliti.
- **Status:** OPEN, MITIGATING, ACCEPTED, CLOSED.

## 2. Ringkasan Risiko Teratas

Risiko dengan perhatian paling mendesak: RISK-01 (jadwal), RISK-02 (beban dua framework), RISK-05 (kebocoran lintas hak akses), RISK-08 (kualitas bahasa Indonesia), RISK-14 (klaim penelitian).

## 3. Register

### 3.1 Jadwal, Cakupan, dan Sumber Daya

| ID | Risiko | P | D | Mitigasi | Pemicu / Indikator | Pemilik | Status |
|---|---|---|---|---|---|---|---|
| RISK-01 | Estimasi jadwal terlalu optimistis. Total MVP sekitar 29 minggu untuk satu pengembang, lebih panjang daripada enam bulan pada konsep awal, padahal cakupan sudah dipangkas | Tinggi | Tinggi | Kalibrasi ulang pada G1 setelah M1; pangkas cakupan melalui Owner; jaga disiplin DoD agar tidak ada utang tersembunyi | Realisasi M1 melebihi 4 minggu | PO, DEV | OPEN |
| RISK-02 | Beban pemeliharaan dua framework PHP melebihi manfaat (dua toolchain, dua pipeline, kontrak API) | Sedang | Sedang | CodeIgniter dibatasi pada Connector & Sync; uji kontrak otomatis; tinjauan formal pada G3 dengan fallback C | Waktu perbaikan konektor lebih besar dari kontribusinya; perubahan kontrak sering | PO, DEV | OPEN |
| RISK-03 | Scope creep: ide konsep awal (WhatsApp, anomali, peramalan, SSO) masuk ke MVP | Sedang | Tinggi | Daftar Out of Scope dan Phase 2/3 tertulis; perubahan lewat kontrol perubahan (bagian 3 pada `FINAL_PROJECT_AGREEMENT.md`) | Permintaan fitur baru di tengah milestone | PO | OPEN |
| RISK-04 | Bus factor satu orang: kehilangan waktu atau ketersediaan pengembang menghentikan proyek | Sedang | Tinggi | Dokumentasi dan README sejak awal; repositori terstruktur; pekerjaan kecil dan sering di-commit | Beban kerja lain (studi, pekerjaan) meningkat | PO | OPEN |

### 3.2 Keamanan dan Privasi

| ID | Risiko | P | D | Mitigasi | Pemicu / Indikator | Pemilik | Status |
|---|---|---|---|---|---|---|---|
| RISK-05 | Kebocoran dokumen lintas hak akses lewat retrieval, cache, atau ACL yang basi (DEC-025) | Sedang | Kritis | Filter ACL pada retrieval; pencabutan sinkron; verifikasi sitasi lapis kedua di Laravel; kunci cache memuat principals; uji matriks dan skenario pencabutan; target nol | Satu kebocoran pada uji saja sudah memicu perbaikan sebelum lanjut | SEC | OPEN |
| RISK-06 | Prompt injection dari isi dokumen mengubah perilaku atau mengeksfiltrasi data | Sedang | Tinggi | Bagian 6 `FINAL_SECURITY_SPECIFICATION.md`: hak alat minimum, tanpa konten eksternal, sitasi wajib, rangkaian uji | Tingkat keberhasilan serangan pada rangkaian uji di atas ambang yang ditetapkan | SEC | OPEN |
| RISK-07 | Data sensitif dikirim ke penyedia LLM eksternal; pelanggaran privasi atau UU PDP | Sedang | Tinggi | Opsi LLM lokal; masking opsional; konfigurasi per instalasi; tinjauan hukum; demo memakai korpus yang boleh dipublikasikan (AS-07) | Klien melarang data keluar; penyedia mengubah kebijakan | PO, SEC | OPEN |
| RISK-18 | Rahasia atau kunci bocor (repositori, log, image) | Rendah | Tinggi | Pemindaian rahasia di CI; rahasia di luar repositori; rotasi terjadwal | Temuan pemindai | SEC | OPEN |
| RISK-19 | Kerentanan rantai pasok pada paket PHP, Python, JavaScript, image Docker | Sedang | Sedang | Lockfile; pemindaian dependensi; versi dikunci; pembaruan terjadwal | Peringatan kerentanan kritis | SEC | OPEN |
| RISK-20 | Cadangan ada tetapi tidak dapat dipulihkan | Rendah | Tinggi | Uji restore terjadwal (AC bagian 6); RPO/RTO ditetapkan | Uji restore gagal | DEV | OPEN |
| RISK-21 | Interpretasi UU PDP tidak tepat; klaim kepatuhan prematur | Sedang | Tinggi | Pemetaan teknis saja; tinjauan hukum sebelum klaim; tidak menyebut "patuh UU PDP" tanpa tinjauan | Klien meminta pernyataan kepatuhan | PO | OPEN |

### 3.3 Teknis dan AI

| ID | Risiko | P | D | Mitigasi | Pemicu / Indikator | Pemilik | Status |
|---|---|---|---|---|---|---|---|
| RISK-08 | Kualitas LLM dan embedding untuk bahasa Indonesia tidak memenuhi target | Sedang | Tinggi | Evaluasi kandidat pada golden dataset sebelum mengunci (DEC-013, DEC-014); antarmuka LLM dapat ditukar; target ditetapkan dari baseline | Recall atau faithfulness jauh di bawah hipotesis pada baseline | DEV, RES | OPEN |
| RISK-09 | ANN pgvector dengan filter ACL selektif mengembalikan hasil kurang atau recall turun | Sedang | Tinggi | Uji pada M0 (AC-M0-06); iterative scan atau kandidat lebih besar; Qdrant sebagai pemicu migrasi (DEC-008) | Recall pada filter selektif jauh di bawah tanpa filter | DEV | OPEN |
| RISK-10 | Pencarian leksikal bawaan PostgreSQL (`ts_rank`, bukan BM25 murni, tanpa konfigurasi bahasa Indonesia) menurunkan kualitas hibrida | Sedang | Sedang | DEC-029: baseline lalu evaluasi ekstensi BM25 pada M0/M1; ablasi dicatat | Ablasi menunjukkan leksikal tidak menambah nilai | DEV, RES | OPEN |
| RISK-11 | Halusinasi atau jawaban salah dipercaya pengguna | Sedang | Tinggi | Sitasi wajib, abstain, tampilkan sumber, umpan balik; verifikasi sitasi (Phase 2) | Faithfulness atau akurasi sitasi rendah | DEV | OPEN |
| RISK-12 | Kesalahan angka karena parameter metrik salah dipilih oleh LLM (meski fungsi hitung benar) | Sedang | Tinggi | Validasi parameter terhadap skema dan kamus data; tampilkan rumus dan parameter; klarifikasi bila ambigu; ukur akurasi pemilihan metrik | Ketidaksesuaian pemilihan metrik pada evaluasi | DEV | OPEN |
| RISK-13 | Parsing PDF dan tabel kompleks buruk; OCR ditunda ke Phase 2 | Tinggi | Sedang | MVP dibatasi PDF berteks; batasan dinyatakan jujur; OCR dan tabel pada FR-042 | Korpus klien didominasi pindaian | DEV | ACCEPTED (untuk MVP) |
| RISK-22 | Dukungan versi tidak lengkap atau berubah: paket PHP belum mendukung PHP 8.4; dukungan PHP 8.5 pada CodeIgniter belum terverifikasi; Laravel 14 (perkiraan Q1 2027) mensyaratkan PHP 8.4 | Sedang | Sedang | Kunci versi pada M0; tidak migrasi mayor selama MVP (DEC-017); periksa kompatibilitas paket | Paket kunci tidak mendukung PHP 8.4 | DEV | OPEN |
| RISK-23 | Beda lingkungan: pengembangan Windows/Laragon vs produksi Linux/Docker | Sedang | Sedang | Pengembangan dan CI berbasis Docker; hindari ketergantungan path dan OS | Bug hanya muncul di satu lingkungan | DEV | OPEN |
| RISK-24 | Kebutuhan perangkat keras LLM lokal belum diketahui; demo atau klien tidak mampu | Sedang | Sedang | Mock LLM untuk uji; LLM komersial untuk demo; ukur kebutuhan sebelum menjanjikan opsi lokal | Model lokal terlalu lambat pada mesin demo | DEV, PO | OPEN |
| RISK-25 | Biaya dan latensi LLM tidak dapat diterima | Sedang | Sedang | Rate limit berlapis; batas konteks; cache (Phase 2); ukur biaya per pertanyaan | Biaya per pertanyaan di atas anggaran | PO | OPEN |
| RISK-26 | Ketidakkonsistenan antar-layanan (kontrak, skema, ACL) menyebabkan data yatim | Sedang | Sedang | Uji kontrak CI; rekonsiliasi harian; migrasi expand-then-contract | Temuan rekonsiliasi | DEV | OPEN |

### 3.4 Penelitian dan Integritas Akademik

| ID | Risiko | P | D | Mitigasi | Pemicu / Indikator | Pemilik | Status |
|---|---|---|---|---|---|---|---|
| RISK-14 | Klaim kebaruan (novelty) berlebihan atau tidak didukung tinjauan pustaka | Sedang | Tinggi | Matriks literatur terverifikasi; rumusan kontribusi yang konservatif; reviewer-minded self-audit | Studi terdahulu serupa ditemukan belakangan | RES | OPEN |
| RISK-15 | Golden dataset bias, terlalu kecil, atau bocor ke penyetelan (test-set leakage) | Sedang | Tinggi | Pembagian dev/test; pembekuan versi; pedoman anotasi; kesepakatan antar-penilai; ukuran ditinjau pada M0 | Skor tidak stabil antar-versi dataset | RES | OPEN |
| RISK-16 | Eksperimen tidak dapat direproduksi (model API berubah, seed tidak dicatat) | Sedang | Tinggi | Catat versi model, prompt, parameter, seed; simpan keluaran mentah; gunakan model lokal untuk pembanding bila perlu | Hasil berubah pada pengulangan | RES | OPEN |
| RISK-17 | Referensi tidak valid atau jurnal tidak terindeks sesuai klaim | Rendah | Tinggi | Verifikasi DOI, penerbit, status indeksasi sebelum dikutip; tidak ada referensi tanpa verifikasi | Referensi tidak dapat diverifikasi | RES | OPEN |
| RISK-27 | Penilaian LLM-judge bias atau tidak andal sebagai ukuran faithfulness | Sedang | Sedang | Periksa manual sebagian sampel; laporkan keterbatasan; gunakan beberapa ukuran | Perbedaan besar antara penilaian manual dan otomatis | RES | OPEN |
| RISK-28 | Lisensi atau kerahasiaan dokumen korpus menghambat publikasi dataset | Sedang | Sedang | Hanya dokumen publik atau sintetis (AS-07); catat sumber dan lisensi | Dokumen tanpa kejelasan lisensi | RES, PO | OPEN |
| RISK-29 | Sistem dianggap "berhasil" karena tampilan, bukan bukti (status palsu) | Sedang | Tinggi | DoD global; kriteria penerimaan berbasis bukti; tidak ada klaim tanpa laporan | Item ditandai selesai tanpa bukti uji | PO, DEV | OPEN |

## 4. Heat Map Ringkas (Berdasarkan Penilaian di Atas)

| | Dampak Sedang | Dampak Tinggi | Dampak Kritis |
|---|---|---|---|
| **P Tinggi** | RISK-13 | RISK-01 | — |
| **P Sedang** | RISK-02, 10, 19, 22, 23, 24, 25, 26, 27, 28 | RISK-03, 04, 06, 07, 08, 09, 11, 12, 14, 15, 16, 21, 29 | RISK-05 |
| **P Rendah** | — | RISK-17, 18, 20 | — |

## 5. Proses Tinjauan

- Risiko ditinjau pada setiap gerbang keputusan (G0–G4) dan setiap akhir milestone.
- Risiko baru, perubahan skor, dan penutupan dicatat di bagian riwayat.
- Risiko berstatus Tinggi/Kritis yang terpicu menghentikan perpindahan ke milestone berikutnya sampai ada keputusan Owner.

## 6. Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0-draft | 2026-11-08 | Penyusunan awal Gelombang 3 (29 risiko) |
