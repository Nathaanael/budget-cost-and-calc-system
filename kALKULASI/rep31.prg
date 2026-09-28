#Include "Stock.ch"

function Report62()
   local cPrint,cOption,cArea1,cArea2,cArea3,cArea4,cArea5,cArea6,cArea7,cArea8,cArea9,cArea10
   local cPrType1,cFGCode,cMode,cDesc,cPrType2,nDivide,nBagi,nBDesc
   local nJul := 0,nAgt := 0,nSep := 0,nOkt := 0,nNov := 0,nDes := 0
   local nSJul := 0,nSAgt := 0,nSSep := 0,nSOkt := 0,nSNov := 0,nSDes := 0
   local nTJul := 0,nTAgt := 0,nTSep := 0,nTOkt := 0,nTNov := 0,nTDes := 0
   local nGJul := 0,nGAgt := 0,nGSep := 0,nGOkt := 0,nGNov := 0,nGDes := 0
   local nTotal:= 0,nSTotal := 0,nTTotal:= 0,nGTotal := 0
   local nPriceLE := 0
   local nWidth    := 124
   local nSW       := 0
   local nPage     := 0
   local GetList   := {}
   local cClr      := setcolor()
   local cScr      := savescreen()
   dataDict()
   set date British
   set score off

   do while .T.
      cOption := alert ( 'Location : ',{'CIKAMPEK','SEMARANG','SURABAYA','NATIONAL'})
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
      @ prow(),pcol() say chr(15)   &&chr(27) + chr(77) + chr(15)  20 condensed
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
             @ prow()  ,110  say "Date : "
             @ prow(),pcol() say date()
             @ prow()+1,1    say CostRef->Desc2
             @ prow()  ,110  say "Time : "
             @ prow(),pcol() say time()
             @ prow()+1,53   say " RAW MATERIAL  V A L U E  LE"
             @ prow()  ,110  say "Page : "
             @ prow(),pcol() say nPage picture "999"
             @ prow()+1,1    say cDesc  picture "@!"
             @ prow()  ,53   say "      AOP Year : "
             @ prow(),pcol() say ltrim(str(val(Right(CostRef->Per,4))+1)) picture "@!"
             @ prow()  ,110  say "In ' "
             @ prow(),pcol() say nBDesc picture "@!"
             @ prow()+1,1    say repli ( '=',nWidth )
             *                             1         2         3         4         5         6         7         8         9         0         1         2
             *                   012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345
             @ prow()+1,1    say "|         Finished Goods       |-------------------------------------------------------------------------------------------|"
             @ prow()+1,1    say "|                              |    Juli     |  Agustus   |  September |  Oktober   |  November  |  Desember  |   Total    |"
             *                      xxxxxxx xxxxxxxxxxxxxxxxxxxx   99,999,999    99,999,999   99,999,999   99,999,999   99,999,999   99,999,999   99,999,999
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
                 nPriceLE := FGMast->UCLE
                 do while Volume->FGCode == cFGCode .and. Volume->PrType2 == cPrType2 .and. prow()<= 55 .and. ! Volume->(Eof())
                     nJul := nJul + Volume->LEJul
                     nAgt := nAgt + Volume->LEAgt
                     nSep := nSep + Volume->LESep
                     nOkt := nOkt + Volume->LEOkt
                     nNov := nNov + Volume->LENov
                     nDes := nDes + Volume->LEDes
                     Volume->( dbSkip() )
                 enddo
                 nJul := round(nJul * nPriceLE,0)
                 nAgt := round(nAgt * nPriceLE,0)
                 nSep := round(nSep * nPriceLE,0)
                 nOkt := round(nOkt * nPriceLE,0)
                 nNov := round(nNov * nPriceLE,0)
                 nDes := round(nDes * nPriceLE,0)
                 nSJul := nSJul + nJul
                 nSAgt := nSAgt + nAgt
                 nSSep := nSSep + nSep
                 nSOkt := nSOkt + nOkt
                 nSNov := nSNov + nNov
                 nSDes := nSDes + nDes
                 store 0 to nJul,nAgt,nSep,nOkt,nNov,nDes
              enddo
              nTotal := nSJul+nSAgt+nSSep+nSOkt+nSNov+nSDes
              nTJul := nTJul + nSJul
              nTAgt := nTAgt + nSAgt
              nTSep := nTSep + nSSep
              nTOkt := nTOkt + nSOkt
              nTNov := nTNov + nSNov
              nTDes := nTDes + nSDes
              nTTotal := nTTotal + nTotal
              nGJul := nGJul + nSJul
              nGAgt := nGAgt + nSAgt
              nGSep := nGSep + nSSep
              nGOkt := nGOkt + nSOkt
              nGNov := nGNov + nSNov
              nGDes := nGDes + nSDes
              nGTotal := nGTotal + nTotal
              TypeMast->( dbSeek(cPrType2) )
              @ prow()+1,05  say substr(TypeMast->Desc,1,20)   picture "@!"
              @ prow()  ,34  say nSJul/nBagi                   picture "@z 999,999,999"
              @ prow()  ,48  say nSAgt/nBagi                   picture "@z 999,999,999"
              @ prow()  ,61  say nSSep/nBagi                   picture "@z 999,999,999"
              @ prow()  ,74  say nSOkt/nBagi                   picture "@z 999,999,999"
              @ prow()  ,87  say nSNov/nBagi                   picture "@z 999,999,999"
              @ prow()  ,100 say nSDes/nBagi                   picture "@z 999,999,999"
              @ prow()  ,113 say nTotal/nBagi                  picture "@z 999,999,999"
              *@ prow()+1,1   say " "
              store 0 to nSJul,nSAgt,nSSep,nSOkt,nSNov,nSDes,nTotal
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
           @ prow()  ,34  say nTJul/nBagi                 picture "@z 999,999,999"
           @ prow()  ,48  say nTAgt/nBagi                 picture "@z 999,999,999"
           @ prow()  ,61  say nTSep/nBagi                 picture "@z 999,999,999"
           @ prow()  ,74  say nTOkt/nBagi                 picture "@z 999,999,999"
           @ prow()  ,87  say nTNov/nBagi                 picture "@z 999,999,999"
           @ prow()  ,100 say nTDes/nBagi                 picture "@z 999,999,999"
           @ prow()  ,113 say nTTotal/nBagi               picture "@z 999,999,999"
           @ prow()+1,1   say " "
           store 0 to nTJul,nTAgt,nTSep,nTOkt,nTNov,nTDes,nTTotal
        else
           cPrType1 := Volume->PrType1
           cPrType2 := Volume->PrType2
           cFGCode := Volume->FGCode
           FGMast->( dbSeek(cFGCode) )
           nPriceLE := FGMast->UCLE
           do while cFGCode == Volume->FGCode .and. ! Volume->(Eof())
              nJul := nJul + Volume->LEJul
              nAgt := nAgt + Volume->LEAgt
              nSep := nSep + Volume->LESep
              nOkt := nOkt + Volume->LEOkt
              nNov := nNov + Volume->LENov
              nDes := nDes + Volume->LEDes
              Volume->( dbSkip() )
           enddo
           nJul := round(nJul * nPriceLE,0)
           nAgt := round(nAgt * nPriceLE,0)
           nSep := round(nSep * nPriceLE,0)
           nOkt := round(nOkt * nPriceLE,0)
           nNov := round(nNov * nPriceLE,0)
           nDes := round(nDes * nPriceLE,0)
           if nJul+nAgt+nSep+nOkt+nNov+nDes # 0
              nTotal := nJul+nAgt+nSep+nOkt+nNov+nDes
              nSJul := nSJul + nJul
              nSAgt := nSAgt + nAgt
              nSSep := nSSep + nSep
              nSOkt := nSOkt + nOkt
              nSNov := nSNov + nNov
              nSDes := nSDes + nDes
              nSTotal := nSTotal + nTotal
              nTJul := nTJul + nJul
              nTAgt := nTAgt + nAgt
              nTSep := nTSep + nSep
              nTOkt := nTOkt + nOkt
              nTNov := nTNov + nNov
              nTDes := nTDes + nDes
              nTTotal := nTTotal + nTotal
              nGJul := nGJul + nJul
              nGAgt := nGAgt + nAgt
              nGSep := nGSep + nSep
              nGOkt := nGOkt + nOkt
              nGNov := nGNov + nNov
              nGDes := nGDes + nDes
              nGTotal := nGTotal + nTotal
              @ prow()+1,3   say cFGCode                     picture "@!"
              FGMast->( dbSeek(cFGCode) )
              @ prow()  ,11  say substr(FGMast->Desc,1,20)   picture "@!"
              @ prow()  ,34  say nJul/nBagi                 picture "@z 999,999,999"
              @ prow()  ,48  say nAgt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,61  say nSep/nBagi                 picture "@z 999,999,999"
              @ prow()  ,74  say nOkt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,87  say nNov/nBagi                 picture "@z 999,999,999"
              @ prow()  ,100 say nDes/nBagi                 picture "@z 999,999,999"
              @ prow()  ,113 say nTotal/nBagi               picture "@z 999,999,999"
              RMValue->( dbAppend() )
              RMValue->FGCode := cFGCode
              RMValue->PrType1 := cPrType1
              RMValue->PrType2 := cPrType2
              RMValue->LEJul := nJul/nBagi
              RMValue->LEAgt := nAgt/nBagi
              RMValue->LESep := nSep/nBagi
              RMValue->LEOkt := nOkt/nBagi
              RMValue->LENov := nNov/nBagi
              RMValue->LEDes := nDes/nBagi
              RMValue->( dbCOmmit() )
              store 0 to nJul,nAgt,nSep,nOkt,nNov,nDes,nTotal
           endif
           if cPrType2 # Volume->PrType2
              @ prow()+1,1   say " "
              @ prow()+1,1   say "Sub Total "
              TypeMast->( dbSeek(cPrType2) )
              @ prow(),pcol() say substr(TypeMast->Desc,1,20) picture "@!"
              @ prow()  ,34  say nSJul/nBagi                 picture "@z 999,999,999"
              @ prow()  ,48  say nSAgt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,61  say nSSep/nBagi                 picture "@z 999,999,999"
              @ prow()  ,74  say nSOkt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,87  say nSNov/nBagi                 picture "@z 999,999,999"
              @ prow()  ,100 say nSDes/nBagi                 picture "@z 999,999,999"
              @ prow()  ,113 say nSTotal/nBagi               picture "@z 999,999,999"
              @ prow()+1,1   say " "
              store 0 to nSJul,nSAgt,nSSep,nSOkt,nSNov,nSDes,nSTotal
           endif
           if cPrType1 # Volume->PrType1
              @ prow()+1,1   say "    Total"
              @ prow()  ,34  say nTJul/nBagi                 picture "@z 999,999,999"
              @ prow()  ,48  say nTAgt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,61  say nTSep/nBagi                 picture "@z 999,999,999"
              @ prow()  ,74  say nTOkt/nBagi                 picture "@z 999,999,999"
              @ prow()  ,87  say nTNov/nBagi                 picture "@z 999,999,999"
              @ prow()  ,100 say nTDes/nBagi                 picture "@z 999,999,999"
              @ prow()  ,113 say nTTotal/nBagi               picture "@z 999,999,999"
              @ prow()+1,1   say " "
              store 0 to nTJul,nTAgt,nTSep,nTOkt,nTNov,nTDes,nTTotal
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
      @ prow()  ,34  say nGJul/nBagi                 picture "@z 999,999,999"
      @ prow()  ,48  say nGAgt/nBagi                 picture "@z 999,999,999"
      @ prow()  ,61  say nGSep/nBagi                 picture "@z 999,999,999"
      @ prow()  ,74  say nGOkt/nBagi                 picture "@z 999,999,999"
      @ prow()  ,87  say nGNov/nBagi                 picture "@z 999,999,999"
      @ prow()  ,100 say nGDes/nBagi                 picture "@z 999,999,999"
      @ prow()  ,113 say nGTotal/nBagi               picture "@z 999,999,999"
      @ prow()+1,1   say repli ( '=',nWidth )
      eject
      *@prow(), pcol() say chr(27)+chr(80)+chr(18)  &&20 condensed
      @prow(), pcol() say chr(18)
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

