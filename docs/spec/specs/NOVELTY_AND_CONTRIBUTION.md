# NOVELTY AND CONTRIBUTION — EKAA

**Versi 0.1 — 10 Nov 2026 — DRAFT PROVISIONAL.** Prinsip: menggabungkan teknologi populer **bukan** novelty.

| Aspek | Penelitian sebelumnya | Usulan EKAA | Perbedaan | Bukti | Kelayakan novelty |
|---|---|---|---|---|---|
| **Methodological** — hibrida+RRF+rerank | Sudah ada di Inggris [4] dan Indonesia [12] | Sama | Hampir tidak ada | Matriks #5, #12 | **RENDAH** |
| **Methodological** — filter hak akses *sebelum* pencarian pada hibrida berbahasa Indonesia, diukur leakage | Konseptual [8]; praktik industri | Terukur dengan uji kebocoran | Kuantifikasi | Pencarian sempit | **SEDANG (belum terbukti)** |
| **Architectural** — Laravel + CodeIgniter + FastAPI, ACL denormalisasi pada chunk | Tidak dicari | Pola tiga layanan | Rekayasa | Tidak ada | **RENDAH sebagai klaim riset** (nilai produk, bukan ilmiah) |
| **Architectural** — router dokumen/analitik + metric engine deterministik | Router kompleksitas [2]; PAL [3] | Router sumber + fungsi teruji (bukan kode bebas) | Kelas dan eksekusi berbeda | Parsial | **SEDANG** |
| **Empirical** — golden dataset Indonesia institusi dengan anotasi terdokumentasi, ablasi komponen, signifikansi | Rigor belum diketahui di [9–13] | Dataset + protokol + kode | Potensi kontribusi utama | Perlu full text | **SEDANG–TINGGI jika dataset dirilis dan rigor ditegakkan** |
| **Empirical** — hasil negatif/netral dilaporkan | Jarang | Wajib (AC §8) | Transparansi | — | Nilai tambah |

**Kontribusi yang defensible saat ini:** (1) dataset evaluasi dan protokol ablasi yang dapat direproduksi; (2) bukti empiris apakah manfaat hibrida/reranking dari literatur Inggris berlaku pada dokumen institusi Indonesia; (3) pengukuran overhead dan efek filter hak akses. **Tidak boleh diklaim:** "pertama", "baru", "state-of-the-art" tanpa matriks lengkap.
