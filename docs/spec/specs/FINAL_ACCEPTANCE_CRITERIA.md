# FINAL ACCEPTANCE CRITERIA — EKAA

| Atribut | Nilai |
|---|---|
| Versi dokumen | 1.0-draft (Gelombang 3) |
| Tanggal pembaruan | 8 November 2026 |
| Status dokumen | **READY FOR OWNER APPROVAL** |
| Dokumen terkait | `FINAL_IMPLEMENTATION_ROADMAP.md`, `FINAL_REQUIREMENT_SPECIFICATION.md`, `FINAL_SECURITY_SPECIFICATION.md`, `PROJECT_RISK_REGISTER.md` |

## 1. Prinsip

1. Kriteria dapat **diuji dan dibuktikan** dengan artefak (log uji, laporan, tangkapan hasil), bukan pernyataan.
2. Semua angka target adalah **target awal hipotesis**. Target ditetapkan ulang pada gerbang G2 setelah baseline diukur (`FINAL_IMPLEMENTATION_ROADMAP.md` bagian 8). Perubahan target dicatat di Decision Register dengan alasan.
3. Hasil evaluasi dilaporkan apa adanya. Tidak memenuhi target bukan alasan mengubah data uji atau metrik secara diam-diam.
4. Kriteria keamanan bersifat **biner** (lulus atau gagal), tanpa toleransi, untuk kebocoran lintas hak akses.

## 2. Bukti yang Diwajibkan

| Jenis bukti | Isi minimal |
|---|---|
| Laporan uji otomatis | Commit, tanggal, daftar uji, hasil |
| Laporan evaluasi | Versi golden dataset, konfigurasi (model, parameter, seed), hasil per metrik, interval ketidakpastian bila jumlah sampel memungkinkan |
| Laporan keamanan | Skenario, hasil, temuan, status perbaikan |
| Laporan beban | Skenario, spesifikasi mesin, p50/p95, throughput, tingkat galat |

## 3. Kriteria per Milestone

### M0 — Fondasi dan Kontrak

| ID | Kriteria | Bukti |
|---|---|---|
| AC-M0-01 | `docker compose up` menjalankan seluruh layanan pada mesin bersih dan semua health check hijau | Log, tangkapan hasil |
| AC-M0-02 | Tabel versi (Python, FastAPI, PostgreSQL, pgvector, Redis, DuckDB, MinIO, Vue, ECharts, paket PHP pada PHP 8.4) terisi dari lockfile dan diverifikasi | `FINAL_TECHNOLOGY_STACK.md` terbarui |
| AC-M0-03 | Tiga schema dan tiga role database ada; setiap role terbukti tidak dapat mengakses schema lain | Uji hak akses |
| AC-M0-04 | OpenAPI Laravel–AI dan Laravel–Connector ada dan stub lulus uji kontrak | Laporan uji kontrak |
| AC-M0-05 | JWT antarlayanan: token valid diterima; kedaluwarsa, `aud` salah, dan `jti` ulang ditolak | Laporan uji |
| AC-M0-06 | Laporan uji pgvector: perilaku ANN dengan filter ACL selektif terukur dan strategi mitigasi dipilih | Laporan |
| AC-M0-07 | Perbandingan awal FTS PostgreSQL vs ekstensi BM25 terdokumentasi dan keputusan lanjutan dicatat | Laporan, Decision Register |
| AC-M0-08 | Migrasi `up` dan `down` lulus pada ketiga schema | Log CI |

### M1 — Identitas, Peran, dan Audit

| ID | Kriteria | Bukti |
|---|---|---|
| AC-M1-01 | Login valid berhasil; kredensial salah ditolak dengan pesan yang sama untuk pengguna tidak ada dan kata sandi salah | Uji API |
| AC-M1-02 | Percobaan login berulang dibatasi sesuai konfigurasi | Uji API |
| AC-M1-03 | Token kedaluwarsa dan token yang dicabut ditolak | Uji API |
| AC-M1-04 | Hanya `org_admin` yang dapat mengelola pengguna, peran, dan unit; peran lain mendapat `403` | Matriks otorisasi otomatis |
| AC-M1-05 | Pengguna nonaktif tidak dapat login dan token lamanya ditolak | Uji API |
| AC-M1-06 | Setiap login, perubahan peran, dan perubahan pengaturan menghasilkan entri audit | Uji |
| AC-M1-07 | Role aplikasi tidak dapat `UPDATE`/`DELETE` pada `audit_logs` | Uji hak database |
| AC-M1-08 | Admin terakhir tidak dapat dinonaktifkan | Uji |

### M2 — Manajemen Dokumen dan Izin

| ID | Kriteria | Bukti |
|---|---|---|
| AC-M2-01 | Berkas dengan tipe tidak diizinkan, MIME dipalsukan, atau melebihi batas ditolak (`415`/`413`) | Uji dengan berkas uji |
| AC-M2-02 | Unggah menghasilkan dokumen, versi, entri audit, dan pekerjaan ingestion; unggah ulang dengan kunci idempoten sama tidak menggandakan | Uji |
| AC-M2-03 | Pengguna tanpa izin tidak melihat dokumen di daftar maupun detail (`404`) | Matriks peran × dokumen |
| AC-M2-04 | Perubahan izin dan status dicabut menyinkronkan ke AI sebelum respons sukses; bila sinkronisasi gagal, aksi dinyatakan gagal dan dokumen tetap ditandai dicabut di Laravel | Uji dengan stub gagal |
| AC-M2-05 | Layanan AI mati: endpoint non-AI tetap berfungsi dan endpoint AI mengembalikan `503` terstruktur | Uji kegagalan |
| AC-M2-06 | URL unduhan bertanda tangan kedaluwarsa sesuai konfigurasi dan tidak dapat dipakai pengguna lain tanpa izin | Uji |

### M3 — Ingestion dan Indeks

| ID | Kriteria | Bukti |
|---|---|---|
| AC-M3-01 | PDF berteks, DOCX, XLSX, dan CSV sampel menghasilkan chunk dengan metadata dokumen, versi, halaman, dan bagian | Uji sampel |
| AC-M3-02 | Teks chunk identik dengan teks sumber pada sampel uji (tanpa kehilangan atau pengubahan) | Uji perbandingan |
| AC-M3-03 | Setiap chunk membawa `acl_principals`, `status`, `is_current` yang sesuai izin Laravel | Uji |
| AC-M3-04 | Penggantian versi menjadikan chunk versi lama tidak muncul pada retrieval | Uji |
| AC-M3-05 | Kegagalan worker menghasilkan status `failed` dengan pesan ringkas tanpa isi dokumen, dan dapat diulang | Uji kegagalan |
| AC-M3-06 | Ingestion tidak memblokir UI; status proses terlihat | Uji E2E |
| AC-M3-07 | Model embedding dan dimensi vektor dikunci dengan dasar evaluasi pada golden dataset | Decision Register, laporan |

### M4 — Retrieval, Jawaban Bersitasi, dan Chat

| ID | Kriteria | Bukti |
|---|---|---|
| AC-M4-01 | Laporan baseline memuat Recall@k, MRR, nDCG, faithfulness, akurasi sitasi, dan ablasi dense vs hibrida vs hibrida+rerank dengan konfigurasi dan seed | Laporan |
| AC-M4-02 | Kebocoran lintas hak akses = **0** pada seluruh skenario uji akses (termasuk pencabutan izin, dokumen dicabut, versi lama) | Laporan keamanan |
| AC-M4-03 | Setiap klaim penting dalam jawaban memiliki sitasi yang dapat dibuka ke dokumen, bagian, dan halaman | Uji sampel dan metrik akurasi sitasi |
| AC-M4-04 | Pertanyaan tanpa bukti menghasilkan `no_evidence`, bukan jawaban mengarang; tingkat abstain benar diukur pada subset pertanyaan tanpa jawaban | Laporan |
| AC-M4-05 | Sitasi yang tidak lolos verifikasi Policy di Laravel dibuang dan dicatat | Uji |
| AC-M4-06 | Riwayat percakapan hanya dapat diakses pemiliknya | Uji otorisasi |
| AC-M4-07 | Target awal (ditetapkan ulang setelah baseline): Recall@10 > 0,9; faithfulness > 0,9 | Laporan; bila tidak tercapai, dicatat dan dibahas pada G2 |
| AC-M4-08 | Pemilihan LLM dicatat dengan dasar evaluasi (kualitas, biaya, latensi) | Decision Register |

### M5 — Analitik Deterministik

