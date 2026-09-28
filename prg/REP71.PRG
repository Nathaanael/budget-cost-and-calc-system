#include "Stock.ch"

function Report113()
   local cPrint,cRMCode,cId,nPE,cOption,cQtr,cDescQtr
   local cPrType1,cPrType2
   local cType,cType1,cType2,cType3,cType4,cType5,cType6,cType7,cType8,cType9,cType10
   local cFGCode  := space(7)
   local cFGCode1 := space(7)
   local cFGCode2 := space(7)
   local nStdB,nStdB1,nRp,nWaste,nPrice
   local nWidth   := 234
   local nCost0 := 0,nCost1 := 0,nCost2 := 0,nCost3 := 0,nCost4 := 0,nCost5 := 0,nCost6 := 0,nCost7 := 0,nCost8 := 0,nCost9 := 0,nCost10:=0
   local nType1 := 0,nType2 := 0,nType3 := 0,nType4 := 0,nType5 := 0,nType6 := 0,nType7 := 0,nType8 := 0,nType9 := 0,nType10 := 0
   local nSW      := 0
   local nSW1     := 0
   local nPage    := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   dataDict()
   set date British
   set score off

   do while .T.
      if CostRef->Code == '00'
         cOption := alert ( " Report Option : ", { " Powder ", " Oil "} )
      endif
      sbox( 17,40,20,62,"gr+/w+,w+/bg+" )
      @ 18,41 say " Code FG : " get cFGCode1 picture "@!"
      @ 19,41 say "      To : " get cFGCode2 picture "@!"
      read
      if lastkey() == K_ESC
         exit
      endif
      cQtr := alert ( " Quartal : ", { " I ", " II ", " III "," IV " } )
      cPrint := alert ( " Device Option  : ", { " View ", " Printer ", " Cancel " } )
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
      @ prow(),pcol() say chr(27)+chr(77)+chr(15)
      if CostRef->Code == '00'
         do case
            case cOption == 1
                 sele TypeMast
                 set filter to substr(TypeMast->PrType1,1,1) == 'R'
                 go top
                 loca for TypeMast->prtype1='R'
                 cType1 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType2 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType3 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType4 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType5 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType6 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType7 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType8 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType9 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType10:= TypeMast->PrType1
            case cOption == 2
                 sele TypeMast
                 set filter to substr(TypeMast->PrType1,1,1) == 'P'
                 go top
                 loca for TypeMast->prtype1='P'
                 cType1 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType2 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType3 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType4 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType5 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType6 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType7 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType8 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType9 := TypeMast->PrType1
                 TypeMast->( dbSkip() )
                 cType10:= TypeMast->PrType1
         endcase
      else
         sele TypeMast
         set filter to substr(TypeMast->PrType1,1,1) == 'R'
         go top
         loca for TypeMast->prtype1='R'
         cType1 := TypeMast->PrType1
         TypeMast->( dbSkip() )
         cType2 := TypeMast->PrType1
         TypeMast->( dbSkip() )
         cType3 := TypeMast->PrType1
         TypeMast->( dbSkip() )
         cType4 := TypeMast->PrType1
         TypeMast->( dbSkip() )
         cType5 := TypeMast->PrType1
         TypeMast->( dbSkip() )
         cType6 := TypeMast->PrType1
         TypeMast->( dbSkip() )
         cType7 := TypeMast->PrType1
         TypeMast->( dbSkip() )
         cType8 := TypeMast->PrType1
         TypeMast->( dbSkip() )
         cType9 := TypeMast->PrType1
         TypeMast->( dbSkip() )
         cType10:= TypeMast->PrType1
      endif
      do case
         case cQtr == 1
              cDescQtr := "QTR I"
         case cQtr == 2
              cDescQtr := "QTR II"
         case cQtr == 3
              cDescQtr := "QTR III"
         case cQtr == 4
              cDescQtr := "QTR IV"
      endcase
      sele FGMast
      set filter to FGMast->FGCode >= cFGCode1 .and. FGMast->FGCode <= cFGCode2
      select Formula
      set filter to Formula->FGCode >= cFGCode1 .and. Formula->FGCode <= cFGCode2
      FGMast->( dbSetOrder(3) )
      FGMast->( dbGoTop() )
      do while ! FGMast-> ( EOF() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
        if nSW == 0
             @ prow()+1,1    say CostRef->Desc1
             @ prow()  ,205  say "   Date : "
             @ prow(),pcol() say date()
             @ prow()+1,1    say CostRef->Desc2
             @ prow()  ,205  say "   Time : "
             @ prow(),pcol() say Time()
             @ prow()+1,110  say "RAW MATERIAL COST ANALYSIS"
             @ prow() ,pcol()+1 say "( "
             @ prow() ,pcol()   say cDescQtr picture "@!"
             @ prow() ,pcol()   say " )"
             nPage++
             @ prow(),205 say "   Page : "
             @ prow(),215 say nPage picture "999"
             @ prow()+1,1   say repli ( '=',nWidth )
 *                            1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1         2
 *                  01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123
 @ prow()+1,1    say "|       Finished Goods             |    Price      |" &&                                                                                                                        P E      |   Total    |  GP/PACK   |     %      |"
*@ prow()+1,1    say "|----------------------------------|---------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|"
*@ prow()+1,1    say "|                                  |     List      |"
TypeMast->( dbSeek(cType1) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|"
TypeMast->( dbSeek(cType2) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|"
TypeMast->( dbSeek(cType3) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|"
TypeMast->( dbSeek(cType4) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|"
TypeMast->( dbSeek(cType5) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|"
TypeMast->( dbSeek(cType6) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|"
TypeMast->( dbSeek(cType7) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|"
TypeMast->( dbSeek(cType8) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|"
TypeMast->( dbSeek(cType9) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|"
TypeMast->( dbSeek(cType10) )
@ prow(),pcol()+1 say substr(TypeMast->Desc,1,10) picture "@!"
@ prow(),pcol()+1 say "|    P E     |   Total    |  GP/PACK   |     %      |"
*@ prow(),pcol()+1 say "|------------|------------|------------|"
 *                             1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1         2
 *                   01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123
 @ prow()+1,1    say "|----------------------------------|---------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|------------|"
 @ prow(),pcol() say "------------|"
 *                      xxxxxxx xxxxxxxxxxxxxxxxxxxx       9,999,999.99    999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99
             nSW := 1
        endif
           cFGCode := FGMast->FGCode
           do case
              case cQtr == 1
                   nPrice := FGMast->Price1
              case cQtr == 2
                   nPrice := FGMast->Price2
              case cQtr == 3
                   nPrice := FGMast->Price3
              case cQtr == 4
                   nPrice := FGMast->Price4
           endcase

           cPrType1 := FGMast->PrType1
           do while FGMast->PrType1 == cPrType1 .and. prow()<=55 .and. ! FGMast->( EOf() )
              cPrType2 := FGMast->PrType2
              do while FGMast->PrType2 == cPrType2 .and. FGMast->PrType1 == cPrType1  .and. prow()<=55 .and. ! FGMast->( EOf() )
                 cFGCode := FGMast->FGCode
                 do case
                    case cQtr == 1
                         nPrice := FGMast->Price1
                    case cQtr == 2
                         nPrice := FGMast->Price2
                    case cQtr == 3
                         nPrice := FGMast->Price3
                    case cQtr == 4
                         nPrice := FGMast->Price4
                 endcase
                 Formula->( dbSeek(cFGCode) )
                 do while Formula->FGCode == cFGCode .and. ! Formula->( Eof() )   &&prow() <= 55
                    cRMCode := Formula->RMCode
                    nStdB   := Formula->StandardB
                    RMMast->( dbSeek( cRMCode ) )
                    cType  := RMMast->Type
                    cId    := RMMast->Id
                    do case
                       case cQtr == 1
                            nRp := RMMast->Rp1
                       case cQtr == 2
                            nRp := RMMast->Rp2
                       case cQtr == 3
                            nRp := RMMast->Rp3
                       case cQtr == 4
                            nRp := RMMast->Rp4
                    endcase
                    nWaste := RMMast->Waste
                    nStdB1 := nStdB+(nStdB*nWaste)
                    do case
                       case cType == cType1
                            if cId == "* " .or. cId # "  "
                               nType1 := nStdB1*nRp
                            else
                               nType1 := (nStdB1*nRp)/1000
                            endif
                            nCost1 := nCost1 + nType1
                       case cType == cType2
                            if cId == "* " .or. cId # "  "
                               nType2 := nStdB1*nRp
                            else
                               nType2 := (nStdB1*nRp)/1000
                            endif
                            nCost2 := nCost2 + nType2
                       case cType == cType3
                            if cId == "* " .or. cId # "  "
                               nType3 := nStdB1*nRp
                            else
                               nType3 := (nStdB1*nRp)/1000
                            endif
                            nCost3 := nCost3 + nType3
                       case cType == cType4
                            if cId == "* " .or. cId # "  "
                               nType4 := nStdB1*nRp
                            else
                               nType4 := (nStdB1*nRp)/1000
                            endif
                            nCost4 := nCost4 + nType4
                       case cType == cType5
                            if cId == "* " .or. cId # "  "
                               nType5 := nStdB1*nRp
                            else
                               nType5 := (nStdB1*nRp)/1000
                            endif
                            nCost5 := nCost5 + nType5
                       case cType == cType6
                            if cId == "* " .or. cId # "  "
                               nType6 := nStdB1*nRp
                            else
                               nType6 := (nStdB1*nRp)/1000
                            endif
                            nCost6 := nCost6 + nType6
                       case cType == cType7
                            if cId == "* " .or. cId # "  "
                               nType7 := nStdB1*nRp
                            else
                               nType7 := (nStdB1*nRp)/1000
                            endif
                            nCost7 := nCost7 + nType7
                       case cType == cType8
                            if cId == "* " .or. cId # "  "
                               nType8 := nStdB1*nRp
                            else
                               nType8 := (nStdB1*nRp)/1000
                            endif
                            nCost8 := nCost8 + nType8
                       case cType == cType9
                            if cId == "* " .or. cId # "  "
                               nType9 := nStdB1*nRp
                            else
                               nType9 := (nStdB1*nRp)/1000
                            endif
                            nCost9 := nCost9 + nType9
                       case cType == cType10
                            if cId == "* " .or. cId # "  "
                               nType10 := nStdB1*nRp
                            else
                               nType10 := (nStdB1*nRp)/1000
                            endif
                            nCost10 := nCost10 + nType10
                    endcase
                    Formula->( dbSkip() )
                 enddo
                 if nCost1 # 0 .or. nCost2 # 0 .or. nCost3 # 0 .or. nCost4 # 0 .or. nCost5 # 0 .or.;
                    nCost6 # 0 .or. nCost7 # 0 .or. nCost8 # 0 .or. nCost9 # 0 .or. nCost10 # 0

                    @ prow()+1,1   say "|"
                    @ prow()  ,3   say cFGCode                     picture "@!"
                    @ prow()  ,11  say substr(FGMast->Desc,1,20)   picture "@!"
                    @ prow()  ,36  say "|"
                    @ prow()  ,38  say nPrice                      picture "@z 9,999,999.99"
                    @ prow()  ,52  say "|"
                    @ prow()  ,54  say nCost1                      picture "@z 999,999.99"
                    @ prow()  ,65  say "|"
                    @ prow()  ,67  say nCost2                      picture "@z 999,999.99"
                    @ prow()  ,78  say "|"
                    @ prow()  ,80  say nCost3                      picture "@z 999,999.99"
                    @ prow()  ,91  say "|"
                    @ prow()  ,93  say nCost4                      picture "@z 999,999.99"
                    @ prow()  ,104 say "|"
                    @ prow()  ,106 say nCost5                      picture "@z 999,999.99"
                    @ prow()  ,117 say "|"
                    @ prow()  ,119 say nCost6                      picture "@z 999,999.99"
                    @ prow()  ,130 say "|"
                    @ prow()  ,132 say nCost7                      picture "@z 999,999.99"
                    @ prow()  ,143 say "|"
                    @ prow()  ,145 say nCost8                      picture "@z 999,999.99"
                    @ prow()  ,156 say "|"
                    @ prow()  ,158 say nCost9                      picture "@z 999,999.99"
                    @ prow()  ,169 say "|"
                    @ prow()  ,171 say nCost10                     picture "@z 999,999.99"
                    @ prow()  ,182 say "|"
                    if cPrType1 == '00'
                       do case
                          case cQtr == 1
                               nPE := CostRef->PE01
                          case cQtr == 2
                               nPE := CostRef->PE02
                          case cQtr == 3
                               nPE := CostRef->PE03
                          case cQtr == 4
                               nPE := CostRef->PE04
                       endcase
                    else
                       do case
                          case cQtr == 1
                               nPE := CostRef->PE11
                          case cQtr == 2
                               nPE := CostRef->PE12
                          case cQtr == 3
                               nPE := CostRef->PE13
                          case cQtr == 4
                               nPE := CostRef->PE14
                       endcase
                    endif
                    @ prow()  ,184 say nPE                          picture "@z 999,999.99"
                    @ prow()  ,195 say "|"
                    nCost0 := nCost1+nCost2+nCost3+nCost4+nCost5+nCost6+nCost7+nCost8+nCost9+nPE
                    @ prow()  ,197 say nCost0                       picture "@z 999,999.99"
                    @ prow()  ,208 say "|"
                    @ prow()  ,210 say (nPrice - nCost0)            picture "@z( 999,999.99"
                    @ prow()  ,221 say "|"
                    @ prow()  ,223 say (nPrice - nCost0)/nCost0*100 picture "@z( 999,999.99"
                    @ prow()  ,234 say "|"
                 endif
                 store 0 to nCost0,nCost1,nCost2,nCost3,nCost4,nCost5,nCost6,nCost7,nCost8,nCost9,nCost10
                 FGMast->( dbSkip() )
                 if prow() >= 55  .or. FGMast->( EOF() )
                    nSW  := 0
                    exit
                 endif
                 if FGMast->( Eof() )
                    exit
                 endif
                 if prow() >= 55
                    nSW  := 0
                    exit
                 endif
              enddo
              if FGMast->( Eof() )
                 exit
              endif
              if prow() >= 55
                 nSW  := 0
                 eject
                 exit
              endif
              if cPrType2 # FGMast->PrType2
                 @ prow()+1,1   say "|"
                 @ prow()  ,36  say "|"
                 @ prow()  ,52  say "|"
                 @ prow()  ,65  say "|"
                 @ prow()  ,78  say "|"
                 @ prow()  ,91  say "|"
                 @ prow()  ,104 say "|"
                 @ prow()  ,117 say "|"
                 @ prow()  ,130 say "|"
                 @ prow()  ,143 say "|"
                 @ prow()  ,156 say "|"
                 @ prow()  ,169 say "|"
                 @ prow()  ,182 say "|"
                 @ prow()  ,195 say "|"
                 @ prow()  ,208 say "|"
                 @ prow()  ,221 say "|"
                 @ prow()  ,234 say "|"
              endif
           enddo
           if FGMast->( Eof() )
                 exit
           endif
           if prow() >= 55
              nSW  := 0
              eject
              exit
           endif
           if cPrType1 # FGMast->PrType1
              @ prow()+1,1   say "|"
              @ prow()  ,36  say "|"
              @ prow()  ,52  say "|"
              @ prow()  ,65  say "|"
              @ prow()  ,78  say "|"
              @ prow()  ,91  say "|"
              @ prow()  ,104 say "|"
              @ prow()  ,117 say "|"
              @ prow()  ,130 say "|"
              @ prow()  ,143 say "|"
              @ prow()  ,156 say "|"
              @ prow()  ,169 say "|"
              @ prow()  ,182 say "|"
              @ prow()  ,195 say "|"
              @ prow()  ,208 say "|"
              @ prow()  ,221 say "|"
              @ prow()  ,234 say "|"
           endif
      enddo
      @ prow()+1,1   say repli ( '=',nWidth )
      eject
      @prow(), pcol() say chr(27)+chr(80)+chr(18)
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
return nil