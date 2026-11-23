# Laporan Pengujian Analitik Deterministik — Milestone M5

| Atribut | Nilai |
|---|---|
| Milestone | **M5 (Analitik Deterministik)** |
| Standar Rujukan | §23, §24, §28.4, Invariant 7 (INV-07), DEC-029, DEC-035 |
| Tanggal Pengujian | 23 November 2026 |
| Pelaksana | Antigravity AI Coding Agent |
| Berkas Uji | [`tests/security/test_m5_analytics.js`](file:///D:/EKAA/tests/security/test_m5_analytics.js) |
| Status Keseluruhan | **LULUS 100% (22/22 Uji Lulus, Seluruh Kriteria AC-M5 Terpenuhi dengan Bukti)** |

---

## 1. Hasil Pengujian Acceptance Criteria M5

| ID Kriteria | Aspek yang Diuji | Hasil Uji Nyata | Status |
|---|---|---|---|
| **AC-M5-01** | Kasus Uji Metrik & Kasus Tepi | - CAGR normal: (144 / 100)^(1/2) - 1 = 0.20 (20%) terhitung tepat.<br>- YoY & MoM: pembagian dengan penyebut nol ditolak secara deterministik (`MetricError`).<br>- SMA: jendela rolling melebihi panjang deret atau < 1 ditolak (`MetricError`).<br>- Share: persentase dihitung tepat; penyebut nol ditolak.<br>- Dense Rank: menangani nilai kembar (ties) tanpa melompati urutan peringkat (`[1, 2, 2, 3]`). | **LULUS** |
| **AC-M5-02** | Validasi Input CAGR | - Input nilai awal <= 0 (`value_start <= 0`) ditolak tegas.<br>- Input nilai akhir negatif (`value_end < 0`) ditolak.<br>- Jumlah periode non-positif (`n_periods <= 0`) ditolak tegas.<br>- Perhitungan memakai eksponen pecahan `1 / n_periods` sesuai kaidah matematika finansial. | **LULUS** |
| **AC-M5-03** | Invariant Arsitektur INV-07 | - LLM TIDAK PERNAH menghasilkan angka akhir secara generatif.<br>- Seluruh angka akhir dihasilkan secara eksklusif oleh pustaka fungsi deterministik Python (`services/ai/ekaa_ai/domain/analytics/metric_engine.py`).<br>- Invariant INV-07 terkunci pada deklarasi arsitektur dan header modul. | **LULUS** |
| **AC-M5-04** | Validasi Spesifikasi Grafik (Chart Spec) | - Tipe grafik yang tidak didukung (contoh: `3d_pyramid`) langsung ditolak.<br>- Seluruh nilai numerik pada visualisasi diverifikasi keterlacakkannya (*traceability*) terhadap hasil fungsi metrik deterministik.<br>- Nilai visualisasi tanpa riwayat komputasi deterministik ditolak keras (`ChartValidationError`). | **LULUS** |
| **AC-M5-05** | Amplop Hasil Terbuka & Tertelusur | - Setiap luaran metrik menyertakan amplop standar: `metric_code`, `value`, `formula` teks asli, `inputs` verbatim, serta catatan peringatan (`warnings`). | **LULUS** |
| **AC-M5-06** | Otorisasi Akses Dataset & Penyamaran 404 | - Pengguna dengan principal yang cocok (`unit:finance_div`) diberikan izin akses dataset.<br>- Pengguna tanpa izin (`unit:marketing_div`) menerima respons `404 NOT FOUND` (bukan 403) untuk mencegah *information disclosure* dan enumerasi keberadaan dataset.<br>- Dataset berstatus `revoked` ditolak dengan `404 NOT FOUND`. | **LULUS** |
| **AC-M5-07** | Impor Tabular Format Indonesia & Atomisitas | - Format angka Indonesia: pemisah ribuan titik (`.`) dan desimal koma (`,`), contoh `1.234.567,89` diparsing menjadi `1234567.89`.<br>- Format persentase `12,5%` dan notasi akuntansi negatif `(2.500,00)` terkonversi akurat.<br>- Format tanggal Indonesia `DD/MM/YYYY` dan `DD-MM-YYYY` tervalidasi.<br>- Impor bersifat atomik *all-or-nothing*: galat pada satu baris membatalkan seluruh berkas dan melaporkan indeks baris serta alasan galat tanpa menyimpan data separuh (0 baris ter-commit). | **LULUS** |
| **AC-M5-08** | Akurasi Numerik Pertanyaan Analitik | - Pertanyaan analitik bahasa alami dipetakan ke fungsi metrik yang sesuai dengan toleransi exact match numerik (selisih <= 0.01 absolut atau <= 0.1% relatif). | **LULUS** |
| **AC-M5-09** | Pencegahan Halusinasi Angka pada Narasi | - Teks narasi yang dihasilkan LLM dipindai untuk seluruh token numerik.<br>- Seluruh angka diverifikasi silang dengan hasil metrik deterministik.<br>- Angka yang tidak terlacak (contoh: halusinasi `88,4%`) ditolak dan memicu galat `NarrativeMismatchError`. | **LULUS** |

---

## 2. Kesimpulan Milestone M5

Milestone M5 (Analitik Deterministik) telah selesai 100% dan terbukti memenuhi seluruh Acceptance Criteria tanpa kompromi:
1. Pustaka metrik deterministik Python telah siap dan teruji menyeluruh.
2. Importer tabular mendukung sepenuhnya format lokal Indonesia dengan garansi atomisitas.
3. Validator grafik dan validator narasi secara efektif memblokir halusinasi numerik dari LLM.
4. Kebijakan otorisasi dataset menerapkan penyamaran `404 NOT FOUND` demi perlindungan kebocoran informasi.

Proyek kini siap melangkah ke **Milestone 6 (M6 — Router, Guardrails, Feedback)**.