| ID | Kriteria | Bukti |
|---|---|---|
| AC-M5-01 | Seluruh kasus uji metrik lulus 100%, termasuk kasus tepi | Laporan uji unit |
| AC-M5-02 | CAGR memakai jumlah periode *n*, bukan selisih tahun tertulis; nilai awal nol atau negatif, periode hilang, dan satuan berbeda menghasilkan peringatan atau penolakan jelas, bukan angka salah | Kasus uji tepi |
| AC-M5-03 | Tidak ada jalur kode yang membuat LLM menghasilkan angka akhir; angka berasal dari fungsi metrik | Tinjauan kode dan uji |
| AC-M5-04 | Nilai pada grafik identik dengan hasil hitung; spesifikasi grafik tidak valid ditolak | Uji validator |
| AC-M5-05 | Setiap hasil menampilkan rumus, parameter, dan sumber dataset | Uji UI/API |
| AC-M5-06 | Pengguna tanpa izin dataset tidak dapat menanyakan atau melihat dataset itu | Matriks otorisasi |
| AC-M5-07 | Impor dengan baris galat melaporkan baris bermasalah dan tidak menyimpan data separuh | Uji |
| AC-M5-08 | Evaluasi akurasi numerik dari bahasa alami pada subset golden dataset dilaporkan (exact match dengan toleransi) | Laporan |

### M6 — Router MVP, Guardrails, dan Umpan Balik

| ID | Kriteria | Bukti |
|---|---|---|
| AC-M6-01 | Precision, recall, dan F1 per kelas (dokumen, analitik, di luar cakupan) dilaporkan; ambang keyakinan ditetapkan dari baseline | Laporan |
| AC-M6-02 | Keyakinan di bawah ambang memicu `clarification_required`, bukan tebakan | Uji |
| AC-M6-03 | Rangkaian uji prompt injection v1 dijalankan dan tingkat keberhasilan serangan dilaporkan; tidak ada klaim "aman" tanpa laporan | Laporan |
| AC-M6-04 | Data pribadi pada pola yang ditentukan di-mask pada log; tingkat deteksi pada sampel uji dilaporkan | Laporan |
| AC-M6-05 | Umpan balik tersimpan terkait pesan; Knowledge Manager dapat meninjau | Uji |
| AC-M6-06 | Pemindaian rahasia pada CI aktif dan menggagalkan build bila menemukan rahasia | Log CI |

### M7 — Connector & Sync

| ID | Kriteria | Bukti |
|---|---|---|
| AC-M7-01 | Berkas baru dan berubah pada folder sumber terdeteksi dan diserahkan ke Laravel; berkas tidak berubah tidak diserahkan ulang | Uji |
| AC-M7-02 | Pengulangan dengan `Idempotency-Key` sama tidak menggandakan dokumen | Uji |
| AC-M7-03 | CodeIgniter tidak dapat menetapkan atau mengubah izin dokumen; izin berasal dari `default_acl` sumber di Laravel | Uji kontrak |
| AC-M7-04 | Kegagalan sumber atau jaringan tercatat pada `sync_runs` dan tidak merusak data yang ada | Uji kegagalan |
| AC-M7-05 | Kredensial konektor tersimpan terenkripsi dan tidak muncul di log atau respons API | Uji |
| AC-M7-06 | Uji kontrak CodeIgniter–Laravel berjalan otomatis pada CI | Log CI |
| AC-M7-07 | Catatan tinjauan beban pemeliharaan dua framework dibuat untuk gerbang G3 | Dokumen |

### M8 — Pengerasan dan Rilis MVP

Lihat bagian 6 dan 7.

## 4. Target Metrik Evaluasi (Awal, Divalidasi oleh Baseline)

| Aspek | Metrik | Target awal | Status target |
|---|---|---|---|
| Retrieval | Recall@10 | > 0,9 | Hipotesis dari `EKAA.docx`; ditetapkan ulang setelah baseline |
| Retrieval | MRR, nDCG | Dilaporkan | Tanpa target hingga baseline |
| Jawaban | Faithfulness | > 0,9 | Hipotesis; ditetapkan ulang |
| Jawaban | Answer relevancy, akurasi sitasi | Dilaporkan | Tanpa target hingga baseline |
| Angka | Kebenaran fungsi metrik pada kasus uji | 100% | Tetap (deterministik) |
| Angka | Akurasi numerik dari bahasa alami (exact match dengan toleransi) | Dilaporkan | Tanpa target hingga baseline |
| Router | Precision/recall/F1 per kelas | Dilaporkan | Tanpa target hingga baseline |
| Keamanan akses | Kebocoran lintas hak akses | 0 | Tetap |
| Performa | Latensi p50/p95, throughput, biaya per pertanyaan | Dilaporkan | Target ditetapkan dari baseline dan spesifikasi mesin demo |
| Pengguna | SUS; waktu menemukan informasi vs cara manual | Dilaporkan | Tergantung ketersediaan peserta |

## 5. Golden Dataset (Usulan Awal)

Korpus: dokumen kampus yang boleh dipublikasikan atau disintesis (AS-07). Ukuran berikut adalah **usulan minimum** yang ditinjau pada M0 berdasarkan kebutuhan statistik tiap metrik.

| Segmen | Usulan minimum | Tujuan |
|---|---|---|
| Pertanyaan dokumen dengan jawaban dan sumber acuan | 100 | Retrieval, faithfulness, sitasi |
| Pertanyaan tanpa bukti | 30 | Abstain |
| Pertanyaan angka dengan jawaban acuan | 50 | Akurasi numerik, pemilihan metrik |
| Pertanyaan ambigu atau di luar cakupan | 30 | Router, klarifikasi |
| Skenario uji akses (peran × dokumen, pencabutan, versi) | 40 | Kebocoran lintas hak akses |
| Dokumen berisi instruksi injeksi | 20 | Prompt injection |

Aturan: pedoman anotasi tertulis; setiap butir diberi pengenal dan versi; pembuatan oleh lebih dari satu penilai untuk sebagian butir bila memungkinkan, dengan kesepakatan antar-penilai dilaporkan; dataset dibekukan per versi sebelum eksperimen; **data uji tidak boleh dipakai untuk menyetel prompt atau parameter** (pembagian dev dan test).

Bila evaluasi memakai LLM sebagai penilai (LLM-judge), bias penilai dicatat dan sebagian sampel diperiksa manual.

## 6. Strategi Pengujian Rilis (M8)

| Jenis | Cakupan | Alat | Syarat lulus |
|---|---|---|---|
| Unit | Metrik, Policy, parser, validator | PHPUnit/Pest, pytest | 100% lulus |
| Integrasi dan API | Laravel–AI, Laravel–Connector, callback | PHPUnit, pytest, uji kontrak | 100% lulus |
| UI/E2E | Unggah → indeks → tanya → sitasi; dataset → metrik → grafik; login dan peran | Playwright | Alur kritis lulus |
| Otorisasi | Matriks peran × sumber daya × aksi | Otomatis | 0 pelanggaran |
| Kebocoran | Skenario akses golden dataset | Skrip evaluasi | 0 kebocoran |
| Keamanan lain | Injeksi, berkas, JWT, SQL (Phase 2), dependensi, rahasia | Rangkaian uji + pemindai | Temuan kritis dan tinggi tertutup atau dikecualikan oleh Owner |
| Beban | Pertanyaan bersamaan, ingestion bersamaan | k6 atau Locust | Hasil dilaporkan; target ditetapkan dari baseline |
| Pemulihan | Restore PostgreSQL, MinIO, DuckDB dari cadangan | Skrip | Restore berhasil; waktu dicatat |
| Pemasangan ulang | Pemasangan pada mesin bersih mengikuti README | Manual terdokumentasi | Berhasil tanpa langkah tersembunyi |

## 7. Kriteria Rilis MVP

MVP dapat dinyatakan siap hanya jika:

1. Seluruh FR berprioritas M berjalan dan lulus kriteria milestone masing-masing.
2. Kebocoran lintas hak akses = 0 pada rangkaian uji.
3. Kasus uji metrik lulus 100%.
4. Laporan baseline dan laporan evaluasi M8 tersedia dengan konfigurasi yang dapat diulang.
5. Laporan keamanan, beban, dan restore tersedia.
6. README, OpenAPI, dan panduan pemasangan memungkinkan pemasangan ulang oleh orang lain.
7. Daftar keterbatasan yang diketahui ditulis jujur (OCR belum ada, text-to-SQL belum ada, dan seterusnya).
8. Owner menyetujui rilis secara eksplisit (G4).

Kriteria yang tidak tercapai tidak disembunyikan: dicatat sebagai pengecualian dengan persetujuan Owner.

## 8. Kriteria Penelitian (Pagar Klaim)

Klaim dalam manuskrip hanya boleh dibuat bila:

1. Didukung data dari eksperimen yang tercatat (konfigurasi, seed, versi dataset).
2. Pembanding dijalankan pada kondisi yang setara.
3. Referensi terverifikasi (DOI, penerbit, indeksasi).
4. Hasil negatif dan keterbatasan dilaporkan.
5. Klaim novelty disusun berdasarkan matriks literatur, bukan asumsi.

## 9. Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0-draft | 2026-11-08 | Penyusunan awal Gelombang 3 |
