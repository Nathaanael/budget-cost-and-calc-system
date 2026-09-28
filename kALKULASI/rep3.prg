#Include "Stock.ch"

function Report61()
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
         Report611(nSelect)
       case nSelect == 2
         Report611(nSelect)
       case nSelect == 3
         Report611(nSelect)
       case nSelect == 4
         Report611(nSelect)
       case nSelect == 5
         Report611(nSelect)
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil


function Report611(unit)
   local cPrint,cOption,cArea1,cArea2,cArea3,cArea4,cArea5,cArea6,cArea7,cArea8,cArea9,cArea10
   local cPrType1,cFGCode,cMode,cDesc,cPrType2,nDivide,nBagi,nBDesc
   local nJan := 0,nFeb := 0,nMar := 0,nApr := 0,nMei := 0,nJun := 0
   local nJul := 0,nAgt := 0,nSep := 0,nOkt := 0,nNov := 0,nDes := 0
   local nSJan := 0,nSFeb := 0,nSMar := 0,nSApr := 0,nSMei := 0,nSJun := 0
   local nSJul := 0,nSAgt := 0,nSSep := 0,nSOkt := 0,nSNov := 0,nSDes := 0
   local nTJan := 0,nTFeb := 0,nTMar := 0,nTApr := 0,nTMei := 0,nTJun := 0
   local nTJul := 0,nTAgt := 0,nTSep := 0,nTOkt := 0,nTNov := 0,nTDes := 0
   local nGJan := 0,nGFeb := 0,nGMar := 0,nGApr := 0,nGMei := 0,nGJun := 0
   local nGJul := 0,nGAgt := 0,nGSep := 0,nGOkt := 0,nGNov := 0,nGDes := 0
   local nQtr1 := 0,nQtr2 := 0,nQtr3 := 0,nQtr4 := 0,nTotal := 0
   local nSQtr1 := 0,nSQtr2 := 0,nSQtr3 := 0,nSQtr4 := 0,nSTotal := 0
   local nTQtr1 := 0,nTQtr2 := 0,nTQtr3 := 0,nTQtr4 := 0,nTTotal := 0
   local nGQtr1 := 0,nGQtr2 := 0,nGQtr3 := 0,nGQtr4 := 0,nGTotal := 0
   local nPrice1 := 0,nPrice2 := 0,nPrice3 := 0,nPrice4 := 0
   local nWidth    := 255
   local nSW       := 0
   local nPage     := 0
   local GetList   := {}
   local cClr      := setcolor()
   local cScr      := savescreen()
   dataDict()
   set date British
   set score off

   do while .T.
      *cOption := alert ( 'Location : ',{'CIKAMPEK','SEMARANG','SURABAYA','NATIONAL'})
      cOption := unit
      cMode   := alert ( 'Mode : ', {' Summary ',' Detail '} )
      nDivide := alert ( 'Divided : ', {'1','100 ','1000', '1000000'} )
      cPrint := alert ( " Output Device : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "RMValue.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(27) + chr(77) + chr(15)  &&20 condensed
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
      sele RMValue
      zap
      sele Volume
      set filter to Volume->Code=cAREA1.OR.Volume->Code=cAREA2.OR.Volume->Code=cAREA3.OR.Volume->Code=cAREA4.OR.Volume->Code=cAREA5;
      .OR.Volume->Code=cAREA6.OR.Volume->Code=cAREA7.OR.Volume->Code=cAREA8.OR.Volume->Code=cAREA9.OR.Volume->Code=cAREA10

      Volume->( dbSetOrder(2) )
      Volume->( dbGoTop() )
      do while ! Volume-> ( EOF() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
        if nSW == 0
             nPage++
             @ prow()+1,1    say CostRef->Desc1
             @ prow()  ,227  say "Date : "
             @ prow(),pcol() say date()
             @ prow()+1,1    say CostRef->Desc2
             @ prow()  ,227  say "Time : "
             @ prow(),pcol() say time()
             @ prow()+1,110  say " RAW MATERIAL  V A L U E "
             @ prow()  ,227  say "Page : "
             @ prow(),pcol() say nPage picture "999"
             @ prow()+1,1    say cDesc  picture "@!"
             @ prow()  ,110  say "      AOP Year : "
             @ prow(),pcol() say ltrim(str(val(Right(CostRef->Per,4))+1)) picture "@!"
             @ prow()  ,227  say "In ' "
             @ prow(),pcol() say nBDesc picture "@!"
             @ prow()+1,1    say repli ( '=',nWidth )
             *                             1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8
             *                   0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789
             @ prow()+1,1    say "|         Finished Goods       |------------------------------------------------------------------------------------------------------------------------------------------------------------"
             @ prow(),pcol() say "------------------------------------------------------------------|"
             @ prow()+1,1    say "|                              |   Januari   |  Februari  |   Maret    |   Qtr 1    |    April   |    Mei     |   Juni     |   Qtr 2    |    Juli    |  Agustus   |  September |    Qtr 3   |"
             @ prow(),pcol() say "   Oktober   |  November  |  Desember  |   Qtr 4    |    Total   |"
             *                   xxxxxxx xxxxxxxxxxxxxxxxxxxx   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99
             @ prow()+1,1   say repli ( '=',nWidth )
             nSW := 1
        endif
        if cMode == 1
           cPrType1 := Volume->PrType1
           do while Volume->PrType1 == cPrType1 .and. prow()<= 55 .and. ! Volume->(Eof())
              cPrType2 := Volume->PrType2
              do while Volume->PrType2 == cPrType2 .and. prow()<= 55 .and. ! Volume->(Eof())
                 cFGCode := Volume->FGCode
                 FGMast->( dbSeek(cFGCode) )
                 nPrice1 := FGMast->UC1
                 nPrice2 := FGMast->UC2
                 nPrice3 := FGMast->UC3
                 nPrice4 := FGMast->UC4
                 /*
                 if CostRef->Code="00"
                     nPrice1 := Round(FGMast->UC1,1)
                     nPrice2 := Round(FGMast->UC2,1)
                     nPrice3 := Round(FGMast->UC3,1)
                     nPrice4 := Round(FGMast->UC4,1)
                 else
                     nPrice1 := Round(FGMast->UC1,-2)
                     nPrice2 := Round(FGMast->UC2,-2)
                     nPrice3 := Round(FGMast->UC3,-2)
                     nPrice4 := Round(FGMast->UC4,-2)
                 endif
                 */
                 do while Volume->FGCode == cFGCode .and. Volume->PrType2 == cPrType2 .and. prow()<= 55 .and. ! Volume->(Eof())
                     nJan := nJan + Volume->Jan
                     nFeb := nFeb + Volume->Feb
                     nMar := nMar + Volume->Mar
                     nApr := nApr + Volume->Apr
                     nMei := nMei + Volume->Mei
                     nJun := nJun + Volume->Jun
                     nJul := nJul + Volume->Jul
                     nAgt := nAgt + Volume->Agt
                     nSep := nSep + Volume->Sep
                     nOkt := nOkt + Volume->Okt
                     nNov := nNov + Volume->Nov
                     nDes := nDes + Volume->Des
                     Volume->( dbSkip() )
                 enddo
                 nJan := round(nJan * nPrice1,0)
                 nFeb := round(nFeb * nPrice1,0)
                 nMar := round(nMar * nPrice1,0)
                 nApr := round(nApr * nPrice2,0)
                 nMei := round(nMei * nPrice2,0)
                 nJun := round(nJun * nPrice2,0)
                 nJul := round(nJul * nPrice3,0)
                 nAgt := round(nAgt * nPrice3,0)
                 nSep := round(nSep * nPrice3,0)
                 nOkt := round(nOkt * nPrice4,0)
                 nNov := round(nNov * nPrice4,0)
                 nDes := round(nDes * nPrice4,0)
                 nSJan := nSJan + nJan
                 nSFeb := nSFeb + nFeb
                 nSMar := nSMar + nMar
                 nSApr := nSApr + nApr
                 nSMei := nSMei + nMei
                 nSJun := nSJun + nJun
                 nSJul := nSJul + nJul
                 nSAgt := nSAgt + nAgt
                 nSSep := nSSep + nSep
                 nSOkt := nSOkt + nOkt
                 nSNov := nSNov + nNov
                 nSDes := nSDes + nDes
                 store 0 to nJan,nFeb,nMar,nApr,nMei,nJun,nJul
                 store 0 to nAgt,nSep,nOkt,nNov,nDes
              enddo
              nQtr1  := nSJan+nSFeb+nSMar
              nQtr2  := nSApr+nSMei+nSJun
              nQtr3  := nSJul+nSAgt+nSSep
              nQtr4  := nSOkt+nSNov+nSDes
              nTotal := nQtr1+nQtr2+nQtr3+nQtr4
              nTJan := nTJan + nSJan
              nTFeb := nTFeb + nSFeb
              nTMar := nTMar + nSMar
              nTApr := nTApr + nSApr
              nTMei := nTMei + nSMei
              nTJun := nTJun + nSJun
              nTJul := nTJul + nSJul
              nTAgt := nTAgt + nSAgt
              nTSep := nTSep + nSSep
              nTOkt := nTOkt + nSOkt
              nTNov := nTNov + nSNov
              nTDes := nTDes + nSDes
              nTQtr1:= nTQtr1 + nQtr1
              nTQtr2:= nTQtr2 + nQtr2
              nTQtr3:= nTQtr3 + nQtr3
              nTQtr4:= nTQtr4 + nQtr4
              nTTotal := nTTotal + nTotal
              nGJan := nGJan + nSJan
              nGFeb := nGFeb + nSFeb
              nGMar := nGMar + nSMar
              nGApr := nGApr + nSApr
              nGMei := nGMei + nSMei
              nGJun := nGJun + nSJun
              nGJul := nGJul + nSJul
              nGAgt := nGAgt + nSAgt
              nGSep := nGSep + nSSep
              nGOkt := nGOkt + nSOkt
              nGNov := nGNov + nSNov
              nGDes := nGDes + nSDes
              nGQtr1:= nGQtr1 + nQtr1
              nGQtr2:= nGQtr2 + nQtr2
              nGQtr3:= nGQtr3 + nQtr3
              nGQtr4:= nGQtr4 + nQtr4
              nGTotal := nGTotal + nTotal
              TypeMast->( dbSeek(cPrType2) )
              @ prow()+1,05  say substr(TypeMast->Desc,1,20)   picture "@!"
              @ prow()  ,34  say nSJan/nBagi                   picture "@z 999,999,999"
              @ prow()  ,47  say nSFeb/nBagi                   picture "@z 999,999,999"
              @ prow()  ,60  say nSMar/nBagi                   picture "@z 999,999,999"
              @ prow()  ,73  say nQtr1/nBagi                   picture "@z 999,999,999"
              @ prow()  ,86  say nSApr/nBagi                   picture "@z 999,999,999"
              @ prow()  ,99  say nSMei/nBagi                   picture "@z 999,999,999"
              @ prow()  ,112 say nSJun/nBagi                   picture "@z 999,999,999"
              @ prow()  ,125 say nQtr2/nBagi                   picture "@z 999,999,999"
              @ prow()  ,138 say nSJul/nBagi                   picture "@z 999,999,999"
              @ prow()  ,151 say nSAgt/nBagi                   picture "@z 999,999,999"
              @ prow()  ,164 say nSSep/nBagi                   picture "@z 999,999,999"
              @ prow()  ,177 say nQtr3/nBagi                   picture "@z 999,999,999"
              @ prow()  ,190 say nSOkt/nBagi                   picture "@z 999,999,999"
              @ prow()  ,203 say nSNov/nBagi                   picture "@z 999,999,999"
              @ prow()  ,216 say nSDes/nBagi                   picture "@z 999,999,999"
              @ prow()  ,229 say nQtr4/nBagi                   picture "@z 999,999,999"
              @ prow()  ,244 say nTotal/nBagi                  picture "@z 999,999,999"
              *@ prow()+1,1   say " "
              store 0 to nSJan,nSFeb,nSMar,nSApr,nSMei,nSJun,nSJul
              store 0 to nSAgt,nSSep,nSOkt,nSNov,nSDes,nQtr1,nQtr2
              store 0 to nQtr3,nQtr4,nTotal
              if prow() >= 57
                 nSW  := 0
                 eject
              endif
              if Volume->( Eof() )
                 exit
              endif
           enddo
           @ prow()+1,1   say " "
           @ prow()+1,5   say "Total"
           @ prow()  ,34  say nTJan/nBagi                 picture "@z 999,999,999"
           @ prow()  ,47  say nTFeb/nBagi                 picture "@z 999,999,999"
           @ prow()  ,60  say nTMar/nBagi                 picture "@z 999,999,999"
           @ prow()  ,73  say nTQtr1/nBagi                picture "@z 999,999,999"
           @ prow()  ,86  say nTApr/nBagi                 picture "@z 999,999,999"
           @ prow()  ,99  say nTMei/nBagi                 picture "@z 999,999,999"
           @ prow()  ,112 say nTJun/nBagi                 picture "@z 999,999,999"
           @ prow()  ,125 say nTQtr2/nBagi                picture "@z 999,999,999"
           @ prow()  ,138 say nTJul/nBagi                 picture "@z 999,999,999"
           @ prow()  ,151 say nTAgt/nBagi                 picture "@z 999,999,999"
           @ prow()  ,164 say nTSep/nBagi                 picture "@z 999,999,999"
           @ prow()  ,177 say nTQtr3/nBagi                picture "@z 999,999,999"
           @ prow()  ,190 say nTOkt/nBagi                 picture "@z 999,999,999"
           @ prow()  ,203 say nTNov/nBagi                 picture "@z 999,999,999"
           @ prow()  ,216 say nTDes/nBagi                 picture "@z 999,999,999"
           @ prow()  ,229 say nTQtr4/nBagi                picture "@z 999,999,999"
           @ prow()  ,244 say nTTotal/nBagi               picture "@z 999,999,999"
           @ prow()+1,1   say " "
           store 0 to nTJan,nTFeb,nTMar,nTApr,nTMei,nTJun,nTJul
           store 0 to nTAgt,nTSep,nTOkt,nTNov,nTDes,nTQtr1,nTQtr2
           store 0 to nTQtr3,nTQtr4,nTTotal
        else
           cPrType1 := Volume->PrType1
           cPrType2 := Volume->PrType2
           cFGCode := Volume->FGCode
           FGMast->( dbSeek(cFGCode) )
           nPrice1 := FGMast->UC1
           nPrice2 := FGMast->UC2
           nPrice3 := FGMast->UC3
           nPrice4 := FGMast->UC4
           /*
           if CostRef->Code="00"
               nPrice1 := Round(FGMast->UC1,1)
               nPrice2 := Round(FGMast->UC2,1)
               nPrice3 := Round(FGMast->UC3,1)
               nPrice4 := Round(FGMast->UC4,1)
           else
               nPrice1 := Round(FGMast->UC1,-2)
               nPrice2 := Round(FGMast->UC2,-2)
               nPrice3 := Round(FGMast->UC3,-2)
               nPrice4 := Round(FGMast->UC4,-2)
           endif
           */
           do while cFGCode == Volume->FGCode .and. ! Volume->(Eof())
              nJan := nJan + Volume->Jan
              nFeb := nFeb + Volume->Feb
              nMar := nMar + Volume->Mar
              nApr := nApr + Volume->Apr
              nMei := nMei + Volume->Mei
              nJun := nJun + Volume->Jun
              nJul := nJul + Volume->Jul
              nAgt := nAgt + Volume->Agt
              nSep := nSep + Volume->Sep
              nOkt := nOkt + Volume->Okt
              nNov := nNov + Volume->Nov
              nDes := nDes + Volume->Des
              Volume->( dbSkip() )
           enddo
           nJan := round(nJan * nPrice1,0)
           nFeb := round(nFeb * nPrice1,0)
           nMar := round(nMar * nPrice1,0)
           nApr := round(nApr * nPrice2,0)
           nMei := round(nMei * nPrice2,0)
           nJun := round(nJun * nPrice2,0)
           nJul := round(nJul * nPrice3,0)
           nAgt := round(nAgt * nPrice3,0)
           nSep := round(nSep * nPrice3,0)
           nOkt := round(nOkt * nPrice4,0)
           nNov := round(nNov * nPrice4,0)
           nDes := round(nDes * nPrice4,0)
           if nJan+nFeb+nMar+nApr+nMei+nJun+nJul+nAgt+nSep+nOkt+nNov+nDes # 0
              nQtr1 := nJan+nFeb+nMar
              nQtr2 := nApr+nMei+nJun
              nQtr3 := nJul+nAgt+nSep
              nQtr4 := nOkt+nNov+nDes
              nTotal := nQtr1+nQtr2+nQtr3+nQtr4
              nSJan := nSJan + nJan
              nSFeb := nSFeb + nFeb
              nSMar := nSMar + nMar
              nSApr := nSApr + nApr
              nSMei := nSMei + nMei
              nSJun := nSJun + nJun
              nSJul := nSJul + nJul
              nSAgt := nSAgt + nAgt
              nSSep := nSSep + nSep
              nSOkt := nSOkt + nOkt
              nSNov := nSNov + nNov
              nSDes := nSDes + nDes
              nSQtr1:= nSQtr1 + nQtr1
              nSQtr2:= nSQtr2 + nQtr2
              nSQtr3:= nSQtr3 + nQtr3
              nSQtr4:= nSQtr4 + nQtr4
              nSTotal := nSTotal + nTotal
              nTJan := nTJan + nJan
              nTFeb := nTFeb + nFeb
              nTMar := nTMar + nMar
              nTApr := nTApr + nApr
              nTMei := nTMei + nMei
              nTJun := nTJun + nJun
              nTJul := nTJul + nJul
              nTAgt := nTAgt + nAgt
              nTSep := nTSep + nSep
              nTOkt := nTOkt + nOkt
              nTNov := nTNov + nNov
              nTDes := nTDes + nDes
              nTQtr1:= nTQtr1 + nQtr1
              nTQtr2:= nTQtr2 + nQtr2
              nTQtr3:= nTQtr3 + nQtr3
              nTQtr4:= nTQtr4 + nQtr4
              nTTotal := nTTotal + nTotal
              nGJan := nGJan + nJan
              nGFeb := nGFeb + nFeb
              nGMar := nGMar + nMar
              nGApr := nGApr + nApr
              nGMei := nGMei + nMei
              nGJun := nGJun + nJun
              nGJul := nGJul + nJul
              nGAgt := nGAgt + nAgt
              nGSep := nGSep + nSep
              nGOkt := nGOkt + nOkt
              nGNov := nGNov + nNov
              nGDes := nGDes + nDes
              nGQtr1:= nGQtr1 + nQtr1
              nGQtr2:= nGQtr2 + nQtr2
              nGQtr3:= nGQtr3 + nQtr3
              nGQtr4:= nGQtr4 + nQtr4
              nGTotal := nGTotal + nTotal
              @ prow()+1,3   say cFGCode                     picture "@!"
              FGMast->( dbSeek(cFGCode) )
              @ prow()  ,11  say substr(FGMast->Desc,1,20)   picture "@!"
              @ prow()  ,34  say nJan/nBagi                 picture "@z 999,999,999"
              @ prow()  ,47  say nFeb/nBagi                 picture "@z 999,999,999"
              @ prow()  ,60  say nMar/nBagi                 picture "@z 999,999,999"
              @ prow()  ,73  say nQtr1/nBagi                picture "@z 999,999,999"
              @ prow()  ,86  say nApr/nBagi                 picture "@z 999,999,999"
              @ prow()  ,99  say nMei/nBagi                 picture "@z 999,999,999"
              @ prow()  ,112 say nJun/nBagi                 picture "@z 999,999,999"
              @ prow()  ,125 say nQtr2/nBagi                picture "@z 999,999,999"
              @ prow()  ,138 say nJul/nBagi                 picture "@z 999,999,999"
              @ prow()  ,151 say nAgt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,164 say nSep/nBagi                 picture "@z 999,999,999"
              @ prow()  ,177 say nQtr3/nBagi                picture "@z 999,999,999"
              @ prow()  ,190 say nOkt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,203 say nNov/nBagi                 picture "@z 999,999,999"
              @ prow()  ,216 say nDes/nBagi                 picture "@z 999,999,999"
              @ prow()  ,229 say nQtr4/nBagi                picture "@z 999,999,999"
              @ prow()  ,244 say nTotal/nBagi               picture "@z 999,999,999"
              RMValue->( dbAppend() )
              RMValue->FGCode := cFGCode
              RMValue->PrType1 := cPrType1
              RMValue->PrType2 := cPrType2
              RMValue->Jan    := nJan/nBagi
              RMValue->Feb    := nFeb/nBagi
              RMValue->Mar    := nMar/nBagi
              RMValue->Apr    := nApr/nBagi
              RMValue->Mei    := nMei/nBagi
              RMValue->Jun    := nJun/nBagi
              RMValue->Jul    := nJul/nBagi
              RMValue->Agt    := nAgt/nBagi
              RMValue->Sep    := nSep/nBagi
              RMValue->Okt    := nOkt/nBagi
              RMValue->Nov    := nNov/nBagi
              RMValue->Des    := nDes/nBagi
              RMValue->( dbCommit() )
              store 0 to nJan,nFeb,nMar,nApr,nMei,nJun,nJul
              store 0 to nAgt,nSep,nOkt,nNov,nDes,nQtr1,nQtr2
              store 0 to nQtr3,nQtr4,nTotal
           endif
           if cPrType2 # Volume->PrType2
              @ prow()+1,1   say " "
              @ prow()+1,1   say "Sub Total "
              TypeMast->( dbSeek(cPrType2) )
              @ prow(),pcol() say substr(TypeMast->Desc,1,20) picture "@!"
              @ prow()  ,34  say nSJan/nBagi                 picture "@z 999,999,999"
              @ prow()  ,47  say nSFeb/nBagi                 picture "@z 999,999,999"
              @ prow()  ,60  say nSMar/nBagi                 picture "@z 999,999,999"
              @ prow()  ,73  say nSQtr1/nBagi                picture "@z 999,999,999"
              @ prow()  ,86  say nSApr/nBagi                 picture "@z 999,999,999"
              @ prow()  ,99  say nSMei/nBagi                 picture "@z 999,999,999"
              @ prow()  ,112 say nSJun/nBagi                 picture "@z 999,999,999"
              @ prow()  ,125 say nSQtr2/nBagi                picture "@z 999,999,999"
              @ prow()  ,138 say nSJul/nBagi                 picture "@z 999,999,999"
              @ prow()  ,151 say nSAgt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,164 say nSSep/nBagi                 picture "@z 999,999,999"
              @ prow()  ,177 say nSQtr3/nBagi                picture "@z 999,999,999"
              @ prow()  ,190 say nSOkt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,203 say nSNov/nBagi                 picture "@z 999,999,999"
              @ prow()  ,216 say nSDes/nBagi                 picture "@z 999,999,999"
              @ prow()  ,229 say nSQtr4/nBagi                picture "@z 999,999,999"
              @ prow()  ,244 say nSTotal/nBagi               picture "@z 999,999,999"
              @ prow()+1,1   say " "
              store 0 to nSJan,nSFeb,nSMar,nSApr,nSMei,nSJun,nSJul
              store 0 to nSAgt,nSSep,nSOkt,nSNov,nSDes,nSQtr1,nSQtr2
              store 0 to nSQtr3,nSQtr4,nSTotal
           endif
           if cPrType1 # Volume->PrType1
              @ prow()+1,1   say "    Total"
              @ prow()  ,34  say nTJan/nBagi                 picture "@z 999,999,999"
              @ prow()  ,47  say nTFeb/nBagi                 picture "@z 999,999,999"
              @ prow()  ,60  say nTMar/nBagi                 picture "@z 999,999,999"
              @ prow()  ,73  say nTQtr1/nBagi                picture "@z 999,999,999"
              @ prow()  ,86  say nTApr/nBagi                 picture "@z 999,999,999"
              @ prow()  ,99  say nTMei/nBagi                 picture "@z 999,999,999"
              @ prow()  ,112 say nTJun/nBagi                 picture "@z 999,999,999"
              @ prow()  ,125 say nTQtr2/nBagi                picture "@z 999,999,999"
              @ prow()  ,138 say nTJul/nBagi                 picture "@z 999,999,999"
              @ prow()  ,151 say nTAgt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,164 say nTSep/nBagi                 picture "@z 999,999,999"
              @ prow()  ,177 say nTQtr3/nBagi                picture "@z 999,999,999"
              @ prow()  ,190 say nTOkt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,203 say nTNov/nBagi                 picture "@z 999,999,999"
              @ prow()  ,216 say nTDes/nBagi                 picture "@z 999,999,999"
              @ prow()  ,229 say nTQtr4/nBagi                picture "@z 999,999,999"
              @ prow()  ,244 say nTTotal/nBagi               picture "@z 999,999,999"
              @ prow()+1,1   say " "
              store 0 to nTJan,nTFeb,nTMar,nTApr,nTMei,nTJun,nTJul
              store 0 to nTAgt,nTSep,nTOkt,nTNov,nTDes,nTQtr1,nTQtr2
              store 0 to nTQtr3,nTQtr4,nTTotal
           endif
           if Volume->( Eof() )
               exit
           endif
           if prow() >= 55
              nSW  := 0
              eject
           endif
        endif
      enddo
      @ prow()+1,1   say repli ( '=',nWidth )
      @ prow()+1,5   say "Grand Total"
      @ prow()  ,34  say nGJan/nBagi                 picture "@z 999,999,999"
      @ prow()  ,47  say nGFeb/nBagi                 picture "@z 999,999,999"
      @ prow()  ,60  say nGMar/nBagi                 picture "@z 999,999,999"
      @ prow()  ,73  say nGQtr1/nBagi                picture "@z 999,999,999"
      @ prow()  ,86  say nGApr/nBagi                 picture "@z 999,999,999"
      @ prow()  ,99  say nGMei/nBagi                 picture "@z 999,999,999"
      @ prow()  ,112 say nGJun/nBagi                 picture "@z 999,999,999"
      @ prow()  ,125 say nGQtr2/nBagi                picture "@z 999,999,999"
      @ prow()  ,138 say nGJul/nBagi                 picture "@z 999,999,999"
      @ prow()  ,151 say nGAgt/nBagi                 picture "@z 999,999,999"
      @ prow()  ,164 say nGSep/nBagi                 picture "@z 999,999,999"
      @ prow()  ,177 say nGQtr3/nBagi                picture "@z 999,999,999"
      @ prow()  ,190 say nGOkt/nBagi                 picture "@z 999,999,999"
      @ prow()  ,203 say nGNov/nBagi                 picture "@z 999,999,999"
      @ prow()  ,216 say nGDes/nBagi                 picture "@z 999,999,999"
      @ prow()  ,229 say nGQtr4/nBagi                picture "@z 999,999,999"
      @ prow()  ,242 say nGTotal/nBagi               picture "@z 9,999,999,999"
      @ prow()+1,1   say repli ( '=',nWidth )
      eject
      @prow(), pcol() say chr(27)+chr(80)+chr(18)  &&20 condensed
      *@prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on

      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"RMValue.prn")
         fErase ( "RMValue.prn" )
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil

