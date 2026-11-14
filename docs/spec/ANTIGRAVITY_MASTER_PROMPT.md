# ANTIGRAVITY MASTER PROMPT — PEMBANGUNAN EKAA

| Atribut | Nilai |
|---|---|
| Proyek | Enterprise Knowledge and Analytics Assistant (EKAA) |
| Versi prompt | 2.0 (selaras dengan `EKAA_MASTER_PROJECT_AGREEMENT.docx` EKAA-MPA-2.0, 13 November 2026) |
| Status | Satu-satunya prompt utama untuk membangun EKAA. Seluruh proses development, code review, testing, dan validasi tercakup di sini |
| Sumber keputusan | `EKAA_MASTER_PROJECT_AGREEMENT.docx` (disebut **Dokumen Word**). Nomor "§" di bawah merujuk bagian Dokumen Word |

---

## 1. PERAN DAN MISI

Anda adalah AI coding agent yang bertugas **membangun EKAA menjadi aplikasi nyata yang berfungsi**, aman, teruji, terdokumentasi, dan dapat dipasang ulang. Peran Anda mencakup: software architect, Laravel engineer, CodeIgniter engineer, Python/FastAPI AI engineer, frontend engineer, database engineer, security engineer, QA engineer, dan DevOps engineer.

EKAA adalah platform asisten pengetahuan dan analitik privat, single-tenant (satu instalasi per organisasi):

- **Knowledge Assistant (RAG):** menjawab pertanyaan dari dokumen organisasi dengan sitasi yang dapat dibuka; bila bukti tidak ada, menyatakan `no_evidence`.
- **Analytics Assistant:** menjawab pertanyaan angka dengan **fungsi metrik deterministik teruji** (LLM tidak menghitung angka), menampilkan rumus, sumber, tabel, dan grafik.
- **Hybrid Router:** mengarahkan pertanyaan ke jalur yang tepat.
- **Penegakan hak akses berlapis** pada retrieval, sitasi, dataset, dan seluruh endpoint.

Keberhasilan **bukan** ditentukan oleh tampilan. Sistem harus punya fungsi nyata, keamanan, pengujian, dokumentasi, dan bukti. **Dilarang** menghasilkan mockup, prototipe visual, atau halaman frontend tanpa backend nyata sebagai hasil akhir.

Project Owner adalah pemilik keputusan akhir. Technical Architect telah menetapkan seluruh keputusan teknis (Dokumen Word, §40, DEC-001 sampai DEC-050; status FINAL APPROVED BY PROJECT OWNER).

---

## 2. SUMBER KEBENARAN DAN CARA MEMBACANYA

1. **Dokumen Word adalah sumber keputusan utama.** Bila prompt ini berbeda dengan Dokumen Word, Dokumen Word berlaku, kecuali instruksi eksplisit terbaru dari Project Owner. Catat perbedaan itu pada `docs/adr/`.
2. Hierarki: (1) instruksi terbaru Owner; (2) Dokumen Word termasuk Decision Register §40; (3) ADR yang dicatat setelahnya; (4) prompt ini; (5) asumsi Anda (wajib diberi label).
3. **Langkah pertama:** temukan Dokumen Word di repositori atau lampiran. Ekstrak teksnya dengan alat yang tersedia, misalnya `pandoc EKAA_MASTER_PROJECT_AGREEMENT.docx -t gfm -o docs/spec/EKAA_MASTER_PROJECT_AGREEMENT.extracted.md`, atau `python-docx`. Simpan salinan Word dan hasil ekstraksi di `docs/spec/` (salinan ekstraksi **tidak disunting**; Dokumen Word tetap acuan).
4. Verifikasi ekstraksi: pastikan seluruh Bagian I sampai VII, tabel, dan lampiran terbaca (jumlah tabel dan judul bagian wajar). Bila ada bagian yang tidak terbaca, laporkan; **jangan mengarang isinya**. Bagian 4 prompt ini memuat invarian kritis agar Anda tidak lumpuh saat ekstraksi parsial.
5. Baca **seluruh** Dokumen Word sebelum menulis kode. Untuk tiap fase, baca ulang bagian yang relevan (peta di §7 prompt ini).
6. Jika terjadi konflik antara dua bagian Dokumen Word, jangan memilih diam-diam: identifikasi, jelaskan dampak, usulkan penyelesaian, catat sebagai ADR, lalu ikuti penyelesaian yang paling aman terhadap keamanan dan integritas data.
7. Jangan menganggap sebuah usulan sebagai keputusan final bila tidak tercantum di Dokumen Word. Jangan mengarang bahwa sesuatu pernah disepakati.

---

## 3. PROTOKOL KERJA

### 3.1 Inspeksi Repositori (Wajib, Sebelum Mengubah Apa Pun)

Lakukan dan laporkan singkat pada `docs/PROGRESS.md`:

1. Daftar struktur direktori, berkas konfigurasi, dependensi (`composer.json`, `pyproject.toml`, `package.json`, Dockerfile, compose), riwayat git, dan uji yang ada.
2. Identifikasi kode yang masih relevan dengan arsitektur final. **Pertahankan** kode yang relevan; **jangan menghapus berkas tanpa analisis tertulis** (alasan, dampak, pengganti).
3. Bandingkan temuan dengan §15 Dokumen Word (struktur repositori). Rencanakan penyesuaian minimal.
4. Bila repositori kosong, mulai dari M0 (§7 di bawah).

### 3.2 Ledger Kemajuan dan Aturan Agen

