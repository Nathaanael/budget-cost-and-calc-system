# Inventaris DBF aktual

Dihasilkan oleh `docs/tools/inspect_legacy.py`. DBF hanya dibaca; record deleted tidak masuk pemeriksaan relasi. Teks didekode sementara sebagai CP437; encoding produksi perlu dikonfirmasi. Header field bersifat faktual, makna bisnis dibahas terpisah.

| File | Record header | Aktif | Deleted | Invalid/truncated | Kolom |
|---|---:|---:|---:|---:|---:|
| AREA.DBF | 18 | 18 | 0 | 0 | 2 |
| BMK.DBF | 50 | 50 | 0 | 0 | 7 |
| BP.DBF | 622 | 622 | 0 | 0 | 19 |
| COBA.DBF | 29 | 29 | 0 | 0 | 11 |
| COSTREF.DBF | 1 | 1 | 0 | 0 | 29 |
| FACTORY.DBF | 25 | 25 | 0 | 0 | 3 |
| FG.DBF | 935 | 935 | 0 | 0 | 20 |
| FGLE02.DBF | 636 | 636 | 0 | 0 | 3 |
| FGMAST - Copy.DBF | 1743 | 1743 | 0 | 0 | 48 |
| FGMAST.DBF | 2762 | 2762 | 0 | 0 | 48 |
| FGMAST1.DBF | 1614 | 1612 | 2 | 0 | 22 |
| fgpe.dbf | 1176 | 1176 | 0 | 0 | 37 |
| FGX.DBF | 518 | 518 | 0 | 0 | 26 |
| FORM.DBF | 9310 | 9299 | 11 | 0 | 11 |
| FORMULA.DBF | 35518 | 35518 | 0 | 0 | 11 |
| FOXUSER.DBF | 5 | 5 | 0 | 0 | 7 |
| FRML.DBF | 35518 | 35518 | 0 | 0 | 11 |
| FRML1.DBF | 35518 | 35518 | 0 | 0 | 11 |
| GABUNG.DBF | 11404 | 11404 | 0 | 0 | 21 |
| GABUNG1.DBF | 90138 | 90138 | 0 | 0 | 20 |
| GABUNG2.DBF | 78734 | 0 | 78734 | 0 | 20 |
| HASIL.DBF | 34831 | 34831 | 0 | 0 | 23 |
| HASIL1.DBF | 8001 | 8001 | 0 | 0 | 23 |
| mcdsap.dbf | 2915 | 2915 | 0 | 0 | 13 |
| NDLFORM.DBF | 2905 | 2905 | 0 | 0 | 4 |
| NDLMAST.DBF | 1447 | 1447 | 0 | 0 | 3 |
| PE0705LE.dbf | 612 | 612 | 0 | 0 | 5 |
| RM.DBF | 657 | 657 | 0 | 0 | 19 |
| RMAMT.DBF | 804 | 804 | 0 | 0 | 15 |
| RMLE02.DBF | 356 | 356 | 0 | 0 | 3 |
| RMMAST.DBF | 2390 | 2390 | 0 | 0 | 20 |
| RMQTY.DBF | 0 | 0 | 0 | 0 | 15 |
| RMVALUE.DBF | 174 | 174 | 0 | 0 | 15 |
| SLSVALUE.DBF | 170 | 170 | 0 | 0 | 15 |
| SYNONIM.DBF | 1118 | 1118 | 0 | 0 | 6 |
| TEMPFORM.DBF | 30 | 30 | 0 | 0 | 5 |
| TEMPNDL.DBF | 10 | 10 | 0 | 0 | 3 |
| TEST.DBF | 1673 | 1673 | 0 | 0 | 40 |
| TYPEMAST.DBF | 47 | 47 | 0 | 0 | 2 |
| VOL.DBF | 370 | 370 | 0 | 0 | 18 |
| VOL03.DBF | 2131 | 2131 | 0 | 0 | 14 |
| VOL1.DBF | 0 | 0 | 0 | 0 | 24 |
| VOLBP.dbf | 807 | 807 | 0 | 0 | 20 |
| VOLNDL.DBF | 1293 | 1293 | 0 | 0 | 22 |
| VOLNDL1.dbf | 1293 | 1293 | 0 | 0 | 20 |
| VOLNDL10.DBF | 370 | 370 | 0 | 0 | 22 |
| VOLNDL2.dbf | 934 | 934 | 0 | 0 | 20 |
| VOLNDLLE.dbf | 835 | 835 | 0 | 0 | 20 |
| VOLUME.DBF | 2597 | 2597 | 0 | 0 | 25 |
| YC06.dbf | 0 | 0 | 0 | 0 | 4 |
| YC061203.DBF | 0 | 0 | 0 | 0 | 4 |
| yc061204.dbf | 0 | 0 | 0 | 0 | 5 |
| yc061205.dbf | 0 | 0 | 0 | 0 | 5 |
| YC06TEST.DBF | 47174 | 47174 | 0 | 0 | 20 |
| YC06TEST1.DBF | 42964 | 42964 | 0 | 0 | 20 |

## Kamus seluruh kolom

C = karakter; N = numerik; D = tanggal; L = logical; M = referensi memo. Panjang adalah byte pada DBF, bukan ukuran tipe SQL. Tidak ada primary key/foreign key SQL yang dideklarasikan DBF.

### AREA.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 2 | 0 |
| DESC | C | 20 | 0 |

### BMK.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ID | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| BATCH | N | 10 | 0 |
| SEQ | N | 2 | 0 |
| RMCODE | C | 7 | 0 |
| STANDARDA | N | 15 | 10 |
| STANDARDB | N | 15 | 10 |

### BP.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| TYPE | C | 2 | 0 |
| RMCODE | C | 7 | 0 |
| ID | C | 2 | 0 |
| DESC | C | 30 | 0 |
| UNIT | C | 5 | 0 |
| TYPE_CURR | C | 1 | 0 |
| USD0 | N | 12 | 2 |
| USDLE | N | 12 | 2 |
| USD1 | N | 12 | 2 |
| USD2 | N | 12 | 2 |
| USD3 | N | 12 | 2 |
| USD4 | N | 12 | 2 |
| RP0 | N | 12 | 2 |
| RPLE | N | 12 | 2 |
| RP1 | N | 12 | 2 |
| RP2 | N | 12 | 2 |
| RP3 | N | 12 | 2 |
| RP4 | N | 12 | 2 |
| WASTE | N | 10 | 5 |

### COBA.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ID | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| BATCH | C | 10 | 0 |
| SEQ | C | 2 | 0 |
| RMCODE | C | 7 | 0 |
| RMLAMA | C | 7 | 0 |
| STANDARDA | C | 15 | 10 |
| STANDARDB | C | 15 | 10 |
| FGLAMA1 | C | 7 | 0 |
| RMLAMA1 | C | 7 | 0 |

### COSTREF.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 2 | 0 |
| DESC1 | C | 30 | 0 |
| DESC2 | C | 30 | 0 |
| PER | C | 8 | 0 |
| PERIOD | C | 15 | 0 |
| RATE0 | N | 15 | 2 |
| RATELE | N | 15 | 2 |
| RATE1 | N | 15 | 2 |
| RATE2 | N | 15 | 2 |
| RATE3 | N | 15 | 2 |
| RATE4 | N | 15 | 2 |
| PE00 | N | 10 | 2 |
| PELE | N | 10 | 2 |
| PE01 | N | 10 | 2 |
| PE02 | N | 10 | 2 |
| PE03 | N | 10 | 2 |
| PE04 | N | 10 | 2 |
| PE10 | N | 10 | 2 |
| PELE1 | N | 10 | 2 |
| PE11 | N | 10 | 2 |
| PE12 | N | 10 | 2 |
| PE13 | N | 10 | 2 |
| PE14 | N | 10 | 2 |
| PE20 | N | 10 | 2 |
| PELE2 | N | 10 | 2 |
| PE21 | N | 10 | 2 |
| PE22 | N | 10 | 2 |
| PE23 | N | 10 | 2 |
| PE24 | N | 10 | 2 |

### FACTORY.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 2 | 0 |
| DESC | C | 20 | 0 |
| AREA | C | 30 | 0 |

### FG.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ACTIVE | C | 1 | 0 |
| LEVEL | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| DESC | C | 30 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| BATCH | N | 9 | 0 |
| HRGJUAL | N | 10 | 2 |
| UC0 | N | 10 | 2 |
| UCLE | N | 10 | 2 |
| UC1 | N | 10 | 2 |
| UC2 | N | 10 | 2 |
| UC3 | N | 10 | 2 |
| UC4 | N | 10 | 2 |
| PRICE0 | N | 10 | 2 |
| PRICELE | N | 10 | 2 |
| PRICE1 | N | 10 | 2 |
| PRICE2 | N | 10 | 2 |
| PRICE3 | N | 10 | 2 |
| PRICE4 | N | 10 | 2 |

### FGLE02.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| FGCODE | C | 7 | 0 |
| DESC | C | 30 | 0 |
| PRICELE | N | 10 | 2 |

### FGMAST - Copy.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ACTIVE | C | 1 | 0 |
| LEVEL | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| DESC | C | 30 | 0 |
| DESC1 | C | 30 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| BATCH | N | 9 | 0 |
| HRGJUAL | N | 10 | 2 |
| HRGCKP | N | 8 | 2 |
| HRGSMG | N | 8 | 2 |
| HRGSBY | N | 8 | 2 |
| HRGPLG | N | 8 | 2 |
| PECKP | N | 8 | 2 |
| PESMG | N | 8 | 2 |
| PESBY | N | 8 | 2 |
| PEPLG | N | 8 | 2 |
| UC0 | N | 10 | 2 |
| UCLE | N | 10 | 2 |
| UC1 | N | 10 | 2 |
| UC2 | N | 10 | 2 |
| UC3 | N | 10 | 2 |
| UC4 | N | 10 | 2 |
| PRICE00 | N | 10 | 2 |
| PRICELE | N | 10 | 2 |
| PRICE01 | N | 10 | 2 |
| PRICE02 | N | 10 | 2 |
| PRICE03 | N | 10 | 2 |
| PRICE04 | N | 10 | 2 |
| PRICE10 | N | 10 | 2 |
| PRICELE1 | N | 10 | 2 |
| PRICE11 | N | 10 | 2 |
| PRICE12 | N | 10 | 2 |
| PRICE13 | N | 10 | 2 |
| PRICE14 | N | 10 | 2 |
| PRICE20 | N | 10 | 2 |
| PRICELE2 | N | 10 | 2 |
| PRICE21 | N | 10 | 2 |
| PRICE22 | N | 10 | 2 |
| PRICE23 | N | 10 | 2 |
| PRICE24 | N | 10 | 2 |
| PRICE30 | N | 10 | 2 |
| PRICELE3 | N | 10 | 2 |
| PRICE31 | N | 10 | 2 |
| PRICE32 | N | 10 | 2 |
| PRICE33 | N | 10 | 2 |
| PRICE34 | N | 10 | 2 |

### FGMAST.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ACTIVE | C | 1 | 0 |
| LEVEL | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| DESC | C | 30 | 0 |
| DESC1 | C | 30 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| BATCH | N | 9 | 0 |
| HRGJUAL | N | 10 | 2 |
| HRGCKP | N | 8 | 2 |
| HRGSMG | N | 8 | 2 |
| HRGSBY | N | 8 | 2 |
| HRGPLG | N | 8 | 2 |
| PECKP | N | 8 | 2 |
| PESMG | N | 8 | 2 |
| PESBY | N | 8 | 2 |
| PEPLG | N | 8 | 2 |
| UC0 | N | 10 | 2 |
| UCLE | N | 10 | 2 |
| UC1 | N | 10 | 2 |
| UC2 | N | 10 | 2 |
| UC3 | N | 10 | 2 |
| UC4 | N | 10 | 2 |
| PRICE00 | N | 10 | 2 |
| PRICELE | N | 10 | 2 |
| PRICE01 | N | 10 | 2 |
| PRICE02 | N | 10 | 2 |
| PRICE03 | N | 10 | 2 |
| PRICE04 | N | 10 | 2 |
| PRICE10 | N | 10 | 2 |
| PRICELE1 | N | 10 | 2 |
| PRICE11 | N | 10 | 2 |
| PRICE12 | N | 10 | 2 |
| PRICE13 | N | 10 | 2 |
| PRICE14 | N | 10 | 2 |
| PRICE20 | N | 10 | 2 |
| PRICELE2 | N | 10 | 2 |
| PRICE21 | N | 10 | 2 |
| PRICE22 | N | 10 | 2 |
| PRICE23 | N | 10 | 2 |
| PRICE24 | N | 10 | 2 |
| PRICE30 | N | 10 | 2 |
| PRICELE3 | N | 10 | 2 |
| PRICE31 | N | 10 | 2 |
| PRICE32 | N | 10 | 2 |
| PRICE33 | N | 10 | 2 |
| PRICE34 | N | 10 | 2 |

### FGMAST1.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| FGCODE | C | 7 | 0 |
| DESC | C | 30 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| BATCH | N | 9 | 0 |
| HRGJUAL | N | 10 | 2 |
| PE0 | N | 10 | 2 |
| PE1 | N | 10 | 2 |
| PE2 | N | 10 | 2 |
| PE3 | N | 10 | 2 |
| PE4 | N | 10 | 2 |
| UC0 | N | 10 | 2 |
| UC1 | N | 10 | 2 |
| UC2 | N | 10 | 2 |
| UC3 | N | 10 | 2 |
| UC4 | N | 10 | 2 |
| PRICE0 | N | 10 | 2 |
| PRICELE | N | 10 | 2 |
| PRICE1 | N | 10 | 2 |
| PRICE2 | N | 10 | 2 |
| PRICE3 | N | 10 | 2 |
| PRICE4 | N | 10 | 2 |

### fgpe.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ACTIVE | C | 1 | 0 |
| LEVEL | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| DESC | C | 30 | 0 |
| DESC1 | C | 30 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| BATCH | N | 9 | 0 |
| CKP | N | 10 | 2 |
| SMG | N | 10 | 2 |
| SBY | N | 10 | 2 |
| HRGJUAL | N | 10 | 2 |
| UC0 | N | 10 | 2 |
| UCLE | N | 10 | 2 |
| UC1 | N | 10 | 2 |
| UC2 | N | 10 | 2 |
| UC3 | N | 10 | 2 |
| UC4 | N | 10 | 2 |
| PRICE00 | N | 10 | 2 |
| PRICELE | N | 10 | 2 |
| PRICE01 | N | 10 | 2 |
| PRICE02 | N | 10 | 2 |
| PRICE03 | N | 10 | 2 |
| PRICE04 | N | 10 | 2 |
| PRICE10 | N | 10 | 2 |
| PRICELE1 | N | 10 | 2 |
| PRICE11 | N | 10 | 2 |
| PRICE12 | N | 10 | 2 |
| PRICE13 | N | 10 | 2 |
| PRICE14 | N | 10 | 2 |
| PRICE20 | N | 10 | 2 |
| PRICELE2 | N | 10 | 2 |
| PRICE21 | N | 10 | 2 |
| PRICE22 | N | 10 | 2 |
| PRICE23 | N | 10 | 2 |
| PRICE24 | N | 10 | 2 |

### FGX.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ACTIVE | C | 1 | 0 |
| ACT | C | 1 | 0 |
| LEVEL | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| DESC | C | 30 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| BATCH | N | 9 | 0 |
| HRGJUAL | N | 10 | 2 |
| UC0 | N | 10 | 2 |
| UCLE | N | 10 | 2 |
| UC1 | N | 10 | 2 |
| UC2 | N | 10 | 2 |
| UC3 | N | 10 | 2 |
| UC4 | N | 10 | 2 |
| PRICE0 | N | 10 | 2 |
| PRICELE | N | 10 | 2 |
| PRICE1 | N | 10 | 2 |
| PRICE2 | N | 10 | 2 |
| PRICE3 | N | 10 | 2 |
| PRICE4 | N | 10 | 2 |
| U1 | N | 10 | 2 |
| U2 | N | 10 | 2 |
| U3 | N | 10 | 2 |
| U4 | N | 10 | 2 |
| P | N | 10 | 2 |

### FORM.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ID | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| BATCH | N | 10 | 0 |
| SEQ | N | 2 | 0 |
| RMCODE | C | 7 | 0 |
| RMLAMA | C | 7 | 0 |
| STANDARDA | N | 15 | 10 |
| STANDARDB | N | 15 | 10 |
| FGLAMA1 | C | 7 | 0 |
| RMLAMA1 | C | 7 | 0 |

### FORMULA.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ID | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| BATCH | C | 10 | 0 |
| SEQ | N | 2 | 0 |
| RMCODE | C | 7 | 0 |
| RMLAMA | C | 7 | 0 |
| STANDARDA | N | 15 | 10 |
| STANDARDB | N | 15 | 10 |
| FGLAMA1 | C | 7 | 0 |
| RMLAMA1 | C | 7 | 0 |

### FOXUSER.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| TYPE | C | 12 | 0 |
| ID | C | 12 | 0 |
| NAME | C | 24 | 0 |
| READONLY | L | 1 | 0 |
| CKVAL | N | 6 | 0 |
| DATA | M | 10 | 0 |
| UPDATED | D | 8 | 0 |

### FRML.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ID | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| BATCH | C | 10 | 0 |
| SEQ | N | 2 | 0 |
| RMCODE | C | 7 | 0 |
| RMLAMA | C | 7 | 0 |
| STANDARDA | N | 15 | 10 |
| STANDARDB | N | 15 | 10 |
| FGLAMA1 | C | 7 | 0 |
| RMLAMA1 | C | 7 | 0 |

### FRML1.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ID | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| BATCH | C | 10 | 0 |
| SEQ | N | 2 | 0 |
| RMCODE | C | 7 | 0 |
| RMLAMA | C | 7 | 0 |
| STANDARDA | N | 15 | 10 |
| STANDARDB | N | 15 | 10 |
| FGLAMA1 | C | 7 | 0 |
| RMLAMA1 | C | 7 | 0 |

### GABUNG.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| A | C | 5 | 0 |
| B | C | 50 | 0 |
| C | C | 49 | 0 |
| D | N | 14 | 2 |
| E | N | 10 | 2 |
| F | N | 14 | 2 |
| G | N | 14 | 2 |
| H | N | 14 | 2 |
| I | N | 4 | 2 |
| J | N | 14 | 2 |
| K | N | 10 | 2 |
| L | N | 14 | 2 |
| M | N | 7 | 2 |
| N | N | 14 | 2 |
| O | N | 10 | 2 |
| P | N | 9 | 2 |
| Q | N | 9 | 2 |
| R | C | 9 | 0 |
| S | C | 9 | 0 |
| T | N | 16 | 2 |
| U | C | 19 | 0 |

### GABUNG1.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| A | C | 5 | 0 |
| B | C | 50 | 0 |
| C | C | 49 | 0 |
| D | N | 14 | 2 |
| E | N | 10 | 2 |
| F | N | 14 | 2 |
| G | N | 14 | 2 |
| H | N | 14 | 2 |
| I | N | 4 | 2 |
| J | N | 14 | 2 |
| K | N | 10 | 2 |
| L | N | 14 | 2 |
| M | N | 7 | 2 |
| N | N | 14 | 2 |
| O | N | 10 | 2 |
| P | N | 9 | 2 |
| Q | N | 9 | 2 |
| R | C | 9 | 0 |
| S | C | 9 | 0 |
| T | C | 19 | 0 |

### GABUNG2.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| A | C | 5 | 0 |
| B | C | 50 | 0 |
| C | C | 49 | 0 |
| D | N | 14 | 2 |
| E | N | 10 | 2 |
| F | N | 14 | 2 |
| G | N | 14 | 2 |
| H | N | 14 | 2 |
| I | N | 4 | 2 |
| J | N | 14 | 2 |
| K | N | 10 | 2 |
| L | N | 14 | 2 |
| M | N | 7 | 2 |
| N | N | 14 | 2 |
| O | N | 10 | 2 |
| P | N | 9 | 2 |
| Q | N | 9 | 2 |
| R | C | 9 | 0 |
| S | C | 9 | 0 |
| T | C | 19 | 0 |

### HASIL.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| A | C | 5 | 0 |
| B | C | 50 | 0 |
| C | C | 49 | 0 |
| D | N | 15 | 2 |
| E | N | 10 | 2 |
| F | N | 14 | 2 |
| G | N | 14 | 2 |
| H | N | 15 | 2 |
| I | N | 4 | 2 |
| J | N | 14 | 2 |
| K | N | 10 | 2 |
| L | N | 14 | 2 |
| M | N | 7 | 2 |
| N | N | 14 | 2 |
| O | N | 10 | 2 |
| P | N | 9 | 2 |
| Q | N | 9 | 2 |
| R | C | 9 | 0 |
| S | C | 9 | 0 |
| T | C | 19 | 0 |
| U | N | 14 | 2 |
| V | N | 14 | 2 |
| GABU | C | 18 | 0 |

### HASIL1.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| A | C | 5 | 0 |
| B | C | 50 | 0 |
| C | C | 49 | 0 |
| D | N | 15 | 2 |
| E | N | 10 | 2 |
| F | N | 14 | 2 |
| G | N | 14 | 2 |
| H | N | 15 | 2 |
| I | N | 4 | 2 |
| J | N | 14 | 2 |
| K | N | 10 | 2 |
| L | N | 14 | 2 |
| M | N | 7 | 2 |
| N | N | 14 | 2 |
| O | N | 10 | 2 |
| P | N | 9 | 2 |
| Q | N | 9 | 2 |
| R | C | 9 | 0 |
| S | C | 9 | 0 |
| T | C | 19 | 0 |
| U | N | 14 | 2 |
| V | N | 14 | 2 |
| GABU | C | 18 | 0 |

### mcdsap.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 7 | 0 |
| CODE1 | C | 7 | 0 |
| CODE2 | C | 1 | 0 |
| CODE3 | C | 1 | 0 |
| CODE4 | C | 1 | 0 |
| GROUP | C | 26 | 0 |
| NAMA | C | 48 | 0 |
| NEW_NAME_C | C | 29 | 0 |
| UNIT | C | 7 | 0 |
| BEGQTY | N | 17 | 3 |
| BEGAMT | N | 17 | 3 |
| ENDQTY | N | 17 | 3 |
| ENDAMT | N | 17 | 3 |

### NDLFORM.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| NDLCODE | C | 7 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| STANDARD | N | 15 | 10 |

### NDLMAST.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| NDLCODE | C | 7 | 0 |
| DESC | C | 30 | 0 |
| UNIT | C | 10 | 0 |

### PE0705LE.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| FGCODE | C | 7 | 0 |
| NAMA | C | 30 | 0 |
| CKP | N | 7 | 2 |
| SMG | N | 7 | 2 |
| SBY | N | 7 | 2 |

### RM.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| TYPE | C | 2 | 0 |
| RMCODE | C | 7 | 0 |
| ID | C | 2 | 0 |
| DESC | C | 30 | 0 |
| UNIT | C | 5 | 0 |
| TYPE_CURR | C | 1 | 0 |
| USD0 | N | 12 | 2 |
| USDLE | N | 12 | 2 |
| USD1 | N | 12 | 2 |
| USD2 | N | 12 | 2 |
| USD3 | N | 12 | 2 |
| USD4 | N | 12 | 2 |
| RP0 | N | 12 | 2 |
| RPLE | N | 12 | 2 |
| RP1 | N | 12 | 2 |
| RP2 | N | 12 | 2 |
| RP3 | N | 12 | 2 |
| RP4 | N | 12 | 2 |
| WASTE | N | 10 | 5 |

### RMAMT.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| RMCODE | C | 7 | 0 |
| DESC | C | 30 | 0 |
| JAN | N | 15 | 2 |
| FEB | N | 15 | 2 |
| MAR | N | 15 | 2 |
| APR | N | 15 | 2 |
| MEI | N | 15 | 2 |
| JUN | N | 15 | 2 |
| JUL | N | 15 | 2 |
| AGT | N | 15 | 2 |
| SEP | N | 15 | 2 |
| OKT | N | 15 | 2 |
| NOV | N | 15 | 2 |
| DES | N | 15 | 2 |
| TOTAL | N | 15 | 2 |

### RMLE02.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| RMCODE | C | 7 | 0 |
| DESC | C | 30 | 0 |
| PRICELE | N | 10 | 0 |

### RMMAST.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| TYPE | C | 6 | 0 |
| RMCODE | C | 7 | 0 |
| RMLAMA | C | 7 | 0 |
| ID | C | 2 | 0 |
| DESC | C | 43 | 0 |
| UNIT | C | 8 | 0 |
| TYPE_CURR | C | 1 | 0 |
| USD0 | N | 12 | 2 |
| USDLE | N | 12 | 2 |
| USD1 | N | 12 | 2 |
| USD2 | N | 12 | 2 |
| USD3 | N | 12 | 2 |
| USD4 | N | 12 | 2 |
| RP0 | N | 12 | 2 |
| RPLE | N | 12 | 2 |
| RP1 | N | 12 | 2 |
| RP2 | N | 12 | 2 |
| RP3 | N | 12 | 2 |
| RP4 | N | 12 | 2 |
| WASTE | N | 10 | 5 |

### RMQTY.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| RMCODE | C | 7 | 0 |
| DESC | C | 30 | 0 |
| JAN | N | 10 | 2 |
| FEB | N | 10 | 2 |
| MAR | N | 10 | 2 |
| APR | N | 10 | 2 |
| MEI | N | 10 | 2 |
| JUN | N | 10 | 2 |
| JUL | N | 10 | 2 |
| AGT | N | 10 | 2 |
| SEP | N | 10 | 2 |
| OKT | N | 10 | 2 |
| NOV | N | 10 | 2 |
| DES | N | 10 | 2 |
| TOTAL | N | 12 | 2 |

### RMVALUE.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| FGCODE | C | 7 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| JAN | N | 15 | 2 |
| FEB | N | 15 | 2 |
| MAR | N | 15 | 2 |
| APR | N | 15 | 2 |
| MEI | N | 15 | 2 |
| JUN | N | 15 | 2 |
| JUL | N | 15 | 2 |
| AGT | N | 15 | 2 |
| SEP | N | 15 | 2 |
| OKT | N | 15 | 2 |
| NOV | N | 15 | 2 |
| DES | N | 15 | 2 |

### SLSVALUE.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| FGCODE | C | 7 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| JAN | N | 15 | 2 |
| FEB | N | 15 | 2 |
| MAR | N | 15 | 2 |
| APR | N | 15 | 2 |
| MEI | N | 15 | 2 |
| JUN | N | 15 | 2 |
| JUL | N | 15 | 2 |
| AGT | N | 15 | 2 |
| SEP | N | 15 | 2 |
| OKT | N | 15 | 2 |
| NOV | N | 15 | 2 |
| DES | N | 15 | 2 |

### SYNONIM.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| RMCODE | C | 7 | 0 |
| RMLAMA | C | 7 | 0 |
| RMDESC | C | 30 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| FGDESC | C | 30 | 0 |

### TEMPFORM.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| RMCODE | C | 7 | 0 |
| DESC | C | 25 | 0 |
| BATCH | N | 8 | 0 |
| STANDARDA | N | 15 | 8 |
| STANDARDB | N | 15 | 8 |

### TEMPNDL.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| FGCODE | C | 7 | 0 |
| DESC | C | 25 | 0 |
| STANDARD | N | 15 | 10 |

### TEST.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| ACTIVE | C | 1 | 0 |
| LEVEL | C | 1 | 0 |
| FGCODE | C | 7 | 0 |
| FGLAMA | C | 7 | 0 |
| DESC | C | 30 | 0 |
| DESC1 | C | 30 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| BATCH | N | 9 | 0 |
| HRGJUAL | N | 10 | 2 |
| HRGCKP | N | 8 | 2 |
| HRGSMG | N | 8 | 2 |
| HRGSBY | N | 8 | 2 |
| PECKP | N | 8 | 2 |
| PESMG | N | 8 | 2 |
| PESBY | N | 8 | 2 |
| UC0 | N | 10 | 2 |
| UCLE | N | 10 | 2 |
| UC1 | N | 10 | 2 |
| UC2 | N | 10 | 2 |
| UC3 | N | 10 | 2 |
| UC4 | N | 10 | 2 |
| PRICE00 | N | 10 | 2 |
| PRICELE | N | 10 | 2 |
| PRICE01 | N | 10 | 2 |
| PRICE02 | N | 10 | 2 |
| PRICE03 | N | 10 | 2 |
| PRICE04 | N | 10 | 2 |
| PRICE10 | N | 10 | 2 |
| PRICELE1 | N | 10 | 2 |
| PRICE11 | N | 10 | 2 |
| PRICE12 | N | 10 | 2 |
| PRICE13 | N | 10 | 2 |
| PRICE14 | N | 10 | 2 |
| PRICE20 | N | 10 | 2 |
| PRICELE2 | N | 10 | 2 |
| PRICE21 | N | 10 | 2 |
| PRICE22 | N | 10 | 2 |
| PRICE23 | N | 10 | 2 |
| PRICE24 | N | 10 | 2 |

### TYPEMAST.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| PRTYPE1 | C | 2 | 0 |
| DESC | C | 30 | 0 |

### VOL.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 2 | 0 |
| NDLCODE | C | 7 | 0 |
| LESEP | N | 10 | 0 |
| LEOKT | N | 10 | 0 |
| LENOV | N | 10 | 0 |
| LEDES | N | 10 | 0 |
| JAN | N | 10 | 0 |
| FEB | N | 10 | 0 |
| MAR | N | 10 | 0 |
| APR | N | 10 | 0 |
| MEI | N | 10 | 0 |
| JUN | N | 10 | 0 |
| JUL | N | 10 | 0 |
| AGT | N | 10 | 0 |
| SEP | N | 10 | 0 |
| OKT | N | 10 | 0 |
| NOV | N | 10 | 0 |
| DES | N | 10 | 0 |

