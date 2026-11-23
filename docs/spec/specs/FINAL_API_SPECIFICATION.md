# FINAL API SPECIFICATION — EKAA

| Atribut | Nilai |
|---|---|
| Versi dokumen | 1.0-draft (Gelombang 2) |
| Tanggal pembaruan | 8 November 2026 |
| Status dokumen | **READY FOR OWNER APPROVAL** |
| Dokumen terkait | `FINAL_SYSTEM_ARCHITECTURE.md`, `FINAL_DATABASE_DESIGN.md`, `FINAL_SECURITY_SPECIFICATION.md`, `FINAL_REQUIREMENT_SPECIFICATION.md` |

Dokumen ini adalah **kontrak rancangan**. Berkas OpenAPI (YAML) per layanan dihasilkan pada M0 dan menjadi sumber kebenaran teknis; bila berbeda, OpenAPI yang telah disetujui mengalahkan dokumen ini dan perbedaan dicatat di Decision Register.

## 1. Tiga Permukaan API

| Permukaan | Prefix | Pemanggil | Penyedia | Autentikasi |
|---|---|---|---|---|
| API publik | `/api/v1` | Browser (SPA), klien REST | Laravel | Token akses pengguna |
| API internal AI | `/internal/v1` (host `ai-api`) | Laravel | FastAPI | JWT layanan |
| API internal Connector | `/internal/v1` (host `connector`) | Laravel | CodeIgniter | JWT layanan |
| API internal Core | `/internal/v1` (host `app`) | CodeIgniter, FastAPI (callback) | Laravel | JWT layanan |

API internal tidak terpublikasi di reverse proxy. Hanya `/api/v1` yang dapat dijangkau dari luar.

## 2. Konvensi Umum

- Format: JSON (`application/json`); unggah berkas: `multipart/form-data`.
- Pengenal sumber daya: UUID.
- Header wajib: `X-Correlation-Id` (dibuat oleh Laravel bila tidak ada, diteruskan ke seluruh layanan dan dicatat di audit).
- Operasi tulis internal wajib `Idempotency-Key`.
- Paginasi: `?page=` dan `?per_page=` (maksimum 100), atau kursor untuk daftar besar.
- Waktu: ISO 8601 UTC.

**Struktur error (seragam di seluruh layanan):**

```json
{
  "error": {
    "code": "FORBIDDEN",
    "message": "Anda tidak memiliki izin untuk aksi ini.",
    "details": [],
    "correlation_id": "c1f3..."
  }
}
```

| HTTP | Kode | Arti |
|---|---|---|
| 400 | `BAD_REQUEST` | Permintaan tidak dapat diproses |
| 401 | `UNAUTHENTICATED` | Token hilang, tidak valid, atau kedaluwarsa |
| 403 | `FORBIDDEN` | Tidak berizin |
| 404 | `NOT_FOUND` | Tidak ada atau tidak boleh diketahui keberadaannya |
| 409 | `CONFLICT` | Konflik status atau idempotency |
| 413 | `PAYLOAD_TOO_LARGE` | Berkas melebihi batas |
| 415 | `UNSUPPORTED_MEDIA_TYPE` | Tipe berkas tidak diizinkan |
| 422 | `VALIDATION_FAILED` | Validasi gagal; `details` berisi per-field |
| 429 | `RATE_LIMITED` | Melebihi batas; header `Retry-After` |
| 502 | `UPSTREAM_ERROR` | Layanan internal gagal |
| 503 | `AI_UNAVAILABLE` | Layanan AI tidak tersedia |
| 504 | `UPSTREAM_TIMEOUT` | Batas waktu terlampaui |

Kode khusus domain: `NO_EVIDENCE` (bukan error HTTP; dikembalikan sebagai respons 200 dengan `status: "no_evidence"`), `OUT_OF_SCOPE`, `CLARIFICATION_REQUIRED`, `METRIC_INVALID_PARAMS`, `DATASET_NOT_ALLOWED`, `INGESTION_FAILED`.

**Batas laju (rate limit):** angka di bawah adalah **nilai awal hipotesis** yang ditinjau setelah pengukuran penggunaan nyata.

## 3. API Publik Laravel (`/api/v1`)

### 3.1 Autentikasi dan akun

| Method | Endpoint | Tujuan | Autentikasi | Otorisasi | Validasi utama | Rate limit awal |
|---|---|---|---|---|---|---|
| POST | `/auth/login` | Masuk | Tidak | — | email format valid, password wajib | 5/menit per IP dan akun |
| POST | `/auth/refresh` | Tukar refresh token | Refresh token | Pemilik token | token wajib | 20/menit |
| POST | `/auth/logout` | Cabut token | Ya | Pemilik | — | 20/menit |
| GET | `/me` | Profil, peran, unit | Ya | Pengguna sendiri | — | 120/menit |

### 3.2 Pengguna, peran, unit (FR-002)

| Method | Endpoint | Tujuan | Otorisasi | Validasi utama |
|---|---|---|---|---|
| GET | `/users` | Daftar pengguna | `org_admin` | paginasi |
| POST | `/users` | Buat pengguna | `org_admin` | email unik, nama wajib, peran valid |
| PATCH | `/users/{id}` | Ubah pengguna | `org_admin` | field yang diizinkan saja |
| POST | `/users/{id}/deactivate` | Nonaktifkan | `org_admin` | tidak boleh menonaktifkan admin terakhir |
| PUT | `/users/{id}/roles` | Tetapkan peran | `org_admin` | peran dikenal |
| GET/POST/PATCH | `/org-units`, `/org-units/{id}` | Kelola unit kerja | `org_admin` | tanpa siklus |

Autentikasi: Ya (token akses). Rate limit awal untuk seluruh grup: 60/menit per pengguna.

### 3.3 Dokumen (FR-010 s.d. FR-012)

| Method | Endpoint | Tujuan | Otorisasi | Validasi utama |
|---|---|---|---|---|
| GET | `/documents` | Daftar dokumen yang dapat diakses | Terautentikasi; hasil difilter Policy | paginasi, filter status |
| POST | `/documents` | Unggah dokumen baru | `knowledge_manager` | tipe, ukuran, MIME server-side, judul |
| GET | `/documents/{id}` | Detail dan versi | Policy `view` | — |
| POST | `/documents/{id}/versions` | Versi baru | `knowledge_manager` | tipe dan ukuran |
| PATCH | `/documents/{id}` | Ubah metadata dan status (berlaku/dicabut) | `knowledge_manager` | status sah |
| PUT | `/documents/{id}/permissions` | Tetapkan izin | `knowledge_manager` | principal valid; minimal satu aturan |
| GET | `/documents/{id}/versions/{v}/download` | URL unduhan bertanda tangan berumur pendek | Policy `view` | — |
| GET | `/documents/{id}/versions/{v}/status` | Status ingestion | Policy `view` | — |
| DELETE | `/documents/{id}` | Hapus lunak | `knowledge_manager` | — |

Rate limit awal: unggah 10/menit per pengguna; lainnya 60/menit.

Contoh `POST /documents` (multipart): `file` (wajib), `title` (wajib), `effective_date` (opsional), `permissions` (opsional, JSON).

Respons `202 Accepted`:

```json
{
  "data": {
    "id": "0192...",
    "version_id": "0192...",
    "status": "pending",
    "ingestion": { "status": "queued" }
  }
}
```

### 3.4 Dataset dan analitik (FR-020 s.d. FR-025)

| Method | Endpoint | Tujuan | Otorisasi | Validasi utama |
|---|---|---|---|---|
| GET | `/datasets` | Daftar dataset yang dapat diakses | Policy | paginasi |
| POST | `/datasets` | Impor CSV/XLSX | `data_analyst` | tipe, ukuran, nama unik |
| GET | `/datasets/{id}` | Detail, profil kolom | Policy | — |
| PUT | `/datasets/{id}/columns` | Kamus data | `data_analyst` | kolom ada pada dataset |
| PUT | `/datasets/{id}/permissions` | Izin dataset | `data_analyst` | principal valid |
| GET | `/metrics` | Pustaka metrik yang tersedia | Terautentikasi | — |
| POST | `/analytics/run` | Jalankan metrik langsung (tanpa bahasa alami) | Policy dataset | `metric_code` dikenal; `params` sesuai `params_schema` |

Contoh `POST /analytics/run`:

```json
{
  "dataset_id": "0192...",
  "metric_code": "cagr",
  "params": { "value_column": "realisasi", "period_column": "tahun", "start": 2020, "end": 2024 }
}
```

Respons:

```json
{
  "data": {
    "value": 0.0731,
    "unit": "ratio",
    "formula": "CAGR = (V_akhir / V_awal)^(1/n) - 1, n = 4 periode",
    "inputs": { "v_awal": 1200, "v_akhir": 1593, "n": 4 },
    "source": { "dataset_id": "0192...", "rows_used": 5 },
    "warnings": []
  }
}
```

(Nilai di atas hanyalah ilustrasi format, bukan hasil eksekusi.) Rate limit awal: 30/menit per pengguna.

### 3.5 Percakapan (FR-017, FR-018, FR-030)

| Method | Endpoint | Tujuan | Otorisasi | Validasi utama | Rate limit awal |
|---|---|---|---|---|---|
| GET | `/conversations` | Daftar percakapan milik sendiri | Pemilik | paginasi | 60/menit |
| POST | `/conversations` | Buat percakapan | Terautentikasi | — | 30/menit |
| GET | `/conversations/{id}` | Isi percakapan | Pemilik | — | 60/menit |
| POST | `/conversations/{id}/messages` | Kirim pertanyaan | Pemilik | `content` 1–4.000 karakter | 10/menit per pengguna |
| DELETE | `/conversations/{id}` | Hapus lunak | Pemilik | — | 30/menit |

Respons `POST .../messages` (`200 OK`), ketiga bentuk hasil:

```json
{
  "data": {
    "message_id": "0192...",
    "route": "document",
    "status": "answered",
    "answer": "…",
    "citations": [
      { "document_id": "0192...", "version_id": "0192...", "title": "…",
        "section": "Pasal 5 ayat 2", "page_start": 4, "page_end": 4,
        "excerpt": "…", "url": "/api/v1/documents/0192.../versions/0192.../download" }
    ],
    "trace_id": "t-..."
  }
}
```

`status` bernilai `answered`, `no_evidence`, `clarification_required`, atau `out_of_scope`. Untuk `route: "analytics"`, respons memuat `metric` (nilai, rumus, input, sumber), `table`, dan `chart_spec`. Untuk pertanyaan yang memicu abstain, `answer` berisi pernyataan bahwa bukti tidak ditemukan dan `citations` kosong.

### 3.6 Umpan balik, audit, pengaturan, konektor

| Method | Endpoint | Tujuan | Otorisasi | Validasi utama |
|---|---|---|---|---|
| POST | `/messages/{id}/feedback` | Beri penilaian | Pemilik percakapan | `rating` ∈ {correct, incorrect} |
| GET | `/feedback` | Tinjau umpan balik | `knowledge_manager` | paginasi |
| GET | `/audit-logs` | Lihat log | `org_admin` (dan `auditor` Phase 2) | rentang waktu wajib, maksimum 90 hari per kueri |
| GET/PATCH | `/settings` | Pengaturan sistem | `org_admin` | kunci dikenal; rahasia tidak dikembalikan |
| GET/POST/PATCH | `/data-sources`, `/data-sources/{id}` | Kelola sumber data | `knowledge_manager` | tipe sah |
| POST | `/data-sources/{id}/sync` | Picu sinkronisasi | `knowledge_manager` | sumber aktif |
| GET | `/data-sources/{id}/sync-runs` | Riwayat sinkronisasi | `knowledge_manager` | — |
| GET | `/health` | Kesehatan dasar | Tidak (tanpa detail internal) | — |

Rate limit awal: 60/menit per pengguna, kecuali `/health` (dibatasi di proxy).

## 4. Kontrak Internal: Laravel → FastAPI (`/internal/v1`)

Autentikasi: JWT layanan (bagian 7). Otorisasi: klaim `aud=ai`, `iss=core`. Validasi: skema Pydantic; ukuran badan dibatasi.

| Method | Endpoint | Tujuan | Validasi utama |
|---|---|---|---|
| POST | `/ingestions` | Mulai ingestion versi dokumen | `document_id`, `version_id`, `storage_key`, `acl_principals`, `status`; `Idempotency-Key` wajib; balas 202 |
| GET | `/ingestions/{id}` | Status | — |
| PUT | `/documents/{id}/acl` | Ganti `acl_principals` seluruh chunk dokumen | Disinkron; balas 200 hanya setelah komit |
| PUT | `/documents/{id}/status` | Tandai `active` atau `revoked`, dan `is_current` | Disinkron |
| DELETE | `/documents/{id}` | Hapus chunk dokumen | Idempoten |
| POST | `/datasets` | Impor dataset ke DuckDB | `dataset_id`, `storage_key`; balas 202 |
| PUT | `/datasets/{id}/columns` | Kirim kamus data | skema kolom |
| POST | `/ask` | Pertanyaan: router, retrieval atau metrik, generasi | lihat di bawah |
| POST | `/analytics/run` | Jalankan metrik langsung | `metric_code`, `params` |
| GET | `/health` | Kesehatan | — |

**`POST /internal/v1/ask`**

Permintaan:

```json
{
  "query": "Berapa CAGR realisasi anggaran 2020-2024 dan apa targetnya di dokumen perencanaan?",
  "conversation_context": [{ "role": "user", "content": "…" }],
  "access": {
    "principals": ["all", "role:0192...", "unit:0192...", "user:0192..."],
    "allowed_dataset_ids": ["0192..."]
  },
  "options": { "top_k": 10, "language": "id" }
}
```

Respons:

```json
{
  "route": "document",
  "route_confidence": 0.0,
  "status": "answered",
  "answer": "…",
  "citations": [{ "document_id": "…", "version_id": "…", "chunk_id": "…",
                  "page_start": 4, "page_end": 4, "section_path": "…", "excerpt": "…" }],
  "metric": null,
  "chart_spec": null,
  "trace_id": "t-...",
  "model_info": { "llm": "…", "prompt_version": "…" }
}
```

