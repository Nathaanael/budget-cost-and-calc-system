#include "stock.ch"

function main()
  local nSelect,cPer,cDesc1,cDesc2
  setcolor("w+/b+")
  cls
  set wrap on
  dispbox( 0,0,24,79,B_DOUBLE+" ","bg+/b+")
  setcolor("bg+/b+")
  @ 22,1 to 22,78 double
  setcolor("w+/b+")
  @ 1,1,21,78 box repl (chr(176),9)
  *setcolor("bG+/B+,gr+/Br+")
  /*
  use costref new
  cPer   := rtrim(CostRef->Period)
  cDesc1 := rtrim(CostRef->Desc1)
  cDesc2 := rtrim(CostRef->Desc2)
  */
  do while .T.
    set key 4  to Rkey
    set key 19 to Lkey
    setcolor("bg+/b+")
    sbox(3,3,12,25,"gr+/bg+,w+/br+")
    @ 4 ,5 say    "    MENU UTAMA  "
    @ 5 ,4 to 5,24
    @ 6 ,5 prompt " 1. Maintenance "
    @ 7 ,5 prompt " 2. Edit File   "
    @ 8 ,5 prompt " 3. Entry       "
    @ 9 ,5 prompt " 4. Calculate   "
    @ 10,5 prompt " 5. Reporting   "
    @ 11,5 prompt " 6. Closing     "

    setcolor("gr+/b+")
    @ 23,3 say " BUDGET COST & SALES Program                (c) Copyright by HŠïdr” (1998)"
    setcolor("gr+/bg+")
    menu to nSelect
    set key 4  to
    set key 19 to
    if lastkey() == K_ESC
      setcolor("w/n")
      cls
      exit
    endif
    do case
       case nSelect == 1
         Maint1()
       case nSelect == 2
         Edit()
       case nSelect == 3
         Entry()
       case nSelect == 4
         Calc()
       case nSelect == 5
         Report()
       case nSelect == 6
         tutup()
    endcase
  enddo
return nil

function maint1()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey
     set key 19 to Lkey
     sbox(6,27,17,43,"gr+/bg+,w+/br+")
     @ 7      ,28 prompt " Noodle        "
     @ row()+1,28 prompt " Area Noodle   "
     @ row()+1,28 prompt " Finished Good "
     @ row()+1,28 prompt " Raw Material  "
     @ row()+1,28 prompt " Formula       "
     @ row()+1,28 prompt " User&Password "
     @ row()+1,28 prompt " Refference    "
     @ row()+1,28 prompt " Factory       "
     @ row()+1,28 prompt " Synonim       "
     @ row()+1,28 prompt " Reindex       "
     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         NDL()
       case nSelect == 2
         Area()
       case nSelect == 3
         FG()
       case nSelect == 4
         RM()
       case nSelect == 5
         Formula()
       case nSelect == 6
         User()
       case nSelect == 7
         Reff()
       case nSelect == 8
         Fact()
       case nSelect == 9
         Synonim()
       case nSelect == 10
         Index()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function NDL()
   local cNDLCode,cDesc,cUnit
   local lSudahAda,lPerluPack := .F.
   local GetList  :={}
   local cClr:=setcolor()
   local cScr:=savescreen()
   set score off
   dataDict()
   do while .T.
      sbox(6,10,14,70,"gr+/b+,w+/bg+")
      @ 7,27 say "Maintenance Noodle"
      @ 8,11 to 8,69
      NDLMast->( dbGoBottom() )
      NDLMast->( dbSkip() )
      cNDLCode := NDLMast->NDLCode
      cDesc    := NDLMast->Desc
      cUnit    := NDLMast->Unit
      @ 10,12 say  "  Noodle Code  : " get cNDLCode    picture "@!"            when myMessage ( " Masukkan Code Noodle, atau tekan Esc untuk keluar ..." )
      @ 11,12 say  "  Description  : " get cDesc       picture "@!"            when .F.
      @ 12,12 say  "         Unit  : " get cUnit       picture "@!"            when .F.
      read
      if lastkey() == K_ESC
         exit
      else
         if cNDLCode == NDLMast->NDLCode
            beep()
            alert( 'Code Noodle Tidak Boleh Kosong .....')
            loop
         endif
         if ( lSudahAda := NDLMast-> ( dbSeek ( cNDLCode ) ) )
              cDesc    := NDLMast->Desc
              cUnit    := NDLMast->Unit
         endif
         @ 11,12 say  "  Description  : " get cDesc       picture "@!"            when myMessage ( " Masukkan Description, atau tekan Esc untuk keluar ..." )
         @ 12,12 say  "         Unit  : " get cUnit       picture "@!"            when myMessage ( " Masukkan Satuan,  atau tekan Esc untuk keluar ..." )
         if lSudahAda
           if NDLMast->( deleted() )
              clear gets
              if alert ( "Data sudah dihapus !!", { " Batal ", " Kembali "} ) == 1
                  NDLMast->( dbRecall() )
              endif
              loop
           endif
           if alert ( " Menu Transaksi ", { " Edit ", " Hapus " } ) == 2
              clear gets
              if alert ( " Benar akan dihapus ? ", { " Yes ", " No " } ) == 1
                 NDLMast->( dbDelete() )
                 lPerluPACK := .T.
              endif
              loop
           endif
         endif
         read
         if ( ! lastkey() == K_ESC ) .and. ( alert ( " Data Sudah Benar ? " , { " Yes ", " No " } ) == 1 )
           if ! lSudahAda
              NDLMast->( dbAppend() )
              NDLMast->NDLCode := cNDLCode
           endif
           NDLMast->Desc   := cDesc
           NDLMast->Unit   := cUnit
           NDLMast->( dbCommit() )
         endif
      endif
   enddo
   if lPerluPACK
      NDLMast->( __dbPack() )
   endif
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
   NDLPrint()
   close all
return nil

function NDLPrint()
   local cPrint
   local nWidth  := 58
   local nSW     := 0
   local nPage   := 0
   local GetList := {}
   local cClr    := setcolor()
   local cScr    := savescreen()
   set date British

   do while .T.
      cPrint := alert ( " Edit List  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "NDL.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)

      NDLMast-> ( dbGoTop () )

      do while ! NDLMast-> ( EOF() )

        if nSW == 0
           nPage++
           @ 1,1   say CostRef->Desc1
           @ 2,1   say CostRef->Desc2
           @ 3,15  say "Edit List Noodle Master"
           @ 4,49  say "Page : "
           @ 4,57  say nPage picture "99"
           @ 5,1   say repli ( "=",nWidth )
           *                    1         2         3         4         5
           *          01234567890123456789012345678901234567890123456789012345678
           @ 6,1  say "|  Code   |          Description            |   Satuan   |"
           @ 7,1  say "|         |                                 |            |"
           *             xxxxxxx   xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx   xxxxxxxxxx
           @ 8,1  say repli ( "=",nWidth )
           nSW := 1
        endif

        @ prow()+1,1   say "|"
        @ prow()  ,4   say NDLMast->NDLCode picture "@!"
        @ prow()  ,11  say "|"
        @ prow()  ,13  say NDLMast->Desc    picture "@!"
        @ prow()  ,45  say "|"
        @ prow()  ,47  say NDLMast->Unit    picture "@!"
        @ prow()  ,58  say "|"

        if prow() >= 55  .or. NDLMast->( EOF() )
          @ prow()+1,1 say repli ( "=",nWidth )
          nSW := 0
          eject
        endif
        NDLMast->( dbSkip() )
      enddo
      @ prow()+1,1 say repli ( "=",nWidth )
      eject
      @prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on

      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"NDL.prn")
         fErase ( "NDL.prn" )
      endif
      setcolor ( cClr )
      restscreen (0,0,maxrow(),maxcol(), cScr )
      exit
   enddo
return nil

function Area()
   local cCode,cDesc
   local lSudahAda,lPerluPack := .F.
   local GetList  :={}
   local cClr:=setcolor()
   local cScr:=savescreen()
   set score off
   dataDict()
   do while .T.
      sbox(6,10,13,70,"gr+/b+,w+/bg+")
      @ 7,27 say "Maintenance Area Noodle"
      @ 8,11 to 8,69
      Area->( dbGoBottom() )
      Area->( dbSkip() )
      cCode := Area->Code
      cDesc := Area->Desc
      @ 10,12 say  "    Area Code  : " get cCode    picture "@!" when myMessage ( " Masukkan Code Area Noodle, atau tekan Esc untuk keluar ..." )
      @ 11,12 say  "  Description  : " get cDesc    picture "@!" when .F.
      read
      if lastkey() == K_ESC
         exit
      else
         if cCode == Area->Code
            beep()
            alert( 'Code Area Noodle Tidak Boleh Kosong .....')
            loop
         endif
         if ( lSudahAda := Area-> ( dbSeek ( cCode ) ) )
              cDesc    := Area->Desc
         endif
         @ 11,12 say  "  Description  : " get cDesc       picture "@!"            when myMessage ( " Masukkan Description, atau tekan Esc untuk keluar ..." )
         if lSudahAda
           if Area->( deleted() )
              clear gets
              if alert ( "Data sudah dihapus !!", { " Batal ", " Kembali "} ) == 1
                  Area->( dbRecall() )
              endif
              loop
           endif
           if alert ( " Menu Transaksi ", { " Edit ", " Hapus " } ) == 2
              clear gets
              if alert ( " Benar akan dihapus ? ", { " Yes ", " No " } ) == 1
                 Area->( dbDelete() )
                 lPerluPACK := .T.
              endif
              loop
           endif
         endif
         read
         if ( ! lastkey() == K_ESC ) .and. ( alert ( " Data Sudah Benar ? " , { " Yes ", " No " } ) == 1 )
           if ! lSudahAda
              Area->( dbAppend() )
              Area->Code := cCode
           endif
           Area->Desc := cDesc
           Area->( dbCommit() )
         endif
      endif
   enddo
   if lPerluPACK
      Area->( __dbPack() )
   endif
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
   AreaPrint()
   close all
return nil

function AreaPrint()
   local cPrint
   local nWidth  := 45
   local nSW     := 0
   local nPage   := 0
   local GetList := {}
   local cClr    := setcolor()
   local cScr    := savescreen()
   set date British

   do while .T.
      cPrint := alert ( " Edit List  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "Area.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)

      Area-> ( dbGoTop () )

      do while ! Area-> ( EOF() )

        if nSW == 0
           nPage++
           @ 1,1   say CostRef->Desc1
           @ 2,1   say CostRef->Desc2
           @ 3,10  say "Edit List Area Noodle "
           @ 4,1   say repli ( "=",nWidth )
           *                    1         2         3         4         5
           *          01234567890123456789012345678901234567890123456789012345678
           @ 5,1  say "|  Code   |          Description            |"
           @ 6,1  say "|         |                                 |"
           *               xx      xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
           @ 7,1  say repli ( "=",nWidth )
           nSW := 1
        endif

        @ prow()+1,1   say "|"
        @ prow()  ,4   say Area->Code    picture "@!"
        @ prow()  ,11  say "|"
        @ prow()  ,13  say Area->Desc    picture "@!"
        @ prow()  ,45  say "|"

        if prow() >= 55  .or. Area->( EOF() )
          @ prow()+1,1 say repli ( "=",nWidth )
          nSW := 0
          eject
        endif
        Area->( dbSkip() )
      enddo
      @ prow()+1,1 say repli ( "=",nWidth )
      eject
      @prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on

      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"Area.prn")
         fErase ( "Area.prn" )
      endif
      setcolor ( cClr )
      restscreen (0,0,maxrow(),maxcol(), cScr )
      exit
   enddo
return nil

function FG()
   local cFGCode,cLevel,cActive
   local cDesc,cPrtype1,cPrtype2,cDesc1
   local nBatch,nHrgJual,nUC0,nUC1,nUC2,nUC3,nUC4,nUCLE
   local nUP00,nUP01,nUP02,nUP03,nUP04,nUPLE
   local nUP10,nUP11,nUP12,nUP13,nUP14,nUPLE1
   local nUP20,nUP21,nUP22,nUP23,nUP24,nUPLE2
   local nPE0,nPE1,nPE2,nPE3,nPE4
   local lSudahAda
   local lPerluPACK := .F.
   local GetList  :={}
   local cClr:=setcolor()
   local cScr:=savescreen()
   set score off
   dataDict()
   do while .T.
      sbox(4,05,19,72,"gr+/b+,w+/bg+")
      @ 5,22 say "Maintenance Finished Goods"
      @ 6,06 to 6,71
      FGMast->( dbGoBottom() )
      FGMast->( dbSkip() )
      cActive    := FGMast->Active
      cFGCode    := FGMast->FGCode
      cLevel     := FGMast->Level
      cDesc      := FGMast->Desc
      cDesc1     := FGMast->Desc1
      nBatch     := FGMast->Batch
      cPrtype1   := FGMast->Prtype1
      cPrtype2   := FGMast->Prtype2
      nHrgJual   := FGMast->HrgJual
      nUC0       := FGMast->UC0
      nUCLE      := FGMast->UCLE
      nUC1       := FGMast->UC1
      nUC2       := FGMast->UC2
      nUC3       := FGMast->UC3
      nUC4       := FGMast->UC4

      nUP00      := FGMast->Price00
      nUPLE      := FGMast->PriceLE
      nUP01      := FGMast->Price01
      nUP02      := FGMast->Price02
      nUP03      := FGMast->Price03
      nUP04      := FGMast->Price04

      nUP10      := FGMast->Price10
      nUPLE1     := FGMast->PriceLE1
      nUP11      := FGMast->Price11
      nUP12      := FGMast->Price12
      nUP13      := FGMast->Price13
      nUP14      := FGMast->Price14

      nUP20      := FGMast->Price20
      nUPLE2     := FGMast->PriceLE2
      nUP21      := FGMast->Price21
      nUP22      := FGMast->Price22
      nUP23      := FGMast->Price23
      nUP24      := FGMast->Price24

      @ 07,07 say  "         Code FG  : " get cFGCode     picture "@!"            when myMessage ( " Masukkan Code Finished Goods, atau tekan Esc untuk keluar ..." )
      @ 07,39 say  "Multi Level [Y/N] : " get cLevel      picture "@!"            when .F.
      @ 08,07 say  "     Description  : " get cDesc       picture "@!"            when .F.
      @ 09,07 say  "     Description 1: " get cDesc1      picture "@!"            when .F.
      @ 10,07 say  "   Product Type 1 : " get cPrtype1    picture "@!"            when .F.
      @ 11,07 say  "   Product Type 2 : " get cPrtype2    picture "@!"            when .F.
      @ 10,35 say  "Batch   : "           get nBatch      picture "@z 999,999"    when .F.
      @ 11,35 say  "HrgJual : "           get nHrgJual    picture "@z 999,999.99" when .F.
      @ 10,54 say  "Active [Y/N] : "            get cActive     picture "@!"            when .F.
      @ 12,07 say  "Unit Cost Current : " get nUC0        picture "@z 999,999.99" when .F.
      @ 13,07 say  "Unit Cost LE      : " get nUCLE       picture "@z 999,999.99" when .F.
      @ 14,07 say  "Unit Cost Qtr 1   : " get nUC1        picture "@z 999,999.99" when .F.
      @ 15,07 say  "Unit Cost Qtr 2   : " get nUC2        picture "@z 999,999.99" when .F.
      @ 16,07 say  "Unit Cost Qtr 3   : " get nUC3        picture "@z 999,999.99" when .F.
      @ 17,07 say  "Unit Cost Qtr 4   : " get nUC4        picture "@z 999,999.99" when .F.
      @ 12,39 say  "Unit Price Current : " get nUP00       picture "@z 999,999.99" when .F.
      @ 13,39 say  "Unit Price LE      : " get nUPLE      picture "@z 999,999.99" when .F.
      @ 14,39 say  "Unit Price Qtr 1   : " get nUP01       picture "@z 999,999.99" when .F.
      @ 15,39 say  "Unit Price Qtr 2   : " get nUP02       picture "@z 999,999.99" when .F.
      @ 16,39 say  "Unit Price Qtr 3   : " get nUP03       picture "@z 999,999.99" when .F.
      @ 17,39 say  "Unit Price Qtr 4   : " get nUP04       picture "@z 999,999.99" when .F.
      read

      if lastkey() == K_ESC
         exit
      else
         if cFGCode == FGMast->FGCode
            beep()
            alert( 'Code FG Tidak Boleh Kosong .....')
            loop
         endif
         if ( lSudahAda := FGMast-> ( dbSeek ( cFGCode ) ) )
             cActive   := FGMast->Active
             cDesc     := FGMast->Desc
             cDesc1    := FGMast->Desc1
             cLevel    := FGMast->Level
             nBatch    := FGMast->Batch
             cPrtype1  := FGMast->Prtype1
             cPrtype2  := FGMast->Prtype2
             nHrgJual  := FGMast->HrgJual
             nUC0      := FGMast->UC0
             nUCLE     := FGMast->UCLE
             nUC1      := FGMast->UC1
             nUC2      := FGMast->UC2
             nUC3      := FGMast->UC3
             nUC4      := FGMast->UC4

             nUP00      := FGMast->Price00
             nUPLE      := FGMast->PriceLE
             nUP01      := FGMast->Price01
             nUP02      := FGMast->Price02
             nUP03      := FGMast->Price03
             nUP04      := FGMast->Price04
         endif
         @ 07,39 say  "Multi Level [Y/N] : " get cLevel      picture "@!"            when myMessage ( " Masukkan Multi Level, atau tekan Esc untuk keluar ..." ) valid cLevel == "Y" .or. cLevel == "N"
         @ 08,07 say  "     Description  : " get cDesc       picture "@!"            when myMessage ( " Masukkan Nama Finished Goods, atau tekan Esc untuk keluar ..." )
         @ 09,07 say  "     Description 1: " get cDesc1      picture "@!"            when myMessage ( " Masukkan keterangan, atau tekan Esc untuk keluar ..." )
         @ 10,07 say  "   Product Type 1 : " get cPrtype1    picture "@!"            when myMessage ( " Masukkan Product Type 1, atau tekan Esc untuk keluar ..." )
         @ 11,07 say  "   Product Type 2 : " get cPrtype2    picture "@!"            when myMessage ( " Masukkan Product Type 2, atau tekan Esc untuk keluar ..." )
         @ 10,35 say  "Batch   : " get nBatch                picture "@z 999,999"    when myMessage ( " Masukkan Batch Finished Goods, atau tekan Esc untuk keluar ..." )
         @ 11,35 say  "HrgJual : " get nHrgJual              picture "@z 999,999.99" when myMessage ( " Masukkan Harga Jual Finished Goods, atau tekan Esc untuk keluar ..." )
         @ 10,54 say  "Active [Y/N] : "  get cActive         picture "@!"            when myMessage ( " Masukkan Code Active, atau tekan Esc untuk keluar ..." ) valid cActive == "Y" .or. cActive == "N"
         @ 12,07 say  "Unit Cost Current : " get nUC0        picture "@z 999,999.99" when myMessage ( " Masukkan Unit Cost Current , atau tekan Esc untuk keluar ..." )
         @ 13,07 say  "Unit Cost LE      : " get nUCLE       picture "@z 999,999.99" when myMessage ( " Masukkan Unit Cost LE , atau tekan Esc untuk keluar ..." )
         @ 14,07 say  "Unit Cost Qtr 1   : " get nUC1        picture "@z 999,999.99" when myMessage ( " Masukkan Unit Cost Qtr 1 , atau tekan Esc untuk keluar ..." )
         @ 15,07 say  "Unit Cost Qtr 2   : " get nUC2        picture "@z 999,999.99" when myMessage ( " Masukkan Unit Cost Qtr 2 , atau tekan Esc untuk keluar ..." )
         @ 16,07 say  "Unit Cost Qtr 3   : " get nUC3        picture "@z 999,999.99" when myMessage ( " Masukkan Unit Cost Qtr 3 , atau tekan Esc untuk keluar ..." )
         @ 17,07 say  "Unit Cost Qtr 4   : " get nUC4        picture "@z 999,999.99" when myMessage ( " Masukkan Unit Cost Qtr 4 , atau tekan Esc untuk keluar ..." )
         @ 12,39 say  "Unit Price Current : " get nUP00       picture "@z 999,999.99" when myMessage ( " Masukkan Unit Price Current , atau tekan Esc untuk keluar ..." )
         @ 13,39 say  "Unit Price LE      : " get nUPLE       picture "@z 999,999.99" when myMessage ( " Masukkan Unit Price LE , atau tekan Esc untuk keluar ..." )
         @ 14,39 say  "Unit Price Qtr 1   : " get nUP01       picture "@z 999,999.99" when myMessage ( " Masukkan Unit Price Qtr 1 , atau tekan Esc untuk keluar ..." )
         @ 15,39 say  "Unit Price Qtr 2   : " get nUP02       picture "@z 999,999.99" when myMessage ( " Masukkan Unit Price Qtr 2 , atau tekan Esc untuk keluar ..." )
         @ 16,39 say  "Unit Price Qtr 3   : " get nUP03       picture "@z 999,999.99" when myMessage ( " Masukkan Unit Price Qtr 3 , atau tekan Esc untuk keluar ..." )
         @ 17,39 say  "Unit Price Qtr 4   : " get nUP04       picture "@z 999,999.99" when myMessage ( " Masukkan Unit Price Qtr 4 , atau tekan Esc untuk keluar ..." )

         if lSudahAda
           if FGMast->( deleted() )
              clear gets
              if alert ( "Data sudah dihapus !!", { " Batal ", " Kembali "} ) == 1
                  FGMast->( dbRecall() )
              endif
              loop
           endif
           if alert ( " Menu Transaksi ", { " Edit ", " Hapus " } ) == 2
              clear gets
              if alert ( " Benar akan dihapus ? ", { " Yes ", " No " } ) == 1
                 FGMast->( dbDelete() )
                 lPerluPACK := .T.
              endif
              loop
           endif
         endif
         read
         if ( ! lastkey() == K_ESC ) .and. ( alert ( " Data Sudah Benar ? " , { " Yes ", " No " } ) == 1 )
           if ! lSudahAda
              FGMast->( dbAppend() )
              FGMast->FGCode := cFGCode
           endif
           FGMast->Active    := cActive
           FGMast->Level     := cLevel
           FGMast->Desc      := cDesc
           FGMast->Desc1     := cDesc1
           FGMast->Batch     := nBatch
           FGMast->Prtype1   := cPrtype1
           FGMast->Prtype2   := cPrtype2
           FGMast->HrgJual   := nHrgJual
           FGMast->UC0       := nUC0
           FGMast->UCLE      := nUCLE
           FGMast->UC1       := nUC1
           FGMast->UC2       := nUC2
           FGMast->UC3       := nUC3
           FGMast->UC4       := nUC4

           FGMast->Price00    := nUP00
           FGMast->PriceLE    := nUPLE
           FGMast->Price01    := nUP01
           FGMast->Price02    := nUP02
           FGMast->Price03    := nUP03
           FGMast->Price04    := nUP04
           FGMast->( dbCommit() )
         endif
      endif
   enddo
   if lPerluPACK
      FGMast->( __dbPack() )
   endif
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
   FGPrint()
   close all
return nil

function FGPrint()
   local cPrint
   local nWidth  := 92
   local nSW     := 0
   local nPage   := 0
   local GetList := {}
   local cClr    := setcolor()
   local cScr    := savescreen()
   set date British

   do while .T.
      cPrint := alert ( " Edit List  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "FGMast.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)
      FGMast-> ( dbGoTop () )
      do while ! FGMast-> ( EOF() )
        if nSW == 0
           nPage++
           @ 1,1   say CostRef->Desc1
           @ 2,1   say CostRef->Desc2
           @ 3,20  say "Edit List FINISHED GOODS"
           @ 4,77  say "Page : "
           @ 4,85  say nPage picture "99"
           @ 5,1   say repli ( "=",nWidth )
           *                    1         2         3         4         5         6         7         8         9         0         1         2         3
           *          01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123
           @ 6,1  say "|  Code FG  |            Description           |  Batch  |  Product Type  | Multi | Active |"
           @ 7,1  say "|           |                                  |         |    1      2    | Level |        |"
           *              xxxxxxx     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx    999,999      xx     xx       xx      xx
           @ 8,1  say repli ( "=",nWidth )
           nSW := 1
        endif
        @ prow()+1,1   say "|"
        @ prow()  ,4   say FGMast->FGCode    picture "@!"
        @ prow()  ,13  say "|"
        @ prow()  ,16  say FGMast->Desc      picture "@!"
        @ prow()  ,48  say "|"
        @ prow()  ,50  say FGMast->Batch     picture "@z 999,999"
        @ prow()  ,58  say "|"
        @ prow()  ,63  say FGMast->Prtype1   picture "@!"
        @ prow()  ,70  say FGMast->Prtype2   picture "@!"
        @ prow()  ,75  say "|"
        @ prow()  ,79  say FGMast->Level     picture "@!"
        @ prow()  ,83  say "|"
        @ prow()  ,87  say FGMast->Active    picture "@!"
        @ prow()  ,92  say "|"
        if prow() >= 55  .or. FGMast->( EOF() )
          @ prow()+1,1 say repli ( "=",nWidth )
          nSW := 0
          eject
        endif
        FGMast->( dbSkip() )
      enddo
      @ prow()+1,1 say repli ( "=",nWidth )
      eject
      @prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on
      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"FGMast.prn")
         fErase ( "FGMast.prn" )
      endif
      setcolor ( cClr )
      restscreen (0,0,maxrow(),maxcol(), cScr )
      exit
   enddo
return nil

function RM()
   local cRMCode
   local cDesc,cTypeRM
   local cId,cUnit,cType,nWaste
   local lSudahAda
   local lPerluPACK := .F.
   local GetList  :={}
   local cClr:=setcolor()
   local cScr:=savescreen()
   set score off
   dataDict()
   do while .T.
      sbox(4,10,15,70,"gr+/b+,w+/bg+")
      @ 5,27 say ' Maintenance Raw Material '
      @ 6,11 to 6,69
      RMMast->( dbGoBottom() )
      RMMast->( dbSkip() )
      cRMCode   := RMMast->RMCode
      cDesc     := RMMast->Desc
      cId       := RMMast->Id
      cUnit     := RMMast->Unit
      cType     := RMMast->Type_Curr
      cTypeRM   := RMMast->Type
      nWaste    := RMMast->Waste
      @ 08,12 say  "        Code RM : " get cRMCode   picture "@!"    when myMessage ( " Masukkan Code Raw Material, atau tekan Esc untuk keluar ..." )
      @ 10,12 say  "    Description : " get cDesc     picture "@!"    when .F.
      @ 11,12 say  "Unit of Measure : " get cUnit     picture "@!"    when .F.
      @ 11,43 say  "ID : "              get cId       picture "@!"    when .F.
      @ 11,53 say  "Type : "            get cTypeRM   picture "@!"    when .F.
      @ 12,12 say  "   Wastage All. : " get nWaste    picture "@z 999,999.99999" when .F.
      @ 13,12 say  "  Type Currency : " get cType     picture "@!"    when .F.
      @ 13,35 say  "[1] USD   [2] Rp."
      read
      if lastkey() == K_ESC
         exit
      else
         if cRMCode == RMMast->RMCode
            beep()
            alert( 'Code RM Tidak Boleh Kosong .....')
            loop
         endif
         if ( lSudahAda := RMMast-> ( dbSeek ( cRMCode ) ) )
             cDesc     := RMMast->Desc
             cUnit     := RMMast->Unit
             cId       := RMMast->Id
             cUnit     := RMMast->Unit
             cType     := RMMast->Type_Curr
             cTypeRM   := RMMast->Type
             nWaste    := RMMast->Waste
         endif
         @ 10,12 say  "    Description : " get cDesc     picture "@!"    when myMessage ( " Masukkan Nama Raw Material, atau tekan Esc untuk keluar ..." )
         @ 11,12 say  "Unit of Measure : " get cUnit     picture "@!"    when myMessage ( " Masukkan Unit of Measure, atau tekan Esc untuk keluar ..." )
         @ 11,43 say  "ID : "              get cId       picture "@!"    when myMessage ( " Masukkan Code ID, atau tekan Esc untuk keluar ..." )
         @ 11,53 say  "Type : "            get cTypeRM   picture "@!"    when myMessage ( " Masukkan Type RM, atau tekan Esc untuk keluar ..." )
         @ 12,12 say  "   Wastage All. : " get nWaste    picture "@z 999,999.99999" when myMessage ( " Masukkan Wastage Allowance ( % ), atau tekan Esc untuk keluar ..." )
         @ 13,12 say  "  Type Currency : " get cType     picture "@!"      when myMessage ( " Masukkan Type Currency, atau tekan Esc untuk keluar ..." )
         @ 13,35 say  "[1] USD   [2] Rp."
         if lSudahAda
           if RMMast->( deleted() )
              clear gets
              if alert ( "Data sudah dihapus !!", { " Batal ", " Kembali "} ) == 1
                  RMMast->( dbRecall() )
              endif
              loop
           endif

           if alert ( " Menu Transaksi ", { " Edit ", " Hapus " } ) == 2
              clear gets
              if alert ( " Benar akan dihapus ? ", { " Yes ", " No " } ) == 1
                 RMMast->( dbDelete() )
                 lPerluPACK := .T.
              endif
              loop
           endif
         endif
         read
         if ( ! lastkey() == K_ESC ) .and. ( alert ( " Data Sudah Benar ? " , { " Yes ", " No " } ) == 1 )
           if ! lSudahAda
              RMMast->( dbAppend() )
              RMMast->RMCode := cRMCode
           endif
           RMMast->Desc      := cDesc
           RMMast->Unit      := cUnit
           RMMast->Id        := cId
           RMMast->Type_Curr := cType
           RMMast->Type      := cTypeRM
           RMMast->Waste     := nWaste
           RMMast->( dbCommit() )
         endif
      endif
   enddo
   if lPerluPACK
      RMMast->( __dbPack() )
   endif
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
   RMPrint()
   close all
return nil

function RMPrint()
   local cPrint
   local nWidth  := 84
   local nSW     := 0
   local nPage   := 0
   local GetList := {}
   local cClr    := setcolor()
   local cScr    := savescreen()
   set date British
   do while .T.
      cPrint := alert ( " Edit List  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "RMMast.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)
      RMMast-> ( dbGoTop () )
      do while ! RMMast-> ( EOF() )
        if nSW == 0
           nPage++
           @ 1,1   say CostRef->Desc1
           @ 2,1   say CostRef->Desc2
           @ 3,28  say "Edit List RAW MATERIALS"
           @ 4,66  say "Page : "
           @ 4,73  say nPage picture "99"
           @ 5,1   say repli ( "=",nWidth )
           *                    1         2         3         4         5         6         7         8         9         0         1
           *          012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789
           @ 6,1  say "|  Code RM  |            Description           |    Unit   |  ID  | Curr | Type RM |"
           *              xxxxxxx     xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx     xxxxxxxx     x
           @ 7,1  say repli ( "=",nWidth )
           nSW := 1
        endif
        @ prow()+1,1   say "|"
        @ prow()  ,4   say RMMast->RMCode      picture "@!"
        @ prow()  ,13  say "|"
        @ prow()  ,16  say RMMast->Desc        picture "@!"
        @ prow()  ,48  say "|"
        @ prow()  ,51  say RMMast->Unit        picture "@!"
        @ prow()  ,60  say "|"
        @ prow()  ,64  say RMMast->Id          picture "@!"
        @ prow()  ,67  say "|"
        do case
           case RMMast->Type_Curr == '1'
              @ prow(), 70 say "USD"  picture "@!"
           case RMMast->Type_Curr == '2'
              @ prow(), 70 say "Rp."  picture "@!"
           otherwise
              @ prow(), 70 say "   "  picture "@!"
        endcase
        @ prow()  ,74 say "|"
        @ prow()  ,78 say RMMast->Type        picture "@!"
        @ prow()  ,84 say "|"
        if prow() >= 55  .or. RMMast->( EOF() )
          @ prow()+1,1 say repli ( "=",nWidth )
          nSW := 0
          eject
        endif
        RMMast->( dbSkip() )
      enddo
      @ prow()+1,1 say repli ( "=",nWidth )
      eject
      @prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on
      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"RMMast.prn")
         fErase ( "RMMast.prn" )
      endif
      setcolor ( cClr )
      restscreen (0,0,maxrow(),maxcol(), cScr )
      exit
   enddo
return nil

function tutup()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey
     set key 19 to Lkey
     sbox(11,27,13,45,"gr+/bg+,w+/br+")
     @ 12,28 prompt " Closing Period "
     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         tutup1()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function tutup1()
    local cPer := space(8)
    local dDate
    local cYEC := "N"
    local lSudahAda
    local nUseQty := 0
    local nUseAmt := 0
    local nEndQty := 0
    local nEndAmt := 0
    local GetList := {}
    local cClr:=setcolor()
    local cScr:=savescreen()
    set score off
    set date British
    dataDict()

    do while .T.
      if alert ( " Data Sudah di Back Up ? ", { " Yes ", " No " } ) == 1
        if alert ( " Closing : ", { " Yes ", " No " } ) == 1
           sbox(17,25,21,57,"gR+/bG+,W+/BR+")
           @ 18,26       say " Closing : "
           setcolor("R+*/bG+,W+/BR+")
           @ 18,col()+1  say substr(CostRef->Period,1,14)
           setcolor("gR+/bG+,W+/BR+")
           @ 19,26 say " Opening : " get cPer picture "@!"
           @ 19,45 say " (ddmmyyyy)"
           @ 20,26 say " Year End Close : " get cYEC picture "@!"
           read
           if lastkey() == K_ESC
             exit
           endif
           CostRef->( dbGoTop() )
           repl CostRef->Per with cPer
           dDate := ctod(substr(cPer,1,2)+'/'+substr(cPer,3,2)+'/'+substr(cPer,7,2))
           repl CostRef->Period WITH CMONTH(dDATE) + ' '+substr(cPer,5,4)   &&LTRIM(STR(YEAR(dDATE)))
           CostRef->( dbCommit() )
           if cYEC == "Y"
             sele fgmast
             repl all uc0 with 0,uc1 with 0,uc2 with 0,uc3 with 0,uc4 with 0
             repl all price0 with 0,price1 with 0,price2 with 0,price3 with 0,price4 with 0
           endif
        else
           exit
        endif
      endif
      exit
    enddo
    setcolor ( cClr )
    restscreen ( 0,0,maxrow(),maxcol(), cScr )
    close all
return nil

function Reff()
   local cCode,nRate0,nRate1,nRate2,nRate3,nRate4,nRateLE
   local nPE00,nPE01,nPE02,nPE03,nPE04,nPELE
   local nPE10,nPE11,nPE12,nPE13,nPE14,nPELE1
   local nPE20,nPE21,nPE22,nPE23,nPE24,nPELE2
   local cDesc1,cDesc2
   local cPer1,cPer2
   local lSudahAda,lPerluPack := .F.
   local GetList  :={}
   local cClr:=setcolor()
   local cScr:=savescreen()
   store space(1) to ket1,ket2
   set score off
   dataDict()
   do while .T.
      sbox(3,02,22,78,"gr+/b+,w+/bg+")
      @ 4,27 say "Maintenance Refference"
      @ 5,03 to 5,77
      CostRef->( dbGoBottom() )
      CostRef->( dbSkip() )
      cCode    := CostRef->Code
      cDesc1   := CostRef->Desc1
      cDesc2   := CostRef->Desc2
      cPer1    := CostRef->Per
      cPer2    := CostRef->Period
      nRate0   := CostRef->Rate0
      nRateLE  := CostRef->RateLE
      nRate1   := CostRef->Rate1
      nRate2   := CostRef->Rate2
      nRate3   := CostRef->Rate3
      nRate4   := CostRef->Rate4

      nPE00    := CostRef->PE00
      nPELE    := CostRef->PELE
      nPE01    := CostRef->PE01
      nPE02    := CostRef->PE02
      nPE03    := CostRef->PE03
      nPE04    := CostRef->PE04

      nPE10    := CostRef->PE10
      nPELE1   := CostRef->PELE1
      nPE11    := CostRef->PE11
      nPE12    := CostRef->PE12
      nPE13    := CostRef->PE13
      nPE14    := CostRef->PE14

      nPE20    := CostRef->PE20
      nPELE2   := CostRef->PELE2
      nPE21    := CostRef->PE21
      nPE22    := CostRef->PE22
      nPE23    := CostRef->PE23
      nPE24    := CostRef->PE24

      @ 07,12 say  "         Code  : " get cCode   picture "@!"            when myMessage ( " Masukkan Code Reff, atau tekan Esc untuk keluar ..." )
      read
      if cCode="00"
         ket1 ="Ckp "
         ket2 ="Smg "
         ket3 ="sby "
      else
         ket1 ="Ckp "
         ket2 ="Smg "
         ket3 ="sby "
      endif
      @ 08,12 say  " Description1  : " get cDesc1  picture "@!"            when .F.
      @ 09,12 say  " Description2  : " get cDesc2  picture "@!"            when .F.
      @ 10,12 say  "      Periode  : " get cPer1   picture "@!"            when .F.
      @ 11,12 say  " Periode Desc  : " get cPer2   picture "@!"            when .F.
      @ 12,12 say  "  Rate Current : " get nRate0  picture "@z99,999,999"  when .F.
      @ 13,12 say  "       Rate LE : " get nRateLE picture "@z99,999,999"  when .F.
      @ 14,12 say  "       Rate 1  : " get nRate1  picture "@z99,999,999"  when .F.
      @ 12,47 say  "Rate 2 : "         get nRate2  picture "@z99,999,999"  when .F.
      @ 13,47 say  "Rate 3 : "         get nRate3  picture "@z99,999,999"  when .F.
      @ 14,47 say  "Rate 4 : "         get nRate4  picture "@z99,999,999"  when .F.

      @ 15,03 say  "#1 PE "+ket1 +": " get nPE00   picture "@z999,999"     when .F.
      @ 16,03 say  " PE LE : " get nPELE   picture "@z999,999"     when .F.
      @ 17,03 say  " PE 1  : " get nPE01   picture "@z999,999"     when .F.
      @ 18,03 say  " PE 2  : " get nPE02   picture "@z999,999"     when .F.
      @ 19,03 say  " PE 3  : " get nPE03   picture "@z999,999"     when .F.
      @ 20,03 say  " PE 4  : " get nPE04   picture "@z999,999"     when .F.

      @ 15,27 say  "#2 PE "+ket2+": " get nPE10   picture "@z999,999"     when .F.
      @ 16,27 say  "     PE LE : " get nPELE1  picture "@z999,999"     when .F.
      @ 17,27 say  "     PE 1  : " get nPE11   picture "@z999,999"     when .F.
      @ 18,27 say  "     PE 2  : " get nPE12   picture "@z999,999"     when .F.
      @ 19,27 say  "     PE 3  : " get nPE13   picture "@z999,999"     when .F.
      @ 20,27 say  "     PE 4  : " get nPE14   picture "@z999,999"     when .F.

      @ 15,51 say  "#3 PE "+ket3+": " get nPE20   picture "@z999,999"     when .F.
      @ 16,51 say  "     PE LE : " get nPELE2  picture "@z999,999"     when .F.
      @ 17,51 say  "     PE 1  : " get nPE21   picture "@z999,999"     when .F.
      @ 18,51 say  "     PE 2  : " get nPE22   picture "@z999,999"     when .F.
      @ 19,51 say  "     PE 3  : " get nPE23   picture "@z999,999"     when .F.
      @ 20,51 say  "     PE 4  : " get nPE24   picture "@z999,999"     when .F.

      read
      if lastkey() == K_ESC
         exit
      else
         if cCode == CostRef->Code
            beep()
            alert( 'Code Reff Tidak Boleh Kosong .....')
            loop
         endif
         if ( lSudahAda := CostRef-> ( dbSeek ( cCode ) ) )
             cDesc1    := CostRef->Desc1
             cDesc2    := CostRef->Desc2
             cPer1     := CostRef->Per
             cPer2     := CostRef->Period
             nRate0    := CostRef->Rate0
             nRateLE   := CostRef->RateLE
             nRate1    := CostRef->Rate1
             nRate2    := CostRef->Rate2
             nRate3    := CostRef->Rate3
             nRate4    := CostRef->Rate4

             nPE00     := CostRef->PE00
             nPELE     := CostRef->PELE
             nPE01     := CostRef->PE01
             nPE02     := CostRef->PE02
             nPE03     := CostRef->PE03
             nPE04     := CostRef->PE04

             nPE10     := CostRef->PE10
             nPELE1    := CostRef->PELE1
             nPE11     := CostRef->PE11
             nPE12     := CostRef->PE12
             nPE13     := CostRef->PE13
             nPE14     := CostRef->PE14

             nPE20     := CostRef->PE20
             nPELE2    := CostRef->PELE2
             nPE21     := CostRef->PE21
             nPE22     := CostRef->PE22
             nPE23     := CostRef->PE23
             nPE24     := CostRef->PE24
         endif

         @ 08,12 say  " Description1  : " get cDesc1      picture "@!"            when myMessage ( " Masukkan Description 1, atau tekan Esc untuk keluar ..." )
         @ 09,12 say  " Description2  : " get cDesc2      picture "@!"            when myMessage ( " Masukkan Description 2, atau tekan Esc untuk keluar ..." )
         @ 10,12 say  "      Periode  : " get cPer1       picture "@!"            when myMessage ( " Masukkan Periode Sekarang , atau tekan Esc untuk keluar ..." )
         @ 11,12 say  " Periode Desc  : " get cPer2       picture "@!"            when myMessage ( " Masukkan Description Periode Sekarang , atau tekan Esc untuk keluar ..." )
         @ 12,12 say  "  Rate Current : " get nRate0      picture "@z99,999,999"  when myMessage ( " Masukkan Rate Current , atau tekan Esc untuk keluar ..." )
         @ 13,12 say  "       Rate LE : " get nRateLE     picture "@z99,999,999"  when myMessage ( " Masukkan Rate LE , atau tekan Esc untuk keluar ..." )
         @ 14,12 say  "       Rate 1  : " get nRate1      picture "@z99,999,999"  when myMessage ( " Masukkan Rate Quartal I , atau tekan Esc untuk keluar ..." )
         @ 12,47 say  "Rate 2 : "         get nRate2      picture "@z99,999,999"  when myMessage ( " Masukkan Rate Quartal II , atau tekan Esc untuk keluar ..." )
         @ 13,47 say  "Rate 3 : "         get nRate3      picture "@z99,999,999"  when myMessage ( " Masukkan Rate Quartal III , atau tekan Esc untuk keluar ..." )
         @ 14,47 say  "Rate 4 : "         get nRate4      picture "@z99,999,999"  when myMessage ( " Masukkan Rate Quartal IV , atau tekan Esc untuk keluar ..." )

         @ 15,03 say  "#1 PE "+ket1 +": " get nPE00   picture "@z999,999"     
         @ 16,03 say  " PE LE : " get nPELE   picture "@z999,999"     
         @ 17,03 say  " PE 1  : " get nPE01   picture "@z999,999"     
         @ 18,03 say  " PE 2  : " get nPE02   picture "@z999,999"     
         @ 19,03 say  " PE 3  : " get nPE03   picture "@z999,999"     
         @ 20,03 say  " PE 4  : " get nPE04   picture "@z999,999"     


         *@ 15,03 say  "#1 PE "+ket1+" : " get nPE00   picture "@z999,999"     &&when .F.
         *@ 16,03 say  "         PE LE : " get nPELE   picture "@z999,999"     &&when .F.
         *@ 17,03 say  "         PE 1  : " get nPE01   picture "@z999,999"     &&when .F.
         *@ 18,03 say  "         PE 2  : " get nPE02   picture "@z999,999"     &&when .F.
         *@ 19,03 say  "         PE 3  : " get nPE03   picture "@z999,999"     &&when .F.
         *@ 20,03 say  "         PE 4  : " get nPE04   picture "@z999,999"     &&when .F.

         @ 15,27 say  "#2 PE "+ket2+" : " get nPE10   picture "@z999,999"     &&when .F.
         @ 16,27 say  "     PE LE : " get nPELE1  picture "@z999,999"     &&when .F.
         @ 17,27 say  "     PE 1  : " get nPE11   picture "@z999,999"     &&when .F.
         @ 18,27 say  "     PE 2  : " get nPE12   picture "@z999,999"     &&when .F.
         @ 19,27 say  "     PE 3  : " get nPE13   picture "@z999,999"     &&when .F.
         @ 20,27 say  "     PE 4  : " get nPE14   picture "@z999,999"     &&when .F.

         @ 15,51 say  "#3 PE "+ket3+" : " get nPE20   picture "@z999,999"     &&when .F.
         @ 16,51 say  "     PE LE : " get nPELE2  picture "@z999,999"     &&when .F.
         @ 17,51 say  "     PE 1  : " get nPE21   picture "@z999,999"     &&when .F.
         @ 18,51 say  "     PE 2  : " get nPE22   picture "@z999,999"     &&when .F.
         @ 19,51 say  "     PE 3  : " get nPE23   picture "@z999,999"     &&when .F.
         @ 20,51 say  "     PE 4  : " get nPE24   picture "@z999,999"     &&when .F.

         read
         if lSudahAda
           if CostRef->( deleted() )
              clear gets
              if alert ( "Data sudah dihapus !!", { " Batal ", " Kembali "} ) == 1
                  CostRef->( dbRecall() )
              endif
              loop
           endif

           if alert ( " Menu Transaksi ", { " Edit ", " Hapus " } ) == 2
              clear gets
              if alert ( " Benar akan dihapus ? ", { " Yes ", " No " } ) == 1
                 CostRef->( dbDelete() )
                 lPerluPACK := .T.
              endif
              loop
           endif
         endif
         read
         if ( ! lastkey() == K_ESC ) .and. ( alert ( " Data Sudah Benar ? " , { " Yes ", " No " } ) == 1 )
           if ! lSudahAda
              CostRef->( dbAppend() )
              CostRef->Code := cCode
           endif
           CostRef->Desc1  := cDesc1
           CostRef->Desc2  := cDesc2
           CostRef->Per    := cPer1
           CostRef->Period := cPer2
           CostRef->Rate0  := nRate0
           CostRef->RateLE := nRateLE
           CostRef->Rate1  := nRate1
           CostRef->Rate2  := nRate2
           CostRef->Rate3  := nRate3
           CostRef->Rate4  := nRate4

           CostRef->PE00   := nPE00
           CostRef->PELE   := nPELE
           CostRef->PE01   := nPE01
           CostRef->PE02   := nPE02
           CostRef->PE03   := nPE03
           CostRef->PE04   := nPE04

           CostRef->PE10   := nPE10
           CostRef->PELE1  := nPELE1
           CostRef->PE11   := nPE11
           CostRef->PE12   := nPE12
           CostRef->PE13   := nPE13
           CostRef->PE14   := nPE14

           CostRef->PE20   := nPE20
           CostRef->PELE2  := nPELE2
           CostRef->PE21   := nPE21
           CostRef->PE22   := nPE22
           CostRef->PE23   := nPE23
           CostRef->PE24   := nPE24


           CostRef->( dbCommit() )
         endif
      endif
   enddo
   if lPerluPACK
      CostRef->( __dbPack() )
   endif
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
   ReffPrint()
   close all
return nil

func Fcode(xcode)
     if xCode="00"
        ket1 ="Local.   "
        ket2 ="Export.  "
     else
        ket1 ="Ing.     "
        ket2 ="F.Dry.   "
     endif
     return''

function ReffPrint()
   local cPrint
   local nWidth  := 118
   local nSW     := 0
   local nPage   := 0
   local GetList := {}
   local cClr    := setcolor()
   local cScr    := savescreen()
   set date British

   do while .T.
      cPrint := alert ( " Edit List  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "Reff.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)
      CostRef-> ( dbGoTop () )
      do while ! CostRef-> ( EOF() )
        if nSW == 0
           nPage++
           @ 1,1   say CostRef->Desc1
           @ 2,1   say CostRef->Desc2
           @ 3,50  say "Edit List Refference Master"
           @ 4,109 say "Page : "
           @ 4,117 say nPage picture "99"
           @ 5,1   say repli ( "=",nWidth )
           *                    1         2         3         4         5         6         7         8         9         0         1         2         3
           *          01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123
           @ 6,1  say "|  Code  |          Description 1          |          Description 2          |    Periode               |    Rate    |"
           @ 7,1  say "|        |                                 |                                 |                          |            |"
           *              xxxx    xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx   xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx   xxxxxxxx xxxxxxxxxxxxxxxx  999,999.99
           @ 8,1  say repli ( "=",nWidth )
           nSW := 1
        endif
        @ prow()+1,1   say "|"
        @ prow()  ,4   say CostRef->Code    picture "@!"
        @ prow()  ,10  say "|"
        @ prow()  ,12  say CostRef->Desc1   picture "@!"
        @ prow()  ,44  say "|"
        @ prow()  ,46  say CostRef->Desc2   picture "@!"
        @ prow()  ,78  say "|"
        @ prow()  ,80  say CostRef->Per     picture "@!"
        @ prow()  ,89  say CostRef->Period  picture "@!"
        @ prow()  ,105 say "|"
        @ prow()  ,107 say CostRef->Rate0   picture "@z 999,999.99"
        @ prow()  ,118 say "|"
        if prow() >= 55  .or. CostRef->( EOF() )
          @ prow()+1,1 say repli ( "=",nWidth )
          nSW := 0
          eject
        endif
        CostRef->( dbSkip() )
      enddo
      @ prow()+1,1 say repli ( "=",nWidth )
      eject
      @prow(), pcol() say chr(18)
      set print off
      set device to screen
      set console on
      set cursor on

      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"Reff.prn")
         fErase ( "Reff.prn" )
      endif
      setcolor ( cClr )
      restscreen (0,0,maxrow(),maxcol(), cScr )
      exit
   enddo
return nil

