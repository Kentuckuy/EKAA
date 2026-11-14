ENTERPRISE KNOWLEDGE AND ANALYTICS ASSISTANT

EKAA

MASTER PROJECT AGREEMENT

Single Source of Truth: konsolidasi perencanaan, kesepakatan final, arsitektur, dan rencana implementasi

| Kode dokumen | EKAA-MPA-2.0 |

| --- | --- |

| Versi | 2.0 (konsolidasi final; menggantikan FINAL_PROJECT_AGREEMENT v1.0 tanggal 8 November 2026) |

| Tanggal | 13 November 2026 |

| Status | FINAL APPROVED BY PROJECT OWNER (dasar: pernyataan persetujuan Project Owner pada Master Instruction 13 November 2026, lihat Bagian 41) |

| Disusun oleh | Technical Architect (Claude), atas mandat Project Owner |

| Pemilik keputusan | Project Owner EKAA |

| Dokumen turunan | ANTIGRAVITY_MASTER_PROMPT.md (instruksi implementasi tunggal) |



Dokumen ini adalah rancangan dan kesepakatan. Tidak ada bagian di dalamnya yang menyatakan sistem sudah dibangun, diuji, atau menghasilkan hasil eksperimen. Hasil dan klaim hanya sah bila didukung bukti yang tercatat.

DAFTAR ISI

Catatan: bila daftar isi tampak kosong, klik kanan pada area ini lalu pilih Update Field (atau tekan F9) di Microsoft Word.

RINGKASAN EKSEKUTIF DAN PERHATIAN PROJECT OWNER

0.1 Ringkasan

Dokumen ini adalah Single Source of Truth proyek Enterprise Knowledge and Analytics Assistant (EKAA). Ia menggabungkan seluruh hasil perencanaan yang tersimpan dalam DATA_EKAA.zip (23 berkas: 11 dokumen resmi proyek dan 12 dokumen riset Track C), menyelesaikan konflik antardokumen, menutup celah yang ditemukan pada audit, dan menetapkan kesepakatan final antara Project Owner dan Technical Architect.

EKAA adalah platform asisten pengetahuan dan analitik privat untuk satu organisasi (single-tenant). Ia menjawab pertanyaan dari dokumen organisasi dengan sitasi yang dapat diperiksa (Knowledge Assistant, RAG), menghitung angka dengan fungsi deterministik yang teruji (Analytics Assistant), dan mengarahkan pertanyaan ke jalur yang tepat melalui Hybrid Router.

Arsitektur final: Laravel 13 sebagai core backend dan satu-satunya identity provider, CodeIgniter 4.7 sebagai Connector & Sync Service, Python FastAPI sebagai layanan AI, Vue 3 sebagai frontend, PostgreSQL + pgvector, DuckDB, Valkey, object store S3-compatible, dan Docker Compose sebagai deployment MVP.

0.2 Cara Membaca Status

| Label | Arti |

| --- | --- |

| FINAL APPROVED BY PROJECT OWNER | Keputusan ditetapkan dan mengikat, berdasarkan pernyataan persetujuan Project Owner tertanggal 13 November 2026 (Bagian 41) atau persetujuan eksplisit sebelumnya yang tercatat |

| FINAL (METODE) | Metode evaluasi dan kriteria pemilihan sudah final; pilihan akhirnya (misalnya model embedding) ditentukan oleh hasil eksperimen. Tidak ada hasil yang dikarang |

| FAKTA | Isi yang bersumber dari dokumen dalam ZIP atau hasil verifikasi yang disebutkan |

| ASUMSI | Dugaan kerja yang belum dikonfirmasi; diberi ID AS-xx |

| HIPOTESIS | Angka awal (target, estimasi, batas laju) yang wajib dikalibrasi ulang dengan pengukuran |

| DEFERRED | Ditunda ke fase tertentu dengan pemicu pengaktifan yang tertulis |



0.3 Hasil Audit Singkat

| Aspek | Temuan |

| --- | --- |

| Berkas dalam ZIP | 23 berkas Markdown, tanpa subfolder: 11 dokumen resmi (Agreement, Requirement, Architecture, Technology Stack, Database, API, Security, Roadmap, Acceptance Criteria, Decision Register, Risk Register) dan 12 dokumen riset Track C berstatus DRAFT |

| Berkas yang tidak ada dalam ZIP | EKAA.docx (konsep awal), PROJECT_CONTEXT.md, dan Decision Log sebelumnya. Isinya tidak dikarang; hanya fakta yang tertulis ulang di 23 berkas dan catatan proyek yang dipakai |

| Konflik antardokumen | 9 konflik ditemukan dan diselesaikan (Lampiran A.3) |

| Celah spesifikasi | 17 celah ditemukan dan ditutup (Lampiran A.4) |

| Keputusan berstatus PENDING/PROPOSED pada dokumen 8 November | DEC-001, 005, 007 s.d. 016, 018 s.d. 023 dan DEC-024 s.d. 030: seluruhnya ditetapkan FINAL APPROVED BY PROJECT OWNER pada dokumen ini |

| Keputusan yang diubah setelah verifikasi | Penyimpanan berkas (MinIO diganti), cache dan antrean (Redis diganti Valkey), antrean worker AI, model token SPA. Lihat 0.4 |



0.4 Perubahan Terpenting terhadap Dokumen 8 November 2026

| No | Perubahan | Alasan | Keputusan |

| --- | --- | --- | --- |

| 1 | MinIO diganti dengan object store S3-compatible yang dipilih lewat lapisan abstraksi; default SeaweedFS (Apache-2.0) | Verifikasi 13 November 2026: repositori MinIO Community Edition diarsipkan (dilaporkan Februari dan April 2026), tidak lagi menerima rilis atau perbaikan keamanan; binary dan image resmi dihentikan sejak Oktober 2025 | DEC-034 |

| 2 | Redis diganti Valkey | Redis 8 berlisensi tri-license (RSALv2, SSPLv1, AGPLv3); Valkey berlisensi BSD-3-Clause di bawah Linux Foundation, kompatibel dengan protokol Redis. Untuk produk yang dipasang di banyak organisasi, lisensi permisif mengurangi risiko hukum | DEC-035 |

| 3 | Antrean pekerjaan ai-worker memakai tabel PostgreSQL (ai.jobs, FOR UPDATE SKIP LOCKED), bukan Redis | Pekerjaan tahan restart, transaksional dengan penulisan chunk, tanpa pustaka antrean tambahan | DEC-032 |

| 4 | Model token SPA ditetapkan: token akses berumur pendek di memori, refresh token berputar pada cookie HttpOnly | Dokumen lama menyebut token dan refresh token tanpa menentukan penyimpanan di sisi klien | DEC-031 |

| 5 | Penambahan requirement FR-034 s.d. FR-038 (siklus kata sandi, evaluation harness, backup/restore, retensi dan penghapusan, reindex) | Diperlukan oleh roadmap dan kriteria penerimaan tetapi belum menjadi requirement | Bagian 7 |

| 6 | Perbaikan skema: constraint unik dengan NULLS NOT DISTINCT, kolom must_change_password, tabel ai.jobs, ekspansi unit induk pada principals | Celah teknis pada desain 8 November | DEC-047 |

| 7 | Gerbang G1 dan G2 menjadi checkpoint bukti yang tidak menunggu Owner; G3 (fallback CodeIgniter) dan G4 (rilis) tetap keputusan Owner | Sesuai mandat 13 November: Owner tidak diminta persetujuan berulang untuk keputusan teknis | DEC-050 |



0.5 Perhatian Project Owner

Mandat 13 November 2026 mewajibkan hal-hal berikut dicatat secara transparan karena menyentuh biaya, lisensi, hukum, privasi, atau tujuan proyek. Item ini bukan permintaan persetujuan ulang, tetapi informasi dan keputusan yang hanya dapat diberikan Owner.

| ID | Perhatian | Mengapa penting | Tindakan yang diperlukan |

| --- | --- | --- | --- |

| OA-01 | MinIO Community Edition tidak dipelihara lagi; diganti SeaweedFS (DEC-034) | Keamanan rantai pasok. Tidak ada biaya lisensi | Tidak ada. Uji konformitas S3 dilakukan di M0 |

| OA-02 | Redis diganti Valkey (DEC-035) | Menghindari kewajiban AGPL/SSPL bila EKAA didistribusikan | Tidak ada |

| OA-03 | Biaya dan privasi LLM. Bila penyedia LLM eksternal dipakai, isi pertanyaan dan potongan dokumen dikirim keluar organisasi | UU PDP dan kebijakan data klien (RISK-07, RISK-25). Anggaran belum ditetapkan | Owner menetapkan anggaran per bulan dan kebijakan data untuk demo; pilihan provider final pada checkpoint G2 |

| OA-04 | Perangkat keras untuk demo, embedding, reranker, dan LLM lokal belum diketahui (AS-06, RISK-24) | Menentukan apakah opsi LLM lokal layak didemonstrasikan | Owner menginformasikan spesifikasi CPU, RAM, GPU server demo sebelum M3 |

| OA-05 | Estimasi 29 minggu (sekitar 6,5 bulan) untuk satu pengembang, lebih panjang dari enam bulan pada konsep awal (RISK-01) | Ekspektasi jadwal | Kalibrasi ulang pada checkpoint G1; pemangkasan cakupan hanya atas persetujuan Owner |

| OA-06 | Kajian hukum UU PDP belum dilakukan | Dilarang mengklaim "patuh UU PDP" tanpa tinjauan hukum (RISK-21) | Owner menyiapkan tinjauan hukum sebelum rilis produksi |

| OA-07 | Lisensi komponen dan lisensi produk. Kandidat ekstensi BM25 mungkin berlisensi AGPL; PyMuPDF (AGPL) dikecualikan dari kode aplikasi. Model bisnis dan lisensi produk EKAA belum diputuskan | Kewajiban hukum distribusi | Owner memutuskan model lisensi produk sebelum rilis; AGPL hanya boleh dipakai sebagai daemon terpisah dan harus dicatat |

| OA-08 | Penetration test pra-produksi direncanakan, pelaksana dan biaya belum ada | Syarat rilis produksi (bukan syarat demo MVP) | Owner menetapkan pelaksana dan anggaran sebelum rilis produksi |

| OA-09 | EKAA.docx, PROJECT_CONTEXT.md, dan Decision Log lama tidak ada dalam ZIP | Riwayat brainstorming hanya direkonstruksi dari 23 berkas dan catatan proyek | Opsional: lampirkan berkas tersebut agar Lampiran A dapat dilengkapi |

| OA-10 | Status indeksasi jurnal (Scopus/SINTA) belum dapat diverifikasi | Target Q2 Scopus atau SINTA 1 sampai 2 belum terbukti terpenuhi oleh jurnal yang tercantum | Owner atau sesi riset lanjutan memverifikasi pada Scimago dan SINTA |

| OA-11 | Gerbang G3 (penggantian CodeIgniter dengan fallback C) dan G4 (rilis MVP) tetap keputusan Owner | Framework final tidak boleh dihapus sepihak; rilis perlu persetujuan | Owner memutuskan saat gerbang dicapai |



BAGIAN I — PROJECT OVERVIEW

1. Pendahuluan

1.1 Tujuan Dokumen

Dokumen ini menjadi acuan tunggal untuk Project Owner, Technical Architect, pengembang, AI coding agent (Google Antigravity), dan peneliti. Bila dokumen lain (termasuk ANTIGRAVITY_MASTER_PROMPT.md) berbeda dengan dokumen ini, dokumen ini yang berlaku, kecuali instruksi eksplisit terbaru dari Project Owner.

1.2 Hierarki Sumber Kebenaran

Instruksi eksplisit terbaru Project Owner.

Dokumen ini (EKAA-MPA-2.0), termasuk Decision Register final pada Bagian 40.

Perubahan yang dicatat setelah dokumen ini melalui kontrol perubahan (Bagian 44.4).

Dokumen konsep awal EKAA.docx (tidak tersedia dalam ZIP).

Rekomendasi dan asumsi Technical Architect.

Konflik tidak dipilih secara diam-diam: konflik diidentifikasi, dampaknya dijelaskan, dan penyelesaiannya dicatat.

1.3 Dokumen yang Digantikan

Dokumen ini menggantikan seluruh 11 dokumen resmi dalam ZIP (versi draft 8 November 2026) sebagai sumber keputusan. Isinya sudah dikonsolidasikan di sini; berkas lama tidak lagi dipakai sebagai acuan implementasi. Dokumen riset Track C tetap berdiri sendiri (Lampiran C) dan tidak boleh dicampur dengan klaim proyek (DEC-023).

1.4 Batas Dokumen

Tahap ini hanya melakukan konsolidasi dan penetapan kesepakatan. Tidak ada kode aplikasi yang dibuat. Tidak ada pernyataan bahwa kontrol keamanan sudah diterapkan, pengujian sudah dijalankan, atau eksperimen sudah menghasilkan angka.

2. Latar Belakang

2.1 Konteks

Organisasi seperti universitas, institusi pemerintah daerah, dan perusahaan menyimpan pengetahuan dalam dokumen (peraturan, SK, pedoman, laporan) dan data angka dalam spreadsheet. Pengguna kesulitan menemukan informasi yang tepat dan versi yang berlaku, dan tidak selalu dapat menjawab pertanyaan angka tanpa bantuan analis.

2.2 Perjalanan Perencanaan (Direkonstruksi dari Sumber yang Tersedia)

| Tanggal | Peristiwa | Sumber |

| --- | --- | --- |

| Sebelum 8 Nov 2026 | Konsep awal EKAA (EKAA.docx), instruksi proyek, dan Prompt 2 menjadi dasar perencanaan | Dirujuk di Decision Register; berkas tidak ada dalam ZIP |

| 8 Nov 2026 | Gelombang 1: Decision Register, Technology Stack, Requirement Specification. Owner menyetujui pola arsitektur B (DEC-002), peran Laravel dan CodeIgniter (DEC-003, DEC-004), penundaan Keycloak (DEC-006), dan Laravel 13 + PHP 8.4 (DEC-017) | FINAL_DECISION_REGISTER.md bagian 3.1 |

| 8 Nov 2026 | Gelombang 2: System Architecture, Database Design, API Specification, Security Specification; DEC-024 s.d. DEC-030 diusulkan | Dokumen terkait |

| 8 Nov 2026 | Gelombang 3: Roadmap, Acceptance Criteria, Risk Register, Project Agreement v1.0 (READY FOR OWNER APPROVAL) | Dokumen terkait |

| Setelah 8 Nov 2026 | Owner menyetujui DEC-024 s.d. DEC-030 dan mengonfirmasi asumsi AS-06 (demo di server lokal atau VPS Docker Compose) dan AS-07 (korpus dokumen kampus yang boleh dipublikasikan atau disintesis) | Catatan proyek |

| 10 Nov 2026 | Track C: 12 dokumen riset berstatus DRAFT. Owner menyetujui rekomendasi tiga artikel berurutan; Artikel 1 mengambil Alternatif A (hibrida + reranking pada dokumen institusi Indonesia); Alternatif D ditolak | Dokumen riset dan catatan proyek |

| 13 Nov 2026 | Konsolidasi final: Owner menyatakan persetujuan atas seluruh rekomendasi teknis dan menugaskan satu dokumen Word dan satu master prompt Antigravity | Master Instruction |



2.3 Alternatif yang Pernah Dipertimbangkan

| Topik | Alternatif | Hasil |

| --- | --- | --- |

| Pola framework | A: dua aplikasi PHP penuh; B: Laravel inti + CodeIgniter satu bounded context; C: Laravel + FastAPI saja | B dipilih. C menjadi fallback yang hanya dapat diterapkan atas persetujuan Owner (DEC-002) |

| Identitas | Laravel saja; Keycloak; keduanya | Laravel saja; Keycloak ditunda ke Phase 3 (DEC-006) |

| Vector store | pgvector; Qdrant | pgvector; Qdrant ditunda (DEC-008) |

| Antrean | Redis; RabbitMQ; Celery | Valkey untuk Laravel; tabel PostgreSQL untuk worker AI; RabbitMQ ditunda (DEC-010, DEC-032, DEC-035) |

| Penyimpanan berkas | Disk lokal; MinIO | S3-compatible via abstraksi; SeaweedFS default (DEC-034) |

| Orkestrasi RAG | Kode sendiri; LlamaIndex; LangChain | Kode sendiri (DEC-018) |

| Frontend | Vue; React; Blade/Livewire | Vue 3 (DEC-012) |

| Cakupan | Semua ide konsep awal; MVP ramping | MVP ramping (DEC-020) |



3. Visi dan Misi

Visi. EKAA menjadi satu pintu terpercaya bagi organisasi untuk bertanya kepada dokumen dan data mereka sendiri, dengan jawaban yang selalu dapat diperiksa, tanpa data keluar dari kendali organisasi.

Misi.

Menjawab pertanyaan dokumen dengan sitasi yang dapat dibuka, dan berani menyatakan bahwa bukti tidak ditemukan.

Menghitung angka dengan fungsi deterministik teruji dan menampilkan rumus serta sumbernya.

Menegakkan hak akses pada setiap lapisan sehingga kebocoran lintas hak akses bernilai nol pada rangkaian uji.

Mudah dipasang, dipelihara, dan dikonfigurasi per organisasi.

Menghasilkan bukti evaluasi empiris yang layak menjadi bahan publikasi ilmiah.

4. Tujuan Proyek

| No | Tujuan | Ukuran keberhasilan |

| --- | --- | --- |

| T1 | Produk EKAA yang berfungsi, aman, terstruktur, dan dapat dikembangkan | Seluruh FR berprioritas M lolos kriteria penerimaan dengan bukti (Bagian 37) |

| T2 | Dokumentasi teknis lengkap dan konsisten | Dokumen ini, OpenAPI, README, runbook (Bagian 38) |

| T3 | Penelitian ilmiah berbasis EKAA | Manuskrip Artikel 1 dengan eksperimen tercatat (Lampiran C) |

| T4 | Sistem yang dapat didemonstrasikan, dievaluasi, dan dipelihara | Demo pada korpus AS-07; laporan baseline, keamanan, beban, restore |



Keberhasilan tidak ditentukan oleh tampilan aplikasi. Sistem harus memiliki fungsi nyata, keamanan, pengujian, dokumentasi, dan bukti evaluasi.

5. Target Pengguna

| Segmen | Contoh | Kebutuhan |

| --- | --- | --- |

| Institusi pendidikan | Universitas, fakultas, lembaga penelitian | Tanya jawab peraturan akademik, SK, pedoman; analitik data akademik |

| Pemerintah dan lembaga publik | Dinas, badan daerah | Peraturan, laporan realisasi anggaran, data program |

| Perusahaan | Unit HR, kepatuhan, keuangan | SOP, kebijakan internal, laporan operasional |

| Peran dalam aplikasi | Org Admin, Knowledge Manager, Data Analyst, Standard User, Auditor (Phase 2) | Lihat Bagian 9 |



Bahasa utama dokumen dan antarmuka: Bahasa Indonesia (AS-02).

6. Permasalahan yang Diselesaikan

| No | Masalah | Solusi EKAA |

| --- | --- | --- |

| P1 | Informasi organisasi tersebar; pencarian lama | Satu pintu tanya jawab dengan retrieval hibrida |

| P2 | Layanan AI publik berisiko bagi data internal dan kepatuhan | Single-tenant, kontrol akses, opsi LLM lokal, audit |

| P3 | LLM tidak andal untuk aritmetika dan dapat mengarang | Metric engine deterministik, sitasi wajib, abstain |

| P4 | Dashboard BI statis; setiap pertanyaan baru butuh analis | Pertanyaan angka dalam bahasa alami dengan rumus dan sumber |

| P5 | Versi dokumen yang berlaku tidak jelas | Manajemen versi dan status berlaku/dicabut dalam retrieval |

| P6 | Hak akses dokumen tidak ditegakkan oleh alat AI | Filter akses pada retrieval dan verifikasi ulang sitasi di Laravel |



BAGIAN II — PROJECT REQUIREMENTS

7. Functional Requirements

Prioritas: M = MVP (Must Have), P2 = Phase 2, P3 = Phase 3. Layanan: LV = Laravel, CI = CodeIgniter, AI = FastAPI, FE = Vue. Kriteria penerimaan lengkap per milestone ada di Bagian 37; kolom "AC ringkas" hanya merangkum.

7.1 Fondasi, Identitas, dan Administrasi

| ID | Fitur | Layanan | Prior. | Deskripsi dan AC ringkas |

| --- | --- | --- | --- | --- |

| FR-001 | Login dan sesi | LV, FE | M | Autentikasi pengguna, token akses berumur pendek, refresh token berputar (DEC-031). Kredensial salah ditolak tanpa membocorkan penyebab; percobaan berulang dibatasi; token kedaluwarsa atau dicabut ditolak |

| FR-002 | Pengguna, peran, unit kerja | LV, FE | M | CRUD pengguna, penetapan peran dan unit (hierarki unit). Hanya Org Admin; setiap perubahan diaudit; pengguna nonaktif tidak dapat login; admin terakhir tidak dapat dinonaktifkan |

| FR-003 | Panel admin dasar | FE, LV | M | Pengelolaan pengguna, dokumen, dataset, sumber data, log. Setiap layar terhubung ke API nyata; akses mengikuti peran |

| FR-004 | Pengaturan sistem | LV | M | Nama, logo, penyedia LLM, retensi, batas ukuran berkas. Perubahan tanpa mengubah kode; rahasia tidak ditampilkan ulang |

| FR-005 | Log audit | LV | M | Pencatatan append-only untuk pertanyaan, sumber diakses, perubahan izin dan konfigurasi. Entri tidak dapat diubah lewat aplikasi; dapat difilter |

| FR-006 | REST API dan OpenAPI | LV, AI, CI | M | Kontrak API terdokumentasi per layanan; kode error seragam; contract test di CI |

| FR-034 | Siklus kata sandi | LV, FE | M | Baru. Pengguna baru mendapat kata sandi sementara dari Org Admin dengan must_change_password; reset oleh Org Admin; tanpa ketergantungan SMTP pada MVP. Kata sandi sementara hanya valid sekali untuk penggantian |



7.2 Knowledge Assistant (RAG)

| ID | Fitur | Layanan | Prior. | Deskripsi dan AC ringkas |

| --- | --- | --- | --- | --- |

| FR-010 | Unggah dan validasi dokumen | LV, FE | M | PDF berteks, DOCX, XLSX, CSV. Validasi tipe, ukuran, MIME dan magic bytes di server, pemindaian malware (ClamAV, DEC-038) sebelum ingestion; berkas disimpan di object store dengan kunci acak |

| FR-011 | Versi dan status dokumen | LV | M | Versi, status (draft, berlaku, dicabut), tanggal efektif. Dokumen dicabut tidak muncul di retrieval; riwayat versi terlihat |

| FR-012 | Izin dokumen | LV | M | Izin per peran, unit, pengguna, dan "semua". Pengguna tanpa izin tidak melihat dokumen di hasil pencarian maupun sitasi |

| FR-013 | Parsing dan chunking | AI | M | Ekstraksi teks dan struktur (judul, pasal, ayat, halaman); chunk berbasis struktur dengan overlap dan relasi parent-child. Setiap chunk menyimpan dokumen, versi, halaman, bagian |

| FR-014 | Embedding dan indeks | AI | M | Embedding ke pgvector dan indeks leksikal (FTS PostgreSQL sebagai baseline; BM25 sejati dievaluasi, DEC-029). Perbaikan teks: dokumen 8 November menulis "indeks BM25" sebagai fakta; yang benar adalah baseline FTS ts_rank. Proses asinkron dengan status |

| FR-015 | Retrieval hibrida berfilter akses | AI | M | Leksikal + dense digabung Reciprocal Rank Fusion; filter acl_principals, status, is_current pada klausa WHERE kedua jalur. Nol chunk dari dokumen tanpa izin pada rangkaian uji kebocoran |

| FR-016 | Reranking | AI | M | Cross-encoder pada 20 sampai 50 kandidat teratas; peningkatan terukur terhadap tanpa reranker (bahan RQ1); latensi dicatat |

| FR-017 | Jawaban bersitasi dan abstain | AI | M | Setiap klaim penting punya sitasi (dokumen, bagian, halaman) yang dapat dibuka; bukti lemah menghasilkan no_evidence, bukan jawaban karangan |

| FR-018 | Chat dan riwayat | FE, LV | M | Antarmuka tanya jawab, riwayat per pengguna; sumber tampil dan tertaut; riwayat hanya terbaca pemiliknya |

| FR-019 | Umpan balik jawaban | FE, LV | M | Penilaian benar/salah dengan catatan; Knowledge Manager dapat meninjau |



7.3 Analytics Assistant

| ID | Fitur | Layanan | Prior. | Deskripsi dan AC ringkas |

| --- | --- | --- | --- | --- |

| FR-020 | Impor dataset | LV, AI | M | CSV dan XLSX; deteksi tipe kolom dan format angka Indonesia (pemisah desimal koma, ribuan titik); baris galat dilaporkan; impor atomik (tidak ada data separuh) |

| FR-021 | Kamus data | LV | M | Arti, satuan, peran kolom (dimensi atau ukuran); tampil pada hasil analitik |

| FR-022 | Pustaka metrik deterministik | AI | M | CAGR, YoY, MoM, rata-rata bergerak, proporsi, ranking, agregasi per dimensi sebagai fungsi teruji. Kebenaran 100% pada kasus uji; kasus tepi menghasilkan peringatan atau penolakan jelas; LLM tidak menghitung angka |

| FR-023 | Pemilihan metrik dari bahasa alami | AI | M | LLM memetakan pertanyaan ke metrik dan parameter lewat function calling; parameter divalidasi skema dan kamus data; metrik, rumus, dan sumber ditampilkan |

| FR-024 | Grafik otomatis | AI, FE | M | Spesifikasi grafik JSON dibatasi whitelist (DEC-041); nilai grafik identik dengan hasil hitung; spesifikasi tidak valid ditolak; ekspor gambar dan tabel |

| FR-025 | Izin dataset | LV | M | Izin per peran, unit, pengguna; pengguna tanpa izin tidak dapat menanyakan dataset |



7.4 Router, Konektor, Keamanan, dan Operasi

| ID | Fitur | Layanan | Prior. | Deskripsi dan AC ringkas |

| --- | --- | --- | --- | --- |

| FR-030 | Hybrid Router MVP | AI | M | Klasifikasi tiga kelas (dokumen, analitik, di luar cakupan) dengan ambang keyakinan dan permintaan klarifikasi; precision, recall, F1 per kelas diukur |

| FR-031 | Connector & Sync MVP | CI | M | Satu konektor folder jaringan atau lokal, jadwal, deteksi perubahan berbasis hash, penyerahan ke Laravel dengan kunci idempoten. CodeIgniter tidak menentukan izin |

| FR-032 | Guardrails dasar | AI | M | Penyaringan prompt injection pada isi dokumen, masking data pribadi pada log, validasi keluaran |

| FR-033 | Pengamanan kunci dan rahasia | Semua | M | Rahasia di luar repositori; pemindaian rahasia di CI menggagalkan build |

| FR-035 | Evaluation harness | AI | M | Baru. CLI di layanan AI untuk menjalankan golden dataset (retrieval, jawaban, metrik, router, kebocoran) dengan konfigurasi, seed, dan versi dataset tercatat; keluaran laporan berformat tetap. Diperlukan oleh RQ1 dan AC-M4-01 |

| FR-036 | Backup dan restore | Operasi | M | Baru. Skrip pencadangan terenkripsi (PostgreSQL, object store, volume DuckDB) dan skrip restore; uji restore terjadwal dengan laporan (NFR-AVL-001) |

| FR-037 | Retensi dan penghapusan | LV, AI | M | Baru. Pekerjaan retensi terjadwal (log, percakapan, trace) dan penghapusan atas permintaan yang menjangkau chunk, vektor, dan berkas; tercatat di audit |

| FR-038 | Reindex | LV, AI | M | Baru. Operasi indeks ulang per versi dokumen atau seluruh korpus bila model embedding atau parameter chunking berubah, tanpa kehilangan dokumen; indeks lama diganti atomik |



7.5 Phase 2 dan Phase 3

| ID | Fitur | Prior. | Alasan penundaan |

| --- | --- | --- | --- |

| FR-040 | Text-to-SQL read-only (Bagian 25) | P2 | Risiko keamanan dan kompleksitas; MVP memakai pustaka metrik |

| FR-041 | Pertanyaan hibrida majemuk (dekomposisi, gabung teks dan angka) | P2 | Butuh router dan kedua jalur stabil |

| FR-042 | OCR dan ekstraksi tabel PDF | P2 | Kualitas bervariasi; perlu riset terpisah |

| FR-043 | Verifikasi sitasi (entailment) dan verifikasi angka | P2 | Menambah latensi; butuh baseline |

| FR-044 | Memori percakapan dan penulisan ulang pertanyaan lanjutan | P2 | Bergantung pada chat yang stabil |

| FR-045 | Konektor Google Drive dan SharePoint | P2 | OAuth dan kuota API |

| FR-046 | Row-level security untuk data tabel | P2 | MVP memakai izin level dataset |

| FR-047 | Peran Auditor | P2 | MVP memakai Org Admin untuk audit |

| FR-048 | Cache semantik dan cache hasil query (kunci memuat principals) | P2 | Optimasi setelah baseline |

| FR-049 | Prometheus dan Grafana, MFA TOTP, hash chain audit, TLS internal | P2 | Observabilitas dan pengerasan lanjutan |

| FR-050 | SSO (Keycloak atau OIDC) | P3 | DEC-006 |

| FR-051 | Deteksi anomali dan peramalan | P3 | Opsional, bukan inti RQ |

| FR-052 | Chat widget dan bot WhatsApp | P3 | Antarmuka tambahan |

| FR-053 | Domain pack per jenis organisasi | P3 | Setelah dua studi kasus |

| FR-054 | Provisioning otomatis dan Helm | P3 | DEC-015 |



8. Non-Functional Requirements

Target bersifat hipotesis awal dan dikalibrasi ulang setelah baseline diukur (checkpoint G2).

| ID | Kategori | Kebutuhan | Target awal dan verifikasi |

| --- | --- | --- | --- |

| NFR-PERF-001 | Performance | Latensi jawaban dokumen | p50 dan p95 dicatat; hipotesis kerja p95 di bawah 15 detik pada perangkat keras demo |

| NFR-PERF-002 | Performance | Latensi jalur metrik | Lebih rendah dari jalur dokumen; p95 dicatat |

| NFR-PERF-003 | Performance | Ingestion asinkron | UI tidak terblokir; status proses terlihat |

| NFR-SEC-001 | Security | Autentikasi dan otorisasi pada setiap endpoint | Uji otorisasi otomatis lulus; tidak ada endpoint publik selain login dan health dasar |

| NFR-SEC-002 | Security | Kebocoran lintas hak akses | Nol pada rangkaian uji; biner |

| NFR-SEC-003 | Security | SQL read-only (Phase 2) | Seluruh kasus uji tulis, DDL, dan akses file ditolak |

| NFR-SEC-004 | Security | TLS saat transit; enkripsi saat tersimpan | Dibuktikan pada konfigurasi deployment |

| NFR-SEC-005 | Security | Rantai pasok | Lockfile, versi dikunci, pemindaian dependensi dan image di CI |

| NFR-REL-001 | Reliability | Kegagalan layanan AI tidak merusak data inti | Laravel tetap berfungsi; error terstruktur 503 AI_UNAVAILABLE |

| NFR-REL-002 | Reliability | Operasi sinkronisasi idempoten | Pengulangan tidak menggandakan dokumen |

| NFR-SCA-001 | Scalability | Worker ingestion dapat ditambah | Throughput naik pada uji beban |

| NFR-MNT-001 | Maintainability | Pemisahan controller dan business logic; kontrak terdokumentasi | Tinjauan kode; OpenAPI sesuai perilaku |

| NFR-MNT-002 | Maintainability | Migrasi terversi dengan rollback | Uji migrasi naik dan turun |

| NFR-AVL-001 | Availability | Pencadangan terjadwal dan uji pemulihan | Uji restore berhasil; RPO dan RTO ditetapkan Owner dan klien |

| NFR-USA-001 | Usability | Pengguna dapat bertanya dan melihat sumber tanpa pelatihan | Skor SUS dan waktu menemukan informasi dibanding cara manual (bila peserta tersedia) |

| NFR-ACC-001 | Accessibility | Antarmuka mengikuti WCAG 2.2 level AA sebagai target | Pemeriksaan otomatis (axe) dan manual pada alur kritis |

| NFR-CMP-001 | Compatibility | Peramban modern (dua versi terakhir) | Uji Playwright |

| NFR-I18N-001 | Lokalisasi | Antarmuka Bahasa Indonesia; zona waktu tampilan Asia/Jakarta; angka dan tanggal berformat Indonesia; penyimpanan UTC | Uji tampilan dan uji parsing angka |

| NFR-PRV-001 | Privacy | Masking data pribadi pada log; retensi terkonfigurasi | Detail di Bagian 30 dan 31 |

| NFR-AUD-001 | Auditability | Setiap jawaban dapat ditelusuri ke sumber, parameter, versi model, dan versi prompt | Rekaman audit dan trace |

| NFR-REP-001 | Reproducibility | Eksperimen dapat diulang | Seed, versi model, versi dataset, dan konfigurasi dicatat |

| NFR-OBS-001 | Observability | Log terstruktur JSON dengan correlation ID di seluruh layanan | Uji format log; correlation ID muncul di ketiga layanan |

| NFR-DEP-001 | Deployability | Pemasangan ulang pada mesin bersih mengikuti README tanpa langkah tersembunyi | Uji pemasangan manual terdokumentasi |

| NFR-COST-001 | Cost | Biaya per pertanyaan (token, komputasi) dicatat | Dilaporkan pada laporan beban dan baseline |



9. User Roles

Single-tenant: tidak ada Super Administrator di dalam aplikasi (DEC-019). Akses infrastruktur dipegang operator di luar aplikasi. Peran bersifat tambahan (satu pengguna dapat memiliki beberapa peran).

9.1 Matriks Peran dan Kapabilitas (MVP)

| Kapabilitas | Org Admin | Knowledge Manager | Data Analyst | Standard User |

| --- | --- | --- | --- | --- |

| Kelola pengguna, peran, unit kerja | Ya | - | - | - |

| Pengaturan sistem (LLM, retensi, batas ukuran) | Ya | - | - | - |

| Lihat log audit | Ya | - | - | - |

| Kelola sumber data dan sinkronisasi | Ya | Ya | - | - |

| Unggah, versi, status, izin dokumen | - | Ya | - | - |

| Tinjau umpan balik jawaban | - | Ya | - | - |

| Impor dataset, kamus data, izin dataset | - | - | Ya | - |

| Bertanya (dokumen dan analitik) pada sumber yang diizinkan | Ya | Ya | Ya | Ya |

| Riwayat percakapan sendiri | Ya | Ya | Ya | Ya |

| Beri umpan balik jawaban | Ya | Ya | Ya | Ya |

| Lihat trace retrieval satu pesan (diagnostik) | Ya | - | - | - |



Peran Auditor (Phase 2): baca log audit dan laporan tanpa akses isi dokumen kecuali diizinkan.

9.2 Prinsip Otorisasi

Izin dokumen dan dataset ditentukan per peran, per unit kerja (termasuk unit induk), per pengguna, atau "semua".

Memiliki peran tidak otomatis memberi akses ke seluruh dokumen. Akses dokumen selalu lewat izin dokumen.

Respons 404 (bukan 403) untuk sumber daya yang keberadaannya tidak boleh diketahui pengguna.

Setiap pemeriksaan dilakukan di backend; frontend tidak pernah menjadi satu-satunya penegak.

10. Use Cases

| ID | Use case | Aktor | Alur utama | FR terkait |

| --- | --- | --- | --- | --- |

| UC-01 | Masuk ke sistem | Semua | Kredensial, verifikasi, token akses dan refresh; ganti kata sandi sementara bila diminta | FR-001, FR-034 |

| UC-02 | Mengelola pengguna dan unit | Org Admin | Buat pengguna, tetapkan peran dan unit, nonaktifkan | FR-002 |

| UC-03 | Mengunggah dokumen berversi | Knowledge Manager | Unggah, validasi, pindai, simpan, atur izin, pantau status ingestion | FR-010 s.d. FR-014 |

| UC-04 | Mencabut dokumen | Knowledge Manager | Ubah status ke dicabut; chunk ditandai revoked sinkron; dokumen hilang dari retrieval | FR-011, FR-012 |

| UC-05 | Bertanya kepada dokumen | Standard User | Pertanyaan, router ke jalur dokumen, retrieval berfilter akses, jawaban bersitasi, buka sumber | FR-015 s.d. FR-018 |

| UC-06 | Pertanyaan tanpa bukti | Standard User | Bukti lemah; sistem menjawab no_evidence dengan penjelasan | FR-017 |

| UC-07 | Mengimpor dataset | Data Analyst | Unggah CSV atau XLSX, profil kolom, kamus data, izin | FR-020, FR-021, FR-025 |

| UC-08 | Bertanya angka | Standard User, Data Analyst | Pertanyaan, router ke analitik, pemilihan metrik, hasil dengan rumus, tabel, grafik | FR-022 s.d. FR-024 |

| UC-09 | Sinkronisasi folder | Knowledge Manager | Konektor memindai folder, mendeteksi perubahan, menyerahkan berkas ke Laravel | FR-031 |

| UC-10 | Memberi umpan balik | Standard User, Knowledge Manager | Nilai jawaban, catatan; Knowledge Manager meninjau | FR-019 |

| UC-11 | Menelusuri satu jawaban | Org Admin | Buka trace: sumber, parameter, model, versi prompt | NFR-AUD-001 |

| UC-12 | Memulihkan dari backup | Operator | Jalankan skrip restore dan laporan uji | FR-036 |



11. Scope dan Batasan Proyek

11.1 Cakupan

| Kelompok | Isi | Rujukan |

| --- | --- | --- |

| MVP (Must Have) | Identitas, peran, audit; manajemen dokumen, versi, izin; ingestion dan indeks (PDF berteks, DOCX, XLSX, CSV); retrieval hibrida berfilter akses dengan reranking; jawaban bersitasi dan abstain; chat; impor dataset, kamus data, metrik deterministik, grafik; Hybrid Router tiga kelas; guardrails dasar; umpan balik; konektor folder; evaluation harness; backup dan restore; retensi; reindex | FR-001 s.d. 006, 010 s.d. 019, 020 s.d. 025, 030 s.d. 038 |

| Phase 2 | Text-to-SQL, pertanyaan hibrida majemuk, OCR dan tabel PDF, verifikasi sitasi dan angka, memori percakapan, konektor Drive dan SharePoint, row-level security, Auditor, cache semantik, Prometheus dan Grafana, MFA, TLS internal | FR-040 s.d. 049 |

| Phase 3 | SSO, anomali dan peramalan, widget dan bot WhatsApp, domain pack, provisioning dan Helm | FR-050 s.d. 054 |

| Di luar cakupan | Multi-tenant dalam satu instalasi; fine-tuning model sendiri; aplikasi mobile native; write-back ke database sumber | Keputusan DEC-001, DEC-020 |



11.2 Asumsi

| ID | Asumsi | Status | Dampak bila salah |

| --- | --- | --- | --- |

| AS-01 | Pengembang utama satu orang dibantu AI coding agent; lingkungan Windows dengan Docker | Belum dikonfirmasi | Estimasi jadwal berubah |

| AS-02 | Bahasa utama dokumen dan antarmuka: Indonesia | Belum dikonfirmasi | Pemilihan embedding dan LLM berubah |

| AS-03 | Format MVP: PDF berteks, DOCX, XLSX, CSV; pindai (OCR) pada Phase 2 | Belum dikonfirmasi | Beban MVP naik |

| AS-04 | Korpus MVP skala menengah (ribuan dokumen, bukan jutaan) | Belum dikonfirmasi | Memengaruhi DEC-008 |

| AS-05 | Demo memakai korpus yang boleh dipublikasikan atau disintesis | Tercakup AS-07 | Risiko privasi dan lisensi |

| AS-06 | Demo pada server lokal atau VPS dengan Docker Compose | Dikonfirmasi Owner | Memengaruhi pilihan LLM |

| AS-07 | Korpus golden dataset: dokumen kampus yang boleh dipublikasikan atau disintesis | Dikonfirmasi Owner | Studi kasus dan publikasi |

| AS-08 | Reverse proxy dipakai untuk TLS dan routing | Final (DEC-036) | - |

| AS-09 | Batas awal berkas: dokumen 50 MB, dataset 100 MB; dapat dikonfigurasi | Hipotesis | Disesuaikan setelah uji beban |

| AS-10 | Satu organisasi per instalasi memakai satu bahasa utama dan satu zona waktu | Belum dikonfirmasi | Lokalisasi |



11.3 Batasan

Tidak ada UI yang tampak berfungsi tanpa integrasi backend nyata.

Sistem bersifat read-only terhadap data sumber.

Tidak ada klaim akurasi, keamanan, atau kepatuhan tanpa bukti pengujian atau tinjauan.

Dokumen pindai dan tabel PDF kompleks tidak dijanjikan pada MVP (RISK-13, diterima).

BAGIAN III — FINAL TECHNOLOGY AND ARCHITECTURE

12. Final Technology Stack

12.1 Prinsip Pemilihan

Sederhana lebih dulu; komponen ditambahkan hanya bila ada kebutuhan yang terukur. Setiap komponen yang ditunda dicatat pada 12.5 beserta pemicu pengaktifannya. Lisensi dipertimbangkan sebagai faktor teknis karena EKAA dipasang di banyak organisasi.

12.2 Stack Final per Lapisan

| Lapisan | Pilihan final | Lisensi | Alasan | Keputusan |

| --- | --- | --- | --- | --- |

| Core backend | Laravel 13, PHP 8.4 | MIT | Policy/Gate, queue, migrasi, ekosistem pengujian; identity provider tunggal | DEC-003, DEC-017 |

| Layanan konektor | CodeIgniter 4.7.x, PHP 8.4 | MIT | Ringan; konteks terbatas | DEC-004, DEC-017 |

| Layanan AI | Python + FastAPI + Pydantic v2 | MIT dan sejenisnya | Ekosistem ML; OpenAPI otomatis | DEC-005 |

| Frontend | Vue 3 + TypeScript + Vite + Pinia + Vue Router + Tailwind CSS; ECharts untuk grafik | MIT, Apache-2.0 | Ringan; SPA terhadap API Laravel | DEC-012, DEC-042 |

| Basis data | PostgreSQL (versi stabil terbaru yang didukung pgvector; PostgreSQL 18 sebagai target, 17 sebagai cadangan), tiga schema | PostgreSQL License | Satu mesin, kepemilikan jelas per schema | DEC-007 |

| Vektor | pgvector 0.8.x atau lebih baru (iterative index scan) | PostgreSQL License | Filter akses dan vektor dalam satu kueri | DEC-008 |

| Analitik tabular | DuckDB (read-only) | MIT | Isolasi dari database operasional; cocok untuk file tabular | DEC-009 |

| Cache, rate limit, antrean Laravel | Valkey | BSD-3-Clause | Kompatibel protokol Redis; lisensi permisif | DEC-035 |

| Antrean worker AI | Tabel PostgreSQL ai.jobs (FOR UPDATE SKIP LOCKED) | - | Tahan restart; transaksional; tanpa pustaka tambahan | DEC-032 |

| Penyimpanan berkas | Object store S3-compatible melalui abstraksi; default SeaweedFS | Apache-2.0 | MinIO Community Edition diarsipkan. Pengganti dapat ditukar (Garage, RustFS) karena kontrak S3 | DEC-034 |

| Reverse proxy | Caddy | Apache-2.0 | TLS otomatis dan konfigurasi ringkas | DEC-036 |

| Pemindaian malware | ClamAV (daemon clamd terpisah) | GPL-2.0 (daemon terpisah) | Pemindaian sebelum ingestion; fail-closed | DEC-038 |

| LLM | Antarmuka tertukar: adapter OpenAI-compatible (mencakup vLLM, Ollama, dan banyak penyedia komersial), adapter native opsional, dan Mock provider untuk uji | - | Data tidak keluar bagi klien tertentu; pemilihan model berdasarkan evaluasi | DEC-013, DEC-033 |

| Embedding dan reranker | Kandidat diuji pada golden dataset (BGE-M3, multilingual-e5, bge-reranker-v2-m3, dan kandidat lain yang ditemukan); pemenang dikunci di Decision Register | Bervariasi; dicatat | Hanya contoh pada konsep awal | DEC-014, DEC-033 |

| Autentikasi | Laravel sebagai IdP; token akses opaque berumur pendek + refresh token berputar; JWT antarlayanan asimetris | - | Tanpa session sharing | DEC-006, DEC-030, DEC-031 |

| Visualisasi | ECharts dengan spesifikasi JSON ber-whitelist | Apache-2.0 | Angka hanya dari hasil hitung | DEC-041 |

| Observabilitas MVP | Log terstruktur JSON + correlation ID; trace retrieval dan LLM di database | - | MVP ramping; Langfuse tidak dipakai | DEC-037 |

| Deployment | Docker Compose | Apache-2.0 | Sesuai skala klien awal | DEC-015 |

| Backup | pg_dump + restic (terenkripsi) | BSD-2-Clause | Cadangan terenkripsi di luar host dengan uji restore | DEC-044 |

| CI | GitHub Actions; seluruh langkah juga dapat dijalankan lokal lewat make ci | - | Version control dan CI | DEC-043 |



12.3 Pustaka Utama per Layanan (Kandidat, Dikunci pada M0)

| Layanan | Kategori | Pilihan |

| --- | --- | --- |

| Laravel | Pengujian dan mutu | Pest, Larastan (PHPStan), Laravel Pint |

| Laravel | Penyimpanan berkas | Flysystem S3 adapter ke object store |

| Laravel | UUIDv7 dan JWT | ramsey/uuid (UUIDv7), pustaka JWT dengan dukungan EdDSA atau RS256 |

| Laravel | Otorisasi | RBAC dan Policy buatan sendiri di atas tabel core.* (tanpa paket RBAC pihak ketiga, agar sesuai skema UUID dan schema) |

| CodeIgniter | Kriptografi dan JWT | Layanan Encryption bawaan (libsodium) untuk kredensial konektor; pustaka JWT |

| CodeIgniter | Pengujian | PHPUnit, PHPStan |

| FastAPI | Data | SQLAlchemy 2, psycopg 3, Alembic, pgvector-python, DuckDB |

| FastAPI | Parsing | pdfplumber (pdfminer.six) dan pypdf (dievaluasi pada M3), python-docx, openpyxl. PyMuPDF dikecualikan karena lisensi AGPL (DEC-039) |

| FastAPI | Model | sentence-transformers atau FlagEmbedding untuk inferensi in-process; opsi endpoint inferensi eksternal melalui konfigurasi |

| FastAPI | Infrastruktur | httpx, tenacity, structlog, PyJWT, boto3 |

| FastAPI | Mutu | pytest, ruff, mypy, pip-audit; manajer paket uv dengan lockfile |

| Frontend | Mutu | Vitest, Playwright, ESLint, vue-tsc, DOMPurify (sanitasi render Markdown), openapi-typescript (klien bertipe dari OpenAPI) |

| Keamanan CI | Pemindaian | gitleaks (rahasia), Trivy (image dan dependensi), composer audit, pip-audit, npm audit |

| Beban | Uji kinerja | k6 |



12.4 Status Verifikasi Versi (13 November 2026)

| Komponen | Hasil verifikasi | Status |

| --- | --- | --- |

| Laravel | Versi mayor 13 dirilis 17 Maret 2026; PHP 8.3 s.d. 8.5; dukungan keamanan sampai 17 Maret 2028. Laravel 14 diperkirakan Q1 2027 dan tidak dimigrasi selama MVP | Terverifikasi (8 Nov) |

| CodeIgniter 4 | Situs resmi menyebut 4.7.3, PHP 8.1+; dukungan PHP 8.5 belum diverifikasi | Terverifikasi sebagian |

| PHP | 8.4 berada dalam rentang dukungan kedua framework | Terverifikasi |

| pgvector | Versi 0.8.0 memperkenalkan iterative index scan (hnsw.iterative_scan, hnsw.max_scan_tuples) untuk mengatasi hasil kurang pada kueri berfilter; versi 0.8.2 tercantum pada PGXN; mendukung PostgreSQL 18. Batas dimensi indeks HNSW: vector 2.000, halfvec 4.000 | Terverifikasi (13 Nov); perilaku pada filter ACL tetap diuji di M0 |

| MinIO Community Edition | Binary dan image dihentikan Oktober 2025; mode pemeliharaan 3 Desember 2025; repositori diarsipkan (dilaporkan Februari dan April 2026) | Terverifikasi; diganti |

| Redis | Redis 8 tri-license (RSALv2, SSPLv1, AGPLv3); Valkey BSD-3-Clause di bawah Linux Foundation | Terverifikasi; diganti |

| SeaweedFS | Apache-2.0; pemeliharaan aktif (sumber sekunder melaporkan rilis 4.48 pada September 2026) | Terverifikasi sebagian; versi dikunci M0 |

| Google Antigravity | Mendukung rules persisten (.agents/rules/ atau .agent/rules/ menurut sumber berbeda), workflows, dan mode Planning; AGENTS.md dipakai lintas alat | Terverifikasi sebagian; lihat Bagian 44 |

| Python, FastAPI, PostgreSQL (minor), DuckDB, Valkey, Caddy, ClamAV, Vue, ECharts, Tailwind | Belum diverifikasi pada dokumen ini | Wajib dikunci dan diverifikasi di M0 |



12.5 Komponen yang Ditunda dan Pemicunya

| Komponen | Alasan | Pemicu pengaktifan |

| --- | --- | --- |

| Keycloak (SSO) | Menduplikasi autentikasi pada MVP | Klien membutuhkan SSO (Phase 3) |

| Qdrant | pgvector cukup untuk korpus menengah | Recall atau latensi pgvector tidak memenuhi target pada korpus terukur |

| RabbitMQ | Valkey dan tabel job cukup | Kebutuhan routing pesan kompleks atau jaminan pengiriman yang tidak terpenuhi |

| Kubernetes dan Helm | Belum ada kebutuhan skala | Klien dengan beban tinggi (Phase 3) |

| Prometheus dan Grafana | MVP cukup lewat log dan trace database | Phase 2 |

| Langfuse | Menambah satu layanan | Bila analisis trace LLM lanjutan dibutuhkan |

| LlamaIndex dan LangChain | Mengurangi ketergantungan; eksperimen harus mudah direproduksi | Hanya bila komponen tertentu terbukti lebih baik dari kode sendiri |



12.6 Kebijakan Versi dan Rantai Pasok (DEC-045)

Seluruh dependensi dikunci pada versi pasti melalui lockfile (composer.lock, uv.lock, package-lock.json); tidak ada tag latest; image Docker dikunci pada tag spesifik dan, untuk produksi, pada digest.

Memilih rilis stabil; rilis yang baru terbit kurang dari 14 hari dihindari kecuali perbaikan keamanan.

Pembaruan dependensi masuk lewat pull request terpisah dengan pengujian; tidak ada penggabungan otomatis.

Tabel versi terverifikasi dicatat pada docs/VERSIONS.md dan diperbarui di setiap perubahan.

Penambahan dependensi baru harus dicatat alasannya; dependensi berlisensi AGPL atau GPL tidak boleh masuk ke kode aplikasi tanpa pencatatan sebagai perhatian Owner (OA-07).

13. System Architecture

13.1 Prinsip Arsitektur

Satu pemilik per fakta. Identitas dan hak akses hanya dimiliki Laravel. Chunk dan vektor hanya dimiliki layanan AI. Konfigurasi dan status konektor hanya dimiliki CodeIgniter.

Laravel adalah satu-satunya gerbang dari klien. Browser tidak pernah memanggil CodeIgniter atau FastAPI.

Pertahanan berlapis untuk akses data. Filter akses di retrieval (FastAPI) dan verifikasi ulang sitasi oleh Laravel sebelum respons dikirim.

LLM tidak menghitung angka. Semua angka berasal dari fungsi metrik teruji atau hasil query tervalidasi.

Kegagalan terisolasi. Layanan AI atau konektor mati tidak menghentikan autentikasi, manajemen dokumen, dan audit.

Kontrak dahulu. OpenAPI dan skema database disepakati sebelum modul yang saling bergantung dibangun.

13.2 Diagram Container

Gambar 13.1 Diagram container EKAA (Docker Compose)

13.3 Aplikasi dan Batas Tanggung Jawab

| Aplikasi | Pemilik | Tanggung jawab | Tidak boleh |

| --- | --- | --- | --- |

| Vue 3 SPA | Frontend | UI chat, panel admin, grafik ECharts | Menyimpan rahasia; memanggil layanan selain Laravel |

| Laravel app | Core | Autentikasi, otorisasi, metadata dokumen dan dataset, percakapan, audit, orkestrasi, penegakan akses | Menjalankan embedding, retrieval, atau perhitungan metrik |

| Laravel queue-worker, scheduler | Core | Tugas latar: kirim ke AI, rekonsiliasi, retensi | - |

| CodeIgniter connector | Connector | Konfigurasi konektor, jadwal, deteksi perubahan, penyerahan berkas ke Laravel | Mengelola pengguna, peran, izin, atau menjawab pertanyaan |

| FastAPI ai-api | AI | Router, retrieval, rerank, generasi, metric engine, validasi grafik | Menyimpan identitas atau memutuskan izin |

| FastAPI ai-worker | AI | Parsing, chunking, embedding, impor dataset, reindex | - |



ai-api dan ai-worker memakai basis kode yang sama dengan peran proses berbeda.

14. Framework Responsibilities

14.1 Pembagian Tanggung Jawab (DEC-002 sampai DEC-005)

| Aspek | Laravel 13 | CodeIgniter 4.7 | FastAPI |

| --- | --- | --- | --- |

| Peran | Core backend dan pemilik identitas serta hak akses | Connector & Sync Service (bounded context terbatas) | AI service |

| Modul | Autentikasi, RBAC dan ABAC terbatas, pengguna, peran, unit, dokumen dan dataset (metadata), versi, percakapan, umpan balik, audit, pengaturan, API untuk frontend, orkestrasi, ACL sync | Definisi sumber eksternal, konektor (MVP: folder), jadwal, deteksi perubahan, penyerahan berkas | Parsing, chunking, embedding, retrieval hibrida, reranking, generasi bersitasi, router, metric engine, impor dataset, guardrails, evaluasi |

| Schema database | core (pemilik migrasi) | connector (pemilik migrasi) | ai (pemilik migrasi) dan berkas DuckDB |

| Komunikasi keluar | REST JSON ke CodeIgniter dan FastAPI dengan JWT layanan | REST ke Laravel (menyerahkan berkas); tidak memanggil FastAPI | REST callback ke Laravel; membaca berkas dari object store |

| Deployment | app, queue-worker, scheduler | connector | ai-api, ai-worker |

| Pengujian | Pest, PHPStan, Pint | PHPUnit, PHPStan | pytest, ruff, mypy |



14.2 Larangan Arsitektural yang Mengikat

Tidak ada business logic yang sama di dua framework.

Tidak ada session sharing; autentikasi antarlayanan memakai JWT berumur pendek.

Tidak ada tabel yang dimiliki dua layanan; tidak ada join lintas schema dari aplikasi.

Migrasi hanya dijalankan oleh pemilik schema.

CodeIgniter tidak membuat keputusan akses; ia hanya menyerahkan berkas dan metadata ke Laravel.

Browser hanya berbicara dengan Laravel.

Perubahan kontrak yang merusak hanya melalui versi baru atau pola tambah-dulu lalu hapus.

14.3 Penilaian Jujur atas Dua Framework PHP

Secara teknis, satu framework PHP lebih sederhana dipelihara daripada dua. Pola B dipertahankan karena keputusan Owner dan nilai portofolio, dengan syarat yang ditegakkan: CodeIgniter dibatasi pada Connector & Sync; kontrak API Laravel-CodeIgniter ditulis dan diuji lebih dulu (M0); pemisahan schema dan role database ditegakkan.

Tinjauan pada checkpoint G3 (akhir M7) memakai indikator terukur yang dicatat selama pengembangan:

| Indikator | Cara ukur | Sinyal masalah (hipotesis) |

| --- | --- | --- |

| Porsi waktu | Jam kerja pada apps/connector dibanding total pada M7 | Lebih dari dua kali kontribusi fiturnya |

| Perubahan kontrak merusak | Jumlah breaking change pada kontrak Laravel-CodeIgniter sejak M0 | Lebih dari dua |

| Durasi dan kegagalan CI | Waktu tambahan pipeline konektor; jumlah kegagalan akibat kontrak | Pipeline tidak stabil |

| Cacat | Cacat yang berasal dari batas dua framework | Berulang |



Bila sinyal terpenuhi, rekomendasi dicatat dan Owner memutuskan. Fallback C (konektor dipindah ke Laravel, antarmuka layanan AI tidak berubah) hanya dapat diterapkan atas persetujuan Owner (OA-11).

15. Repository Architecture

15.1 Keputusan

Satu monorepo (DEC-043) agar kontrak, skema, dan pengujian lintas layanan konsisten bagi pengembang tunggal. Setiap aplikasi tetap memiliki toolchain, Dockerfile, dan pengujian sendiri.

15.2 Struktur Direktori

ekaa/

AGENTS.md                  # aturan kerja AI coding agent (ringkasan Bagian 44)

README.md                  # pemasangan, perintah, arsitektur singkat

Makefile                   # make up | down | test | ci | migrate | seed | eval | backup

docker-compose.yml         # produksi/demo

docker-compose.dev.yml     # override pengembangan

.env.example               # tanpa rahasia nyata

.github/workflows/ci.yml

apps/

core/                    # Laravel 13 (app, queue-worker, scheduler)

connector/               # CodeIgniter 4.7 (Connector & Sync)

web/                     # Vue 3 SPA

services/

ai/                      # FastAPI: ai-api + ai-worker (satu basis kode)

contracts/

openapi/                 # core-public, core-internal, ai-internal, connector-internal (YAML)

schemas/                 # JSON Schema: chart_spec, ask_request, ask_response

deploy/

caddy/  postgres/init/  seaweedfs/  clamav/

scripts/                 # install, healthcheck, backup, restore

eval/

datasets/                # golden dataset terversi (dev/test dibekukan)

protocols/               # pedoman anotasi, protokol eksperimen

reports/                 # laporan baseline dan evaluasi

tests/

e2e/ security/ load/     # Playwright, matriks otorisasi/kebocoran, k6

docs/

spec/                    # salinan dokumen Word dan hasil ekstraksi teks

adr/  runbook/  PROGRESS.md  VERSIONS.md  KNOWN_LIMITATIONS.md

15.3 Struktur Internal per Aplikasi

| Aplikasi | Struktur lapisan |

| --- | --- |

| Laravel | Http/Controllers (tipis) -> Http/Requests (validasi) -> Services atau Actions (business logic) -> Repositories atau Eloquent -> Policies. Modul dikelompokkan per domain: Auth, Identity, Documents, Datasets, Conversations, Audit, Settings, Integration (klien AI, klien konektor, ACL sync) |

| CodeIgniter | Controllers (tipis) -> Services (Scanner, ChangeDetector, Handoff) -> Models; konektor sebagai kelas yang mengimplementasikan antarmuka SourceConnector |

| FastAPI | api/ (router) -> domain/ (router intent, retrieval, rerank, generation, metrics, charts, guardrails) -> infra/ (db, storage, llm, embedding, jobs); eval/ terpisah dari API; worker/ untuk pekerjaan |

| Vue | features/ (auth, chat, documents, datasets, admin), shared/ (api client, ui, i18n), stores/ |



16. Database Architecture

16.1 Keputusan Desain

| ID | Keputusan | Alasan |

| --- | --- | --- |

| DD-01 | Satu PostgreSQL, tiga schema (core, connector, ai), tiga role (DEC-007) | Kepemilikan jelas tanpa banyak instans |

| DD-02 | Kunci primer uuid UUIDv7 dibangkitkan aplikasi (DEC-026) | Referensi lintas layanan; urutan waktu baik untuk indeks |

| DD-03 | Tanpa tabel organizations (DEC-027); profil organisasi di core.system_settings | Single-tenant |

| DD-04 | Foreign key hanya di dalam satu schema; referensi lintas schema berupa kolom UUID tanpa FK | Mencegah ketergantungan migrasi antarlayanan |

| DD-05 | Integritas lintas schema dijaga rekonsiliasi harian (Laravel) dan hapus lunak | Mendeteksi data yatim |

| DD-06 | Hapus lunak (deleted_at) untuk dokumen, dataset, pengguna; log audit tidak dihapus lewat aplikasi | Auditabilitas |

| DD-07 | Dimensi vektor mengikuti model embedding terpilih; ditulis pada migrasi setelah model dikunci (checkpoint M3) | Dimensi tidak boleh diasumsikan |

| DD-08 | Waktu timestamptz (UTC) | Konsistensi |

| DD-09 | Baru. Constraint unik pada kolom yang boleh NULL memakai NULLS NOT DISTINCT (PostgreSQL 15 ke atas) | Pada desain lama, UNIQUE(document_id, principal_type, principal_id) dengan principal_id NULL (principal all) mengizinkan duplikat |

| DD-10 | Baru. core.users memiliki must_change_password boolean dan password_changed_at | Mendukung FR-034 |

| DD-11 | Baru. ai.jobs sebagai antrean pekerjaan worker AI (DEC-032) | Pekerjaan tahan restart |

| DD-12 | Baru. Kolom enumerasi (status, rating, dan sejenisnya) memakai CHECK constraint atau tipe enum | Mencegah nilai tidak sah |

| DD-13 | Baru. Indeks HNSW dievaluasi sebagai indeks parsial (WHERE status = 'active' AND is_current) bersama hnsw.iterative_scan pada M0 | Mengurangi hasil kurang pada kueri berfilter |

| DD-14 | Baru. Ekspansi principals: himpunan principals pengguna memuat all, user:<id>, role:<id> untuk setiap peran, serta unit:<id> untuk unit pengguna dan seluruh unit induknya | Izin pada unit induk berlaku bagi anggota unit turunan; ekspansi dilakukan Laravel, bukan FastAPI |



16.2 Kepemilikan per Layanan

| Schema | Role DB | Hak | Pemilik migrasi |

| --- | --- | --- | --- |

| core | ekaa_core | Penuh pada core; tanpa akses ke ai dan connector | Laravel |

| connector | ekaa_connector | Penuh pada connector; tanpa akses lain | CodeIgniter |

| ai | ekaa_ai | Penuh pada ai; tanpa akses lain | FastAPI (Alembic) |



core.audit_logs hanya memberi INSERT dan SELECT kepada ekaa_core. Pembersihan sesuai retensi dijalankan role administratif terpisah di luar aplikasi.

16.3 Schema `core`

Kolom standar created_at dan updated_at (timestamptz NOT NULL) ada pada semua tabel kecuali dinyatakan lain.

| Tabel | Kolom utama | Constraint dan indeks |

| --- | --- | --- |

| users | id, email, name, password_hash, is_active, must_change_password, password_changed_at, last_login_at, deleted_at | UNIQUE(email ternormalisasi huruf kecil); INDEX(is_active) |

| roles | id, code, name, description | UNIQUE(code): org_admin, knowledge_manager, data_analyst, standard_user, auditor |

| permissions | id, code, description | UNIQUE(code) |

| role_permission | role_id, permission_id | PK(role_id, permission_id); FK; INDEX(permission_id) |

| role_user | user_id, role_id | PK(user_id, role_id); FK; INDEX(role_id) |

| org_units | id, parent_id, name, code | UNIQUE(code); INDEX(parent_id); siklus dicegah di level aplikasi |

| user_units | user_id, org_unit_id | PK ganda; FK; INDEX(org_unit_id) |

| data_sources | id, name, type (upload, folder, gdrive, sharepoint, database), default_acl (jsonb), owner_id, is_active | UNIQUE(name) |

| documents | id, title, owner_id, data_source_id, status (draft, active, revoked), current_version_id, external_ref, deleted_at | INDEX(status); INDEX(data_source_id); UNIQUE(data_source_id, external_ref) WHERE external_ref IS NOT NULL |

| document_versions | id, document_id, version_no, storage_key, mime_type, size_bytes, sha256, effective_date, status (pending, indexed, failed, superseded, revoked), indexed_at | UNIQUE(document_id, version_no); INDEX(sha256); INDEX(status) |

| document_permissions | id, document_id, principal_type (role, unit, user, all), principal_id, access (read) | UNIQUE NULLS NOT DISTINCT(document_id, principal_type, principal_id); INDEX(principal_type, principal_id) |

| ingestion_jobs | id, document_version_id, status (queued, running, done, failed), error, started_at, finished_at, attempt | INDEX(status); INDEX(document_version_id) |

| datasets | id, name, data_source_id, owner_id, storage_key, row_count, status, schema_hash, locale_config (jsonb), deleted_at | UNIQUE(name) |

| dataset_columns | id, dataset_id, name, data_type, unit, description, is_dimension, is_measure | UNIQUE(dataset_id, name) |

| dataset_permissions | id, dataset_id, principal_type, principal_id, access | UNIQUE NULLS NOT DISTINCT(dataset_id, principal_type, principal_id) |

| metric_definitions | id, code, name, description, function_ref, params_schema (jsonb), is_active | UNIQUE(code) |

| analytics_jobs | id, user_id, dataset_id, metric_id, params, result, formula, status, duration_ms | INDEX(user_id, created_at); INDEX(dataset_id) |

| conversations | id, user_id, title, deleted_at | INDEX(user_id, updated_at DESC) |

| messages | id, conversation_id, role, content, route, route_confidence, payload (jsonb: tabel, chart_spec), model_info (jsonb: model, prompt_version, parameter), trace_id, latency_ms | INDEX(conversation_id, created_at) |

| message_citations | id, message_id, document_id, version_id, chunk_ref, page_start, page_end, section_path, quote_excerpt | INDEX(message_id); INDEX(document_id) |

| feedback | id, message_id, user_id, rating (correct, incorrect), note, reviewed_by, reviewed_at | UNIQUE(message_id, user_id) |

| audit_logs | id, occurred_at, actor_id, actor_type, action, resource_type, resource_id, outcome, ip, correlation_id, details (jsonb) | Tanpa FK; INDEX(occurred_at); INDEX(actor_id, occurred_at); INDEX(action); INDEX(resource_type, resource_id); tanpa UPDATE/DELETE |

| system_settings | key, value (jsonb), is_secret, updated_by | PK(key); rahasia dienkripsi aplikasi, tidak teks biasa |

| refresh_tokens | id, user_id, token_hash, expires_at, revoked_at, replaced_by, user_agent | UNIQUE(token_hash); INDEX(user_id) |

| access_tokens | id, user_id, token_hash, expires_at, revoked_at | UNIQUE(token_hash); INDEX(user_id); dibuat bila token akses opaque disimpan di tabel sendiri (DEC-031) |



Tabel standar Laravel (jobs, failed_jobs, migrations, cache) tidak diulang.

16.4 Schema `connector`

| Tabel | Kolom utama | Constraint dan indeks |

| --- | --- | --- |

| sources | id, core_source_id, type, config (jsonb tanpa rahasia), credentials_enc (bytea), schedule, is_active | UNIQUE(core_source_id) |

| sync_runs | id, source_id, started_at, finished_at, status, items_seen, items_new, items_changed, error | INDEX(source_id, started_at DESC) |

| source_items | id, source_id, external_id, path, content_hash, size_bytes, last_seen_at, core_document_id, state (new, synced, changed, removed, error), idempotency_key | UNIQUE(source_id, external_id); INDEX(state); UNIQUE(idempotency_key) |



16.5 Schema `ai`

| Tabel | Kolom utama | Constraint dan indeks |

| --- | --- | --- |

| embedding_models | id, name, dimension, is_active, config (jsonb) | UNIQUE(name) |

| document_index_state | document_id, version_id, status, chunk_count, embedding_model_id, indexed_at, error | PK(document_id, version_id); FK model; INDEX(status) |

| chunks | id, document_id, version_id, chunk_index, parent_chunk_id, content, content_tsv, embedding vector(n), page_start, page_end, section_path, token_count, acl_principals text[], status (active, revoked), is_current | HNSW pada embedding (kelas operator sesuai jarak); GIN pada content_tsv; GIN pada acl_principals; INDEX(document_id, version_id); INDEX(status, is_current) |

| jobs | id, type (ingest, reindex, dataset_import), payload (jsonb), status (queued, running, done, failed), attempts, max_attempts, run_after, locked_by, locked_at, last_error, idempotency_key, correlation_id | UNIQUE(idempotency_key); INDEX(status, run_after); worker mengambil dengan FOR UPDATE SKIP LOCKED |

| dataset_files | dataset_id, storage_key, duckdb_path, schema_hash, row_count, imported_at | PK(dataset_id) |

| dataset_acl | dataset_id, principal | PK ganda; salinan untuk audit sisi AI; keputusan akhir tetap di Laravel |

| retrieval_traces | id, trace_id, query_hash, route, candidates, reranked, latency_ms, model_info | INDEX(trace_id); retensi pendek |

| eval_sets, eval_items, eval_runs, eval_results | Set evaluasi bernama dan versi; butir (pertanyaan, jawaban acuan, sumber acuan, tipe); proses (konfigurasi, seed); hasil per butir | UNIQUE(name, version); INDEX(eval_set_id); INDEX(run_id) |



Format `acl_principals`: string all, role:<uuid>, unit:<uuid>, user:<uuid>. Retrieval memakai operator irisan array (acl_principals && :principals) dengan principals dibentuk Laravel per permintaan (DD-14).

16.6 Penyimpanan Vektor dan Pencarian Leksikal

pgvector (DEC-008). Verifikasi M0: perilaku ANN dengan filter ACL selektif diuji pada korpus uji; mitigasi dipilih dari iterative scan (relaxed_order atau strict_order), indeks parsial, atau kandidat lebih besar; parameter HNSW ditetapkan dari pengukuran recall dan latensi, bukan tebakan.

Dimensi vektor dikunci setelah model embedding dipilih. HNSW pada tipe vector dibatasi 2.000 dimensi; bila model melebihi batas, gunakan halfvec.

Leksikal (DEC-029): FTS PostgreSQL content_tsv dengan konfigurasi simple sebagai baseline. Ini bukan BM25 murni dan tidak memiliki konfigurasi bahasa Indonesia. Kandidat BM25 sejati dievaluasi di M0/M1 dengan kriteria: lisensi (AGPL hanya sebagai daemon atau ekstensi terpisah dan dicatat ke Owner), dukungan versi PostgreSQL yang dipakai, aktivitas pemeliharaan, dukungan tokenisasi Indonesia, dan peningkatan nDCG terukur pada dev split. Hasil dicatat sebagai bahan RQ1.

Pemicu migrasi ke Qdrant: recall atau latensi pgvector tidak memenuhi target pada korpus terukur.

16.7 Integritas, Migrasi, dan Retensi

| Hubungan | Mekanisme |

| --- | --- |

| Dalam schema | FK dengan ON DELETE RESTRICT; cascade hanya untuk tabel turunan murni (role_user, role_permission, user_units) |

| core ke ai | Laravel memanggil API internal; rekonsiliasi harian membandingkan core.document_versions dengan ai.document_index_state |

| connector ke core | core_document_id dan core_source_id referensi logis; rekonsiliasi mendeteksi yatim |

| Pencabutan dokumen | Laravel ubah status lalu memanggil ai untuk menandai chunk revoked secara sinkron |

| Penghapusan | Hapus lunak di core; penghapusan fisik chunk dan berkas oleh pekerjaan retensi dan dicatat audit |

| Migrasi | Setiap layanan hanya memigrasi schema miliknya; setiap migrasi punya up dan down yang diuji; perubahan kontrak memakai pola expand-then-contract |



Retensi (nilai awal; dapat dikonfigurasi, ditetapkan final oleh kebijakan organisasi dan tinjauan hukum): audit_logs mulai dari 1 tahun; messages dan conversations dikonfigurasi per instalasi; retrieval_traces sekitar 30 hari; refresh_tokens dihapus saat kedaluwarsa.

17. API Architecture

17.1 Permukaan API

| Permukaan | Prefix | Pemanggil | Penyedia | Autentikasi |

| --- | --- | --- | --- | --- |

| API publik | /api/v1 | Browser, klien REST | Laravel | Token akses pengguna |

| API internal AI | /internal/v1 (host ai-api) | Laravel | FastAPI | JWT layanan |

| API internal Connector | /internal/v1 (host connector) | Laravel | CodeIgniter | JWT layanan |

| API internal Core | /internal/v1 (host app) | CodeIgniter, FastAPI (callback) | Laravel | JWT layanan |



API internal tidak dipublikasikan di reverse proxy. Hanya /api/v1 dan berkas statis SPA yang dapat dijangkau dari luar.

17.2 Konvensi Umum

Format JSON; unggah berkas multipart/form-data; pengenal sumber daya UUID; waktu ISO 8601 UTC.

Header X-Correlation-Id dibuat Laravel bila tidak ada dan diteruskan ke seluruh layanan serta dicatat di audit dan log.

Operasi tulis internal wajib Idempotency-Key; pengulangan dengan kunci sama mengembalikan hasil sebelumnya.

Paginasi ?page= dan ?per_page= (maksimum 100) atau kursor untuk daftar besar.

Kontrak ditulis spec-first sebagai OpenAPI YAML di contracts/openapi/; bila implementasi berbeda, OpenAPI yang berlaku dan perbedaan dicatat di Decision Register.

Struktur error seragam:

{

"error": {

"code": "FORBIDDEN",

"message": "Anda tidak memiliki izin untuk aksi ini.",

"details": [],

"correlation_id": "c1f3..."

}

}

| HTTP | Kode | Arti |

| --- | --- | --- |

| 400 | BAD_REQUEST | Permintaan tidak dapat diproses |

| 401 | UNAUTHENTICATED | Token hilang, tidak valid, atau kedaluwarsa |

| 403 | FORBIDDEN | Tidak berizin |

| 404 | NOT_FOUND | Tidak ada atau tidak boleh diketahui keberadaannya |

| 409 | CONFLICT | Konflik status atau idempotency |

| 413 | PAYLOAD_TOO_LARGE | Berkas melebihi batas |

| 415 | UNSUPPORTED_MEDIA_TYPE | Tipe berkas tidak diizinkan |

| 422 | VALIDATION_FAILED | Validasi gagal; details berisi per-field |

| 429 | RATE_LIMITED | Melebihi batas; header Retry-After |

| 502 | UPSTREAM_ERROR | Layanan internal gagal |

| 503 | AI_UNAVAILABLE | Layanan AI tidak tersedia |

| 504 | UPSTREAM_TIMEOUT | Batas waktu terlampaui |



Status domain (respons 200, bukan error HTTP): answered, no_evidence, clarification_required, out_of_scope. Kode domain: OUT_OF_SCOPE, CLARIFICATION_REQUIRED, METRIC_INVALID_PARAMS, DATASET_NOT_ALLOWED, INGESTION_FAILED, MALWARE_DETECTED.

17.3 API Publik Laravel (`/api/v1`)

| Grup | Method dan endpoint | Otorisasi | Rate limit awal |

| --- | --- | --- | --- |

| Autentikasi | POST /auth/login; POST /auth/refresh; POST /auth/logout; POST /auth/change-password; GET /me | Login publik; lainnya pemilik token | login 5/menit per IP dan akun; refresh 20/menit; /me 120/menit |

| Pengguna | GET, POST /users; PATCH /users/{id}; POST /users/{id}/deactivate; POST /users/{id}/reset-password; PUT /users/{id}/roles; GET, POST, PATCH /org-units | org_admin | 60/menit |

| Dokumen | GET, POST /documents; GET, PATCH, DELETE /documents/{id}; POST /documents/{id}/versions; PUT /documents/{id}/permissions; GET /documents/{id}/versions/{v}/download (URL bertanda tangan berumur pendek); GET /documents/{id}/versions/{v}/status; POST /documents/{id}/versions/{v}/reindex | Policy; tulis oleh knowledge_manager | unggah 10/menit; lain 60/menit |

| Dataset dan analitik | GET, POST /datasets; GET /datasets/{id}; PUT /datasets/{id}/columns; PUT /datasets/{id}/permissions; GET /metrics; POST /analytics/run | Policy; tulis oleh data_analyst | analytics/run 30/menit |

| Percakapan | GET, POST /conversations; GET, DELETE /conversations/{id}; POST /conversations/{id}/messages | Pemilik | kirim pesan 10/menit per pengguna |

| Umpan balik | POST /messages/{id}/feedback; GET /feedback | Pemilik percakapan; knowledge_manager | 60/menit |

| Diagnostik | GET /messages/{id}/trace | org_admin | 30/menit |

| Audit dan pengaturan | GET /audit-logs (rentang waktu wajib, maksimum 90 hari); GET, PATCH /settings | org_admin | 60/menit |

| Sumber data | GET, POST, PATCH /data-sources; POST /data-sources/{id}/sync; GET /data-sources/{id}/sync-runs | knowledge_manager, org_admin | 60/menit |

| Kesehatan | GET /health | Publik tanpa detail internal | dibatasi proxy |



Seluruh nilai batas laju adalah hipotesis awal dan ditinjau setelah pengukuran penggunaan nyata.

Contoh `POST /documents` (202 Accepted):

{ "data": { "id": "0192...", "version_id": "0192...", "status": "pending",

"ingestion": { "status": "queued" } } }

Contoh `POST /analytics/run`:

{ "dataset_id": "0192...", "metric_code": "cagr",

"params": { "value_column": "realisasi", "period_column": "tahun", "start": 2020, "end": 2024 } }

Respons memuat value, unit, formula, inputs, source, dan warnings. Nilai pada contoh dokumentasi hanyalah ilustrasi format, bukan hasil eksekusi.

Respons `POST /conversations/{id}/messages` memuat message_id, route, status, answer, citations (dokumen, versi, bagian, halaman, kutipan, URL sumber), dan trace_id. Untuk rute analitik, respons memuat metric (nilai, rumus, input, sumber), table, dan chart_spec. Untuk pertanyaan yang memicu abstain, answer menyatakan bukti tidak ditemukan dan citations kosong.

17.4 Kontrak Internal Laravel ke FastAPI (`/internal/v1`)

| Method dan endpoint | Tujuan | Catatan |

| --- | --- | --- |

| POST /ingestions | Mulai ingestion versi dokumen | Balas 202; Idempotency-Key wajib; memuat document_id, version_id, storage_key, acl_principals, status |

| GET /ingestions/{id} | Status | - |

| PUT /documents/{id}/acl | Ganti acl_principals seluruh chunk dokumen | Sinkron; balas 200 hanya setelah komit |

| PUT /documents/{id}/status | Tandai active atau revoked, dan is_current | Sinkron |

| DELETE /documents/{id} | Hapus chunk dokumen | Idempoten |

| POST /documents/{id}/versions/{v}/reindex | Indeks ulang | Balas 202 |

| POST /datasets; PUT /datasets/{id}/columns | Impor dataset ke DuckDB; kamus data | Impor 202 |

| POST /ask | Router, retrieval atau metrik, generasi | Lihat di bawah |

| POST /analytics/run | Jalankan metrik langsung | metric_code, params |

| GET /health | Kesehatan | - |



`POST /internal/v1/ask` menerima query, conversation_context, access.principals, access.allowed_dataset_ids, dan options (top_k, language). Respons memuat route, route_confidence, status, answer, citations (document_id, version_id, chunk_id, page_start, page_end, section_path, excerpt), metric, chart_spec, trace_id, dan model_info (model, versi prompt).

Kontrak keamanan: principals dan allowed_dataset_ids berasal dari Laravel dan tidak pernah dari klien. Bila principals kosong, FastAPI mengembalikan nol hasil, bukan seluruh dokumen.

17.5 Kontrak Internal Laravel ke CodeIgniter dan Callback

| Arah | Method dan endpoint | Tujuan |

| --- | --- | --- |

| LV ke CI | POST /sources; PATCH, DELETE /sources/{id} | Buat, ubah, nonaktifkan konfigurasi konektor (kredensial dienkripsi di sisi CI) |

| LV ke CI | POST /sources/{id}/sync; GET /sources/{id}/runs | Picu sinkronisasi (idempoten); riwayat |

| CI ke LV | POST /connector/items | Serahkan berkas baru atau berubah (multipart: core_source_id, external_id, content_hash, berkas; Idempotency-Key wajib) |

| CI ke LV | POST /connector/items/{external_id}/removed | Item hilang di sumber |

| AI ke LV | POST /ingestions/{id}/status; POST /datasets/{id}/status | Callback status done atau failed (galat ringkas tanpa isi dokumen) |



Laravel menerima item konektor, memberlakukan ACL bawaan sumber (data_sources.default_acl), dan memutuskan apakah dokumen baru atau versi baru. CodeIgniter tidak menentukan izin.

17.6 JWT Antarlayanan (DEC-030)

| Klaim | Isi |

| --- | --- |

| iss | core, connector, atau ai |

| aud | Layanan tujuan |

| sub | Identitas layanan; konteks pengguna pada klaim act |

| jti | Pengenal unik (cegah replay dalam masa berlaku) |

| iat, exp | exp paling lama 5 menit setelah iat |

| scope | Daftar izin (misalnya ai:ask, ai:ingest) |

| principals | Hanya pada panggilan atas nama pengguna; ukuran maksimum ditetapkan |



Setiap penerbit menandatangani dengan kunci asimetris miliknya; penerima menyimpan kunci publik penerbit. Algoritma (EdDSA atau RS256) dikunci pada M0 sesuai dukungan pustaka. Penerima menolak token kedaluwarsa, aud salah, iss tak dikenal, dan jti yang diulang.

18. Integration Architecture

18.1 Alur Ingestion (DEC-028)

Knowledge Manager mengunggah berkas ke Laravel.

Laravel memvalidasi tipe, ukuran, MIME dan magic bytes; memindai dengan ClamAV (gagal pindai = tolak); menyimpan ke object store dengan kunci acak; mencatat dokumen, versi, izin, dan audit.

Laravel memanggil POST /internal/v1/ingestions (JWT layanan, Idempotency-Key); ai-api membalas 202 dan menulis baris di ai.jobs.

ai-worker mengambil pekerjaan (SKIP LOCKED), mengunduh berkas, mem-parsing, chunking berbasis struktur, embedding, lalu menyimpan chunk, vektor, dan acl_principals dalam satu transaksi per versi.

ai-worker memanggil callback status ke Laravel (done atau failed); Laravel memperbarui status versi dan mencatat audit.

Versi baru menjadikan chunk versi lama is_current = false secara atomik setelah versi baru berhasil terindeks.

18.2 Alur Pertanyaan

Pengguna mengirim pesan; Laravel mengautentikasi dan membentuk principals (DD-14) dan allowed_dataset_ids.

Laravel memanggil POST /internal/v1/ask dengan JWT layanan.

ai-api mengklasifikasikan niat (Bagian 26), menjalankan jalur dokumen atau analitik, dan mengembalikan jawaban, sitasi, metrik, spesifikasi grafik, serta jejak.

Laravel memverifikasi Policy untuk tiap document_id pada sitasi; sitasi tak lolos dibuang dan dicatat sebagai anomali keamanan.

Laravel menyimpan pesan, sitasi, model_info, dan audit, lalu mengirim respons terverifikasi.

18.3 Alur Perubahan Izin dan Pencabutan

Knowledge Manager mengubah izin atau status dokumen di Laravel.

Pencabutan (hapus izin, ubah ke dicabut): Laravel menandai di core, memanggil PUT /internal/v1/documents/{id}/acl atau /status secara sinkron, dan baru mengembalikan sukses setelah AI mengonfirmasi komit. Bila gagal: aksi dinyatakan gagal, dokumen tetap ditandai revoked di Laravel (sitasinya dibuang pada verifikasi lapis kedua), dan sinkronisasi dicoba ulang.

Pemberian izin boleh asinkron melalui antrean Laravel dengan retry.

Rekonsiliasi harian membandingkan ACL core dan ai; selisih dicatat sebagai insiden dan diperbaiki.

18.4 Alur Sinkronisasi Konektor

Jadwal (atau pemicu manual) memanggil konektor CodeIgniter; konektor memindai folder dan menghitung hash isi.

Item baru atau berubah diserahkan ke POST /internal/v1/connector/items dengan Idempotency-Key turunan dari source_id, external_id, dan content_hash.

Laravel membuat dokumen atau versi baru dengan ACL dari default_acl sumber, lalu menjalankan alur ingestion 18.1.

Item hilang dilaporkan lewat endpoint removed; Laravel memutuskan apakah dokumen dicabut.

Kegagalan sumber atau jaringan dicatat pada sync_runs tanpa merusak data yang ada.

18.5 Penanganan Kegagalan (Nilai Awal Hipotesis)

| Kegagalan | Perilaku |

| --- | --- |

| ai-api tidak sehat | Laravel mengembalikan 503 AI_UNAVAILABLE; endpoint non-AI tetap berfungsi; circuit breaker sederhana setelah kegagalan beruntun |

| Timeout ask | Batas awal 60 detik; hasil 504 UPSTREAM_TIMEOUT; tidak ada retry otomatis untuk pertanyaan pengguna |

| Callback ingestion gagal | Worker mengulang dengan backoff eksponensial; rekonsiliasi harian menutup celah |

| Job worker gagal | Retry hingga max_attempts; kemudian failed dengan pesan ringkas tanpa isi dokumen; dapat dijalankan ulang |

| Penyedia LLM galat atau habis kuota | Error terstruktur; tidak ada fallback ke penyedia lain tanpa konfigurasi eksplisit |

| Object store tidak tersedia | Unggah ditolak dengan error terstruktur; tidak ada catatan dokumen yatim (transaksi dan kompensasi) |

| Konektor mati | Tidak memengaruhi pertanyaan; status sumber ditampilkan di panel admin |



BAGIAN IV — AI AND DATA ARCHITECTURE

19. Knowledge Management

19.1 Siklus Hidup Dokumen

| Status dokumen | Arti | Muncul di retrieval |

| --- | --- | --- |

| draft | Diunggah, belum berlaku | Tidak |

| active | Berlaku; punya satu versi terkini | Ya, sesuai izin |

| revoked | Dicabut | Tidak; riwayat tetap dapat dilihat Knowledge Manager |



| Status versi | Arti |

| --- | --- |

| pending | Menunggu atau sedang diindeks |

| indexed | Berhasil diindeks |

| failed | Gagal; dapat diulang |

| superseded | Digantikan versi lebih baru |

| revoked | Dicabut |



19.2 Aturan Versi Berlaku

Versi terkini dokumen adalah versi indexed dengan version_no tertinggi yang effective_date-nya kosong atau tidak melebihi hari ini (zona waktu organisasi).

Versi dengan effective_date di masa depan diindeks lebih dulu tetapi is_current = false; pekerjaan terjadwal Laravel membalik status pada tanggal efektif dan menyinkronkannya ke AI.

Hanya satu versi per dokumen yang is_current = true pada satu waktu.

Retrieval hanya mengambil chunk status = 'active' dan is_current = true. Versi lama tetap tersimpan untuk audit dan sitasi historis (sitasi pada pesan lama merujuk version_id yang tepat).

19.3 Metadata Dokumen

Judul, pemilik, sumber, nomor dan tanggal (bila ada, diisi Knowledge Manager), tanggal efektif, status, izin, hash isi (SHA-256), tipe MIME, ukuran, serta riwayat versi. Metadata struktural hasil parsing (halaman, bagian) berada pada chunk.

19.4 Izin Dokumen

Izin ditetapkan Knowledge Manager per peran, unit (berlaku untuk unit turunan, DD-14), pengguna, atau all. Dokumen dari konektor memperoleh izin dari data_sources.default_acl dan dapat diubah Knowledge Manager. Dokumen tanpa aturan izin tidak dapat diaktifkan (minimal satu aturan).

19.5 Backlog Tata Kelola (Bukan MVP)

| ID | Usulan | Alasan penundaan |

| --- | --- | --- |

| BL-01 | Penandaan sensitivitas dokumen (misalnya restricted) yang mengecualikan chunk dari konteks bila penyedia LLM eksternal aktif | Menyentuh kebijakan privasi (OA-03); dirancang setelah Owner menetapkan kebijakan data |

| BL-02 | Klasifikasi dokumen otomatis | Bukan inti RQ |



20. Document Ingestion

20.1 Pipeline

| Tahap | Pemilik | Kontrol |

| --- | --- | --- |

| Penerimaan | Laravel | Batas ukuran (awal 50 MB), daftar tipe, MIME dan magic bytes di server, nama dinormalisasi, penyimpanan di bucket privat dengan kunci acak |

| Pemindaian | Laravel | ClamAV; hasil positif atau pemindai tidak tersedia = tolak (fail-closed), kode MALWARE_DETECTED atau UPSTREAM_ERROR |

| Parsing | ai-worker | Terisolasi, batas waktu dan memori; tidak mengeksekusi makro atau mengikuti tautan eksternal |

| Chunking | ai-worker | Berbasis struktur dengan overlap dan parent-child; metadata halaman dan bagian wajib |

| Embedding dan indeks | ai-worker | Embedding, content_tsv, acl_principals, status, is_current; satu transaksi per versi |

| Callback | ai-worker | Status done atau failed ke Laravel |



Isi dokumen diperlakukan sebagai data tidak tepercaya pada seluruh tahap.

20.2 Aturan Parsing per Format

| Format | Aturan |

| --- | --- |

| PDF berteks | Ekstraksi per halaman dengan pelestarian urutan baca; header dan footer berulang dibuang untuk pengindeksan tetapi nomor halaman dipertahankan; penyambungan kata terpotong. Halaman tanpa lapisan teks dilaporkan sebagai peringatan needs_ocr dan tidak diindeks (OCR Phase 2) |

| DOCX | Struktur dari gaya heading; tabel diekstraksi sebagai teks baris dengan header; makro diabaikan |

| XLSX dan CSV sebagai dokumen | Diindeks sebagai teks: nama sheet, baris header diulang pada tiap chunk, kelompok baris sebagai chunk. Berkas ini tidak dipakai untuk perhitungan; untuk analitik, impor sebagai dataset (Bagian 23) |

| Batas | Maksimum halaman, sel, dan baris per berkas ditetapkan pada konfigurasi (nilai awal hipotesis) untuk mencegah decompression bomb dan pemrosesan berlebih |



Pemilihan pustaka PDF dilakukan pada M3 dengan uji perbandingan pada berkas sampel (kualitas urutan baca, fidelitas teks, kecepatan, lisensi). PyMuPDF dikecualikan karena AGPL (DEC-039).

20.3 Chunking

Struktur dahulu. Pemisah berdasarkan heading, "BAB", "Pasal", "Ayat", penomoran bertingkat, dan gaya DOCX; section_path disimpan, misalnya "BAB II > Pasal 5 > Ayat 2".

Child chunk untuk retrieval dengan target ukuran awal sekitar 350 token dan overlap sekitar 50 token (hipotesis; ukuran dan overlap diperlakukan sebagai parameter tetap pada ablasi inti RQ1 dan dicatat). Parent chunk (satu bagian) disimpan untuk perluasan konteks pada generasi.

Fidelitas. Teks chunk identik dengan teks sumber (hanya normalisasi spasi); tidak ada penulisan ulang, peringkasan, atau pengubahan angka (AC-M3-02). Offset ke teks sumber disimpan untuk verifikasi.

Token dihitung dengan tokenizer model embedding terpilih.

Setiap chunk membawa document_id, version_id, page_start, page_end, section_path, acl_principals, status, is_current.

20.4 Embedding

Antarmuka EmbeddingProvider dengan konfigurasi per model (awalan kueri dan pasase bila model membutuhkannya, normalisasi, dimensi). Implementasi default berjalan in-process di ai-worker; endpoint inferensi eksternal dapat dikonfigurasi. Model dan dimensi dicatat di ai.embedding_models dan pada setiap document_index_state. Penggantian model memicu reindex (FR-038) dengan penggantian atomik.

20.5 Penanganan Galat

| Kondisi | Perilaku |

| --- | --- |

| Berkas rusak atau tidak dapat di-parsing | Status failed, pesan ringkas tanpa isi dokumen, dapat diulang |

| Worker mati di tengah proses | Transaksi dibatalkan; job kembali queued melalui batas kunci; tidak ada chunk separuh |

| Penggantian versi | Chunk versi lama dinonaktifkan hanya setelah versi baru sukses terindeks |

| Ukuran melebihi batas | 413 sebelum pemrosesan |



21. RAG Architecture

21.1 Komponen dan Alur

Gambar 21.1 Alur pertanyaan: Hybrid Router, jalur dokumen, dan jalur analitik

| Komponen | Fungsi |

| --- | --- |

| Router Intent | Mengklasifikasikan niat (Bagian 26) |

| Retriever | Leksikal dan dense dengan filter akses, digabung RRF (Bagian 22) |

| Reranker | Cross-encoder pada kandidat teratas |

| Sufficiency check | Menentukan apakah bukti cukup atau abstain |

| Generator bersitasi | LLM menjawab hanya dari konteks, dengan sitasi per klaim |

| Guardrails | Penyaringan injeksi, validasi keluaran, pembuangan sitasi tak sah |

| LLM Client | Antarmuka tertukar; mencatat model, versi prompt, dan parameter |



Orkestrasi memakai kode sendiri yang ringan (DEC-018) agar eksperimen mudah direproduksi dan dependensi minimal.

21.2 Kontrak Prompt Generasi

Instruksi sistem dipisahkan dari konteks. Konteks diberi penanda sebagai data tidak tepercaya; isi konteks tidak boleh dianggap instruksi.

Model diminta menjawab dalam Bahasa Indonesia hanya berdasarkan konteks yang diberikan.

Keluaran terstruktur (JSON): daftar segmen jawaban, tiap segmen dengan daftar chunk_id yang mendukung, serta penanda sufficient (benar atau salah).

Bila konteks tidak cukup, model wajib menyatakannya; sistem lalu mengembalikan no_evidence.

Model tidak memegang alat yang dapat mengubah data, mengirim data keluar, atau membaca sumber di luar konteks.

Keluaran dibatasi: tanpa HTML, skrip, gambar eksternal, atau tautan selain sumber internal.

Template prompt disimpan di repositori dengan versi prompt (hash) yang dicatat pada model_info. Temperature rendah (awal 0) dan seed bila didukung penyedia.

21.3 Logika Abstain

Abstain terjadi bila salah satu terpenuhi: (a) retrieval kosong; (b) skor reranker teratas di bawah ambang yang dikalibrasi pada dev split; (c) model menandai konteks tidak cukup; (d) tidak satu pun sitasi lolos validasi. Tingkat abstain benar diukur pada subset pertanyaan tanpa jawaban (AC-M4-04). Ambang ditetapkan dari baseline, bukan angka tebakan.

21.4 Parameter Awal (Hipotesis, Dituning pada Dev Split)

| Parameter | Nilai awal | Catatan |

| --- | --- | --- |

| Kandidat leksikal dan dense | 50 dan 50 | Sebelum fusi |

| RRF k | 60 | Dari rujukan sekunder; diverifikasi ke makalah asli lalu diuji sensitivitasnya (grid 20, 60, 100) |

| Kandidat ke reranker | 30 | Rentang 20 sampai 50 |

| Chunk ke generator | 8 (dengan perluasan parent bila anggaran token memungkinkan) | Anggaran token konteks dikonfigurasi |

| Temperature | 0 | - |



21.5 Pemilihan Model: Protokol Evaluasi (FINAL (METODE))

Tidak ada hasil yang dikarang. Pemenang dikunci hanya setelah eksperimen dan dicatat di Decision Register dengan identifikasi run.

| Keputusan | Kandidat | Metode | Kriteria |

| --- | --- | --- | --- |

| Embedding (DEC-014) | BGE-M3, multilingual-e5 (varian yang dapat dijalankan), dan kandidat Indonesia atau multibahasa lain yang ditemukan dan lisensinya sesuai | Retrieval dense saja pada dev split golden dataset; Recall@{5,10,20}, MRR, nDCG@10; ukur latensi indeks dan kueri serta memori | Utama: nDCG@10 dev. Kendala: lisensi dapat dipakai, dimensi dapat diindeks (<= 2.000 atau halfvec), latensi dan memori sesuai perangkat keras demo. Seri: model lebih kecil |

| Reranker (DEC-014) | bge-reranker-v2-m3 dan kandidat lain | Hibrida tanpa dan dengan reranker; nDCG@10 dan latensi tambahan | Peningkatan nDCG@10 terukur dengan biaya latensi dapat diterima; dilaporkan apa adanya meski tidak signifikan |

| BM25 (DEC-029) | FTS ts_rank, BM25 sejati (kandidat diverifikasi) | Ablasi C0 dan C1 | Kriteria 16.6 |

| LLM (DEC-013) | Minimal tiga kandidat: satu komersial, satu lokal bila perangkat keras ada (OA-04), satu cadangan | Generasi pada dev split: faithfulness, akurasi sitasi, akurasi abstain, penilaian manual kualitas Bahasa Indonesia pada sampel, latensi, biaya per pertanyaan | Skor berbobot yang ditetapkan sebelum run dan tidak diubah setelah melihat hasil: faithfulness 0,35; akurasi sitasi 0,25; akurasi abstain 0,15; kualitas bahasa 0,10; biaya dan latensi ternormalisasi 0,15. Kendala keras: kebijakan data Owner (OA-03) |

| Konfirmasi | Model terpilih | Dijalankan sekali pada test split yang dibekukan | Tidak boleh dipakai menyetel prompt atau parameter |



Bila LLM-judge dipakai untuk faithfulness, bias penilai dicatat dan sebagian sampel diperiksa manual (RISK-27).

22. Retrieval dan Citation

22.1 Retrieval Hibrida Berfilter Akses

Laravel mengirim principals (DD-14). Bila kosong, hasil nol.

Dua pencarian paralel pada ai.chunks, keduanya dengan klausa acl_principals && :principals AND status = 'active' AND is_current:

Leksikal: content_tsv (baseline FTS simple; BM25 sejati bila terpilih).

Dense: jarak vektor pada embedding (HNSW, iterative scan sesuai hasil M0).

Reciprocal Rank Fusion: skor akhir sebuah chunk adalah jumlah dari 1 dibagi (k + peringkat chunk pada tiap daftar); chunk yang tidak muncul pada suatu daftar tidak menyumbang skor dari daftar itu.

