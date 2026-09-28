# Dokumentasi modernisasi Budget Cost & Sales ke Laravel

Tanggal analisis: **28 September 2026**. Sumber: folder proyek kALKULASI yang disediakan. Bahasa: Indonesia. Status: rancangan implementasi berdasarkan analisis statis PRG dan pemeriksaan DBF; bukan hasil menjalankan EXE.

## Cara menggunakan

1. Baca [gambaran sistem dan aturan bisnis](01-sistem-lama.md) untuk memahami proses yang harus dipertahankan.
2. Gunakan [kamus DBF lengkap](02-kamus-dbf.md) saat menulis importer atau menelusuri kolom lama.
3. Jadikan [desain database Laravel dan ERD](03-database-laravel.md) sebagai dasar migration dan model.
4. Implementasikan halaman berdasarkan [spesifikasi UI](04-ui-dan-alur.md).
5. Ikuti [arsitektur, kontrak endpoint, dan laporan](05-arsitektur-dan-laporan.md).
6. Jalankan tahapan [migrasi, pengujian, dan backlog](06-migrasi-dan-pengujian.md).

Dokumen ini membedakan **terbukti** (dibaca dari kode/data), **usulan** (perilaku aplikasi baru), dan **konfirmasi** (keputusan bisnis yang belum dapat ditentukan). Nama field lama tidak otomatis menunjukkan makna bisnisnya.

**Penamaan database disesuaikan dengan permintaan pengguna:** tabel utama mempertahankan nama lama seperti RMMAST, FGMAST, FORMULA, VOLNDL, dan COSTREF. Tabel tambahan memakai nama induknya, misalnya RMMAST_PRICE dan FORMULA_VERSION. Daftar tabel, ERD, serta mapping relasi terbaru ada di [rancangan database](03-database-laravel.md); nama tabel yang sama tidak mengharuskan seluruh struktur kolom tetap sama.

## Ringkasan hasil

Sistem merupakan aplikasi desktop berbasis xBase dengan file DBF dan indeks NTX. Sintaks `TBrowseDB`, `dbSeek`, serta `stock.ch` menunjukkan keluarga Clipper/xBase; folder juga berisi runtime FoxPro. Compiler dan EXE produksi yang benar belum terverifikasi. Label di `MAINT.PRG` menyebut **BUDGET COST & SALES Program**.

Cakupan utama: master RM/FG/noodle, formula, harga bahan dan kurs, volume bulanan, unit cost, harga per pabrik, laporan anggaran, dan closing. Tidak cukup bukti untuk memasukkan pembelian, stok transaksi, atau jurnal akuntansi sebagai modul inti proyek ini.

Hasil pembacaan: **55 DBF**, **386.435 record tidak ditandai deleted**, termasuk salinan, tabel kerja, dan data pendukung. Angka ini bukan jumlah transaksi unik. Master utama berisi 2.390 RM, 2.762 FG, 1.447 noodle, 18 area, dan 25 record factory/report scope. `COSTREF` berisi periode JANUARI 2027; jangan menggantinya dengan tanggal komputer saat impor.

Risiko utama: source belum tentu sama dengan EXE produksi; `REGISTER.DBF` dan `stock.ch` tidak tersedia di folder; matching harga bergantung pada drive W; pasangan formula/volume tidak selalu unik; terdapat orphan references. Keputusan mengenai satuan, waste, sumber harga, serta periode LE harus diselesaikan sebelum kalkulasi disetujui untuk produksi.

## Lampiran mesin dan reproduksi

- [Schema + hash SHA-256 seluruh DBF](evidence/dbf-schema.json).
- [Hasil pemeriksaan kunci dan relasi](evidence/data-quality.json).
- [Peta deklarasi fungsi/prosedur](evidence/source-map.json).
- [Skrip profiler read-only](tools/inspect_legacy.py): jalankan `python docs/tools/inspect_legacy.py` dengan Python 3. Skrip membaca DBF dan menulis ulang lampiran dokumentasi; tidak menjalankan program lama dan tidak mengubah DBF/NTX.

Pemeriksaan relasi membandingkan karakter yang sudah di-strip dan mengecualikan record deleted. Semantik pencarian xBase, collation, memo FPT, dan isi XLSX belum diverifikasi. Dokumen tidak menyatakan seluruh data sudah bersih. Rancangan database di sini belum berupa aplikasi atau migration yang sudah diuji.
