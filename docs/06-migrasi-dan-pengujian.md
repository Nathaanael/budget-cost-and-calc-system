# 06 — Migrasi, validasi, dan rencana kerja

## Temuan kualitas data

Sumber angka: [data-quality.json](evidence/data-quality.json), hasil membaca record DBF yang tidak ditandai deleted. “Duplikat” berarti kunci kandidat sama, bukan bukti seluruh kolom identik atau boleh dihapus.

| Pemeriksaan | Temuan | Tindakan |
|---|---|---|
| Master RM/FG/NDL/area/factory/type/reference | tidak ditemukan duplikat/blank pada kunci yang diuji | masih validasi format, semantik dan flags |
| FORMULA (FGCODE,RMCODE) | 218 kelompok berulang, 218 baris tambahan | simpan line_no; review apakah komponen sengaja berulang |
| VOLUME (FGCODE,CODE) | 70 kelompok berulang, 95 baris tambahan | arsipkan detail; bandingkan agregat dengan hitung ulang |
| SYNONIM RMCODE | 3 kelompok berulang, 3 baris tambahan | selesaikan konflik sebelum mapping aktif |
| FORMULA → RMMAST | 2 baris tidak cocok, 2 kode | quarantine; minta master/mapping yang benar |
| NDLFORM → NDLMAST | 6 baris tidak cocok, 3 kode | quarantine dan review versi sumber |
| NDLFORM → FGMAST | 3 baris tidak cocok, 3 kode | review referensi FG |
| SYNONIM → RMMAST | 100 baris/kode tidak cocok | review apakah data usang atau master kurang |
| SYNONIM → FGMAST1 | 99 baris/kode tidak cocok | master eksternal bisa tidak lengkap; jangan substitusi ke FG lokal |
| WASTE | seluruh 2.390 RM bernilai 0 | perlu contoh nonnol untuk membuktikan skala persen/rasio |
| Deleted records | antara lain FGMAST1 2, FORM 11, GABUNG2 78.734 | simpan flag; jangan aktifkan kembali otomatis |
| COSTREF | PER 01012027; PERIOD JANUARI 2027 | tentukan tahun tiap skenario secara eksplisit |

Tidak ada record invalid/truncated yang ditemukan oleh profiler pada 55 DBF ini. Ini bukan validasi penuh format DBF atau bukti tidak ada isi numerik/tanggal yang salah. Profiler belum memvalidasi semua nilai numerik, total bulanan, keutuhan memo, atau relasi selain yang tercantum.

## Tahapan migrasi

1. **Tetapkan sumber resmi.** Identifikasi EXE produksi, kumpulkan source/build yang cocok, REGISTER yang diperlukan, dependency W, dan laporan contoh. Ambil salinan ketika aplikasi tidak menulis; simpan SHA-256. Arsipkan semua file termasuk salinan sebelum klasifikasi.
2. **Profiling dan staging.** Baca header/field, record number, deleted flag, raw bytes/payload, encoding dan schema fingerprint. Jangan melakukan PACK/ZAP/REINDEX pada sumber. Gunakan parser DBF yang mendukung varian aktual; profiler dokumentasi ini bukan importer produksi.
3. **Mapping master.** Simpan leading zero dan legacy code. Tetapkan satuan/divisor, kelompok pabrik dan area. Resolve orphan serta duplicate dengan catatan keputusan.
4. **Mapping periode dan transaksi.** Tentukan tahun current/LE/AOP, unpivot harga/volume, versikan formula. Jangan mengisi semester LE yang tidak ada dengan nilai historis rekaan. Bedakan missing, nol, dan record excluded.
5. **Impor percobaan.** Commit master, formula, harga, volume sesuai dependency. Simpan lineage. Foreign key diterapkan setelah staging valid, bukan dimatikan untuk menyembunyikan error.
6. **Rekonsiliasi input.** Bandingkan row count, active/deleted/excluded, total per dimensi, dan nilai decimal. Satu record volume dapat menjadi 18 record target; jangan membandingkan raw row count tanpa aturan transformasi.
7. **Rekonsiliasi output.** Gunakan sumber beku sama pada EXE resmi dan Laravel, bandingkan UC/price/volume/13 laporan. Catat setiap selisih sebagai bug baru, bug legacy yang disetujui diperbaiki, atau mapping berbeda.
8. **UAT dan parallel run.** Pemilik proses melakukan satu siklus lengkap, termasuk input baru, rerun, laporan dan closing, memakai dataset representatif. Tanda tangan hasil dan daftar pengecualian.
9. **Cutover.** Bekukan input legacy, ambil backup final, lakukan impor delta/full yang dipilih, rekonsiliasi final, alihkan pengguna. Legacy tersedia read-only untuk referensi.
10. **Rollback.** Sebelum cutover tentukan pemicu rollback dan penanggung jawab. Bila ada data baru di web, ekspor perubahan dan rekonsiliasi terlebih dahulu; jangan langsung menghidupkan dua sistem yang sama-sama menerima input.