Deduplikasi per parent bila beberapa child dari bagian sama bersaing.

Reranker menilai pasangan (pertanyaan, chunk) pada kandidat teratas dan menghasilkan urutan akhir.

Hasil dikirim ke sufficiency check dan generator.

Filter diterapkan sebelum atau di dalam pencarian, bukan sesudah, agar dokumen tanpa izin tidak pernah masuk kandidat. Risiko ANN berfilter yang mengembalikan hasil kurang diuji di M0 (AC-M0-06).

22.2 Format Sitasi

| Field | Isi |

| --- | --- |

| document_id, version_id, chunk_id | Identitas tepat sumber |

| title | Judul dokumen |

| section_path | Misalnya "BAB II > Pasal 5 > Ayat 2" |

| page_start, page_end | Halaman |

| excerpt | Kutipan pendek (maksimum sekitar 300 karakter) dari chunk |

| url | Tautan unduhan bertanda tangan ke versi dokumen |



Antarmuka menampilkan sitasi bernomor yang dapat dibuka ke dokumen, bagian, dan halaman.

22.3 Verifikasi Sitasi

| Lapis | Pemeriksa | Pemeriksaan |

| --- | --- | --- |

| 1 | FastAPI | Setiap chunk_id yang dikutip harus termasuk himpunan chunk hasil retrieval; sitasi lain dibuang |

| 2 | Laravel | Policy view untuk setiap document_id; sitasi tak lolos dibuang dan dicatat sebagai insiden keamanan |

| 3 (Phase 2) | FastAPI | Verifikasi entailment antara klaim dan kutipan (FR-043) |



Jawaban yang kehilangan seluruh sitasi setelah verifikasi diubah menjadi no_evidence.

23. Analytics Assistant

23.1 Alur Impor Dataset

Data Analyst mengunggah CSV atau XLSX ke Laravel (validasi tipe, ukuran awal 100 MB, MIME, pemindaian ClamAV).

Laravel menyimpan berkas ke object store, mencatat datasets, dan memanggil POST /internal/v1/datasets (202).

ai-worker membaca berkas (CSV melalui read_csv DuckDB, XLSX melalui openpyxl lalu dimuat ke DuckDB), melakukan profil kolom: tipe, jumlah null, nilai unik, rentang.

Format angka dan tanggal Indonesia: pemisah desimal koma dan ribuan titik, persentase, awalan "Rp", tanggal dd/mm/yyyy dan nama bulan Indonesia dideteksi; konfigurasi locale_config dapat dikoreksi Data Analyst sebelum impor dikonfirmasi.

Kolom ukuran bertipe desimal disimpan sebagai DECIMAL (DEC-048).

Impor atomik: baris galat dilaporkan (nomor baris, kolom, alasan); bila melebihi ambang yang dikonfigurasi, impor dibatalkan; tidak ada data separuh (AC-M5-07).

ai-worker memanggil callback status; Laravel memperbarui datasets dan dataset_columns.

23.2 Kamus Data

Data Analyst melengkapi arti, satuan, dan peran kolom (dimensi atau ukuran). Kamus tampil pada hasil analitik dan menjadi satu-satunya konteks skema bagi metric planner. Metrik yang membutuhkan satuan wajib memiliki satuan terisi; satuan berbeda ditolak atau diberi peringatan.

23.3 Alur Pertanyaan Angka

Router mengarahkan ke jalur analitik (Bagian 26).

Metric Planner (LLM function calling) menerima pertanyaan, daftar dataset yang diizinkan, nama kolom, tipe, satuan, deskripsi, dan nilai unik dimensi dalam jumlah terbatas (awal 50; dapat dimatikan bila penyedia LLM eksternal tidak boleh menerima data nilai). Planner tidak menerima baris data mentah.

Planner memilih metric_code dan parameter. Validasi: parameter sesuai params_schema, kolom ada pada dataset dan perannya benar, filter memakai kolom yang sah. Gagal atau ambigu: clarification_required atau METRIC_INVALID_PARAMS.

Metric Engine menjalankan fungsi deterministik pada DuckDB dibuka read-only; SQL dibangun dari templat berparameter dengan identifier dari daftar putih, bukan ditulis LLM.

Hasil: nilai, satuan, rumus, input, jumlah baris dipakai, peringatan, dan SQL yang dieksekusi (ditampilkan pada pengguna).

LLM menyusun spesifikasi grafik JSON; validator menolak spesifikasi tidak valid (Bagian 28.4) dan memeriksa bahwa nilai grafik identik dengan hasil hitung. Gagal: tampilkan tabel.

Penjelasan naratif singkat boleh disusun LLM tanpa memperkenalkan angka baru; setiap angka pada narasi harus cocok dengan hasil hitung (validator mengekstrak angka dan membandingkan).

24. Metric Engine

24.1 Kontrak Fungsi Metrik

Setiap metrik adalah fungsi murni dengan: skema parameter (JSON Schema) di metric_definitions.params_schema; keluaran terstruktur {value|values, unit, formula, inputs, rows_used, warnings, query}; penanganan kasus tepi eksplisit (peringatan atau penolakan, tidak pernah angka salah secara diam-diam); dan uji unit termasuk uji properti.

24.2 Katalog Metrik MVP

| Kode | Definisi | Kasus tepi |

| --- | --- | --- |

| cagr | (V_akhir / V_awal) dipangkatkan (1 / n), dikurangi 1. n adalah jumlah periode antara pengamatan awal dan akhir (contoh: 2020 sampai 2024 berarti n = 4), bukan selisih tahun yang tertulis di pertanyaan | V_awal nol atau negatif: tolak; V_akhir negatif: tolak; periode hilang di antara awal dan akhir: peringatan atau tolak sesuai parameter; satuan berbeda: tolak |

| yoy | (V_t - V_t-1) / abs(V_t-1) per periode tahunan | Pembagi nol: nilai tak terdefinisi dengan peringatan; basis negatif: dinyatakan pada peringatan |

| mom | Sama dengan yoy untuk periode bulanan | Bulan hilang: peringatan; tidak diisi otomatis |

| moving_average | Rata-rata bergerak sederhana (trailing) dengan jendela k | k - 1 nilai pertama tak terdefinisi; data tidak berurutan diurutkan menurut kolom periode; periode hilang: peringatan |

| share | Bagian terhadap total: jumlah kelompok / jumlah total, dalam persen | Total nol: tolak; nilai negatif: peringatan |

| rank | Peringkat atau N teratas dan terbawah menurut ukuran | Seri diperlakukan dengan competition ranking (1, 2, 2, 4) sebagai bawaan; parameter lain dapat dipilih |

| aggregate | SUM, AVG, MIN, MAX, COUNT, COUNT DISTINCT, MEDIAN per satu atau lebih dimensi dengan filter opsional (sama dengan, di antara, dalam daftar) | Kolom non-numerik untuk SUM atau AVG: tolak; hasil kosong: dinyatakan, bukan nol |

| delta | Selisih absolut antara dua periode atau kelompok | Satuan berbeda: tolak |



Penambahan metrik baru mengikuti kontrak 24.1 dan memerlukan uji sebelum diaktifkan.

24.3 Kebijakan Numerik (DEC-048)

Nilai ukuran bertipe desimal diproses sebagai DECIMAL atau Decimal Python; pecahan dan rasio dihitung dengan presisi tinggi terdokumentasi.

Pembulatan hanya pada tampilan (awal: setengah ke atas, dua desimal untuk persen) dan diumumkan pada hasil; nilai internal tidak dibulatkan.

Tampilan memakai format Indonesia (koma desimal, titik ribuan) pada antarmuka; API membawa angka bertipe numerik, bukan teks berformat.

Tidak ada konversi satuan implisit.

Toleransi uji untuk rasio: ditetapkan pada pengujian dan dicatat (awal 1e-9 pada perbandingan float turunan).

24.4 Pengujian Metrik

Minimal per metrik: kasus normal, setiap kasus tepi pada 24.2, data dengan nilai null, satu dimensi dan banyak dimensi, data acak melalui uji properti (misalnya CAGR dari deret yang dibangkitkan dengan laju diketahui harus memulihkan laju tersebut dalam toleransi). Seluruh kasus lulus 100% (AC-M5-01). Tidak ada jalur kode yang membiarkan LLM menghasilkan angka akhir (AC-M5-03), diperiksa lewat tinjauan kode dan uji.

25. Text-to-SQL (Phase 2, FR-040)

Tidak ada text-to-SQL pada MVP. Spesifikasi berikut mengikat saat P2-A dimulai.

| Tahap | Ketentuan |

| --- | --- |

| Schema linking | LLM menerima skema dan kamus data dataset yang diizinkan saja |

| Pembangkitan | LLM menghasilkan satu pernyataan SQL dialek DuckDB |

| Validasi | Parser SQL (kandidat sqlglot, diverifikasi) menolak apa pun selain satu SELECT; tanpa multi-statement, DDL, DML, COPY, ATTACH, INSTALL, LOAD, pembacaan file atau jaringan; tabel dibatasi pada allowed_dataset_ids |

| Eksekusi | DuckDB read-only; ekstensi dan akses sistem berkas dan jaringan dinonaktifkan; batas waktu, memori, dan jumlah baris hasil |

| Koreksi diri | Maksimum dua percobaan dengan pesan galat DuckDB |

| Transparansi | SQL yang dieksekusi dicatat di audit dan ditampilkan kepada pengguna |

| Evaluasi | Execution accuracy pada golden dataset; seluruh uji penulisan, DDL, dan akses file harus ditolak (NFR-SEC-003) |



Text-to-SQL berposisi sebagai jalur cadangan setelah metrik teruji; metrik teruji tetap dipilih lebih dulu bila pertanyaan sesuai katalog.

26. Hybrid Router

26.1 Kelas dan Keputusan

| Kelas | Contoh | Jalur |

| --- | --- | --- |

| document | "Apa syarat cuti akademik menurut peraturan rektor?" | RAG |

| analytics | "Berapa CAGR realisasi anggaran 2020 sampai 2024?" | Metric planner dan engine |

| out_of_scope | Pertanyaan umum yang tidak terkait dokumen atau data organisasi | Tolak dengan penjelasan |

| hybrid (Phase 2) | Pertanyaan majemuk dokumen dan angka | Dekomposisi, kedua jalur, gabung hasil dan sumber |



Keyakinan di bawah ambang memicu clarification_required yang meminta pengguna memilih jalur, bukan tebakan.

26.2 Implementasi (DEC-040)

Antarmuka IntentClassifier dengan dua implementasi yang dibandingkan pada dev split dan dipilih berdasarkan F1 makro serta latensi:

LLM few-shot dengan keluaran JSON terstruktur (kelas dan keyakinan).

Baseline ringan: TF-IDF dengan regresi logistik (atau setara) dilatih pada dev split. Pracetak menunjukkan fitur TF-IDF cukup kompetitif pada klasifikasi tipe kueri, sehingga baseline ini wajib dievaluasi (Lampiran C).

Aturan praprosesor sebelum klasifikasi: nama dataset yang diizinkan atau istilah metrik yang eksplisit menambah sinyal analitik; tidak ada aturan yang melewati pemeriksaan akses.

26.3 Ketentuan

Router hanya melihat pertanyaan dan nama dataset yang diizinkan, tidak isi dokumen.

Router tidak pernah memperluas akses.

Antarmuka menampilkan jalur terpilih dan memungkinkan pengguna mengubahnya; permintaan pesan menerima route_hint opsional (document atau analytics) yang tetap melewati seluruh pemeriksaan akses.

Keputusan dan keyakinan dicatat pada messages.route dan route_confidence.

Ambang keyakinan ditetapkan dari baseline pada dev split (AC-M6-01).

Metrik: precision, recall, F1 per kelas pada golden dataset.

BAGIAN V — APPLICATION DEVELOPMENT

27. Frontend Architecture

27.1 Keputusan (DEC-012, DEC-042)

Vue 3 (Composition API) dengan TypeScript, Vite, Pinia, Vue Router, Tailwind CSS, dan ECharts (impor modular agar ukuran bundel terkendali). Komponen primitif aksesibel dibuat sendiri atau memakai pustaka headless yang dicatat pada docs/VERSIONS.md; tidak memakai kerangka komponen besar tanpa alasan tercatat. Klien API bertipe dibangkitkan dari OpenAPI (openapi-typescript) sehingga perubahan kontrak terdeteksi saat kompilasi. Internasionalisasi memakai vue-i18n dengan Bahasa Indonesia sebagai bahasa bawaan.

27.2 Struktur dan Aturan

| Aspek | Ketentuan |

| --- | --- |

| Struktur | features/ (auth, chat, documents, datasets, admin, audit), shared/ (api, ui, i18n, utils), stores/ |

| Akses data | Seluruh pemanggilan lewat satu lapisan API client; tidak ada fetch tersebar di komponen |

| State | Pinia untuk sesi, percakapan aktif, dan preferensi; data server tidak digandakan tanpa kebutuhan |

| Rute dan guard | Guard berbasis peran hanya untuk pengalaman pengguna; penegakan sebenarnya di backend |

| Aturan tampilan | Tidak ada layar tanpa endpoint nyata; tidak ada data tiruan pada build produksi |



27.3 Autentikasi di Sisi Klien (DEC-031)

Token akses (opaque, berumur sekitar 15 menit) disimpan hanya di memori aplikasi; tidak di localStorage atau sessionStorage.

Refresh token berputar (rotation) disimpan pada cookie HttpOnly, Secure, SameSite=Strict, dengan Path=/api/v1/auth. Penggunaan ulang refresh token lama mencabut seluruh rantai token pengguna tersebut.

Pada muat ulang halaman, SPA memanggil /auth/refresh untuk memulihkan sesi. Respons 401 memicu satu percobaan refresh lalu logout bila gagal.

SPA dan API disajikan dari asal yang sama melalui reverse proxy, sehingga tidak ada CORS lintas asal.

27.4 Penyajian Keluaran Model

Teks jawaban dirender sebagai Markdown terbatas dan disanitasi (DOMPurify): tanpa HTML mentah, tanpa gambar eksternal, tautan hanya ke sumber internal. Sitasi dirender sebagai komponen tersendiri dari data terstruktur, bukan dari parsing teks bebas.

28. UI/UX Requirements

28.1 Layar MVP

| Layar | Peran | Kebutuhan utama |

| --- | --- | --- |

| Login dan ganti kata sandi | Semua | Pesan galat netral; layar ganti kata sandi wajib untuk akun dengan must_change_password |

| Chat | Semua | Kotak pertanyaan, riwayat percakapan, jawaban dengan sitasi, indikator jalur (dokumen atau analitik) dengan opsi mengubah, tabel dan grafik, umpan balik benar atau salah, status memuat dan galat |

| Penampil sumber | Semua | Membuka dokumen pada halaman dan bagian yang dikutip |

| Dokumen | Knowledge Manager | Daftar, filter status, unggah, versi, status ingestion, izin, cabut |

| Dataset | Data Analyst | Impor, profil kolom, kamus data, izin |

| Sumber data | Knowledge Manager, Org Admin | Konfigurasi konektor folder, jadwal, sinkron manual, riwayat sinkron |

| Umpan balik | Knowledge Manager | Tinjau jawaban bertanda salah |

| Admin | Org Admin | Pengguna, peran, unit, pengaturan, log audit, trace pesan |



28.2 Prinsip UX

Kejujuran sistem: tampilkan sumber, rumus, dan batas; pesan "bukti tidak ditemukan" dirancang sebagai hasil yang sah, bukan kegagalan.

Status nyata: progres ingestion dan impor tampil dengan status sebenarnya dari backend; tidak ada indikator palsu.

Galat yang dapat ditindaklanjuti: pesan dalam Bahasa Indonesia, tanpa detail teknis berbahaya, dengan correlation_id untuk dukungan.

Responsif pada lebar desktop dan tablet; layout seluler fungsional untuk chat.

Aksesibilitas (NFR-ACC-001): navigasi papan ketik, fokus terlihat, kontras sesuai WCAG 2.2 AA, label untuk pembaca layar, grafik disertai tabel data setara.

Lokalisasi (NFR-I18N-001): angka berformat Indonesia, tanggal berzona Asia/Jakarta.

28.3 Keadaan Antarmuka yang Wajib Ada

Memuat, kosong, galat, tidak berizin (404 ditampilkan netral), no_evidence, clarification_required, out_of_scope, batas laju (dengan waktu tunggu), layanan AI tidak tersedia, sesi berakhir.

28.4 Spesifikasi Grafik dan Validator (DEC-041)

Spesifikasi grafik adalah JSON yang divalidasi skema (contracts/schemas/chart_spec.json) sebelum dikirim ke klien dan lagi pada klien sebelum render.

| Aspek | Ketentuan |

| --- | --- |

| Tipe yang diizinkan | line, bar, pie (hanya untuk proporsi), scatter (opsional) |

| Isi | Judul, label sumbu, satuan, seri dengan nilai numerik, kategori |

| Dilarang | Fungsi atau string yang dieksekusi (formatter JavaScript), HTML, URL eksternal, gambar |

| Kesesuaian data | Nilai pada seri harus identik dengan hasil hitung atau baris query (pemeriksaan otomatis); tidak cocok = grafik ditolak dan tabel ditampilkan |

| Batas | Jumlah seri dan titik dibatasi; label dipotong dengan aman |

| Ekspor | Gambar (PNG) dan tabel (CSV) |



29. Backend Implementation

29.1 Standar Lintas Layanan

| Aspek | Standar |

| --- | --- |

| Lapisan | Controller atau router tipis; business logic di service atau action; akses data di repository atau model; validasi di lapisan masuk |

| Validasi | Seluruh input divalidasi di backend (Form Request di Laravel, validasi CodeIgniter, Pydantic di FastAPI) |

| Otorisasi | Policy atau Gate di Laravel pada setiap aksi; dependensi otorisasi di FastAPI untuk klaim layanan |

| Error | Struktur seragam Bagian 17.2; tanpa stack trace ke klien; log terstruktur di server |

| Logging | JSON, correlation_id, tanpa rahasia, tanpa isi dokumen penuh, dengan masking data pribadi (FR-032) |

| Konfigurasi | Variabel lingkungan; .env.example tanpa nilai rahasia; validasi konfigurasi saat start (gagal cepat) |

| Migrasi | Terversi, up dan down diuji, pemilik tunggal per schema |

| Kontrak | OpenAPI spec-first; contract test di CI |

| Idempotensi | Idempotency-Key pada operasi tulis internal |

| Waktu | UTC pada penyimpanan |

| Dependensi | Kebijakan Bagian 12.6 |



29.2 Laravel

Modul domain pada 15.3. Pekerjaan antrean dengan retry dan backoff untuk pengiriman ke AI dan sinkronisasi izin. Tiga proses terpisah: app, queue-worker, scheduler. Penerbitan dan verifikasi token (DEC-031) dan JWT layanan (DEC-030) berada di modul Auth dan Integration. Audit ditulis oleh satu layanan khusus (AuditRecorder) sehingga format seragam.

29.3 CodeIgniter

Kelas SourceConnector dengan implementasi MVP folder (jaringan atau lokal yang dipasang sebagai volume). Kredensial sumber dienkripsi dengan layanan Encryption bawaan; kunci dari lingkungan. Penjadwal memakai perintah CLI yang dipicu cron di kontainer. Konektor hanya membaca sumber. Seluruh endpoint internal memverifikasi JWT layanan dari Laravel.

29.4 FastAPI

Satu basis kode dua peran proses. domain/ tidak bergantung pada kerangka web sehingga dapat diuji dan dipakai evaluation harness. Antarmuka LLMClient, EmbeddingProvider, Reranker, ObjectStore, dan IntentClassifier memiliki implementasi nyata dan Mock untuk pengujian deterministik. Evaluation harness (eval/) dipanggil melalui CLI (python -m ekaa_ai.eval ...), bukan endpoint publik.

29.5 Standar Mutu Kode

Pint, Larastan, PHPStan (level tinggi yang dicatat), ruff, mypy, ESLint, vue-tsc harus bersih di CI. Fungsi publik pada logika inti memiliki uji. Tidak ada kode mati atau placeholder berpura-pura selesai; hal yang belum selesai ditandai eksplisit pada docs/PROGRESS.md dan KNOWN_LIMITATIONS.md.

30. Security Architecture

30.1 Prinsip

Secure by design; zero trust antarlayanan; least privilege; pertahanan berlapis; fail closed; semua isi dokumen tidak tepercaya; kontrol keamanan diverifikasi dengan pengujian, tidak diklaim.

30.2 Model Ancaman

| ID | Ancaman | Kontrol utama | Verifikasi |

| --- | --- | --- | --- |

| T-01 | Kebocoran lintas hak akses lewat retrieval atau sitasi | Filter ACL di retrieval; verifikasi Policy pada setiap sitasi; pencabutan sinkron | Matriks uji kebocoran (AC-M4-02), nol temuan |

| T-02 | Prompt injection tidak langsung dari isi dokumen | Konteks sebagai data; LLM tanpa alat berbahaya; validasi keluaran; sitasi diverifikasi; pemindaian pola | Korpus uji injeksi (AC-M6-03) |

| T-03 | Prompt injection langsung dari pengguna | Pembatasan tugas; filter akses bukan bagian prompt; keluaran terstruktur | Uji adversarial |

| T-04 | Eksfiltrasi data lewat keluaran model (tautan, gambar, markdown) | Sanitasi keluaran; tanpa gambar atau tautan eksternal | Uji render |

| T-05 | Text-to-SQL berbahaya (Phase 2) | Parser, read-only, ekstensi nonaktif, daftar putih tabel | Uji penolakan (NFR-SEC-003) |

| T-06 | Unggah berkas berbahaya | Validasi server, ClamAV fail-closed, parser terisolasi, batas ukuran, kunci acak | Uji berkas berbahaya |

| T-07 | Pencurian atau penyalahgunaan token | Token pendek, refresh berputar dan terdeteksi reuse, cookie HttpOnly, pencabutan | Uji autentikasi |

| T-08 | Eskalasi hak (IDOR, peran) | Policy per aksi; 404 untuk sumber daya tak berhak | Matriks otorisasi otomatis |

| T-09 | Pemalsuan antarlayanan | JWT asimetris aud/iss/jti/exp pendek; jaringan internal | Uji token salah |

| T-10 | Brute force dan penyalahgunaan | Rate limit login dan API, penguncian bertahap | Uji beban keamanan |

| T-11 | Kebocoran rahasia | Rahasia di luar repositori; gitleaks; tidak ada rahasia di log | CI |

| T-12 | Dependensi atau image berbahaya | Lockfile, pemindaian, kebijakan versi | CI |

| T-13 | Manipulasi atau penghapusan audit | Role DB hanya INSERT dan SELECT; hash chain (Phase 2) | Uji hak DB |

| T-14 | DoS biaya (pertanyaan berulang ke LLM) | Rate limit, batas token, pelaporan biaya | Uji beban |

| T-15 | Cache poisoning lintas pengguna (Phase 2) | Kunci cache memuat principals | Uji lintas pengguna |



30.3 Autentikasi dan Otorisasi

Gambar 30.1 Penegakan akses berlapis pada jalur pertanyaan

| Kontrol | Ketentuan |

| --- | --- |

| Kata sandi | Hash adaptif (Argon2id atau bcrypt sesuai bawaan Laravel yang dikunci pada M0); kebijakan panjang minimum; kata sandi sementara wajib diganti |

| Pembatasan percobaan | Rate limit per IP dan per akun; penguncian bertahap tanpa membocorkan keberadaan akun |

| Token | Akses opaque ~15 menit; refresh berputar dengan deteksi reuse; pencabutan saat logout, nonaktif, ganti kata sandi |

| Peran | org_admin, knowledge_manager, data_analyst, standard_user; auditor pada Phase 2 |

| Penegakan | Policy untuk dokumen, dataset, percakapan; pemeriksaan di backend selalu; tidak ada super admin di dalam aplikasi |

| Verifikasi | Setiap endpoint publik tercakup matriks uji otorisasi otomatis (peran x endpoint x kepemilikan) |



30.4 Perlindungan Data

| Aspek | Ketentuan |

| --- | --- |

| Transit | TLS pada reverse proxy (HSTS). TLS internal antarlayanan opsional pada MVP (jaringan Docker internal terisolasi) dan menjadi Phase 2 |

| Tersimpan | Enkripsi volume atau disk pada host; rahasia aplikasi (kredensial konektor, kunci API LLM) dienkripsi aplikasi di basis data; tidak ada klaim enkripsi di tingkat aplikasi untuk seluruh isi dokumen |

| Rahasia | Variabel lingkungan atau Docker secrets; tidak di repositori; rotasi terdokumentasi |

| Log | Tanpa kata sandi, token, atau isi dokumen penuh; masking identifier pribadi (NIK, email, nomor telepon) yang dapat dideteksi |

