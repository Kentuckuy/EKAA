# FINAL REQUIREMENT SPECIFICATION — EKAA

| Atribut | Nilai |
|---|---|
| Versi dokumen | 1.0-draft (Gelombang 1) |
| Tanggal pembaruan | 8 November 2026 |
| Status dokumen | **READY FOR OWNER APPROVAL** |
| Dokumen terkait | `FINAL_DECISION_REGISTER.md`, `FINAL_TECHNOLOGY_STACK.md` |
| Dokumen yang akan dirujuk (Gelombang 2–3) | `FINAL_SYSTEM_ARCHITECTURE.md`, `FINAL_DATABASE_DESIGN.md`, `FINAL_API_SPECIFICATION.md`, `FINAL_SECURITY_SPECIFICATION.md`, `FINAL_ACCEPTANCE_CRITERIA.md` |

## 1. Ringkasan Konsep (Dikonsolidasikan)

**Apa itu EKAA.** Platform asisten pengetahuan dan analitik privat, satu instalasi untuk satu organisasi (DEC-001).

**Masalah.** Informasi tersebar; layanan AI publik berisiko bagi data dan kepatuhan UU PDP; LLM tidak andal untuk aritmetika dan dapat mengarang; dashboard BI statis.

**Pengguna.** Staf, pengelola pengetahuan, analis data, dan administrator organisasi.

**Tujuan produk.** (1) Satu pintu akses untuk dokumen dan data angka. (2) Setiap jawaban dapat diverifikasi: sitasi untuk teks, rumus dan query untuk angka. (3) Data tetap di bawah kendali organisasi. (4) Mudah dipasang dan dipelihara. (5) Menghasilkan evaluasi empiris untuk publikasi.

**Dua jalur inti.** Knowledge Assistant (RAG bersitasi dengan kontrol akses) dan Analytics Assistant (metrik deterministik, visualisasi), dihubungkan Hybrid Router.

Label: bagian ini adalah **fakta dari `EKAA.docx`** yang disusun ulang. Rekomendasi dan asumsi diberi label tersendiri di bawah.

## 2. Asumsi

| ID | Asumsi | Dampak bila salah |
|---|---|---|
| AS-01 | Pengembang utama satu orang; lingkungan pengembangan Windows/Laragon dan Docker tersedia | Estimasi jadwal berubah |
| AS-02 | Bahasa utama dokumen dan antarmuka: Indonesia | Pemilihan embedding dan LLM berubah |
| AS-03 | Format dokumen MVP: PDF berteks, DOCX, XLSX, CSV. Dokumen pindai (OCR) pada Phase 2 | Beban MVP naik |
| AS-04 | Ukuran korpus MVP skala menengah (ribuan dokumen, bukan jutaan) | Memengaruhi DEC-008 |
| AS-05 | Studi kasus demo memakai korpus yang boleh dipublikasikan atau disintesis | Tidak ada data riil sensitif di demo dan publikasi |

## 3. Peran Pengguna

Single-tenant, sehingga **tidak ada Super Administrator di dalam aplikasi** (DEC-019). Akses infrastruktur dipegang operator di luar aplikasi.

| Peran | Fungsi | Hak akses utama |
|---|---|---|
| Org Admin | Mengelola organisasi | Pengguna, peran, unit kerja, pengaturan sistem, sumber data, kebijakan retensi; melihat log audit (sebelum peran Auditor ada) |
| Knowledge Manager | Mengelola pengetahuan | Unggah, versi, status berlaku/dicabut, dan izin dokumen; menjalankan sinkronisasi; meninjau umpan balik jawaban |
| Data Analyst | Mengelola data angka | Impor dataset, kamus data, definisi metrik, izin dataset |
| Standard User | Pengguna akhir | Bertanya, melihat sumber, memberi umpan balik, riwayat percakapan sendiri; hanya pada dokumen dan dataset yang diizinkan |
| Auditor (Phase 2) | Pemeriksa | Baca log audit dan laporan, tanpa akses isi dokumen kecuali diizinkan |

Peran bersifat tambahan (satu pengguna dapat memiliki beberapa peran). Hak akses dokumen dan dataset ditentukan per peran, per unit kerja, dan per izin eksplisit.

## 4. Functional Requirements

