#include "Stock.CH"

function Formula()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey1
     set key 19 to Lkey1
     sbox(11,45,14,59,"gr+/bg+,w+/br+")
     @ 12     ,46 prompt " Formula NDL "
     @ row()+1,46 prompt " Formula FG  "
     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         FormulaA()
       case nSelect == 2
         FormulaB()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function FormulaA()
  local cClr   := setcolor()
  local cScr   := savescreen()
  local cClr1  := setcolor()
  local cScr1  := savescreen()
  local cNDLCode,cDesc,I
  local lSudahAda
  local GetList := {}
  dataDict()
  set score off
  set date British

  do while .T.
    @ 0,0 clea to 24,79
    NDLForm->( dbGoBottom() )
    NDLForm->( dbSkip() )
    cNDLCode  := NDLForm->NDLCode
    setcolor( "n+/n+" )
    sbox( 0,0,03,78, "gr+/W+,W+/bg+" )
    setcursor(1)
    @ 1,1 say " NOODLE Code : " get cNDLCode   picture "@!"  when  myMessage ( " Masukkan Code NOODLE, atau tekan Esc untuk keluar .... " ) valid chkCodeNDL ( @cNDLCode )
    read
    if lastkey() == K_ESC
      exit
      setcolor (cClr)
      restscreen  ( 0,0,maxrow(),maxcol(),cScr)
    endif
    NDLMast->( dbSeek ( cNDLCode ) )
    cDesc  := NDLMast->Desc
    @ 1,28 say cDesc  picture "@!"
    lSudahAda := NDLForm->(dbSeek(cNDLCode))
    @ 24,0 clea to 24,79
    if lastkey() == K_ESC
       exit
    endif
    FormulaA1(cNDLCode,lSudahAda)
enddo
NFPrint()
close all
setcolor (cClr)
restscreen  ( 0,0,maxrow(),maxcol(),cScr )
return nil

func AV211(x,y)
     local MScreen:=savescreen(), MColor:=setcolor()
     local desc
     local i:=1
     if ! y=2
        return .T.
     endif
     if x == space(7)
        chkCodeFG(@x,@desc)
     endif
     select FGMast
     set order to 1
     dbseek(x)
     if ! found()
       chkCodeFG(@x,@desc)
     endif
     if found()
        x   := FGMast->FGCode
        desc:= FGMast->Desc
     endif
     select TempNDL
     restscreen(,,,,MScreen)
     setcolor(MColor)
     if lastkey()==K_ENTER
        repl TempNDL->FGCode with x,TempNDL->Desc with Desc
        return .T.
     endif
return .F.

