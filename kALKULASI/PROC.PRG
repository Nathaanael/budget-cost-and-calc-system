procedure BORDER
parameter BM,JUDUL,PROG
   clear
   @  0,0  say BM
   @  0,63 say cmonth(date())+' '+str(day(date()),2)+','+str(year(date()),4)
   @  1,70 say PROG
   SET COLOR TO B/W
   @  2,(80-len(JUDUL)-12)/2 say '  **  '+JUDUL+'  **  '
   SET COLOR TO B
   @  3,0 say repli(CHR(196),80)
   @ 22,0 say repli(CHR(196),80)
   SET COLOR TO W
return

procedure MESAGE
parameter PESAN
   set color to R/W
   @ 24,0 say '  '+PESAN+'  '
   set console off
   wait
   set console on
   SET COLOR TO W
   @ 24,0 say space(len(PESAN))+'     '
return

procedure FILE1
   select 1
   use BAHANB inde BAHANB alias BAHAN
   select 2
   use PRODUCTB inde PRODUCTB alias PRODUCT
   select 3
   use FORMULAB inde FORMULAB alias FORMULA
   select 4
   use CTRANB inde CTRANB alias CTRAN
   select 5
   use CPRDB inde CPRDB alias CPRD
return

procedure FILE2
   select 1
   use BAHANM inde BAHANM alias BAHAN
   select 2
   use PRODUCTM inde PRODUCTM alias  PRODUCT
   select 3
   use FORMULAM inde FORMULAM alias FORMULA
   select 4
   use CTRANM inde CTRANM alias CTRAN
   SELECT 5
   USE CPRDM INDE CPRDM alias CPRD
return

procedure FILE3
   select 1
   use BAHANF inde BAHANF alias BAHAN
   select 2
   use PRODUCTF inde PRODUCTF alias  PRODUCT
   select 3
   use FORMULAF inde FORMULAF alias FORMULA
   select 4
   use CTRANF inde CTRANF alias CTRAN
   SELECT 5
   USE CPRDF INDE CPRDF alias CPRD
return

procedure PASS
parameters PROG,XYZ
   set color to R+*
   @ 15,32 say 'P A S S W O R D'
   set color to N/N,N
   store space(15) to XYZ
   @ 16,32 get XYZ pict '@!'
   read
   set color to
   if XYZ <> 'COSTING '+PROG
      ? chr(7)
      ? chr(7)
      XYZ = '1'
   endif
return