Kolom ringkas berikut memuat: deskripsi dan tujuan, aktor, input → proses → output, dependency, prioritas, dan acceptance criteria. Prioritas: **M** = MVP Must Have, **P2** = Phase 2, **P3** = Phase 3. Pemilik layanan: LV = Laravel, CI = CodeIgniter, AI = FastAPI, FE = frontend.

### 4.1 Fondasi, identitas, dan administrasi

| ID | Fitur (layanan) | Deskripsi dan tujuan | Aktor | Input → Proses → Output | Dependency | Prior. | Acceptance criteria |
|---|---|---|---|---|---|---|---|
| FR-001 | Login dan sesi (LV, FE) | Autentikasi pengguna dan penerbitan token akses. Tujuan: satu identitas tunggal. | Semua | Kredensial → verifikasi, rate limit, terbitkan token → token/sesi | — | M | Login valid berhasil; kredensial salah ditolak tanpa membocorkan penyebab; percobaan berulang dibatasi; token kedaluwarsa ditolak |
| FR-002 | Manajemen pengguna, peran, unit kerja (LV, FE) | CRUD pengguna, penetapan peran dan unit. Tujuan: kontrol akses. | Org Admin | Data pengguna → validasi, simpan, audit → pengguna/peran | FR-001 | M | Hanya Org Admin yang dapat mengubah; setiap perubahan tercatat di audit; pengguna nonaktif tidak dapat login |
| FR-003 | Panel admin dasar (FE, LV) | Antarmuka pengelolaan pengguna, dokumen, dataset, log. | Org Admin, Knowledge Manager, Data Analyst | Aksi UI → panggil API → tampilan | FR-002 | M | Setiap layar terhubung ke API nyata; akses mengikuti peran |
| FR-004 | Pengaturan sistem (LV) | Konfigurasi per instalasi: nama, logo, penyedia LLM, retensi. | Org Admin | Nilai konfigurasi → validasi, simpan → konfigurasi aktif | FR-002 | M | Perubahan tanpa mengubah kode; rahasia tidak ditampilkan ulang |
| FR-005 | Log audit (LV) | Mencatat pertanyaan, sumber diakses, perubahan konfigurasi dan hak akses. Tujuan: auditabilitas. | Sistem; Org Admin | Peristiwa → catat append-only → daftar log | FR-001 | M | Setiap pertanyaan dan perubahan izin menghasilkan entri; entri tidak dapat diubah lewat aplikasi; dapat difilter |
| FR-006 | REST API dan OpenAPI (LV, AI, CI) | Kontrak API terdokumentasi. | Pengembang, klien | Permintaan → validasi → respons standar | — | M | Dokumentasi OpenAPI dihasilkan dan sesuai perilaku; kode error konsisten |

### 4.2 Knowledge Assistant (RAG)

| ID | Fitur (layanan) | Deskripsi dan tujuan | Aktor | Input → Proses → Output | Dependency | Prior. | Acceptance criteria |
|---|---|---|---|---|---|---|---|
| FR-010 | Unggah dan validasi dokumen (LV, FE) | Unggah PDF berteks, DOCX, XLSX, CSV. | Knowledge Manager | Berkas → validasi tipe, ukuran, pemindaian dasar → simpan di MinIO + metadata | FR-002 | M | Tipe/ukuran tidak valid ditolak; berkas tersimpan; metadata tercatat; MIME dicek di sisi server, bukan hanya ekstensi |
| FR-011 | Versi dan status dokumen (LV) | Versi, status (berlaku, dicabut), tanggal efektif. | Knowledge Manager | Versi baru/perubahan status → simpan riwayat | FR-010 | M | Dokumen dicabut tidak muncul dalam retrieval; riwayat versi dapat dilihat |
| FR-012 | Izin dokumen (LV) | Izin per peran, unit kerja, dan dokumen. | Knowledge Manager | Aturan izin → simpan, audit | FR-002, FR-010 | M | Pengguna tanpa izin tidak melihat dokumen di hasil pencarian maupun sitasi; diuji dengan kasus lintas izin |
| FR-013 | Parsing dan chunking (AI) | Ekstraksi teks dan struktur (judul, pasal, halaman); chunk berbasis struktur dengan overlap. | Sistem | Berkas → parsing → chunk bermetadata | FR-010 | M | Setiap chunk menyimpan dokumen, versi, halaman, dan bagian; teks identik dengan sumber pada sampel uji |
| FR-014 | Embedding dan indeks (AI) | Pembuatan vektor dan indeks leksikal. | Sistem | Chunk → embedding → pgvector + indeks BM25 | FR-013 | M | Chunk terindeks; pembaruan versi mengganti indeks lama; proses berjalan di worker asinkron dengan status |
| FR-015 | Retrieval hibrida berfilter akses (AI) | BM25 + dense digabung Reciprocal Rank Fusion; filter izin dan versi diterapkan **sebelum** pencarian. | Standard User | Pertanyaan + konteks izin → retrieval → kandidat | FR-012, FR-014 | M | Nol chunk dari dokumen tanpa izin pada rangkaian uji kebocoran; Recall@10 diukur terhadap golden dataset (target awal > 0,9, divalidasi setelah baseline) |
| FR-016 | Reranking (AI) | Cross-encoder pada kandidat teratas. | Sistem | Kandidat → rerank → urutan akhir | FR-015 | M | Peningkatan terhadap tanpa reranker terukur (bahan RQ1); latensi dicatat |
| FR-017 | Jawaban bersitasi dan abstain (AI) | Jawaban berlandaskan bukti dengan sitasi (dokumen, bagian, halaman); menyatakan bukti tidak ditemukan bila lemah. | Standard User | Pertanyaan + kandidat → generasi → jawaban + sitasi | FR-016 | M | Setiap klaim penting punya sitasi yang dapat dibuka; pertanyaan tanpa bukti menghasilkan penolakan terstruktur; faithfulness diukur (target awal > 0,9) |
| FR-018 | Chat dan riwayat (FE, LV) | Antarmuka tanya jawab, riwayat percakapan per pengguna. | Standard User | Pesan → API → tampilan jawaban, sumber | FR-017 | M | Riwayat hanya dapat dibaca pemiliknya; sumber tampil dan tertaut |
| FR-019 | Umpan balik jawaban (FE, LV) | Penilaian benar/salah beserta catatan. | Standard User; Knowledge Manager | Penilaian → simpan → daftar tinjauan | FR-018 | M | Umpan balik tersimpan terkait pesan; Knowledge Manager dapat meninjau |

### 4.3 Analytics Assistant

| ID | Fitur (layanan) | Deskripsi dan tujuan | Aktor | Input → Proses → Output | Dependency | Prior. | Acceptance criteria |
|---|---|---|---|---|---|---|---|
| FR-020 | Impor dataset (LV, AI) | Impor CSV dan XLSX. | Data Analyst | Berkas → validasi, profil kolom → dataset tersimpan | FR-002 | M | Tipe kolom terdeteksi; baris galat dilaporkan; dataset terhubung ke pemilik dan izin |
| FR-021 | Kamus data (LV) | Penjelasan kolom, satuan, dan metrik. | Data Analyst | Deskripsi → simpan | FR-020 | M | Setiap kolom dapat diberi arti dan satuan; tampil pada hasil analitik |
| FR-022 | Pustaka metrik deterministik (AI) | CAGR, YoY, MoM, rata-rata bergerak, proporsi, ranking, agregasi per dimensi. Ditulis sebagai fungsi teruji. | Sistem | Pilihan metrik + parameter → hitung → nilai + rumus | FR-020 | M | Hasil benar 100% pada kasus uji (target); CAGR memakai jumlah periode n; menangani nilai awal nol/negatif, periode hilang, dan perbedaan satuan dengan pesan jelas; LLM tidak menghitung angka |
| FR-023 | Pemilihan metrik dari bahasa alami (AI) | LLM memetakan pertanyaan ke metrik dan parameter lewat function calling. | Standard User, Data Analyst | Pertanyaan → pemilihan metrik → validasi parameter | FR-022, FR-021 | M | Parameter divalidasi skema; permintaan tak dikenal ditolak; metrik, rumus, dan sumber data ditampilkan |
| FR-024 | Grafik otomatis (AI, FE) | LLM menghasilkan spesifikasi grafik JSON tervalidasi; data dari hasil hitung. | Standard User, Data Analyst | Hasil → spesifikasi → ECharts | FR-022 | M | Nilai pada grafik identik dengan hasil hitung; spesifikasi tidak valid ditolak; ekspor gambar atau tabel |
| FR-025 | Izin dataset (LV) | Izin per peran, unit, dan dataset. | Data Analyst | Aturan → simpan, audit | FR-020 | M | Pengguna tanpa izin tidak dapat menanyakan dataset |

### 4.4 Router, konektor, dan keamanan MVP

| ID | Fitur (layanan) | Deskripsi dan tujuan | Aktor | Input → Proses → Output | Dependency | Prior. | Acceptance criteria |
|---|---|---|---|---|---|---|---|
| FR-030 | Hybrid Router MVP (AI) | Klasifikasi: dokumen, analitik, di luar cakupan; ambang keyakinan dan permintaan klarifikasi. | Sistem | Pertanyaan → kelas + keyakinan → jalur | FR-017, FR-023 | M | Precision/recall/F1 per kelas diukur pada golden dataset; keyakinan rendah memicu klarifikasi, bukan tebakan |
| FR-031 | Connector & Sync MVP (CI) | Satu konektor (folder jaringan/lokal), jadwal sinkronisasi, deteksi perubahan. | Knowledge Manager | Sumber + jadwal → pindai → serahkan berkas baru/berubah ke Laravel | FR-006, FR-010 | M | Berkas baru dan berubah terdeteksi; CI tidak membuat keputusan izin; kontrak API diuji; kegagalan konektor tercatat dan tidak merusak data |
| FR-032 | Guardrails dasar (AI) | Penyaringan prompt injection dari isi dokumen, masking data pribadi dasar, validasi keluaran. | Sistem | Teks → penyaringan → teks aman | FR-017 | M | Rangkaian uji injection (tersusun dan terdokumentasi) tidak mengubah perilaku sistem; pola data pribadi dasar dimasking pada log |
| FR-033 | Pengamanan kunci dan rahasia (semua) | Rahasia di variabel lingkungan atau penyimpan rahasia; tidak ada di repositori. | Operator | — | — | M | Pemindaian rahasia pada CI tidak menemukan kebocoran |

### 4.5 Phase 2 dan Phase 3

| ID | Fitur | Prior. | Alasan penundaan | Dependency |
|---|---|---|---|---|
| FR-040 | Text-to-SQL read-only (parser SELECT, batas waktu, koreksi diri) | P2 | Risiko keamanan dan kompleksitas; MVP memakai pustaka metrik | FR-022, FR-021 |
| FR-041 | Pertanyaan hibrida majemuk (dekomposisi, gabung teks dan angka) | P2 | Membutuhkan router dan kedua jalur stabil | FR-030 |
| FR-042 | OCR dan ekstraksi tabel PDF | P2 | Kualitas bervariasi; perlu riset terpisah | FR-013 |
| FR-043 | Verifikasi sitasi (entailment) dan verifikasi angka | P2 | Menambah latensi; butuh baseline | FR-017 |
| FR-044 | Memori percakapan dan penulisan ulang pertanyaan lanjutan | P2 | Bergantung pada chat yang stabil | FR-018 |
| FR-045 | Konektor Google Drive dan SharePoint | P2 | Otorisasi OAuth dan kuota API | FR-031 |
| FR-046 | Row-level security untuk data tabel | P2 | MVP memakai izin level dataset | FR-025 |
| FR-047 | Peran Auditor | P2 | MVP memakai Org Admin untuk audit | FR-005 |
| FR-048 | Cache semantik dan cache hasil query | P2 | Optimasi setelah baseline | FR-017 |
| FR-049 | Prometheus dan Grafana | P2 | Observabilitas lanjutan | DEC-016 |
| FR-050 | SSO (Keycloak/OIDC) | P3 | DEC-006 | FR-001 |
| FR-051 | Deteksi anomali dan peramalan | P3 | Opsional, bukan inti RQ | FR-022 |
| FR-052 | Chat widget dan bot WhatsApp | P3 | Antarmuka tambahan | FR-018 |
| FR-053 | Domain pack per jenis organisasi | P3 | Setelah dua studi kasus | FR-021 |
| FR-054 | Provisioning otomatis dan Helm | P3 | DEC-015 | — |

## 5. Cakupan Fitur Akhir

**MVP — Must Have:** FR-001 s.d. FR-006, FR-010 s.d. FR-019, FR-020 s.d. FR-025, FR-030 s.d. FR-033.

**Phase 2 — Advanced:** FR-040 s.d. FR-049.

**Phase 3 — Enterprise Enhancement:** FR-050 s.d. FR-054.

**Out of Scope (belum dikerjakan):**

| Item | Alasan |
|---|---|
| Multi-tenant dalam satu instalasi | Bertentangan dengan DEC-001 |
| Fine-tuning model sendiri | Di luar kebutuhan dan anggaran; diganti evaluasi model yang ada |
| Aplikasi mobile native | Antarmuka web dan WhatsApp mencukupi |
| Penulisan data ke database sumber (write-back) | Risiko keamanan; sistem bersifat read-only terhadap data sumber |