(`route_confidence` bernilai nol di atas hanya sebagai placeholder format.) Kontrak: `principals` dan `allowed_dataset_ids` berasal dari Laravel dan **tidak pernah** dari klien. Bila `principals` kosong, FastAPI mengembalikan nol hasil, bukan seluruh dokumen.

Rate limit: dikendalikan Laravel (batas pada endpoint publik); FastAPI menambah batas konkurensi per layanan.

## 5. Kontrak Internal: Laravel → CodeIgniter (`/internal/v1`)

| Method | Endpoint | Tujuan | Validasi utama |
|---|---|---|---|
| POST | `/sources` | Buat konfigurasi konektor | `core_source_id`, `type`, `config`, `schedule`; kredensial dienkripsi di sisi CodeIgniter |
| PATCH | `/sources/{id}` | Ubah konfigurasi | — |
| DELETE | `/sources/{id}` | Nonaktifkan | — |
| POST | `/sources/{id}/sync` | Picu sinkronisasi sekarang | `Idempotency-Key` |
| GET | `/sources/{id}/runs` | Riwayat sinkronisasi | — |
| GET | `/health` | Kesehatan | — |

## 6. Kontrak Internal: Callback ke Laravel (`/internal/v1`)

| Method | Endpoint | Pemanggil | Tujuan | Validasi utama |
|---|---|---|---|---|
| POST | `/connector/items` | CodeIgniter | Serahkan berkas baru atau berubah | multipart: `core_source_id`, `external_id`, `content_hash`, berkas; `Idempotency-Key` wajib |
| POST | `/connector/items/{external_id}/removed` | CodeIgniter | Beri tahu item hilang di sumber | — |
| POST | `/ingestions/{id}/status` | FastAPI | Laporkan `done` atau `failed` | status sah; kesalahan ringkas tanpa isi dokumen |
| POST | `/datasets/{id}/status` | FastAPI | Status impor dataset | — |

Aturan: Laravel menerima item konektor, **memberlakukan ACL bawaan sumber** (`data_sources.default_acl`), dan memutuskan apakah dokumen baru atau versi baru. CodeIgniter tidak menentukan izin. Pengulangan dengan `Idempotency-Key` yang sama mengembalikan hasil sebelumnya (tidak menggandakan dokumen).

## 7. JWT Layanan (DEC-030, PROPOSED)

| Klaim | Isi |
|---|---|
| `iss` | `core`, `connector`, atau `ai` |
| `aud` | Layanan tujuan |
| `sub` | Identitas layanan; untuk konteks pengguna, `user_id` pada klaim `act` |
| `jti` | Pengenal unik (mencegah replay dalam masa berlaku) |
| `iat`, `exp` | `exp` paling lama 5 menit setelah `iat` |
| `scope` | Daftar izin (misalnya `ai:ask`, `ai:ingest`) |
| `principals` | Hanya pada panggilan atas nama pengguna; maksimum ukuran ditetapkan |

Penerbit menandatangani dengan kunci asimetris miliknya; penerima memegang kunci publik penerbit. Rotasi kunci mengikuti `FINAL_SECURITY_SPECIFICATION.md`. Detail algoritma dikunci pada M0 dan diverifikasi terhadap pustaka yang dipakai.

## 8. Strategi Kontrak dan Versi

- OpenAPI per layanan dihasilkan atau ditulis pada M0; pengujian kontrak (contract test) pada pipeline CI memeriksa Laravel–CodeIgniter dan Laravel–FastAPI.
- Perubahan yang merusak (breaking) menaikkan versi (`/v2`) atau memakai pola tambah-dulu lalu hapus.
- Laravel mengembalikan `503 AI_UNAVAILABLE` bila FastAPI tidak sehat; endpoint non-AI tetap berfungsi (NFR-REL-001).

## 9. Matriks Keterlacakan Singkat

| Requirement | Endpoint utama |
|---|---|
| FR-001 | `/auth/*`, `/me` |
| FR-002 | `/users`, `/org-units` |
| FR-005 | `/audit-logs` |
| FR-010 s.d. FR-012 | `/documents*` |
| FR-013 s.d. FR-016 | `/internal/v1/ingestions` (AI) |
| FR-017, FR-018, FR-030 | `/conversations/{id}/messages`; `/internal/v1/ask` |
| FR-019 | `/messages/{id}/feedback` |
| FR-020 s.d. FR-025 | `/datasets*`, `/metrics`, `/analytics/run` |
| FR-031 | `/data-sources*`; kontrak Connector |

## 10. Riwayat Perubahan

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0-draft | 2026-11-08 | Penyusunan awal Gelombang 2 |