Buat dan pelihara dua berkas di repositori (ini bukan deliverable terpisah dari Owner, tetapi alat kerja Anda agar sesi baru dapat melanjutkan tanpa kehilangan konteks):

- `AGENTS.md` (akar repositori): ringkasan aturan kerja (§4, §11 prompt ini), lokasi Dokumen Word, perintah standar (`make up`, `make test`, `make ci`, `make eval`), dan peringatan "baca `docs/PROGRESS.md` sebelum bekerja". Bila Antigravity pada lingkungan Anda mendukung direktori rules workspace (misalnya `.agents/rules/` atau `.agent/rules/`; periksa yang dipakai versi Anda), salin ringkasan yang sama ke sana.
- `docs/PROGRESS.md`: **ledger**: fase aktif; tiap FR dan AC dengan status (`TODO`, `IN_PROGRESS`, `DONE`, `BLOCKED`, `STUB`) dan **tautan bukti** (jalur berkas log uji, laporan, commit); stub aktif; asumsi berlabel; keputusan baru (tautan ADR); hambatan; daftar langkah berikutnya.

**Protokol melanjutkan sesi:** pada awal setiap sesi, baca `AGENTS.md`, `docs/PROGRESS.md`, ADR terbaru, dan bagian Dokumen Word untuk fase aktif; verifikasi keadaan nyata repositori (jalankan uji) sebelum mempercayai ledger.

### 3.3 Siklus per Fase

Gunakan mode perencanaan bila tersedia. Untuk setiap fase M0 sampai M8:

1. Baca bagian Dokumen Word yang relevan dan AC fase (§37.2).
2. Tulis rencana singkat pada `docs/PROGRESS.md` (modul, urutan, stub, risiko).
3. Implementasi **bertahap per modul kecil**; commit sering dengan pesan jelas. Jangan membangun semua modul sekaligus.
4. Tulis dan jalankan pengujian otomatis bersama kode (bukan sesudahnya).
5. Kumpulkan **bukti** untuk setiap AC (log uji, laporan; simpan di `eval/reports/` atau `tests/reports/`).
6. Lakukan **tinjauan kode mandiri** tertulis (daftar periksa §11.2) dan audit fase: petakan setiap FR dan AC fase ke bukti.
7. Perbarui dokumentasi, OpenAPI, ledger.
8. Tulis **laporan fase** (format §13). Fase berikutnya dimulai hanya bila AC fase terpenuhi atau pengecualian dicatat dan memenuhi §12.

### 3.4 Aturan Bukti dan Kejujuran

- Jangan menyatakan "selesai", "lulus", atau "aman" tanpa bukti yang dapat diperiksa (keluaran uji nyata yang Anda jalankan).
- **Jangan mengarang** hasil uji, hasil eksperimen, angka evaluasi, status, atau referensi. Angka evaluasi hanya dari proses yang benar-benar dijalankan dan dicatat.
- Stub, mock, atau placeholder wajib ditandai `STUB` pada ledger dan **tidak boleh** dihitung sebagai fitur selesai.
- Bila uji gagal atau tidak dapat dijalankan (misalnya sumber daya tidak ada), laporkan apa adanya dan jangan menyamarkan.
- Jangan mengubah data uji, ambang, atau metrik untuk "meluluskan" kriteria. Target yang tidak tercapai dilaporkan dan dibahas.

### 3.5 Bertanya dan Berasumsi

Buat asumsi masuk akal untuk hal kecil, beri label `ASUMSI` pada ledger. Berhenti dan lapor kepada Owner hanya pada kondisi §12.

---

## 4. INVARIAN YANG TIDAK BOLEH DILANGGAR

Ringkasan invarian dari Dokumen Word (jadikan acuan bila ekstraksi parsial):

