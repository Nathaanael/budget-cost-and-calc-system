#include "Stock.CH"

function Synonim()
   local cFGCode,cRMCode,cDesc,cDesc1,cDesc2,cDrive,cFGCode1
   local lSudahAda
   local lPerluPACK := .F.
   local GetList  :={}
   local cClr:=setcolor()
   local cScr:=savescreen()
   set score off
   dataDict()
   if alert ( " Please make sure Mapping Drive W ",{" Yes "," No "} ) == 1
      sele FGMast1
      zap
      appe from w:FGMast
      do while .T.
         sbox(4,10,14,68,"gr+/b+,w+/bg+")
         @ 5,25 say "Maintenance Synonim"
         @ 6,11 to 6,67
         Synonim->( dbGoBottom() )
         Synonim->( dbSkip() )
         cRMCode   := Synonim->RMCode
         cFGCode   := Synonim->FGCode

         @ 08,13 say  "   Code Raw Material : " get cRMCode    picture "@!"               when myMessage ( " Masukkan Code Noodle, atau tekan Esc untuk keluar ..." ) valid chkCodeRM ( @cRMCode )
         @ 10,13 say  " Code Finished Goods : " get cFGCode    picture "@!"               when .F.
         read
         RMMast->( dbSeek(cRMCode) )
         cDesc := RMMast->Desc
         @ 09,37 say cDesc  picture "@!"
         if lastkey() == K_ESC
            exit
         else
            if cRMCode == Synonim->RMCode
              beep()
              alert( 'Code RM Tidak Boleh Kosong .....')
              loop
            endif
            if ( lSudahAda := Synonim-> ( dbSeek ( cRMCode ) ) )
               cFGCode  := Synonim->FGCode
            endif
            if lastkey() == K_ESC
               exit
            endif
            @ 10,13 say  " Code Finished Goods : " get cFGCode    picture "@!"  when myMessage ( " Masukkan Code Finished Goods, atau tekan Esc untuk keluar ..." )   valid chkCodeFG1 ( @cFGCode )
            read
            if lastkey() == K_ESC
               exit
            endif
            FGMast1->( dbSeek(cFGCode) )
            cDesc1 := FGMast1->Desc
            @ 11,37 say cDesc1  picture "@!"
            if lSudahAda
               if Synonim->( deleted() )
                  clear gets
                  if alert ( "Data sudah dihapus !!", { " Batal ", " Kembali "} ) == 1
                     Synonim->( dbRecall() )
                  endif
                  loop
               endif
               if alert ( " Menu Transaksi ", { " Edit ", " Hapus " } ) == 2
                  clear gets
                  if alert ( " Benar akan dihapus ? ", { " Yes ", " No " } ) == 1
                     Synonim->( dbDelete() )
                     lPerluPACK := .T.
                  endif
                  loop
               endif
            endif
            read
            if ( ! lastkey() == K_ESC ) .and. ( alert ( " Data Sudah Benar ? " , { " Yes ", " No " } ) == 1 )
               if ! lSudahAda
                  Synonim->( dbAppend() )
                  Synonim->RMCode := cRMCode
               endif
               Synonim->RMDesc   := cDesc
               Synonim->FGCode   := cFGCode
               Synonim->FGDesc   := cDesc1
               Synonim->( dbCommit() )
            endif
         endif
      enddo
      if lPerluPACK
         Synonim->( __dbPack() )
      endif
      SyPrint()
   endif
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
   close all
return nil

function SyPrint()
   local cPrint,cFGCode
   local cRMCode  := space(7)
   local cRMCode1 := space(7)
   local cRMCode2 := space(7)
   local nWidth    := 64
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
      @ 18,41 say "  Code RM : " get cRMCode1 picture "@!"
      @ 19,41 say "       To : " get cRMCode2 picture "@!"
      read
      if lastkey() == K_ESC
         exit
      endif
      cPrint := alert ( " Output Device  : ", { " View ", " Printer ", " Cancel " } )
      if cPrint == 3
        exit
      endif
      if cPrint == 1
        set print to "Synonim.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(15)

      select Synonim
      set filter to Synonim->RMCode >= cRMCode1 .and. Synonim->RMCode <= cRMCode2

      Synonim->( dbGoTop() )
      do while ! Synonim-> ( EOF() )
        set device to screen
        sbox( 20,24,22,51,"GR+/bG+,W+/BR+")
        setcolor( "gr+*/bg,w+/br+" )
        @ 21,25  say " Wait for a minutes ..... "
        set device to print
        cRMCode := Synonim->RMCode

        if nSW == 0
             @ prow()+1,1   say CostRef->Desc1
             @ prow()+1,1   say CostRef->Desc2
             @ prow()+1,28  say "DAFTAR SYNONIM"
             nPage++
             @ prow(),52 say "Page : "
             @ prow(),60 say nPage picture "999"
             @ prow()+1,1   say repli ( '=',nWidth )
             *                            1         2         3         4         5         6         7         8
             *                  012345678901234567890123456789012345678901234567890123456789012345678901234567890123456
             @ prow()+1,1   say "|     Raw Material             |         Finished Goods        |"
             *                     xxxxxxx xxxxxxxxxxxxxxxxxxxx    xxxxxxx xxxxxxxxxxxxxxxxxxxx
             @ prow()+1,1   say repli( '=',nWidth )
             nSW := 1
        endif
        @ prow()+1,1 say "|"
        if nSW1 == 0
            @ prow()  ,3  say cRMCode                    picture "@!"
            RMMast->( dbSeek(cRMCode) )
            @ prow()  ,11 say substr(RMMast->Desc,1,20)  picture "@!"
            nSW1 := 1
        endif
        @ prow()  ,32  say "|"
        @ prow()  ,35  say Synonim->FGCode                picture "@!"
        FGMast1->( dbSeek( Synonim->FGCode ) )
        @ prow()  ,43  say substr(FGMast1->Desc,1,20)      picture "@!"
        @ prow()  ,64  say "|"
        Synonim->( dbSkip() )
        if cRMCode # Synonim->RMCode
            nSW1 := 0
        else
            loop
        endif
        if Synonim->( Eof() )
             exit
        endif
        if prow() >= 55  .or. Synonim->( EOF() )
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
         FileRead (0,0,24,79,"Synonim.prn")
         fErase ( "Synonim.prn" )
      endif
      exit
   enddo
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return nil

