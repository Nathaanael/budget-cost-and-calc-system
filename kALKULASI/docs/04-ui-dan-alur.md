# 04 — Spesifikasi UI dan alur pengguna

Seluruh layar berikut adalah **usulan web**, direkonstruksi dari fungsi/menu lama; bukan screenshot aplikasi berjalan. Fokus desktop untuk grid lebar, tetap dapat dibaca pada tablet. Bahasa antarmuka Indonesia, istilah RM/FG/LE dipertahankan dengan bantuan penjelasan.

## Navigasi

```text
Dashboard
Master Data
  Bahan Baku (RM) / Produk (FG) / Noodle
  Area / Pabrik dan Cakupan / Tipe Produk / Satuan
Anggaran
  Periode dan Skenario / Kurs / Harga RM / PE per Pabrik
Formula
  Formula FG / Formula Noodle / Mapping Harga Eksternal
Volume Noodle
Kalkulasi dan Riwayat
Laporan (13 kategori legacy)
Impor dan Validasi
Closing
Administrasi: Pengguna, Hak Akses, Audit
```

Header global selalu menampilkan periode, skenario, status open/closed, dan pengguna. Mengubah periode ketika form belum disimpan menampilkan pilihan simpan draft/buang perubahan/batal. Menu disembunyikan sesuai role dan endpoint tetap memeriksa permission di server.

**Penamaan untuk memudahkan pengguna lama:** menu, judul halaman, dan pilihan jenis impor menampilkan nama yang sudah dikenal beserta keterangannya: `RMMAST — Bahan Baku`, `FGMAST — Finished Goods`, `NDLMAST — Noodle`, `FORMULA — Formula FG`, `NDLFORM — Formula Noodle`, `VOLNDL — Volume Noodle`, `VOLUME — Hasil Volume FG`, `COSTREF — Referensi Anggaran`, serta `SYNONIM — Mapping Harga`. Tabel tambahan untuk versi/histori ditampilkan sebagai tab atau riwayat di halaman induk. Pohon navigasi dan wireframe di bawah menunjukkan fungsi halaman; label implementasinya mengikuti ketentuan ini.

## Daftar layar dan field

| ID | Layar | Field/filter | Aksi dan hasil |
|---|---|---|---|
| UI-01 | Login | identitas, password | masuk/reset; pesan gagal generik dan rate limit |
| UI-02 | Dashboard | periode, skenario | jumlah master, formula bermasalah, input belum lengkap, run terakhir; angka berasal run terpilih |
| UI-03 | Daftar/detail RM | kode, nama, tipe, ID legacy, satuan, mata uang, waste, divisor | cari/paginasi/tambah/edit/nonaktif; tab harga dan riwayat; kode unik |
| UI-04 | Daftar/detail FG | kode, kode lama, nama 1/2, tipe/subtipe, batch, LEVEL, active | tab formula, PE, hasil harga, riwayat; hasil kalkulasi read-only |
| UI-05 | Noodle | kode, nama, satuan | tab formula dan volume; kode unik |
| UI-06 | Area/pabrik | kode, nama, kind, price family, area anggota | edit cakupan per skenario; aggregate tidak dipilih sebagai pabrik harga |
| UI-07 | Periode/kurs | tahun, label, tanggal, current/LE/Q1–Q4, kurs | validasi tanggal dan kurs > 0; periode closed read-only |
| UI-08 | Harga RM | RM, mata uang, current/LE/Q1–Q4, sumber | grid edit harga; preview konversi/matching; missing berbeda dari angka 0 |
| UI-09 | Formula FG | produk, skenario, versi, batch; RM, StdA, StdB | tambah/hapus baris, draft, validasi, approve; duplicate ditandai tanpa hilang otomatis |
| UI-10 | Formula noodle | noodle, versi; FG, standard | grid komponen; cek semua referensi sebelum approve |
| UI-11 | Volume noodle | area, noodle, skenario, tahun; Jan–Des | grid bulanan, subtotal, impor; LE hanya bulan tersedia; validasi server |
| UI-12 | Mapping eksternal | snapshot sumber, RM, kode FG eksternal | cocokkan, preview nilai lama/baru, tunjukkan konflik; tidak overwrite tanpa trace |
| UI-13 | Kalkulasi | periode, slot, scope, mode multilevel, versi aturan | preflight, jalankan, progres, rincian error, hasil dan selisih |
| UI-14 | Laporan | kategori, run, skenario, pabrik/area, tipe/FG/RM | preview, drilldown, export; label unit dan pembulatan terlihat |
| UI-15 | Impor | file, jenis data, mapping, tahun, encoding | upload → validasi → preview → commit; unduh daftar error |
| UI-16 | Closing | periode, run final, backup reference, catatan | ringkasan verifikasi → tutup → status locked; otorisasi khusus |
| UI-17 | Audit/admin | aktor, waktu, entitas, aksi | detail before/after; pembatasan akses dan penyamaran data sensitif |

## Wireframe formula

```text
┌──────────────────────────────────────────────────────────────────────────┐
│ Budget Cost & Sales       Periode [2027 v]  Skenario [AOP v]   [Akun]    │
├───────────────┬──────────────────────────────────────────────────────────┤
│ Dashboard     │ Formula FG   >   FG-DEMO (contoh fiktif)                 │
│ Master        │ Nama: Produk Contoh   Batch: [1000]  Versi: 3 [Draft]   │
│ Anggaran      │ [Komposisi] [Simulasi biaya] [Riwayat]                   │
│ Formula ●     │ RM       Nama          Std A       Std B       Status   │
│ Volume        │ [cari]   Bahan A       [10.000]    [0.010]      Valid    │
│ Kalkulasi     │ [cari]   Bahan B       [20.000]    [0.020]      Valid    │
│ Laporan       │ [+ Tambah baris]                                        │
│ Impor         │                                                        │
│ Closing       │ Waste/satuan mengikuti snapshot saat kalkulasi.         │
│               │ [Batal] [Simpan draft] [Validasi] [Ajukan persetujuan]    │
└───────────────┴──────────────────────────────────────────────────────────┘
```

Angka adalah contoh tata letak, bukan rumus hubungan StdA/StdB. Gunakan format input numerik yang jelas: tampilan Indonesia boleh memakai koma desimal, tetapi parsing server tidak ambigu. Grid harus mendukung keyboard Tab/Enter, header tetap, pencarian kode/nama, dan indikator perubahan belum disimpan. Tidak perlu membatasi formula ke 30 baris hanya karena scratch table lama menggunakan 30 baris.

## Wireframe kalkulasi dan penelusuran biaya

```text
Kalkulasi — AOP 2027
[Slot: Q1] [Scope: Semua FG] [Multilevel: Ya] [Aturan: legacy-v1]

Pemeriksaan input
✓ Versi formula tersedia           ✓ Kurs tersedia
! 2 referensi RM tidak ditemukan    ! 3 mapping harga konflik
[Lihat masalah]                     [Jalankan — nonaktif sampai valid]

Riwayat run       Status      Mulai        Oleh       Hasil
RUN-CONTOH        Berhasil    10:15 WIB    Operator   [Buka]

Detail FG → bahan → standard × (1+waste) ÷ divisor × harga = biaya
Sumber harga: snapshot/import/slot | PE: pabrik terpilih
[Bandingkan run sebelumnya] [Unduh hasil]
```

Klik Jalankan menghasilkan run ID segera dan mencegah klik ganda melalui idempotency key. Progres menampilkan tahap dan jumlah selesai, bukan estimasi palsu. Run gagal menyediakan alasan dan retry terkontrol; hasil sukses sebelumnya tetap tersedia. Perubahan input setelah run ditandai “hasil berasal dari versi input sebelumnya”.

## Alur impor

1. Operator memilih jenis sumber dan tahun/skema periode; sistem menghitung hash file.
2. Sistem membaca ke staging dan menampilkan jumlah aktif, deleted, invalid, duplicate, orphan, serta usulan mapping.
3. Operator meninjau masalah per record. Baris error tidak otomatis diabaikan.
4. Commit hanya untuk batch atau subset yang sudah disetujui secara eksplisit; ringkasan excluded rows wajib terlihat.
5. Hasil mencatat jumlah target per entitas, waktu, pengguna, mapping version dan tautan audit. Mengirim ulang request tidak menambah record yang sama.

## Alur closing

Halaman menampilkan periode yang akan dikunci, run final, selisih rekonsiliasi, status input, dan backup reference. Tombol “Tutup periode” membutuhkan permission closer, pemeriksaan ulang di server, serta konfirmasi nama periode. Setelah sukses tampil halaman ringkasan read-only dan periode baru dapat dibuat. Periode lama tidak direset. Reopen, jika disepakati, meminta alasan dan menghasilkan audit event baru.

## State dan aksesibilitas

Setiap grid mempunyai loading, empty, validation error, forbidden, server error, dan saved state. Error menunjukkan field/baris serta cara memperbaiki. Konflik edit dua pengguna menampilkan revisi terbaru dan meminta reload/merge, bukan diam-diam overwrite. Jangan mengandalkan warna saja; gunakan label dan ikon. Setiap input mempunyai label, navigasi keyboard, fokus yang terlihat, serta konfirmasi perubahan yang dapat dibaca pembaca layar.

Laporan lebar memakai horizontal scroll dan export; master/form penting tetap nyaman pada lebar tablet. Target performa awal yang perlu diuji: daftar master p95 < 2 detik pada data contoh dan kalkulasi dijalankan melalui background job. Ini target penerimaan, bukan hasil benchmark.