### VOL03.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 2 | 0 |
| NDLCODE | C | 7 | 0 |
| JAN | N | 10 | 0 |
| FEB | N | 10 | 0 |
| MAR | N | 10 | 0 |
| APR | N | 10 | 0 |
| MEI | N | 10 | 0 |
| JUN | N | 10 | 0 |
| JUL | N | 10 | 0 |
| AGT | N | 10 | 0 |
| SEP | N | 10 | 0 |
| OKT | N | 10 | 0 |
| NOV | N | 10 | 0 |
| DES | N | 10 | 0 |

### VOL1.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 2 | 0 |
| FGCODE | C | 7 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| LEJUL | N | 10 | 0 |
| LEAGT | N | 10 | 0 |
| LESEP | N | 10 | 0 |
| LEOKT | N | 10 | 0 |
| LENOV | N | 10 | 0 |
| LEDES | N | 10 | 0 |
| TOTALLE | N | 10 | 0 |
| JAN | N | 10 | 0 |
| FEB | N | 10 | 0 |
| MAR | N | 10 | 0 |
| APR | N | 10 | 0 |
| MEI | N | 10 | 0 |
| JUN | N | 10 | 0 |
| JUL | N | 10 | 0 |
| AGT | N | 10 | 0 |
| SEP | N | 10 | 0 |
| OKT | N | 10 | 0 |
| NOV | N | 10 | 0 |
| DES | N | 10 | 0 |
| TOTAL | N | 12 | 0 |

### VOLBP.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 7 | 0 |
| NDLCODE | C | 11 | 0 |
| LEJUL | N | 12 | 0 |
| LEAGT | N | 12 | 0 |
| LESEP | N | 12 | 0 |
| LEOKT | N | 12 | 0 |
| LENOV | N | 12 | 0 |
| LEDES | N | 12 | 0 |
| JAN | N | 14 | 0 |
| FEB | N | 13 | 0 |
| MAR | N | 13 | 0 |
| APR | N | 13 | 0 |
| MEI | N | 13 | 0 |
| JUN | N | 12 | 0 |
| JUL | N | 12 | 0 |
| AGT | N | 12 | 0 |
| SEP | N | 12 | 0 |
| OKT | N | 12 | 0 |
| NOV | N | 12 | 0 |
| DES | N | 12 | 0 |

### VOLNDL.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 2 | 0 |
| NDLCODE | C | 7 | 0 |
| LEJUL | N | 10 | 0 |
| LEAGT | N | 10 | 0 |
| LESEP | N | 10 | 0 |
| LEOKT | N | 10 | 0 |
| LENOV | N | 10 | 0 |
| LEDES | N | 10 | 0 |
| TOTALLE | N | 10 | 0 |
| JAN | N | 10 | 0 |
| FEB | N | 10 | 0 |
| MAR | N | 10 | 0 |
| APR | N | 10 | 0 |
| MEI | N | 10 | 0 |
| JUN | N | 10 | 0 |
| JUL | N | 10 | 0 |
| AGT | N | 10 | 0 |
| SEP | N | 10 | 0 |
| OKT | N | 10 | 0 |
| NOV | N | 10 | 0 |
| DES | N | 10 | 0 |
| TOTAL | N | 10 | 0 |

### VOLNDL1.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 6 | 0 |
| NDLCODE | C | 9 | 0 |
| LEJUL | N | 9 | 0 |
| LEAGT | N | 9 | 0 |
| LESEP | N | 9 | 0 |
| LEOKT | N | 9 | 0 |
| LENOV | N | 9 | 0 |
| LEDES | N | 9 | 0 |
| JAN | N | 9 | 0 |
| FEB | N | 9 | 0 |
| MAR | N | 9 | 0 |
| APR | N | 9 | 0 |
| MEI | N | 9 | 0 |
| JUN | N | 9 | 0 |
| JUL | N | 9 | 0 |
| AGT | N | 9 | 0 |
| SEP | N | 9 | 0 |
| OKT | N | 9 | 0 |
| NOV | N | 9 | 0 |
| DES | N | 9 | 0 |

### VOLNDL10.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 2 | 0 |
| NDLCODE | C | 7 | 0 |
| LEJUL | N | 10 | 0 |
| LEAGT | N | 10 | 0 |
| LESEP | N | 10 | 0 |
| LEOKT | N | 10 | 0 |
| LENOV | N | 10 | 0 |
| LEDES | N | 10 | 0 |
| TOTALLE | N | 10 | 0 |
| JAN | N | 10 | 0 |
| FEB | N | 10 | 0 |
| MAR | N | 10 | 0 |
| APR | N | 10 | 0 |
| MEI | N | 10 | 0 |
| JUN | N | 10 | 0 |
| JUL | N | 10 | 0 |
| AGT | N | 10 | 0 |
| SEP | N | 10 | 0 |
| OKT | N | 10 | 0 |
| NOV | N | 10 | 0 |
| DES | N | 10 | 0 |
| TOTAL | N | 10 | 0 |