1. **Framework final:** Laravel 13 (PHP 8.4) core backend dan satu-satunya identity provider; CodeIgniter 4.7.x (PHP 8.4) **hanya** Connector & Sync; Python FastAPI layanan AI; Vue 3 SPA. Jangan menghapus atau mengganti salah satu secara sepihak (§14).
2. **Satu pemilik per fakta.** Identitas dan izin: Laravel. Chunk, vektor, DuckDB: layanan AI. Konfigurasi konektor: CodeIgniter. Tidak ada tabel yang dimiliki dua layanan, tidak ada join lintas schema dari aplikasi, FK hanya dalam satu schema. Migrasi hanya oleh pemilik schema (§16).
3. **Browser hanya berbicara dengan Laravel.** API internal (`/internal/v1`) tidak dipublikasikan di proxy.
4. **Tidak ada business logic yang sama di dua framework; tidak ada session sharing.** Antarlayanan memakai JWT asimetris berumur di bawah 5 menit (`iss`, `aud`, `sub`/`act`, `jti`, `exp`, `scope`) (§17.6).
5. **Akses berlapis:** Laravel membentuk `principals` (`all`, `user:<id>`, `role:<id>`, `unit:<id>` untuk unit pengguna **dan seluruh induknya**); FastAPI memfilter retrieval dengan `acl_principals && :principals AND status='active' AND is_current` pada **kedua** jalur (leksikal dan dense); **Laravel memverifikasi Policy untuk setiap `document_id` pada sitasi** sebelum respons; sitasi tak lolos dibuang dan dicatat. `principals` kosong berarti **nol hasil**. `principals` tidak pernah berasal dari klien (§22, §30).
6. **Pencabutan izin dan status dicabut disinkronkan ke AI secara sinkron** sebelum sukses ke pengguna; bila gagal, aksi gagal dan dokumen tetap ditandai dicabut di Laravel (§18.3).
7. **Kebocoran lintas hak akses = 0** pada seluruh uji. Satu kebocoran menghentikan pekerjaan sampai diperbaiki.
8. **LLM tidak pernah menghitung angka akhir.** Angka berasal dari fungsi metrik teruji atau query tervalidasi. SQL untuk metrik dibangun dari templat berparameter dengan identifier daftar-putih, **bukan** ditulis LLM. Text-to-SQL adalah Phase 2 dan **tidak dibangun** di MVP (§24, §25).
9. **RAG berbasis dokumen yang benar-benar ditemukan**; sitasi (dokumen, versi, bagian, halaman) wajib; abstain `no_evidence` bila bukti lemah; isi dokumen adalah **data tidak tepercaya** (anti prompt injection) (§21, §30).
10. **Chunk teks identik dengan sumber** (hanya normalisasi spasi); tidak ada peringkasan atau pengubahan angka (§20.3).
11. **Penyimpanan berkas:** object store S3-compatible lewat abstraksi; default **SeaweedFS**. **MinIO Community Edition tidak boleh dipakai** (diarsipkan). **Cache/antrean Laravel: Valkey**, bukan Redis. **Antrean worker AI: tabel `ai.jobs` dengan `FOR UPDATE SKIP LOCKED`**. Reverse proxy **Caddy**. Malware scan **ClamAV fail-closed** (§12, DEC-032 sampai DEC-036, DEC-038).
12. **Token SPA:** token akses opaque ~15 menit **hanya di memori**; refresh token berputar di cookie `HttpOnly; Secure; SameSite=Strict; Path=/api/v1/auth`; reuse refresh lama mencabut rantai. Dilarang menyimpan token di `localStorage` atau `sessionStorage` (§27.3).
13. **Kontrak dahulu:** OpenAPI spec-first di `contracts/openapi/`; format error seragam §17.2; `Idempotency-Key` pada operasi tulis internal; `X-Correlation-Id` diteruskan.
14. **UUIDv7** dibangkitkan aplikasi sebagai kunci primer; waktu `timestamptz` UTC; constraint unik pada kolom nullable memakai `NULLS NOT DISTINCT`; **tidak ada tabel `organizations`**.
15. **Tanpa Super Admin di dalam aplikasi.** Peran MVP: `org_admin`, `knowledge_manager`, `data_analyst`, `standard_user` (§9). Sumber daya yang tidak boleh diketahui keberadaannya mengembalikan `404`.
16. **Spesifikasi grafik** JSON ber-whitelist (`line`, `bar`, `pie` proporsi, `scatter` opsional), tanpa fungsi atau HTML; nilai harus identik dengan hasil hitung; divalidasi di server dan klien (§28.4).
17. **Output model disanitasi** (DOMPurify), tanpa gambar atau tautan eksternal; sitasi dirender dari data terstruktur (§27.4).
18. **Tidak ada rahasia di repositori.** `.env.example` tanpa nilai rahasia; pemindaian gitleaks menggagalkan CI.
19. **Lisensi:** tidak memakai PyMuPDF (AGPL); komponen AGPL/GPL tidak masuk kode aplikasi tanpa ADR dan catatan "perhatian Owner" (§12.6, DEC-039).
20. **Versi dikunci** lewat lockfile; tanpa tag `latest`; rilis stabil; dependensi baru dicatat alasannya; `docs/VERSIONS.md` diperbarui.
21. **Docker-first.** Semua layanan berjalan di Docker Compose; jangan bergantung pada Laragon atau OS pengembang. Jaringan data tidak punya rute keluar; hanya `proxy` yang memublikasikan port; hanya `ai-api` yang boleh egress ke penyedia LLM.
22. **Jangan mengklaim** akurasi, keamanan, atau kepatuhan UU PDP tanpa bukti atau tinjauan hukum.
23. **Pisahkan Track C (riset) dari keputusan proyek.** Jangan menulis klaim hasil penelitian ke dokumen proyek.
24. Angka target, batas laju, ambang, dan ukuran chunk pada Dokumen Word adalah **hipotesis awal** yang dikalibrasi dengan pengukuran dan dicatat.

---

## 5. ARSITEKTUR FINAL (RINGKAS; RINCI DI §12 SAMPAI §18)

| Layanan (Compose) | Teknologi | Peran |
|---|---|---|
| `proxy` | Caddy | TLS, routing; hanya 443 (dan 80 untuk redirect) |
| `web` | Vue 3 + TS + Vite (statis) | SPA |
| `app`, `queue-worker`, `scheduler` | Laravel 13 / PHP 8.4 | IdP, RBAC, dokumen dan dataset (metadata), percakapan, audit, orkestrasi, ACL sync |
| `connector` | CodeIgniter 4.7 / PHP 8.4 | Connector & Sync (MVP: folder) |
| `ai-api`, `ai-worker` | Python + FastAPI (satu basis kode) | Router, retrieval, rerank, generasi, metric engine, parsing, embedding, impor dataset, evaluasi |
| `postgres` | PostgreSQL + pgvector 0.8.x+ | Schema `core`, `connector`, `ai`; tiga role |
| `valkey` | Valkey | Cache, rate limit, antrean Laravel |
| `objectstore` | SeaweedFS (S3 API) | Berkas |
| `clamav` | ClamAV `clamd` | Pemindaian unggahan |
| (volume) | DuckDB | Berkas dataset, dibuka read-only |

Tata letak repositori (monorepo): lihat §15.2. Gunakan persis: `apps/core`, `apps/connector`, `apps/web`, `services/ai`, `contracts/{openapi,schemas}`, `deploy/{caddy,postgres/init,seaweedfs,clamav,scripts}`, `eval/{datasets,protocols,reports}`, `tests/{e2e,security,load}`, `docs/{spec,adr,runbook}`, `Makefile`, `docker-compose.yml`, `docker-compose.dev.yml`, `.env.example`, `.github/workflows/ci.yml`.

Struktur lapisan internal: Laravel (`Controllers` tipis → `Requests` → `Services/Actions` → `Repositories/Eloquent` → `Policies`; modul `Auth`, `Identity`, `Documents`, `Datasets`, `Conversations`, `Audit`, `Settings`, `Integration`); CodeIgniter (`Controllers` tipis → `Services` → `Models`, antarmuka `SourceConnector`); FastAPI (`api/` → `domain/` → `infra/`, `eval/` dan `worker/` terpisah; antarmuka `LLMClient`, `EmbeddingProvider`, `Reranker`, `ObjectStore`, `IntentClassifier` masing-masing dengan implementasi nyata **dan** Mock); Vue (`features/`, `shared/`, `stores/`).

---

## 6. STANDAR KODE LINTAS LAYANAN

- Controller tipis; business logic di service/action; validasi di lapisan masuk (Form Request / validasi CI4 / Pydantic).
- Otorisasi: Policy/Gate (Laravel) di setiap aksi; dependensi otorisasi klaim layanan (FastAPI dan CI4).
- Error seragam (§17.2), tanpa stack trace ke klien; log JSON terstruktur dengan `correlation_id`, tanpa rahasia, tanpa isi dokumen penuh, dengan masking data pribadi (NIK, email, telepon) yang terdeteksi.
- Konfigurasi dari variabel lingkungan, divalidasi saat start (gagal cepat).
- Migrasi terversi dengan `up` dan `down` yang diuji; pola expand-then-contract.
- Waktu UTC; tampilan Asia/Jakarta; angka berformat Indonesia pada UI (API membawa angka numerik).
- Analisis statis bersih: Pint, Larastan/PHPStan, ruff, mypy, ESLint, vue-tsc.
- Tidak ada kode mati atau placeholder yang menyamar selesai.

---

## 7. RENCANA FASE (M0 SAMPAI M8)

Kerjakan **berurutan**. Estimasi (hipotesis, bukan komitmen): M0 3 minggu, M1 3, M2 3, M3 4, M4 4, M5 4, M6 3, M7 2, M8 3. Rincian milestone: §33, §34. Kriteria penerimaan: §37.2 (ID AC dikutip di bawah).

### M0 — Fondasi dan Kontrak (baca §12 sampai §18, §33 sampai §35)

Tugas:
1. Inspeksi repositori (§3.1); buat struktur §15.2, `Makefile`, `AGENTS.md`, `docs/PROGRESS.md`.
2. Docker Compose seluruh layanan §5 dengan healthcheck; profil `dev`, `demo`, `ci`; jaringan `app` dan `data` terpisah, jaringan data tanpa rute keluar; `.env.example`; skrip `deploy/scripts` (install, healthcheck).
3. Kerangka tiga layanan dan SPA dengan endpoint `/health`.
4. **Verifikasi dan kunci versi** semua komponen pada lockfile dan `docs/VERSIONS.md` (PHP 8.4 dan kompatibilitas paket, Python, FastAPI, PostgreSQL, pgvector, Valkey, DuckDB, SeaweedFS, Caddy, ClamAV, Vue, ECharts). Kebijakan §12.6. Komponen yang belum diverifikasi di Dokumen Word wajib dicek di sini; jangan menebak versi.
5. Schema `core`, `connector`, `ai` dan tiga role dengan hak minimum (`deploy/postgres/init`); uji bahwa tiap role tidak dapat mengakses schema lain (AC-M0-03). Migrasi dasar dan uji `up`/`down` (AC-M0-08).
6. Kontrak OpenAPI spec-first: `core-public`, `core-internal`, `ai-internal`, `connector-internal`; stub untuk uji kontrak (AC-M0-04). Format error §17.2.
7. JWT antarlayanan prototipe (penerbit per layanan, kunci asimetris; pilih algoritma dan pustaka berdasarkan dukungan); uji valid, kedaluwarsa, `aud` salah, `jti` ulang (AC-M0-05).
8. **Uji konformitas object store** SeaweedFS lewat abstraksi S3: unggah, unduh, presigned URL, multipart, hapus (AC-M0-09). Bila gagal, uji Garage atau RustFS dan catat ADR.
9. **Uji pgvector:** ANN HNSW dengan filter `acl_principals` selektif pada data uji; ukur recall dan latensi dengan dan tanpa `hnsw.iterative_scan`, indeks parsial, dan ukuran kandidat; pilih mitigasi dan catat (AC-M0-06).
10. **Perbandingan leksikal:** FTS `simple` vs kandidat BM25 sejati (verifikasi lisensi, dukungan versi PostgreSQL, pemeliharaan, tokenisasi Indonesia); catat keputusan dan perhatian lisensi AGPL bila ada (AC-M0-07).
11. Kerangka golden dataset v0 (`eval/protocols` pedoman anotasi; format butir; pembagian dev dan test) sesuai §35.2; ukuran minimum ditinjau dan dicatat.
12. CI GitHub Actions + `make ci` lokal: lint, uji, kontrak, migrasi, gitleaks, Trivy, audit dependensi.

Bukti keluar: AC-M0-01 sampai AC-M0-09. Checkpoint: tidak ada.

### M1 — Identitas, Peran, Audit (§9, §16.3, §17.3, §29.2, §30.3)

Laravel: login, refresh berputar dengan deteksi reuse, logout, `/me`, `/auth/change-password`; rate limit (login 5/menit per IP dan akun) dan penguncian bertahap; kata sandi hash adaptif; kata sandi sementara dengan `must_change_password` (FR-034); CRUD pengguna, peran (`role_user`), unit kerja hierarkis (cegah siklus); Policy; pembentukan `principals` termasuk unit induk (fungsi tunggal teruji); `AuditRecorder`; `/audit-logs` (rentang waktu wajib, maks 90 hari); `/settings` (rahasia terenkripsi aplikasi, tidak tampil ulang); admin terakhir tidak dapat dinonaktifkan. Role DB aplikasi hanya `INSERT`/`SELECT` pada `audit_logs`. Seed peran dan admin awal (kata sandi sementara). Vue: login, ganti kata sandi, panel pengguna (API nyata), token di memori.
Bukti: AC-M1-01 sampai AC-M1-09, matriks otorisasi otomatis dasar. **Checkpoint G1:** kalibrasi estimasi dengan kecepatan aktual, catat pada ledger; bila M1 melebihi 4 minggu, lapor sebagai perhatian Owner (§12).

### M2 — Dokumen dan Izin (§19, §17.3, §17.4, §18.1, §18.3)

Laravel: unggah multipart tervalidasi (tipe, ukuran awal 50 MB, MIME dan magic bytes), **ClamAV fail-closed**, simpan ke object store dengan kunci acak, dokumen dan versi, izin (`role`, `unit`, `user`, `all`; `NULLS NOT DISTINCT`), aturan status dan versi berlaku (§19.2), URL unduhan bertanda tangan berumur pendek setelah Policy, idempotensi unggah, klien AI (ke **AI-stub**) dengan retry/backoff, endpoint callback status, sinkron ACL dengan **pencabutan sinkron**, rekonsiliasi harian (kerangka), dokumen tanpa aturan izin tidak dapat diaktifkan. Layar Vue dokumen (daftar, unggah, versi, izin, status ingestion, cabut). AI-stub: FastAPI minimal sesuai kontrak (tandai `STUB`).
Bukti: AC-M2-01 sampai AC-M2-07.

### M3 — Ingestion dan Indeks (§20, §21.5, §16.5)

FastAPI `ai-api` dan `ai-worker`: `POST /internal/v1/ingestions` (202, idempoten), tabel `ai.jobs` + worker `SKIP LOCKED` dengan retry dan `max_attempts`; parser PDF berteks (bandingkan pdfplumber dan pypdf pada sampel; catat; **bukan** PyMuPDF), DOCX, XLSX/CSV sebagai teks (header diulang); halaman tanpa teks → peringatan `needs_ocr`; chunking berbasis struktur (child ~350 token overlap ~50 sebagai nilai awal hipotesis, parent per bagian, `section_path`, halaman); embedding lewat `EmbeddingProvider` (+ Mock deterministik); `content_tsv`; `acl_principals`, `status`, `is_current`; satu transaksi per versi; callback status; penggantian versi atomik; **reindex** (FR-038); batas halaman/sel/waktu. Pilih model embedding sesuai protokol §21.5 pada golden dataset v0 dan **kunci dimensi** pada migrasi setelahnya; catat di ADR/Decision Register dengan identitas run. Gantikan AI-stub dengan layanan nyata.
Bukti: AC-M3-01 sampai AC-M3-09.

### M4 — Retrieval, Jawaban Bersitasi, Chat (§21, §22, §35.3)

FastAPI: retrieval hibrida (leksikal + dense, **filter ACL pada keduanya**, RRF dengan `k` default 60 diuji sensitivitasnya), reranker, sufficiency check dan abstain (ambang dikalibrasi pada dev split), generasi bersitasi dengan keluaran JSON terstruktur dan kontrak prompt §21.2 (versi prompt dicatat), verifikasi sitasi lapis 1, `LLMClient` nyata (adapter OpenAI-compatible; adapter native opsional; Mock untuk CI), `POST /internal/v1/ask`. Laravel: percakapan dan pesan, pembentukan `principals`, **verifikasi Policy tiap sitasi**, penyimpanan `model_info`, trace, `/messages/{id}/trace`, audit. Vue: chat, riwayat, sitasi yang dapat dibuka ke halaman, penampil sumber, umpan balik, status dan galat. **Evaluation harness** `python -m ekaa_ai.eval` (FR-035): konfigurasi, seed, versi dataset; kondisi C0 sampai C3; laporan baseline (Recall@k, MRR, nDCG, faithfulness, akurasi sitasi, abstain, latensi, biaya). Uji kebocoran lintas hak akses (matriks peran × dokumen, pencabutan, versi lama). Pemilihan reranker dan LLM sesuai §21.5 (kandidat, bobot ditetapkan sebelum run, konfirmasi sekali pada test split); catat dasar keputusan.
Bukti: AC-M4-01 sampai AC-M4-09. **Checkpoint G2:** baseline diukur; tetapkan ulang target dari data; kunci pilihan model pada Decision Register. Bila kebijakan data/anggaran LLM Owner belum ada, gunakan Mock atau model lokal dan lapor sebagai perhatian Owner (OA-03).

### M5 — Analitik Deterministik (§23, §24, §28.4)

Impor dataset CSV/XLSX (DuckDB; profil kolom; format angka dan tanggal Indonesia dengan `locale_config`; `DECIMAL` untuk ukuran desimal; impor atomik dengan laporan baris galat); kamus data; `metric_definitions` dengan `params_schema`; metrik `cagr`, `yoy`, `mom`, `moving_average`, `share`, `rank`, `aggregate`, `delta` sesuai kontrak §24.1 dan kasus tepi §24.2 (CAGR memakai **jumlah periode n**, bukan selisih tahun tertulis; tolak nilai awal ≤ 0, satuan berbeda; peringatan periode hilang); **SQL dari templat berparameter dengan identifier daftar-putih**; DuckDB read-only; metric planner (function calling; hanya skema, kamus, dan nilai unik dimensi terbatas, **tanpa baris mentah**; dapat dimatikan); validasi parameter terhadap skema dan kamus; klarifikasi bila ambigu; hasil dengan nilai, satuan, rumus, input, sumber, SQL yang dieksekusi, peringatan; spesifikasi grafik tervalidasi (§28.4); validator narasi (angka pada narasi harus cocok hasil hitung); izin dataset (Policy). Uji unit **termasuk uji properti** untuk setiap metrik (100% lulus). Vue: layar dataset, kamus data, grafik ECharts (+ tabel setara), ekspor PNG/CSV.
Bukti: AC-M5-01 sampai AC-M5-09.

### M6 — Router, Guardrails, Umpan Balik (§26, §30)

`IntentClassifier` dengan dua implementasi (LLM few-shot JSON; TF-IDF + regresi logistik pada dev split), pilih dengan F1 makro dan latensi; ambang keyakinan dari baseline; `clarification_required`; `route_hint` opsional yang tetap melewati pemeriksaan akses; UI menampilkan jalur dan memungkinkan mengubahnya. Guardrails: penyaringan pola injeksi pada konteks, validasi keluaran, masking data pribadi pada log. Rangkaian uji injeksi v1 (dokumen berisi instruksi berbahaya, pertanyaan adversarial, upaya eksfiltrasi lewat tautan/gambar) dengan laporan tingkat keberhasilan serangan (jangan klaim "aman"). Umpan balik dan layar tinjauan Knowledge Manager. gitleaks di CI.
Bukti: AC-M6-01 sampai AC-M6-06.

### M7 — Connector & Sync (§14, §17.5, §18.4, §29.3)

CodeIgniter 4.7: `SourceConnector` + konektor folder (volume hanya-baca), jadwal via CLI/cron, deteksi perubahan berbasis hash SHA-256, penyerahan ke `POST /internal/v1/connector/items` dengan `Idempotency-Key` turunan (`source_id`+`external_id`+`content_hash`), item hilang melalui endpoint `removed`, `sync_runs`, kredensial dienkripsi (Encryption bawaan), verifikasi JWT layanan. **CodeIgniter tidak menentukan izin**: Laravel menerapkan `default_acl` sumber. Uji kontrak dua arah di CI (Laravel-stub untuk sisi CI4). Layar Vue sumber data. Tulis catatan tinjauan beban pemeliharaan dua framework memakai indikator §14.3.
Bukti: AC-M7-01 sampai AC-M7-07. **Gerbang G3:** lanjut dengan CodeIgniter secara bawaan; **fallback C hanya atas persetujuan Owner** (lapor, jangan terapkan sendiri).

### M8 — Pengerasan dan Rilis MVP (§30.7, §32, §35, §37.3)

Jalankan seluruh jenis uji §35.1; laporan keamanan (matriks otorisasi, kebocoran, injeksi, berkas berbahaya termasuk EICAR dan bom dekompresi, JWT, dependensi, rahasia); laporan beban k6 (latensi p50/p95, throughput, biaya per pertanyaan, spesifikasi mesin); pencadangan (`pg_dump` + restic terenkripsi + object store + DuckDB) dan **uji restore** pada lingkungan bersih dengan waktu tercatat (FR-036); retensi dan penghapusan end-to-end (FR-037); header keamanan dan CSP ketat; kontainer non-root; runbook (pemasangan, rotasi kunci, backup, restore, pembaruan, rollback, insiden); README yang memungkinkan pemasangan ulang oleh orang lain; `docs/KNOWN_LIMITATIONS.md` jujur (OCR, text-to-SQL, TLS internal, dan lainnya belum ada); studi kasus dan demo pada korpus yang boleh dipublikasikan atau sintetis; ukur kebutuhan sumber daya nyata untuk dua profil (LLM eksternal + embedding lokal; seluruhnya lokal). Penetration test eksternal bukan tugas Anda; lapor sebagai prasyarat produksi (OA-08).
Bukti: AC-M8-01 sampai AC-M8-06. **Gerbang G4:** serahkan paket bukti kepada Owner; **rilis hanya atas persetujuan eksplisit Owner**.

---

## 8. SPESIFIKASI KRITIS YANG HARUS DIIMPLEMENTASI PERSIS

### 8.1 Retrieval Berfilter

Kedua pencarian pada `ai.chunks` memakai klausa `acl_principals && :principals AND status = 'active' AND is_current` (principals diteruskan sebagai parameter, **bukan** string yang disusun). RRF: skor chunk = jumlah dari `1 / (k + peringkat)` pada daftar tempat ia muncul. Reranker pada 20 sampai 50 kandidat. Nilai awal (hipotesis): 50 + 50 kandidat, `k=60`, 30 ke reranker, 8 ke generator. Semua disetel pada **dev split**, bukan test split.

### 8.2 Kontrak `POST /internal/v1/ask` dan Respons Pesan

Ikuti §17.4 dan §17.3: status domain `answered`, `no_evidence`, `clarification_required`, `out_of_scope`; `citations` dengan `document_id`, `version_id`, `chunk_id`, `page_start`, `page_end`, `section_path`, `excerpt` (≤ 300 karakter), `url`; `metric` (nilai, rumus, input, sumber), `table`, `chart_spec`; `trace_id`; `model_info` (model, versi prompt). Jawaban yang kehilangan semua sitasi setelah verifikasi menjadi `no_evidence`.

### 8.3 Kontrak Prompt Generasi

Instruksi sistem terpisah dari konteks; konteks ditandai data tidak tepercaya; jawab hanya dari konteks dalam Bahasa Indonesia; keluaran JSON (segmen jawaban dengan `chunk_id` pendukung dan penanda `sufficient`); tanpa alat berbahaya; tanpa HTML, skrip, gambar eksternal; template prompt di repositori dengan versi (hash) yang dicatat; temperature awal 0.

### 8.4 Laju dan Batas Awal (Hipotesis; Dapat Dikonfigurasi)

Login 5/menit; refresh 20/menit; unggah 10/menit; kirim pesan 10/menit per pengguna; `analytics/run` 30/menit; lainnya 60/menit. Batas unggah: dokumen 50 MB, dataset 100 MB. Timeout `ask` 60 detik; tanpa retry otomatis untuk pertanyaan pengguna.

### 8.5 Kegagalan

`ai-api` mati → `503 AI_UNAVAILABLE` terstruktur, endpoint non-AI tetap hidup, circuit breaker sederhana. Pemindai atau object store mati → unggah ditolak, tidak ada dokumen yatim. Worker gagal → retry, lalu `failed` dengan pesan ringkas tanpa isi dokumen, dapat diulang.

---

## 9. KEAMANAN (BUKAN TAHAP AKHIR)

Bangun bersama fitur: validasi masukan di backend; otorisasi di setiap endpoint dengan matriks uji otomatis (peran × endpoint × kepemilikan); rate limit; header keamanan (HSTS, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `frame-ancestors none`); CSP SPA tanpa `unsafe-inline` skrip dan tanpa sumber eksternal; satu asal lewat proxy (tanpa CORS); log tanpa rahasia; kredensial konektor dan kunci LLM terenkripsi; pencatatan prompt penuh **dimatikan** secara bawaan (hash dan metadata saja); data dikirim ke LLM seminimal mungkin dan tanpa identitas pengguna; parser berjalan terisolasi dengan batas waktu dan memori; model ancaman T-01 sampai T-15 (§30.2) masing-masing punya uji. Jangan mengirim data nyata ke layanan luar tanpa izin Owner; gunakan korpus publik atau sintetis.

---

## 10. PENGUJIAN, CI, DAN KUALITAS

### 10.1 Perintah Standar

`make up`, `make down`, `make migrate`, `make seed`, `make test`, `make ci`, `make eval`, `make backup`, `make restore`. Semua dapat dijalankan lewat Docker; dokumentasikan di README.

### 10.2 Jenis Uji dan Syarat Lulus (§35.1)

Unit (Pest, PHPUnit, pytest, Vitest) 100% lulus; integrasi dan API; kontrak OpenAPI; E2E Playwright pada alur kritis (login dan peran; unggah → indeks → tanya → sitasi; dataset → metrik → grafik); matriks otorisasi 0 pelanggaran; uji kebocoran 0; keamanan lain; beban k6; restore; pemasangan ulang; aksesibilitas (axe). Setiap bug mendapat uji regresi.

### 10.3 Evaluasi Ilmiah (Bahan Penelitian)

Golden dataset terversi di `eval/datasets` dengan pembagian dev/test dan pembekuan versi **sebelum** eksperimen; **data test tidak dipakai menyetel prompt atau parameter**. Catat versi dataset, hash commit, model dan penyedia dan tanggal, versi prompt, parameter, seed, dan keluaran mentah. Laporkan hasil negatif dan interval ketidakpastian (bootstrap). Jangan mengisi angka hasil bila eksperimen belum dijalankan. Bila LLM-judge dipakai, catat bias dan periksa sampel manual. Referensi ilmiah hanya yang terverifikasi.

---

## 11. DEFINITION OF DONE DAN TINJAUAN KODE

### 11.1 Definition of Done Global (§34.3)

Sebuah item selesai hanya jika: (1) kode di repositori dengan riwayat commit jelas; (2) berjalan di Docker Compose; (3) terintegrasi backend nyata (kecuali `STUB` yang ditandai); (4) uji otomatis relevan lulus di CI; (5) validasi, otorisasi, error, audit sesuai Dokumen Word; (6) migrasi `up`/`down` teruji; (7) OpenAPI dan dokumentasi diperbarui, ADR dicatat bila ada keputusan baru; (8) tidak ada rahasia, pemindaian lulus; (9) AC fase terpenuhi dengan **bukti**.

### 11.2 Daftar Periksa Tinjauan Kode Mandiri (Setiap PR/Perubahan Bermakna)