## Test case prioritas

Contoh angka di bawah sintetis untuk test, bukan harga produksi.

| ID | Input/kondisi | Hasil yang diharapkan |
|---|---|---|
| T01 FX | USD=2,50; kurs=16.000; tipe=1 | RP=40.000 per slot; tipe lain tidak diubah Calc1 |
| T02 Waste/konversi | StdB=10; waste ratio=0,02; RP=20.000; ID blank | qty=10,2; cost=204; sekaligus uji skala waste yang disetujui |
| T03 ID nonblank | input T02; ID nonblank | cost=204.000, tanpa /1000 |
| T04 Harga pabrik | UC=204; PE=25 | harga=229; tiap pabrik memakai PE masing-masing |
| T05 Zero cost | harga sebelumnya=100; UC baru=0 | compatibility menyelidiki harga lama tetap; mode koreksi mengikuti keputusan tercatat |
| T06 Multilevel | FG internal A dipakai sebagai RM oleh B | harga A yang sesuai legacy mengalir ke B; urutan deterministik; siklus ditolak |
| T07 Missing reference | kode RM/FG tidak ditemukan | preflight blocked; tidak memakai record terakhir hasil seek |
| T08 Formula berulang | dua baris FG+RM sama | kedua line tetap ada; kontribusi keduanya dihitung sesuai keputusan, tidak last-write-wins |
| T09 Volume | NDL1=100×2 FG; NDL2=50×3 FG di area sama | total FG=350; LE dan AOP tidak tercampur |
| T10 Volume nonbersebelahan | FG muncul pada beberapa noodle | GROUP BY tetap satu output per dimensi; bandingkan total legacy |
| T11 Rounding | nilai tepat .005, nilai kecil, kode panjang 5, UC field 2 desimal | cocok dengan golden output EXE dan rule_version, bukan asumsi float |
| T12 Sales rounding | dua kontribusi 0,49 dan 0,49 | dengan round masing-masing 0 desimal → 0, bukan round total → 1 |
| T13 Mapping | current dan LE eksternal tersedia | Calc2 compatibility mengubah LE/Q1–Q4; current tidak berubah |
| T14 Periode | input ke periode closed | 409/penolakan; tidak ada perubahan DB |
| T15 Hak akses | viewer mengirim POST calculate/close | ditolak server meskipun tombol disembunyikan |
| T16 Konflik edit | dua operator menyimpan revision sama | request kedua 409; data pertama tidak tertimpa |
| T17 Idempotensi | retry impor/job/request sama | tidak membuat baris/hasil ganda |
| T18 Kegagalan job | fail setelah setengah hasil dibuat | run failed, hasil parsial tidak dipublish; run terakhir tetap tersedia |
| T19 Closing | run sukses final, lalu close dua kali | satu closure; histori tetap; request ulang aman |
| T20 Import | deleted, leading zero, numeric blank, format decimal | raw tersimpan; kode tidak jadi integer; invalid ditandai; no silent coercion |
| T21 Kelengkapan output | 13 laporan × scope yang tersedia | total dan detail cocok atau mempunyai pengecualian yang disetujui |
| T22 Snapshot | ubah PE/waste/master setelah run sukses | report run lama tidak berubah; rerun memiliki snapshot baru |