### VOLNDL2.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 6 | 0 |
| NDLCODE | C | 9 | 0 |
| LEJUL | N | 9 | 0 |
| LEAGT | N | 9 | 0 |
| LESEP | N | 9 | 0 |
| LEOKT | N | 9 | 0 |
| LENOV | N | 9 | 0 |
| LEDES | N | 9 | 0 |
| JAN | N | 19 | 0 |
| FEB | N | 9 | 0 |
| MAR | N | 9 | 0 |
| APR | N | 9 | 0 |
| MEI | N | 9 | 0 |
| JUN | N | 9 | 0 |
| JUL | N | 9 | 0 |
| AGT | N | 9 | 0 |
| SEP | N | 9 | 0 |
| OKT | N | 16 | 0 |
| NOV | N | 17 | 0 |
| DES | N | 17 | 0 |

### VOLNDLLE.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 9 | 0 |
| NDLCODE | C | 9 | 0 |
| LEJUL | N | 9 | 0 |
| LEAGT | N | 9 | 0 |
| LESEP | N | 9 | 0 |
| LEOKT | N | 9 | 0 |
| LENOV | N | 9 | 0 |
| LEDES | N | 9 | 0 |
| JAN | N | 9 | 0 |
| FEB | N | 9 | 0 |
| MAR | N | 9 | 0 |
| APR | N | 9 | 0 |
| MEI | N | 9 | 0 |
| JUN | N | 9 | 0 |
| JUL | N | 9 | 0 |
| AGT | N | 9 | 0 |
| SEP | N | 9 | 0 |
| OKT | N | 9 | 0 |
| NOV | N | 9 | 0 |
| DES | N | 9 | 0 |

### VOLUME.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| CODE | C | 2 | 0 |
| FGCODE | C | 7 | 0 |
| DESC | C | 30 | 0 |
| PRTYPE1 | C | 2 | 0 |
| PRTYPE2 | C | 2 | 0 |
| LEJUL | N | 10 | 0 |
| LEAGT | N | 10 | 0 |
| LESEP | N | 10 | 0 |
| LEOKT | N | 10 | 0 |
| LENOV | N | 10 | 0 |
| LEDES | N | 10 | 0 |
| TOTALLE | N | 10 | 0 |
| JAN | N | 10 | 0 |
| FEB | N | 10 | 0 |
| MAR | N | 10 | 0 |
| APR | N | 10 | 0 |
| MEI | N | 10 | 0 |
| JUN | N | 10 | 0 |
| JUL | N | 10 | 0 |
| AGT | N | 10 | 0 |
| SEP | N | 10 | 0 |
| OKT | N | 10 | 0 |
| NOV | N | 10 | 0 |
| DES | N | 10 | 0 |
| TOTAL | N | 12 | 0 |

### YC06.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| KODE | C | 7 | 0 |
| MATERIAL | C | 44 | 0 |
| QTY | N | 16 | 2 |
| AMT | N | 17 | 2 |

### YC061203.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| KODE | C | 7 | 0 |
| MATERIAL | C | 44 | 0 |
| QTY | N | 16 | 2 |
| AMT | N | 17 | 2 |

### yc061204.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| KODE | C | 11 | 0 |
| MATERIAL | C | 32 | 0 |
| QTY | N | 15 | 2 |
| AMOUNT | N | 12 | 0 |
| AMT | N | 12 | 0 |

### yc061205.dbf

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| KODE | C | 11 | 0 |
| MATERIAL | C | 32 | 0 |
| QTY | N | 12 | 2 |
| AMOUNT | N | 12 | 0 |
| AMT | N | 12 | 0 |

### YC06TEST.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| A | C | 5 | 0 |
| B | C | 50 | 0 |
| C | C | 49 | 0 |
| D | N | 14 | 2 |
| E | N | 10 | 2 |
| F | N | 14 | 2 |
| G | N | 14 | 2 |
| H | N | 14 | 2 |
| I | N | 4 | 2 |
| J | N | 14 | 2 |
| K | N | 10 | 2 |
| L | N | 14 | 2 |
| M | N | 7 | 2 |
| N | N | 14 | 2 |
| O | N | 10 | 2 |
| P | N | 9 | 2 |
| Q | N | 9 | 2 |
| R | C | 9 | 0 |
| S | C | 9 | 0 |
| T | C | 19 | 0 |

### YC06TEST1.DBF

| Kolom | Tipe | Byte | Desimal |
|---|---|---:|---:|
| A | C | 5 | 0 |
| B | C | 60 | 0 |
| C | C | 49 | 0 |
| D | N | 15 | 2 |
| E | N | 10 | 2 |
| F | N | 14 | 2 |
| G | N | 14 | 2 |
| H | N | 15 | 2 |
| I | N | 4 | 2 |
| J | N | 14 | 2 |
| K | N | 10 | 2 |
| L | N | 14 | 2 |
| M | N | 7 | 2 |
| N | N | 14 | 2 |
| O | N | 10 | 2 |
| P | N | 9 | 2 |
| Q | N | 9 | 2 |
| R | C | 7 | 0 |
| S | C | 7 | 0 |
| T | C | 25 | 0 |