| Berkas | Bucket privat; unduhan lewat URL bertanda tangan berumur pendek setelah pemeriksaan Policy |



30.5 Pengerasan Aplikasi

Header keamanan pada proxy atau Laravel: HSTS, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, frame-ancestors none.

Content-Security-Policy ketat pada SPA (tanpa unsafe-inline untuk skrip); tanpa sumber eksternal.

Cookie refresh HttpOnly; Secure; SameSite=Strict; token akses tidak pernah di cookie atau storage.

Tanpa CORS lintas asal (satu asal lewat proxy); permintaan mengubah status memakai token akses non-cookie sehingga CSRF tidak berlaku pada API; endpoint refresh dilindungi SameSite=Strict dan pemeriksaan Origin.

Pesan galat generik ke klien; detail hanya di log.

Kontainer: pengguna non-root, sistem berkas root hanya-baca bila memungkinkan, kapabilitas dikurangi, tanpa port internal terbuka ke host.

30.6 Keamanan LLM dan Provider

Mulai dari kebijakan data (OA-03). Panggilan keluar ke penyedia LLM hanya dari ai-api melalui egress yang dibatasi; kunci API disimpan terenkripsi; pencatatan prompt penuh dimatikan secara bawaan (hanya hash dan metadata, kecuali dinyalakan untuk debugging dengan masa retensi pendek); data dikirim seminimal mungkin (potongan konteks, tanpa identitas pengguna). Penyedia yang dikonfigurasi dicatat pada audit.

30.7 Pengujian Keamanan

| Uji | Cakupan | Kapan |

| --- | --- | --- |

| Matriks otorisasi | Semua endpoint x peran x kepemilikan | Setiap CI |

| Uji kebocoran lintas akses | Pertanyaan oleh pengguna berbeda terhadap korpus berizin berbeda | Mulai M4; setiap rilis |

| Uji injeksi | Dokumen berisi instruksi berbahaya, pertanyaan adversarial | M6 dan sesudahnya |

| Pemindaian | gitleaks, Trivy, audit dependensi | Setiap CI |

| Uji berkas berbahaya | EICAR, berkas rusak, bom dekompresi | M2 dan M3 |

| Penetration test eksternal | Sebelum rilis produksi | M8 (OA-08; bukan syarat demo) |



31. Data Governance

31.1 Klasifikasi dan Peran Data

| Kelompok data | Contoh | Pemilik | Perlakuan |

| --- | --- | --- | --- |

| Identitas dan akses | Pengguna, peran, izin | Org Admin | Audit perubahan; retensi selama akun |

| Konten organisasi | Dokumen, dataset | Knowledge Manager, Data Analyst | Izin per sumber; retensi sesuai kebijakan organisasi |

| Percakapan | Pertanyaan, jawaban, sitasi | Pengguna pemilik | Terbaca hanya pemilik; retensi dikonfigurasi |

| Telemetri | Trace retrieval, log | Operator | Retensi pendek; tanpa isi penuh |

| Audit | Log audit | Org Admin | Append-only; retensi minimal sesuai konfigurasi |

| Data evaluasi | Golden dataset | Peneliti | Hanya korpus yang boleh dipublikasikan atau disintesis (AS-07); versi dan pembekuan tercatat |



31.2 Kebijakan

Minimisasi: hanya data yang diperlukan dikirim ke penyedia LLM; embedding dan reranker berjalan lokal secara bawaan.

Retensi dan penghapusan: pekerjaan retensi terjadwal (FR-037); penghapusan dokumen menjangkau berkas, chunk, vektor, dan cache dengan catatan audit.

Audit: setiap pertanyaan, sumber yang diakses, perubahan izin dan konfigurasi tercatat; log audit hanya dapat dibaca org_admin.

Kualitas data: impor dataset melaporkan galat baris; kamus data diwajibkan untuk kolom ukuran.

Hak subjek data dan kepatuhan: kajian hukum UU PDP belum dilakukan (OA-06). Dokumen ini tidak mengklaim kepatuhan hukum; kontrol teknis (akses, audit, retensi, penghapusan) disiapkan sebagai fondasi.

Data uji: tidak memakai data pribadi nyata tanpa dasar; korpus sintetis atau publik (AS-07).

Penyedia LLM: daftar penyedia yang diizinkan dan kebijakan data ditetapkan Owner (OA-03) dan dicatat pada pengaturan sistem; BL-01 (sensitivitas dokumen) dirancang setelah kebijakan ada.

32. Deployment dan Infrastructure

32.1 Topologi

Gambar 32.1 Zona jaringan dan aliran deployment Docker Compose

32.2 Layanan Compose

| Layanan | Image atau basis | Jaringan | Volume | Healthcheck |

| --- | --- | --- | --- | --- |

| proxy | Caddy | publik dan aplikasi | konfigurasi, data sertifikat | HTTP internal |

| web | Berkas statis SPA (disajikan proxy atau Nginx kecil) | aplikasi | - | HTTP |

| app | Laravel (PHP-FPM) | aplikasi dan data | - | /health |

| queue-worker | Laravel (image sama) | aplikasi dan data | - | proses hidup |

| scheduler | Laravel (image sama) | aplikasi dan data | - | proses hidup |

| connector | CodeIgniter | aplikasi dan data | volume sumber (hanya baca) | /health |

| ai-api | FastAPI | aplikasi dan data | model cache | /health |

| ai-worker | FastAPI (image sama) | aplikasi dan data | model cache, volume DuckDB | proses hidup |

| postgres | PostgreSQL + pgvector | data | data | pg_isready |

| valkey | Valkey | data | data (persistensi sesuai kebutuhan) | PING |

| objectstore | SeaweedFS (S3 API) | data | data | HTTP |

| clamav | ClamAV (clamd) | data | basis tanda tangan | PING |



Hanya proxy yang memublikasikan port ke host (443, dan 80 untuk pengalihan). Jaringan data tidak memiliki rute keluar. Egress ke penyedia LLM hanya dari ai-api (dibatasi pada host yang diizinkan bila lingkungan mendukung).

32.3 Profil Lingkungan

| Profil | Tujuan | Catatan |

| --- | --- | --- |

| dev | Pengembangan | Hot reload, port debug terbatas ke localhost, data sintetis |

| demo | Server lokal atau VPS (AS-06) | Compose produksi, TLS (ACME atau CA internal), rahasia dari lingkungan |

| ci | Pipeline | Layanan minimum untuk uji integrasi |



Pengembangan dijalankan Docker-first (WSL2 atau Linux); Laragon tidak dipakai sebagai runtime proyek ini (DEC-049, RISK-23).

32.4 Konfigurasi dan Rahasia

.env.example mendaftar seluruh variabel tanpa nilai rahasia. Rahasia (kunci DB, kunci JWT, kunci enkripsi, kunci LLM) berasal dari variabel lingkungan atau Docker secrets pada host. Kunci JWT per layanan dibangkitkan oleh skrip pemasangan dan dirotasi lewat prosedur runbook. Konfigurasi divalidasi saat start.

32.5 Kapasitas dan Sumber Daya (Hipotesis)

Perangkat keras demo belum diketahui (OA-04). Dokumentasi memuat kebutuhan minimum yang diukur pada M3 dan M8 (CPU, RAM, disk, GPU opsional) untuk dua profil: LLM eksternal dengan embedding lokal, dan seluruhnya lokal. Tidak ada angka kebutuhan yang dinyatakan sebelum pengukuran.

32.6 Backup, Restore, dan Pemulihan (FR-036, DEC-044)

| Komponen | Metode |

| --- | --- |

| PostgreSQL | pg_dump terjadwal (dan arsip WAL bila RPO menuntut) |

| Object store | Salinan terenkripsi ke lokasi di luar host |

| Volume DuckDB | Salinan terjadwal |

| Enkripsi dan penyimpanan | restic ke repositori terenkripsi di luar host |

| Uji | Uji restore terjadwal pada lingkungan bersih dengan laporan; RPO dan RTO ditetapkan Owner dan klien (NFR-AVL-001) |



32.7 Pemasangan, Pembaruan, dan Rollback

Pemasangan: make install (atau skrip deploy/scripts/install) membuat rahasia, kunci, schema, role database, seed peran dan metrik, dan admin awal dengan kata sandi sementara wajib diganti.

Pembaruan: tarik image bertag spesifik, jalankan migrasi per pemilik schema, uji kesehatan, lalu alihkan; migrasi bersifat expand-then-contract.

Rollback: kembali ke tag image sebelumnya dan, bila perlu, restore cadangan sebelum migrasi; prosedur diuji pada M8.

Observabilitas MVP: log JSON per kontainer dengan correlation_id, endpoint /health, trace retrieval dan model di database, metrik latensi pada laporan beban. Prometheus dan Grafana Phase 2.

BAGIAN VI — DEVELOPMENT MANAGEMENT

33. Implementation Roadmap

33.1 Prinsip

Kontrak dahulu, implementasi kemudian. OpenAPI dan skema database disepakati pada M0.

Tidak ada modul menunggu modul lain tanpa antarmuka atau stub yang jelas (33.4).

Vertikal tipis lebih dulu. Setelah M4 ada alur menyeluruh unggah, indeks, tanya, sitasi, lalu diperdalam.

Evaluasi sejak awal. Golden dataset mulai disusun di M0; baseline diukur di M4.

Fitur dianggap selesai hanya bila berjalan, diuji, dan terdokumentasi (Definition of Done 34.3).

33.2 Milestone dan Estimasi (Hipotesis)

Estimasi mengasumsikan satu pengembang dibantu AI coding agent (AS-01), dengan ketidakpastian sekitar plus minus 30%. Angka dikalibrasi ulang pada checkpoint G1 dan bukan komitmen jadwal sebelum itu (RISK-01, OA-05).

| Milestone | Nama | Minggu | Ketergantungan | Hasil utama |

| --- | --- | --- | --- | --- |

| M0 | Fondasi dan Kontrak | 3 | - | Repositori, Compose, versi terkunci, OpenAPI, schema dan role, JWT, uji pgvector dan leksikal |

| M1 | Identitas, Peran, Audit | 3 | M0 | Autentikasi, pengguna, peran, unit, audit, pengaturan, panel admin dasar |

| M2 | Dokumen dan Izin | 3 | M1, AI-stub | Unggah, versi, status, izin, sinkron ACL, unduhan bertanda tangan |

| M3 | Ingestion dan Indeks | 4 | M2 | Layanan AI nyata, parsing, chunking, embedding, indeks, model embedding terkunci |

| M4 | Retrieval, Jawaban, Chat | 4 | M3, golden dataset v1 | RAG berfilter akses, reranker, sitasi, abstain, chat, laporan baseline |

| M5 | Analitik Deterministik | 4 | M1, M3, LLM dari M4 | Impor dataset, kamus data, metrik, grafik |

| M6 | Router, Guardrails, Feedback | 3 | M4, M5 | Router tiga kelas, guardrails, umpan balik, uji injeksi |

| M7 | Connector & Sync | 2 | M2 | Konektor folder CodeIgniter, kontrak teruji, tinjauan G3 |

| M8 | Pengerasan dan Rilis MVP | 3 | M6, M7 | Keamanan, beban, restore, dokumentasi, demo |

|  | Total MVP | 29 |  | Sekitar 6,5 bulan |



Urutan kerja M0 sampai M8. M5 dan M7 dapat bergeser selama ketergantungannya terpenuhi.

33.3 Checkpoint Evidence dan Gerbang (DEC-050)

Mandat 13 November membebaskan Owner dari persetujuan berulang atas keputusan teknis. Gerbang karenanya dibedakan menjadi checkpoint bukti (dijalankan Technical Architect dan agen implementasi secara otomatis) dan gerbang Owner.

| Gerbang | Waktu | Jenis | Isi dan aturan |

| --- | --- | --- | --- |

| G0 | Sebelum M0 | Tertutup | Persetujuan 13 November 2026 (dokumen ini) |

| G1 | Akhir M1 | Checkpoint bukti | Kalibrasi estimasi dengan kecepatan aktual; hasil dicatat pada docs/PROGRESS.md. Bila realisasi M1 melebihi 4 minggu, pemangkasan cakupan diajukan kepada Owner sebagai perhatian, bukan dilakukan sepihak |

| G2 | Akhir M4 | Checkpoint bukti | Baseline diukur; target awal (Recall@10, faithfulness, latensi) ditetapkan ulang dari data; pilihan embedding, reranker, dan LLM dikunci di Decision Register. Anggaran LLM dan kebijakan data (OA-03) harus sudah ada |

| G3 | Akhir M7 | Gerbang Owner bersyarat | Tinjauan beban pemeliharaan dua framework (14.3). Bawaan: lanjut dengan CodeIgniter. Fallback C hanya atas persetujuan Owner |

| G4 | Akhir M8 | Gerbang Owner | Persetujuan rilis MVP berdasarkan kriteria rilis (37.3) |



Risiko berstatus Tinggi atau Kritis yang terpicu menghentikan perpindahan milestone sampai ditangani dan dicatat.

33.4 Stub dan Antarmuka Sementara

| Ketergantungan | Stub sampai modul nyata siap | Dipakai oleh |

| --- | --- | --- |

| Layanan AI (sebelum M3) | AI-stub: FastAPI minimal yang memenuhi kontrak ingestion dan memanggil callback | M2 |

| LLM | Mock LLM provider deterministik | M4 sampai M6 dan CI |

| Konektor (sebelum M7) | Unggah manual lewat API yang sama dengan /internal/v1/connector/items | M2 |

| Laravel (saat M7) | Laravel nyata; Laravel-stub untuk uji kontrak CodeIgniter paralel | M7 |

| Embedding (sebelum dikunci) | Embedding palsu deterministik untuk uji alur | M3 |



Stub harus ditandai eksplisit pada docs/PROGRESS.md dan tidak boleh dilaporkan sebagai fitur selesai.

34. Development Phases

34.1 Rincian Fase

| Fase | Tujuan | Modul dan FR | Deliverables kunci |

| --- | --- | --- | --- |

| M0 | Lingkungan dapat direproduksi dan kontrak disepakati | Repositori monorepo, Compose seluruh layanan, CI, kerangka tiga layanan dan SPA, OpenAPI, migrasi dasar | docker compose up hijau; lockfile dan docs/VERSIONS.md terverifikasi; konformitas S3 object store (presigned URL, multipart) pada SeaweedFS; uji pgvector ANN berfilter dan keputusan mitigasi; perbandingan FTS dan kandidat BM25; kunci JWT prototipe; golden dataset v0 (kerangka dan pedoman anotasi); pemilihan pustaka JWT dan algoritma |

| M1 | Fondasi keamanan | FR-001 s.d. 006, FR-034 | Login, refresh berputar, logout, pembatasan percobaan; CRUD pengguna, peran, unit; Policy; audit append-only; pengaturan; panel admin pengguna; kata sandi sementara |

| M2 | Dokumen dan izin | FR-010 s.d. 012, FR-037 (kerangka) | Unggah tervalidasi dan dipindai; versi, status, izin; URL unduhan bertanda tangan; sinkron ACL dengan pencabutan sinkron; rekonsiliasi harian (kerangka); layar dokumen |

| M3 | Ingestion dan indeks | FR-013, FR-014, FR-038 | ai-api dan ai-worker; parser; chunking; embedding; FTS; ai.jobs; callback; penggantian versi atomik; reindex; model embedding dikunci |

| M4 | Tanya jawab dokumen | FR-015 s.d. 018, FR-035 | Retrieval hibrida berfilter, reranker, generasi bersitasi, abstain, chat dan riwayat, penampil sumber; evaluation harness; laporan baseline; uji kebocoran awal; LLM terpilih |

| M5 | Analitik | FR-020 s.d. 025 | Importer DuckDB; kamus data; katalog metrik; planner; validator grafik; layar dataset dan grafik |

| M6 | Satu pintu tanya | FR-019, FR-030, FR-032, FR-033 | Router tiga kelas; guardrails; umpan balik dan layar tinjauan; pemindaian rahasia CI; rangkaian uji injeksi v1 |

| M7 | Konektor | FR-031 | Konektor folder; kredensial terenkripsi; deteksi hash; penyerahan idempoten; riwayat sinkron; uji kontrak; tinjauan G3 |

| M8 | Pengerasan dan rilis | FR-036, FR-037 (penuh) | Laporan keamanan, beban, restore; pemasangan ulang; README, OpenAPI, runbook; studi kasus dan demo; daftar keterbatasan; evaluasi pengguna bila peserta ada |



34.2 Pola Pengerjaan per Fase

Setiap fase mengikuti urutan: (1) inspeksi repositori dan pembacaan bagian dokumen ini yang relevan; (2) rencana tertulis singkat pada docs/PROGRESS.md; (3) implementasi bertahap per modul kecil; (4) pengujian otomatis; (5) pengumpulan bukti terhadap AC fase; (6) pembaruan dokumentasi; (7) laporan fase dan checkpoint. Fase berikutnya tidak dimulai sebelum AC fase terpenuhi atau pengecualian tercatat.

34.3 Definition of Done Global

Sebuah item dinyatakan selesai hanya jika seluruhnya terpenuhi:

Kode ada di repositori dengan riwayat commit jelas dan lolos tinjauan.

Berjalan pada Docker Compose, bukan hanya di mesin pengembang.

Terintegrasi dengan backend nyata (bukan data tiruan di UI), kecuali stub yang ditandai pada 33.4.

Pengujian otomatis relevan tersedia dan lulus di CI.

Validasi masukan, otorisasi, penanganan error, dan audit diterapkan sesuai dokumen ini.

Migrasi database punya up dan down yang diuji.

OpenAPI dan dokumentasi diperbarui; Decision Register (Bagian 40) diperbarui bila ada keputusan baru.

Tidak ada rahasia di repositori; pemindaian rahasia dan dependensi lulus.

Kriteria penerimaan milestone (Bagian 37) terpenuhi dengan bukti.

35. Testing Strategy

35.1 Jenis Uji

| Jenis | Cakupan | Alat | Syarat lulus |

| --- | --- | --- | --- |

| Unit | Metrik, Policy, parser, validator, router | Pest, PHPUnit, pytest, Vitest | 100% lulus |

| Integrasi dan API | Laravel-AI, Laravel-Connector, callback, database | Pest, PHPUnit, pytest | 100% lulus |

| Kontrak | Respons terhadap OpenAPI di kedua sisi | Validator OpenAPI, stub | Tidak ada penyimpangan |

| UI dan E2E | Unggah, indeks, tanya, sitasi; dataset, metrik, grafik; login dan peran | Playwright | Alur kritis lulus |

| Otorisasi | Matriks peran x sumber daya x aksi | Otomatis | 0 pelanggaran |

| Kebocoran | Skenario akses pada golden dataset | Evaluation harness | 0 kebocoran |

| Keamanan lain | Injeksi, berkas berbahaya, JWT, SQL (Phase 2), dependensi, rahasia | Rangkaian uji dan pemindai | Temuan kritis dan tinggi tertutup atau dikecualikan Owner |

| Beban | Pertanyaan dan ingestion bersamaan | k6 | Hasil dilaporkan dengan spesifikasi mesin |

| Pemulihan | Restore PostgreSQL, object store, DuckDB | Skrip | Restore berhasil, waktu dicatat |

| Pemasangan ulang | Mesin bersih mengikuti README | Manual terdokumentasi | Tanpa langkah tersembunyi |

| Aksesibilitas | Alur kritis | axe dan manual | Tidak ada pelanggaran kritis |



Setiap bug yang ditemukan memperoleh uji regresi sebelum dinyatakan selesai.

35.2 Golden Dataset

Korpus: dokumen kampus yang boleh dipublikasikan atau disintesis (AS-07). Ukuran berikut adalah usulan minimum yang ditinjau pada M0 berdasarkan kebutuhan statistik.

| Segmen | Minimum | Tujuan |

| --- | --- | --- |

| Pertanyaan dokumen dengan jawaban dan sumber acuan | 100 | Retrieval, faithfulness, sitasi |

| Pertanyaan tanpa bukti | 30 | Abstain |

| Pertanyaan angka dengan jawaban acuan | 50 | Akurasi numerik, pemilihan metrik |

| Pertanyaan ambigu atau di luar cakupan | 30 | Router, klarifikasi |

| Skenario akses (peran x dokumen, pencabutan, versi) | 40 | Kebocoran lintas hak akses |

| Dokumen berisi instruksi injeksi | 20 | Prompt injection |



Aturan: pedoman anotasi tertulis; setiap butir punya pengenal dan versi; sebagian butir dianotasi lebih dari satu penilai bila memungkinkan dengan kesepakatan antar-penilai dilaporkan; pembagian dev dan test dengan pembekuan versi sebelum eksperimen; data test tidak boleh dipakai untuk menyetel prompt atau parameter; bias LLM-judge dicatat dan sebagian sampel diperiksa manual.

35.3 Rancangan Evaluasi Retrieval dan Jawaban

| Kondisi | Konfigurasi | Tujuan |

| --- | --- | --- |

| C0 | Dense saja | Pembanding dasar |

| C1 | Leksikal saja (FTS, lalu BM25 sejati bila terpilih) | Nilai leksikal |

| C2 | Hibrida RRF | Nilai fusi |

| C3 | Hibrida + reranker | Nilai reranker (RQ1) |



Semua kondisi memakai korpus, pertanyaan, parameter chunking, dan seed yang sama. Metrik: Recall@{5,10,20}, MRR, nDCG@10; faithfulness, answer relevancy, akurasi sitasi; akurasi abstain; latensi dan biaya per pertanyaan. Interval ketidakpastian melalui bootstrap pada butir pertanyaan; perbandingan berpasangan pada pertanyaan yang sama. Hasil dilaporkan apa adanya, termasuk hasil negatif. Tidak ada angka hasil pada dokumen ini karena belum ada eksperimen.

35.4 Reproduksibilitas (NFR-REP-001)

Setiap proses evaluasi mencatat versi golden dataset, hash commit, model dan versinya (termasuk penyedia dan tanggal), versi prompt, parameter, seed, dan keluaran mentah. Pembanding sebaiknya mencakup model lokal bila model API dapat berubah (RISK-16).

36. Quality Assurance

| Aspek | Ketentuan |

| --- | --- |

| Tinjauan kode | Setiap perubahan melalui pull request dengan daftar periksa: sesuai dokumen ini, ada uji, tidak ada rahasia, migrasi aman, dokumentasi diperbarui. Karena pengembang tunggal, agen implementasi menjalankan tinjauan mandiri tertulis pada PR dan Owner meninjau pada checkpoint |

| Pipeline CI | Lint dan analisis statis (Pint, PHPStan atau Larastan, ruff, mypy, ESLint, vue-tsc); uji unit dan integrasi; uji kontrak; migrasi naik-turun; gitleaks; Trivy; audit dependensi; pembangunan image. Seluruh langkah juga dapat dijalankan lokal lewat make ci |

| Mutu kode | Tidak ada duplikasi business logic antar-framework; tidak ada kode mati; fungsi inti beruji; dependensi baru dicatat alasannya |

| Pelaporan status | Status pekerjaan hanya "selesai" dengan bukti (log uji, laporan). Pekerjaan berpura-pura atau stub dilaporkan sebagai stub |

| Manajemen cacat | Cacat dicatat dengan tingkat keparahan; kritis dan tinggi menghentikan perpindahan milestone; keterbatasan yang diterima masuk KNOWN_LIMITATIONS.md |

| Perubahan keputusan | Hanya melalui pencatatan di Decision Register dengan alasan; tidak ada penggantian teknologi final secara diam-diam |

| Audit hasil implementasi | Pada setiap akhir milestone dilakukan audit terhadap dokumen ini: setiap FR dan AC fase dipetakan ke bukti; hasil audit masuk laporan fase |



37. Acceptance Criteria

37.1 Prinsip

Kriteria dapat diuji dan dibuktikan dengan artefak, bukan pernyataan.

Angka target adalah hipotesis awal; ditetapkan ulang pada G2 setelah baseline, dengan alasan tercatat.

Hasil dilaporkan apa adanya; tidak memenuhi target bukan alasan mengubah data uji atau metrik secara diam-diam.

Kriteria keamanan bersifat biner (lulus atau gagal), tanpa toleransi untuk kebocoran lintas hak akses.

Bukti yang diwajibkan: laporan uji otomatis (commit, tanggal, daftar uji, hasil); laporan evaluasi (versi dataset, model, parameter, seed, hasil per metrik, interval); laporan keamanan (skenario, hasil, temuan, status); laporan beban (skenario, spesifikasi mesin, p50 dan p95, throughput, galat).

37.2 Kriteria per Milestone

M0 — Fondasi dan Kontrak

| ID | Kriteria | Bukti |

| --- | --- | --- |

| AC-M0-01 | docker compose up menjalankan seluruh layanan pada mesin bersih dengan semua health check hijau | Log, tangkapan hasil |

| AC-M0-02 | Tabel versi (PHP 8.4 dan paket, Python, FastAPI, PostgreSQL, pgvector, Valkey, DuckDB, SeaweedFS, Caddy, ClamAV, Vue, ECharts) terisi dari lockfile dan diverifikasi | docs/VERSIONS.md |

| AC-M0-03 | Tiga schema dan tiga role ada; setiap role terbukti tidak dapat mengakses schema lain | Uji hak akses |

| AC-M0-04 | OpenAPI Laravel-AI dan Laravel-Connector ada; stub lulus uji kontrak | Laporan kontrak |

| AC-M0-05 | JWT antarlayanan: valid diterima; kedaluwarsa, aud salah, dan jti ulang ditolak | Laporan uji |

| AC-M0-06 | Uji pgvector: perilaku ANN dengan filter ACL selektif terukur dan strategi mitigasi dipilih | Laporan |

| AC-M0-07 | Perbandingan awal FTS dan kandidat BM25 terdokumentasi (termasuk lisensi) dan keputusan dicatat | Laporan, Decision Register |

| AC-M0-08 | Migrasi up dan down lulus pada ketiga schema | Log CI |

| AC-M0-09 | Object store terpilih lulus uji konformitas S3 (unggah, unduh, presigned URL, multipart, hapus) melalui lapisan abstraksi | Laporan uji |



M1 — Identitas, Peran, Audit

| ID | Kriteria | Bukti |

| --- | --- | --- |

| AC-M1-01 | Login valid berhasil; kredensial salah ditolak dengan pesan sama untuk pengguna tidak ada dan kata sandi salah | Uji API |

| AC-M1-02 | Percobaan login berulang dibatasi sesuai konfigurasi | Uji API |

| AC-M1-03 | Token kedaluwarsa dan dicabut ditolak; reuse refresh token lama mencabut rantai token | Uji API |

| AC-M1-04 | Hanya org_admin mengelola pengguna, peran, unit; peran lain mendapat 403 | Matriks otorisasi |

| AC-M1-05 | Pengguna nonaktif tidak dapat login dan token lamanya ditolak | Uji API |

| AC-M1-06 | Login, perubahan peran, dan perubahan pengaturan menghasilkan entri audit | Uji |

| AC-M1-07 | Role aplikasi tidak dapat UPDATE atau DELETE pada audit_logs | Uji hak database |

| AC-M1-08 | Admin terakhir tidak dapat dinonaktifkan | Uji |

| AC-M1-09 | Akun dengan must_change_password hanya dapat mengganti kata sandi sampai selesai; kata sandi sementara tidak dapat dipakai ulang | Uji |



M2 — Dokumen dan Izin

| ID | Kriteria | Bukti |

| --- | --- | --- |

| AC-M2-01 | Tipe tidak diizinkan, MIME dipalsukan, atau ukuran berlebih ditolak (415 atau 413); berkas uji EICAR ditolak oleh pemindai; pemindai mati berarti unggah ditolak | Uji berkas |

| AC-M2-02 | Unggah menghasilkan dokumen, versi, audit, dan pekerjaan ingestion; unggah ulang dengan kunci idempoten sama tidak menggandakan | Uji |

| AC-M2-03 | Pengguna tanpa izin tidak melihat dokumen di daftar maupun detail (404) | Matriks peran x dokumen |

| AC-M2-04 | Perubahan izin dan status dicabut menyinkron ke AI sebelum sukses; bila gagal, aksi gagal dan dokumen tetap ditandai dicabut di Laravel | Uji stub gagal |

| AC-M2-05 | Layanan AI mati: endpoint non-AI berfungsi; endpoint AI mengembalikan 503 terstruktur | Uji kegagalan |

| AC-M2-06 | URL unduhan bertanda tangan kedaluwarsa sesuai konfigurasi dan tidak dapat dipakai pengguna tanpa izin | Uji |

| AC-M2-07 | Dokumen tanpa aturan izin tidak dapat diaktifkan; izin pada unit induk berlaku bagi anggota unit turunan | Uji |



M3 — Ingestion dan Indeks

| ID | Kriteria | Bukti |

| --- | --- | --- |

| AC-M3-01 | PDF berteks, DOCX, XLSX, CSV sampel menghasilkan chunk dengan metadata dokumen, versi, halaman, bagian | Uji sampel |

| AC-M3-02 | Teks chunk identik dengan teks sumber pada sampel uji | Uji perbandingan |

| AC-M3-03 | Tiap chunk membawa acl_principals, status, is_current sesuai izin Laravel | Uji |

| AC-M3-04 | Penggantian versi menjadikan chunk lama tidak muncul pada retrieval | Uji |

| AC-M3-05 | Kegagalan worker menghasilkan failed dengan pesan ringkas tanpa isi dokumen dan dapat diulang; tidak ada chunk separuh | Uji kegagalan |

| AC-M3-06 | Ingestion tidak memblokir UI; status proses terlihat | Uji E2E |

| AC-M3-07 | Model embedding dan dimensi vektor dikunci dengan dasar evaluasi pada golden dataset (protokol 21.5) | Decision Register, laporan |

| AC-M3-08 | Reindex mengganti indeks secara atomik tanpa kehilangan dokumen | Uji |

| AC-M3-09 | Pustaka parsing PDF dipilih dengan uji perbandingan dan lisensi dicatat | Laporan |



M4 — Retrieval, Jawaban Bersitasi, Chat

| ID | Kriteria | Bukti |

| --- | --- | --- |

| AC-M4-01 | Laporan baseline memuat Recall@k, MRR, nDCG, faithfulness, akurasi sitasi, dan ablasi C0 sampai C3 dengan konfigurasi dan seed | Laporan |

| AC-M4-02 | Kebocoran lintas hak akses = 0 pada seluruh skenario (pencabutan izin, dokumen dicabut, versi lama) | Laporan keamanan |

| AC-M4-03 | Setiap klaim penting punya sitasi yang dapat dibuka ke dokumen, bagian, dan halaman | Uji sampel, metrik akurasi sitasi |

| AC-M4-04 | Pertanyaan tanpa bukti menghasilkan no_evidence; tingkat abstain benar diukur | Laporan |

| AC-M4-05 | Sitasi yang gagal verifikasi Policy di Laravel dibuang dan dicatat | Uji |

| AC-M4-06 | Riwayat percakapan hanya dapat diakses pemiliknya | Uji otorisasi |

| AC-M4-07 | Target awal (ditetapkan ulang pada G2): Recall@10 > 0,9; faithfulness > 0,9. Bila tidak tercapai, dicatat dan dibahas | Laporan |

| AC-M4-08 | Pemilihan LLM dicatat dengan dasar evaluasi (kualitas, biaya, latensi, kebijakan data) | Decision Register |

| AC-M4-09 | Evaluation harness menghasilkan hasil yang sama pada dua kali jalan dengan konfigurasi dan seed sama (dalam batas determinisme penyedia, dicatat) | Laporan |



M5 — Analitik Deterministik

| ID | Kriteria | Bukti |

| --- | --- | --- |

| AC-M5-01 | Seluruh kasus uji metrik lulus 100% termasuk kasus tepi | Laporan unit |

| AC-M5-02 | CAGR memakai jumlah periode n; nilai awal nol atau negatif, periode hilang, dan satuan berbeda menghasilkan peringatan atau penolakan, bukan angka salah | Kasus uji tepi |

| AC-M5-03 | Tidak ada jalur kode yang membuat LLM menghasilkan angka akhir | Tinjauan kode dan uji |

| AC-M5-04 | Nilai grafik identik dengan hasil hitung; spesifikasi tidak valid ditolak | Uji validator |

| AC-M5-05 | Setiap hasil menampilkan rumus, parameter, dan sumber dataset | Uji UI dan API |

| AC-M5-06 | Pengguna tanpa izin dataset tidak dapat menanyakan atau melihatnya | Matriks otorisasi |

| AC-M5-07 | Impor dengan baris galat melaporkan baris bermasalah dan tidak menyimpan data separuh; format angka Indonesia terbaca benar | Uji |

| AC-M5-08 | Akurasi numerik dari bahasa alami pada subset golden dataset dilaporkan (exact match dengan toleransi) | Laporan |

| AC-M5-09 | Angka pada narasi yang tidak cocok dengan hasil hitung ditolak validator | Uji |



M6 — Router, Guardrails, Umpan Balik

| ID | Kriteria | Bukti |

| --- | --- | --- |

| AC-M6-01 | Precision, recall, F1 per kelas dilaporkan untuk kedua implementasi router; ambang keyakinan ditetapkan dari baseline | Laporan |

| AC-M6-02 | Keyakinan di bawah ambang memicu clarification_required; route_hint tetap melewati pemeriksaan akses | Uji |

| AC-M6-03 | Rangkaian uji prompt injection v1 dijalankan dan tingkat keberhasilan serangan dilaporkan; tidak ada klaim "aman" tanpa laporan | Laporan |

| AC-M6-04 | Data pribadi pada pola yang ditentukan di-mask pada log; tingkat deteksi pada sampel dilaporkan | Laporan |

| AC-M6-05 | Umpan balik tersimpan terkait pesan; Knowledge Manager dapat meninjau | Uji |

| AC-M6-06 | Pemindaian rahasia di CI aktif dan menggagalkan build bila menemukan rahasia | Log CI |



M7 — Connector & Sync

| ID | Kriteria | Bukti |

| --- | --- | --- |

| AC-M7-01 | Berkas baru dan berubah pada folder terdeteksi dan diserahkan; tidak berubah tidak diserahkan ulang | Uji |

| AC-M7-02 | Pengulangan dengan Idempotency-Key sama tidak menggandakan dokumen | Uji |

| AC-M7-03 | CodeIgniter tidak dapat menetapkan atau mengubah izin; izin berasal dari default_acl di Laravel | Uji kontrak |

| AC-M7-04 | Kegagalan sumber atau jaringan tercatat di sync_runs dan tidak merusak data | Uji kegagalan |

| AC-M7-05 | Kredensial konektor terenkripsi dan tidak muncul di log atau respons | Uji |

| AC-M7-06 | Uji kontrak CodeIgniter-Laravel berjalan otomatis di CI | Log CI |

| AC-M7-07 | Catatan tinjauan beban pemeliharaan dua framework dibuat dengan indikator 14.3 untuk G3 | Dokumen |



M8 — Pengerasan dan Rilis MVP

| ID | Kriteria | Bukti |

| --- | --- | --- |

| AC-M8-01 | Seluruh jenis uji 35.1 dijalankan dan lulus sesuai syarat | Laporan |

| AC-M8-02 | Laporan keamanan: otorisasi, kebocoran, injeksi, berkas, JWT, dependensi, rahasia; temuan kritis dan tinggi tertutup atau dikecualikan Owner | Laporan |

| AC-M8-03 | Laporan beban: latensi p50 dan p95, throughput, biaya per pertanyaan, spesifikasi mesin | Laporan |

| AC-M8-04 | Restore dari cadangan berhasil pada lingkungan bersih dan waktunya dicatat; retensi dan penghapusan teruji (FR-037) | Laporan |

| AC-M8-05 | Pemasangan ulang oleh orang lain mengikuti README berhasil tanpa langkah tersembunyi | Catatan uji |

| AC-M8-06 | KNOWN_LIMITATIONS.md ditulis jujur (OCR belum ada, text-to-SQL belum ada, dan seterusnya) | Dokumen |



37.3 Kriteria Rilis MVP

MVP siap hanya bila: (1) seluruh FR berprioritas M berjalan dan lulus AC milestone; (2) kebocoran lintas hak akses = 0; (3) kasus uji metrik lulus 100%; (4) laporan baseline dan evaluasi M8 tersedia dengan konfigurasi yang dapat diulang; (5) laporan keamanan, beban, dan restore tersedia; (6) README, OpenAPI, dan panduan pemasangan memungkinkan pemasangan ulang; (7) daftar keterbatasan ditulis jujur; (8) Owner menyetujui rilis secara eksplisit (G4). Kriteria yang tidak tercapai dicatat sebagai pengecualian dengan persetujuan Owner.

37.4 Target Metrik Evaluasi

| Aspek | Metrik | Target awal | Status |

| --- | --- | --- | --- |

| Retrieval | Recall@10 | > 0,9 | Hipotesis dari konsep awal; ditetapkan ulang pada G2 |

| Retrieval | MRR, nDCG | Dilaporkan | Tanpa target sampai baseline |

| Jawaban | Faithfulness | > 0,9 | Hipotesis; ditetapkan ulang pada G2 |

| Jawaban | Answer relevancy, akurasi sitasi, akurasi abstain | Dilaporkan | Tanpa target sampai baseline |

| Angka | Kebenaran fungsi metrik pada kasus uji | 100% | Tetap |

| Angka | Akurasi numerik dari bahasa alami | Dilaporkan | Tanpa target sampai baseline |

| Router | Precision, recall, F1 per kelas | Dilaporkan | Tanpa target sampai baseline |

| Keamanan akses | Kebocoran lintas hak akses | 0 | Tetap |

| Performa | Latensi p50 dan p95, throughput, biaya per pertanyaan | Dilaporkan | Ditetapkan dari baseline dan spesifikasi mesin demo |

| Pengguna | SUS, waktu menemukan informasi dibanding cara manual | Dilaporkan | Bergantung pada ketersediaan peserta |



38. Documentation Requirements

Dokumentasi dibuat di repositori proyek oleh agen implementasi dan bukan deliverable tambahan dari tahap konsolidasi ini.

| Dokumen | Isi | Pemilik pembaruan |

| --- | --- | --- |

| README.md | Gambaran, prasyarat, pemasangan, perintah make, struktur, pemecahan masalah | Setiap fase |

| AGENTS.md | Aturan kerja agen (ringkasan Bagian 44) | M0, dijaga konsisten |

| docs/spec/ | Salinan dokumen Word dan hasil ekstraksi teks (tidak disunting) | M0 |

| docs/PROGRESS.md | Ledger kemajuan: fase, status per FR dan AC dengan tautan bukti, stub aktif, keputusan baru, hambatan | Setiap sesi |

| docs/VERSIONS.md | Versi terkunci dan terverifikasi per komponen | Setiap perubahan dependensi |

| docs/adr/ | Catatan keputusan baru dan penyimpangan dari dokumen ini beserta alasannya | Saat terjadi |

| docs/runbook/ | Pemasangan, rotasi kunci, backup, restore, pembaruan, rollback, penanganan insiden | M8 (kerangka sejak M0) |

| docs/KNOWN_LIMITATIONS.md | Keterbatasan yang diketahui, termasuk pengecualian AC | Berjalan |

| contracts/openapi/ | Kontrak API yang menjadi sumber | Setiap perubahan API |

| eval/protocols/, eval/reports/ | Pedoman anotasi, protokol dan laporan eksperimen | M0 dan seterusnya |



39. Risk Management

39.1 Proses

Risiko ditinjau pada setiap checkpoint dan akhir milestone. Skor adalah pendapat analis, bukan data historis. Risiko Tinggi atau Kritis yang terpicu menghentikan perpindahan milestone sampai ada penanganan tercatat.

39.2 Daftar Risiko (Terkonsolidasi)

P = probabilitas, D = dampak (R rendah, S sedang, T tinggi, K kritis).

| ID | Risiko | P | D | Mitigasi utama | Status |

| --- | --- | --- | --- | --- | --- |

| RISK-01 | Jadwal optimistis: 29 minggu untuk satu pengembang | T | T | Kalibrasi G1; pangkas cakupan lewat Owner; disiplin DoD | OPEN |

| RISK-02 | Beban pemeliharaan dua framework PHP | S | S | Batas CodeIgniter; uji kontrak; tinjauan G3 dengan indikator terukur | OPEN |

| RISK-03 | Scope creep | S | T | Daftar Phase 2 dan 3; kontrol perubahan | OPEN |

| RISK-04 | Bus factor satu orang | S | T | Dokumentasi, PROGRESS.md, commit kecil | OPEN |

| RISK-05 | Kebocoran lintas hak akses | S | K | Filter ACL, pencabutan sinkron, verifikasi sitasi Laravel, uji matriks, target nol | OPEN |

| RISK-06 | Prompt injection dari dokumen | S | T | Konteks sebagai data, tanpa alat berbahaya, sitasi diverifikasi, rangkaian uji | OPEN |

| RISK-07 | Data sensitif dikirim ke LLM eksternal | S | T | LLM lokal, kebijakan data Owner (OA-03), korpus publik, tinjauan hukum | OPEN |

| RISK-08 | Kualitas LLM dan embedding Indonesia kurang | S | T | Evaluasi kandidat sebelum mengunci; antarmuka tertukar | OPEN |

| RISK-09 | ANN pgvector dengan filter selektif | S | T | Uji M0; iterative scan, indeks parsial; pemicu Qdrant | OPEN |

| RISK-10 | Leksikal FTS bukan BM25 murni | S | S | DEC-029: baseline lalu evaluasi | OPEN |

| RISK-11 | Halusinasi dipercaya pengguna | S | T | Sitasi wajib, abstain, umpan balik | OPEN |

| RISK-12 | Parameter metrik salah dipilih LLM | S | T | Validasi skema dan kamus, tampilkan rumus, klarifikasi, ukur akurasi pemilihan | OPEN |

| RISK-13 | Parsing PDF dan tabel kompleks buruk | T | S | MVP PDF berteks; batasan jujur; OCR Phase 2 | ACCEPTED (MVP) |

| RISK-14 | Klaim novelty berlebihan | S | T | Matriks literatur, rumusan konservatif | OPEN |

| RISK-15 | Golden dataset bias atau bocor ke penyetelan | S | T | Dev dan test, pembekuan, anotasi, kesepakatan antar-penilai | OPEN |

| RISK-16 | Eksperimen tidak dapat diulang | S | T | Catat versi model, prompt, seed; keluaran mentah; model lokal pembanding | OPEN |

| RISK-17 | Referensi atau indeksasi jurnal tidak valid | R | T | Verifikasi DOI, penerbit, indeksasi | OPEN |

| RISK-18 | Rahasia bocor | R | T | gitleaks, rahasia di luar repositori, rotasi | OPEN |

| RISK-19 | Kerentanan rantai pasok | S | S | Lockfile, pemindaian, versi dikunci (12.6) | OPEN |

| RISK-20 | Cadangan tidak dapat dipulihkan | R | T | Uji restore terjadwal | OPEN |

| RISK-21 | Klaim kepatuhan UU PDP prematur | S | T | Pemetaan teknis saja; tinjauan hukum | OPEN |

| RISK-22 | Dukungan versi PHP dan paket | S | S | Kunci versi M0; tanpa migrasi mayor selama MVP | OPEN |

| RISK-23 | Beda lingkungan Windows dan Linux | S | S | Docker-first (DEC-049) | MITIGATING |

| RISK-24 | Perangkat keras LLM lokal belum diketahui | S | S | Mock LLM; LLM komersial untuk demo; ukur dulu | OPEN |

| RISK-25 | Biaya dan latensi LLM | S | S | Rate limit, batas konteks, ukur biaya per pertanyaan | OPEN |

| RISK-26 | Ketidakkonsistenan antar-layanan | S | S | Uji kontrak, rekonsiliasi harian, expand-then-contract | OPEN |

| RISK-27 | LLM-judge bias | S | S | Periksa manual sampel; beberapa ukuran | OPEN |

| RISK-28 | Lisensi korpus menghambat publikasi dataset | S | S | Hanya publik atau sintetis; catat sumber | OPEN |

| RISK-29 | Status "berhasil" palsu karena tampilan | S | T | DoD, AC berbasis bukti, PROGRESS.md dengan tautan bukti | OPEN |

| RISK-30 | Baru. Komponen penyimpanan berkas tidak lagi dipelihara (MinIO CE) | T | T | Diganti SeaweedFS melalui abstraksi S3; uji konformitas M0; pengganti cadangan Garage dan RustFS | CLOSED oleh DEC-034 (konformitas masih diuji) |

| RISK-31 | Baru. Agen implementasi melaporkan selesai tanpa bukti, atau menyimpang dari dokumen | S | T | Ledger dengan tautan bukti, checkpoint per fase, audit milestone, aturan penghentian di master prompt | OPEN |

| RISK-32 | Baru. Kandidat BM25 atau pustaka berlisensi AGPL masuk tanpa tinjauan | S | S | Kebijakan 12.6 dan DEC-039; pencatatan ke Owner | OPEN |

| RISK-33 | Baru. Maturitas SeaweedFS pada fitur S3 yang dibutuhkan | S | S | Abstraksi, uji konformitas M0, cadangan alternatif | OPEN |



BAGIAN VII — FINAL PROJECT AGREEMENT

40. Final Technical Decisions

40.1 Decision Register Final

Seluruh keputusan berikut berstatus FINAL APPROVED BY PROJECT OWNER berdasarkan pernyataan Owner tertanggal 13 November 2026 (Bagian 41), kecuali bila ditandai lain. Perubahan menandai keputusan yang diubah atau dilengkapi dari dokumen 8 November.

| ID | Topik | Keputusan final | Dasar dan perubahan |

| --- | --- | --- | --- |

| DEC-001 | Model deployment | Single-tenant, satu instalasi per organisasi | Konsep inti; isolasi sederhana |

| DEC-002 | Pola framework | Pola B: Laravel inti, CodeIgniter satu bounded context, FastAPI; fallback C hanya atas persetujuan Owner | Disetujui Owner sebelumnya; ditegaskan |

| DEC-003 | Peran Laravel | Core backend dan satu-satunya pemilik identitas dan hak akses | Disetujui sebelumnya |

| DEC-004 | Peran CodeIgniter | Connector & Sync Service; MVP satu konektor folder | Disetujui sebelumnya |

| DEC-005 | Layanan AI | FastAPI terpisah | Ditetapkan |

| DEC-006 | Identitas | Laravel IdP tunggal; Keycloak ditunda Phase 3 | Disetujui sebelumnya |

| DEC-007 | Basis data | Satu PostgreSQL, schema core, connector, ai, role minimum | Ditetapkan |

| DEC-008 | Vektor | pgvector 0.8.x ke atas; Qdrant ditunda | Ditetapkan; verifikasi iterative scan |

| DEC-009 | Analitik | DuckDB read-only; metrik teruji di MVP; text-to-SQL Phase 2 | Ditetapkan |

| DEC-010 | Cache dan antrean | Valkey untuk cache, rate limit, antrean Laravel; RabbitMQ ditunda | Perubahan: Redis diganti Valkey (DEC-035); antrean worker AI memakai DEC-032 |

| DEC-011 | Penyimpanan berkas | Object store S3-compatible via abstraksi | Perubahan: MinIO diganti (DEC-034) |

| DEC-012 | Frontend | Vue 3 SPA, ECharts | Ditetapkan; detail DEC-042 |

| DEC-013 | LLM | Antarmuka tertukar; adapter OpenAI-compatible, native opsional, Mock; pemilihan lewat evaluasi | FINAL (METODE); protokol 21.5 |

| DEC-014 | Embedding dan reranker | Kandidat diuji pada golden dataset; pemenang dikunci setelah eksperimen | FINAL (METODE); protokol 21.5 |

| DEC-015 | Deployment | Docker Compose; Helm ditunda | Ditetapkan |

| DEC-016 | Observabilitas | Log JSON + correlation ID + trace di database; Langfuse tidak dipakai di MVP | Perubahan: lihat DEC-037 |

| DEC-017 | Versi platform | Laravel 13, CodeIgniter 4.7.x, PHP 8.4 | Disetujui sebelumnya |

| DEC-018 | Orkestrasi RAG | Kode sendiri ringan | Ditetapkan |

| DEC-019 | Peran | Org Admin, Knowledge Manager, Data Analyst, Standard User; Auditor Phase 2; tanpa super admin di aplikasi | Ditetapkan |

| DEC-020 | Cakupan MVP | MVP ramping (Bagian 11) | Ditetapkan |

| DEC-021 | Router | Tiga kelas MVP; hibrida majemuk Phase 2 | Ditetapkan; implementasi DEC-040 |

| DEC-022 | Komunikasi antarlayanan | REST JSON, OpenAPI, JWT, correlation ID, idempotency | Ditetapkan |

| DEC-023 | Pemisahan Track C | Keputusan proyek dan klaim riset terpisah | Ditetapkan |

| DEC-024 | Reverse proxy | Caddy | Perubahan: produk dipilih (DEC-036) |

| DEC-025 | Sinkron hak akses | Denormalisasi acl_principals; Laravel sumber kebenaran; pencabutan sinkron; verifikasi sitasi di Laravel | Disetujui Owner setelah 8 Nov |

| DEC-026 | Kunci primer | UUIDv7 dari aplikasi | Disetujui Owner setelah 8 Nov |

| DEC-027 | Tabel organizations | Tidak dibuat | Disetujui Owner setelah 8 Nov |

| DEC-028 | Kontrak ingestion | HTTP 202 + callback | Disetujui Owner setelah 8 Nov |

| DEC-029 | Leksikal | FTS simple baseline; BM25 sejati dievaluasi M0/M1 | Disetujui; kriteria evaluasi diperjelas (16.6) |

| DEC-030 | JWT antarlayanan | Penerbit per layanan, kunci asimetris, aud spesifik, umur di bawah 5 menit | Disetujui Owner setelah 8 Nov |

| DEC-031 | Baru Model token SPA | Token akses opaque ~15 menit di memori; refresh berputar pada cookie HttpOnly SameSite=Strict Path /api/v1/auth; deteksi reuse | Melengkapi DEC-006 |

| DEC-032 | Baru Antrean worker AI | Tabel ai.jobs dengan FOR UPDATE SKIP LOCKED, retry, idempotency key | Tahan restart dan transaksional |

| DEC-033 | Baru Protokol pemilihan model | Embedding, reranker, LLM, BM25: kandidat, metode, kriteria, pembekuan test split (21.5) | FINAL (METODE) |

| DEC-034 | Baru Object store | Antarmuka S3; default SeaweedFS; cadangan Garage dan RustFS; MinIO CE dikecualikan | Archived upstream; uji konformitas M0 |

| DEC-035 | Baru Cache dan antrean | Valkey (BSD-3) menggantikan Redis | Lisensi |

| DEC-036 | Baru Reverse proxy | Caddy | Konfigurasi ringkas, TLS otomatis |

| DEC-037 | Baru Observabilitas MVP | Log JSON, correlation ID, ai.retrieval_traces dan messages.model_info; tanpa Langfuse | Mengurangi layanan |

| DEC-038 | Baru Malware scan | ClamAV daemon terpisah; fail-closed | Syarat unggah |

| DEC-039 | Baru Parser dan lisensi | pdfplumber dan pypdf dievaluasi, python-docx, openpyxl; PyMuPDF dikecualikan; AGPL/GPL tidak masuk kode aplikasi tanpa catatan Owner | Lisensi |

| DEC-040 | Baru Implementasi router | Antarmuka IntentClassifier: LLM few-shot dan TF-IDF + regresi logistik, dipilih dengan F1 pada dev split | FINAL (METODE) |

| DEC-041 | Baru Spesifikasi grafik | JSON ber-whitelist, divalidasi di server dan klien, nilai harus sama dengan hasil hitung | Keamanan dan integritas angka |

| DEC-042 | Baru Stack frontend | Vue 3, TypeScript, Vite, Pinia, Vue Router, Tailwind, ECharts, vue-i18n, DOMPurify, openapi-typescript | Rinci 27.1 |

| DEC-043 | Baru Repositori dan tooling | Monorepo; make sebagai antarmuka perintah; GitHub Actions dan make ci lokal | Bagian 15 |

| DEC-044 | Baru Backup | pg_dump + restic terenkripsi + uji restore | Bagian 32.6 |

| DEC-045 | Baru Kebijakan versi | Lockfile, versi pasti, rilis stabil, tanpa merge otomatis, docs/VERSIONS.md, catat AGPL/GPL | Bagian 12.6 |

| DEC-046 | Baru Evaluation harness | CLI di layanan AI; golden dataset di eval/datasets; pembekuan dev dan test | FR-035 |

| DEC-047 | Baru Perbaikan skema | NULLS NOT DISTINCT, must_change_password, ai.jobs, CHECK enum, ekspansi unit induk, indeks HNSW parsial dievaluasi | Bagian 16 |

| DEC-048 | Baru Kebijakan numerik | DECIMAL untuk ukuran desimal, pembulatan hanya pada tampilan, tanpa konversi satuan implisit | Bagian 24.3 |

| DEC-049 | Baru Lingkungan pengembangan | Docker-first; Laragon bukan runtime | RISK-23 |

| DEC-050 | Baru Tata kelola gerbang | G1 dan G2 checkpoint bukti otomatis; G3 (fallback) dan G4 (rilis) gerbang Owner | Bagian 33.3 |



40.2 Keputusan Ditunda (Terdaftar)

Keycloak, Qdrant, RabbitMQ, Kubernetes dan Helm, Prometheus dan Grafana, Langfuse, text-to-SQL, OCR, konektor cloud, SSO, MFA, hash chain audit, cache semantik. Pemicunya pada 12.5 dan Bagian 11.

41. Project Owner Approval

41.1 Pernyataan Persetujuan (Dikutip dari Master Instruction, 13 November 2026)

Saya menyetujui seluruh saran, rekomendasi, ide, perbaikan, dan keputusan teknis yang Anda berikan untuk pengembangan EKAA, baik yang sudah pernah dibahas maupun yang akan Anda usulkan ke depannya.

41.2 Wewenang yang Diberikan

Technical Architect berwenang memilih teknologi, arsitektur, pembagian framework, database, vector database, cache, queue, storage, infrastruktur, pendekatan AI, desain database dan API, keamanan, prioritas, serta menyelesaikan inkonsistensi dan keputusan berstatus PROPOSED atau PENDING.

41.3 Batas Wewenang

Tujuan utama, visi, kebutuhan bisnis, dan tujuan penelitian tidak diubah sembarangan.

Laravel 13, CodeIgniter 4, dan FastAPI tidak dihapus sepihak; fallback C hanya atas persetujuan Owner.

Hal yang berpengaruh pada biaya besar, lisensi berbayar, kewajiban hukum, privasi data, atau perubahan mendasar pada tujuan dicatat sebagai perhatian Owner (Bagian 0.5).

Persetujuan ini tidak menjadikan hasil eksperimen, pernyataan kepatuhan hukum, atau status implementasi sebagai terbukti; bukti tetap diwajibkan.

41.4 Status Persetujuan

| Item | Status |

| --- | --- |

| Keputusan DEC-001 s.d. DEC-050 | FINAL APPROVED BY PROJECT OWNER (FINAL (METODE) untuk DEC-013, 014, 033, 040) |

| Persetujuan eksplisit sebelumnya | DEC-002, 003, 004, 006, 017; DEC-024 s.d. 030 dan asumsi AS-06, AS-07 (catatan proyek) |

| Gerbang yang tetap milik Owner | G3 bersyarat dan G4 (OA-11) |

| Perhatian Owner terbuka | OA-01 s.d. OA-11 (Bagian 0.5) |



41.5 Penandatanganan

| Peran | Nama | Tanggal | Tanda tangan |

| --- | --- | --- | --- |

| Project Owner |  |  |  |

| Technical Architect | Claude (atas mandat Owner) | 13 November 2026 | - |



Kolom nama dan tanda tangan Project Owner diisi bila diperlukan bukti tertulis tambahan; dasar persetujuan saat ini adalah pernyataan pada 41.1.

42. Final Deliverables

42.1 Deliverable Tahap Konsolidasi Ini

| No | Berkas | Isi |

| --- | --- | --- |

| 1 | EKAA_MASTER_PROJECT_AGREEMENT.docx | Dokumen ini |

| 2 | ANTIGRAVITY_MASTER_PROMPT.md | Satu instruksi implementasi lengkap untuk Google Antigravity |



Tidak ada berkas lain pada tahap ini.

42.2 Deliverable Akhir Proyek (Dihasilkan Agen Implementasi)

Repositori monorepo dengan tiga layanan, SPA, kontrak, skrip, dan pengujian.

Sistem yang berjalan lewat docker compose up dan dapat dipasang ulang dari README.

Laporan: baseline retrieval dan jawaban (M4), keamanan, beban, restore (M8), evaluasi router dan injeksi (M6).

Golden dataset terversi dan protokol evaluasi (bahan Artikel 1).

Dokumentasi pada Bagian 38.

Demo pada korpus AS-07.

43. Target System

Pada akhir MVP, EKAA adalah sistem single-tenant yang:

Memungkinkan Knowledge Manager mengelola dokumen berversi dengan izin tegas, dan Data Analyst mengimpor dataset dengan kamus data.

Menjawab pertanyaan dokumen dengan sitasi yang dapat dibuka, atau menyatakan bukti tidak ditemukan.

Menjawab pertanyaan angka dengan metrik teruji, menampilkan rumus, sumber, tabel, dan grafik yang nilainya sama dengan hasil hitung.

Mengarahkan pertanyaan lewat Hybrid Router tiga kelas dengan klarifikasi bila ragu.

Menegakkan hak akses berlapis dengan kebocoran lintas hak akses nol pada rangkaian uji.

Mencatat audit dan jejak jawaban, dapat dicadangkan dan dipulihkan, dan dapat dipasang ulang.

Terdokumentasi, teruji, dan dievaluasi dengan laporan yang dapat diulang.

Sistem tidak mengklaim akurasi, keamanan, atau kepatuhan hukum di luar yang dibuktikan laporan.

44. Implementation Rules

44.1 Aturan Mengikat bagi Pengembang dan Agen

Dokumen ini adalah sumber keputusan utama. Penyimpangan hanya melalui 44.4.

Inspeksi repositori sebelum mengubah apa pun; pertahankan kode yang relevan; tidak menghapus berkas tanpa analisis tertulis.

Bekerja bertahap per fase (Bagian 34); tidak membangun semua modul sekaligus.

Tidak ada mockup, prototipe visual, atau halaman tanpa backend nyata sebagai hasil akhir. Stub hanya sementara dan ditandai.

Tidak mengklaim selesai tanpa bukti yang dapat diperiksa; tidak mengarang hasil uji, hasil eksperimen, status, atau referensi.

Batas framework dan larangan arsitektural (14.2) tidak boleh dilanggar.

Keamanan bukan tahap akhir: validasi, otorisasi, audit, dan penanganan rahasia dibangun bersama fitur.

Tidak menambah dependensi tanpa alasan tercatat; kebijakan 12.6 berlaku.

Tidak menjalankan tindakan destruktif pada sistem di luar repositori, tidak mengirim data nyata ke layanan luar tanpa izin Owner, dan tidak memasukkan rahasia ke repositori.

Bila ada ambiguitas yang menghambat, buat asumsi yang masuk akal, beri label, catat; bertanya hanya bila keputusan hilang itu benar-benar menghambat atau berkonsekuensi besar.

44.2 Pemisahan Track

Track A (manajemen), B (rekayasa perangkat lunak), C (riset), dan D (agen) saling mendukung tetapi dokumen keputusan proyek tidak dicampur dengan klaim hasil penelitian.

44.3 Penghentian Wajib

Agen berhenti dan melapor (tidak melanjutkan) bila: kebocoran lintas hak akses ditemukan; AC fase tidak dapat dipenuhi dan tidak ada pengecualian; keputusan memerlukan persetujuan Owner (biaya, lisensi, hukum, privasi, fallback C, rilis); konflik dengan dokumen ini tidak dapat diselesaikan dari dokumen.

44.4 Kontrol Perubahan

Perubahan keputusan: (1) diusulkan beserta dampak teknis, biaya, dan risiko; (2) diklasifikasi sebagai teknis (diputuskan Technical Architect atau agen sesuai mandat) atau perhatian Owner; (3) dicatat sebagai ADR di docs/adr/ dan diringkas pada Decision Register versi berikutnya; (4) dokumen ini diperbarui pada revisi terjadwal, dengan nomor versi dan catatan perubahan.

45. Final Validation

45.1 Daftar Periksa Mutu (Tahap 7)

| Pertanyaan | Hasil pemeriksaan |

| --- | --- |

| Apakah semua dokumen lama sudah tercakup? | Ya: 11 dokumen resmi tercakup pada Bagian I sampai VII dan lampiran; 12 dokumen riset diringkas pada Lampiran C dan dipisahkan sesuai DEC-023. Pemetaan pada Lampiran A.2 |

| Apakah ada keputusan yang bertentangan? | Sembilan konflik dokumen lama diselesaikan (A.3); pemeriksaan silang ID DEC, FR, AC, dan nomor bagian dilakukan pada dokumen akhir |

| Apakah arsitektur konsisten? | Ya: satu diagram container, satu aliran data, satu pemilik per fakta (13.1); perubahan Redis ke Valkey, MinIO ke SeaweedFS, dan antrean AI diterapkan di seluruh bagian |

| Apakah tanggung jawab framework jelas? | Ya: matriks 14.1, larangan 14.2, dan batas pemilik schema 16.2 |

| Apakah fitur utama lengkap? | Ya: FR-001 s.d. 038 (MVP) dan FR-040 s.d. 054 (fase lanjut); empat FR baru menutup celah (A.4) |

| Apakah roadmap realistis? | Dinyatakan sebagai hipotesis dengan kalibrasi G1 dan risiko RISK-01 terbuka; tidak diklaim realistis tanpa data kecepatan |

| Apakah acceptance criteria dapat diuji? | Ya: setiap AC punya bukti yang disebutkan; AC baru ditambahkan untuk elemen baru |

| Apakah master prompt sesuai dengan dokumen Word? | Master prompt merujuk nomor bagian dokumen ini dan memuat invarian kritis secara mandiri; keselarasan diperiksa pada tahap pembuatan |

| Apakah kedua file dapat dibuka dan dibaca? | Diperiksa pada tahap pembuatan (konversi dan render); hasil dilaporkan pada ringkasan akhir |



45.2 Keterbatasan Dokumen Ini

Versi komponen di luar yang terverifikasi pada 12.4 belum dikunci; dikunci di M0.

Seluruh angka target, batas laju, ambang, dan estimasi adalah hipotesis.

Tidak ada hasil eksperimen, uji, atau evaluasi dalam dokumen ini.

EKAA.docx, PROJECT_CONTEXT.md, dan Decision Log lama tidak tersedia; riwayat direkonstruksi dari yang ada.

Kajian hukum UU PDP, verifikasi indeksasi jurnal, dan penetration test belum dilakukan.

LAMPIRAN

Lampiran A. Hasil Audit ZIP dan Rekonsiliasi

A.1 Inventaris

| Kelompok | Berkas |

| --- | --- |

| Dokumen resmi (11) | FINAL_PROJECT_AGREEMENT, FINAL_DECISION_REGISTER, FINAL_TECHNOLOGY_STACK, FINAL_REQUIREMENT_SPECIFICATION, FINAL_SYSTEM_ARCHITECTURE, FINAL_DATABASE_DESIGN, FINAL_API_SPECIFICATION, FINAL_SECURITY_SPECIFICATION, FINAL_IMPLEMENTATION_ROADMAP, FINAL_ACCEPTANCE_CRITERIA, PROJECT_RISK_REGISTER |

| Riset Track C (12, DRAFT) | RESEARCH_GAP_ANALYSIS, RESEARCH_QUESTIONS_AND_HYPOTHESES, RESEARCH_METHODOLOGY, EXPERIMENTAL_DESIGN, NOVELTY_AND_CONTRIBUTION, JOURNAL_TARGET_RECOMMENDATION, PREVIOUS_RESEARCH_COMPARISON, RESEARCH_SEARCH_PROTOCOL, CRITICAL_LITERATURE_REVIEW, LITERATURE_REVIEW_MATRIX, REFERENCES_IEEE, ARTICLE_DRAFT_IMRAD |

| Tidak ada dalam ZIP | EKAA.docx, PROJECT_CONTEXT.md, Decision Log lama |



A.2 Pemetaan Dokumen Lama ke Dokumen Ini

| Dokumen lama | Bagian pada dokumen ini |

| --- | --- |

| FINAL_PROJECT_AGREEMENT | 1 s.d. 4, 40, 41, 43, 44 |

| FINAL_DECISION_REGISTER | 40 |

| FINAL_TECHNOLOGY_STACK | 12 |

| FINAL_REQUIREMENT_SPECIFICATION | 5 s.d. 11 |

| FINAL_SYSTEM_ARCHITECTURE | 13, 14, 18 |

| FINAL_DATABASE_DESIGN | 16 |

| FINAL_API_SPECIFICATION | 17 |

| FINAL_SECURITY_SPECIFICATION | 30, 31 |

| FINAL_IMPLEMENTATION_ROADMAP | 33, 34 |

| FINAL_ACCEPTANCE_CRITERIA | 35, 37 |

| PROJECT_RISK_REGISTER | 39 |

| Dokumen riset (12) | Lampiran C |



A.3 Konflik Antardokumen dan Penyelesaiannya

| No | Konflik | Penyelesaian |

| --- | --- | --- |

| K-01 | Agreement bertanda versi 1.3-draft dan DEC-024 s.d. 030 "disetujui", sedangkan Decision Register bertanda 1.2-draft dengan status PROPOSED | Seluruhnya FINAL APPROVED pada dokumen ini; persetujuan Owner sebelumnya dan 13 November tercatat |

| K-02 | Konsep menyebut "indeks BM25"; DEC-029 menetapkan FTS ts_rank | FR-014 dikoreksi; baseline FTS, BM25 sejati dievaluasi (16.6) |

| K-03 | DEC-010 dan DEC-028 menyiratkan antrean Redis bersama, DEC-028 menolak antrean lintas bahasa | Kontrak ingestion HTTP 202 + callback; antrean internal worker AI = tabel PostgreSQL (DEC-032); Valkey hanya untuk Laravel |

| K-04 | Token berumur pendek dan refresh token disebut, penyimpanan klien tidak ditentukan | DEC-031 |

| K-05 | Keputusan MinIO bertentangan dengan fakta pemeliharaan terbaru | DEC-034 |

| K-06 | Redis dipilih tanpa pertimbangan lisensi distribusi | DEC-035 |

| K-07 | Observabilitas: "Langfuse opsional" ambigu | DEC-037: tidak dipakai di MVP |

| K-08 | Reverse proxy "Caddy atau Nginx" | DEC-036: Caddy |

| K-09 | Gerbang G1 sampai G4 mensyaratkan keputusan Owner, bertentangan dengan mandat 13 November | DEC-050: G1 dan G2 menjadi checkpoint bukti; G3 bersyarat dan G4 tetap Owner |



A.4 Celah Spesifikasi dan Penutupannya

| No | Celah | Penutupan |

| --- | --- | --- |

| G-01 | Tidak ada siklus kata sandi awal dan reset | FR-034, DD-10, AC-M1-09 |

| G-02 | Constraint unik dengan principal NULL mengizinkan duplikat | DD-09 |

| G-03 | Evaluasi (ai.eval_*) tanpa antarmuka | FR-035, DEC-046, AC-M4-09 |

| G-04 | Backup dan restore belum menjadi requirement | FR-036, DEC-044, AC-M8-04 |

| G-05 | Retensi dan penghapusan belum menjadi requirement | FR-037 |

| G-06 | Tidak ada reindex saat model embedding diganti | FR-038, AC-M3-08 |

| G-07 | Antrean worker AI tidak tahan restart | DEC-032, DD-11 |

| G-08 | Perluasan izin unit induk tidak didefinisikan | DD-14, AC-M2-07 |

| G-09 | Tidak ada trace diagnostik per pesan | Endpoint /messages/{id}/trace, UC-11 |

| G-10 | Router tidak dapat dikoreksi pengguna | route_hint (26.3), AC-M6-02 |

| G-11 | Format angka Indonesia pada impor dataset tidak ditentukan | locale_config, 23.1, AC-M5-07 |

| G-12 | Tidak ada validasi narasi analitik terhadap angka | 23.3 langkah 7, AC-M5-09 |

| G-13 | Aturan versi berlaku dan tanggal efektif tidak rinci | 19.2 |

| G-14 | Tidak ada uji konformitas object store pengganti | AC-M0-09 |

| G-15 | Perilaku HNSW dengan filter selektif tidak spesifik | DD-13, 16.6 |

| G-16 | Pemilihan pustaka parsing dan isu lisensi tidak ditetapkan | DEC-039, AC-M3-09 |

| G-17 | Kebijakan versi dan rantai pasok tidak tertulis | DEC-045, 12.6 |



Lampiran B. Matriks Ketertelusuran (Ringkas)

| Kelompok FR | Milestone | AC | Endpoint atau komponen utama |

| --- | --- | --- | --- |

| FR-001, 034 | M1 | AC-M1-01 s.d. 03, 09 | /auth/*, /me |

| FR-002, 003, 004, 005 | M1 | AC-M1-04 s.d. 08 | /users, /org-units, /settings, /audit-logs |

| FR-006 | M0, semua | AC-M0-04 | contracts/openapi/ |

| FR-010, 011, 012 | M2 | AC-M2-01 s.d. 07 | /documents* |

| FR-013, 014, 038 | M3 | AC-M3-01 s.d. 09 | /internal/v1/ingestions, ai.chunks, ai.jobs |

| FR-015, 016, 017, 018 | M4 | AC-M4-01 s.d. 08 | /conversations/*/messages, /internal/v1/ask |

| FR-035 | M4 | AC-M4-01, 09 | CLI ekaa_ai.eval |

| FR-019 | M6 | AC-M6-05 | /messages/{id}/feedback |

| FR-020 s.d. 025 | M5 | AC-M5-01 s.d. 09 | /datasets*, /metrics, /analytics/run |

| FR-030, 032, 033 | M6 | AC-M6-01 s.d. 04, 06 | Router, guardrails, CI |

| FR-031 | M7 | AC-M7-01 s.d. 07 | apps/connector, /internal/v1/connector/* |

| FR-036, 037 | M8 | AC-M8-04 | deploy/scripts, pekerjaan retensi |



Lampiran C. Ringkasan Track C (Riset, Berstatus DRAFT, Terpisah dari Klaim Proyek)

| Aspek | Isi (sebagaimana tercatat; belum ada hasil eksperimen) |

| --- | --- |

| Keputusan | Tiga artikel berurutan. Artikel 1: retrieval hibrida dengan reranking pada dokumen institusi berbahasa Indonesia dengan sitasi terverifikasi (Alternatif A). Alternatif D ditolak |

| Pertanyaan riset dan hipotesis | Disusun pada RESEARCH_QUESTIONS_AND_HYPOTHESES (RQ1 menyangkut nilai hibrida dan reranker); hipotesis bersifat dugaan yang diuji, bukan temuan |

| Hubungan dengan proyek | Golden dataset, evaluation harness, ablasi C0 sampai C3, dan protokol pemilihan model pada dokumen ini menjadi infrastruktur eksperimen |

| Bahasa dan format | Judul artikel Bahasa Inggris; isi Bahasa Indonesia sebagai bahasa kerja awal; sekitar 2.500 kata; struktur IMRAD; sitasi IEEE |

| Target jurnal | Daftar kandidat tersedia; status indeksasi (Scopus atau SINTA) belum diverifikasi (OA-10); tidak ada klaim kuartil |

| Pagar klaim | Klaim hanya dengan data tercatat, pembanding setara, referensi terverifikasi, hasil negatif dilaporkan, novelty berdasarkan matriks literatur (Bagian 37.1 dan RISK-14) |

| Catatan | Rujukan sekunder (misalnya nilai k pada RRF, kekompetitifan TF-IDF) wajib diverifikasi ke sumber primer sebelum dikutip pada manuskrip |



Lampiran D. Glosarium

| Istilah | Arti |

| --- | --- |

| ABAC | Attribute-Based Access Control |

| ACL | Daftar kontrol akses; di EKAA berupa acl_principals pada chunk dan document_permissions pada core |

| Abstain | Menjawab bahwa bukti tidak ditemukan alih-alih mengarang |

| ANN | Approximate Nearest Neighbor |

| Chunk | Potongan teks dokumen yang diindeks; child untuk retrieval, parent untuk konteks |

| CAGR | Compound Annual Growth Rate |

| DuckDB | Mesin basis data analitik tertanam |

| FTS | Full-Text Search PostgreSQL |

| Golden dataset | Kumpulan pertanyaan dan jawaban acuan terversi untuk evaluasi |

| HNSW | Hierarchical Navigable Small World, jenis indeks vektor |

| IdP | Identity Provider |

| JWT | JSON Web Token |

| nDCG, MRR, Recall@k | Metrik evaluasi retrieval |

| Principals | Himpunan identitas akses pengguna: all, peran, unit (dan induk), pengguna |

| RAG | Retrieval-Augmented Generation |

| RRF | Reciprocal Rank Fusion |

| Reranker | Model cross-encoder yang mengurutkan ulang kandidat |

| Single-tenant | Satu instalasi untuk satu organisasi |

| Valkey | Penyimpan data kunci-nilai sumber terbuka kompatibel protokol Redis |



Lampiran E. Riwayat Dokumen

| Versi | Tanggal | Perubahan |

| --- | --- | --- |

| 1.0-draft | 8 November 2026 | Gelombang 3: Project Agreement dan dokumen pendukung (11 dokumen resmi) |

| 2.0 | 13 November 2026 | Konsolidasi seluruh dokumen menjadi satu dokumen Word; penutupan 17 celah; penyelesaian 9 konflik; perubahan DEC-010, 011, 016, 024; penambahan DEC-031 s.d. DEC-050; FR-034 s.d. FR-038; penetapan seluruh keputusan FINAL APPROVED BY PROJECT OWNER |

