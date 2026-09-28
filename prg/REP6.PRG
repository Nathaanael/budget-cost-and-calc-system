# include "Stock.ch"

function Report9()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey
     set key 19 to Lkey1
     sbox(15,64,21,78,"gr+/bg+,w+/br+")
     @ 16     ,65 prompt " Cikampek   "
     @ row()+1,65 prompt " Semarang   "
     @ row()+1,65 prompt " Surabaya   "
     @ row()+1,65 prompt " Palembang  "
     @ row()+1,65 prompt " National   "
     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         Report91(nSelect)
       case nSelect == 2
         Report91(nSelect)
       case nSelect == 3
         Report91(nSelect)
       case nSelect == 4
         Report91(nSelect)
       case nSelect == 5
         Report91(nSelect)
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil



function Report91(unit)
   local cPrint,cPrType2,cPrType1,cOption,cSum,cDesc,cId
   local cArea1,cArea2,cArea3,cArea4,cArea5
   local cArea6,cArea7,cArea8,cArea9,cArea10
   local bl1:=0,bl2:=0,bl3:=0,bl4:=0,bl5:=0,bl6:=0,bl7:=0,bl8:=0,bl9:=0,bl10:=0,bl11:=0,bl12:=0
   *local std,pakai01,pakai02,pakai03,pakai04,pakai05,pakai06
   *local pakai07,pakai08,pakai09,pakai10,pakai11,pakai12
   *local total01:=0,total02:=0,total03:=0,total04:=0,total05:=0,total06:=0
   *local total07:=0,total08:=0,total09:=0,total10:=0,total11:=0,total12:=0,total00:=0
   local gtotal01:=0,gtotal02:=0,gtotal03:=0,gtotal04:=0,gtotal05:=0,gtotal06:=0
   local gtotal07:=0,gtotal08:=0,gtotal09:=0,gtotal10:=0,gtotal11:=0,gtotal12:=0,gtotal00:=0
   local cRMCode  := space(7)
   local cRMCode1 := space(7)
   local cRMCode2 := space(7)
   local cFGCode
   local nWidth   := 271
   local nSW      := 0,Brs := 0
   local nPage    := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   store 0 to total01,total02,total03,total04,total05,total06,total07,total08,total09,total10,total11,total12,total00
   store 0 to std,pakai01,pakai02,pakai03,pakai04,pakai05,pakai06
   store 0 to pakai07,pakai08,pakai09,pakai10,pakai11,pakai12
   store 0 to nPrice1,nPrice2,nPrice3,nPrice4

   dataDict()
   set date British
   set score off
   do while .T.
      sbox( 17,40,20,62,"gr+/w+,w+/bg+" )
      @ 18,41 say " Code RM : " get cRMCode1 picture "@!"
      @ 19,41 say "      To : " get cRMCode2 picture "@!"
      read
      if lastkey() == K_ESC
         exit
      endif
      *cOption := alert ( 'LOCATION : ',{'CIKAMPEK','SEMARANG','SURABAYA','NATIONAL'})
      cOption := unit
      cSum    := alert ( 'OPTION : ',{'SUMMARY','DETAIL'})
      nDivide := alert ( 'Divided : ', {'1','100 ','1000', '1000000'} )

      cPrint := alert ( " Output Device  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "Alokasi.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(27)+chr(77)+chr(15)
      sele FORMULA
      set filter to Formula->RMCode >= cRMCode1 .and. Formula->RMCode <= cRMCode2
      go top
      do case
         case nDivide == 1
              nBagi := 1
              nBDesc := '1'
         case nDivide == 2
              nBagi := 100
              nBDesc := '100'
         case nDivide == 3
              nBagi := 1000
              nBDesc := '1000'
         case nDivide == 4
              nBagi := 1000000
              nBDesc := '1000000'
      endcase
      select Factory
      do case
         case cOption == 1
            loca for Factory->code = 'S1'
         case cOption == 2
            loca for Factory->code = 'S2'
         case cOption == 3
            loca for Factory->code = 'S3'
         case cOption == 4
            loca for Factory->code = 'S4'
         otherwise
            loca for Factory->code = 'S0'
      endcase
      cDesc := Factory->Desc
      cAREA1  = SUBS(Factory->AREA,01,2)
      IF cAREA1 # SPACE(2)
         cAREA1 = RTRIM(cAREA1)
      ENDIF
      cAREA2  = SUBS(Factory->AREA,04,2)
      IF cAREA2 # SPACE(2)
         cAREA2 = RTRIM(cAREA2)
      ENDIF
      cAREA3  = SUBS(Factory->AREA,07,2)
      IF cAREA3 # SPACE(2)
         cAREA3 = RTRIM(cAREA3)
      ENDIF
      cAREA4  = SUBS(Factory->AREA,10,2)
      IF cAREA4 # SPACE(2)
         cAREA4 = RTRIM(cAREA4)
      ENDIF
      cAREA5  = SUBS(Factory->AREA,13,2)
      IF cAREA5 # SPACE(2)
         cAREA5 = RTRIM(cAREA5)
      ENDIF
      cAREA6  = SUBS(Factory->AREA,16,2)
      IF cAREA6 # SPACE(2)
         cAREA6 = RTRIM(cAREA6)
      ENDIF
      cAREA7  = SUBS(Factory->AREA,19,2)
      IF cAREA7 # SPACE(2)
         cAREA7 = RTRIM(cAREA7)
      ENDIF
      cAREA8  = SUBS(Factory->AREA,22,2)
      IF cAREA8 # SPACE(2)
         cAREA8 = RTRIM(cAREA8)
      ENDIF
      cAREA9  = SUBS(Factory->AREA,25,2)
      IF cAREA9 # SPACE(2)
         cAREA9 = RTRIM(cAREA9)
      ENDIF
      cAREA10 = SUBS(Factory->AREA,28,2)
      IF cAREA10 # SPACE(2)
         cAREA10 = RTRIM(cAREA10)
      ENDIF
      sele RMQty
      zap
      sele Volume
      set filter to Volume->Code=cAREA1.OR.Volume->Code=cAREA2.OR.Volume->Code=cAREA3.OR.Volume->Code=cAREA4.OR.Volume->Code=cAREA5;
      .OR.Volume->Code=cAREA6.OR.Volume->Code=cAREA7.OR.Volume->Code=cAREA8.OR.Volume->Code=cAREA9.OR.Volume->Code=cAREA10
      sele Formula
      Formula->( dbsetOrder(2) )
      Formula->( dbGoTop() )
      do while ! Formula->( Eof() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
         if BRS = 0 .or. BRS > 55
            *@ 0,0 say chr(15)
            @ 1,1  SAY CostRef->Desc1
            @ 2,1  SAY CostRef->Desc2
            @ 2,100 SAY 'ALOKASI PEMAKAIAN RM ( AMOUNT )'
            @ 3,100 SAY '-------------------------------'
            @ 3,255 say "In ' "
            @ 3,pcol() say nBDesc picture "@!"
            *                    1         2         3         4         5         6         7         8         9        10         1         2         3         4         5         6         7         8         9        20         1         2         3
            *          012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123
            @  4,1 say cdesc picture "@!"
            nPage++
            @  4,255 say 'Page : '
            @  4,263 say nPage picture "999"
            @  5,1 SAY repli('-',nWidth)
            @  6,1 SAY '    BAHAN BAKU           :    FINISHED GOODS      :       JAN      :       FEB      :      MAR       :       APR      :      MEI       :      JUN       :       JUL      :      AGT       :       SEP      :       OKT      :'
            @ 6,pcol() say'       NOV      :       DES      :     TOTAL     :'
            @  7,1 SAY repli('=',nWidth)
            *            xxxxxxx xxxxxxxxxxxxxxx   xxxxxxx xxxxxxxxxxxxxxx  999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999
            BRS = 8
         endif
         cRMCode := Formula->RMCode
         sele RMMast
         set order to 1
         dbSeek(cRmCode)
         nPrice1 := RMMast->Rp1
         nPrice2 := RMMast->Rp2
         nPrice3 := RMMast->Rp3
         nPrice4 := RMMast->Rp4
         if cSum == 2
             do while Formula->RMCode == cRMCode .and. ! Formula->(eof())
                fFGCode := Formula->FGCode
                std     := Formula->Standardb
                sele volume
                set order to 3
                if dbseek( fFGCode ) 
                   AHtngVlmd( fFgCode,cRmCode,std,1,1 )
                else
                   sele frml
                   set order to 2
                   if dbseek( fFgCode ) 
                      fRmCode1 := Frml->RMCode
                      do while Frml->RMCode == fRmCode1 .and. !Frml->(eof())
                         fFgCode1 := Frml->FGCode
                         fstd     := Frml->Standardb
                         sele volume
                         set order to 3
                         if dbseek( fFgCode1 )
                            AHtngVlmD( fFgCode1,fRmCode1,std,fstd,1 )
                         else
                            sele frml1
                            set order to 2
                            if dbseek( fFgCode1 )
                               fRmCode2 := Frml1->RMCode
                               do while Frml1->RMCode == fRmCode2 .and. !Frml1->(eof())
                                  fFgCode2 := Frml1->FGCode
                                  fstd1    := Frml1->Standardb
                                  sele volume
                                  set order to 3
                                  if dbseek( fFgCode2 )
                                     AHtngVlmD( fFgCode2,fRmCode2,std,fstd,fstd1 )
                                  endif
                                  Frml1->( dbSkip() )
                               enddo
                            endif
                         endif
                         Frml->( dbSkip() )
                      enddo
                   endif
                endif
                total00 := total01+total02+total03+total04+total05+total06+total07+total08+total09+total10+total11+total12
                gtotal01 := gtotal01 + total01
                gtotal02 := gtotal02 + total02
                gtotal03 := gtotal03 + total03
                gtotal04 := gtotal04 + total04
                gtotal05 := gtotal05 + total05
                gtotal06 := gtotal06 + total06
                gtotal07 := gtotal07 + total07
                gtotal08 := gtotal08 + total08
                gtotal09 := gtotal09 + total09
                gtotal10 := gtotal10 + total10
                gtotal11 := gtotal11 + total11
                gtotal12 := gtotal12 + total12
                gtotal00 := gtotal01+gtotal02+gtotal03+gtotal04+gtotal05+gtotal06+gtotal07+gtotal08+gtotal09+gtotal10+gtotal11+gtotal12
                if total00 = 0
                   Formula->( dbskip() )
                   loop
                endif
                if nSw == 0
                   @ brs,2   say cRMCode picture "@!"
                   RMMast->( dbSeek(cRMCode) )
                   @ brs,10  say substr(RMMast->desc,1,15)  picture "@!"
                   nSw := 1
                endif
                @ brs,26  say ":"
                @ brs,28  say fFGCode      picture "@!"
                FGMast->( dbSeek( fFGCode) )
                @ brs,36  say substr(FGMast->desc,1,15)  picture "@!"
                @ brs,51  say ":"
                @ brs,53  say total01/nBagi     picture "@z 9,999,999,999"
                @ brs,68  say ":"
                @ brs,70  say total02/nBagi     picture "@z 9,999,999,999"
                @ brs,85  say ":"
                @ brs,87  say total03/nBagi     picture "@z 9,999,999,999"
                @ brs,102 say ":"
                @ brs,104 say total04/nBagi     picture "@z 9,999,999,999"
                @ brs,119 say ":"
                @ brs,121 say total05/nBagi     picture "@z 9,999,999,999"
                @ brs,136 say ":"
                @ brs,138 say total06/nBagi     picture "@z 9,999,999,999"
                @ brs,153 say ":"
                @ brs,155 say total07/nBagi     picture "@z 9,999,999,999"
                @ brs,170 say ":"
                @ brs,172 say total08/nBagi     picture "@z 9,999,999,999"
                @ brs,187 say ":"
                @ brs,189 say total09/nBagi     picture "@z 9,999,999,999"
                @ brs,204 say ":"
                @ brs,206 say total10/nBagi     picture "@z 9,999,999,999"
                @ brs,221 say ":"
                @ brs,223 say total11/nBagi     picture "@z 9,999,999,999"
                @ brs,238 say ":"
                @ brs,240 say total12/nBagi     picture "@z 9,999,999,999"
                @ brs,255 say ":"
                @ brs,257 say total00/nBagi     picture "@z 99,999,999,999"
                @ brs,271 say ":"
                store 0 to total01,total02,total03,total04,total05,total06
                store 0 to total07,total08,total09,total10,total11,total12,total00
                store 0 to bl1,bl2,bl3,bl4,bl5,bl6,bl7,bl8,bl9,bl10,bl11,bl12
                Formula->( dbSkip() )
                brs = brs + 1
                if brs > 55
                   @ brs,1 say repli('-',nWidth)
                   eject
                   nSw := 0
                   brs := 0
                   exit
                endif
             enddo
             if Formula->RMCode # cRMCode
                @ brs,1 say repli('-',nWidth)
                brs = brs + 1
                @ brs,10  say 'Total'
                @ brs,51  say ":"
                @ brs,53  say gtotal01/nBagi     picture "@z 9,999,999,999"
                @ brs,68  say ":"
                @ brs,70  say gtotal02/nBagi     picture "@z 9,999,999,999"
                @ brs,85  say ":"
                @ brs,87  say gtotal03/nBagi     picture "@z 9,999,999,999"
                @ brs,102 say ":"
                @ brs,104 say gtotal04/nBagi     picture "@z 9,999,999,999"
                @ brs,119 say ":"
                @ brs,121 say gtotal05/nBagi     picture "@z 9,999,999,999"
                @ brs,136 say ":"
                @ brs,138 say gtotal06/nBagi     picture "@z 9,999,999,999"
                @ brs,153 say ":"
                @ brs,155 say gtotal07/nBagi     picture "@z 9,999,999,999"
                @ brs,170 say ":"
                @ brs,172 say gtotal08/nBagi     picture "@z 9,999,999,999"
                @ brs,187 say ":"
                @ brs,189 say gtotal09/nBagi     picture "@z 9,999,999,999"
                @ brs,204 say ":"
                @ brs,206 say gtotal10/nBagi     picture "@z 9,999,999,999"
                @ brs,221 say ":"
                @ brs,223 say gtotal11/nBagi     picture "@z 9,999,999,999"
                @ brs,238 say ":"
                @ brs,240 say gtotal12/nBagi     picture "@z 9,999,999,999"
                @ brs,255 say ":"
                @ brs,257 say gtotal00/nBagi     picture "@z 99,999,999,999"
                @ brs,271 say ":"
                brs = brs + 1
                @ brs,1 say repli('-',nWidth)
                store 0 to gtotal01,gtotal02,gtotal03,gtotal04,gtotal05,gtotal06
                store 0 to gtotal07,gtotal08,gtotal09,gtotal10,gtotal11,gtotal12,gtotal00
                nSw := 0
                brs = brs + 1
             endif
             if Formula->( Eof() )
                exit
             endif
         else
             Ahtng1(cRMCode)
             total00 := total01+total02+total03+total04+total05+total06+total07+total08+total09+total10+total11+total12
             if total00 # 0
                @ brs,2   say cRmCode picture "@!"
                RMMast->( dbSeek( cRmCode ) )
                @ brs,10  say substr(RMMast->desc,1,15)  picture "@!"
                @ brs,26  say ":"
                @ brs,51  say ":"
                @ brs,53  say total01/nBagi     picture "@z 9,999,999,999"
                @ brs,68  say ":"
                @ brs,70  say total02/nBagi     picture "@z 9,999,999,999"
                @ brs,85  say ":"
                @ brs,87  say total03/nBagi     picture "@z 9,999,999,999"
                @ brs,102 say ":"
                @ brs,104 say total04/nBagi     picture "@z 9,999,999,999"
                @ brs,119 say ":"
                @ brs,121 say total05/nBagi     picture "@z 9,999,999,999"
                @ brs,136 say ":"
                @ brs,138 say total06/nBagi     picture "@z 9,999,999,999"
                @ brs,153 say ":"
                @ brs,155 say total07/nBagi     picture "@z 9,999,999,999"
                @ brs,170 say ":"
                @ brs,172 say total08/nBagi     picture "@z 9,999,999,999"
                @ brs,187 say ":"
                @ brs,189 say total09/nBagi     picture "@z 9,999,999,999"
                @ brs,204 say ":"
                @ brs,206 say total10/nBagi     picture "@z 9,999,999,999"
                @ brs,221 say ":"
                @ brs,223 say total11/nBagi     picture "@z 9,999,999,999"
                @ brs,238 say ":"
                @ brs,240 say total12/nBagi     picture "@z 9,999,999,999"
                @ brs,255 say ":"
                @ brs,257 say total00/nBagi     picture "@z 99,999,999,999"
                @ brs,271 say ":"
                brs = brs + 1
                sele RMAmt
                RMAmt->( dbAppend() )
                RMAmt->RMCode := cRMCode
                RMAmt->Desc   := RMMast->Desc
                RMAmt->Jan    := total01
                RMAmt->Feb    := total02
                RMAmt->Mar    := total03
                RMAmt->Apr    := total04
                RMAmt->Mei    := total05
                RMAmt->Jun    := total06
                RMAmt->Jul    := total07
                RMAmt->Agt    := total08
                RMAmt->Sep    := total09
                RMAmt->Okt    := total10
                RMAmt->Nov    := total11
                RMAmt->Des    := total12
                RMAmt->Total  := total00
             endif
             gtotal01 := gtotal01 + total01
             gtotal02 := gtotal02 + total02
             gtotal03 := gtotal03 + total03
             gtotal04 := gtotal04 + total04
             gtotal05 := gtotal05 + total05
             gtotal06 := gtotal06 + total06
             gtotal07 := gtotal07 + total07
             gtotal08 := gtotal08 + total08
             gtotal09 := gtotal09 + total09
             gtotal10 := gtotal10 + total10
             gtotal11 := gtotal11 + total11
             gtotal12 := gtotal12 + total12
             gtotal00 := gtotal01+gtotal02+gtotal03+gtotal04+gtotal05+gtotal06+gtotal07+gtotal08+gtotal09+gtotal10+gtotal11+gtotal12
             store 0 to total01,total02,total03,total04,total05,total06
             store 0 to total07,total08,total09,total10,total11,total12,total00
             if brs > 55
                @ brs,1 say repli('-',nWidth)
                eject
                brs := 0
             endif
             if Formula->(Eof())
                exit
             endif
         endif
      enddo
      if cSum == 1
         @ brs,1 say repli('-',nWidth)
         brs = brs + 1
         @ brs,10  say 'Total'
         @ brs,51  say ":"
         @ brs,53  say gtotal01/nBagi     picture "@z 9,999,999,999"
         @ brs,68  say ":"
         @ brs,70  say gtotal02/nBagi     picture "@z 9,999,999,999"
         @ brs,85  say ":"
         @ brs,87  say gtotal03/nBagi     picture "@z 9,999,999,999"
         @ brs,102 say ":"
         @ brs,104 say gtotal04/nBagi     picture "@z 9,999,999,999"
         @ brs,119 say ":"
         @ brs,121 say gtotal05/nBagi     picture "@z 9,999,999,999"
         @ brs,136 say ":"
         @ brs,138 say gtotal06/nBagi     picture "@z 9,999,999,999"
         @ brs,153 say ":"
         @ brs,155 say gtotal07/nBagi     picture "@z 9,999,999,999"
         @ brs,170 say ":"
         @ brs,172 say gtotal08/nBagi     picture "@z 9,999,999,999"
         @ brs,187 say ":"
         @ brs,189 say gtotal09/nBagi     picture "@z 9,999,999,999"
         @ brs,204 say ":"
         @ brs,206 say gtotal10/nBagi     picture "@z 9,999,999,999"
         @ brs,221 say ":"
         @ brs,223 say gtotal11/nBagi     picture "@z 9,999,999,999"
         @ brs,238 say ":"
         @ brs,240 say gtotal12/nBagi     picture "@z 9,999,999,999"
         @ brs,255 say ":"
         @ brs,257 say gtotal00/nBagi     picture "@z 99,999,999,999"
         @ brs,271 say ":"
         brs = brs + 1
         @ brs,1 say repli('-',nWidth)
      endif
      set devi to screen
      set print off
      set cons on
      set cursor on
      eject
      @ prow(),pcol() say chr(27)+chr(18)+chr(80)
      if cPrint == 1
         set printer to
         Fileread(0,0,24,79,"Alokasi.prn")
         fErase ("Alokasi.prn")
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil


**************************
Func AHtng1(fRmCode)
     store 0 to total01,total02,total03,total04,total05,total06,total07,total08,total09,total10,total11,total12
     store 0 to std,st1
     store 0 to std,pakai01,pakai02,pakai03,pakai04,pakai05,pakai06
     store 0 to pakai07,pakai08,pakai09,pakai10,pakai11,pakai12
     store 0 to bl1,bl2,bl3,bl4,bl5,bl6,bl7,bl8,bl9,bl10,bl11,bl12
     sele formula
     do while Formula->RMCode == fRmCode .and. !Formula->(eof())
        fFgCode := Formula->FGCode
        std     := Formula->Standardb
        sele volume
        set order to 3
        if dbseek( fFgCode )
           AHtngVlm( fFgCode,fRmCode,std,1,1 )
           total01 := total01 + pakai01
           total02 := total02 + pakai02
           total03 := total03 + pakai03
           total04 := total04 + pakai04
           total05 := total05 + pakai05
           total06 := total06 + pakai06
           total07 := total07 + pakai07
           total08 := total08 + pakai08
           total09 := total09 + pakai09
           total10 := total10 + pakai10
           total11 := total11 + pakai11
           total12 := total12 + pakai12
        else
           sele frml
           set order to 2
           if dbseek( fFgCode ) 
              fRmCode1 := Frml->RMCode
              do while Frml->RMCode == fRmCode1 .and. !Frml->(eof())
                 fFgCode1 := Frml->FGCode
                 fstd     := Frml->Standardb
                 sele volume
                 set order to 3
                 if dbseek( fFgCode1 )
                    AHtngVlm( fFgCode1,fRmCode1,std,fstd,1 )
                    total01 := total01 + pakai01
                    total02 := total02 + pakai02
                    total03 := total03 + pakai03
                    total04 := total04 + pakai04
                    total05 := total05 + pakai05
                    total06 := total06 + pakai06
                    total07 := total07 + pakai07
                    total08 := total08 + pakai08
                    total09 := total09 + pakai09
                    total10 := total10 + pakai10
                    total11 := total11 + pakai11
                    total12 := total12 + pakai12
                 else
                    sele frml1
                    set order to 2
                    if dbseek( fFgCode1 )
                       fRmCode2 := Frml1->RMCode
                       do while Frml1->RMCode == fRmCode2 .and. !Frml1->(eof())
                          fFgCode2 := Frml1->FGCode
                          fstd1    := Frml1->Standardb
                          sele volume
                          set order to 3
                          if dbseek( fFgCode2 )
                             AHtngVlm( fFgCode2,fRmCode2,std,fstd,fstd1 )
                             total01 := total01 + pakai01
                             total02 := total02 + pakai02
                             total03 := total03 + pakai03
                             total04 := total04 + pakai04
                             total05 := total05 + pakai05
                             total06 := total06 + pakai06
                             total07 := total07 + pakai07
                             total08 := total08 + pakai08
                             total09 := total09 + pakai09
                             total10 := total10 + pakai10
                             total11 := total11 + pakai11
                             total12 := total12 + pakai12
                          endif
                          Frml1->( dbSkip() )
                          store 0 to bl1,bl2,bl3,bl4,bl5,bl6,bl7,bl8,bl9,bl10,bl11,bl12
                       enddo
                    endif
                 endif
                 Frml->( dbSkip() )
                store 0 to bl1,bl2,bl3,bl4,bl5,bl6,bl7,bl8,bl9,bl10,bl11,bl12
              enddo
           endif
        endif
        select formula
        Formula->( dbSkip() )
        store 0 to bl1,bl2,bl3,bl4,bl5,bl6,bl7,bl8,bl9,bl10,bl11,bl12
     enddo
     return''

Func AHtngvlm(mFgCode1,mRmCode1,mstd1,mstd2,mstd3)
     store 0 to bl1,bl2,bl3,bl4,bl5,bl6,bl7,bl8,bl9,bl10,bl11,bl12
     selec volume
     set order to 3
     do while Volume->FGCode == mFgCode1 .and. !Volume->(eof())
        bl1  := bl1  + Volume->Jan
        bl2  := bl2  + Volume->Feb
        bl3  := bl3  + Volume->Mar
        bl4  := bl4  + Volume->Apr
        bl5  := bl5  + Volume->Mei
        bl6  := bl6  + Volume->Jun
        bl7  := bl7  + Volume->Jul
        bl8  := bl8  + Volume->Agt
        bl9  := bl9  + Volume->Sep
        bl10 := bl10 + Volume->Okt
        bl11 := bl11 + Volume->Nov
        bl12 := bl12 + Volume->Des
        Volume->( dbSkip() )
     enddo
     sele RMMast
     set order to 1
     dbSeek(mRmCode1)
     cId := RMMAst->Id
     if cId # '  ' .and. cId# '* ' .and. RMMast->Desc # 'KARTON'
        pakai01 := mstd1*mstd2*mstd3*bl1 *1000*nPrice1
        pakai02 := mstd1*mstd2*mstd3*bl2 *1000*nPrice1
        pakai03 := mstd1*mstd2*mstd3*bl3 *1000*nPrice1
        pakai04 := mstd1*mstd2*mstd3*bl4 *1000*nPrice2
        pakai05 := mstd1*mstd2*mstd3*bl5 *1000*nPrice2
        pakai06 := mstd1*mstd2*mstd3*bl6 *1000*nPrice2
        pakai07 := mstd1*mstd2*mstd3*bl7 *1000*nPrice3
        pakai08 := mstd1*mstd2*mstd3*bl8 *1000*nPrice3
        pakai09 := mstd1*mstd2*mstd3*bl9 *1000*nPrice3
        pakai10 := mstd1*mstd2*mstd3*bl10*1000*nPrice4
        pakai11 := mstd1*mstd2*mstd3*bl11*1000*nPrice4
        pakai12 := mstd1*mstd2*mstd3*bl12*1000*nPrice4
     else
        pakai01 := mstd1*mstd2*mstd3*bl1 *nPrice1
        pakai02 := mstd1*mstd2*mstd3*bl2 *nPrice1
        pakai03 := mstd1*mstd2*mstd3*bl3 *nPrice1
        pakai04 := mstd1*mstd2*mstd3*bl4 *nPrice2
        pakai05 := mstd1*mstd2*mstd3*bl5 *nPrice2
        pakai06 := mstd1*mstd2*mstd3*bl6 *nPrice2
        pakai07 := mstd1*mstd2*mstd3*bl7 *nPrice3
        pakai08 := mstd1*mstd2*mstd3*bl8 *nPrice3
        pakai09 := mstd1*mstd2*mstd3*bl9 *nPrice3
        pakai10 := mstd1*mstd2*mstd3*bl10*nPrice4
        pakai11 := mstd1*mstd2*mstd3*bl11*nPrice4
        pakai12 := mstd1*mstd2*mstd3*bl12*nPrice4
     endif
     return''

Func AHtngvlmD(mFgCode1,mRmCode1,mstd1,mstd2,mstd3)
     store 0 to bl1,bl2,bl3,bl4,bl5,bl6,bl7,bl8,bl9,bl10,bl11,bl12
     *store 0 to total01,total02,total03,total04,total05,total06,total07,total08,total09,total10,total11,total12
     selec volume
     set order to 3
     do while Volume->FGCode == mFgCode1 .and. !Volume->(eof())
        bl1  := bl1  + Volume->Jan
        bl2  := bl2  + Volume->Feb
        bl3  := bl3  + Volume->Mar
        bl4  := bl4  + Volume->Apr
        bl5  := bl5  + Volume->Mei
        bl6  := bl6  + Volume->Jun
        bl7  := bl7  + Volume->Jul
        bl8  := bl8  + Volume->Agt
        bl9  := bl9  + Volume->Sep
        bl10 := bl10 + Volume->Okt
        bl11 := bl11 + Volume->Nov
        bl12 := bl12 + Volume->Des
        Volume->( dbSkip() )
     enddo
     sele RMMast
     set order to 1
     dbSeek(mRmCode1)
     cId := RMMAst->Id
     if cId # '  ' .and. cId# '* ' .and. RMMast->Desc # 'KARTON'
        pakai01 := mstd1*mstd2*mstd3*bl1 *1000*nPrice1
        pakai02 := mstd1*mstd2*mstd3*bl2 *1000*nPrice1
        pakai03 := mstd1*mstd2*mstd3*bl3 *1000*nPrice1
        pakai04 := mstd1*mstd2*mstd3*bl4 *1000*nPrice2
        pakai05 := mstd1*mstd2*mstd3*bl5 *1000*nPrice2
        pakai06 := mstd1*mstd2*mstd3*bl6 *1000*nPrice2
        pakai07 := mstd1*mstd2*mstd3*bl7 *1000*nPrice3
        pakai08 := mstd1*mstd2*mstd3*bl8 *1000*nPrice3
        pakai09 := mstd1*mstd2*mstd3*bl9 *1000*nPrice3
        pakai10 := mstd1*mstd2*mstd3*bl10*1000*nPrice4
        pakai11 := mstd1*mstd2*mstd3*bl11*1000*nPrice4
        pakai12 := mstd1*mstd2*mstd3*bl12*1000*nPrice4
     else
        pakai01 := mstd1*mstd2*mstd3*bl1 *nPrice1
        pakai02 := mstd1*mstd2*mstd3*bl2 *nPrice1
        pakai03 := mstd1*mstd2*mstd3*bl3 *nPrice1
        pakai04 := mstd1*mstd2*mstd3*bl4 *nPrice2
        pakai05 := mstd1*mstd2*mstd3*bl5 *nPrice2
        pakai06 := mstd1*mstd2*mstd3*bl6 *nPrice2
        pakai07 := mstd1*mstd2*mstd3*bl7 *nPrice3
        pakai08 := mstd1*mstd2*mstd3*bl8 *nPrice3
        pakai09 := mstd1*mstd2*mstd3*bl9 *nPrice3
        pakai10 := mstd1*mstd2*mstd3*bl10*nPrice4
        pakai11 := mstd1*mstd2*mstd3*bl11*nPrice4
        pakai12 := mstd1*mstd2*mstd3*bl12*nPrice4
     endif
     total01 := total01 + pakai01
     total02 := total02 + pakai02
     total03 := total03 + pakai03
     total04 := total04 + pakai04
     total05 := total05 + pakai05
     total06 := total06 + pakai06
     total07 := total07 + pakai07
     total08 := total08 + pakai08
     total09 := total09 + pakai09
     total10 := total10 + pakai10
     total11 := total11 + pakai11
     total12 := total12 + pakai12
     return''


function Report10()
   local cPrint,cDesc,nStdA,nStdB
   local cRMCode  := space(7)
   local cRMCode1 := space(7)
   local cRMCode2 := space(7)
   local cFGCode
   local nWidth   := 100
   local nSW      := 0,Brs := 0
   local nPage    := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   dataDict()
   set date British
   set score off
   do while .T.
      sbox( 17,40,20,62,"gr+/w+,w+/bg+" )
      @ 18,41 say " Code RM : " get cRMCode1 picture "@!"
      @ 19,41 say "      To : " get cRMCode2 picture "@!"
      read
      if lastkey() == K_ESC
         exit
      endif
      cPrint := alert ( " Output Device  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "Alokasi.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)
      sele FORMULA
      set filter to Formula->RMCode >= cRMCode1 .and. Formula->RMCode <= cRMCode2
      go top
      Formula->( dbsetOrder(2) )
      Formula->( dbGoTop() )
      do while ! Formula->( Eof() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
         if BRS = 0 .or. BRS > 55
            @ 1,1  SAY CostRef->Desc1
            @ 2,1  SAY CostRef->Desc2
            @ 3,40 SAY 'ALOKASI PEMAKAIAN RM dalam FG'
            @ 4,40 SAY '-----------------------------'
            nPage++
            @  4,90 say 'Page : '
            @  4,pcol() say nPage picture "999"
            *                    1         2         3         4         5         6         7         8         9        10
            *          01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890
            @  5,1 SAY repli('=',nWidth)
            @  6,1 SAY '     Raw Material                   Finished Goods                Standard A       Standard B'
            @  7,1 SAY repli('=',nWidth)
            *            xxxxxxx xxxxxxxxxxxxxxxxxxxx   xxxxxxx xxxxxxxxxxxxxxxxxxxx    999,999.999999   999,999.999999
            brs = 8
         endif
         cRMCode := Formula->RMCode
         RMMast->( dbSeek( cRMCode ) )
         do while Formula->RMCode == cRMCode .and. ! Formula->(eof())
            cFGCode := Formula->FGCode
            nStdA   := Formula->StandardA
            nStdB   := Formula->StandardB
            if nSw == 0
               @ brs,2   say cRMCode picture "@!"
               RMMast->( dbSeek(cRMCode) )
               @ brs,10  say substr(RMMast->desc,1,20)  picture "@!"
               nSw := 1
            endif
            @ brs,33  say cFGCode      picture "@!"
            FGMast->( dbSeek( cFGCode) )
            @ brs,41  say substr(FGMast->desc,1,20)  picture "@!"
            @ brs,65  say nStdA       picture "@z 999,999.999999"
            @ brs,82  say nStdB       picture "@z 999,999.999999"
            Formula->( dbSkip() )
            brs = brs + 1
            if brs > 55
               @ brs,1 say repli('-',nWidth)
               eject
               nSw := 0
               brs := 0
               exit
            endif
         enddo
         if Formula->RMCode # cRMCode
            *@ brs,1 say repli('-',nWidth)
            *brs = brs + 1
            @ brs,1 say repli('-',nWidth)
            brs = brs + 1
            nSw := 0
         endif
         if Formula->( Eof() )
            exit
         endif
      enddo
      *brs = brs + 1
      *@ brs,1 say repli('-',nWidth)
      set devi to screen
      set print off
      set cons on
      set cursor on
      eject
      @ prow(),pcol() say chr(18)
      if cPrint == 1
         set printer to
         Fileread(0,0,24,79,"Alokasi.prn")
         fErase ("Alokasi.prn")
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil



