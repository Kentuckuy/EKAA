# FINAL SECURITY SPECIFICATION — EKAA

| Atribut | Nilai |
|---|---|
| Versi dokumen | 1.0-draft (Gelombang 2) |
| Tanggal pembaruan | 8 November 2026 |
| Status dokumen | **READY FOR OWNER APPROVAL** |
| Dokumen terkait | `FINAL_SYSTEM_ARCHITECTURE.md`, `FINAL_DATABASE_DESIGN.md`, `FINAL_API_SPECIFICATION.md`, `FINAL_REQUIREMENT_SPECIFICATION.md` (NFR-SEC, NFR-PRV, NFR-AUD) |

**Batas dokumen.** Dokumen ini adalah spesifikasi rancangan. Tidak ada kontrol yang dinyatakan sudah diterapkan atau teruji. Bagian UU PDP adalah pemetaan teknis, **bukan nasihat hukum**, dan harus ditinjau oleh pihak yang berkompeten sebelum dipakai sebagai klaim kepatuhan.

## 1. Single-Tenant Isolation

| Kontrol | Spesifikasi |
|---|---|
| Satu instalasi satu organisasi | Database, MinIO, Redis, dan kunci terpisah per instalasi; tidak ada data organisasi lain di sistem yang sama |
| Isolasi antarlayanan | Schema dan role database terpisah (DD-01); bucket MinIO dengan kredensial per layanan dan hak minimum; jaringan Docker internal |
| Isolasi per pengguna | Percakapan hanya terbaca pemiliknya; dokumen dan dataset melalui izin |
| Pemisahan lingkungan | Pengembangan, uji, dan produksi memakai kredensial dan data terpisah; data produksi tidak dipakai di pengembangan tanpa anonimisasi |

## 2. Autentikasi

- Laravel adalah satu-satunya pemegang kredensial pengguna (DEC-006). CodeIgniter dan FastAPI tidak menerima kata sandi pengguna.
- Kata sandi di-hash dengan Argon2id (parameter ditetapkan dari pengukuran perangkat keras deployment). Kebijakan panjang minimum dan pemeriksaan kata sandi umum.
- Token akses berumur pendek; refresh token disimpan sebagai hash dan dapat dicabut; rotasi refresh token pada setiap penukaran.
- Pembatasan percobaan login dan penguncian sementara; pesan galat tidak membedakan "pengguna tidak ada" dan "kata sandi salah".
- MFA (TOTP): Phase 2. SSO (OIDC): Phase 3 (DEC-006).
- Autentikasi antarlayanan: JWT layanan (DEC-030) berumur di bawah 5 menit, `aud` spesifik, `jti` unik.

## 3. Otorisasi (RBAC, ABAC, Dokumen, Baris)

**RBAC.** Lima peran (DEC-019). Izin berbentuk kode yang dipetakan ke peran di `core.permissions`. Pemeriksaan melalui Policy/Gate Laravel pada setiap endpoint; tidak ada pemeriksaan hanya di frontend.

**ABAC (terbatas).** Atribut yang dipakai: unit kerja pengguna, status dokumen (`active`/`revoked`), dan versi berlaku. Aturan lebih kompleks (misalnya tingkat klasifikasi) ditunda dan tidak boleh ditambahkan tanpa persetujuan.

**Otorisasi dokumen.**

1. Sumber kebenaran: `core.document_permissions`.
2. Retrieval: filter `acl_principals && principals` pada chunk, ditambah `status='active'` dan `is_current` (DEC-025).
3. Pencabutan izin: diproses **sinkron**; Laravel tidak mengembalikan sukses sebelum chunk di schema `ai` diperbarui. Bila sinkronisasi gagal, aksi pencabutan dinyatakan gagal dan dicoba ulang; sementara itu dokumen ditandai `revoked` di sisi Laravel agar sitasinya dibuang pada verifikasi lapis kedua.
4. Verifikasi lapis kedua: Laravel memeriksa Policy untuk tiap `document_id` pada sitasi sebelum respons dikirim. Kegagalan dicatat sebagai insiden keamanan.
5. Respons `404` (bukan `403`) untuk sumber daya yang keberadaannya tidak boleh diketahui pengguna.
6. Cache semantik (Phase 2) **wajib** menyertakan himpunan `principals` pada kunci cache agar jawaban tidak bocor lintas hak akses.

**Row-level (data tabel).** MVP: izin tingkat dataset. Row-level security: Phase 2 (FR-046); sebelum itu, dataset yang membutuhkan pembatasan baris tidak boleh diunggah ke pengguna yang tidak berhak penuh.

## 4. Enkripsi

| Kebutuhan | Spesifikasi |
|---|---|
| Saat transit (eksternal) | TLS pada reverse proxy; HSTS; protokol dan cipher modern mengikuti rekomendasi terkini saat deployment |
| Saat transit (internal) | Jaringan Docker internal terisolasi pada MVP. mTLS atau TLS internal: Phase 2 dan wajib bila layanan tersebar di beberapa host |
| Saat tersimpan | Enkripsi volume/disk host untuk PostgreSQL, MinIO, dan DuckDB; enkripsi sisi-server MinIO diaktifkan bila didukung konfigurasi; kredensial konektor dienkripsi pada tingkat aplikasi dengan kunci di luar basis data |
| Cadangan | Dienkripsi sebelum keluar dari host |

Kunci enkripsi tidak disimpan di database yang sama dengan data terenkripsi.

## 5. Validasi Berkas

Mengikuti arsitektur bagian 14, ditambah ketentuan berikut:

1. Daftar tipe diizinkan (MVP: PDF berteks, DOCX, XLSX, CSV); tipe lain ditolak `415`.
2. Pemeriksaan MIME dan *magic bytes* di server; ekstensi tidak dipercaya.
3. Batas ukuran per berkas dan per permintaan; batas jumlah halaman, sel, dan baris untuk mencegah *decompression bomb* dan pemrosesan berlebih.
4. Nama berkas dinormalisasi; berkas disimpan dengan kunci acak, bukan nama asli.
5. Parser dijalankan terisolasi dengan batas waktu dan memori; makro dan tautan eksternal tidak dieksekusi atau diikuti.
6. Pemindaian malware sebelum ingestion (komponen diverifikasi pada M0).
7. Berkas Excel/CSV: sel yang diawali rumus perlu dinetralkan pada ekspor agar tidak menjadi injeksi rumus di aplikasi spreadsheet pengguna.

## 6. Pencegahan Prompt Injection

Isi dokumen dan hasil retrieval **bukan instruksi**. Kontrol berlapis (tidak ada satu pun yang cukup sendiri):

| Lapisan | Kontrol |
|---|---|
| Struktur prompt | Instruksi sistem dan konteks dokumen dipisahkan jelas; konteks diberi penanda sebagai data tidak tepercaya |
| Hak minimum alat | LLM **tidak** memegang alat yang dapat mengubah data, mengirim data keluar, atau membaca sumber di luar hasil retrieval yang sudah difilter |
| Penyaringan masukan | Pola injeksi umum pada isi dokumen dan pertanyaan dideteksi dan ditandai (FR-032); deteksi bersifat heuristik, bukan jaminan |
| Validasi keluaran | Jawaban wajib bersitasi pada chunk yang diambil; sitasi yang tidak merujuk chunk hasil retrieval dibuang |
| Pembatasan keluaran | Tidak merender HTML/skrip dari keluaran LLM; tautan hanya ke sumber internal; gambar eksternal tidak dimuat (mencegah eksfiltrasi lewat URL) |
| Akses data | Retrieval hanya dengan `principals` pengguna sehingga injeksi tidak dapat memperluas akses |
| Pengujian | Rangkaian uji injeksi tersusun (dokumen berisi instruksi jahat, permintaan membocorkan prompt sistem, pengalihan alat) dijalankan pada CI; hasil dilaporkan apa adanya |

Klaim "terlindungi dari prompt injection" **tidak boleh** dibuat tanpa hasil pengujian; yang dapat dinyatakan hanya tingkat keberhasilan serangan pada rangkaian uji yang terdokumentasi.

## 7. Pembatasan SQL (Read-Only)

- **MVP:** tidak ada SQL yang ditulis LLM. Eksekusi hanya melalui fungsi metrik teruji dengan parameter tervalidasi skema.
- **Phase 2 (text-to-SQL, FR-040):**
  1. Parser SQL menolak apa pun selain satu pernyataan `SELECT`; tidak ada multi-statement, DDL, DML, atau pemanggilan fungsi file/jaringan.
  2. DuckDB dibuka dalam mode read-only; ekstensi dan akses sistem berkas/jaringan dinonaktifkan; tabel yang dapat diakses dibatasi pada `allowed_dataset_ids`.
  3. Batas waktu dan batas memori per kueri; batas jumlah baris hasil.
  4. Koreksi diri dibatasi jumlah percobaan.
  5. Seluruh SQL yang dieksekusi dicatat (audit) dan ditampilkan kepada pengguna.
- Kasus uji: seluruh upaya tulis/DDL dan akses file harus ditolak (NFR-SEC-003).

## 8. Manajemen Rahasia

- Tidak ada rahasia di repositori; pemindaian rahasia pada pipeline CI (FR-033).
- MVP: variabel lingkungan atau Docker secrets dengan izin berkas ketat; `.env` tidak disertakan dalam image.
- Kunci tanda-tangan JWT per layanan dan kunci enkripsi aplikasi dipisahkan; rotasi terjadwal dan prosedur rotasi darurat didokumentasikan.
- Kunci API LLM dikonfigurasi per instalasi dan tidak ditampilkan ulang di UI atau log.
- Phase 2/3: penyimpan rahasia khusus (misalnya Vault) bila diperlukan klien.

## 9. Audit Logging

| Aspek | Spesifikasi |
|---|---|
| Peristiwa wajib | Login (berhasil dan gagal), pertanyaan, dokumen/dataset yang disitasi, perubahan izin dan peran, unggah, pencabutan, perubahan pengaturan, ekspor, aksi administratif, kegagalan verifikasi sitasi |
| Isi | Waktu, pelaku, aksi, sumber daya, hasil, IP, `correlation_id`, rincian terbatas |
| Perlindungan isi | Teks pertanyaan dan jawaban dicatat sesuai kebijakan konfigurasi; data pribadi di-*mask* pada log (bagian 12) |
| Integritas | Role aplikasi tanpa `UPDATE`/`DELETE` pada `audit_logs`; rantai hash (hash chain) untuk deteksi pengubahan: Phase 2 |
| Akses | Hanya `org_admin` (dan `auditor` Phase 2); akses ke log itu sendiri dicatat |

## 10. Retensi, Backup, dan Pemulihan

- Retensi mengikuti `FINAL_DATABASE_DESIGN.md` bagian 9 dan dapat dikonfigurasi; pekerjaan retensi dicatat di audit.
- Backup: PostgreSQL (dump atau arsip WAL), MinIO (versioning atau replikasi), dan volume DuckDB; terenkripsi; salinan di luar host.
- RPO dan RTO ditetapkan Owner dan klien; **uji pemulihan (restore) terjadwal** adalah syarat NFR-AVL-001, karena backup yang tidak pernah diuji tidak dihitung sebagai kontrol.
- Penghapusan atas permintaan pemilik data harus menjangkau chunk, vektor, berkas, dan cadangan sesuai jadwal rotasi; keterbatasan penghapusan pada cadangan didokumentasikan.

## 11. Rate Limiting

Batas pada tiga tingkat: (1) reverse proxy per IP, (2) Laravel per pengguna dan per endpoint (nilai awal pada `FINAL_API_SPECIFICATION.md`), (3) FastAPI per layanan (konkurensi dan antrean) untuk melindungi biaya dan sumber daya LLM. Nilai awal adalah hipotesis yang disesuaikan setelah pengukuran. Pertanyaan ke LLM juga dibatasi per pengguna untuk mengendalikan biaya.

## 12. Masking Data Pribadi

| Aspek | Spesifikasi |
|---|---|
| Cakupan MVP | Pola data pribadi umum (misalnya alamat email, nomor telepon, nomor identitas kependudukan, NPWP) dideteksi dan di-*mask* **pada log dan jejak diagnostik** |
| Konten dokumen | Tidak di-*mask* otomatis pada jalur jawaban pada MVP, karena dapat merusak kebenaran jawaban; kebijakan per dokumen (penandaan sensitif dan pembatasan akses) dikelola Knowledge Manager |
| Ke penyedia LLM eksternal | Opsi konfigurasi: *mask* sebelum dikirim; opsi LLM lokal untuk klien yang melarang data keluar |
| Keterbatasan | Deteksi berbasis pola tidak sempurna dan menghasilkan negatif/positif palsu; angka kinerjanya diukur dan dilaporkan, bukan diasumsikan |

## 13. Pemetaan UU PDP (Teknis, Bukan Nasihat Hukum)

Acuan: Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi. **Nomor pasal, peraturan pelaksana, dan tenggat tertentu belum diverifikasi dalam dokumen ini** dan harus diverifikasi pada tinjauan hukum sebelum klaim kepatuhan dibuat.

| Prinsip | Dukungan teknis dalam EKAA | Status |
|---|---|---|
| Pembatasan tujuan dan minimisasi | Hanya data yang diunggah organisasi; retensi dikonfigurasi; masking pada log | Dirancang |
| Keamanan data | Enkripsi, RBAC, audit, isolasi single-tenant | Dirancang |
| Hak subjek data (akses, koreksi, penghapusan) | Pencarian dan penghapusan dokumen, percakapan, dan log atas permintaan; keterbatasan pada cadangan | Dirancang sebagian; prosedur operasional perlu disusun klien |
| Pemberitahuan insiden | Audit dan deteksi anomali menyediakan bahan; prosedur insiden dan tenggat pemberitahuan **diverifikasi pada tinjauan hukum** | Prosedur belum disusun |
| Transfer data lintas negara | Opsi LLM lokal; bila LLM eksternal dipakai, keputusan dan dasar hukum ada pada organisasi | Konfigurasi klien |
| Peran pengendali dan prosesor | Organisasi klien sebagai pengendali; peran pengembang EKAA ditentukan secara kontraktual | Di luar lingkup teknis |

## 14. Threat Model

Metode: STRIDE untuk komponen dan daftar risiko khusus LLM mengacu pada kategori OWASP untuk aplikasi LLM serta OWASP untuk aplikasi web/API. Nomor edisi dan penomoran OWASP **diverifikasi pada saat tinjauan keamanan**; dokumen ini memakai nama kategori.

| ID | Ancaman | Aset | Kategori | Mitigasi | Verifikasi |
|---|---|---|---|---|---|
| T-01 | Kebocoran dokumen lintas hak akses lewat retrieval | Dokumen | Information disclosure | Filter ACL pada retrieval; verifikasi sitasi di Laravel; pencabutan sinkron; kunci cache menyertakan principals | Uji matriks peran × dokumen; target kebocoran nol |
| T-02 | Prompt injection dari isi dokumen | Integritas jawaban, data | Tampering / LLM | Bagian 6 | Rangkaian uji injeksi |
| T-03 | Eksfiltrasi lewat keluaran LLM (URL, markdown gambar) | Data | Information disclosure | Tidak memuat sumber eksternal; sanitasi keluaran | Uji render |
| T-04 | SQL berbahaya dari LLM (Phase 2) | Data, integritas | Tampering | Bagian 7 | Uji penolakan tulis/DDL/file |
| T-05 | Pencurian atau penebakan kredensial | Akun | Spoofing | Argon2id, rate limit, penguncian, MFA Phase 2 | Uji autentikasi |
| T-06 | Pemalsuan atau replay JWT antarlayanan | Antarlayanan | Spoofing | Kunci asimetris, `aud`, `jti`, umur pendek | Uji kontrak keamanan |
| T-07 | Berkas berbahaya (malware, bom dekompresi) | Server, pengguna | Denial of service / Tampering | Bagian 5 | Uji berkas uji berbahaya |
| T-08 | Akses langsung ke layanan internal atau database dari luar | Semua | Elevation | Jaringan internal, hanya proxy terbuka, firewall | Pemindaian port |
| T-09 | Penyalahgunaan biaya atau DoS lewat pertanyaan berulang | Biaya, ketersediaan | Denial of service | Rate limit berlapis, batas konkurensi | Uji beban |
| T-10 | Perubahan atau penghapusan jejak audit | Audit | Repudiation | Role tanpa UPDATE/DELETE; hash chain Phase 2 | Uji hak database |
| T-11 | Kebocoran rahasia (repositori, log, image) | Kunci | Information disclosure | Bagian 8; pemindaian rahasia | Pemindaian CI |
| T-12 | Data pribadi pada log atau jejak diagnostik | Privasi | Information disclosure | Masking pada log; retensi pendek | Uji pola |
| T-13 | Konektor menyerahkan berkas dengan izin keliru | Dokumen | Elevation | ACL ditetapkan Laravel dari `default_acl`; konektor tidak menentukan izin | Uji kontrak |
| T-14 | Jawaban salah dipercaya sebagai fakta (halusinasi, angka salah) | Keputusan pengguna | Integritas | Sitasi wajib, abstain, metrik deterministik, tampilkan rumus dan sumber | Evaluasi RAG dan metrik |
| T-15 | Ketergantungan rantai pasok (paket PHP, Python, JS, image) | Semua | Tampering | Lockfile, pemindaian kerentanan dependensi, versi dikunci | Pemindaian CI |
| T-16 | Pemulihan gagal saat insiden | Ketersediaan | DoS | Uji restore terjadwal | Laporan uji restore |
| T-17 | Penyedia LLM eksternal menerima data sensitif | Privasi | Information disclosure | Opsi LLM lokal, masking, kontrak | Konfigurasi dan tinjauan hukum |

## 15. Pengujian Keamanan

| Jenis | Cakupan |
|---|---|
| Otorisasi | Matriks peran × sumber daya × aksi (otomatis di CI) |
| Kebocoran lintas hak akses | Skenario pencabutan izin, perubahan versi, dokumen dicabut, cache |
| Prompt injection | Rangkaian uji terdokumentasi (bagian 6) |
| SQL | Uji penolakan (Phase 2) |
| Berkas | Berkas uji berbahaya |
| Kontrak antarlayanan | Token kedaluwarsa, `aud` salah, replay |
| Dependensi dan rahasia | Pemindaian otomatis pada CI |
| Penetration test | Direncanakan sebelum rilis produksi; pelaksana dan cakupan ditetapkan Owner |

## 16. Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0-draft | 2026-11-08 | Penyusunan awal Gelombang 2 |
