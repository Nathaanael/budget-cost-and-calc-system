# 05 — Arsitektur, endpoint, dan laporan

## Rekomendasi teknologi

Usulan awal adalah **Laravel 13 + PHP 8.3 atau lebih baru yang didukung lingkungan perusahaan**. Dokumentasi rilis resmi menyebut minimum PHP 8.3 untuk Laravel 13; kompatibilitas extension dan dependency proyek tetap diuji saat setup. Sumber diperiksa 28 September 2026: [Laravel Release Notes](https://laravel.com/framework/docs/releases) dan [Deployment](https://laravel.com/framework/docs/deployment).

Gunakan aplikasi monolit modular dengan Blade untuk halaman dan JavaScript seperlunya untuk grid. Ini pilihan rancangan agar satu proyek mencakup validasi, otorisasi, dan rendering. Database relasional menyimpan data bisnis; queue worker menjalankan kalkulasi/import/export; penyimpanan privat menyimpan snapshot dan berkas hasil.

Laravel menyediakan abstraksi queue dengan beberapa backend, termasuk database dan Redis. Backend database cukup sebagai titik awal; pilih Redis bila kebutuhan operasi membenarkannya. [Dokumentasi queue](https://laravel.com/framework/docs/queues).

## Pembagian tanggung jawab

```text
app/
  Models/                    Master, scenario, formula, input, run, result
  Http/Controllers/          Request/response; tanpa rumus biaya
  Http/Requests/             Format data dan validasi per aksi
  Policies/                  Hak lihat/edit/calculate/close per scope
  Services/Costing/          PurchasePrice, Matching, UnitCost, SellingPrice
  Services/Volume/           Ekspansi noodle → FG dan agregasi area
  Services/Reporting/        Query snapshot hasil dan aturan presentasi
  Services/Import/           Staging, mapping, validasi dan rekonsiliasi
  Services/Period/           Closing dan opening periode
  Jobs/                     RunCalculation, ImportLegacy, ExportReport
  Enums/                    Status dan kode skenario/slot
tests/
  Unit/Costing/              Rumus, decimal, edge cases
  Feature/                  Endpoint, permission, periode, konflik edit
  Integration/Legacy/       Golden fixtures dan pembanding output lama
```

Model merepresentasikan tabel, service melakukan aturan bisnis, controller memanggil service. Jangan memindahkan kode PRG menjadi satu controller besar. Semua service kalkulasi menerima snapshot input dan versi aturan; tidak membaca master mutable di tengah proses.

Nama tabel SQL mengikuti nama legacy dalam [rancangan database](03-database-laravel.md), misalnya model RawMaterial terhubung ke `RMMAST` dan FinishedGood ke `FGMAST`. Tetapkan mapping tabel dan foreign key secara eksplisit pada model/relasi/migration. Nama class service, endpoint, serta nama field request tetap boleh deskriptif; kontrak endpoint di bawah tidak berubah karena nama tabel berubah.

## Pipeline kalkulasi

1. Validasi izin, periode open, semua FK, kelengkapan harga, mapping, dan dependency tanpa siklus.
2. Buat run dan input snapshot konsisten di transaksi singkat. Catat revisions dan hash. Pengguna tetap dapat melihat run sebelumnya.
3. Dispatch job setelah transaksi selesai. Terapkan lock per budget scope dan idempotency key agar retry tidak menerbitkan hasil ganda.
4. Konversi harga sesuai source policy; terapkan matching eksternal untuk material yang ditentukan. Untuk material dengan lebih dari satu calon sumber, preflight harus meminta aturan prioritas yang telah dikonfigurasi.
5. Hitung FG internal kemudian FG pemakai; simpan detail biaya per baris dan slot. Hitung harga per pabrik.
6. Ekspansi volume noodle, kelompokkan FG/area/scenario/month. Hitung laporan dari run yang sama.
7. Validasi total dan kelengkapan. Publish run atomik hanya jika seluruh langkah berhasil; failure tidak mengubah pointer hasil terakhir.

Gunakan decimal arithmetic dengan rounding mode dan titik pembulatan eksplisit. Cast decimal model saja tidak menjamin operasi PHP bebas float. Implementasikan value object/decimal library yang diuji dan pin versinya. Jangan memakai `round()` secara tersebar tanpa aturan terpusat.

## Role yang diusulkan

Role berikut belum terbukti ada pada legacy dan perlu disepakati pemilik proses.

| Aksi | Admin | Master editor | Budget operator | Reviewer/closer | Viewer |
|---|---|---|---|---|---|
| Pengguna dan role | Ya | Tidak | Tidak | Tidak | Tidak |
| Master/formula draft | Lihat | Edit | Lihat | Lihat | Lihat terbatas |
| Harga/volume/impor draft | Lihat | Tidak | Edit | Lihat | Lihat terbatas |
| Approve input/formula | Tidak default | Tidak | Tidak | Ya | Tidak |
| Jalankan kalkulasi | Tidak default | Tidak | Ya | Ya | Tidak |
| Tutup/reopen periode | Tidak default | Tidak | Tidak | Ya | Tidak |
| Laporan/export | Sesuai scope | Sesuai scope | Sesuai scope | Ya | Sesuai scope |

Permission selalu diperiksa server, termasuk export dan akses job. Hindari menganggap admin teknis otomatis mempunyai wewenang persetujuan biaya. Gunakan password hash framework, session security, CSRF untuk web, dan audit. Otorisasi action dapat memakai Policy Laravel. [Dokumentasi authorization resmi](https://github.com/laravel/docs/blob/13.x/authorization.md).

## Kontrak endpoint usulan

Route web memakai session; JSON berikut untuk grid dan proses asinkron dalam aplikasi yang sama. Tidak perlu API publik terpisah kecuali ada kebutuhan integrasi.

| Method/path | Request inti | Hasil dan constraint |
|---|---|---|
| GET /raw-materials | search, page, type | daftar paginated; server membatasi ukuran page |
| POST /raw-materials | code, name, unit_id, waste_ratio, divisor | 201, field error 422; permission master.write |
| PATCH /raw-materials/{id} | field yang berubah, revision | 200 atau 409 revision conflict |
| POST /formula-versions | fg_id, scenario_id, batch_qty, items[] | 201 draft; simpan header/items dalam satu transaksi |
| POST /formula-versions/{id}/approve | revision | 200; validasi referensi dan permission reviewer |
| PUT /noodle-volumes | scenario_id, area_id, rows[], revision | perubahan atomik untuk scope; closed period 409 |
| POST /imports | multipart file, source_type, mapping_version | 202 + batch_id; file privat dan batas ukuran |
| GET /imports/{id} | — | counts, row_errors, preview, status |
| POST /imports/{id}/commit | accepted_preview_hash | 202; tolak preview kedaluwarsa |
| POST /calculations/preflight | cycle_id, slot_ids, rule_version, multilevel | blocking_errors[], warnings[], input_revision |
| POST /calculation-runs | parameter di atas + input_revision; Idempotency-Key | 202 + run_id; stale input 409 |
| GET /calculation-runs/{id} | — | status, stage, completed, total, errors |
| GET /reports/{report_code} | run_id, scenario_id, factory_id?, area_id?, format | HTML/JSON; export besar via job 202 |
| POST /periods/{id}/close | published_run_id, revision, backup_reference | 200 immutable closure; run gagal/stale 409 |

Contoh hasil preflight, data fiktif:

```json
{
  "can_run": false,
  "input_revision": "revision-contoh",
  "blocking_errors": [
    {"code": "MISSING_MATERIAL", "entity": "formula_item", "id": 12,
     "message": "Bahan pada baris formula tidak ditemukan."}
  ],
  "warnings": []
}
```

Gunakan 403 untuk izin ditolak, 404 untuk entitas di luar scope yang tidak boleh diketahui, 422 untuk input invalid, 409 untuk state/revision conflict. Detail internal exception tidak dikirim ke pengguna.

## Katalog laporan

Seluruh 13 pilihan berikut terbukti pada REP1.PRG:11–23. Detail formula khusus setiap cabang harus dilengkapi dengan golden output sebelum dianggap selesai; inventaris menu bukan bukti kesetaraan laporan.

| Kode | Nama legacy | Dasar data baru dan filter utama | Sumber entry |
|---|---|---|---|
| R01 | RM Price Budget | harga material per slot, mata uang, RM | Report1 |
| R02 | RM Unit Cost Standard | detail formula, waste, qty, harga, amount, FG | Report2 |
| R03 | Unit Cost & Sell.Price | UC + harga per pabrik/slot, tipe produk | Report3 |
| R04 | Sales Volume | volume FG/area/bulan, LE/AOP, pabrik/nasional | Report4 → REP4 |
| R05 | Sales Value | volume × harga per pabrik, LE/AOP | Report5 → REP2/rep21 |
| R06 | Raw Material Value | nilai bahan per scope/skenario | Report6 → rep3/rep31 |
| R07 | UC & SP Variance | pasangan pembanding UC/harga + selisih | REP5 Report7 |
| R08 | Alokasi Pemakaian RM (Qty) | formula × volume dan aturan unit | REP5 Report8/81 |
| R09 | Alokasi Pemakaian RM (Amt) | detail pemakaian × harga | REP6 Report9/91 |
| R10 | Pemakaian RM dalam All FG | penggunaan satu RM lintas FG | REP6 Report10 |
| R11 | Detail Cost per Type RM | usage/price/amount menurut tipe RM | REP1 Report11 → rep7/REP71 |
| R12 | RM Unit Cost Standard New | format biaya khusus; klasifikasi bahan | REP1 Report12 |
| R13 | RM Unit Cost Standard Smry | ringkasan biaya khusus | REP1 Report21 |

REP2.PRG:297 dan :558 menunjukkan nilai bulanan menjumlahkan `round(volume_per_pabrik × harga_per_pabrik,0)` untuk empat pabrik. Membulatkan sekali setelah total dapat memberi hasil berbeda. Jangan menggunakan satu harga nasional untuk semua area tanpa membuktikan rumusnya.

R07 pada REP5.PRG:80–88 menggunakan selisih Q1 minus current dan persen `(nilai_Q1 - nilai_current) / nilai_current × 100`, baik untuk price maupun UC. Namun, cabang price membaca PRICE1/PRICE0 yang tidak ada persis pada FGMAST aktual. Pasangan field pengganti dan perilaku baseline nol perlu dikonfirmasi. UI baru menampilkan “tidak terdefinisi” untuk persen dengan baseline nol sampai aturan bisnis lain disetujui.

Semua export mencantumkan run ID, periode/tahun, skenario, scope, versi aturan, satuan, aturan pembulatan, waktu, dan pembuat. Tautan drilldown membawa pengguna ke baris formula/sumber harga. Format awal: tabel web dan CSV; PDF/XLSX dapat ditambahkan dengan template laporan yang disetujui. Lindungi CSV export dari formula injection saat mengekspor teks yang berasal dari input.

## Operasi produksi

Pisahkan development, staging, production; gunakan HTTPS dan storage privat; kredensial berada di konfigurasi rahasia. Jalankan migration melalui rilis terkontrol, worker dikelola service supervisor, log menggunakan run/request ID. Backup mencakup database, input snapshot, dan file impor; lakukan latihan restore. Target retensi, jam layanan, kapasitas, RPO/RTO, dan pilihan server masih perlu keputusan TI perusahaan. Jangan menampilkan target tersebut seolah sudah disepakati.
