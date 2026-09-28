#include "Stock.ch"
#define K_PLUS 43
#define K_MINUS 45

function Edit()
  local cClr := setcolor()
  local cScr := savescreen()
  local nSelect

  do while .T.
     set key 4  to Rkey
     set key 19 to Lkey
     sbox(7,27,10,43,"gr+/bg+,w+/br+")
     @ 8      ,28 prompt " File RMMast "
     @ row()+1,28 prompt " File FGMast "

     menu to nSelect
     set key 4  to
     set key 19 to

     do case
       case nSelect == 0
         exit
       case nSelect == 1
         Edit1()
       case nSelect == 2
         Edit2()
    endcase
  enddo
  restscreen (0,0,maxrow(),maxcol(),cScr)
  setcolor(cClr)

return nil

function Edit1()
   local cClr   := setcolor()
   local cScr   := savescreen()
   local nRow,nCol,X,FieldSkrg,MEdit
   local Tabel  :=TBrowseDB()
   local aKolom[17],aPict[17]
   local GetList := {}
   set date british
   set score off
   dataDict()
   sele RMMast

   aKolom[1]  := "RMMast->Type"
   aKolom[2]  := "RMMast->ID"
   aKolom[3]  := "RMMast->RMCode"
   aKolom[4]  := "RMMast->Desc"
   aKolom[5]  := "RMMast->Type_Curr"
   aKolom[6]  := "RMMast->USD0"
   aKolom[7]  := "RMMast->USDLE"
   aKolom[8]  := "RMMast->USD1"
   aKolom[9]  := "RMMast->USD2"
   aKolom[10]  := "RMMast->USD3"
   aKolom[11] := "RMMast->USD4"
   aKolom[12] := "RMMast->RP0"
   aKolom[13] := "RMMast->RPLE"
   aKolom[14] := "RMMast->RP1"
   aKolom[15] := "RMMast->RP2"
   aKolom[16] := "RMMast->RP3"
   aKolom[17] := "RMMast->RP4"

   aPict[1]  := "@!"
   aPict[2]  := "@!"
   aPict[3]  := "@!"
   aPict[4]  := "@!"
   aPict[5]  := "@!"
   aPict[6]  := "@ 999,999,999.99"
   aPict[7]  := "@ 999,999,999.99"
   aPict[8]  := "@ 999,999,999.99"
   aPict[9]  := "@ 999,999,999.99"
   aPict[10]  := "@ 999,999,999.99"
   aPict[11] := "@ 999,999,999.99"
   aPict[12] := "@ 999,999,999.99"
   aPict[13] := "@ 999,999,999.99"
   aPict[14] := "@ 999,999,999.99"
   aPict[15] := "@ 999,999,999.99"
   aPict[16] := "@ 999,999,999.99"
   aPict[17] := "@ 999,999,999.99"

   sbox(18,5,21,75,"gr+/r+")
   @ 19     ,7  say "F1    -> Cari Code RM"
   @ row()+1,7  say "F2    -> Cari Nama RM"
   @ 19     ,32 say "ENTER -> Edit        "
   @ row()+1,32 say "DEL   -> Hapus/Recall"
   @ 19     ,57 say "Alt+P -> P a c k "
   @ row()+1,57 say "ESC   -> Exit "  &&"Lock (+/-)"

   Tabel:Addcolumn(TBColumnNew("Type",{||tran(RMMast->Type,aPict[1])}))
   Tabel:Addcolumn(TBColumnNew("Id",{||tran(RMMast->Id,aPict[2])}))
   Tabel:Addcolumn(TBColumnNew("Code RM",{||tran(RMMast->RMCode,aPict[3])}))
   Tabel:Addcolumn(TBColumnNew("Description",{||tran(RMMast->Desc,aPict[4])}))
   Tabel:Addcolumn(TBColumnNew("Curr",{||tran(RMMast->Type_Curr,aPict[5])}))
   Tabel:Addcolumn(TBColumnNew("USD Current",{||tran(RMMast->USD0,aPict[6])}))
   Tabel:Addcolumn(TBColumnNew("USD LE",{||tran(RMMast->USDLE,aPict[7])}))
   Tabel:Addcolumn(TBColumnNew("USD #1 Qtr",{||tran(RMMast->USD1,aPict[8])}))
   Tabel:Addcolumn(TBColumnNew("USD #2 Qtr",{||tran(RMMast->USD2,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("USD #3 Qtr",{||tran(RMMast->USD3,aPict[10])}))
   Tabel:Addcolumn(TBColumnNew("USD #4 Qtr",{||tran(RMMast->USD4,aPict[11])}))
   Tabel:Addcolumn(TBColumnNew("Rp. Current",{||tran(RMMast->RP0,aPict[12])}))
   Tabel:Addcolumn(TBColumnNew("Rp. LE",{||tran(RMMast->RPLE,aPict[13])}))
   Tabel:Addcolumn(TBColumnNew("Rp. #1 Qtr",{||tran(RMMast->RP1,aPict[14])}))
   Tabel:Addcolumn(TBColumnNew("Rp. #2 Qtr",{||tran(RMMast->RP2,aPict[15])}))
   Tabel:Addcolumn(TBColumnNew("Rp. #3 Qtr",{||tran(RMMast->RP3,aPict[16])}))
   Tabel:Addcolumn(TBColumnNew("Rp. #4 Qtr",{||tran(RMMast->RP4,aPict[17])}))

   Tabel:HeadSep:=chr(205)
   Tabel:ColSep:=chr(179)
   Tabel:colorSpec:="bg+/b+,gr+/r+"
   Tabel:Autolite:= .F.
   Tabel:nTop:=3
   Tabel:nLeft:=6
   Tabel:nBottom:=15
   Tabel:nRight:=74
   Tabel:Freeze:=1

   sbox(2,5,16,75,"w+/b+")
   setcursor(0)
   do while .T.
      Tabel:ForceStable()
      nRow := Row()
      nCOl := Col()
      cClr := setcolor()
      setcolor("r+/B")
      @ 16,58 say "Status -> "+if(deleted(),"<DEL> ","<NORM>")
      setcolor(cClr)
      devPos(nRow,nCol)

      X:=SlowFlash(Tabel)

      do case
         case Kursor(Tabel,X)
         case X == K_ESC
              exit
         case X == K_F1
              Tabel:goTop()
              Tabel:forceStable()
              CariRMCode()
              Tabel:refreshAll()
         case X == K_F2
              Tabel:goTop()
              Tabel:forceStable()
              CariNamaRM()
              Tabel:refreshAll()
         case X == K_ENTER
              if Tabel:colPos == 3 &&.or. Tabel:colPos == 2
                 tone(700,1)
                 tone(300,1)
                 loop
              endif
              FieldSkrg := aKolom[Tabel:colPos]
              MEdit := &FieldSkrg
              setcursor(1)
              @ row(), col() get MEdit pict aPict[Tabel:colPos]
              read
              setcursor(0)
              if lastkey()==K_ESC
                 loop
              endif
              repl &FieldSkrg with MEdit
              Tabel:refreshCurrent()
         case X == K_DEL
              if deleted()
                 recall
              else
                 delete
              endif
              tone(700,1)
              Tabel:refreshCurrent()
         case X == K_ALT_P
              Hapus()
              Tabel:refreshALL()
         case X == K_PLUS .or. X == K_MINUS
              if X == K_PLUS
                 if Tabel:leftvisible > 0
                    Tabel:freeze++
                 endif
              else
                 if Tabel:freeze > 0
                    Tabel:freeze--
                 endif
              endif
              if Tabel:colpos <= Tabel:freeze
                 Tabel:dehilite()
                 Tabel:colpos := Tabel:freeze+1
              endif
      endcase
   enddo
   setcursor(1)
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return Nil


function Edit2()
   local cClr   := setcolor()
   local cScr   := savescreen()
   local nRow,nCol,X,FieldSkrg,MEdit
   local Tabel  :=TBrowseDB()
   local aKolom[42],aPict[42]
   local GetList := {}
   set date british
   set score off
   dataDict()
   sele FGMast

   
   aKolom[1]  := "FGMast->FGCode"
   aKolom[2]  := "FGMast->Desc"
   aKolom[3]  := "FGMast->Active"
   aKolom[4]  := "FGMast->Level"
   aKolom[5]  := "FGMast->PrType1"
   aKolom[6]  := "FGMast->PrType2"
   aKolom[7]  := "FGMast->Batch"
   aKolom[8]  := "FGMast->HrgJual"
   aKolom[9]  := "FGMast->Peckp"
   aKolom[10] := "FGMast->Pesmg"
   aKolom[11] := "FGMast->Pesby"
   aKolom[12] := "FGMast->Peplg"

   aKolom[13] := "FGMast->UC0"
   aKolom[14] := "FGMast->UCLE"
   aKolom[15] := "FGMast->UC1"
   aKolom[16] := "FGMast->UC2"
   aKolom[17] := "FGMast->UC3"
   aKolom[18] := "FGMast->UC4"

   aKolom[19] := "FGMast->Price00"
   aKolom[20] := "FGMast->PriceLE"
   aKolom[21] := "FGMast->Price01"
   aKolom[22] := "FGMast->Price02"
   aKolom[23] := "FGMast->Price03"
   aKolom[24] := "FGMast->Price04"

   aKolom[25] := "FGMast->Price10"
   aKolom[26] := "FGMast->PriceLE1"
   aKolom[27] := "FGMast->Price11"
   aKolom[28] := "FGMast->Price12"
   aKolom[29] := "FGMast->Price13"
   aKolom[30] := "FGMast->Price14"

   aKolom[31] := "FGMast->Price20"
   aKolom[32] := "FGMast->PriceLE2"
   aKolom[33] := "FGMast->Price21"
   aKolom[34] := "FGMast->Price22"
   aKolom[35] := "FGMast->Price23"
   aKolom[36] := "FGMast->Price24"

   aKolom[37] := "FGMast->Price20"
   aKolom[38] := "FGMast->PriceLE2"
   aKolom[39] := "FGMast->Price21"
   aKolom[40] := "FGMast->Price22"
   aKolom[41] := "FGMast->Price23"
   aKolom[42] := "FGMast->Price24"



   aPict[1]  := "@!"
   aPict[2]  := "@!"
   aPict[3]  := "@!"
   aPict[4]  := "@!"
   aPict[5]  := "@!"
   aPict[6]  := "@!"
   aPict[7]  := "@z 999,999"
   aPict[8]  := "@z 999,999,999.99"
   aPict[9]  := "@z 999,999.99"
   aPict[10] := "@z 999,999.99"
   aPict[11] := "@z 999,999.99"
   aPict[12] := "@z 999,999.99"

   aPict[13] := "@z 999,999,999.99"
   aPict[14] := "@z 999,999,999.99"
   aPict[15] := "@z 999,999,999.99"
   aPict[16] := "@z 999,999,999.99"
   aPict[17] := "@z 999,999,999.99"
   aPict[18] := "@z 999,999,999.99"

   aPict[19] := "@z 999,999,999.99"
   aPict[20] := "@z 999,999,999.99"
   aPict[21] := "@z 999,999,999.99"
   aPict[22] := "@z 999,999,999.99"
   aPict[23] := "@z 999,999,999.99"
   aPict[24] := "@z 999,999,999.99"

   aPict[25] := "@z 999,999,999.99"
   aPict[26] := "@z 999,999,999.99"
   aPict[27] := "@z 999,999,999.99"
   aPict[28] := "@z 999,999,999.99"
   aPict[29] := "@z 999,999,999.99"
   aPict[30] := "@z 999,999,999.99"

   aPict[31] := "@z 999,999,999.99"
   aPict[32] := "@z 999,999,999.99"
   aPict[33] := "@z 999,999,999.99"
   aPict[34] := "@z 999,999,999.99"
   aPict[35] := "@z 999,999,999.99"
   aPict[36] := "@z 999,999,999.99"

   aPict[37] := "@z 999,999,999.99"
   aPict[38] := "@z 999,999,999.99"
   aPict[39] := "@z 999,999,999.99"
   aPict[40] := "@z 999,999,999.99"
   aPict[41] := "@z 999,999,999.99"
   aPict[42] := "@z 999,999,999.99"


   sbox(18,5,21,75,"gr+/r+")
   @ 19     ,7  say "F1    -> Cari Code FG"
   @ row()+1,7  say "F2    -> Cari FG Desc"
   @ 19     ,32 say "DEL   -> Hapus/Recall"
   @ row()+1,32 say "ENTER -> Edit        "
   @ 19     ,57 say "ESC   -> Exit "
   @ row()+1,57 say "Alt+P -> P a c k"

   Tabel:Addcolumn(TBColumnNew("Code FG",{||tran(FGMast->FGCode,aPict[1])}))
   Tabel:Addcolumn(TBColumnNew("Desc",{||tran(FGMast->Desc,aPict[2])}))
   Tabel:Addcolumn(TBColumnNew("Active",{||tran(FGMast->Active,aPict[3])}))
   Tabel:Addcolumn(TBColumnNew("Level",{||tran(FGMast->Level,aPict[4])}))
   Tabel:Addcolumn(TBColumnNew("Type1",{||tran(FGMast->PrType1,aPict[5])}))
   Tabel:Addcolumn(TBColumnNew("Type2",{||tran(FGMast->PrType2,aPict[6])}))
   Tabel:Addcolumn(TBColumnNew("Batch",{||tran(FGMast->Batch,aPict[7])}))
   Tabel:Addcolumn(TBColumnNew("Hrg Jual",{||tran(FGMast->HrgJual,aPict[8])}))

   Tabel:Addcolumn(TBColumnNew("PE Ckp",{||tran(FGMast->peckp,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PE Smg",{||tran(FGMast->pesmg,aPict[10])}))
   Tabel:Addcolumn(TBColumnNew("PE Sby",{||tran(FGMast->pesby,aPict[11])}))
   Tabel:Addcolumn(TBColumnNew("PE Plg",{||tran(FGMast->peplg,aPict[12])}))

   Tabel:Addcolumn(TBColumnNew("U.Cost Curr",{||tran(FGMast->UC0,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("U.Cost LE",{||tran(FGMast->UCLE,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("U.Cost #1 Qtr",{||tran(FGMast->UC1,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("U.Cost #2 Qtr",{||tran(FGMast->UC2,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("U.Cost #3 Qtr",{||tran(FGMast->UC3,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("U.Cost #4 Qtr",{||tran(FGMast->UC4,aPict[9])}))

   Tabel:Addcolumn(TBColumnNew("PrCKP Curr",{||tran(FGMast->Price00,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrCKP LE",{||tran(FGMast->PriceLE,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrCKP #1 Qtr",{||tran(FGMast->Price01,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrCKP #2 Qtr",{||tran(FGMast->Price02,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrCKP #3 Qtr",{||tran(FGMast->Price03,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrCKP #4 Qtr",{||tran(FGMast->Price04,aPict[9])}))

   Tabel:Addcolumn(TBColumnNew("PrSMG Curr",{||tran(FGMast->Price10,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSMG LE",{||tran(FGMast->PriceLE1,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSMG #1 Qtr",{||tran(FGMast->Price11,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSMG #2 Qtr",{||tran(FGMast->Price12,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSMG #3 Qtr",{||tran(FGMast->Price13,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSMG #4 Qtr",{||tran(FGMast->Price14,aPict[9])}))

   Tabel:Addcolumn(TBColumnNew("PrSBY Curr",{||tran(FGMast->Price20,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSBY LE",{||tran(FGMast->PriceLE2,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSBY #1 Qtr",{||tran(FGMast->Price21,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSBY #2 Qtr",{||tran(FGMast->Price22,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSBY #3 Qtr",{||tran(FGMast->Price23,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrSBY #4 Qtr",{||tran(FGMast->Price24,aPict[9])}))

   Tabel:Addcolumn(TBColumnNew("PrPLG Curr",{||tran(FGMast->Price30,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrPLG LE",{||tran(FGMast->PriceLE3,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrPLG #1 Qtr",{||tran(FGMast->Price31,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrPLG #2 Qtr",{||tran(FGMast->Price32,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrPLG #3 Qtr",{||tran(FGMast->Price33,aPict[9])}))
   Tabel:Addcolumn(TBColumnNew("PrPLG #4 Qtr",{||tran(FGMast->Price34,aPict[9])}))


   Tabel:HeadSep:=chr(205)
   Tabel:ColSep:=chr(179)
   Tabel:colorSpec:="bg+/b+,gr+/r+"
   Tabel:Autolite:= .F.
   Tabel:nTop:=3
   Tabel:nLeft:=6
   Tabel:nBottom:=15
   Tabel:nRight:=74
   Tabel:freeze:=1

   sbox(2,5,16,75,"w+/b+")
   setcursor(0)
   do while .T.
      Tabel:ForceStable()
      nRow := Row()
      nCOl := Col()
      cClr := setcolor()
      setcolor("r+/B")
      @ 16,58 say "Status -> "+if(deleted(),"<DEL> ","<NORM>")
      setcolor(cClr)
      devPos(nRow,nCol)

      X:=SlowFlash(Tabel)

      do case
         case Kursor(Tabel,X)
         case X == K_ESC
              exit
         case X == K_F1
              Tabel:goTop()
              Tabel:forceStable()
              CariFGCode()
              Tabel:refreshAll()
         case X == K_F2
              Tabel:goTop()
              Tabel:forceStable()
              CariNamaFG()
              Tabel:refreshAll()
         case X == K_ENTER
              if Tabel:colPos == 1 &&.or. Tabel:colPos == 2
                 tone(700,1)
                 tone(300,1)
                 loop
              endif
              FieldSkrg := aKolom[Tabel:colPos]
              MEdit := &FieldSkrg
              setcursor(1)
              @ row(), col() get MEdit pict aPict[Tabel:colPos]
              read
              setcursor(0)
              if lastkey()==K_ESC
                 loop
              endif
              repl &FieldSkrg with MEdit
              Tabel:refreshCurrent()
         case X == K_DEL
              if deleted()
                 recall
              else
                 delete
              endif
              tone(700,1)
              Tabel:refreshCurrent()
         case X == K_ALT_P
              Hapus()
              Tabel:refreshALL()
         case X == K_PLUS .or. X == K_MINUS
              if X == K_PLUS
                 if Tabel:leftvisible > 0
                    Tabel:freeze++
                 endif
              else
                 if Tabel:freeze > 0
                    Tabel:freeze--
                 endif
              endif
              if Tabel:colpos <= Tabel:freeze
                 Tabel:dehilite()
                 Tabel:colpos := Tabel:freeze+1
              endif
      endcase
   enddo
   setcursor(1)
   setcolor ( cClr )
   restscreen (0,0,maxrow(),maxcol(), cScr )
   close all
return Nil

