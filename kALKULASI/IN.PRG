# Include "Stock.ch"

function Entry()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect
  do while .T.
     set key 4  to Rkey
     set key 19 to Lkey
     sbox(8,27,11,43,"gr+/bg+,w+/br+")
     @ 9 ,28 prompt " Volume Noodle "
     @ 10,28 prompt " R M  Price    "
     menu to nSelect
     set key 4  to
     set key 19 to
     do case
       case nSelect == 0
         exit
       case nSelect == 1
         Vol()
       case nSelect == 2
         RMPrc()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)
return nil

function Vol()
   local cCode,cNDLCode,cDesc
   local nJan,nFeb,nMar,nApr,nMei,nJun,nJul,nAgt,nSep,nOkt,nNov,nDes
   local nLE1,nLE2,nLE3,nLE4,nLE5,nLE6
   local lSudahAda,lPerluPack := .F.
   local GetList  :={}
   local cClr:=setcolor()
   local cScr:=savescreen()
   set score off
   dataDict()
   do while .T.
      sbox(4,10,21,70,"gr+/b+,w+/bg+")
      @ 5,27 say "Maintenance Volume Noodle"
      @ 6,11 to 6,69
      VolNDL->( dbGoBottom() )
      VolNDL->( dbSkip() )
      cCode    := VolNDL->Code
      cNDLCode := VolNDL->NDLCode
      nLE1     := VolNDL->LEJul
      nLE2     := VolNDL->LEAgt
      nLE3     := VolNDL->LESep
      nLE4     := VolNDL->LEOkt
      nLE5     := VolNDL->LENov
      nLE6     := VolNDL->LEDes
      nJan     := VolNDL->Jan
      nFeb     := VolNDL->Feb
      nMar     := VolNDL->Mar
      nApr     := VolNDL->Apr
      nMei     := VolNDL->Mei
      nJun     := VolNDL->Jun
      nJul     := VolNDL->Jul
      nAgt     := VolNDL->Agt
      nSep     := VolNDL->Sep
      nOkt     := VolNDL->Okt
      nNov     := VolNDL->Nov
      nDes     := VolNDL->Des

      @ 07,12 say  "    Area Code : " get cCode      picture "@!"            when myMessage ( " Masukkan Code Area Noodle, atau tekan Esc untuk keluar ..." ) valid chkArea ( @cCode,@cDesc )
      @ 08,12 say  "  Noodle Code : " get cNDLCode   picture "@!"            when .F.
      @ 10,12 say  "      LE Juli : " get nLE1       picture "@z 999,999.99" when .F.
      @ 11,12 say  "   LE Agustus : " get nLE2       picture "@z 999,999.99" when .F.
      @ 12,12 say  "  LE September: " get nLE3       picture "@z 999,999.99" when .F.
      @ 10,42 say  "LE Oktober  : "   get nLE4       picture "@z 999,999.99" when .F.
      @ 11,42 say  "LE November : "   get nLE5       picture "@z 999,999.99" when .F.
      @ 12,42 say  "LE Desember : "   get nLE6       picture "@z 999,999.99" when .F.

      @ 14,12 say  "      Januari : " get nJan       picture "@z 999,999.99" when .F.
      @ 15,12 say  "     Februari : " get nFeb       picture "@z 999,999.99" when .F.
      @ 16,12 say  "        Maret : " get nMar       picture "@z 999,999.99" when .F.
      @ 17,12 say  "        April : " get nApr       picture "@z 999,999.99" when .F.
      @ 18,12 say  "          Mei : " get nMar       picture "@z 999,999.99" when .F.
      @ 19,12 say  "         Juni : " get nApr       picture "@z 999,999.99" when .F.
      @ 14,42 say  "     Juli : "     get nJul       picture "@z 999,999.99" when .F.
      @ 15,42 say  "  Agustus : "     get nAgt       picture "@z 999,999.99" when .F.
      @ 16,42 say  "September : "     get nSep       picture "@z 999,999.99" when .F.
      @ 17,42 say  "  Oktober : "     get nOkt       picture "@z 999,999.99" when .F.
      @ 18,42 say  " November : "     get nNov       picture "@z 999,999.99" when .F.
      @ 19,42 say  " Desember : "     get nDes       picture "@z 999,999.99" when .F.

      read
      if lastkey() == K_ESC
         exit
      else
         if cCode == VolNDL->Code
            beep()
            alert( 'Code Area Noodle Tidak Boleh Kosong .....')
            loop
         endif
         @ 07,32 say  cDesc picture "@!"
         @ 08,12 say  "  Noodle Code : " get cNDLCode   picture "@!"            when myMessage ( " Masukkan Code Noodle, atau tekan Esc untuk keluar ..." ) valid chkCodeNdl ( @cNdlCode,@cDesc )
         @ 10,12 say  "      LE Juli : " get nLE1       picture "@z 999,999.99" when .F.
         @ 11,12 say  "   LE Agustus : " get nLE2       picture "@z 999,999.99" when .F.
         @ 12,12 say  "  LE September: " get nLE3       picture "@z 999,999.99" when .F.
         @ 10,42 say  "LE Oktober  : "   get nLE4       picture "@z 999,999.99" when .F.
         @ 11,42 say  "LE November : "   get nLE5       picture "@z 999,999.99" when .F.
         @ 12,42 say  "LE Desember : "   get nLE6       picture "@z 999,999.99" when .F.

         @ 14,12 say  "      Januari : " get nJan       picture "@z 999,999.99" when .F.
         @ 15,12 say  "     Februari : " get nFeb       picture "@z 999,999.99" when .F.
         @ 16,12 say  "        Maret : " get nMar       picture "@z 999,999.99" when .F.
         @ 17,12 say  "        April : " get nApr       picture "@z 999,999.99" when .F.
         @ 18,12 say  "          Mei : " get nMar       picture "@z 999,999.99" when .F.
         @ 19,12 say  "         Juni : " get nApr       picture "@z 999,999.99" when .F.
         @ 14,42 say  "     Juli : "     get nJul       picture "@z 999,999.99" when .F.
         @ 15,42 say  "  Agustus : "     get nAgt       picture "@z 999,999.99" when .F.
         @ 16,42 say  "September : "     get nSep       picture "@z 999,999.99" when .F.
         @ 17,42 say  "  Oktober : "     get nOkt       picture "@z 999,999.99" when .F.
         @ 18,42 say  " November : "     get nNov       picture "@z 999,999.99" when .F.
         @ 19,42 say  " Desember : "     get nDes       picture "@z 999,999.99" when .F.
         read
         if ( lSudahAda := VolNDL-> ( dbSeek ( cCode+cNDLCode ) ) )
              nLE1     := VolNDL->LEJul
              nLE2     := VolNDL->LEAgt
              nLE3     := VolNDL->LESep
              nLE4     := VolNDL->LEOkt
              nLE5     := VolNDL->LENov
              nLE6     := VolNDL->LEDes
              nJan     := VolNDL->Jan
              nFeb     := VolNDL->Feb
              nMar     := VolNDL->Mar
              nApr     := VolNDL->Apr
              nMei     := VolNDL->Mei
              nJun     := VolNDL->Jun
              nJul     := VolNDL->Jul
              nAgt     := VolNDL->Agt
              nSep     := VolNDL->Sep
              nOkt     := VolNDL->Okt
              nNov     := VolNDL->Nov
              nDes     := VolNDL->Des
         endif
         @ 08,37 say cDesc picture "@!"
         @ 10,12 say  "      LE Juli : " get nLE1       picture "@z 999,999.99" when myMessage ( " Masukkan Volume LE Juli, atau tekan Esc untuk keluar ..." )
         @ 11,12 say  "   LE Agustus : " get nLE2       picture "@z 999,999.99" when myMessage ( " Masukkan Volume LE Agustus, atau tekan Esc untuk keluar ..." )
         @ 12,12 say  "  LE September: " get nLE3       picture "@z 999,999.99" when myMessage ( " Masukkan Volume LE September, atau tekan Esc untuk keluar ..." )
         @ 10,42 say  "LE Oktober  : "   get nLE4       picture "@z 999,999.99" when myMessage ( " Masukkan Volume LE Oktober, atau tekan Esc untuk keluar ..." )
         @ 11,42 say  "LE November : "   get nLE5       picture "@z 999,999.99" when myMessage ( " Masukkan Volume LE November, atau tekan Esc untuk keluar ..." )
         @ 12,42 say  "LE Desember : "   get nLE6       picture "@z 999,999.99" when myMessage ( " Masukkan Volume LE Desember, atau tekan Esc untuk keluar ..." )

         @ 14,12 say  "      Januari : " get nJan       picture "@z 999,999.99" when myMessage ( " Masukkan Volume Januari, atau tekan Esc untuk keluar ..." )
         @ 15,12 say  "     Februari : " get nFeb       picture "@z 999,999.99" when myMessage ( " Masukkan Volume Februari, atau tekan Esc untuk keluar ..." )
         @ 16,12 say  "        Maret : " get nMar       picture "@z 999,999.99" when myMessage ( " Masukkan Volume Maret, atau tekan Esc untuk keluar ..." )
         @ 17,12 say  "        April : " get nApr       picture "@z 999,999.99" when myMessage ( " Masukkan Volume April, atau tekan Esc untuk keluar ..." )
         @ 18,12 say  "          Mei : " get nMar       picture "@z 999,999.99" when myMessage ( " Masukkan Volume Mei, atau tekan Esc untuk keluar ..." )
         @ 19,12 say  "         Juni : " get nApr       picture "@z 999,999.99" when myMessage ( " Masukkan Volume Juni, atau tekan Esc untuk keluar ..." )
         @ 14,42 say  "     Juli : "     get nJul       picture "@z 999,999.99" when myMessage ( " Masukkan Volume Juli, atau tekan Esc untuk keluar ..." )
         @ 15,42 say  "  Agustus : "     get nAgt       picture "@z 999,999.99" when myMessage ( " Masukkan Volume Agustus, atau tekan Esc untuk keluar ..." )
         @ 16,42 say  "September : "     get nSep       picture "@z 999,999.99" when myMessage ( " Masukkan Volume September, atau tekan Esc untuk keluar ..." )
         @ 17,42 say  "  Oktober : "     get nOkt       picture "@z 999,999.99" when myMessage ( " Masukkan Volume Oktober, atau tekan Esc untuk keluar ..." )
         @ 18,42 say  " November : "     get nNov       picture "@z 999,999.99" when myMessage ( " Masukkan Volume November, atau tekan Esc untuk keluar ..." )
         @ 19,42 say  " Desember : "     get nDes       picture "@z 999,999.99" when myMessage ( " Masukkan Volume Desember, atau tekan Esc untuk keluar ..." )

         if lSudahAda
           if VolNDL->( deleted() )
              clear gets
              if alert ( "Data sudah dihapus !!", { " Batal ", " Kembali "} ) == 1
                  VolNDL->( dbRecall() )
              endif
              loop
           endif
           if alert ( " Menu Transaksi ", { " Edit ", " Hapus " } ) == 2
              clear gets
              if alert ( " Benar akan dihapus ? ", { " Yes ", " No " } ) == 1
                 VolNDL->( dbDelete() )
                 lPerluPACK := .T.
              endif
              loop
           endif
         endif
         read
         if ( ! lastkey() == K_ESC ) .and. ( alert ( " Data Sudah Benar ? " , { " Yes ", " No " } ) == 1 )
           if ! lSudahAda
              VolNDL->( dbAppend() )
              VolNDL->Code    := cCode
              VolNDL->NDLCode := cNDLCode
           endif
           VolNDL->LEJul  := nLE1
           VolNDL->LEAgt  := nLE2
           VolNDL->LESep  := nLE3
           VolNDL->LEOkt  := nLE4
           VolNDL->LENov  := nLE5
           VolNDL->LEDes  := nLE6
           VolNDL->Jan    := nJan
           VolNDL->Feb    := nFeb
           VolNDL->Mar    := nMar
           VolNDL->Apr    := nApr
           VolNDL->Mei    := nMei
           VolNDL->Jun    := nJun
           VolNDL->Jul    := nJul
           VolNDL->Agt    := nAgt
           VolNDL->Sep    := nSep
           VolNDL->Okt    := nOkt
           VolNDL->Nov    := nNov
           VolNDL->Des    := nDes
           VolNDL->Total  := nJan+nFeb+nMar+nApr+nMei+nJun+nJul+nAgt+nSep+nOkt+nNov+nDes
           VolNDL->TotalLE:= nLE1+nLE2+nLE3+nLE4+nLE5+nLE6
           VolNDL->( dbCommit() )
         endif
      endif
   enddo
   if lPerluPACK
      VolNDL->( __dbPack() )
   endif
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
   VolPrint()
   close all
return nil

function VolPrint()
   local cPrint
   local nWidth  := 264
   local nSW     := 0
   local nTJan   := 0
   local nTFeb   := 0
   local nTMar   := 0
   local nTApr   := 0
   local nTMei   := 0
   local nTJun   := 0
   local nTJul   := 0
   local nTAgt   := 0
   local nTSep   := 0
   local nTOkt   := 0
   local nTNov   := 0
   local nTDes   := 0
   local nTTotal := 0
   local nTLEJul   := 0
   local nTLEAgt   := 0
   local nTLESep   := 0
   local nTLEOkt   := 0
   local nTLENov   := 0
   local nTLEDes   := 0
   local nTTotalLE := 0
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
        set print to "VolNDL.prn"
      endif
      set console off
      set print on
      set device to print
      SetPrc(0,0)
      @ prow(),pcol() say chr(27)+chr(77)+chr(15)

      VolNDL-> ( dbGoTop () )

      do while ! VolNDL-> ( EOF() )

        if nSW == 0
           nPage++
           @ 1,1   say CostRef->Desc1
           @ 2,1   say CostRef->Desc2
           @ 3,130 say "Edit List Volume Noodle "
           @ 4,1   say "Periode : "
           @ 4,pcol() say CostRef->Period picture "@!"
           @ 4,250 say "Page : "
           @ 4,258 say nPage picture "99"
           @ 5,1   say repli ( "=",nWidth )
*                    1         2         3         4         5         6         7         8         9         0         1         2         3
*          01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789
@ 6,1  say "|       Area            |           Noodle               |-------------------------------- L E -----------------------------------|"
@ 6,pcol() say "-------------------------------------------------------- A O P --------------------------------------------------------|            |"
@ 7,1  say "| Code  Description     |  Code     Description          |   Jul   |   Agt   |   Sep   |   Okt   |   Nov   |   Des   |   Total    |"
*              xx   xxxxxxxxxxxxxxx    xxxxxxx  xxxxxxxxxxxxxxxxxxxx   999,999   999,999   999,999   999,999   999,999   999,999   99,999,999
*             3         4         5         6         7         8         9         0         1         2         3         4         5         6
*            9012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234
@ 7,pcol() say "   Jan   |   Feb   |   Mar   |   Apr   |   Mei   |   Jun   |   Jul   |   Agt   |   Sep   |   Okt   |   Nov   |   Des   |   Total    |"

*                999,999   999,999   999,999   999,999   999,999   999,999   999,999   999,999   999,999   999,999   999,999   999,999   99,999,999

@8,1  say repli ( "=",nWidth )
           nSW := 1
        endif

        if VolNDL->Jan # 0 .or. VolNDL->Feb # 0 .or. VolNDL->Mar # 0 .or. VolNDL->Apr # 0 .or. ;
           VolNDL->Mei # 0 .or. VolNDL->Jun # 0 .or. VolNDL->Jul # 0 .or. VolNDL->Agt # 0 .or. ;
           VolNDL->Sep # 0 .or. VolNDL->Okt # 0 .or. VolNDL->Nov # 0 .or. VolNDL->Des # 0 .or. ;
           VolNDL->LEJul # 0 .or. VolNDL->LEAgt # 0 .or. VolNDL->LESep # 0 .or. VolNDL->LEOkt # 0 .or. VolNDL->LENov # 0 .or. VolNDL->LEDes # 0
              @ prow()+1,1   say "|"
              @ prow()  ,4   say VolNDL->Code                picture "@!"
              Area->( dbSeek(VolNDL->Code) )
              @ prow()  ,09  say substr(Area->Desc,1,15)     picture "@!"
              @ prow()  ,25  say "|"
              @ prow()  ,28  say VolNDL->NDLCode             picture "@!"
              NDLMast->( dbSeek( VolNDL->NDLCode ) )
              @ prow()  ,37  say substr(NDLMast->Desc,1,20)  picture "@!"
              @ prow()  ,58  say "|"
              @ prow()  ,60  say VolNDL->LEJul               picture "@z 999,999"
              @ prow()  ,68  say "|"
              @ prow()  ,70  say VolNDL->LEAgt               picture "@z 999,999"
              @ prow()  ,78  say "|"
              @ prow()  ,80  say VolNDL->LESep               picture "@z 999,999"
              @ prow()  ,88  say "|"
              @ prow()  ,90  say VolNDL->LEOkt               picture "@z 999,999"
              @ prow()  ,98  say "|"
              @ prow()  ,100 say VolNDL->LENov               picture "@z 999,999"
              @ prow()  ,108 say "|"
              @ prow()  ,110 say VolNDL->LEDes               picture "@z 999,999"
              @ prow()  ,118 say "|"
              @ prow()  ,120 say VolNDL->TotalLE             picture "@z 99,999,999"
              @ prow()  ,131 say "|"
              @ prow()  ,133 say VolNDL->Jan                 picture "@z 999,999"
              @ prow()  ,141 say "|"
              @ prow()  ,143 say VolNDL->Feb                 picture "@z 999,999"
              @ prow()  ,151 say "|"
              @ prow()  ,153 say VolNDL->Mar                 picture "@z 999,999"
              @ prow()  ,161 say "|"
              @ prow()  ,163 say VolNDL->Apr                 picture "@z 999,999"
              @ prow()  ,171 say "|"
              @ prow()  ,173 say VolNDL->Mei                 picture "@z 999,999"
              @ prow()  ,181 say "|"
              @ prow()  ,183 say VolNDL->Jun                 picture "@z 999,999"
              @ prow()  ,191 say "|"
              @ prow()  ,193 say VolNDL->Jul                 picture "@z 999,999"
              @ prow()  ,201 say "|"
              @ prow()  ,203 say VolNDL->Agt                 picture "@z 999,999"
              @ prow()  ,211 say "|"
              @ prow()  ,213 say VolNDL->Sep                 picture "@z 999,999"
              @ prow()  ,221 say "|"
              @ prow()  ,223 say VolNDL->Okt                 picture "@z 999,999"
              @ prow()  ,231 say "|"
              @ prow()  ,233 say VolNDL->Nov                 picture "@z 999,999"
              @ prow()  ,241 say "|"
              @ prow()  ,243 say VolNDL->Des                 picture "@z 999,999"
              @ prow()  ,251 say "|"
              @ prow()  ,253 say VolNDL->Total               picture "@z 99,999,999"
              @ prow()  ,264 say "|"
              nTLEJul := nTLEJul + VolNDL->LEJul
              nTLEAgt := nTLEAgt + VolNDL->LEAgt
              nTLESep := nTLESep + VolNDL->LESep
              nTLEOkt := nTLEOkt + VolNDL->LEOkt
              nTLENov := nTLENov + VolNDL->LENov
              nTLEDes := nTLEDes + VolNDL->LEDes
              nTTotalLE := nTTotalLE + VolNDL->TotalLE
              nTJan := nTJan + VolNDL->Jan
              nTFeb := nTFeb + VolNDL->Feb
              nTMar := nTMar + VolNDL->Mar
              nTApr := nTApr + VolNDL->Apr
              nTMei := nTMei + VolNDL->Mei
              nTJun := nTJun + VolNDL->Jun
              nTJul := nTJul + VolNDL->Jul
              nTAgt := nTAgt + VolNDL->Agt
              nTSep := nTSep + VolNDL->Sep
              nTOkt := nTOkt + VolNDL->Okt
              nTNov := nTNov + VolNDL->Nov
              nTDes := nTDes + VolNDL->Des
              nTTotal := nTTotal + VolNDL->Total

              if prow() >= 55
                 @ prow()+1,1 say repli ( "=",nWidth )
                 nSW := 0
                 eject
              endif
              if VolNDL->( EOF() )
                 exit
              endif
        endif
        VolNDL->( dbSkip() )
      enddo
      @ prow()+1,1 say repli ( "=",nWidth )
      @ prow()+1,1   say "|"
      @ prow()  ,58  say "|"
      @ prow()  ,60  say nTLEJul               picture "@z 999,999"
      @ prow()  ,68  say "|"
      @ prow()  ,70  say nTLEAgt               picture "@z 999,999"
      @ prow()  ,78  say "|"
      @ prow()  ,80  say nTLESep               picture "@z 999,999"
      @ prow()  ,88  say "|"
      @ prow()  ,90  say nTLEOkt               picture "@z 999,999"
      @ prow()  ,98  say "|"
      @ prow()  ,100 say nTLENov               picture "@z 999,999"
      @ prow()  ,108 say "|"
      @ prow()  ,110 say nTLEDes               picture "@z 999,999"
      @ prow()  ,118 say "|"
      @ prow()  ,120 say nTTotalLE             picture "@z 99,999,999"
      @ prow()  ,131 say "|"
      @ prow()  ,133 say nTJan                 picture "@z 999,999"
      @ prow()  ,141 say "|"
      @ prow()  ,143 say nTFeb                 picture "@z 999,999"
      @ prow()  ,151 say "|"
      @ prow()  ,153 say nTMar                 picture "@z 999,999"
      @ prow()  ,161 say "|"
      @ prow()  ,163 say nTApr                 picture "@z 999,999"
      @ prow()  ,171 say "|"
      @ prow()  ,173 say nTMei                 picture "@z 999,999"
      @ prow()  ,181 say "|"
      @ prow()  ,183 say nTJun                 picture "@z 999,999"
      @ prow()  ,191 say "|"
      @ prow()  ,193 say nTJul                 picture "@z 999,999"
      @ prow()  ,201 say "|"
      @ prow()  ,203 say nTAgt                 picture "@z 999,999"
      @ prow()  ,211 say "|"
      @ prow()  ,213 say nTSep                 picture "@z 999,999"
      @ prow()  ,221 say "|"
      @ prow()  ,223 say nTOkt                 picture "@z 999,999"
      @ prow()  ,231 say "|"
      @ prow()  ,233 say nTNov                 picture "@z 999,999"
      @ prow()  ,241 say "|"
      @ prow()  ,243 say nTDes                 picture "@z 999,999"
      @ prow()  ,251 say "|"
      @ prow()  ,253 say nTTotal               picture "@z 99,999,999"
      @ prow()  ,264 say "|"
      @ prow()+1,1 say repli ( "=",nWidth )
      eject
      @prow(), pcol() say chr(27)+chr(80)+chr(18)
      set print off
      set device to screen
      set console on
      set cursor on

      if cPrint == 1
         set printer to
         FileRead (0,0,24,79,"VolNDL.prn")
         fErase ( "VolNDL.prn" )
      endif
      setcolor ( cClr )
      restscreen (0,0,maxrow(),maxcol(), cScr )
      exit
   enddo
return nil

function RMPrc()
   local cRMCode,cId
   local cDesc
   local nUSD0,nUSD1,nUSD2,nUSD3,nUSD4,nUSDLE
   local nRP0,nRP1,nRP2,nRP3,nRP4,nRPLE
   local lSudahAda
   local lPerluPACK := .F.
   local GetList  :={}
   local cClr:=setcolor()
   local cScr:=savescreen()
   set score off
   dataDict()
   do while .T.
      sbox(4,09,18,70,"gr+/b+,w+/bg+")
      @ 5,27 say ' Entry Raw Material Price'
      @ 6,10 to 6,69
      RMMast->( dbGoBottom() )
      RMMast->( dbSkip() )
      cRMCode   := RMMast->RMCode
      cId       := RMMast->Id
      nUSD0     := RMMast->USD0
      nUSDLE    := RMMast->USDLE
      nUSD1     := RMMast->USD1
      nUSD2     := RMMast->USD2
      nUSD3     := RMMast->USD3
      nUSD4     := RMMast->USD4
      nRP0      := RMMast->RP0
      nRPLE     := RMMast->RPLE
      nRP1      := RMMast->RP1
      nRP2      := RMMast->RP2
      nRP3      := RMMast->RP3
      nRP4      := RMMast->RP4
      @ 08,10 say  "        Code RM : " get cRMCode   picture "@!"             when myMessage ( " Masukkan Code Raw Material, atau tekan Esc untuk keluar ..." ) valid chkCodeRM ( @cRMCode )
      @ 08,38 say  " Id : "             get cId       picture "@!"             when .F.
      @ 11,10 say  "    Current USD : " get nUSD0     picture "@z 999,999.99"  when .F.
      @ 12,10 say  "        L E USD : " get nUSDLE    picture "@z 999,999.99"  when .F.
      @ 13,10 say  "       Qtr1 USD : " get nUSD1     picture "@z 999,999.99"  when .F.
      @ 14,10 say  "       Qtr2 USD : " get nUSD2     picture "@z 999,999.99"  when .F.
      @ 15,10 say  "       Qtr3 USD : " get nUSD3     picture "@z 999,999.99"  when .F.
      @ 16,10 say  "       Qtr4 USD : " get nUSD4     picture "@z 999,999.99"  when .F.
      @ 11,41 say  "Current Rp. : " get nRP1          picture "@z 9,999,999.99"   when .F.
      @ 12,41 say  "L E     Rp. : " get nRPLE         picture "@z 9,999,999.99"   when .F.
      @ 13,41 say  "Qtr1    Rp. : " get nRP1          picture "@z 9,999,999.99"   when .F.
      @ 14,41 say  "Qtr2    Rp. : " get nRP2          picture "@z 9,999,999.99"   when .F.
      @ 15,41 say  "Qtr3    Rp. : " get nRP3          picture "@z 9,999,999.99"   when .F.
      @ 16,41 say  "Qtr4    Rp. : " get nRP4          picture "@z 9,999,999.99"   when .F.
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
             cId       := RMMast->Id
             nUSD0     := RMMast->USD0
             nUSDLE    := RMMast->USDLE
             nUSD1     := RMMast->USD1
             nUSD2     := RMMast->USD2
             nUSD3     := RMMast->USD3
             nUSD4     := RMMast->USD4
             nRP0      := RMMast->RP0
             nRPLE     := RMMast->RPLE
             nRP1      := RMMast->RP1
             nRP2      := RMMast->RP2
             nRP3      := RMMast->RP3
             nRP4      := RMMast->RP4
         endif
         @ 09,31 say  cDesc                              picture "@!"
         @ 08,45 say  cId                                picture "@!"
         @ 11,10 say  "    Current USD : " get nUSD0     picture "@z 999,999.99"  when myMessage ( " Masukkan Harga RM Current dalam USD, atau tekan Esc untuk keluar ..." )
         @ 12,10 say  "        L E USD : " get nUSDLE    picture "@z 999,999.99"  when myMessage ( " Masukkan Harga LE dalam USD, atau tekan Esc untuk keluar ..." )
         @ 13,10 say  "       Qtr1 USD : " get nUSD1     picture "@z 999,999.99"  when myMessage ( " Masukkan Harga RM Qtr 1 dalam USD, atau tekan Esc untuk keluar ..." )
         @ 14,10 say  "       Qtr2 USD : " get nUSD2     picture "@z 999,999.99"  when myMessage ( " Masukkan Harga RM Qtr 2 dalam USD, atau tekan Esc untuk keluar ..." )
         @ 15,10 say  "       Qtr3 USD : " get nUSD3     picture "@z 999,999.99"  when myMessage ( " Masukkan Harga RM Qtr 3 dalam USD, atau tekan Esc untuk keluar ..." )
         @ 16,10 say  "       Qtr4 USD : " get nUSD4     picture "@z 999,999.99"  when myMessage ( " Masukkan Harga RM Qtr 4 dalam USD, atau tekan Esc untuk keluar ..." )
         @ 11,41 say  "Current Rp. : " get nRP0          picture "@z 9,999,999.99"   when myMessage ( " Masukkan Harga RM Current dalam Rp., atau tekan Esc untuk keluar ..." )
         @ 12,41 say  "L E     Rp. : " get nRPLE         picture "@z 9,999,999.99"   when myMessage ( " Masukkan Harga LE dalam Rp., atau tekan Esc untuk keluar ..." )
         @ 13,41 say  "Qtr1    Rp. : " get nRP1          picture "@z 9,999,999.99"   when myMessage ( " Masukkan Harga RM Qtr 1 dalam Rp., atau tekan Esc untuk keluar ..." )
         @ 14,41 say  "Qtr2    Rp. : " get nRP2          picture "@z 9,999,999.99"   when myMessage ( " Masukkan Harga RM Qtr 2 dalam Rp., atau tekan Esc untuk keluar ..." )
         @ 15,41 say  "Qtr3    Rp. : " get nRP3          picture "@z 9,999,999.99"   when myMessage ( " Masukkan Harga RM Qtr 3 dalam Rp., atau tekan Esc untuk keluar ..." )
         @ 16,41 say  "Qtr4    Rp. : " get nRP4          picture "@z 9,999,999.99"   when myMessage ( " Masukkan Harga RM Qtr 4 dalam Rp., atau tekan Esc untuk keluar ..." )
         read
         if ( ! lastkey() == K_ESC ) .and. ( alert ( " Data Sudah Benar ? " , { " Yes ", " No " } ) == 1 )
           if cId == "  " .or. cId == "* "
              RMMast->USD0      := nUSD0
              RMMast->USDLE     := nUSDLE
              RMMast->USD1      := nUSD1
              RMMast->USD2      := nUSD2
              RMMast->USD3      := nUSD3
              RMMast->USD4      := nUSD4
              RMMast->RP0       := nRP0
              RMMast->RPLE      := nRPLE
              RMMast->RP1       := nRP1
              RMMast->RP2       := nRP2
              RMMast->RP3       := nRP3
              RMMast->RP4       := nRP4
              RMMast->( dbCommit() )
           else
              select RMMast
              set filter to RMMast->Id == cId
              RMMast->( dbGoTop() )
              do while ! RMMast->( eof() )
                 RMMast->USD0      := nUSD0
                 RMMast->USDLE     := nUSDLE
                 RMMast->USD1      := nUSD1
                 RMMast->USD2      := nUSD2
                 RMMast->USD3      := nUSD3
                 RMMast->USD4      := nUSD4
                 RMMast->RP0       := nRP0
                 RMMast->RPLE      := nRPLE
                 RMMast->RP1       := nRP1
                 RMMast->RP2       := nRP2
                 RMMast->RP3       := nRP3
                 RMMast->RP4       := nRP4
                 RMMast->( dbCommit() )
                 RMMast->( dbSkip() )
              enddo
              sele RMMast
              set filter to
           endif
         endif
      endif
   enddo
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
   close all
return nil

