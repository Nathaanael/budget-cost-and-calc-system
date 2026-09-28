#include "stock.ch"
function Calc()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect

  do while .T.
     set key 4  to Rkey
     set key 19 to Lkey
     sbox(9,27,14,44,"gr+/bg+,w+/br+")
     @ 10     ,28 prompt " Purchase Price "
     @ row()+1,28 prompt " Matching Price "
     @ row()+1,28 prompt " U.Cost+U.Price "
     @ row()+1,28 prompt " Volume Noodle  "

     menu to nSelect
     set key 4  to
     set key 19 to

     do case
       case nSelect == 0
         exit
       case nSelect == 1
         Calc1()
       case nSelect == 2
         Calc2()
       case nSelect == 3
         Calc3()
       case nSelect == 4
         Calc4()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)

return nil

function Calc1()
    local nRate0,nRate1,nRate2,nRate3,nRate4,nRateLE
    local cClr:=setcolor()
    local cScr:=savescreen()
    set score off
    set date British
    dataDict()
    do while .T.
        if alert ( " Calculate : ", { " Yes ", " No " } ) == 1
           sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
           setcolor( "gr+*/bg,w+/br+" )
           @ 21,25  say " Wait for a minutes ..... "
           nRate0  := CostRef->Rate0
           nRateLE := CostRef->RateLE
           nRate1  := CostRef->Rate1
           nRate2  := CostRef->Rate2
           nRate3  := CostRef->Rate3
           nRate4  := CostRef->Rate4
           sele RMMast
           set filter to RMMast->Type_Curr == '1'
           RMMast->( dbGoTop() )
           do while ! RMMast->( Eof() )
             RMMast->RP0  := nRate0  * RMMast->USD0
             RMMast->RPLE := nRateLE * RMMast->USDLE
             RMMast->RP1  := nRate1  * RMMast->USD1
             RMMast->RP2  := nRate2  * RMMast->USD2
             RMMast->RP3  := nRate3  * RMMast->USD3
             RMMast->RP4  := nRate4  * RMMast->USD4
             RMMast->( dbCommit() )
             RMMast->( dbSkip() )
           enddo
        else
           exit
        endif
      exit
    enddo
    setcolor ( cClr )
    restscreen ( 0,0,maxrow(),maxcol(), cScr )
    close all
return nil

function Calc2()
    local nPrice0,nPrice1,nPrice2,nPrice3,nPrice4,nPriceLE
    local cRMCode,cFGCode
    local cClr:=setcolor()
    local cScr:=savescreen()
    set score off
    set date British
    dataDict()
    if alert ( "Please make sure Mapping Drive W",{" Yes "," No "} ) == 1

      do while .T.
        if alert ( " Calculate : ", { " Yes ", " No " } ) == 1
           sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
           setcolor( "gr+*/bg,w+/br+" )
           @ 21,25  say " Wait for a minutes ..... "
           sele FGMast1
           zap
           appe from w:FGMast
           Synonim->( dbGoTop() )
           do while ! Synonim->( Eof() )
             cRMCode := Synonim->RMCode
             cFGCode := Synonim->FGCode
             FGMast1->( dbSeek(cFGCode) )
             *nPrice0 := FGMast1->Price0
             nPriceLE := FGMast1->PriceLE
             nPrice1  := FGMast1->Price1
             nPrice2  := FGMast1->Price2
             nPrice3  := FGMast1->Price3
             nPrice4  := FGMast1->Price4
             RMMast->( dbSeek(cRMCode) )
             *RMMast->RP0 := nPrice0
             RMMast->RPLE := nPriceLE
             RMMast->RP1  := nPrice1
             RMMast->RP2  := nPrice2
             RMMast->RP3  := nPrice3
             RMMast->RP4  := nPrice4
             RMMast->( dbCommit() )
             Synonim->( dbSkip() )
           enddo
        else
           exit
        endif
        exit
      enddo
    endif
    setcolor ( cClr )
    restscreen ( 0,0,maxrow(),maxcol(), cScr )
    close all
return nil

