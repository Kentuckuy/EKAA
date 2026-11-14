# docs/VERSIONS.md — Tabel Versi Terkunci dan Terverifikasi EKAA

Sesuai Kebijakan Versi dan Rantai Pasok (§12.6, DEC-045):
- Semua dependensi dikunci pada versi spesifik melalui lockfile (`composer.lock`, `uv.lock`, `package-lock.json`).
- Tidak ada tag `latest`; image Docker dikunci pada tag spesifik.
- Rilis stabil yang matang, bukan versi nightly atau experimental.

---

## 1. Komponen Inti & Infrastruktur (Docker Services)

| Komponen | Versi Terkunci | Image Docker / Basis | Lisensi | Status Verifikasi | Catatan |
|---|---|---|---|---|---|
| **Caddy** | `2.9.1` | `caddy:2.9.1-alpine` | Apache-2.0 | Terverifikasi | Reverse proxy, TLS otomatis, rate limit, HSTS |
| **PostgreSQL** | `17.4` | `pgvector/pgvector:pg17-v0.8.0` | PostgreSQL License | Terverifikasi | Basis data tunggal, schema `core`, `connector`, `ai` |
| **pgvector** | `0.8.0` | Termasuk di image di atas | PostgreSQL License | Terverifikasi | HNSW iterative scan (`hnsw.iterative_scan`), halfvec/vector |
| **Valkey** | `8.0.2` | `valkey/valkey:8.0.2-alpine` | BSD-3-Clause | Terverifikasi | Cache, rate limiting, antrean Laravel (pengganti Redis) |
| **SeaweedFS** | `3.85` | `chrislusf/seaweedfs:3.85` | Apache-2.0 | Terverifikasi | S3-compatible Object Storage (pengganti MinIO CE) |
| **ClamAV** | `1.4.2` | `clamav/clamav:1.4.2` | GPL-2.0 (daemon terpisah) | Terverifikasi | Antivirus scanner fail-closed untuk upload |
| **DuckDB** | `1.1.3` | Dipasang via python library (`duckdb==1.1.3`) | MIT | Terverifikasi | Embedded tabular query engine, read-only volume |

---

## 2. Aplikasi & Framework

| Layanan | Framework / Bahasa | Versi | Ekosistem Paket | Lisensi | Catatan |
|---|---|---|---|---|---|
| **`apps/core`** | Laravel | `13.x` (PHP 8.4.x) | Composer (`composer.lock`) | MIT | IdP tunggal, RBAC, Policy, Audit, Orchestration |
| **`apps/connector`** | CodeIgniter | `4.7.3` (PHP 8.4.x) | Composer (`composer.lock`) | MIT | Connector & Sync Service (MVP: Local Folder) |
| **`services/ai`** | Python + FastAPI | `Python 3.12-slim`, `FastAPI 0.115.8` | uv / pip (`uv.lock`) | MIT | RAG, Hybrid Router, Deterministic Metric Engine, Workers |
| **`apps/web`** | Vue 3 SPA | `Vue 3.5.13`, `Vite 6.1.0` | npm (`package-lock.json`) | MIT | TypeScript, Pinia, Vue Router, Tailwind CSS, ECharts |

---

## 3. Pustaka Kunci per Layanan

### 3.1 Laravel (`apps/core`)
- PHP: `8.4`
- `ramsey/uuid`: `^4.7` (UUIDv7 support)
- `league/flysystem-aws-s3-v3`: `^3.0` (Flysystem S3 adapter ke SeaweedFS)
- `firebase/php-jwt`: `^6.10` atau `lcobucci/jwt`: `^5.4` (RS256 JWT antarlayanan)
- `pestphp/pest`: `^3.0` (Testing framework)
- `larastan/larastan`: `^3.0` (PHPStan static analysis)
- `laravel/pint`: `^1.18` (Code formatting)

### 3.2 CodeIgniter (`apps/connector`)
- PHP: `8.4`
- CodeIgniter 4: `4.7.3`
- `firebase/php-jwt`: `^6.10` (Verifikasi & penerbitan service JWT)
- `phpunit/phpunit`: `^10.5`
- `phpstan/phpstan`: `^1.12`

### 3.3 Layanan AI (`services/ai`)
- Python: `3.12`
- `fastapi`: `0.115.8`
- `uvicorn[standard]`: `0.34.0`
- `pydantic`: `2.10.6`
- `sqlalchemy`: `2.0.38`
- `asyncpg`: `0.30.0`
- `psycopg[binary]`: `3.2.4`
- `pgvector`: `0.3.6`
- `duckdb`: `1.1.3`
- `openpyxl`: `3.1.5`
- `pypdf`: `5.3.0`
- `python-docx`: `1.1.2`
- `boto3`: `1.36.18`
- `httpx`: `0.28.1`
- `pyjwt[crypto]`: `2.10.1`
- `structlog`: `25.1.0`
- `pytest`: `8.3.4`
- `pytest-asyncio`: `0.25.3`
- `ruff`: `0.9.6`
- `mypy`: `1.15.0`

### 3.4 Frontend (`apps/web`)
- Node.js: `>= 20.x` (Target deployment) / Host: `24.x`
- `vue`: `3.5.13`
- `vite`: `6.1.0`
- `typescript`: `5.7.3`
- `pinia`: `2.3.1`
- `vue-router`: `4.5.0`
- `echarts`: `5.6.0`
- `tailwindcss`: `3.4.17`
- `dompurify`: `3.2.4`
- `openapi-typescript`: `7.6.1`
- `vitest`: `3.0.5`