function FormulaA1(cNDLCode,lSudahAda)
   local cClr1  := setcolor()
   local cScr1  := savescreen()
   local cClr2  := setcolor()
   local cScr2  := savescreen()
   local cCode,cDesc
   local FieldSkrg,MEdit
   local B:=TBrowseDB(),Pil,X,nRec
   local i := 0
   local j := 0
   local C1,C2,C3,C4,c5
   local Akolom[5], APict[5], Ajudul[5]
   local GetList:={}

   if lSudahAda
      sele TempNDL
      zap
      NDLForm->( dbgotop() )
      NDLForm->( dbSeek(cNDLCode) )
      do while NDLForm->NDLCode == cNDLCode
         sele TempNDL
         append Blank
         repl FGCode   with NDLForm->FGCode
         repl Standard with NDLForm->Standard
         FGMast->( dbSeek(TempNDL->FGCode) )
         repl Desc with FGMast->Desc
         i++
         NDLForm->( dbskip() )
      enddo
      sele TempNDL
      if i < 10
         for j := 1 to 10-i
            dbAppend()
         next i
      endif
      dbgotop()
   else
      select TempNDL
      zap
      for i:=1 to 10
         dbappend()
      next i
      dbgotop()
   endif
   Akolom[1] :="recno()"
   Akolom[2] :="FGCode"
   Akolom[3] :="Desc"
   Akolom[4] :="Standard"
   Apict[1] :="999."
   Apict[2] :="@!"
   Apict[3] :="@!"
   Apict[4] :="@KEZ 99,999.999999"
   Ajudul[1]:="Nomor"
   Ajudul[2]:="Code FG"
   AJudul[3]:="Nama FG"
   Ajudul[4]:="Standard"
   Ajudul[5]:=" "
   C1:=TBColumnNew(Ajudul[1],{||tran(&(Akolom[1]),Apict[1])})
   C2:=TBColumnNew(Ajudul[2],{||tran(&(Akolom[2]),Apict[2])})
   C3:=TBColumnNew(Ajudul[3],{||tran(&(Akolom[3]),Apict[3])})
   C4:=TBColumnNew(Ajudul[4],{||tran(&(Akolom[4]),Apict[4])})
   c5:=TBColumnNew(Ajudul[5])
   B:addcolumn(C1)
   B:addcolumn(C2)
   B:addcolumn(C3)
   B:addcolumn(C4)
   B:addcolumn(C5)
   B:colorSpec:="w/b+,w+/br+,w/r,n/w"
   B:headsep := chr(205)
   B:colsep  := chr(179)
   B:nTop    := 6
   B:nLeft   := 5
   B:nBottom := 17
   B:nRight  := 73
   sbox( 5,4,18,74,"gr+/b+")
   sbox( 20,4,22,74,"gr+/b+")
   @ 21,5 say ' Esc -> Exit   F1 -> Proses   Del-> Delete   Alt+P->Pack'

   keyboard chr(K_RIGHT)+chr(K_ENTER)
   do while.T.
      B:forceStable()
      @ 18,58 say " Status-> "+if (deleted(),"<DEL >","<NORM>")
      X:=slowflash(B)
      do case
         case Kursor(B,X)
         case X=K_ESC
              cScr1:=savescreen()
              cClr1:=setcolor()
              do SBox with 10,20,14,60,"w+/bg+,gr+/br"
              @ 11,30 say "Betul akan dibatalkan?"
              @ 12,21 say repl(chr(196),39)
              @ 13,25    prompt " Ya "
              @ row(),50 prompt "Tidak"
              menu to Pil
              setcolor (cClr1)
              restscreen( 0,0,maxrow(),maxcol(),cScr1)
              if Pil=1
                 exit
              endif
         case X=K_ENTER
              if B:ColPos=1 .or. B:colPos=3
                 tone(700,1)
                 keyboard chr(K_RIGHT)+chr(K_ENTER)
                 loop
              endif
              if B:ColPos=5
                 tone(700,1)
                 keyboard chr(K_CTRL_HOME)+chr(K_DOWN)+chr(K_RIGHT)+chr(K_ENTER)
                 loop
              endif
              FieldsKrg:=Akolom[B:ColPos]
              MEdit:=&FieldsKrg
              setcursor(1)
              @ row(),col() get MEdit pict Apict[B:ColPos] valid AV211(@MEdit,B:ColPos)
              read
              setcursor(0)
              if lastkey()=K_ESC
                 loop
              endif
              repl &FieldsKrg with MEdit
              keyboard chr(K_RIGHT)+chr(K_ENTER)
              B:refreshAll()
         case X=K_F1
                 if lSudahAda
                    NDLForm->( dbSeek(cNDLCode) )
                    do while NDLForm->NDLCode == cNDLCode
                      NDLForm->( dbDelete() )
                      NDLForm->( dbSkip() )
                    enddo
                    NDLForm->(__dbPack() )
                 endif
                 sele TempNDL
                 dbgotop()
                 do while ! eof()
                       nRec := RecNo()
                       if TempNDL->FGCode=space(7)
                          dbskip()
                          loop
                       endif
                       select NDLForm
                       dBappend()
                       NDLForm->NDLCode   := cNDLCode
                       NDLForm->FGCode    := TempNDL->FGCode
                       *NDLForm->Seq       := nRec
                       NDLForm->Standard  := TempNDL->Standard
                       NDLForm->(dbcommit())
                       sele TempNDL
                       dbSkip()
                 enddo
                 tone(700,5)
                 exit
              B:refreshAll()
         case X=K_DEL
             if deleted()
                recall
             else
                delete
             endif
             tone(700,1)
             B:refreshCurrent()
         case X=K_ALT_P
             Hapus()
             B:refreshALL()
      endcase
   enddo
return nil

function NFPrint()
   local cPrint,cFGCode,nStd
   local cNDLCode  := space(7)
   local cNDLCode1 := space(7)
   local cNDLCode2 := space(7)
   local nWidth    := 81
   local nSW       := 0
   local nSW1      := 0
   local nPage     := 0
   local GetList   := {}
   local cClr      := setcolor()
   local cScr      := savescreen()
   set date British
   set score off

   do while .T.
      sbox( 17,40,20,62,"gr+/w+,w+/bg+" )
      @ 17,42 say " Edit List "
      @ 18,41 say " Code NDL : " get cNDLCode1 picture "@!"
      @ 19,41 say "       To : " get cNDLCode2 picture "@!"
      read
      if lastkey() == K_ESC
         exit
      endif
      cPrint := alert ( " Output Device  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "Formula.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)

      select NDLForm
      set filter to NDLForm->NDLCode >= cNDLCode1 .and. NDLForm->NDLCode <= cNDLCode2

      NDLForm->( dbGoTop() )
      do while ! NDLForm-> ( EOF() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
        cNDLCode := NDLForm->NDLCode

        if nSW == 0
             @ prow()+1,1   say CostRef->Desc1
             @ prow()+1,1   say CostRef->Desc2
             @ prow()+1,28  say "DAFTAR FORMULA NOODLE"
             nPage++
             @ prow(),70 say "Page : "
             @ prow(),78 say nPage picture "999"
             *@ prow(),70 say CostRef->Period
             @ prow()+1,1   say repli ( '=',nWidth )
             *                            1         2         3         4         5         6         7         8
             *                  012345678901234567890123456789012345678901234567890123456789012345678901234567890123456
             @ prow()+1,1   say "|     Noodle                   |         Finished Goods        |    Standard    |"
             *                     xxxxxxx xxxxxxxxxxxxxxxxxxxx    xxxxxxx xxxxxxxxxxxxxxxxxxxx    99,999.999999
             @ prow()+1,1   say repli( '=',nWidth )
             nSW := 1
        endif
        @ prow()+1,1 say "|"
        if nSW1 == 0
            @ prow()  ,3  say cNDLCode                    picture "@!"
            NDLMast->( dbSeek(cNDLCode) )
            @ prow()  ,11 say substr(NDLMast->Desc,1,20)  picture "@!"
            nSW1 := 1
        endif
        @ prow()  ,32  say "|"
        @ prow()  ,35  say NDLForm->FGCode                picture "@!"
        FGMast->( dbSeek( NDLForm->FGCode ) )
        @ prow()  ,43  say substr(FGMast->Desc,1,20)      picture "@!"
        @ prow()  ,64  say "|"
        @ prow()  ,67  say NDLForm->Standard              picture "@z 99,999.999999"
        @ prow()  ,81  say "|"
        NDLForm->( dbSkip() )
        if cNDLCode # NDLForm->NDLCode
            nSW1 := 0
        else
            loop
        endif
        if NDLForm->( Eof() )
             exit
        endif
        if prow() >= 55  .or. Formula->( EOF() )
           @ prow()+1,1   say repli( '-',nWidth )
           nSW  := 0
           eject
        endif
      enddo
      @ prow()+1,1   say repli( '-',nWidth )
      eject
      @prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on

      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"Formula.prn")
         fErase ( "Formula.prn" )
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil

function FormulaB()
  local cClr   := setcolor()
  local cScr   := savescreen()
  local cClr1  := setcolor()
  local cScr1  := savescreen()
  local cFGCode,cDesc,I
  local lSudahAda
  local nBatch := 0
  local GetList := {}
  dataDict()
  set score off
  set date British

  do while .T.
    nBatch := 0
    @ 0,0 clea to 24,79
    Formula->( dbGoBottom() )
    Formula->( dbSkip() )
    cFGCode  := Formula->FGCode
    setcolor( "n+/n+" )
    sbox( 0,0,03,78, "gr+/W+,W+/bg+" )
    setcursor(1)
    @ 1,1 say " Finished Goods : " get cFGCode   picture "@!"         when  myMessage ( " Masukkan Code Finished Goods, atau tekan Esc untuk keluar .... " ) valid chkCodeFG ( @cFGCode )
    @ 2,1 say "          Batch : " get nBatch    picture "@z 999,999" when .F.
    read
    if lastkey() == K_ESC
      exit
      setcolor (cClr)
      restscreen  ( 0,0,maxrow(),maxcol(),cScr)
    endif
    FGMast->( dbSeek ( cFGCode ) )
    cDesc  := FGMast->Desc
    nBatch := FGMast->Batch
    @ 1,28 say cDesc  picture "@!"
    @ 2,20 say nBatch picture "@z 999,999"
    lSudahAda := Formula->(dbSeek(cFGCode))
    @ 24,0 clea to 24,79
    if lastkey() == K_ESC
       exit
    endif
    Formula1(cFGCode,nBatch,lSudahAda)
enddo
FPrint()
close all
setcolor (cClr)
restscreen  ( 0,0,maxrow(),maxcol(),cScr )
return nil

func V211(x,y,nBatch)
     local MScreen:=savescreen(), MColor:=setcolor()
     local desc
     local i:=1
     if ! y=2
        return .T.
     endif
     if x == space(7)
        chkCodeRM(@x,@desc)
     endif
     select RMMast
     set order to 1
     dbseek(x)
     if ! found()
       chkCodeRM(@x,@desc)
     endif
     if found()
        x   := RMMast->RMCode
        desc:= RMMast->Desc
     endif
     select TempForm
     restscreen(,,,,MScreen)
     setcolor(MColor)
     if lastkey()==K_ENTER
        repl TempForm->RMCode with x,TempForm->Desc with Desc
        repl TempForm->Batch  with nBatch
        return .T.
     endif
return .F.

function Formula1(cFGCode,nBatch,lSudahAda)
   local cClr1  := setcolor()
   local cScr1  := savescreen()
   local cClr2  := setcolor()
   local cScr2  := savescreen()
   local cCode,cDesc
   local FieldSkrg,MEdit
   local B:=TBrowseDB(),Pil,X,nRec
   local i := 0
   local j := 0
   local C1,C2,C3,C4,C5,c6
   local Akolom[5], APict[5], Ajudul[6]
   local GetList:={}

   if lSudahAda
      sele TempForm
      zap
      Formula->( dbgotop() )
      Formula->( dbSeek(cFGCode) )
      do while Formula->FGCode == cFGCode
         sele TempForm
         append Blank
         repl Batch     with nBatch
         repl RMCode    with Formula->RMCode
         repl StandardA with Formula->StandardA
         repl StandardB with Formula->StandardB
         RMMast->( dbSeek(TempForm->RMCode) )
         repl Desc with RMMast->Desc
         i++
         Formula->( dbskip() )
      enddo
      sele TempForm
      if i < 30
         for j := 1 to 30-i
            dbAppend()
         next i
      endif
      dbgotop()
   else
      select TempForm
      zap
      for i:=1 to 30
         dbappend()
      next i
      dbgotop()
   endif
   Akolom[1] :="recno()"
   Akolom[2] :="RMCode"
   Akolom[3] :="Desc"
   Akolom[4] :="StandardA"
   Akolom[5] :="StandardB"
   Apict[1] :="999."
   Apict[2] :="@!"
   Apict[3] :="@!"
   Apict[4] :="@KEZ 99,999.9999999999"
   Apict[5] :="@KEZ 99,999.9999999999"
   Ajudul[1]:="Nomor"
   Ajudul[2]:="Code RM"
   AJudul[3]:="Nama RM"
   Ajudul[4]:="StandardA"
   Ajudul[5]:="StandardB"
   Ajudul[6]:=" "
   C1:=TBColumnNew(Ajudul[1],{||tran(&(Akolom[1]),Apict[1])})
   C2:=TBColumnNew(Ajudul[2],{||tran(&(Akolom[2]),Apict[2])})
   C3:=TBColumnNew(Ajudul[3],{||tran(&(Akolom[3]),Apict[3])})
   C4:=TBColumnNew(Ajudul[4],{||tran(&(Akolom[4]),Apict[4])})
   C5:=TBColumnNew(Ajudul[5],{||tran(&(Akolom[5]),Apict[5])})
   C6:=TBColumnNew(Ajudul[6])
   B:addcolumn(C1)
   B:addcolumn(C2)
   B:addcolumn(C3)
   B:addcolumn(C4)
   B:addcolumn(C5)
   B:addcolumn(C6)
   B:colorSpec:="w/b+,w+/br+,w/r,n/w"
   B:headsep := chr(205)
   B:colsep  := chr(179)
   B:nTop    := 6
   B:nLeft   := 1
   B:nBottom := 19
   B:nRight  := 77
   sbox( 5,0,20,78,"gr+/b+")
   sbox( 21,0,23,78,"gr+/b+")
   @ 22,1 say 'Esc -> Exit  F1 -> Proses  Del-> Delete  Alt+P->Pack'

   keyboard chr(K_RIGHT)+chr(K_ENTER)
   do while.T.
      B:forceStable()
      @ 22,58 say " Status-> "+if (deleted(),"<DEL >","<NORM>")
      X:=slowflash(B)
      do case
         case Kursor(B,X)
         case X=K_ESC
              cScr1:=savescreen()
              cClr1:=setcolor()
              do SBox with 10,20,14,60,"w+/bg+,gr+/br"
              @ 11,30 say "Betul akan dibatalkan?"
              @ 12,21 say repl(chr(196),39)
              @ 13,25    prompt " Ya "
              @ row(),50 prompt "Tidak"
              menu to Pil
              setcolor (cClr1)
              restscreen( 0,0,maxrow(),maxcol(),cScr1)
              if Pil=1
                 exit
              endif
         case X=K_ENTER
              if B:ColPos=1 .or. B:colPos=3
                 tone(700,1)
                 keyboard chr(K_RIGHT)+chr(K_ENTER)
                 loop
              endif
              if B:ColPos=6
                 tone(700,1)
                 keyboard chr(K_CTRL_HOME)+chr(K_DOWN)+chr(K_RIGHT)+chr(K_ENTER)
                 loop
              endif
              FieldsKrg:=Akolom[B:ColPos]
              MEdit:=&FieldsKrg
              setcursor(1)
              @ row(),col() get MEdit pict Apict[B:ColPos] valid V211(@MEdit,B:ColPos,nBatch)
              read
              setcursor(0)
              if lastkey()=K_ESC
                 loop
              endif
              repl &FieldsKrg with MEdit
              keyboard chr(K_RIGHT)+chr(K_ENTER)
              B:refreshAll()
         case X=K_F1
                 if lSudahAda
                    Formula->( dbSeek(cFGCode) )
                    do while Formula->FGCode == cFGCode
                      Formula->( dbDelete() )
                      Formula->( dbSkip() )
                    enddo
                    Formula->(__dbPack() )
                 endif
                 sele TempForm
                 dbgotop()
                 do while ! eof()
                       nRec := RecNo()
                       if TempForm->RMCode=space(7)
                          dbskip()
                          loop
                       endif
                       select Formula
                       dBappend()
                       Formula->FGCode    := cFGCode
                       Formula->RMCode    := TempForm->RMCode
                       Formula->Seq       := nRec
                       Formula->StandardA := TempForm->StandardA
                       Formula->StandardB := TempForm->StandardB
                       Formula->(dbcommit())
                       sele TempForm
                       dbSkip()
                 enddo
                 tone(700,5)
                 exit
              B:refreshAll()
         case X=K_DEL
             if deleted()
                recall
             else
                delete
             endif
             tone(700,1)
             B:refreshCurrent()
         case X=K_ALT_P
             Hapus()
             B:refreshALL()
      endcase
   enddo
return nil

Function Hapus()
   local cClr   := setcolor()
   local cScr   := savescreen()
   sbox(10,20,12,60,"w*+/bg+")
   @ 11,25 say " Tunggu ... , sedang PACK ! "
   pack
   tone(100,1)
   tone(200,1)
   tone(300,1)
   tone(400,1)
   tone(500,1)
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
return Nil


function FPrint()
   local cPrint,cRMCode,nBatch
   local cFGCode  := space(7)
   local cFGCode1 := space(7)
   local cFGCode2 := space(7)
   local nWidth   := 70
   local nSW      := 0
   local nSW1     := 0
   local nPage    := 0
   local nomer    := 0
   local GetList  := {}
   local cClr     := setcolor()
   local cScr     := savescreen()
   set date British
   set score off

   do while .T.
      sbox( 17,40,20,62,"gr+/w+,w+/bg+" )
      @ 17,42 say " Edit List "
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
        set print to "Formula.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)

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
        FGMast->( dbSeek(cFGCode) )
        nBatch := FGMast->Batch
        if nSW == 0
           if nSW1 == 0
             @ prow()+1,1   say CostRef->Desc1
             @ prow()+1,1   say CostRef->Desc2
             @ prow()+1,25  say "DAFTAR FORMULA"
             nPage++
             @ prow(),59 say "Page : "
             @ prow(),67 say nPage picture "999"
             nSW1 := 1
           endif
             @ prow()+1,1   say "Product : "
             FGMast->( dbSeek( cFGCode ) )
             @ prow()  ,12  say FGMast->FGCode
             @ prow()  ,20  say FGMast->Desc
             @ prow()  ,60  say CostRef->Period
             @ prow()+1,1   say repli ( '=',nWidth )
             *                            1         2         3         4         5         6         7
             *                  012345678901234567890123456789012345678901234567890123456789012345678901
             @ prow()+1,1   say "|            Materials             |   Standard A   |   Standard B   |"
             *                     99. xxxxxxx xxxxxxxxxxxxxxxxxxxx    99,999.999999    99,999.999999
             @ prow()+1,1   say repli( '=',nWidth )
             nSW := 1
        endif

        if prow() <= 55
           cFGCode := Formula->FGCode
           do while Formula->FGCode == cFGCode .and. prow() <= 55
              cRMCode := Formula->RMCode
              RMMast->( dbSeek( cRMCode ) )
              Nomer++

              @ prow()+1,3   say Nomer picture "99"
              @ prow()  ,5   say "."
              @ prow()  ,7   say Formula->RMCode             picture "@!"
              @ prow()  ,15  say substr(RMMast->Desc,1,20)   picture "@!"
              @ prow()  ,39  say Formula->StandardA          picture "@z 99,999.999999"
              @ prow()  ,56  say Formula->StandardB          picture "@z 99,999.999999"
              Formula->( dbSkip() )
           enddo
           if cFGCode # Formula->FGCode
              @ prow()+1,1   say repli( '-',nWidth )
              @ prow()+1,4   say " Standard Per Adukan : "
              @ prow()  ,pcol()  say nBatch  picture "@z 999,999"
              @ prow()+1,1 say " "
              @ prow()+1,1 say " "
              nSW   := 0
              Nomer := 0
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
      @prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on

      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"Formula.prn")
         fErase ( "Formula.prn" )
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil

function Fact()
   local cCode,cDesc
   local c01 := space(2)
   local c02 := space(2)
   local c03 := space(2)
   local c04 := space(2)
   local c05 := space(2)
   local c06 := space(2)
   local c07 := space(2)
   local c08 := space(2)
   local c09 := space(2)
   local c10 := space(2)
   local c99 := space(2)
   local lSudahAda,lPerluPack := .F.
   local GetList  :={}
   local cClr:=setcolor()
   local cScr:=savescreen()
   set score off
   dataDict()
   do while .T.
      sbox(6,10,19,70,"gr+/b+,w+/bg+")
      @ 7,27 say "Maintenance Factory"
      @ 8,11 to 8,69
      Factory->( dbGoBottom() )
      Factory->( dbSkip() )
      cCode := Factory->Code
      cDesc := Factory->Desc
      store space(2) to c01,c02,c03,c04,c05,c06,c07,c08,c09,c10
      @ 10,12 say  "  Factory Code : " get cCode     picture "@!"    when myMessage ( " Masukkan Code Factory, atau tekan Esc untuk keluar ..." )
      @ 11,12 say  "  Description  : " get cDesc     picture "@!"    when .F.
      @ 13,12 say  "   #01st Area  : " get c01       picture "@!"    when .F.
      @ 14,12 say  "   #02nd Area  : " get c02       picture "@!"    when .F.
      @ 15,12 say  "   #03rd Area  : " get c03       picture "@!"    when .F.
      @ 16,12 say  "   #04th Area  : " get c04       picture "@!"    when .F.
      @ 17,12 say  "   #05th Area  : " get c05       picture "@!"    when .F.
      @ 13,32 say  "   #06th Area  : " get c06       picture "@!"    when .F.
      @ 14,32 say  "   #07th Area  : " get c07       picture "@!"    when .F.
      @ 15,32 say  "   #08th Area  : " get c08       picture "@!"    when .F.
      @ 16,32 say  "   #09th Area  : " get c09       picture "@!"    when .F.
      @ 17,32 say  "   #10th Area  : " get c10       picture "@!"    when .F.

      read
      if lastkey() == K_ESC

         exit
      else
         if cCode == Factory->Code
            beep()
            alert( 'Code Factory Tidak Boleh Kosong .....')
            loop
         endif
         if ( lSudahAda := Factory-> ( dbSeek ( cCode ) ) )
              cDesc  := Factory->Desc
              c01    := substr(Factory->Area,01,2)
              c02    := substr(Factory->Area,04,2)
              c03    := substr(Factory->Area,07,2)
              c04    := substr(Factory->Area,10,2)
              c05    := substr(Factory->Area,13,2)
              c06    := substr(Factory->Area,16,2)
              c07    := substr(Factory->Area,19,2)
              c08    := substr(Factory->Area,22,2)
              c09    := substr(Factory->Area,25,2)
              c10    := substr(Factory->Area,28,2)
         endif
         @ 11,12 say  "  Description  : " get cDesc     picture "@!"    when myMessage ( " Masukkan Description, atau tekan Esc untuk keluar ..." )
         @ 13,12 say  "   #01st Area  : " get c01       picture "@!"    when myMessage ( " Masukkan #01st Area, atau tekan Esc untuk keluar ..." )
         @ 14,12 say  "   #02nd Area  : " get c02       picture "@!"    when myMessage ( " Masukkan #02nd Area, atau tekan Esc untuk keluar ..." )
         @ 15,12 say  "   #03rd Area  : " get c03       picture "@!"    when myMessage ( " Masukkan #03rd Area, atau tekan Esc untuk keluar ..." )
         @ 16,12 say  "   #04th Area  : " get c04       picture "@!"    when myMessage ( " Masukkan #04th Area, atau tekan Esc untuk keluar ..." )
         @ 17,12 say  "   #05th Area  : " get c05       picture "@!"    when myMessage ( " Masukkan #05th Area, atau tekan Esc untuk keluar ..." )
         @ 13,32 say  "   #06th Area  : " get c06       picture "@!"    when myMessage ( " Masukkan #06th Area, atau tekan Esc untuk keluar ..." )
         @ 14,32 say  "   #07th Area  : " get c07       picture "@!"    when myMessage ( " Masukkan #07th Area, atau tekan Esc untuk keluar ..." )
         @ 15,32 say  "   #08th Area  : " get c08       picture "@!"    when myMessage ( " Masukkan #08th Area, atau tekan Esc untuk keluar ..." )
         @ 16,32 say  "   #09th Area  : " get c09       picture "@!"    when myMessage ( " Masukkan #09th Area, atau tekan Esc untuk keluar ..." )
         @ 17,32 say  "   #10th Area  : " get c10       picture "@!"    when myMessage ( " Masukkan #10th Area, atau tekan Esc untuk keluar ..." )

         if lSudahAda
           if Factory->( deleted() )
              clear gets
              if alert ( "Data sudah dihapus !!", { " Batal ", " Kembali "} ) == 1
                  Factory->( dbRecall() )
              endif
              loop
           endif
           if alert ( " Menu Transaksi ", { " Edit ", " Hapus " } ) == 2
              clear gets
              if alert ( " Benar akan dihapus ? ", { " Yes ", " No " } ) == 1
                 Factory->( dbDelete() )
                 lPerluPACK := .T.
              endif
              loop
           endif
         endif
         read
         if ( ! lastkey() == K_ESC ) .and. ( alert ( " Data Sudah Benar ? " , { " Yes ", " No " } ) == 1 )
           c99 := c01+' '+c02+' '+c03+' '+c04+' '+c05+' '+c06+' '+c07+' '+c08+' '+c09+' '+c10+' '
           if ! lSudahAda
              Factory->( dbAppend() )
              Factory->Code := cCode
           endif
           Factory->Desc := cDesc
           Factory->Area := c99
           Factory->( dbCommit() )
           store space(2) to c01,c02,c03,c04,c05,c06,c07,c08,c09,c10
         endif
      endif
   enddo
   if lPerluPACK
      Factory->( __dbPack() )
   endif
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
   *NDLPrint()
   close all
return nil