*****************************************************************************
*****************************************************************************
function Calc3()
   local cFGCode,cRMCode,cId,cMulti
   local nStdB,nStdB1,nRp0,nRp1,nRp2,nRp3,nRp4,nWaste,nRpLE
   local nPE00,nPE01,nPE02,nPE03,nPE04,nPELE
   local nPE10,nPE11,nPE12,nPE13,nPE14,nPELE1
   local nPE20,nPE21,nPE22,nPE23,nPE24,nPELE2
   local nCostLE  := 0
   local nCost0   := 0
   local nCost1   := 0
   local nCost2   := 0
   local nCost3   := 0
   local nCost4   := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   dataDict()
   set date British
   set score off

   do while .T.
      if alert ( " Calculate : ", { " Yes ", " No " } ) == 1
          cMulti := alert ( " Calc Multi Level : ", { " Yes ", " No " } )
          sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
          setcolor( "gr+*/bg,w+/br+" )
          @ 21,25  say " Wait for a minutes ..... "
          *nPE00  := CostRef->PE00
          *nPELE  := CostRef->PELE
          *nPE01  := CostRef->PE01
          *nPE02  := CostRef->PE02
          *nPE03  := CostRef->PE03
          *nPE04  := CostRef->PE04

          *nPE10  := CostRef->PE10
          *nPELE1 := CostRef->PELE1
          *nPE11  := CostRef->PE11
          *nPE12  := CostRef->PE12
          *nPE13  := CostRef->PE13
          *nPE14  := CostRef->PE14

          *nPE20  := CostRef->PE20
          *nPELE2 := CostRef->PELE2
          *nPE21  := CostRef->PE21
          *nPE22  := CostRef->PE22
          *nPE23  := CostRef->PE23
          *nPE24  := CostRef->PE24

          if cMulti == 1
             sele FGMast
             set filter to FGMast->Level == "Y"
             FGMast->( dbSetOrder(1) )
             FGMast->( dbGoTop() )
             do while ! FGMast-> ( EOF() )
                cFGCode := FGMast->FGCode
                nPE00  := FGMast->peckp
                nPELE  := FGMast->peckp
                nPE01  := FGMast->peckp
                nPE02  := FGMast->peckp
                nPE03  := FGMast->peckp
                nPE04  := FGMast->peckp

                nPE10  := FGMast->pesmg
                nPELE1 := FGMast->pesmg
                nPE11  := FGMast->pesmg
                nPE12  := FGMast->pesmg
                nPE13  := FGMast->pesmg
                nPE14  := FGMast->pesmg

                nPE20  := FGMast->pesby
                nPELE2 := FGMast->pesby
                nPE21  := FGMast->pesby
                nPE22  := FGMast->pesby
                nPE23  := FGMast->pesby
                nPE24  := FGMast->pesby

                nPE30  := FGMast->peplg
                nPELE3 := FGMast->peplg
                nPE31  := FGMast->peplg
                nPE32  := FGMast->peplg
                nPE33  := FGMast->peplg
                nPE34  := FGMast->peplg


                Formula->( dbSeek(cFGCode) )
                do while Formula->FGCode == cFGCode .and. ! Formula-> ( EOF() )
                   cRMCode := Formula->RMCode
                   nStdB   := Formula->StandardB
                   RMMast->( dbSeek( cRMCode ) )
                   cId    := RMMast->Id
                   nRp0   := RMMast->Rp0
                   nRpLE  := RMMast->RpLE
                   nRp1   := RMMast->Rp1
                   nRp2   := RMMast->Rp2
                   nRp3   := RMMast->Rp3
                   nRp4   := RMMast->Rp4
                   nWaste := RMMast->Waste
                   nStdB1 := nStdB+(nStdB*nWaste)
                   if cId == "* " .or. cId # " "
                      nCost0  := nCost0  + (nStdB1*nRp0)
                      nCostLE := nCostLE + (nStdB1*nRpLE)
                      nCost1  := nCost1  + (nStdB1*nRp1)
                      nCost2  := nCost2  + (nStdB1*nRp2)
                      nCost3  := nCost3  + (nStdB1*nRp3)
                      nCost4  := nCost4  + (nStdB1*nRp4)
                   else
                      nCost0  := nCost0  + ((nStdB1*nRp0)/1000)
                      nCostLE := nCostLE + ((nStdB1*nRpLE)/1000)
                      nCost1  := nCost1  + ((nStdB1*nRp1)/1000)
                      nCost2  := nCost2  + ((nStdB1*nRp2)/1000)
                      nCost3  := nCost3  + ((nStdB1*nRp3)/1000)
                      nCost4  := nCost4  + ((nStdB1*nRp4)/1000)
                   endif
                   Formula->( dbSkip() )
                enddo
                FGMast->( dbSeek(cFGCode) )
                FGMast->UC0    := nCost0
                FGMast->UCLE   := nCostLE
                FGMast->UC1    := nCost1
                FGMast->UC2    := nCost2
                FGMast->UC3    := nCost3
                FGMast->UC4    := nCost4

                ****************************
                if nCost0 # 0
                   if len(rtrim(cFGCode))=5
                      FGMast->Price00 := (FGMast->UC0+nPE00)
                      FGMast->Price10 := (FGMast->UC0+nPE10)
                      FGMast->Price20 := (FGMast->UC0+nPE20)
                      FGMast->Price30 := (FGMast->UC0+nPE30)

                   else
                      FGMast->Price00 := Round((FGMast->UC0+nPE00),2)
                      FGMast->Price10 := Round((FGMast->UC0+nPE10),2)
                      FGMast->Price20 := Round((FGMast->UC0+nPE20),2)
                      FGMast->Price30 := Round((FGMast->UC0+nPE30),2)
                   endif
                endif
                if nCostLE # 0
                   if len(rtrim(cFGCode))=5
                      FGMast->PriceLE := (FGMast->UCLE+nPELE)
                      FGMast->PriceLE1:= (FGMast->UCLE+nPELE1)
                      FGMast->PriceLE2:= (FGMast->UCLE+nPELE2)
                      FGMast->PriceLE3:= (FGMast->UCLE+nPELE3)  
                   else
                      FGMast->PriceLE := Round((FGMast->UCLE+nPELE),2)
                      FGMast->PriceLE1:= Round((FGMast->UCLE+nPELE1),2)
                      FGMast->PriceLE2:= Round((FGMast->UCLE+nPELE2),2)
                      FGMast->PriceLE3:= Round((FGMast->UCLE+nPELE3),2)
                   endif
                endif
                if nCost1 # 0
                   if len(rtrim(cFGCode))=5
                      FGMast->Price01 := (FGMast->UC1+nPE01)
                      FGMast->Price11 := (FGMast->UC1+nPE11)
                      FGMast->Price21 := (FGMast->UC1+nPE21)
                      FGMast->Price31 := (FGMast->UC1+nPE31)
                   else
                      FGMast->Price01 := Round((FGMast->UC1+nPE01),2)
                      FGMast->Price11 := Round((FGMast->UC1+nPE11),2)
                      FGMast->Price21 := Round((FGMast->UC1+nPE21),2)
                      FGMast->Price31 := Round((FGMast->UC1+nPE31),2)
                   endif
                endif
                if nCost2 # 0
                   if len(rtrim(cFGCode))=5
                      FGMast->Price02 := (FGMast->UC2+nPE02)
                      FGMast->Price12 := (FGMast->UC2+nPE12)
                      FGMast->Price22 := (FGMast->UC2+nPE22)
                      FGMast->Price32 := (FGMast->UC2+nPE32)
                   else
                      FGMast->Price02 := Round((FGMast->UC2+nPE02),2)
                      FGMast->Price12 := Round((FGMast->UC2+nPE12),2)
                      FGMast->Price22 := Round((FGMast->UC2+nPE22),2)
                      FGMast->Price32 := Round((FGMast->UC2+nPE32),2)
                   endif
                endif
                if nCost3 # 0
                   if len(rtrim(cFGCode))=5
                      FGMast->Price03 := (FGMast->UC3+nPE03)
                      FGMast->Price13 := (FGMast->UC3+nPE13)
                      FGMast->Price23 := (FGMast->UC3+nPE23)
                      FGMast->Price33 := (FGMast->UC3+nPE33)
                   else
                      FGMast->Price03 := Round((FGMast->UC3+nPE03),2)
                      FGMast->Price13 := Round((FGMast->UC3+nPE13),2)
                      FGMast->Price23 := Round((FGMast->UC3+nPE23),2)
                      FGMast->Price33 := Round((FGMast->UC3+nPE33),2)
                   endif
                endif
                if nCost4 # 0
                   if len(rtrim(cFGCode))=5
                      FGMast->Price04 := (FGMast->UC4+nPE04)
                      FGMast->Price14 := (FGMast->UC4+nPE14)
                      FGMast->Price24 := (FGMast->UC4+nPE24)
                      FGMast->Price34 := (FGMast->UC4+nPE34)
                   else
                      FGMast->Price04 := Round((FGMast->UC4+nPE04),2)
                      FGMast->Price14 := Round((FGMast->UC4+nPE14),2)
                      FGMast->Price24 := Round((FGMast->UC4+nPE24),2)
                      FGMast->Price34 := Round((FGMast->UC4+nPE34),2)
                   endif
                endif
                nCost0  := 0
                nCostLE := 0
                nCost1  := 0
                nCost2  := 0
                nCost3  := 0
                nCost4  := 0
                FGMast->( dbCommit() )
                FGMast->( dbSkip() )
             enddo
             FGMast->( dbGoTop() )
             do while ! FGMast-> ( EOF() )
                *cFGCode := FGMast->FGCode
                *nCost0  := FGMast->UC0
                *nCostLE := FGMast->UCLE
                *nCost1  := FGMast->UC1
                *nCost2  := FGMast->UC2
                *nCost3  := FGMast->UC3
                *nCost4  := FGMast->UC4
                *RMMast->( dbSeek(cFGCode) )
                *RMMast->RP0  := nCost0
                *RMMast->RPLE := nCostLE
                *RMMast->RP1  := nCost1
                *RMMast->RP2  := nCost2
                *RMMast->RP3  := nCost3
                *RMMast->RP4  := nCost4

                cFGCode := FGMast->FGCode
                nCost0  := FGMast->PRICE00
                nCostLE := FGMast->PRICELE
                nCost1  := FGMast->PRICE01
                nCost2  := FGMast->PRICE02
                nCost3  := FGMast->PRICE03
                nCost4  := FGMast->PRICE04
                RMMast->( dbSeek(cFGCode) )
                RMMast->RP0  := nCost0
                RMMast->RPLE := nCostLE
                RMMast->RP1  := nCost1
                RMMast->RP2  := nCost2
                RMMast->RP3  := nCost3
                RMMast->RP4  := nCost4
                FGMast->( dbSkip() )
             enddo
          endif

          sele FGMast
          set filter to
          set filter to FGMast->Level # "Y"
          FGMast->( dbSetOrder(1) )
          FGMast->( dbGoTop() )

          do while ! FGMast-> ( EOF() )
                cFGCode := FGMast->FGCode
                nPE00  := FGMast->peckp
                nPELE  := FGMast->peckp
                nPE01  := FGMast->peckp
                nPE02  := FGMast->peckp
                nPE03  := FGMast->peckp
                nPE04  := FGMast->peckp

                nPE10  := FGMast->pesmg
                nPELE1 := FGMast->pesmg
                nPE11  := FGMast->pesmg
                nPE12  := FGMast->pesmg
                nPE13  := FGMast->pesmg
                nPE14  := FGMast->pesmg

                nPE20  := FGMast->pesby
                nPELE2 := FGMast->pesby
                nPE21  := FGMast->pesby
                nPE22  := FGMast->pesby
                nPE23  := FGMast->pesby
                nPE24  := FGMast->pesby

                nPE30  := FGMast->peplg
                nPELE3 := FGMast->peplg
                nPE31  := FGMast->peplg
                nPE32  := FGMast->peplg
                nPE33  := FGMast->peplg
                nPE34  := FGMast->peplg


                Formula->( dbSeek(cFGCode) )
                do while Formula->FGCode == cFGCode .and. ! Formula-> ( EOF() )
                   cRMCode := Formula->RMCode
                   nStdB   := Formula->StandardB
                   RMMast->( dbSeek( cRMCode ) )
                   cId    := RMMast->Id
                   nRp0   := RMMast->Rp0
                   nRpLE  := RMMast->RpLE
                   nRp1   := RMMast->Rp1
                   nRp2   := RMMast->Rp2
                   nRp3   := RMMast->Rp3
                   nRp4   := RMMast->Rp4
                   nWaste := RMMast->Waste
                   nStdB1 := nStdB+(nStdB*nWaste)
                   if cId == "* " .or. cId # " "
                      nCost0  := nCost0  + (nStdB1*nRp0)
                      nCostLE := nCostLE + (nStdB1*nRpLE)
                      nCost1  := nCost1  + (nStdB1*nRp1)
                      nCost2  := nCost2  + (nStdB1*nRp2)
                      nCost3  := nCost3  + (nStdB1*nRp3)
                      nCost4  := nCost4  + (nStdB1*nRp4)
                   else
                      nCost0  := nCost0  + ((nStdB1*nRp0)/1000)
                      nCostLE := nCostLE + ((nStdB1*nRpLE)/1000)
                      nCost1  := nCost1  + ((nStdB1*nRp1)/1000)
                      nCost2  := nCost2  + ((nStdB1*nRp2)/1000)
                      nCost3  := nCost3  + ((nStdB1*nRp3)/1000)
                      nCost4  := nCost4  + ((nStdB1*nRp4)/1000)
                   endif
                   Formula->( dbSkip() )
                enddo
                FGMast->( dbSeek(cFGCode) )
                FGMast->UC0    := nCost0
                FGMast->UCLE   := nCostLE
                FGMast->UC1    := nCost1
                FGMast->UC2    := nCost2
                FGMast->UC3    := nCost3
                FGMast->UC4    := nCost4

                if nCost0 # 0
                   FGMast->Price00 := Round((FGMast->UC0+nPE00),2)
                   FGMast->Price10 := Round((FGMast->UC0+nPE10),2)
                   FGMast->Price20 := Round((FGMast->UC0+nPE20),2)
                   FGMast->Price30 := Round((FGMast->UC0+nPE30),2)
                endif
                if nCostLE # 0
                   FGMast->PriceLE := Round((FGMast->UCLE+nPELE),2)
                   FGMast->PriceLE1:= Round((FGMast->UCLE+nPELE1),2)
                   FGMast->PriceLE2:= Round((FGMast->UCLE+nPELE2),2)
                   FGMast->PriceLE3:= Round((FGMast->UCLE+nPELE3),2)
                endif
                if nCost1 # 0
                   FGMast->Price01 := Round((FGMast->UC1+nPE01),2)
                   FGMast->Price11 := Round((FGMast->UC1+nPE11),2)
                   FGMast->Price21 := Round((FGMast->UC1+nPE21),2)
                   FGMast->Price31 := Round((FGMast->UC1+nPE31),2)
                endif
                if nCost2 # 0
                   FGMast->Price02 := Round((FGMast->UC2+nPE02),2)
                   FGMast->Price12 := Round((FGMast->UC2+nPE12),2)
                   FGMast->Price22 := Round((FGMast->UC2+nPE22),2)
                   FGMast->Price32 := Round((FGMast->UC2+nPE32),2)
                endif
                if nCost3 # 0
                   FGMast->Price03 := Round((FGMast->UC3+nPE03),2)
                   FGMast->Price13 := Round((FGMast->UC3+nPE13),2)
                   FGMast->Price23 := Round((FGMast->UC3+nPE23),2)
                   FGMast->Price33 := Round((FGMast->UC3+nPE33),2)
                endif
                if nCost4 # 0
                   if len(rtrim(cFGCode))=5
                      FGMast->Price04 := (FGMast->UC4+nPE04)
                      FGMast->Price14 := (FGMast->UC4+nPE14)
                      FGMast->Price24 := (FGMast->UC4+nPE24)
                      FGMast->Price34 := (FGMast->UC4+nPE34)
                   else
                      FGMast->Price04 := Round((FGMast->UC4+nPE04),2)
                      FGMast->Price14 := Round((FGMast->UC4+nPE14),2)
                      FGMast->Price24 := Round((FGMast->UC4+nPE24),2)
                      FGMast->Price34 := Round((FGMast->UC4+nPE34),2)
                   endif
                endif
                nCost0  := 0
                nCostLE := 0
                nCost1  := 0
                nCost2  := 0
                nCost3  := 0
                nCost4  := 0
                FGMast->( dbCommit() )
                FGMast->( dbSkip() )
          enddo
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil

function Calc4()
   local cFGCode,cNDLCode,cPrType1,cPrType2,nStd
   local nJan:=0,nFeb:=0,nMar:=0,nApr:=0,nMei:=0,nJun:=0,nJul:=0,nAgt:=0,nSep:=0,nOkt:=0,nNov:=0,nDes:=0
   local nLE1 := 0,nLE2 := 0,nLE3 := 0,nLE4 := 0,nLE5 := 0,nLE6 := 0
   local aArea[1],X,I,Y
   local n := 0
   local rec := 0
   local nTLE1 := 0
   local nTLE2 := 0
   local nTLE3 := 0
   local nTLE4 := 0
   local nTLE5 := 0
   local nTLE6 := 0
   local nTJan := 0
   local nTFeb := 0
   local nTMar := 0
   local nTApr := 0
   local nTMei := 0
   local nTJun := 0
   local nTJul := 0
   local nTAgt := 0
   local nTSep := 0
   local nTOkt := 0
   local nTNov := 0
   local nTDes := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   dataDict()
   set date British
   set score off

   do while .T.
      if alert ( " Calculate : ", { " Yes ", " No " } ) == 1
          sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
          setcolor( "gr+*/bg,w+/br+" )
          @ 21,25  say " Wait for a minutes ..... "
          Area->( dbGoBottom() )
          x := (Area->( Recno() ))+1
          ASize(aArea,x)
          Area->( dbGoTop() )
          for I := 1 to X
              aArea[I] := Area->Code
              Area->( dbSkip() )
          next
          @23,25 say x
          Volume->( __dbZap() )
          NDLForm->( dbGoBottom() )
          y := NDLForm->( recno() )
          rec := y * x
          for I := 1 to X
              sele VolNDL
              set filter to VolNDL->Jan # 0 .or. VolNDL->Feb # 0 .or. VolNDL->Mar # 0 .or. VolNDL->Apr # 0 .or. VolNDL->Mei # 0 .or. VolNDL->Jun # 0 .or.;
                            VolNDL->Jul # 0 .or. VolNDL->Agt # 0 .or. VolNDL->Sep # 0 .or. VolNDL->Okt # 0 .or. VolNDL->Nov # 0 .or. VolNDL->Des # 0 .or.;
                            VolNDL->LEJul # 0 .or. VolNDL->LEAgt # 0 .or. VolNDL->LESep # 0 .or. VolNDL->LEOkt # 0 .or. VolNDL->LENov # 0 .or. VolNDL->LEDes # 0
              NDLForm->( dbSetOrder(1) )
              NDLForm->( dbGoTop() )
              do while ! NDLForm-> ( EOF() )
                 n++
                 @ 21,46 say n/rec*100 pict "999%"
                 cFGCode  := NDLForm->FGCode
                 FGMast->( dbSeek(cFGCode) )
                 cPrType1 := FGMast->Prtype1
                 cPrType2 := FGMast->Prtype2
                 cNDLCode := NDLForm->NDLCode
                 nStd     := NDLForm->Standard
                 VolNDL->( dbSetOrder(2) )
                 VolNDL->( dbSeek(aArea[I]+cNDLCode) )
                 do while VolNDL->Code == aArea[I] .and. VolNDL->NDLCode == cNDLCode .and. ! VolNDL->( Eof() )
                    nLE1  := nLE1 + (VolNDL->LEJul * nStd)
                    nLE2  := nLE2 + (VolNDL->LEAgt * nStd)
                    nLE3  := nLE3 + (VolNDL->LESep * nStd)
                    nLE4  := nLE4 + (VolNDL->LEOkt * nStd)
                    nLE5  := nLE5 + (VolNDL->LENov * nStd)
                    nLE6  := nLE6 + (VolNDL->LEDes * nStd)
                    nJan  := nJan + (VolNDL->Jan * nStd)
                    nFeb  := nFeb + (VolNDL->Feb * nStd)
                    nMar  := nMar + (VolNDL->Mar * nStd)
                    nApr  := nApr + (VolNDL->Apr * nStd)
                    nMei  := nMei + (VolNDL->Mei * nStd)
                    nJun  := nJun + (VolNDL->Jun * nStd)
                    nJul  := nJul + (VolNDL->Jul * nStd)
                    nAgt  := nAgt + (VolNDL->Agt * nStd)
                    nSep  := nSep + (VolNDL->Sep * nStd)
                    nOkt  := nOkt + (VolNDL->Okt * nStd)
                    nNov  := nNov + (VolNDL->Nov * nStd)
                    nDes  := nDes + (VolNDL->Des * nStd)
                    VolNDL->(dbSkip())
                 enddo
                 nTLE1 := nTLE1 + nLE1
                 nTLE2 := nTLE2 + nLE2
                 nTLE3 := nTLE3 + nLE3
                 nTLE4 := nTLE4 + nLE4
                 nTLE5 := nTLE5 + nLE5
                 nTLE6 := nTLE6 + nLE6
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
                 store 0 to nLE1,nLE2,nLE3,nLE4,nLE5,nLE6,nJan,nFeb,nMar,nApr,nMei,nJun,nJul,nAgt
                 store 0 to nSep,nOkt,nNov,nDes
                 NDLForm->( dbSkip() )
                 if NDLForm->FGCode # cFGCode
                    if nTJan+nTFeb+nTMar+nTApr+nTMei+nTJun+nTJul+nTAgt+nTSep+nTOkt+nTNov+nTDes > 0 .or. nTLE1+nTLE2+nTLE3+nTLE4+nTLE5+nTLE6 > 0
                      *Volume->( dbSeek(aArea[I]+cFGCode )
                      Volume->( dbAppend() )
                      Volume->Code   := aArea[I]
                      Volume->FGCode := cFGCode
                      Volume->PrType1:= cPrtype1
                      Volume->PrType2:= cPrtype2
                      Volume->LEJul := nTLE1
                      Volume->LEAgt := nTLE2
                      Volume->LESep := nTLE3
                      Volume->LEOkt := nTLE4
                      Volume->LENov := nTLE5
                      Volume->LEDes := nTLE6
                      Volume->Jan := nTJan
                      Volume->Feb := nTFeb
                      Volume->Mar := nTMar
                      Volume->Apr := nTApr
                      Volume->Mei := nTMei
                      Volume->Jun := nTJun
                      Volume->Jul := nTJul
                      Volume->Agt := nTAgt
                      Volume->Sep := nTSep
                      Volume->Okt := nTOkt
                      Volume->Nov := nTNov
                      Volume->Des := nTDes
                      Volume->Total   := nTJan+nTFeb+nTMar+nTApr+nTMei+nTJun+nTJul+nTAgt+nTSep+nTOkt+nTNov+nTDes
                      Volume->TotalLE := nTLE1+nTLE2+nTLE3+nTLE4+nTLE5+nTLE6
                      Volume->( dbCommit() )
                      store 0 to nTJan,nTFeb,nTMar,nTApr,nTMei,nTJun,nTJul,nTAgt,nTSep,nTOkt,nTNov,nTDes,nTLE1,nTLE2,nTLE3,nTLE4,nTLE5,nTLE6
                    endif
                 endif
              enddo
          next
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil






function Calc5()
   local cFGCode,cNDLCode,cArea,cPrType1,cPrType2
   local nStd,nJan,nFeb,nMar,nApr,nMei,nJun,nJul,nAgt,nSep,nOkt,nNov,nDes
   local nLE1,nLE2,nLE3,nLE4,nLE5,nLE6
   local nTLE1 := 0
   local nTLE2 := 0
   local nTLE3 := 0
   local nTLE4 := 0
   local nTLE5 := 0
   local nTLE6 := 0
   local nTJan := 0
   local nTFeb := 0
   local nTMar := 0
   local nTApr := 0
   local nTMei := 0
   local nTJun := 0
   local nTJul := 0
   local nTAgt := 0
   local nTSep := 0
   local nTOkt := 0
   local nTNov := 0
   local nTDes := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   dataDict()
   set date British
   set score off

   do while .T.
      if alert ( " Calculate : ", { " Yes ", " No " } ) == 1
          sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
          setcolor( "gr+*/bg,w+/br+" )
          @ 21,25  say " Wait for a minutes ..... "
          Volume->( __dbZap() )
          NDLForm->( dbSetOrder(1) )
          NDLForm->( dbGoTop() )
          do while ! NDLForm-> ( EOF() )
             cFGCode  := NDLForm->FGCode
             FGMast->( dbSeek(cFGCode) )
             cPrType1 := FGMast->Prtype1
             cPrType2 := FGMast->Prtype2
             cNDLCode := NDLForm->NDLCode
             nStd     := NDLForm->Standard
             VolNDL->( dbSetOrder(2) )
             VolNDL->( dbSeek(cNDLCode) )
             do while VolNDL->NDLCode == cNDLCode .and. ! VolNDL-> ( EOF() )
                cArea := VolNDL->Code
                do while VolNDL->Code == cArea .and. ! VolNDL-> ( EOF() )
                   nLE1  := VolNDL->LEJul * nStd
                   nLE2  := VolNDL->LEAgt * nStd
                   nLE3  := VolNDL->LESep * nStd
                   nLE4  := VolNDL->LEOkt * nStd
                   nLE5  := VolNDL->LENov * nStd
                   nLE6  := VolNDL->LEDes * nStd
                   nJan  := VolNDL->Jan * nStd
                   nFeb  := VolNDL->Feb * nStd
                   nMar  := VolNDL->Mar * nStd
                   nApr  := VolNDL->Apr * nStd
                   nMei  := VolNDL->Mei * nStd
                   nJun  := VolNDL->Jun * nStd
                   nJul  := VolNDL->Jul * nStd
                   nAgt  := VolNDL->Agt * nStd
                   nSep  := VolNDL->Sep * nStd
                   nOkt  := VolNDL->Okt * nStd
                   nNov  := VolNDL->Nov * nStd
                   nDes  := VolNDL->Des * nStd
                   nTLE1 := nTLE1 + nLE1
                   nTLE2 := nTLE2 + nLE2
                   nTLE3 := nTLE3 + nLE3
                   nTLE4 := nTLE4 + nLE4
                   nTLE5 := nTLE5 + nLE5
                   nTLE6 := nTLE6 + nLE6
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
                   VolNDL->( dbSkip() )
                enddo
                Volume->( dbAppend() )
                Volume->Code   := cArea
                Volume->FGCode := cFGCode
                Volume->PrType1:= cPrtype1
                Volume->PrType2:= cPrtype2
                Volume->LEJul := nTLE1
                Volume->LEAgt := nTLE2
                Volume->LESep := nTLE3
                Volume->LEOkt := nTLE4
                Volume->LENov := nTLE5
                Volume->LEDes := nTLE6
                Volume->Jan := nTJan
                Volume->Feb := nTFeb
                Volume->Mar := nTMar
                Volume->Apr := nTApr
                Volume->Mei := nTMei
                Volume->Jun := nTJun
                Volume->Jul := nTJul
                Volume->Agt := nTAgt
                Volume->Sep := nTSep
                Volume->Okt := nTOkt
                Volume->Nov := nTNov
                Volume->Des := nTDes
                Volume->( dbCommit() )
                nTLE1 := 0
                nTLE2 := 0
                nTLE3 := 0
                nTJan := 0
                nTFeb := 0
                nTMar := 0
                nTApr := 0
                nTMei := 0
                nTJun := 0
                nTJul := 0
                nTAgt := 0
                nTSep := 0
                nTOkt := 0
                nTNov := 0
                nTDes := 0
             enddo
             NDLForm->( dbSkip() )
          enddo
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil


