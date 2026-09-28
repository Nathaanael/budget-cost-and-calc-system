#include "Stock.CH"

function dataDict()
  use RMMast new
  if ! file ( "RMMast1" + ordBagExt() ) .or. ! file ( "RMMast2" + ordBagExt() ) .or. ! file ( "RMMast3" + ordBagExt() )
     inde on RMMast->RMCode to RMMast1
     inde on RMMast->Desc   to RMMast2
     inde on RMMast->id+RMMAst->RMCode to RMMast3
  else
     set inde to RMMast1,RMMast2,RMMast3
  endif
  use FGMast new
  if ! file ( "FGMast1" + ordBagExt() ) .or. ! file ( "FGMast2" + ordBagExt() ) .or. ! file ( "FGMast3" + ordBagExt() )
     inde on FGMast->FGCode to FGMast1
     inde on FGMast->Desc   to FGMast2
     inde on FGMast->PrType1+FGMast->Prtype2+FGMast->FGCode to FGMast3
  else
     set inde to FGMast1,FGMast2,FGMast3
  endif
  use CostRef new
  if ! file ( "CostRef1" + ordBagExt() )
     inde on CostRef->Code to CostRef1
  else
     set inde to CostRef1
  endif
  use YC06 new
  if ! file ( "YC061" + ordBagExt() )
     inde on yc06->kode to yc061
  else
     set inde to yc061
  endif
  use MCDSAP new
  if ! file ( "MCDSAP1" + ordBagExt() )
     inde on mcdsap->code1+mcdsap->code to mcdsap1
  else
     set inde to mcdsap1
  endif

  use yc061203 new
  use yc061204 new
  use yc061205 new
  use RMQty new
  use RMAmt new
  use RMValue new
  use SLSValue new
  use TempForm new
  use TempNDL new
  use Formula new
  COPY TO FRML.DBF
  COPY TO FRML1.DBF
  if ! file ( "Formula1" + ordBagExt() ) .or. ! file ( "Formula2" + ordBagExt() )
     inde on Formula->FGCode+Formula->RMCode to Formula1
     inde on Formula->RMCode                 to Formula2
  else
     set inde to Formula1,Formula2
  endif
  use Frml new
  if ! file ( "Frml1" + ordBagExt() ) .or. ! file ( "Frml2" + ordBagExt() )
     inde on Frml->FGCode+Frml->RMCode to Frml1
     inde on Frml->RMCode              to Frml2
  else
     set inde to Frml1,Frml2
  endif
  use Frml1 new
  if ! file ( "Frml11" + ordBagExt() ) .or. ! file ( "Frml12" + ordBagExt() )
     inde on Frml1->FGCode+Frml1->RMCode to Frml11
     inde on Frml1->RMCode               to Frml12
  else
     set inde to Frml11,Frml12
  endif

  use NDLMast new
  if ! file ( "NDLMast1" + ordBagExt() ) .or. ! file ( "NDLMast2" + ordBagExt() )
     inde on NDLMast->NDLCode to NDLMast1
     inde on NDLMast->Desc    to NDLMast2
  else
     set inde to NDLMast1,NDLMast2
  endif
  use NDLForm new
  if ! file ( "NDLForm1" + ordBagExt() )
     inde on NDLForm->NDLCode+NDLForm->FGCode to NDLForm1
  else
     set inde to NDLForm1
  endif
  use Area new
  if ! file ( "Area1" + ordBagExt() )
     inde on Area->Code  to Area1
  else
     set inde to Area1
  endif
  use TypeMast new
  if ! file ( "Type1" + ordBagExt() )
     inde on TypeMast->PrType1  to Type1
  else
     set inde to Type1
  endif
  use VolNDL new
  if ! file ( "VolNDL1" + ordBagExt() ) .or. ! file ( "VolNDL2" + ordBagExt() )
     inde on VolNDL->Code+VolNDL->NDLCode to VolNDL1
     inde on VolNDL->Code+VolNDL->NDLCode to VolNDL2
  else
     set inde to VolNDL1,VolNDL2
  endif
  use Volume new
  if ! file ( "Volume1" + ordBagExt() ) .or. ! file ( "Volume2" + ordBagExt() ) .or. ! file ( "Volume3" + ordBagExt() )
     inde on Volume->FGCode+Volume->Code                    to Volume1
     inde on Volume->PrType1+Volume->PrType2+Volume->FGCode to Volume2
     inde on Volume->FGCode                                 to Volume3
  else
     set inde to Volume1,Volume2,Volume3
  endif

  use Synonim new
  if ! file ( "Synonim1" + ordBagExt() )
     inde on Synonim->RMCode to Synonim1
  else
     set inde to Synonim1
  endif
  use FGMast1 new
  if ! file ( "FGMast11" + ordBagExt() ) .or. ! file ( "FGMast21" + ordBagExt() )
     inde on FGMast1->FGCode to FGMast11
     inde on FGMast1->Desc   to FGMast21
  else
     set inde to FGMast11,FGMast21
  endif
  use Factory new
  if ! file ( "Factory1" + ordBagExt() )
     inde on Factory->Code  to Factory1
  else
     set inde to Factory1
  endif
  
return nil

function Index()
  local cClr := setcolor()
  local cScr := savescreen()
  do while .T.
    if alert ( " Reindex : ",{"Yes","No"} ) == 1
       sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
       setcolor( "gr+*/bg,w+/br+" )
       @ 21,25  say " Wait for a minutes ..... "
       fErase( "RMMast1"  + ordBagExt() )
       fErase( "RMMast2"  + ordBagExt() )
       fErase( "RMMast3"  + ordBagExt() )
       fErase( "NDLMast1" + ordBagExt() )
       fErase( "NDLMast2" + ordBagExt() )
       fErase( "FGMast1"  + ordBagExt() )
       fErase( "FGMast2"  + ordBagExt() )
       fErase( "FGMast3"  + ordBagExt() )
       fErase( "Formula1" + ordBagExt() )
       fErase( "Formula2" + ordBagExt() )
       fErase( "Frml1"    + ordBagExt() )
       fErase( "Frml2"    + ordBagExt() )
       fErase( "Frml11"    + ordBagExt() )
       fErase( "Frml12"    + ordBagExt() )
       fErase( "CostRef1" + ordBagExt() )
       fErase( "NDLForm1" + ordBagExt() )
       fErase( "Area1"    + ordBagExt() )
       fErase( "VolNDL1"  + ordBagExt() )
       fErase( "VolNDL2"  + ordBagExt() )
       fErase( "Volume1"  + ordBagExt() )
       fErase( "Volume2"  + ordBagExt() )
       fErase( "Volume3"  + ordBagExt() )
       fErase( "Factory1" + ordBagExt() )
       fErase( "Type1"    + ordBagExt() )
       fErase( "Synonim1" + ordBagExt() )
       fErase( "FGMast11" + ordBagExt() )
       fErase( "FGMast21" + ordBagExt() )
       fErase( "YC061"    + ordBagExt() )
       fErase( "mcdsap1"  + ordBagExt() )
       dataDict()
       close all
    endif
    exit
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function myMessage ( cMessage )
    local nRow := row()
    local nCol := col()

    @ 23,2 say " "+ padR( cMessage, 75 ) color "w+/bg+"
    setpos (nRow,nCol)
return .T.

function  Beep()
    tone ( 400, 2 )
    tone ( 200, 2 )

return nil

proc Rkey
   keyboard chr(27)+chr(24)+chr(13)
return

proc Lkey
   keyboard chr(27)+chr(5)+chr(13)
return

proc Rkey1
   keyboard chr(27)+chr(24)
return

proc Lkey1
   keyboard chr(27)+chr(5)
return


function sbox(t,l,b,r,clr)
  local v,h,i
  v=savescreen(t+1,r+1,b+1,r+2)
  h=savescreen(b+1,l+2,b+1,r+2)

  for i=2 to len(h) step 2
    h = stuff(h,i,1,chr(7))
  next

  for i = 2 to len(v) step 2
    v = stuff(v,i,1,chr(7))
  next

  set colo to (clr)
  @ t,l clear to b,r
  @ t,l to b,r

  restscreen(t+1,r+1,b+1,r+2,v)
  restscreen(b+1,l+2,b+1,r+2,h)
return nil

function Kursor(t,k)
   do case
      case k == K_UP
           t : up()
      case k == K_DOWN
           t : down()
      case k == K_LEFT
           t : left()
      case k == K_RIGHT
           t : right()
      case k == K_CTRL_LEFT
           t : panLeft()
      case k == K_CTRL_RIGHT
           t : panRight()
      case k == K_PGUP
           t : pageup()
      case k == K_PGDN
           t : pagedown()
      case k == K_CTRL_PGUP
           t : goTop()
      case k == K_CTRL_PGDN
           t : goBottom()
      case k == K_HOME
           t : home()
      case k == K_END
           t : end()
      case k == K_CTRL_HOME
           t : panHome()
      case k == K_CTRL_END
           t : panEnd()
      case k == K_TAB
           t : panRight()
      case k == K_SH_TAB
           t : panLeft()
      otherwise
           return .F.
   endcase
return .T.

function SlowFlash(Browse)
   local key
   do while .T.
      browse:hilite()
      if (key:=inkey(1)) # 0
         exit
      endif
      browse:dehilite()
      if (key:=inkey(0.5)) # 0
         exit
      endif
   enddo
return key

function chkCodeFG ( cCode,cDesc )
   local lValid := .F.
   local nRecno := FGMast->( recno() )
   FGMast->( dbSetOrder(1) )
   if FGMast->( dbSeek( cCode ) )
     lValid  := .T.
   else
     beep()
     alert ( " Code Finished Goods Tidak Ada !! ...Pilih yang Benar !" )
     tblCodeFG ( @cCode, @cDesc )
   endif
     FGMast->( dbGoTo( nRecno ) )

return lValid

function tblCodeFG ( cFGCode ,cDesc )
  local nRecno := FGMast->( recNo () )
  local cClr   := setcolor() &&("bg+/b+")
  local cScr   := savescreen () && 6,28,18,74 )
  local bF2,nRow,nCol,X
  local Tabel := TBrowseDB()
  local GetList := {}
  local aKolom[2],aPict[2]
  sele FGMast
  go top

  aKolom[1] := "FGMast->Code"
  aKolom[2] := "FGMast->Desc"
  aPict[1]  := "@!"
  aPict[2]  := "@!"

  sbox(20,30,23,74,"gr+/r+")
  @ 21 ,31 say "   F1->Cari Code    ENTER->Pilih "
  @ 22 ,31 say "   F2->Cari Nama    Esc->Quit    "

  Tabel:Addcolumn(TBColumnNew("Code",{||tran(FGMast->FGCode,aPict[1])}))
  Tabel:Addcolumn(TBColumnNew("Description",{||tran(FGMast->Desc,aPict[2])}))
  Tabel:HeadSep:=chr(205)
  Tabel:ColSep:=chr(179)
  Tabel:colorSpec:="gr+/r+"
  Tabel:Autolite:= .F.
  Tabel:nTop:=6
  Tabel:nLeft:=31
  Tabel:nBottom:=17
  Tabel:nRight:=73
  sbox( 5,30,18,74,"gr+/r+" )
  Setcursor(0)

  do while .T.
     Tabel:ForceStable()
     nRow := Row()
     nCol := Col()
     DevPos(nRow,nCol)
     x:=SlowFlash(Tabel)

     do case
        case kursor(Tabel,X)
        case x == K_ESC
            exit
        case x == K_F1
           Tabel:gotop()
           Tabel:forceStable()
           CariFGCode()
           Tabel:refreshAll()
        case x == K_F2
           Tabel:gotop()
           Tabel:forceStable()
           CariNamaFG()
           Tabel:refreshAll()
        case x == K_ENTER
           cFGCode := FGMast->FGCode
           cDesc   := FGmast->Desc
           exit
      endcase
   enddo
   setcursor(1)
   FGMast->( dbGoTo( nRecno ) )
   FGMast->( dbSetOrder(1) )
   setcolor ( cClr )
   restscreen( 0,0,maxrow(),maxcol(), cScr )

return nil

function CariFGCode()
   local cClr     := setcolor()
   local cScr     := savescreen()
   local cFGCode  := space(7)
   local x := 1
   local getList := {}
   setcursor(1)
   sbox(10,37,12,67,"w+/r")
   @ 11,39 say " Code FG : " get cFGCode pict "@!"
   read
   setcursor(0)
   FGMast->( dbSetOrder(1) )
   FGMast->( dbSeek(cFGCode) )
   do while ! found() .and. x<=len(cFGCode)
      FGMast->( dbSeek(left(cFGCode,len(cFGCode)-x++)))
   enddo

   if ! found()
     sbox(15,20,17,60,"W+/b")
     @ 16,25 say " Code Finished Goods tidak ada "
     tone(700,10)
     inkey(5)
   else
     tone(700,1)
     tone(300,1)
   endif

   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
return Nil

function CariNamaFG()
   local cClr     := setcolor()
   local cScr     := savescreen()
   local cNamaFG  := space(20)
   local x := 1
   local getList := {}
   setcursor(1)
   sbox(10,33,12,68,"w+/r")
   @ 11,34 say " Nama FG : " get cnamaFG pict "@!"
   read
   setcursor(0)
   FGMast->( dbSetOrder(2) )
   FGMast->( dbSeek(cNamaFG) )
   do while ! found() .and. x<=len(cnamaFG)
      FGMast->( dbSeek(left(cNamaFG,len(cNamaFG)-x++)))
   enddo

   if ! found()
     sbox(15,20,17,60,"W+/b")
     @ 16,25 say " Nama Finished Goods tidak ada "
     tone(700,10)
     inkey(5)
   else
     tone(700,1)
     tone(300,1)
   endif
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
return Nil

function chkCodeRM ( cRMCode,cDesc )
   local lValid := .F.
   local nRecno := RMMast->( recno() )
   RMMast->( dbSetOrder(1) )
   if RMMast->( dbSeek( cRMCode ) )
     lValid  := .T.
   else
     beep()
     alert ( " Code Raw Material Tidak Ada !! ...Pilih yang Benar !" )
     tblCodeRM ( @cRMCode, @cDesc )
   endif
     RMMast->( dbGoTo( nRecno ) )

return lValid

function tblCodeRM ( cRMCode ,cDesc )
  local nRecno := RMMast->( recNo () )
  local cClr   := setcolor() &&("bg+/b+")
  local cScr   := savescreen () && 6,28,18,74 )
  local bF2,nRow,nCol,X
  local Tabel := TBrowseDB()
  local GetList := {}
  local aKolom[2],aPict[2]
  sele RMMast
  go top

  aKolom[1] := "RMMast->Code"
  aKolom[2] := "RMMast->Desc"
  aPict[1]  := "@!"
  aPict[2]  := "@!"

  sbox(20,30,23,74,"gr+/r+")
  @ 21 ,31 say "   F1->Cari Code    ENTER->Pilih "
  @ 22 ,31 say "   F2->Cari Nama    Esc->Quit    "
  *sbox(21,30,23,74,"gr+/r+")
  *@ 22 ,31 say "  F1->Cari Code  ENTER->Pilih  Esc->Keluar"

  Tabel:Addcolumn(TBColumnNew("Code",{||tran(RMMast->RMCode,aPict[1])}))
  Tabel:Addcolumn(TBColumnNew("Description",{||tran(RMMast->Desc,aPict[2])}))
  Tabel:HeadSep:=chr(205)
  Tabel:ColSep:=chr(179)
  Tabel:colorSpec:="bg+/b+,gr+/r+"
  Tabel:Autolite:= .F.
  Tabel:nTop:=6
  Tabel:nLeft:=31
  Tabel:nBottom:=17
  Tabel:nRight:=73
  sbox( 5,30,18,74,"W+/b+" )
  Setcursor(0)

  do while .T.
     Tabel:ForceStable()
     nRow := Row()
     nCol := Col()
     DevPos(nRow,nCol)
     x:=SlowFlash(Tabel)

     do case
        case kursor(Tabel,X)
        case x == K_ESC
            exit
        case x == K_F1
           Tabel:gotop()
           Tabel:forceStable()
           CariRMCode()
           Tabel:refreshAll()
        case x == K_F2
           Tabel:gotop()
           Tabel:forceStable()
           CariNamaRM()
           Tabel:refreshAll()
        case x == K_ENTER
           cRMCode := RMMast->RMCode
           cDesc   := RMMast->Desc
           exit
      endcase
   enddo
   setcursor(1)
   RMMast->( dbGoTo( nRecno ) )
   setcolor ( cClr )
   restscreen( 0,0,maxrow(),maxcol(), cScr )

return nil

function CariRMCode()
   local cClr     := setcolor()
   local cScr     := savescreen()
   local cRMCode  := space(7)
   local x := 1
   local getList := {}
   setcursor(1)
   sbox(10,35,12,65,"w+/r")
   @ 11,38 say " Code RM : " get cRMCode pict "@!"
   read
   setcursor(0)
   RMMast->( dbSetOrder(1) )
   RMMast->( dbSeek(cRMCode) )
   do while ! found() .and. x<=len(cRMCode)
      RMMast->( dbSeek(left(cRmCode,len(cRMCode)-x++)))
   enddo

   if ! found()
     sbox(15,20,17,60,"W+/b")
     @ 16,25 say " Code Raw Material tidak ada "
     tone(700,10)
     inkey(5)
   else
     tone(700,1)
     tone(300,1)
   endif

   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
return Nil

function CariNamaRM()
   local cClr     := setcolor()
   local cScr     := savescreen()
   local cNamaRM  := space(20)
   local x := 1
   local getList := {}
   setcursor(1)
   sbox(10,33,12,68,"w+/r")
   @ 11,34 say " Nama RM : " get cnamaRM pict "@!"
   read
   setcursor(0)
   RMMast->( dbSetOrder(2) )
   RMMast->( dbSeek(cNamaRM) )
   do while ! found() .and. x<=len(cnamaRM)
      RMMast->( dbSeek(left(cNamaRM,len(cNamaRM)-x++)))
   enddo

   if ! found()
     sbox(15,20,17,60,"W+/b")
     @ 16,25 say " Nama Raw Material tidak ada "
     tone(700,10)
     inkey(5)
   else
     tone(700,1)
     tone(300,1)
   endif
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
return Nil

function chkCodeNDL ( cNDLCode,cDesc )
   local lValid := .F.
   local nRecno := NDLMast->( recno() )
   NDLMast->( dbSetOrder(1) )
   if NDLMast->( dbSeek( cNDLCode ) )
     lValid  := .T.
   else
     beep()
     alert ( " Code Noodle Tidak Ada !! ...Pilih yang Benar !" )
     tblCodeNDL ( @cNDLCode, @cDesc )
   endif
     NdlMast->( dbGoTo( nRecno ) )

return lValid

function tblCodeNDL ( cNDLCode ,cDesc )
  local nRecno := NDLMast->( recNo () )
  local cClr   := setcolor() &&("bg+/b+")
  local cScr   := savescreen () && 6,28,18,74 )
  local bF2,nRow,nCol,X
  local Tabel := TBrowseDB()
  local GetList := {}
  local aKolom[2],aPict[2]
  sele NDlMast
  go top

  aKolom[1] := "NDLMast->NDLCode"
  aKolom[2] := "NDLMast->Desc"
  aPict[1]  := "@!"
  aPict[2]  := "@!"

  sbox(21,20,23,74,"gr+/r+")
  @ 22 ,21 say "F1->Cari Code F2->Cari Nama ENTER->Pilih Esc->Quit"

  Tabel:Addcolumn(TBColumnNew("Code",{||tran(NDLMast->NDLCode,aPict[1])}))
  Tabel:Addcolumn(TBColumnNew("Description",{||tran(NDLMast->Desc,aPict[2])}))
  Tabel:HeadSep:=chr(205)
  Tabel:ColSep:=chr(179)
  Tabel:colorSpec:="gr+/r+"
  Tabel:Autolite:= .F.
  Tabel:nTop:=7
  Tabel:nLeft:=21
  Tabel:nBottom:=18
  Tabel:nRight:=73
  sbox( 6,20,19,74,"gr+/r+" )
  Setcursor(0)

  do while .T.
     Tabel:ForceStable()
     nRow := Row()
     nCol := Col()
     DevPos(nRow,nCol)
     x:=SlowFlash(Tabel)

     do case
        case kursor(Tabel,X)
        case x == K_ESC
            exit
        case x == K_F1
           Tabel:gotop()
           Tabel:forceStable()
           CariNDLCode()
           Tabel:refreshAll()
        case x == K_F2
           Tabel:gotop()
           Tabel:forceStable()
           CariNDLNama()
           Tabel:refreshAll()
        case x == K_ENTER
           cNDLCode := NDLMast->NDlCode
           cDesc    := NDLMast->Desc
           exit
      endcase
   enddo
   setcursor(1)
   NDLMast->( dbGoTo( nRecno ) )
   setcolor ( cClr )
   restscreen( 0,0,maxrow(),maxcol(), cScr )

return nil

function CariNDLCode()
   local cClr     := setcolor()
   local cScr     := savescreen()
   local cNDLCode := space(7)
   local x := 1
   local getList := {}
   setcursor(1)
   sbox(10,35,12,65,"w+/r")
   @ 11,38 say " Code NDL : " get cNDLCode pict "@!"
   read
   setcursor(0)
   NDLMast->( dbSetOrder(1) )
   NDLMast->( dbSeek(cNDLCode) )
   do while ! found() .and. x<=len(cNDLCode)
      NDLMast->( dbSeek(left(cNDLCode,len(cNDLCode)-x++)))
   enddo

   if ! found()
     sbox(15,20,17,60,"W+/b")
     @ 16,25 say " Code Noodle tidak ada "
     tone(700,10)
     inkey(5)
   else
     tone(700,1)
     tone(300,1)
   endif

   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
return Nil

function CariNDLNama()
   local cClr     := setcolor()
   local cScr     := savescreen()
   local cNamaNDL := space(20)
   local x := 1
   local getList := {}
   setcursor(1)
   sbox(10,33,12,68,"w+/r")
   @ 11,34 say " Nama NDL : " get cnamaNDL pict "@!"
   read
   setcursor(0)
   NDLMast->( dbSetOrder(2) )
   NDLMast->( dbSeek(cNamaNDL) )
   do while ! found() .and. x<=len(cnamaNDL)
      NDLMast->( dbSeek(left(cNamaNDL,len(cNamaNDL)-x++)))
   enddo

   if ! found()
     sbox(15,20,17,60,"W+/b")
     @ 16,25 say " Nama NOODLE tidak ada "
     tone(700,10)
     inkey(5)
   else
     tone(700,1)
     tone(300,1)
   endif
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
return Nil

function chkArea ( cCode,cDesc )
   local lValid := .F.
   local nRecno := Area->( recno() )
   Area->( dbSetOrder(1) )
   if Area->( dbSeek( cCode ) )
     lValid  := .T.
   else
     beep()
     alert ( " Code Area  Tidak Ada !! ...Pilih yang Benar !" )
     tblArea ( @cCode, @cDesc )
   endif
     Area->( dbGoTo( nRecno ) )

return lValid

function tblArea ( cCode ,cDesc )
  local nRecno := Area->( recNo () )
  local cClr   := setcolor() &&("bg+/b+")
  local cScr   := savescreen () && 6,28,18,74 )
  local bF2,nRow,nCol,X
  local Tabel := TBrowseDB()
  local GetList := {}
  local aKolom[2],aPict[2]
  sele Area
  go top

  aKolom[1] := "Area->Code"
  aKolom[2] := "Area->Desc"
  aPict[1]  := "@!"
  aPict[2]  := "@!"

  Tabel:Addcolumn(TBColumnNew("Code",{||tran(Area->Code,aPict[1])}))
  Tabel:Addcolumn(TBColumnNew("Description",{||tran(Area->Desc,aPict[2])}))
  Tabel:HeadSep:=chr(205)
  Tabel:ColSep:=chr(179)
  Tabel:colorSpec:="gr+/r+"
  Tabel:Autolite:= .F.
  Tabel:nTop:=7
  Tabel:nLeft:=31
  Tabel:nBottom:=18
  Tabel:nRight:=73
  sbox( 6,30,19,74,"gr+/r+" )
  Setcursor(0)

  do while .T.
     Tabel:ForceStable()
     nRow := Row()
     nCol := Col()
     DevPos(nRow,nCol)
     x:=SlowFlash(Tabel)

     do case
        case kursor(Tabel,X)
        case x == K_ESC
            exit
        case x == K_ENTER
           cCode := Area->Code
           cDesc := Area->Desc
           exit
      endcase
   enddo
   setcursor(1)
   Area->( dbGoTo( nRecno ) )
   setcolor ( cClr )
   restscreen( 0,0,maxrow(),maxcol(), cScr )

return nil

function chkCodeFG1 ( cCode,cDesc )
   local lValid := .F.
   local nRecno := FGMast1->( recno() )
   FGMast1->( dbSetOrder(1) )
   if FGMast1->( dbSeek( cCode ) )
     lValid  := .T.
   else
     beep()
     alert ( " Code Finished Goods Tidak Ada !! ...Pilih yang Benar !" )
     tblCodeFG1 ( @cCode, @cDesc )
   endif
     FGMast1->( dbGoTo( nRecno ) )

return lValid

function tblCodeFG1 ( cFGCode ,cDesc )
  local nRecno := FGMast1->( recNo () )
  local cClr   := setcolor() &&("bg+/b+")
  local cScr   := savescreen () && 6,28,18,74 )
  local bF2,nRow,nCol,X
  local Tabel := TBrowseDB()
  local GetList := {}
  local aKolom[2],aPict[2]
  sele FGMast1
  go top

  aKolom[1] := "FGMast1->Code"
  aKolom[2] := "FGMast1->Desc"
  aPict[1]  := "@!"
  aPict[2]  := "@!"

  sbox(20,30,23,74,"gr+/r+")
  @ 21 ,31 say "   F1->Cari Code    ENTER->Pilih "
  @ 22 ,31 say "   F2->Cari Nama    Esc->Quit    "

  Tabel:Addcolumn(TBColumnNew("Code",{||tran(FGMast1->FGCode,aPict[1])}))
  Tabel:Addcolumn(TBColumnNew("Description",{||tran(FGMast1->Desc,aPict[2])}))
  Tabel:HeadSep:=chr(205)
  Tabel:ColSep:=chr(179)
  Tabel:colorSpec:="gr+/r+"
  Tabel:Autolite:= .F.
  Tabel:nTop:=6
  Tabel:nLeft:=31
  Tabel:nBottom:=17
  Tabel:nRight:=73
  sbox( 5,30,18,74,"gr+/r+" )
  Setcursor(0)

  do while .T.
     Tabel:ForceStable()
     nRow := Row()
     nCol := Col()
     DevPos(nRow,nCol)
     x:=SlowFlash(Tabel)

     do case
        case kursor(Tabel,X)
        case x == K_ESC
            exit
        case x == K_F1
           Tabel:gotop()
           Tabel:forceStable()
           CariCode1()
           Tabel:refreshAll()
        case x == K_F2
           Tabel:gotop()
           Tabel:forceStable()
           CariNama1()
           Tabel:refreshAll()
        case x == K_ENTER
           cFGCode := FGMast1->FGCode
           cDesc   := FGmast1->Desc
           exit
      endcase
   enddo
   setcursor(1)
   FGMast1->( dbGoTo( nRecno ) )
   FGMast1->( dbSetOrder(1) )
   setcolor ( cClr )
   restscreen( 0,0,maxrow(),maxcol(), cScr )

return nil

function CariCode1()
   local cClr     := setcolor()
   local cScr     := savescreen()
   local cFGCode  := space(7)
   local x := 1
   local getList := {}
   setcursor(1)
   sbox(10,37,12,67,"w+/r")
   @ 11,39 say " Code FG : " get cFGCode pict "@!"
   read
   setcursor(0)
   FGMast1->( dbSetOrder(1) )
   FGMast1->( dbSeek(cFGCode) )
   do while ! found() .and. x<=len(cFGCode)
      FGMast1->( dbSeek(left(cFGCode,len(cFGCode)-x++)))
   enddo

   if ! found()
     sbox(15,20,17,60,"W+/b")
     @ 16,25 say " Code Finished Goods tidak ada "
     tone(700,10)
     inkey(5)
   else
     tone(700,1)
     tone(300,1)
   endif

   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
return Nil

function CariNama1()
   local cClr     := setcolor()
   local cScr     := savescreen()
   local cNamaFG  := space(20)
   local x := 1
   local getList := {}
   setcursor(1)
   sbox(10,33,12,68,"w+/r")
   @ 11,34 say " Nama FG : " get cnamaFG pict "@!"
   read
   setcursor(0)
   FGMast1->( dbSetOrder(2) )
   FGMast1->( dbSeek(cNamaFG) )
   do while ! found() .and. x<=len(cnamaFG)
      FGMast1->( dbSeek(left(cNamaFG,len(cNamaFG)-x++)))
   enddo

   if ! found()
     sbox(15,20,17,60,"W+/b")
     @ 16,25 say " Nama Finished Goods tidak ada "
     tone(700,10)
     inkey(5)
   else
     tone(700,1)
     tone(300,1)
   endif
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
return Nil