## 6. Non-Functional Requirements

Target bersifat **awal** dan harus ditetapkan ulang setelah baseline diukur.

| ID | Kategori | Kebutuhan | Target awal / verifikasi |
|---|---|---|---|
| NFR-PERF-001 | Performance | Latensi jawaban dokumen | p50 dan p95 dicatat; target ditetapkan setelah baseline (hipotesis kerja: p95 di bawah 15 detik) |
| NFR-PERF-002 | Performance | Latensi jalur metrik | Lebih rendah dari jalur dokumen; p95 dicatat |
| NFR-PERF-003 | Performance | Ingestion berjalan asinkron | UI tidak terblokir; status proses terlihat |
| NFR-SEC-001 | Security | Autentikasi dan otorisasi pada setiap endpoint | Uji otorisasi otomatis lulus; tidak ada endpoint publik selain login |
| NFR-SEC-002 | Security | Kebocoran lintas hak akses | Nol pada rangkaian uji |
| NFR-SEC-003 | Security | SQL hanya read-only (Phase 2) | Seluruh kasus uji tulis/DDL ditolak |
| NFR-SEC-004 | Security | Enkripsi saat transit (TLS) dan tersimpan | Dibuktikan pada konfigurasi deployment |
| NFR-REL-001 | Reliability | Kegagalan layanan AI tidak merusak data inti | Uji kegagalan: Laravel tetap berfungsi dan mengembalikan error terstruktur |
| NFR-REL-002 | Reliability | Operasi sinkronisasi idempoten | Pengulangan tidak menggandakan dokumen |
| NFR-SCA-001 | Scalability | Worker ingestion dapat ditambah | Penambahan worker meningkatkan throughput pada uji beban |
| NFR-MNT-001 | Maintainability | Pemisahan controller dan business logic; kontrak API terdokumentasi | Tinjauan kode; OpenAPI sesuai perilaku |
| NFR-MNT-002 | Maintainability | Migrasi database terversi dengan rollback | Uji migrasi naik dan turun |
| NFR-AVL-001 | Availability | Pencadangan terjadwal dan uji pemulihan | Uji restore berhasil; target RPO/RTO ditetapkan Owner |
| NFR-USA-001 | Usability | Pengguna dapat bertanya dan melihat sumber tanpa pelatihan | Skor SUS dan waktu menemukan informasi dibanding cara manual |
| NFR-CMP-001 | Compatibility | Peramban modern (dua versi terakhir) | Uji Playwright |
| NFR-PRV-001 | Privacy | Masking data pribadi pada log dan retensi terkonfigurasi | Sesuai UU PDP; detail di `FINAL_SECURITY_SPECIFICATION.md` |
| NFR-AUD-001 | Auditability | Setiap jawaban dapat ditelusuri ke sumber, parameter, dan versi model | Rekaman audit memuat identifikasi model dan prompt |
| NFR-REP-001 | Reproducibility (riset) | Eksperimen dapat diulang | Seed, versi model, dan konfigurasi dicatat |

## 7. Strategi Pengujian (Ringkasan, Dirinci di `FINAL_ACCEPTANCE_CRITERIA.md`)

| Jenis | Alat | Cakupan |
|---|---|---|
| Unit | PHPUnit/Pest, pytest | Pustaka metrik, policy, parser |
| Integrasi dan API | PHPUnit, pytest, kontrak OpenAPI | Laravel–CodeIgniter, Laravel–FastAPI |
| UI dan E2E | Playwright | Alur unggah → tanya → sitasi |
| Keamanan dan otorisasi | Uji matriks peran × sumber daya | Kebocoran lintas hak akses, prompt injection |
| RAG dan retrieval | Golden dataset | Recall@k, MRR, nDCG, faithfulness, akurasi sitasi |
| Metric engine | Kasus uji deterministik | Kebenaran 100% |
| Router | Dataset berlabel | Precision, recall, F1 per kelas |
| Performa | k6 atau Locust | Latensi p50/p95, throughput, biaya per pertanyaan |

## 8. Pertanyaan Terbuka untuk Owner

Lihat `FINAL_DECISION_REGISTER.md` bagian 4.2.

## 9. Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0-draft | 2026-11-08 | Penyusunan awal |
