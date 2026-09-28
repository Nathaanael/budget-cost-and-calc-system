#include "Stock.ch"

function Report()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey
     set key 19 to Lkey
     sbox(08,27,22,56,"gr+/bg+,w+/br+")
     @ 09     ,28 prompt " RM Price Budget            "
     @ row()+1,28 prompt " RM Unit Cost Standard      "
     @ row()+1,28 prompt " Unit Cost & Sell.Price     "
     @ row()+1,28 prompt " Sales Volume               "
     @ row()+1,28 prompt " Sales Value                "
     @ row()+1,28 prompt " Raw Material Value         "
     @ row()+1,28 prompt " UC & SP Variance           "
     @ row()+1,28 prompt " Alokasi Pemakaian RM (Qty) "
     @ row()+1,28 prompt " Alokasi Pemakaian RM (Amt) "
     @ row()+1,28 prompt " Pemakaian RM dalam All FG  "
     @ row()+1,28 prompt " Detail Cost per Type RM    "
     @ row()+1,28 prompt " RM Unit Cost Standard New  "
     @ row()+1,28 prompt " RM Unit Cost Standard Smry "

     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         Report1()
       case nSelect == 2
         Report2()
       case nSelect == 3
         Report3()
       case nSelect == 4
         Report4()
       case nSelect == 5
         Report5()
       case nSelect == 6
         Report6()
       case nSelect == 7
         Report7()
       case nSelect == 8
         Report8()
       case nSelect == 9
         Report9()
       case nSelect == 10
         Report10()
       case nSelect == 11
         Report11()
       case nSelect == 12
         Report12()
       case nSelect == 13
         Report21()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function Report4()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey
     set key 19 to Lkey1
     sbox(14,58,17,66,"gr+/bg+,w+/br+")
     @ 15     ,59 prompt " L E   "
     @ row()+1,59 prompt " A O P "
     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         Report42()
       case nSelect == 2
         Report41()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function Report41()
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
         Report411(nSelect)
       case nSelect == 2
         Report411(nSelect)
       case nSelect == 3
         Report411(nSelect)
       case nSelect == 4
         Report411(nSelect)
       case nSelect == 5
         Report411(nSelect)
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil



function Report5()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey
     set key 19 to Lkey
     sbox(15,58,18,66,"gr+/bg+,w+/br+")
     @ 16     ,59 prompt " L E   "
     @ row()+1,59 prompt " A O P "
     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         Report52()
       case nSelect == 2
         Report51()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function Report51()
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
         Report511(nSelect)
       case nSelect == 2
         Report511(nSelect)
       case nSelect == 3
         Report511(nSelect)
       case nSelect == 4
         Report511(nSelect)
       case nSelect == 5
         Report511(nSelect)
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil



function Report6()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey1
     set key 19 to Lkey
     sbox(16,58,19,66,"gr+/bg+,w+/br+")
     @ 17     ,59 prompt " L E   "
     @ row()+1,59 prompt " A O P "
     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         Report62()
       case nSelect == 2
         Report61()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function Report11()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey1
     set key 19 to Lkey1
     sbox(18,58,22,67,"gr+/bg+,w+/br+")
     @ 19     ,59 prompt " Usage  "
     @ row()+1,59 prompt " Price  "
     @ row()+1,59 prompt " Amount "

     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         Report111()
       case nSelect == 2
         Report112()
       case nSelect == 3
         Report113()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function Report1()
   local cPrint,cId,nUSD0,nUSDLE,nUSD1,nUSD2,nUSD3,nUSD4
   local nRP0,nRPLE,nRP1,nRP2,nRP3,nRP4
   local cRMCode  := space(7)
   local cRMCode1 := space(7)
   local cRMCode2 := space(7)
   local nWidth   := 200
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
      @ 18,41 say " Code RM : " get cRMCode1 picture "@!"
      @ 19,41 say "      To : " get cRMCode2 picture "@!"
      read
      if lastkey() == K_ESC
         exit
      endif
      cPrint := alert ( " Option Device  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "RMPrice.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      *@ prow(),pcol() say chr(27) + chr(77) + chr(15)  20 condensed
      @ prow(),pcol() say chr(15)
      select RMMast
      set filter to RMMast->RMCode >= cRMCode1 .and. RMMast->RMCode <= cRMCode2
      RMMast->( dbSetOrder(3) )
      RMMast->( dbGoTop() )
      do while ! RMMast-> ( EOF() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
        if nSW == 0
             nPage++
             @ prow()+1,1    say CostRef->Desc1
             @ prow()  ,170  say "   Date : "
             @ prow(),pcol() say date()
             @ prow()+1,1    say CostRef->Desc2
             @ prow()  ,170  say "   Time : "
             @ prow(),pcol() say Time()
             @ prow()+1,90   say "RM BUDGET PRICE"
             @ prow()  ,170  say "   Page : "
             @ prow(),pcol() say nPage picture "999"
             @ prow()+1,170  say "Periode : "
             @ prow()  ,180  say CostRef->Period
             @ prow()+1,1    say repli ( '=',nWidth )
             *                          1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0
             *                012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890
             @ prow()+1,1 say "|         Raw Material         |----------------------------------- U S D -----------------------------------|-------------------------------------- R U P I A H --------------------------------------|"
             @ prow()+1,1 say "|                              |  Current   |    LE      |    Qtr 1   |    Qtr 2   |    Qtr 3   |    Qtr 4   |   Current    |      LE      |     Qtr 1    |     Qtr 2    |     Qtr 3    |     Qtr 4    |"
             *                   xxxxxxx xxxxxxxxxxxxxxxxxxxx   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   9,999,999.99   9,999,999.99   9,999,999.99   9,999,999.99   9,999,999.99   9,999,999.99
             @ prow()+1,1 say "|                              |"
             @ prow(),pcol()+2 say CostRef->Rate0 picture "@z 999,999"
             @ prow(),45  say "|"
             @ prow(),pcol()+2 say CostRef->RateLE picture "@z 999,999"
             @ prow(),58  say "|"
             @ prow(),pcol()+2 say CostRef->Rate1 picture "@z 999,999"
             @ prow(),71  say "|"
             @ prow(),pcol()+2 say CostRef->Rate2 picture "@z 999,999"
             @ prow(),84  say "|"
             @ prow(),pcol()+2 say CostRef->Rate3 picture "@z 999,999"
             @ prow(),97  say "|"
             @ prow(),pcol()+2 say CostRef->Rate4 picture "@z 999,999"
             @ prow(),110 say "|              |              |              |              |              |              |"
             @ prow()+1,1   say repli ( '=',nWidth )
             nSW := 1
        endif
        if  RMMast->Id == '* ' .or. RMMast->Id == '  '
            if  RMMast->RP0 # 0 .or. RMMast->RP1 # 0 .or. RMMast->RP2 # 0 .or. RMMast->RP3 # 0 .or. RMMast->RP4 # 0
                @ prow()+1,1   say "|"
                @ prow()  ,3   say RMMast->RMCode              picture "@!"
                @ prow()  ,11  say substr(RMMast->Desc,1,20)   picture "@!"
                @ prow()  ,32  say "|"
                @ prow()  ,34  say RMMast->USD0                picture "@z 999,999.99"
                @ prow()  ,45  say "|"
                @ prow()  ,47  say RMMast->USDLE               picture "@z 999,999.99"
                @ prow()  ,58  say "|"
                @ prow()  ,60  say RMMast->USD1                picture "@z 999,999.99"
                @ prow()  ,71  say "|"
                @ prow()  ,73  say RMMast->USD2                picture "@z 999,999.99"
                @ prow()  ,84  say "|"
                @ prow()  ,86  say RMMast->USD3                picture "@z 999,999.99"
                @ prow()  ,97  say "|"
                @ prow()  ,99  say RMMast->USD4                picture "@z 999,999.99"
                @ prow()  ,110 say "|"
                @ prow()  ,112 say RMMast->RP0                 picture "@z 9,999,999.99"
                @ prow()  ,125 say "|"
                @ prow()  ,127 say RMMast->RPLE                picture "@z 9,999,999.99"
                @ prow()  ,140 say "|"
                @ prow()  ,142 say RMMast->RP1                 picture "@z 9,999,999.99"
                @ prow()  ,155 say "|"
                @ prow()  ,157 say RMMast->RP2                 picture "@z 9,999,999.99"
                @ prow()  ,170 say "|"
                @ prow()  ,172 say RMMast->RP3                 picture "@z 9,999,999.99"
                @ prow()  ,185 say "|"
                @ prow()  ,187 say RMMast->RP4                 picture "@z 9,999,999.99"
                @ prow()  ,200 say "|"
            endif
            RMMast->( dbSkip() )
        else
           cId := RMMast->Id
           if  RMMast->RP0 # 0 .or. RMMast->RP1 # 0 .or. RMMast->RP2 # 0 .or. RMMast->RP3 # 0 .or. RMMast->RP4 # 0
                @ prow()+1,1   say "|"
                @ prow()  ,3   say RMMast->RMCode              picture "@!"
                @ prow()  ,11  say substr(RMMast->Desc,1,20)   picture "@!"
                @ prow()  ,32  say "|"
                @ prow()  ,34  say RMMast->USD0                picture "@z 999,999.99"
                @ prow()  ,45  say "|"
                @ prow()  ,47  say RMMast->USDLE               picture "@z 999,999.99"
                @ prow()  ,58  say "|"
                @ prow()  ,60  say RMMast->USD1                picture "@z 999,999.99"
                @ prow()  ,71  say "|"
                @ prow()  ,73  say RMMast->USD2                picture "@z 999,999.99"
                @ prow()  ,84  say "|"
                @ prow()  ,86  say RMMast->USD3                picture "@z 999,999.99"
                @ prow()  ,97  say "|"
                @ prow()  ,99  say RMMast->USD4                picture "@z 999,999.99"
                @ prow()  ,110 say "|"
                @ prow()  ,112 say RMMast->RP0                 picture "@z 9,999,999.99"
                @ prow()  ,125 say "|"
                @ prow()  ,127 say RMMast->RPLE                picture "@z 9,999,999.99"
                @ prow()  ,140 say "|"
                @ prow()  ,142 say RMMast->RP1                 picture "@z 9,999,999.99"
                @ prow()  ,155 say "|"
                @ prow()  ,157 say RMMast->RP2                 picture "@z 9,999,999.99"
                @ prow()  ,170 say "|"
                @ prow()  ,172 say RMMast->RP3                 picture "@z 9,999,999.99"
                @ prow()  ,185 say "|"
                @ prow()  ,187 say RMMast->RP4                 picture "@z 9,999,999.99"
                @ prow()  ,200 say "|"

            endif
            do while RMMast->Id == cId .and. ! RMMast->(Eof() )
                    RMMast->( dbSkip() )
            enddo
        endif
        if prow() >= 55  &&.or. RMMast->( EOF() )
           @ prow()+1,1 say repli ( '-',nWidth )
           nSW  := 0
           eject
        endif
        if RMMast->(Eof())
           exit
        endif
      enddo
      @ prow()+1,1 say repli ( '-',nWidth )
      eject
      *@prow(), pcol() say chr(27)+chr(80)+chr(18)  20 condensed
      @prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on
      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"RMPrice.prn")
         fErase ( "RMPrice.prn" )
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil

function Report2()
   local cPrint,cRMCode,cId
   local cFGCode  := space(7)
   local cFGCode1 := space(7)
   local cFGCode2 := space(7)
   local nStdB,nStdB1,nRp0,nRpLE,nRp1,nRp2,nRp3,nRp4,nWaste
   local nWidth   := 250
   local nCost0   := 0,nCost1 := 0,nCost2 := 0,nCost3 := 0,nCost4 := 0,nCostLE := 0
   local nSW      := 0
   local nSW1     := 0
   local nPage    := 0
   local nomer    := 0
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
      cPrint := alert ( " Option Report  : ", { " View ", " Printer ", " Cancel " } )
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
      select Formula
      set filter to Formula->FGCode >= cFGCode1 .and. Formula->FGCode <= cFGCode2
      Formula->( dbGoTop() )
      do while ! Formula-> ( EOF() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
        cFGCode := Formula->FGCode
        if nSW == 0
             @ prow()+1,1    say CostRef->Desc1
             @ prow()  ,220  say "   Date : "
             @ prow(),pcol() say date()
             @ prow()+1,1    say CostRef->Desc2
             @ prow()  ,220  say "   Time : "
             @ prow(),pcol() say Time()
             @ prow()+1,120  say "RAW MATERIAL UNIT COST STANDARD"
             if nSW1 == 0
                nPage++
                @ prow(),220 say "   Page : "
                @ prow(),230 say nPage picture "999"
                nSW1 := 1
             endif
             @ prow()+1,1   say "Product : "
             FGMast->( dbSeek( cFGCode ) )
             @ prow()  ,12  say FGMast->FGCode
             @ prow()  ,20  say FGMast->Desc
             @ prow()  ,220 say "Periode : "
             @ prow()  ,230 say CostRef->Period
             @ prow()+1,1   say repli ( '=',nWidth )
*                            1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1         2
*                  0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012
@ prow()+1,1   say "|            Materials             |" &&  Standard    |   Budget     |              |                                                                                                                                           |"
@ prow(),pcol() say "   Standard    |   Budget     |              |                                                                                                                                                                       |"
@ prow()+1,1   say "|                                  |" &&  Qty. Usage   |   Wastage    |    Budget    |-------------------------------------------------------------------------------------------------------------------------------------------|"
@ prow(),pcol() say "  Qty. Usage   |   Wastage    |    Budget    |-----------------------------------------------------------------------------------------------------------------------------------------------------------------------|"
@ prow()+1,1   say "|                                  |" && (Zero Waste)  | Allowance %  |   Qty Usage  |          Current          |           Qtr I           |          Qtr II           |          Qtr III          |          Qtr IV           |"
@ prow(),pcol() say " (Zero Waste)  | Allowance %  |   Qty Usage  |          Current          |            L E            |          Qtr I            |          Qtr II           |          Qtr III          |          Qtr IV           |"
@ prow()+1,1   say "|                                  |" &&---------------|--------------|--------------|---------------------------|---------------------------|---------------------------|---------------------------|---------------------------|"
@ prow(),pcol() say "---------------|--------------|--------------|---------------------------|---------------------------|---------------------------|---------------------------|---------------------------|---------------------------|"
@ prow()+1,1   say "|                                  |" &&               |              |              |  RM Price   |  Unit Cost  |  RM Price   |  Unit Cost  |  RM Price   |  Unit Cost  |  RM Price   |  Unit Cost  |  RM Price   |  Unit Cost  |"
@ prow(),pcol() say "               |              |              |  RM Price   |  Unit Cost  |  RM Price   |  Unit Cost  |  RM Price   |  Unit Cost  |  RM Price   |  Unit Cost  |  RM Price   |  Unit Cost  |  RM Price   |  Unit Cost  |"
@ prow()+1,1   say "|                                  |" &&     ( A )     |    ( B )     |  C = A+(A*B) |    ( D )    |  E = C * D  |    ( F )    |  G = C * F  |    ( H )    |  I = C * H  |    ( J )    |  K = C * J  |    ( L )    |  M = C * L  |"
@ prow(),pcol() say "     ( A )     |    ( B )     |  C = A+(A*B) |    ( D )    |  E = C * D  |    ( F )    |  G = C * F  |    ( H )    |  I = C * H  |    ( J )    |  K = C * J  |    ( L )    |  M = C * L  |    ( N )    |  O = C * N  |"
@ prow()+1,1   say "|----------------------------------|" &&---------------|--------------|--------------|-------------------------------------------------------------------------------------------------------------------------------------------|"
@ prow(),pcol() say "---------------|--------------|--------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------|"
*                     99. xxxxxxx xxxxxxxxxxxxxxxxxxxx   99,999.999999  99,999.999999  99,999.999999  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99
*   2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1         2         3         4         5
*78901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345
*                     99,999.999999  99,999.999999  99,999.999999  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99
             nSW := 1
        endif
      if prow() <= 55
        cFGCode := Formula->FGCode
        do while Formula->FGCode == cFGCode .and. prow() <= 55
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
          Nomer++
          @ prow()+1,1   say "|"
          @ prow()  ,3   say Nomer picture "99"
          @ prow()  ,5   say "."
          @ prow()  ,7   say Formula->RMCode             picture "@!"
          @ prow()  ,15  say substr(RMMast->Desc,1,20)   picture "@!"
          @ prow()  ,36  say "|"
          @ prow()  ,38  say Formula->StandardB          picture "@z 99,999.999999"
          @ prow()  ,52  say "|"
          @ prow()  ,53  say nWaste                      picture "@z 99,999.999999"
          @ prow()  ,67  say "|"
          @ prow()  ,68  say nStdB1                      picture "@z 99,999.999999"
          @ prow()  ,82  say "|"
          @ prow()  ,83  say nRp0                        picture "@z 9,999,999.99"
          @ prow()  ,96  say "|"
          if cId == "* " .or. cId # "  "
            @ prow()  ,97  say (nStdB1*nRp0)             picture "@z 9,999,999.99"
            @ prow()  ,110 say "|"
            @ prow()  ,111 say nRpLE                     picture "@z 9,999,999.99"
            @ prow()  ,124 say "|"
            @ prow()  ,125 say (nStdB1*nRpLE)            picture "@z 9,999,999.99"
            @ prow()  ,138 say "|"
            @ prow()  ,139 say nRp1                      picture "@z 9,999,999.99"
            @ prow()  ,152 say "|"
            @ prow()  ,153 say nStdB1*nRp1               picture "@z 9,999,999.99"
            @ prow()  ,166 say "|"
            @ prow()  ,167 say nRp2                      picture "@z 9,999,999.99"
            @ prow()  ,180 say "|"
            @ prow()  ,181 say nStdB1*nRp2               picture "@z 9,999,999.99"
            @ prow()  ,194 say "|"
            @ prow()  ,195 say nRp3                      picture "@z 9,999,999.99"
            @ prow()  ,208 say "|"
            @ prow()  ,209 say nStdB1*nRp3               picture "@z 9,999,999.99"
            @ prow()  ,222 say "|"
            @ prow()  ,223 say nRp4                      picture "@z 9,999,999.99"
            @ prow()  ,236 say "|"
            @ prow()  ,237 say nStdB1*nRp4               picture "@z 9,999,999.99"
            @ prow()  ,250 say "|"
          else
            @ prow()  ,97  say (nStdB1*nRp0)/1000        picture "@z 9,999,999.99"
            @ prow()  ,110 say "|"
            @ prow()  ,111 say nRpLE                     picture "@z 9,999,999.99"
            @ prow()  ,124 say "|"
            @ prow()  ,125 say (nStdB1*nRpLE)/1000        picture "@z 9,999,999.99"
            @ prow()  ,138 say "|"
            @ prow()  ,139 say nRp1                      picture "@z 9,999,999.99"
            @ prow()  ,152 say "|"
            @ prow()  ,153 say (nStdB1*nRp1)/1000        picture "@z 9,999,999.99"
            @ prow()  ,166 say "|"
            @ prow()  ,167 say nRp2                      picture "@z 9,999,999.99"
            @ prow()  ,180 say "|"
            @ prow()  ,181 say (nStdB1*nRp2)/1000        picture "@z 9,999,999.99"
            @ prow()  ,194 say "|"
            @ prow()  ,195 say nRp3                      picture "@z 9,999,999.99"
            @ prow()  ,208 say "|"
            @ prow()  ,209 say (nStdB1*nRp3)/1000        picture "@z 9,999,999.99"
            @ prow()  ,222 say "|"
            @ prow()  ,223 say nRp4                      picture "@z 9,999,999.99"
            @ prow()  ,236 say "|"
            @ prow()  ,237 say (nStdB1*nRp4)/1000        picture "@z 9,999,999.99"
            @ prow()  ,250 say "|"
          endif
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
        if cFGCode # Formula->FGCode
            @ prow()+1,1   say repli( '-',nWidth )
            @ prow()+1,1   say "|"
            @ prow()  ,4   say " Unit Cost Per Pack "
            @ prow()  ,96  say "|"
            @ prow()  ,97  say nCost0        picture "@z 9,999,999.99"
            @ prow()  ,110 say "|"
            @ prow()  ,124 say "|"
            @ prow()  ,125 say nCostLE       picture "@z 9,999,999.99"
            @ prow()  ,138 say "|"
            @ prow()  ,152 say "|"
            @ prow()  ,153 say nCost1        picture "@z 9,999,999.99"
            @ prow()  ,166 say "|"
            @ prow()  ,180 say "|"
            @ prow()  ,181 say nCost2        picture "@z 9,999,999.99"
            @ prow()  ,194 say "|"
            @ prow()  ,208 say "|"
            @ prow()  ,209 say nCost3        picture "@z 9,999,999.99"
            @ prow()  ,222 say "|"
            @ prow()  ,236 say "|"
            @ prow()  ,237 say nCost4        picture "@z 9,999,999.99"
            @ prow()  ,250 say "|"
            @ prow()+1,1   say repli( '-',nWidth )
            @ prow()+1,1   say " "
            @ prow()+1,1   say " "
            store 0 to nCost0,nCostLE,nCost1,nCost2,nCost3,nCost4
            nSW     := 0
            Nomer   := 0
        else
            loop
        endif
      else
        nSW := 0
        nSW1:= 0
        eject
      endif
        if Formula->( Eof() )
             exit
        endif
        if prow() >= 55  .or. Formula->( EOF() )
           nSW  := 0
           nSW1 := 0
           eject
        endif
      enddo
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
return nil


function Report21()
   local cPrint,cRMCode,cId
   local cFGCode  := space(7)
   local cFGCode1 := space(7)
   local cFGCode2 := space(7)
   local nStdB,nStdB1,nRp0,nRpLE,nRp1,nRp2,nRp3,nRp4,nWaste
   local nWidth   := 250
   local nCost0   := 0,nCost1 := 0,nCost2 := 0,nCost3 := 0,nCost4 := 0,nCostLE := 0
   local nSW      := 0
   local nSW1     := 0
   local nPage    := 0
   local nomer    := 0
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
      cPrint := alert ( " Option Report  : ", { " View ", " Printer ", " Cancel " } )
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
      select Formula
      set filter to Formula->FGCode >= cFGCode1 .and. Formula->FGCode <= cFGCode2
      Formula->( dbGoTop() )
      do while ! Formula-> ( EOF() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
        cFGCode := Formula->FGCode
        if nSW == 0
             @ prow()+1,1    say CostRef->Desc1
             @ prow()  ,60  say "   Date : "
             @ prow(),pcol() say date()
             @ prow()+1,1    say CostRef->Desc2
             @ prow()  ,60  say "   Time : "
             @ prow(),pcol() say Time()
             @ prow()+1,10  say "RAW MATERIAL UNIT COST STANDARD"
             if nSW1 == 0
                nPage++
                @ prow(),60 say "   Page : "
                @ prow(),pcol()+1 say nPage picture "999"
                nSW1 := 1
             endif
             @ prow()+1,1   say "Product : "
             FGMast->( dbSeek( cFGCode ) )
             @ prow()  ,12  say FGMast->FGCode
             @ prow()  ,20  say FGMast->Desc
             @ prow()  ,60 say "Periode : "
             @ prow()  ,pcol()+1 say CostRef->Period
*                            1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1         2
*                  0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012
@ prow()+1,1    say "=================================================================================="
@ prow()+1,1    say "|            Materials             |   Standard    |                             |"
@ prow()+1,1    say "|                                  |  Qty. Usage   |-----------------------------|"
@ prow()+1,1    say "|                                  | (Zero Waste)  |           Qtr IV            |"
@ prow()+1,1    say "|                                  |---------------|-----------------------------|"
@ prow()+1,1    say "|                                  |               |   RM Price   |   Unit Cost  |"
@ prow()+1,1    say "|                                  |     ( A )     |     ( N )    |   O = C * N  |"
@ prow()+1,1    say "=================================================================================="
*                     99. xxxxxxx xxxxxxxxxxxxxxxxxxxx   99,999.999999  99,999.999999  99,999.999999  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99
*   2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1         2         3         4         5
*78901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345
*                     99,999.999999  99,999.999999  99,999.999999  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99  9,999,999.99
             nSW := 1
        endif
      if prow() <= 55
        cFGCode := Formula->FGCode
        do while Formula->FGCode == cFGCode .and. prow() <= 55
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
          Nomer++
          @ prow()+1,1   say "|"
          @ prow()  ,3   say Nomer picture "99"
          @ prow()  ,5   say "."
          @ prow()  ,7   say Formula->RMCode             picture "@!"
          @ prow()  ,15  say substr(RMMast->Desc,1,20)   picture "@!"
          @ prow()  ,36  say "|"
          @ prow()  ,38  say Formula->StandardB          picture "@z 99,999.999999"
          @ prow()  ,52  say "|"
          *@ prow()  ,53  say nWaste                      picture "@z 99,999.999999"
          *@ prow()  ,67  say "|"
          *@ prow()  ,68  say nStdB1                      picture "@z 99,999.999999"
          *@ prow()  ,82  say "|"
          *@ prow()  ,83  say nRp0                        picture "@z 9,999,999.99"
          *@ prow()  ,96  say "|"
          if cId == "* " .or. cId # "  "
            *@ prow()  ,97  say (nStdB1*nRp0)             picture "@z 9,999,999.99"
            *@ prow()  ,110 say "|"
            *@ prow()  ,111 say nRpLE                     picture "@z 9,999,999.99"
            *@ prow()  ,124 say "|"
            *@ prow()  ,125 say (nStdB1*nRpLE)            picture "@z 9,999,999.99"
            *@ prow()  ,138 say "|"
            *@ prow()  ,139 say nRp1                      picture "@z 9,999,999.99"
            *@ prow()  ,152 say "|"
            *@ prow()  ,153 say nStdB1*nRp1               picture "@z 9,999,999.99"
            *@ prow()  ,166 say "|"
            *@ prow()  ,167 say nRp2                      picture "@z 9,999,999.99"
            *@ prow()  ,180 say "|"
            *@ prow()  ,181 say nStdB1*nRp2               picture "@z 9,999,999.99"
            *@ prow()  ,194 say "|"
            *@ prow()  ,195 say nRp3                      picture "@z 9,999,999.99"
            *@ prow()  ,208 say "|"
            *@ prow()  ,209 say nStdB1*nRp3               picture "@z 9,999,999.99"
            *@ prow()  ,222 say "|"
            @ prow()  ,pcol()+1 say nRp4                      picture "@z 9,999,999.99"
            @ prow()  ,pcol()+1 say "|"
            @ prow()  ,pcol()+1 say nStdB1*nRp4               picture "@z 9,999,999.99"
            @ prow()  ,pcol()+1 say "|"
          else
            *@ prow()  ,97  say (nStdB1*nRp0)/1000        picture "@z 9,999,999.99"
            *@ prow()  ,110 say "|"
            *@ prow()  ,111 say nRpLE                     picture "@z 9,999,999.99"
            *@ prow()  ,124 say "|"
            *@ prow()  ,125 say (nStdB1*nRpLE)/1000        picture "@z 9,999,999.99"
            *@ prow()  ,138 say "|"
            *@ prow()  ,139 say nRp1                      picture "@z 9,999,999.99"
            *@ prow()  ,152 say "|"
            *@ prow()  ,153 say (nStdB1*nRp1)/1000        picture "@z 9,999,999.99"
            *@ prow()  ,166 say "|"
            *@ prow()  ,167 say nRp2                      picture "@z 9,999,999.99"
            *@ prow()  ,180 say "|"
            *@ prow()  ,181 say (nStdB1*nRp2)/1000        picture "@z 9,999,999.99"
            *@ prow()  ,194 say "|"
            *@ prow()  ,195 say nRp3                      picture "@z 9,999,999.99"
            *@ prow()  ,208 say "|"
            *@ prow()  ,209 say (nStdB1*nRp3)/1000        picture "@z 9,999,999.99"
            *@ prow()  ,222 say "|"
            @ prow()  ,pcol()+1 say nRp4                      picture "@z 9,999,999.99"
            @ prow()  ,pcol()+1 say "|"
            @ prow()  ,pcol()+1 say (nStdB1*nRp4)/1000        picture "@z 9,999,999.99"
            @ prow()  ,pcol()+1 say "|"
          endif
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
        if cFGCode # Formula->FGCode
            @ prow()+1,1   say "----------------------------------------------------------------------------------"
            @ prow()+1,1   say "|    Unit Cost Per Pack                            |              |"
            *@ prow()  ,97  say nCost0        picture "@z 9,999,999.99"
            *@ prow()  ,110 say "|"
            *@ prow()  ,124 say "|"
            *@ prow()  ,125 say nCostLE       picture "@z 9,999,999.99"
            *@ prow()  ,138 say "|"
            *@ prow()  ,152 say "|"
            *@ prow()  ,153 say nCost1        picture "@z 9,999,999.99"
            *@ prow()  ,166 say "|"
            *@ prow()  ,180 say "|"
            *@ prow()  ,181 say nCost2        picture "@z 9,999,999.99"
            *@ prow()  ,194 say "|"
            *@ prow()  ,208 say "|"
            *@ prow()  ,209 say nCost3        picture "@z 9,999,999.99"
            *@ prow()  ,222 say "|"
            *@ prow()  ,236 say "|"
            @ prow()  ,pcol()+1 say nCost4        picture "@z 9,999,999.99"
            @ prow()  ,pcol()+1 say "|"
            @ prow()+1,1   say "----------------------------------------------------------------------------------"
            @ prow()+1,1   say " "
            @ prow()+1,1   say " "
            store 0 to nCost0,nCostLE,nCost1,nCost2,nCost3,nCost4
            nSW     := 0
            Nomer   := 0
        else
            loop
        endif
      else
        nSW := 0
        nSW1:= 0
        eject
      endif
        if Formula->( Eof() )
             exit
        endif
        if prow() >= 55  .or. Formula->( EOF() )
           nSW  := 0
           nSW1 := 0
           eject
        endif
      enddo
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
return nil



function Report3()
   local cPrint,cPrType2,cPrType1
   local cFGCode  := space(7)
   local cFGCode1 := space(7)
   local cFGCode2 := space(7)
   local nWidth   := 110
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
      cPrint := alert ( " Edit List  : ", { " View ", " Printer ", " Cancel " } )
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
      FGMast->Price00#0 .or. FGMast->PriceLE#0 .or. FGMast->Price01#0 .or. FGMast->Price02#0 .or. FGMast->Price03#0 .or. FGMast->Price04#0 .or.;
      FGMast->Price10#0 .or. FGMast->PriceLE1#0.or. FGMast->Price11#0 .or. FGMast->Price12#0 .or. FGMast->Price13#0 .or. FGMast->Price14#0 .or.;
      FGMast->Price20#0 .or. FGMast->PriceLE2#0.or. FGMast->Price21#0 .or. FGMast->Price22#0 .or. FGMast->Price23#0 .or. FGMast->Price24#0 .or.;
      FGMast->Price30#0 .or. FGMast->PriceLE3#0.or. FGMast->Price31#0 .or. FGMast->Price32#0 .or. FGMast->Price33#0 .or. FGMast->Price34#0)
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
             @ prow()  ,208  say "Date : "
             @ prow(),pcol() say date()
             @ prow()+1,1    say CostRef->Desc2
             @ prow()  ,208  say "Time : "
             @ prow(),pcol() say Time()
             @ prow()+1,101  say "SELLING PRICE  &  UNIT COST"
             @ prow()+1,101  say "    AOP Year : "
             @ prow(),pcol() say ltrim(str(val(right(CostRef->Per,4))+1)) picture "@!"
             @ prow()  ,208  say "Page : "
             @ prow(),pcol() say nPage picture "999"
             *@ prow()+1,1    say repli ( '=',nWidth )
             *@ prow()  ,117  say repli ( '=',nWidth )
*                          1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1         2
*                01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789
*@ prow()+1,1 say "|         Finished Goods       |------------------------------ SELLING PRICE --------------------------------|      |         Finished Goods       |--------------------------------- UNIT COST  --------------------------------|"
*@ prow()+1,1 say "|                              |  Current   |    L E     |    Qtr 1   |    Qtr 2   |    Qtr 3   |    Qtr 4   |      |                              |  Current   |    L E     |    Qtr 1   |    Qtr 2   |    Qtr 3   |    Qtr 4   |"
*                   xxxxxxx xxxxxxxxxxxxxxxxxxxx   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99          xxxxxxx xxxxxxxxxxxxxxxxxxxx   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99   999,999.99


@ prow()+1,1 say "=====================================================================================  ========================================================  ========================================================  ========================================================  ========================================================="
@ prow()+1,1 say "|         Finished Goods     |---------------- SELLING PRICE CIKAMPEK---------------|  |---------------- SELLING PRICE SEMARANG---------------|  |---------------- SELLING PRICE SURABAYA---------------|  |---------------- SELLING PRICE PALEMBANG--------------|  |---------------- UNIT COST  --------------------------|"
@ prow()+1,1 say "|                            | Current |  L E   |  Qtr 1 |  Qtr 2 |  Qtr 3 |  Qtr 4 |  | Current |  L E   |  Qtr 1 |  Qtr 2 |  Qtr 3 |  Qtr 4 |  | Current |  L E   |  Qtr 1 |  Qtr 2 |  Qtr 3 |  Qtr 4 |  | Current |  L E   |  Qtr 1 |  Qtr 2 |  Qtr 3 |  Qtr 4 |  | Current |  L E   |  Qtr 1 |  Qtr 2 |  Qtr 3 |  Qtr 4 |"
@ prow()+1,1 say "=====================================================================================  ========================================================  ========================================================  ========================================================  ========================================================="

             *@ prow()+1,1    say repli ( '=',nWidth )
             *@ prow()  ,117  say repli ( '=',nWidth )
             nSW := 1
        endif
        cPrType1 := FGMast->PrType1
        do while FGMast->PrType1 == cPrType1 .and. prow()<= 55 .and. ! FGMast->(Eof())
           cPrType2 := FGMast->PrType2
           do while FGMast->PrType2 == cPrType2 .and. FGMast->PrType1 == cPrType1 .and. prow()<= 55 .and. ! FGMast->(Eof())
              if  FGMast->UC0 # 0 .or. FGMast->UCLE#0 .or. FGMast->UC1 # 0 .or. FGMast->UC2 # 0 .or. FGMast->UC3 # 0 .or. FGMast->UC4 # 0 .or. ;
                  FGMast->Price00 # 0 .or. FGMast->PriceLE#0 .or. FGMast->Price01 # 0 .or. FGMast->Price02 # 0 .or. FGMast->Price03 # 0 .or. FGMast->Price04 # 0 .or.;
                  FGMast->Price10#0 .or. FGMast->PriceLE1#0.or. FGMast->Price11#0 .or. FGMast->Price12#0 .or. FGMast->Price13#0 .or. FGMast->Price14#0 .or.;
                  FGMast->Price20#0 .or. FGMast->PriceLE2#0.or. FGMast->Price21#0 .or. FGMast->Price22#0 .or. FGMast->Price23#0 .or. FGMast->Price24#0
                  @ prow()+1,3   say FGMast->FGCode              picture "@!"
                  @ prow()  ,11  say substr(FGMast->Desc,1,20)   picture "@!"
                  @ prow()  ,pcol()    say FGMast->Price00       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->PriceLE       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price01       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price02       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price03       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price04       picture "@z 9,999.99"

                  @ prow()  ,pcol()+5  say FGMast->Price10       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->PriceLE1      picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price11       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price12       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price13       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price14       picture "@z 9,999.99"

                  @ prow()  ,pcol()+5  say FGMast->Price20       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->PriceLE2      picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price21       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price22       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price23       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price24       picture "@z 9,999.99"

                  @ prow()  ,pcol()+5  say FGMast->Price30       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->PriceLE3      picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price31       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price32       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price33       picture "@z 9,999.99"
                  @ prow()  ,pcol()+1  say FGMast->Price34       picture "@z 9,999.99"


                  @ prow()  ,pcol()+1 say FGMast->UC0                 picture "@z 99,999.99"
                  @ prow()  ,pcol()+1 say FGMast->UCLE                picture "@z 99,999.99"
                  @ prow()  ,pcol()+1 say FGMast->UC1                 picture "@z 99,999.99"
                  @ prow()  ,pcol()+1 say FGMast->UC2                 picture "@z 99,999.99"
                  @ prow()  ,pcol()+1 say FGMast->UC3                 picture "@z 99,999.99"
                  @ prow()  ,pcol()+1 say FGMast->UC4                 picture "@z 99,999.99"
              endif
              FGMast->( dbSkip() )
              if prow() >= 55  .or. FGMast->( EOF() )
                 @ prow()+1,1 say "=====================================================================================  ========================================================  ========================================================  ========================================================="
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


function Report12()
   *use
   *dataDict()
   local cPrint,cRMCode,cId
   local cFGCode  := space(7)
   local cFGCode1 := space(7)
   local cFGCode2 := space(7)
   local nStdB,nStdB1,nRp0,nRpLE,nRp1,nRp2,nRp3,nRp4,nWaste
   local nWidth   := 250
   local nCost0   := 0,nCost1 := 0,nCost2 := 0,nCost3 := 0,nCost4 := 0,nCostLE := 0
   local nSW      := 0
   local nSW1     := 0
   local nPage    := 0
   local nomer    := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   dataDict()
   set date British
   set score off

   Do while .T.
      sbox( 17,40,20,62,"gr+/w+,w+/bg+" )
      @ 18,41 say " Code FG : " get cFGCode1 picture "@!"
      @ 19,41 say "      To : " get cFGCode2 picture "@!"
      read
      if lastkey() == K_ESC
         exit
      endif
      cPrint := alert ( " Option Report  : ", { " View ", " Printer ", " Cancel " } )
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

   Sele yc06
   set order to 1
   zap

   sele yc061203
   go top
   do while !eof()
      Sele yc06
      set order to 1
      if !dbseek(yc061203->kode)
         append blank
         repl kode     with yc061203->kode
         repl material with yc061203->material
         repl qty      with yc061203->qty
         repl amt      with yc061203->amt
      endif
      sele yc061203
      skip
   enddo

   sele yc061204
   go top
   do while !eof()
      Sele yc06
      set order to 1
      if !dbseek(yc061204->kode)
         append blank
         repl kode     with yc061204->kode
         repl material with yc061204->material
         repl qty      with yc061204->qty
         repl amt      with yc061204->amt
      endif
      sele yc061204
      skip
   enddo

   sele yc061205
   go top
   do while !eof()
      Sele yc06
      set order to 1
      if !dbseek(yc061205->kode)
         append blank
         repl kode     with yc061205->kode
         repl material with yc061205->material
         repl qty      with yc061205->qty
         repl amt      with yc061205->amt
      endif
      sele yc061205
      skip
   enddo



      select Mcdsap
      set order to 1
      set filter to Mcdsap->code >= cFGCode1 .and. Mcdsap->code <= cFGCode2
      Mcdsap->( dbGoTop() )
      Do while ! Mcdsap-> ( EOF() )
         set device to screen
         sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
         setcolor( "gr+*/bg,w+/br+" )
         @ 21,25  say " Wait for a minutes ..... "
         set device to print
         mgroup1 := Code1
         mnama1  := Group
         mcr     := 0
         Do while ! Mcdsap-> ( EOF() ) .and. mgroup1=Code1
         cFGCode := Mcdsap->Code
         sele formula
         set order to 1
         if Dbseek(cFGCode)
            mcr     := 1
            mGaram  := 0
            mGula   := 0
            mMSG    := 0
            mBGrng  := 0
            mBCabe  := 0
            mMOlien := 0
            mBMerah := 0
            mKecap  := 0
            mSambal := 0
            mEtiket := 0
            mKarton := 0
            mPremix := 0
            mSayur  := 0
            mOther  := 0
            Do while Formula->FGCode == cFGCode .and. ! Formula-> ( EOF() )
               cRMCode := Formula->RMCode
               nStdB   := Formula->StandardB
               RMMast->( dbSeek( cRMCode ) )
               cGroup := RMMast->type
               cId    := RMMast->Id
               nRp0   := RMMast->Rp0
               nRpLE  := RMMast->RpLE
               nRp1   := RMMast->Rp1
               nRp2   := RMMast->Rp2
               nRp3   := RMMast->Rp3
               nRp4   := RMMast->Rp4
               nWaste := RMMast->Waste
               nStdB1 := nStdB+(nStdB*nWaste)
               if cId == "* " .or. cId # "  "
                  mucost = nStdB1*nRp4
                  nCost0  := nCost0  + (nStdB1*nRp0)
                  nCostLE := nCostLE + (nStdB1*nRpLE)
                  nCost1  := nCost1  + (nStdB1*nRp1)
                  nCost2  := nCost2  + (nStdB1*nRp2)
                  nCost3  := nCost3  + (nStdB1*nRp3)
                  nCost4  := nCost4  + (nStdB1*nRp4)
               else
                  mucost = (nStdB1*nRp4)/1000
                  nCost0  := nCost0  + ((nStdB1*nRp0)/1000)
                  nCostLE := nCostLE + ((nStdB1*nRpLE)/1000)
                  nCost1  := nCost1  + ((nStdB1*nRp1)/1000)
                  nCost2  := nCost2  + ((nStdB1*nRp2)/1000)
                  nCost3  := nCost3  + ((nStdB1*nRp3)/1000)
                  nCost4  := nCost4  + ((nStdB1*nRp4)/1000)         
               endif
               do case
                  case cRMCode='301578'
                       mGaram = mucost
                  case cRMCode='301579'
                       mGula  = mucost
                  case cRMCode='301580'
                       mMSG   = mucost
                  case cRMCode='301923' .or. cRMCode='305292' .or. cRMCode='307570' .or. cRMCode='306129'
                       mBGrng = mBGrng  + mucost
                  case cRMCode='202240' .or. cRMCode='201890' .or. cRMCode='201887' .or. cRMCode='301630'
                       mBCabe = mBCabe  + mucost
                  case cRMCode='301593'
                       mMOlien= mucost
                  case cRMCode='301655'
                       mBMerah= mucost
                  case cRMCode='202487' .or. cRMCode='204284' .or. cRMCode='200289' .or. cRMCode='101748' .or. cRMCode='301641'
                       mKecap = mKecap  + mucost
                  case cRMCode='204759' .or. cRMCode='101650' .or. cRMCode='103213' .or. cRMCode='101542' .or. cRMCode='104482'
                       mSambal= mSambal + mucost
                  case cRMCode>='200215' .and. cRMCode<='200254'
                       mEtiket= mEtiket + mucost
                  case cRMCode='301637' .or. cRMCode='301638' .or. cRMCode='301639'
                       mKarton= mKarton + mucost
                  other
                       do case
                          case cGroup ='RE'
                               mPremix= mPremix + mucost
                          case cGroup ='RH'
                               mSayur = mSayur  + mucost
                          other
                               mOther = mOther  + mucost
                       endcase
               Endcase
               Formula->( dbSkip() )
            Enddo
            if prow() >= 55 .or. nSW = 0
               mfooter :=  "+======================================+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+===========+===========+===========+===========+===========+"
               mfooter1:=  "+--------------------------------------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+-----------+-----------+-----------+-----------+-----------+"
               mfooter2:=                                        " |          |          |          |          |          |          |          |          |          |          |          |          |          |          |           |           |           |           |           |"
               if prow() >= 55
                  @ prow()+1,1    say mfooter
                  eject
               endif
               nSW := 1
               @ prow()+1,1    say CostRef->Desc1
               @ prow()  ,220  say "   Date : "
               @ prow(),pcol() say date()
               @ prow()+1,1    say CostRef->Desc2
               @ prow()  ,220  say "   Time : "
               @ prow(),pcol() say Time()
               @ prow()+1,120  say "RAW MATERIAL UNIT COST STANDARD"
               nPage++
               @ prow(),220 say "   Page : "
               @ prow(),230 say nPage picture "999"
               @ prow()+1,220 say "Periode : "
               @ prow()  ,230 say CostRef->Period
               @ prow()+1,1   say "+======================================+=====================================================================================================================================================================+=======================+=======================+"
               @ prow()+1,1   say "|            Flavour                   |                                                                            Raw Materials                                                                            |        PE             |       COGS            |"
               @ prow()+1,1   say "|                                      +----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+-----------+-----------+-----------+-----------+-----------+"
               @ prow()+1,1   say "|                                      |   Garam  |   Gula   |    MSG   |  Premix  |  Bawang  |   Bubuk  |  Minyak  |  Bawang  |  Kecap   |  Sambal  |   Sayur  |  Etiket  |  Karton  |  Others  |   TOTAL   | Sandard   |  Actual   | Sandard   |  Actual   |"
               @ prow()+1,1   say "|                                      |          |          |          |          |  Goreng  |   Cabe   |  Olien   |  Merah   |          |          |          |          |          |          |           |           |           |           |           |"
               @ prow()+1,1   say "+--------------------------------------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+-----------+-----------+-----------+-----------+-----------+"
                
            endif
            if substr(mgroup1,1,1)='C'.or.substr(mgroup1,1,1)='D'
            mJml  := mGaram+mGula+mMSG+mBGrng+mBCabe+mMOlien+mBMerah+mKecap+mSambal+mEtiket+mKarton+mPremix+mSayur+mOther
            store 0 to nCost0,nCostLE,nCost1,nCost2,nCost3,nCost4
            Nomer++
            @ prow()+1,1      say "|"
            @ prow()  ,pcol()+0 say Nomer picture "9999"
            @ prow()  ,pcol() say ". "
            FGMast->( dbSeek( cFGCode ) )
            if FGMast->Prtype2='23'
               mpestd:=costref->pe14
            else
               mpestd:=costref->pe04
            endif
            YC06->( dbSeek( cFGCode ) )
            mpeact   :=YC06->amt/yc06->qty
            mcogsstd :=mjml+mpestd
            mcogsact :=mjml+mpeact
            @ prow()  ,pcol() say cFGCode+""
            @ prow()  ,pcol() say substr(FGMast->Desc,1,25)+"|"
            @ prow()  ,pcol() say mGaram   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mGula    picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mMSG     picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mPremix  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mBGrng   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mBCabe   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mMOlien  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mBMerah  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mKecap   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mSambal  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mSayur   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mEtiket  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mKarton  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mOther   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mJml     picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mpestd   picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mpeact   picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mcogsstd picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mcogsact picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            if mcdsap->( Eof() )
               @ prow()+1,1   say MFooter
               exit
            endif
            endif
         Endif
         sele mcdsap
         skip
         enddo
         if substr(mgroup1,1,1)='C'.or.substr(mgroup1,1,1)='D'
            if mcr=1
               @ prow()+1,1   say MFooter1
               @ prow()+1,1   say "|       ****"+Mnama1+mfooter2
               @ prow()+1,1   say MFooter1
            endif
         endif
      enddo
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
return nil

function Report13()
   local cPrint,cRMCode,cId
   local cFGCode  := space(7)
   local cFGCode1 := space(7)
   local cFGCode2 := space(7)
   local nStdB,nStdB1,nRp0,nRpLE,nRp1,nRp2,nRp3,nRp4,nWaste
   local nWidth   := 250
   local nCost0   := 0,nCost1 := 0,nCost2 := 0,nCost3 := 0,nCost4 := 0,nCostLE := 0
   local nSW      := 0
   local nSW1     := 0
   local nPage    := 0
   local nomer    := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   dataDict()
   set date British
   set score off

   Do while .T.
      sbox( 17,40,20,62,"gr+/w+,w+/bg+" )
      @ 18,41 say " Code FG : " get cFGCode1 picture "@!"
      @ 19,41 say "      To : " get cFGCode2 picture "@!"
      read
      if lastkey() == K_ESC
         exit
      endif
      cPrint := alert ( " Option Report  : ", { " View ", " Printer ", " Cancel " } )
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


      select Mcdsap
      set order to 1
      set filter to Mcdsap->code >= cFGCode1 .and. Mcdsap->code <= cFGCode2
      Mcdsap->( dbGoTop() )
      Do while ! Mcdsap-> ( EOF() )
         set device to screen
         sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
         setcolor( "gr+*/bg,w+/br+" )
         @ 21,25  say " Wait for a minutes ..... "
         set device to print
         mgroup1 := Code1
         mnama1  := Group
         mcr     := 0
         Do while ! Mcdsap-> ( EOF() ) .and. mgroup1=Code1
         cFGCode := Mcdsap->Code
         sele Fgnew
         set order to 1
         if Dbseek(cFGCode)
            mcr     := 1
            mGaram  := 0
            mGula   := 0
            mMSG    := 0
            mBGrng  := 0
            mBCabe  := 0
            mMOlien := 0
            mBMerah := 0
            mKecap  := 0
            mSambal := 0
            mEtiket := 0
            mKarton := 0
            mPremix := 0
            mSayur  := 0
            mOther  := 0

            vGaram  := 0
            vGula   := 0
            vMSG    := 0
            vBGrng  := 0
            vBCabe  := 0
            vMOlien := 0
            vBMerah := 0
            vKecap  := 0
            vSambal := 0
            vEtiket := 0
            vKarton := 0
            vPremix := 0
            vSayur  := 0
            vOther  := 0

            Do while Fgnew->FGCode == cFGCode .and. ! Fgnew-> ( EOF() )
               cRMCode := Fgnew->input_mate
               *if cRMCode
               RMMast->( dbSeek( cRMCode ) )
               cGroup := RMMast->type
               do case
                  case cRMCode='301578'
                       mGaram = usageaqty
                       vGaram = usageaqty
                  case cRMCode='301579'
                       mGula  = usageaqty
                       VGula  = usageaqty
                  case cRMCode='301580'
                       mMSG   = usageaqty
                       vMSG   = usageaqty
                  case cRMCode='301923' .or. cRMCode='305292' .or. cRMCode='307570' .or. cRMCode='306129'
                       mBGrng = mBGrng  + usageaqty
                       vBGrng = VBGrng  + usageaqty
                  case cRMCode='202240' .or. cRMCode='201890' .or. cRMCode='201887' .or. cRMCode='301630'
                       mBCabe = mBCabe  + usageaqty
                       vBCabe = vBCabe  + usageaqty
                  case cRMCode='301593'
                       mMOlien= usageaqty
                       vMOlien= usageaqty
                  case cRMCode='301655'
                       mBMerah= usageaqty
                       vBMerah= usageaqty
                  case cRMCode='202487' .or. cRMCode='204284' .or. cRMCode='200289' .or. cRMCode='101748' .or. cRMCode='301641'
                       mKecap = mKecap  + usageaqty
                       vKecap = vKecap  + usageaqty
                  case cRMCode='204759' .or. cRMCode='101650' .or. cRMCode='103213' .or. cRMCode='101542' .or. cRMCode='104482'
                       mSambal= mSambal + usageaqty
                       vSambal= vSambal + usageaqty
                  case cRMCode>='20210' .and. cRMCode<='200251'
                       mEtiket= mEtiket + usageaqty
                       vEtiket= vEtiket + usageaqty
                  case cRMCode='301637' .or. cRMCode='301638' .or. cRMCode='301639'
                       mKarton= mKarton + usageaqty
                       vKarton= vKarton + usageaqty
                  other
                       do case
                          case cGroup ='RE'
                               mPremix= mPremix + mucost
                          case cGroup ='RH'
                               mSayur = mSayur  + mucost
                          other
                               mOther = mOther  + mucost
                       endcase
               Endcase
               Formula->( dbSkip() )
            Enddo
            if prow() >= 55 .or. nSW = 0
               mfooter :=  "+======================================+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+==========+===========+===========+===========+===========+===========+"
               mfooter1:=  "+--------------------------------------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+-----------+-----------+-----------+-----------+-----------+"
               mfooter2:=                                        " |          |          |          |          |          |          |          |          |          |          |          |          |          |          |           |           |           |           |           |"
               if prow() >= 55
                  @ prow()+1,1    say mfooter
                  eject
               endif
               nSW := 1
               @ prow()+1,1    say CostRef->Desc1
               @ prow()  ,220  say "   Date : "
               @ prow(),pcol() say date()
               @ prow()+1,1    say CostRef->Desc2
               @ prow()  ,220  say "   Time : "
               @ prow(),pcol() say Time()
               @ prow()+1,120  say "RAW MATERIAL UNIT COST STANDARD"
               nPage++
               @ prow(),220 say "   Page : "
               @ prow(),230 say nPage picture "999"
               @ prow()+1,220 say "Periode : "
               @ prow()  ,230 say CostRef->Period
               @ prow()+1,1   say "+======================================+=====================================================================================================================================================================+=======================+=======================+"
               @ prow()+1,1   say "|            Flavour                   |                                                                            Raw Materials                                                                            |        PE             |       COGS            |"
               @ prow()+1,1   say "|                                      +----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+-----------+-----------+-----------+-----------+-----------+"
               @ prow()+1,1   say "|                                      |   Garam  |   Gula   |    MSG   |  Premix  |  Bawang  |   Bubuk  |  Minyak  |  Bawang  |  Kecap   |  Sambal  |   Sayur  |  Etiket  |  Karton  |  Others  |   TOTAL   | Sandard   |  Actual   | Sandard   |  Actual   |"
               @ prow()+1,1   say "|                                      |          |          |          |          |  Goreng  |   Cabe   |  Olien   |  Merah   |          |          |          |          |          |          |           |           |           |           |           |"
               @ prow()+1,1   say "+--------------------------------------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+----------+-----------+-----------+-----------+-----------+-----------+"
                
            endif
            if substr(mgroup1,1,1)='C'.or.substr(mgroup1,1,1)='D'
            mJml  := mGaram+mGula+mMSG+mBGrng+mBCabe+mMOlien+mBMerah+mKecap+mSambal+mEtiket+mKarton+mPremix+mSayur+mOther
            store 0 to nCost0,nCostLE,nCost1,nCost2,nCost3,nCost4
            Nomer++
            @ prow()+1,1      say "|"
            @ prow()  ,pcol()+0 say Nomer picture "9999"
            @ prow()  ,pcol() say ". "
            FGMast->( dbSeek( cFGCode ) )
            if FGMast->Prtype2='23'
               mpestd:=costref->pe14
            else
               mpestd:=costref->pe04
            endif
            YC06->( dbSeek( cFGCode ) )
            mpeact   :=YC06->amt/yc06->qty
            mcogsstd :=mjml+mpestd
            mcogsact :=mjml+mpeact
            @ prow()  ,pcol() say cFGCode+""
            @ prow()  ,pcol() say substr(FGMast->Desc,1,25)+"|"
            @ prow()  ,pcol() say mGaram   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mGula    picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mMSG     picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mPremix  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mBGrng   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mBCabe   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mMOlien  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mBMerah  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mKecap   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mSambal  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mSayur   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mEtiket  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mKarton  picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mOther   picture "@z 99,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mJml     picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mpestd   picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mpeact   picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mcogsstd picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            @ prow()  ,pcol() say mcogsact picture "@z 999,999.99"
            @ prow()  ,pcol() say " |"
            if mcdsap->( Eof() )
               @ prow()+1,1   say MFooter
               exit
            endif
            endif
         Endif
         sele mcdsap
         skip
         enddo
         if substr(mgroup1,1,1)='C'.or.substr(mgroup1,1,1)='D'
            if mcr=1
               @ prow()+1,1   say MFooter1
               @ prow()+1,1   say "|       ****"+Mnama1+mfooter2
               @ prow()+1,1   say MFooter1
            endif
         endif
      enddo
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
return nil

 

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        1713138  Zainudin Achmad  1713141  Evi Sulviana  1713142  1713143  Achmad Sanusi  1713144  Dadun  1713145  1713146  Muji Priyatno  1713147  Dudung  1713148  Mujiarto  1713150  Muhammad Wahyudin  1713151  Sahidin  1713152  1713155  Yusup Supiandi  1713156  Zul Effendi  1713157  Sutoro  1713158
  Sudarmadji  1713159  1713160  Agus Khumaedi  1713161  Asep Syaipudin  1713162  Sujana  1713167  R. Ahmad Muzaffar  1713168  Muhammad Helmi  1713169  Gunarwan  1713171  Selam  1713172  Mutasodirin  1713176  Hadiman Basir  1713177  Igo Rinaldo  1713178  May Maulana  1713179  Yayan Maulana  1713180  Suyatno  1713183  1713187  Dedi Supriyadi  1713188  Saepuloh  1713189  Toto Hindaryo  1713192  Agus Rostandi  1713193  Dodi Hendra  1713195
  Agus Majid  1713197  Adun Junari  1713200  Cepi Suheryana  1713202  Samudi  1713203  Kadi  1713205  Noor Zahrul Ferry  1713207  Muhammad Sofwan Ma'Ruf  1713209  1713210  Alamsyah Nur Amin  1713211  BA1155  ASHARI  BA3385  CECEP SUTARYA  BD1018  MUHAMMAD  BA3388  ISAK  BA1565  SUTONO  BA0202  AYI AKIM  BA3414  BA3411  ASEP BAHTIAR  BA3245  DARYA PREMIX  BD1124  IRE  BA3415  WALAM  BA0286  BA1570  TATA SUPRIYATNA  BA0244  DEDEH KURNIASIH  BA3331  EDI SUMARDI  BA1480  NURSADJIDIN  BC0047	  BAMBANG S  BA3431  WAHYU PURANAMA  BA0210  SOLEH  BA0298  BA0301  DADAN HAMDANI  BA3416  ANDI HIDAYAT  BA1532	  MULYADI B  BA3423  MAMAT MUSLIM  BD1040  DJULI MARGONO  BD2039  TE.           Z2d11 3d1Í    ..          Z2d11 3d1–Ì    A1 . b i n  w  ÿÿÿÿÿÿÿÿÿÿ  ÿÿÿÿå       BIN Z2d11 3d1Í    åC a c h e  y  ÿÿÿÿÿÿÿÿÿÿ  ÿÿÿÿCACHE       =)e11 *e1Í    AS e t t i  øn g s   ÿÿÿÿ  ÿÿÿÿSETTINGS    \a	1	1 ]a	1Í                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