Toleransi tidak boleh satu angka global sembarang: input decimal harus persis sesuai mapping; keluaran uang legacy diuji pada skala penyimpanannya; agregat dan kuantitas mengikuti aturan pembulatan per laporan. Setiap toleransi nonnol memerlukan persetujuan pemilik laporan dan tercatat pada hasil perbandingan.

## Backlog berurutan

| Tahap | Deliverable | Kriteria selesai |
|---|---|---|
| P0 Analisis | keputusan bisnis, data resmi, golden dataset | source/EXE dipetakan; isu blocking diputuskan |
| P1 Fondasi | Laravel, login, roles, schema, audit, periode | permission dan period lock diuji |
| P2 Master/impor | RM/FG/noodle/area/pabrik, staging, mapping | data invalid terlihat; importer idempotent; lineage lengkap |
| P3 Input | formula versioning, kurs/harga, PE, volume | validasi, revision conflict dan approval bekerja |
| P4 Engine | FX, matching, UC/price, multilevel, volume | T01–T13 lulus; rekonsiliasi golden disetujui |
| P5 Laporan | 13 kategori dan export | detail/subtotal/scope/rounding cocok |
| P6 Closing/operasi | backup, close, audit, restore, monitoring | T14–T22 lulus, latihan restore dan UAT selesai |
| P7 Cutover | migrasi final, pelatihan, runbook | rekonsiliasi final dan persetujuan operasional |

Estimasi waktu baru dibuat setelah jumlah pengembang, kedalaman 13 laporan, integrasi SAP/W, hosting dan SLA diketahui. P0–P4 adalah jalur kritis; membuat UI terlebih dahulu tidak menyelesaikan ketidakpastian angka biaya.

## Pertanyaan untuk workshop pemilik sistem

1. EXE mana yang digunakan sehari-hari: COSTplb atau versi lain? Apakah source ini sama dengan build tersebut?
2. Apakah W:FGMast masih aktif? Siapa pemilik master dan kapan snapshot diperbarui?
3. Current, LE, AOP mengacu tahun apa? Apakah LE hanya semester kedua atau butuh gabungan aktual semester pertama?
4. Apa definisi StdA, StdB, batch dan satuan masing-masing? Apakah RM.ID memang penentu konversi satuan?
5. Apakah input waste 2% disimpan 2 atau 0,02? Sediakan satu contoh nonnol yang telah disetujui.
6. Apa arti PE, HRGJUAL dan HRG per pabrik? PE dari FG atau CostRef yang berlaku secara operasional?
7. Apakah CKP di menu setara Purwakarta pada master? Bagaimana area C6 dan cakupan nasional dikelola?
8. Apakah duplikat formula sengaja merupakan baris terpisah? Mapping synonym yang konflik mana yang benar?
9. Untuk harga bahan yang juga USD, external dan internal, sumber mana memiliki prioritas?
10. Laporan mana wajib identik dan mana boleh diperbaiki? Apa denominator variance dan aturan rounding resminya?
11. Apakah closing benar-benar dijalankan dengan source saat ini? Apa arti reset tahunan yang diinginkan?
12. Siapa yang boleh edit, approve, calculate, export, close, reopen? Apakah pembatasan per pabrik diperlukan?
13. Apakah data MCDSAP/YC06 merupakan input laporan aktif atau arsip? Format integrasi resmi apa yang tersedia?
14. Format dokumentasi/operasional tambahan, retensi, server, backup, jumlah pengguna, dan target layanan apa yang diwajibkan TI?

Daftar ini menjadi agenda workshop; dokumentasi dan pekerjaan fondasi tetap dapat digunakan sebelum semua jawaban tersedia. Engine produksi dan cutover menunggu penyelesaian keputusan yang memengaruhi angka.
