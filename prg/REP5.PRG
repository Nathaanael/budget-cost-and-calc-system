# include "Stock.ch"

function Report7()
   local cPrint,cPrType2,cPrType1
   local cFGCode  := space(7)
   local cFGCode1 := space(7)
   local cFGCode2 := space(7)
   local nWidth   := 84
   local nSW      := 0
   local nPage    := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   dataDict()
   set date British
   set score off
   do while .T.
      sbox( 17,40,20,62,"gr+/w+,w+/bg+" )
      @ 18,41 say " Code FG : " get cFGCode1 picture "@!"
      @ 19,41 say "      To : " get cFGCode2 picture "@!"
      read
      if lastkey() == K_ESC
         exit
      endif
      cPrint := alert ( " Output Device  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "UnitCost.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)
      select FGMast
      set filter to FGMast->Active='Y' .and. FGMast->FGCode >= cFGCode1 .and. FGMast->FGCode <= cFGCode2 .and. (FGMast->UC0#0 .or. FGMast->UCLE#0 .or. FGMast->UC1#0 .or. FGMast->UC2#0 .or. FGMast->UC3#0 .or. FGMast->UC4#0 .or.;
      FGMast->Price0#0 .or. FGMast->PriceLE#0 .or. FGMast->Price1#0 .or. FGMast->Price2#0 .or. FGMast->Price3#0 .or. FGMast->Price4#0)

      FGMast->( dbSetOrder(3) )
      FGMast->( dbGoTop() )
      do while ! FGMast-> ( EOF() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
        if nSW == 0
             nPage++
             @ prow()+1,1    say CostRef->Desc1
             @ prow()  ,160  say "Date : "
             @ prow(),pcol() say date()
             @ prow()+1,1    say CostRef->Desc2
             @ prow()  ,160  say "Time : "
             @ prow(),pcol() say time()
             @ prow()+1,75   say "SELLING PRICE  &  UNIT COST"
             @ prow()+1,75   say "     AOP Year : "
             @ prow(),pcol() say ltrim(str(val(right(CostRef->Per,4))+1)) picture "@!"
             @ prow()  ,160  say "Page : "
             @ prow(),pcol() say nPage picture "999"
             @ prow()+1,1    say repli ( '=',nWidth )
             @ prow()  ,91   say repli ( '=',nWidth )
*                          1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1         2
*                01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789
@ prow()+1,1 say "|         Finished Goods       |      SELLING PRICE      |        Variance         |      |         Finished Goods       |        UNIT COST        |        Variance         |"
@ prow()+1,1 say "|                              |  Current   |   A O P    |    Rp.     |      %     |      |                              |  Current   |   A O P    |    Rp.     |     %      |"
*                   xxxxxxx xxxxxxxxxxxxxxxxxxxx   999,999.99   999,999.99   999,999.99   999,999.99          xxxxxxx xxxxxxxxxxxxxxxxxxxx   999,999.99   999,999.99   999,999.99   999,999.99
             @ prow()+1,1    say repli ( '=',nWidth )
             @ prow()  ,91   say repli ( '=',nWidth )
             nSW := 1
        endif
        cPrType1 := FGMast->PrType1
        do while FGMast->PrType1 == cPrType1 .and. prow()<= 55 .and. ! FGMast->(Eof())
           cPrType2 := FGMast->PrType2
           do while FGMast->PrType2 == cPrType2 .and. FGMast->PrType1 == cPrType1 .and. prow()<= 55 .and. ! FGMast->(Eof())
              if  FGMast->UC0 # 0 .or. FGMast->Price0 # 0
                  @ prow()+1,3   say FGMast->FGCode                                       picture "@!"
                  @ prow()  ,11  say substr(FGMast->Desc,1,20)                            picture "@!"
                  @ prow()  ,34  say FGMast->Price0                                       picture "@z 999,999.99"
                  @ prow()  ,47  say FGMast->Price1                                       picture "@z 999,999.99"
                  @ prow()  ,60  say (FGMast->Price1-FGMast->Price0)                      picture "@z 999,999.99"
                  @ prow()  ,73  say (FGMast->Price1-FGMast->Price0)/FGMast->Price0*100   picture "@z 999,999.99"
                  @ prow()  ,93  say FGMast->FGCode              picture "@!"
                  @ prow()  ,101 say substr(FGMast->Desc,1,20)   picture "@!"
                  @ prow()  ,124 say FGMast->UC0                 picture "@z 999,999.99"
                  @ prow()  ,137 say FGMast->UC1                 picture "@z 999,999.99"
                  @ prow()  ,150 say (FGMast->UC1-FGMast->UC0)   picture "@z 999,999.99"
                  @ prow()  ,163 say (FGMast->UC1-FGMast->UC0)/FGMast->UC0*100            picture "@z 999,999.99"
              endif
              FGMast->( dbSkip() )
              if prow() >= 55  .or. FGMast->( EOF() )
                 @ prow()+1,1    say repli ( '-',nWidth )
                 @ prow()  ,91   say repli ( '-',nWidth )
                 nSW  := 0
                 exit
              endif
              if FGMast->( Eof() )
                 exit
              endif
              if prow() >= 55
                 nSW := 0
                 exit
              endif
           enddo
           if FGMast->( Eof() )
               exit
           endif
           if prow() >= 55
              nSW := 0
              eject
              exit
           endif
           if cPrType2 # FGMast->PrType2
              @ prow()+1,1   say " "
           endif
        enddo
        if cPrType1 # FGMast->PrType1
           @ prow()+1,1   say " "
        endif
      enddo
      eject
      @prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on
      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"UnitCost.prn")
         fErase ( "UnitCost.prn" )
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil


function Report8()
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
         Report81(nSelect)
       case nSelect == 2
         Report81(nSelect)
       case nSelect == 3
         Report81(nSelect)
       case nSelect == 4
         Report81(nSelect)
       case nSelect == 5
         Report81(nSelect)
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil


function Report81(unit)
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
            @ 2,100 SAY 'ALOKASI PEMAKAIAN RM ( QUANTITY NEW)'
            @ 3,100 SAY '---------------------------------'
            *                    1         2         3         4         5         6         7         8         9        10         1         2         3         4         5         6         7         8         9        20         1         2         3
            *          012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123
            @  4,1 say cdesc picture "@!"
            nPage++
            @  4,261 say 'Page : '
            @  4,269 say nPage picture "999"
            @  5,1 SAY repli('-',nWidth)
            @  6,1 SAY '    BAHAN BAKU           :    FINISHED GOODS      :       JAN      :       FEB      :      MAR       :       APR      :      MEI       :      JUN       :       JUL      :      AGT       :       SEP      :       OKT      :'
            @ 6,pcol() say'       NOV      :       DES      :     TOTAL     :'
            @  7,1 SAY repli('=',nWidth)
            *            xxxxxxx xxxxxxxxxxxxxxx   xxxxxxx xxxxxxxxxxxxxxx  999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999   999,999,999
            BRS = 8
         endif
         cRMCode := Formula->RMCode
         if cSum == 2
             do while Formula->RMCode == cRMCode .and. ! Formula->(eof())
                fFGCode := Formula->FGCode
                std     := Formula->Standardb
                sele volume
                set order to 3
                if dbseek( fFGCode ) 
                   HtngVlmd( fFgCode,cRmCode,std,1,1 )
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
                            HtngVlmD( fFgCode1,fRmCode1,std,fstd,1 )
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
                                     HtngVlmD( fFgCode2,fRmCode2,std,fstd,fstd1 )
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
                @ brs,53  say total01     picture "@z 999,999,999.99"
                @ brs,68  say ":"
                @ brs,70  say total02     picture "@z 999,999,999.99"
                @ brs,85  say ":"
                @ brs,87  say total03     picture "@z 999,999,999.99"
                @ brs,102 say ":"
                @ brs,104 say total04     picture "@z 999,999,999.99"
                @ brs,119 say ":"
                @ brs,121 say total05     picture "@z 999,999,999.99"
                @ brs,136 say ":"
                @ brs,138 say total06     picture "@z 999,999,999.99"
                @ brs,153 say ":"
                @ brs,155 say total07     picture "@z 999,999,999.99"
                @ brs,170 say ":"
                @ brs,172 say total08     picture "@z 999,999,999.99"
                @ brs,187 say ":"
                @ brs,189 say total09     picture "@z 999,999,999.99"
                @ brs,204 say ":"
                @ brs,206 say total10     picture "@z 999,999,999.99"
                @ brs,221 say ":"
                @ brs,223 say total11     picture "@z 999,999,999.99"
                @ brs,238 say ":"
                @ brs,240 say total12     picture "@z 999,999,999.99"
                @ brs,255 say ":"
                @ brs,257 say total00     picture "@z 999,999,999.99"
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
                @ brs,53  say gtotal01     picture "@z 999,999,999.99"
                @ brs,68  say ":"
                @ brs,70  say gtotal02     picture "@z 999,999,999.99"
                @ brs,85  say ":"
                @ brs,87  say gtotal03     picture "@z 999,999,999.99"
                @ brs,102 say ":"
                @ brs,104 say gtotal04     picture "@z 999,999,999.99"
                @ brs,119 say ":"
                @ brs,121 say gtotal05     picture "@z 999,999,999.99"
                @ brs,136 say ":"
                @ brs,138 say gtotal06     picture "@z 999,999,999.99"
                @ brs,153 say ":"
                @ brs,155 say gtotal07     picture "@z 999,999,999.99"
                @ brs,170 say ":"
                @ brs,172 say gtotal08     picture "@z 999,999,999.99"
                @ brs,187 say ":"
                @ brs,189 say gtotal09     picture "@z 999,999,999.99"
                @ brs,204 say ":"
                @ brs,206 say gtotal10     picture "@z 999,999,999.99"
                @ brs,221 say ":"
                @ brs,223 say gtotal11     picture "@z 999,999,999.99"
                @ brs,238 say ":"
                @ brs,240 say gtotal12     picture "@z 999,999,999.99"
                @ brs,255 say ":"
                @ brs,257 say gtotal00     picture "@z 999,999,999.99"
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
             htng1(cRMCode)
             total00 := total01+total02+total03+total04+total05+total06+total07+total08+total09+total10+total11+total12
             if total00 # 0
                @ brs,2   say cRmCode picture "@!"
                RMMast->( dbSeek( cRmCode ) )
                @ brs,10  say substr(RMMast->desc,1,15)  picture "@!"
                @ brs,26  say ":"
                @ brs,51  say ":"
                @ brs,53  say total01     picture "@z 999,999,999.99"
                @ brs,68  say ":"
                @ brs,70  say total02     picture "@z 999,999,999.99"
                @ brs,85  say ":"
                @ brs,87  say total03     picture "@z 999,999,999.99"
                @ brs,102 say ":"
                @ brs,104 say total04     picture "@z 999,999,999.99"
                @ brs,119 say ":"
                @ brs,121 say total05     picture "@z 999,999,999.99"
                @ brs,136 say ":"
                @ brs,138 say total06     picture "@z 999,999,999.99"
                @ brs,153 say ":"
                @ brs,155 say total07     picture "@z 999,999,999.99"
                @ brs,170 say ":"
                @ brs,172 say total08     picture "@z 999,999,999.99"
                @ brs,187 say ":"
                @ brs,189 say total09     picture "@z 999,999,999.99"
                @ brs,204 say ":"
                @ brs,206 say total10     picture "@z 999,999,999.99"
                @ brs,221 say ":"
                @ brs,223 say total11     picture "@z 999,999,999.99"
                @ brs,238 say ":"
                @ brs,240 say total12     picture "@z 999,999,999.99"
                @ brs,255 say ":"
                @ brs,257 say total00     picture "@z 999,999,999.99"
                @ brs,271 say ":"
                brs = brs + 1
                sele RMQty
                RMQty->( dbAppend() )
                RMQty->RMCode := cRmCode
                RMQty->Desc   := RMMast->Desc
                RMQty->Jan    := total01
                RMQty->Feb    := total02
                RMQty->Mar    := total03
                RMQty->Apr    := total04
                RMQty->Mei    := total05
                RMQty->Jun    := total06
                RMQty->Jul    := total07
                RMQty->Agt    := total08
                RMQty->Sep    := total09
                RMQty->Okt    := total10
                RMQty->Nov    := total11
                RMQty->Des    := total12
                RMQty->Total  := total00
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
         @ brs,53  say gtotal01     picture "@z 999,999,999.99"
         @ brs,68  say ":"
         @ brs,70  say gtotal02     picture "@z 999,999,999.99"
         @ brs,85  say ":"
         @ brs,87  say gtotal03     picture "@z 999,999,999.99"
         @ brs,102 say ":"
         @ brs,104 say gtotal04     picture "@z 999,999,999.99"
         @ brs,119 say ":"
         @ brs,121 say gtotal05     picture "@z 999,999,999.99"
         @ brs,136 say ":"
         @ brs,138 say gtotal06     picture "@z 999,999,999.99"
         @ brs,153 say ":"
         @ brs,155 say gtotal07     picture "@z 999,999,999.99"
         @ brs,170 say ":"
         @ brs,172 say gtotal08     picture "@z 999,999,999.99"
         @ brs,187 say ":"
         @ brs,189 say gtotal09     picture "@z 999,999,999.99"
         @ brs,204 say ":"
         @ brs,206 say gtotal10     picture "@z 999,999,999.99"
         @ brs,221 say ":"
         @ brs,223 say gtotal11     picture "@z 999,999,999.99"
         @ brs,238 say ":"
         @ brs,240 say gtotal12     picture "@z 999,999,999.99"
         @ brs,255 say ":"
         @ brs,257 say gtotal00     picture "@z 999,999,999.99"
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
Func Htng1(fRmCode)
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
           HtngVlm( fFgCode,fRmCode,std,1,1 )
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
                    HtngVlm( fFgCode1,fRmCode1,std,fstd,1 )
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
                             HtngVlm( fFgCode2,fRmCode2,std,fstd,fstd1 )
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

Func Htngvlm(mFgCode1,mRmCode1,mstd1,mstd2,mstd3)
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
        pakai01 := mstd1*mstd2*mstd3*bl1 *1000
        pakai02 := mstd1*mstd2*mstd3*bl2 *1000
        pakai03 := mstd1*mstd2*mstd3*bl3 *1000
        pakai04 := mstd1*mstd2*mstd3*bl4 *1000
        pakai05 := mstd1*mstd2*mstd3*bl5 *1000
        pakai06 := mstd1*mstd2*mstd3*bl6 *1000
        pakai07 := mstd1*mstd2*mstd3*bl7 *1000
        pakai08 := mstd1*mstd2*mstd3*bl8 *1000
        pakai09 := mstd1*mstd2*mstd3*bl9 *1000
        pakai10 := mstd1*mstd2*mstd3*bl10*1000
        pakai11 := mstd1*mstd2*mstd3*bl11*1000
        pakai12 := mstd1*mstd2*mstd3*bl12*1000
     else
        pakai01 := mstd1*mstd2*mstd3*bl1
        pakai02 := mstd1*mstd2*mstd3*bl2
        pakai03 := mstd1*mstd2*mstd3*bl3
        pakai04 := mstd1*mstd2*mstd3*bl4
        pakai05 := mstd1*mstd2*mstd3*bl5
        pakai06 := mstd1*mstd2*mstd3*bl6
        pakai07 := mstd1*mstd2*mstd3*bl7
        pakai08 := mstd1*mstd2*mstd3*bl8
        pakai09 := mstd1*mstd2*mstd3*bl9
        pakai10 := mstd1*mstd2*mstd3*bl10
        pakai11 := mstd1*mstd2*mstd3*bl11
        pakai12 := mstd1*mstd2*mstd3*bl12
     endif
     return''

Func HtngvlmD(mFgCode1,mRmCode1,mstd1,mstd2,mstd3)
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
        pakai01 := mstd1*mstd2*mstd3*bl1 *1000
        pakai02 := mstd1*mstd2*mstd3*bl2 *1000
        pakai03 := mstd1*mstd2*mstd3*bl3 *1000
        pakai04 := mstd1*mstd2*mstd3*bl4 *1000
        pakai05 := mstd1*mstd2*mstd3*bl5 *1000
        pakai06 := mstd1*mstd2*mstd3*bl6 *1000
        pakai07 := mstd1*mstd2*mstd3*bl7 *1000
        pakai08 := mstd1*mstd2*mstd3*bl8 *1000
        pakai09 := mstd1*mstd2*mstd3*bl9 *1000
        pakai10 := mstd1*mstd2*mstd3*bl10*1000
        pakai11 := mstd1*mstd2*mstd3*bl11*1000
        pakai12 := mstd1*mstd2*mstd3*bl12*1000
     else
        pakai01 := mstd1*mstd2*mstd3*bl1
        pakai02 := mstd1*mstd2*mstd3*bl2
        pakai03 := mstd1*mstd2*mstd3*bl3
        pakai04 := mstd1*mstd2*mstd3*bl4
        pakai05 := mstd1*mstd2*mstd3*bl5
        pakai06 := mstd1*mstd2*mstd3*bl6
        pakai07 := mstd1*mstd2*mstd3*bl7
        pakai08 := mstd1*mstd2*mstd3*bl8
        pakai09 := mstd1*mstd2*mstd3*bl9
        pakai10 := mstd1*mstd2*mstd3*bl10
        pakai11 := mstd1*mstd2*mstd3*bl11
        pakai12 := mstd1*mstd2*mstd3*bl12
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



