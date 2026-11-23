# Laporan Pengujian Router, Guardrails, dan Umpan Balik — Milestone M6

| Atribut | Nilai |
|---|---|
| Milestone | **M6 (Router, Guardrails, Feedback)** |
| Standar Rujukan | §25, §26, §27, Invariant 7, DEC-030, DEC-036 |
| Tanggal Pengujian | 24 November 2026 |
| Pelaksana | Antigravity AI Coding Agent |
| Berkas Uji | [`tests/security/test_m6_router_guardrails.js`](file:///D:/EKAA/tests/security/test_m6_router_guardrails.js) |
| Status Keseluruhan | **LULUS 100% (19/19 Uji Lulus, Seluruh Kriteria AC-M6 Terpenuhi dengan Bukti)** |

---

## 1. Hasil Pengujian Acceptance Criteria M6

| ID Kriteria | Aspek yang Diuji | Hasil Uji Nyata | Status |
|---|---|---|---|
| **AC-M6-01** | Akurasi Routing Intent | - Pertanyaan regulasi ("syarat cuti akademik...") diarahkan secara presisi ke pipeline `document_rag` (confidence >= 0.70).<br>- Pertanyaan analitik ("total realisasi anggaran...") diarahkan ke `analytics` (confidence >= 0.70).<br>- Pertanyaan non-domain (resep memasak, cuaca, dll.) dideteksi sebagai `out_of_scope`. | **LULUS** |
| **AC-M6-02** | Pencegahan Injeksi Prompt & Keamanan | - Upaya override instruksi (English: *ignore previous instructions* & Bahasa: *abaikan seluruh instruksi sebelumnya*) diblokir mutlak.<br>- Upaya jailbreak *Developer Mode* / *DAN Mode* diblokir.<br>- Upaya ekstraksi *system prompt* atau kredensial rahasia diblokir tanpa membocorkan isi prompt internal.<br>- Muatan injeksi SQL (*UNION SELECT*, *DROP TABLE*) dan injeksi skrip XSS (*<script>*) diblokir.<br>- Karakter zero-width tak terlihat dan null-byte dibersihkan melalui sanitasi awal. | **LULUS** |
| **AC-M6-03** | Rekam Umpan Balik & Proteksi Kepemilikan | - Umpan balik positif (*thumbs up*) tersimpan valid dengan referensi `conversation_id`, `message_id`, dan `user_id`.<br>- Percobaan pemberian umpan balik pada percakapan pengguna lain ditolak tegas dengan penyamaran `404 NOT FOUND` untuk mencegah kebocoran informasi kepemilikan. | **LULUS** |
| **AC-M6-04** | Alasan Terstruktur Umpan Balik Negatif | - Umpan balik negatif (*thumbs down*) wajib memilih alasan valid dari daftar kanonikal (`inaccurate`, `incomplete`, `wrong_citation`, `unhelpful`, `other`).<br>- Pengiriman umpan balik negatif tanpa alasan terstruktur langsung ditolak validasi sistem. | **LULUS** |
| **AC-M6-05** | Mekanisme Klarifikasi Ambigu | - Pertanyaan 1 kata yang terlalu singkat (contoh: "anggaran?", "peraturan?") secara proaktif memicu intent `clarification_required` dengan saran pertanyaan klarifikasi terarah.<br>- Pertanyaan dengan bobot sinyal dokumen dan analitik seimbang (*exact tie*) memicu permintaan klarifikasi preferensi ke pengguna. | **LULUS** |
| **AC-M6-06** | Integrasi End-to-End Pipeline | Pengujian rangkaian pipa terpadu berhasil membuktikan alur: Muatan berbahaya terhenti di *Guardrails* -> Pertanyaan ambigu meminta klarifikasi di *Router* -> Pertanyaan valid didistribusikan ke mesin `document_rag` atau `analytics` secara mulus. | **LULUS** |

---

## 2. Kesimpulan Milestone M6

Milestone M6 (Router, Guardrails, Feedback) telah selesai 100%:
1. Hybrid intent router mengklasifikasikan pertanyaan secara akurat dan menolak spekulasi buta melalui mekanisme klarifikasi proaktif.
2. Guardrails keamanan secara tangguh memblokir berbagai vektor serangan injeksi prompt dalam Bahasa Indonesia maupun Bahasa Inggris.
3. Layanan feedback menyediakan taksonomi alasan terstruktur yang esensial untuk audit kualitas dan perbaikan berkelanjutan.

Proyek kini siap melangkah ke **Milestone 7 (M7 — Connector & Sync)**.