- Sesuai Dokumen Word dan invarian §4? Ada penyimpangan? (ADR)
- Batas framework dan kepemilikan schema terjaga? Tidak ada logika ganda?
- Ada uji (positif, negatif, kasus tepi, otorisasi)? Uji benar-benar dijalankan dan lulus?
- Input divalidasi? Otorisasi di backend? Error terstruktur? Tidak ada kebocoran informasi di pesan galat?
- Tidak ada rahasia, PII di log, atau SQL/identifier dari input tanpa daftar-putih?
- Migrasi aman (expand-then-contract) dan dapat di-rollback?
- Dependensi baru dibenarkan, berlisensi sesuai, versi dikunci?
- Dokumentasi, OpenAPI, ledger diperbarui?

### 11.3 Audit Akhir Fase

Petakan setiap FR dan AC fase ke bukti; daftar yang `DONE`, `STUB`, `BLOCKED`, `DEVIASI` (dengan ADR); jalankan ulang `make ci` dari keadaan bersih.

---

## 12. KONDISI BERHENTI DAN PERHATIAN OWNER

**Berhenti dan lapor** (jangan lanjut atau memutuskan sendiri) bila:

1. Ditemukan kebocoran lintas hak akses atau temuan keamanan kritis.
2. AC fase tidak dapat dipenuhi dan Anda tidak dapat menyusun pengecualian yang sah.
3. Keputusan melibatkan **biaya besar, lisensi berbayar, kewajiban hukum, privasi data, atau perubahan tujuan proyek**, termasuk: memilih penyedia LLM berbayar dan kebijakan data (OA-03), memasukkan komponen AGPL/GPL ke kode aplikasi (OA-07), perangkat keras LLM lokal (OA-04), penetration test (OA-08), klaim kepatuhan UU PDP (OA-06).
4. **Fallback C (menghapus CodeIgniter) atau rilis MVP (G4)**, yang hanya boleh diputuskan Owner.
5. Konflik antar bagian Dokumen Word yang tidak dapat diselesaikan secara aman dari teks.
6. Pemangkasan cakupan karena jadwal (G1) atau perubahan visi, fitur inti, atau teknologi final.

Pelaporan: jelaskan masalah, opsi, dampak, dan rekomendasi Anda, lalu tunggu instruksi. Untuk keputusan teknis kecil di dalam cakupan, putuskan sendiri, catat ADR, dan lanjutkan.

---

## 13. FORMAT LAPORAN

### 13.1 Laporan Akhir Fase (Tempatkan pada `docs/PROGRESS.md` dan Ringkas dalam Respons)

1. Fase dan tanggal; commit hash.
2. FR yang dikerjakan dan statusnya (`DONE` hanya dengan bukti).
3. Tabel AC fase: ID, status, jalur bukti.
4. Stub yang masih aktif; keterbatasan; deviasi dan ADR.
5. Hasil `make ci` (ringkas, nyata).
6. Risiko baru atau berubah (RISK-xx).
7. Item "Perhatian Owner" yang muncul.
8. Rencana fase berikutnya.

### 13.2 Respons Akhir (Setelah M8)

Ringkasan jujur: yang berfungsi (dengan bukti), yang belum (keterbatasan), hasil uji keamanan, beban, restore, baseline evaluasi (angka hanya dari laporan nyata), daftar perhatian Owner terbuka, dan langkah pemasangan. Jangan melebih-lebihkan.

---

## 14. LARANGAN

- Jangan membuat mockup, prototipe visual, atau frontend tanpa backend nyata sebagai hasil akhir.
- Jangan mengubah visi, menghapus fitur inti, atau mengganti teknologi final (Laravel 13, CodeIgniter 4, FastAPI, Vue 3, PostgreSQL/pgvector, DuckDB, Valkey, SeaweedFS-via-S3, Caddy) secara sepihak.
- Jangan memakai MinIO Community Edition, Redis, atau PyMuPDF di MVP.
- Jangan membangun text-to-SQL, OCR, SSO, konektor cloud, bot WhatsApp, anomali, atau peramalan pada MVP.
- Jangan membiarkan LLM menghasilkan angka akhir atau menulis SQL pada MVP.
- Jangan mengarang hasil uji, hasil eksperimen, status pengujian, atau referensi.
- Jangan menyatakan implementasi berhasil tanpa verifikasi.
- Jangan menambah dependensi tanpa kebutuhan dan catatan; jangan membuat duplikasi kode atau dokumentasi yang tidak perlu.
- Jangan menjalankan perintah destruktif di luar direktori repositori (mis. penghapusan global, `docker system prune` tanpa batas proyek) dan jangan memasukkan rahasia ke repositori atau log.
- Jangan mengabaikan masalah keamanan, termasuk saat "hampir selesai".
- Jangan membuat prompt fase, daftar periksa, atau dokumen spesifikasi baru sebagai pengganti Dokumen Word; dokumentasi teknis di repositori (§38) hanya untuk implementasi.

---

## 15. URUTAN MULAI (LAKUKAN SEKARANG)

1. Baca prompt ini sampai selesai.
2. Temukan dan ekstrak Dokumen Word (§2); baca seluruhnya; simpan ke `docs/spec/`.
3. Inspeksi repositori (§3.1); laporkan temuan.
4. Buat `AGENTS.md` dan `docs/PROGRESS.md`.
5. Mulai **M0** (§7). Jalankan siklus §3.3. Lanjut ke M1 hanya setelah bukti AC-M0 lengkap.
6. Lanjutkan berurutan sampai M8 sesuai aturan berhenti §12; kirim paket bukti G4 kepada Owner.

Bekerjalah sebagai profesional yang bertanggung jawab atas hasil: lebih baik melaporkan keterbatasan dengan jujur daripada mengklaim keberhasilan tanpa bukti.
