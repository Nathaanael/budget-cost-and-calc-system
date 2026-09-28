#Include "Stock.ch"

function Report511(unit)
   local cPrint,cOption,cArea1,cArea2,cArea3,cArea4,cArea5,cArea6,cArea7,cArea8,cArea9,cArea10
   local cPrType1,cFGCode,cMode,cDesc,cPrType2,nDivide,nBagi,nBDesc
   local nJan := 0,nFeb := 0,nMar := 0,nApr := 0,nMei := 0,nJun := 0
   local nJul := 0,nAgt := 0,nSep := 0,nOkt := 0,nNov := 0,nDes := 0
   local nJan0 := 0,nFeb0 := 0,nMar0 := 0,nApr0 := 0,nMei0 := 0,nJun0 := 0
   local nJul0 := 0,nAgt0 := 0,nSep0 := 0,nOkt0 := 0,nNov0 := 0,nDes0 := 0
   local nJan1 := 0,nFeb1 := 0,nMar1 := 0,nApr1 := 0,nMei1 := 0,nJun1 := 0
   local nJul1 := 0,nAgt1 := 0,nSep1 := 0,nOkt1 := 0,nNov1 := 0,nDes1 := 0
   local nJan2 := 0,nFeb2 := 0,nMar2 := 0,nApr2 := 0,nMei2 := 0,nJun2 := 0
   local nJul2 := 0,nAgt2 := 0,nSep2 := 0,nOkt2 := 0,nNov2 := 0,nDes2 := 0
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
   local nPrice1  := 0,nPrice2  := 0,nPrice3  := 0,nPrice4  := 0
   local nPrice01 := 0,nPrice02 := 0,nPrice03 := 0,nPrice04 := 0
   local nPrice11 := 0,nPrice12 := 0,nPrice13 := 0,nPrice14 := 0
   local nPrice21 := 0,nPrice22 := 0,nPrice23 := 0,nPrice24 := 0
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
        set print to "SlsValue.prn"
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
            *loca for Factory->code = 'S1'
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
      sele SLSValue
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
             @ prow()+1,110  say " S A L E S   V A L U E "
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
                 store 0 to nPrice01,nPrice02,nPrice03,nPrice04
                 store 0 to nPrice11,nPrice12,nPrice13,nPrice14
                 store 0 to nPrice21,nPrice22,nPrice23,nPrice24

                 cFGCode := Volume->FGCode
                 FGMast->( dbSeek(cFGCode) )
                 Do case
                    case cOption == 1
                         nPrice01 := FGMast->Price01
                         nPrice02 := FGMast->Price02
                         nPrice03 := FGMast->Price03
                         nPrice04 := FGMast->Price04
                    case cOption == 2
                         nPrice11 := FGMast->Price11
                         nPrice12 := FGMast->Price12
                         nPrice13 := FGMast->Price13
                         nPrice14 := FGMast->Price14
                    case cOption == 3
                         nPrice21 := FGMast->Price21
                         nPrice22 := FGMast->Price22
                         nPrice23 := FGMast->Price23
                         nPrice24 := FGMast->Price24
                    Other
                         nPrice01 := FGMast->Price01
                         nPrice02 := FGMast->Price02
                         nPrice03 := FGMast->Price03
                         nPrice04 := FGMast->Price04

                         nPrice11 := FGMast->Price11
                         nPrice12 := FGMast->Price12
                         nPrice13 := FGMast->Price13
                         nPrice14 := FGMast->Price14

                         nPrice21 := FGMast->Price21
                         nPrice22 := FGMast->Price22
                         nPrice23 := FGMast->Price23
                         nPrice24 := FGMast->Price24
                 Endcase
                 store 0 to nJan0,nFeb0,nMar0,nApr0,nMei0,nJun0,nJul0
                 store 0 to nAgt0,nSep0,nOkt0,nNov0,nDes0
                 store 0 to nJan1,nFeb1,nMar1,nApr1,nMei1,nJun1,nJul1
                 store 0 to nAgt1,nSep1,nOkt1,nNov1,nDes1
                 store 0 to nJan2,nFeb2,nMar2,nApr2,nMei2,nJun2,nJul2
                 store 0 to nAgt2,nSep2,nOkt2,nNov2,nDes2
                 do while Volume->FGCode == cFGCode .and. Volume->PrType2 == cPrType2 .and. prow()<= 55 .and. ! Volume->(Eof())
                    mcd:=Volume->code
                    if mcd="C1".or.mcd="C2".or.mcd="W3".or.mcd="W4".or.mcd="C5".or.mcd="W1".or.mcd="W2".or.mcd="W5"
                       nJan0 := nJan0 + Volume->Jan
                       nFeb0 := nFeb0 + Volume->Feb
                       nMar0 := nMar0 + Volume->Mar
                       nApr0 := nApr0 + Volume->Apr
                       nMei0 := nMei0 + Volume->Mei
                       nJun0 := nJun0 + Volume->Jun
                       nJul0 := nJul0 + Volume->Jul
                       nAgt0 := nAgt0 + Volume->Agt
                       nSep0 := nSep0 + Volume->Sep
                       nOkt0 := nOkt0 + Volume->Okt
                       nNov0 := nNov0 + Volume->Nov
                       nDes0 := nDes0 + Volume->Des
                    Elseif mcd="C3".or.mcd="C4".or.mcd="C6"
                       nJan1 := nJan1 + Volume->Jan
                       nFeb1 := nFeb1 + Volume->Feb
                       nMar1 := nMar1 + Volume->Mar
                       nApr1 := nApr1 + Volume->Apr
                       nMei1 := nMei1 + Volume->Mei
                       nJun1 := nJun1 + Volume->Jun
                       nJul1 := nJul1 + Volume->Jul
                       nAgt1 := nAgt1 + Volume->Agt
                       nSep1 := nSep1 + Volume->Sep
                       nOkt1 := nOkt1 + Volume->Okt
                       nNov1 := nNov1 + Volume->Nov
                       nDes1 := nDes1 + Volume->Des
                    elseif mcd="E1".or.mcd="E2".or.mcd="E3".or.mcd="E4"
                       nJan2 := nJan2 + Volume->Jan
                       nFeb2 := nFeb2 + Volume->Feb
                       nMar2 := nMar2 + Volume->Mar
                       nApr2 := nApr2 + Volume->Apr
                       nMei2 := nMei2 + Volume->Mei
                       nJun2 := nJun2 + Volume->Jun
                       nJul2 := nJul2 + Volume->Jul
                       nAgt2 := nAgt2 + Volume->Agt
                       nSep2 := nSep2 + Volume->Sep
                       nOkt2 := nOkt2 + Volume->Okt
                       nNov2 := nNov2 + Volume->Nov
                       nDes2 := nDes2 + Volume->Des
                    Endif
                    Volume->( dbSkip() )
                 enddo
                 nJan := ( round(nJan0 * nPrice01,0) + round(nJan1 * nPrice11,0) + round(nJan2 * nPrice21,0) )
                 nFeb := ( round(nFeb0 * nPrice01,0) + round(nFeb1 * nPrice11,0) + round(nFeb2 * nPrice21,0) )
                 nMar := ( round(nMar0 * nPrice01,0) + round(nMar1 * nPrice11,0) + round(nMar2 * nPrice21,0) )
                 nApr := ( round(nApr0 * nPrice02,0) + round(nApr1 * nPrice12,0) + round(nApr2 * nPrice22,0) )
                 nMei := ( round(nMei0 * nPrice02,0) + round(nMei1 * nPrice12,0) + round(nMei2 * nPrice22,0) )
                 nJun := ( round(nJun0 * nPrice02,0) + round(nJun1 * nPrice12,0) + round(nJun2 * nPrice22,0) )
                 nJul := ( round(nJul0 * nPrice03,0) + round(nJul1 * nPrice13,0) + round(nJul2 * nPrice23,0) )
                 nAgt := ( round(nAgt0 * nPrice03,0) + round(nAgt1 * nPrice13,0) + round(nAgt2 * nPrice23,0) )
                 nSep := ( round(nSep0 * nPrice03,0) + round(nSep1 * nPrice13,0) + round(nSep2 * nPrice23,0) )
                 nOkt := ( round(nOkt0 * nPrice04,0) + round(nOkt1 * nPrice14,0) + round(nOkt2 * nPrice24,0) )
                 nNov := ( round(nNov0 * nPrice04,0) + round(nNov1 * nPrice14,0) + round(nNov2 * nPrice24,0) )
                 nDes := ( round(nDes0 * nPrice04,0) + round(nDes1 * nPrice14,0) + round(nDes2 * nPrice24,0) )

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
           cFGCode  := Volume->FGCode
           store 0 to nPrice01,nPrice02,nPrice03,nPrice04
           store 0 to nPrice11,nPrice12,nPrice13,nPrice14
           store 0 to nPrice21,nPrice22,nPrice23,nPrice24
           FGMast->( dbSeek(cFGCode) )
           Do case
              case cOption == 1
                   nPrice01 := FGMast->Price01
                   nPrice02 := FGMast->Price02
                   nPrice03 := FGMast->Price03
                   nPrice04 := FGMast->Price04
              case cOption == 2
                   nPrice11 := FGMast->Price11
                   nPrice12 := FGMast->Price12
                   nPrice13 := FGMast->Price13
                   nPrice14 := FGMast->Price14
              case cOption == 3
                   nPrice21 := FGMast->Price21
                   nPrice22 := FGMast->Price22
                   nPrice23 := FGMast->Price23
                   nPrice24 := FGMast->Price24
              Other
                   nPrice01 := FGMast->Price01
                   nPrice02 := FGMast->Price02
                   nPrice03 := FGMast->Price03
                   nPrice04 := FGMast->Price04

                   nPrice11 := FGMast->Price11
                   nPrice12 := FGMast->Price12
                   nPrice13 := FGMast->Price13
                   nPrice14 := FGMast->Price14

                   nPrice21 := FGMast->Price21
                   nPrice22 := FGMast->Price22
                   nPrice23 := FGMast->Price23
                   nPrice24 := FGMast->Price24
           Endcase
           store 0 to nJan0,nFeb0,nMar0,nApr0,nMei0,nJun0,nJul0
           store 0 to nAgt0,nSep0,nOkt0,nNov0,nDes0
           store 0 to nJan1,nFeb1,nMar1,nApr1,nMei1,nJun1,nJul1
           store 0 to nAgt1,nSep1,nOkt1,nNov1,nDes1
           store 0 to nJan2,nFeb2,nMar2,nApr2,nMei2,nJun2,nJul2
           store 0 to nAgt2,nSep2,nOkt2,nNov2,nDes2
           do while cFGCode == Volume->FGCode .and. ! Volume->(Eof())
                 mcd:=Volume->code
                 if mcd="C1".or.mcd="C2".or.mcd="W3".or.mcd="W4".or.mcd="C5".or.mcd="W1".or.mcd="W2".or.mcd="W5"
                    nJan0 := nJan0 + Volume->Jan
                    nFeb0 := nFeb0 + Volume->Feb
                    nMar0 := nMar0 + Volume->Mar
                    nApr0 := nApr0 + Volume->Apr
                    nMei0 := nMei0 + Volume->Mei
                    nJun0 := nJun0 + Volume->Jun
                    nJul0 := nJul0 + Volume->Jul
                    nAgt0 := nAgt0 + Volume->Agt
                    nSep0 := nSep0 + Volume->Sep
                    nOkt0 := nOkt0 + Volume->Okt
                    nNov0 := nNov0 + Volume->Nov
                    nDes0 := nDes0 + Volume->Des
                 Elseif mcd="C3".or.mcd="C4".or.mcd="C6"
                    nJan1 := nJan1 + Volume->Jan
                    nFeb1 := nFeb1 + Volume->Feb
                    nMar1 := nMar1 + Volume->Mar
                    nApr1 := nApr1 + Volume->Apr
                    nMei1 := nMei1 + Volume->Mei
                    nJun1 := nJun1 + Volume->Jun
                    nJul1 := nJul1 + Volume->Jul
                    nAgt1 := nAgt1 + Volume->Agt
                    nSep1 := nSep1 + Volume->Sep
                    nOkt1 := nOkt1 + Volume->Okt
                    nNov1 := nNov1 + Volume->Nov
                    nDes1 := nDes1 + Volume->Des
                 elseif mcd="E1".or.mcd="E2".or.mcd="E3".or.mcd="E4"
                    nJan2 := nJan2 + Volume->Jan
                    nFeb2 := nFeb2 + Volume->Feb
                    nMar2 := nMar2 + Volume->Mar
                    nApr2 := nApr2 + Volume->Apr
                    nMei2 := nMei2 + Volume->Mei
                    nJun2 := nJun2 + Volume->Jun
                    nJul2 := nJul2 + Volume->Jul
                    nAgt2 := nAgt2 + Volume->Agt
                    nSep2 := nSep2 + Volume->Sep
                    nOkt2 := nOkt2 + Volume->Okt
                    nNov2 := nNov2 + Volume->Nov
                    nDes2 := nDes2 + Volume->Des
                 Endif
              Volume->( dbSkip() )
           enddo
           nJan := ( round(nJan0 * nPrice01,0) + round(nJan1 * nPrice11,0) + round(nJan2 * nPrice21,0) )
           nFeb := ( round(nFeb0 * nPrice01,0) + round(nFeb1 * nPrice11,0) + round(nFeb2 * nPrice21,0) )
           nMar := ( round(nMar0 * nPrice01,0) + round(nMar1 * nPrice11,0) + round(nMar2 * nPrice21,0) )
           nApr := ( round(nApr0 * nPrice02,0) + round(nApr1 * nPrice12,0) + round(nApr2 * nPrice22,0) )
           nMei := ( round(nMei0 * nPrice02,0) + round(nMei1 * nPrice12,0) + round(nMei2 * nPrice22,0) )
           nJun := ( round(nJun0 * nPrice02,0) + round(nJun1 * nPrice12,0) + round(nJun2 * nPrice22,0) )
           nJul := ( round(nJul0 * nPrice03,0) + round(nJul1 * nPrice13,0) + round(nJul2 * nPrice23,0) )
           nAgt := ( round(nAgt0 * nPrice03,0) + round(nAgt1 * nPrice13,0) + round(nAgt2 * nPrice23,0) )
           nSep := ( round(nSep0 * nPrice03,0) + round(nSep1 * nPrice13,0) + round(nSep2 * nPrice23,0) )
           nOkt := ( round(nOkt0 * nPrice04,0) + round(nOkt1 * nPrice14,0) + round(nOkt2 * nPrice24,0) )
           nNov := ( round(nNov0 * nPrice04,0) + round(nNov1 * nPrice14,0) + round(nNov2 * nPrice24,0) )
           nDes := ( round(nDes0 * nPrice04,0) + round(nDes1 * nPrice14,0) + round(nDes2 * nPrice24,0) )
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
              SLSValue->( dbAppend() )
              SLSValue->FGCode  := cFGCode
              SLSValue->PrType1 := cPrType1
              SLSValue->PrType2 := cPrType2
              SLSValue->Jan     := nJan/nBagi
              SLSValue->Feb     := nFeb/nBagi
              SLSValue->Mar     := nMar/nBagi
              SLSValue->Apr     := nApr/nBagi
              SLSValue->Mei     := nMei/nBagi
              SLSValue->Jun     := nJun/nBagi
              SLSValue->Jul     := nJul/nBagi
              SLSValue->Agt     := nAgt/nBagi
              SLSValue->Sep     := nSep/nBagi
              SLSValue->Okt     := nOkt/nBagi
              SLSValue->Nov     := nNov/nBagi
              SLSValue->Des     := nDes/nBagi
              SLSValue->( dbCommit() )
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
         FileRead (0,0,24,79,"SlsValue.prn")
         fErase ( "SlsValue.prn" )
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil

