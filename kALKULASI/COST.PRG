#include "stock.ch"
*Mmain()
*return

function Mmain()
  local cClr := setcolor()
  local cScr := savescreen()
  local id1  := space(1)
  local id2  := space(1)
  local id3  := space(1)
  local id4  := space(1)
  local id5  := space(1)
  local id6  := space(1)
  local id7  := space(1)
  local id8  := space(1)
  local id9  := space(1)
  local xname := space(9)
  local GetList  :={}
  local ok,xid
  set scoreboard off
  cls
  logo()
  sbox(03,03,06,27)
  DO WHILE .T.
      setcolor("gr+/b+,gr+/b+")
      @ 04,05 SAY 'User Name :          '
      @ 05,05 SAY 'Password  :          '
      setcolor("gr+/b+,gr+/b+")
      SET CURSOR ON
      @ 04,17 GET XNAME PICT '@!'
      READ
      IF LASTKEY() = 27
         OK = .F.
         EXIT
      ENDIF
      USE register
      LOCA FOR register->USERNAME = XNAME
      IF .NOT. FOUND()
         alert('User Not Yet Registered!')
         LOOP
      ENDIF
      setcolor("gr+/b+,gr+/b+")
      DO WHILE .T.
         setcolor("n/n,n/n")
         @ 05,17 GET ID1 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,17 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,18 GET ID2 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,18 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,19 GET ID3 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,19 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,20 GET ID4 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,20 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,21 GET ID5 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,21 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,22 GET ID6 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,22 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,23 GET ID7 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,23 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,24 GET ID8 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,24 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,25 GET ID9 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,25 SAY 'X'
         EXIT
      ENDDO
      XID := ID1+ID2+ID3+ID4+id5+id6+id7+id8+id9
      IF LASTKEY() = 27
         OK = .F.
         EXIT
      ENDIF
      IF ENCRYPT(XID) = register->USERID
         OK = .T.
         main()
         EXIT
      ENDIF
      alert('Wrong Password!')
   ENDDO
   restscreen (0,0,maxrow(),maxcol(),cScr)
   setcolor(cClr)
RETURN OK

FUNCTION ENCRYPT(XID)
   local a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r
   A = SUBS(XID,1,1)
   B = SUBS(XID,2,1)
   C = SUBS(XID,3,1)
   D = SUBS(XID,4,1)
   e = SUBS(XID,5,1)
   f = SUBS(XID,6,1)
   g = SUBS(XID,7,1)
   h = SUBS(XID,8,1)
   i = SUBS(XID,9,1)
   j = STR(ASC(A)+02,2)
   k = STR(ASC(B)+03,2)
   l = STR(ASC(C)+05,2)
   m = STR(ASC(D)+07,2)
   n = STR(ASC(e)+09,2)
   o = STR(ASC(f)+11,2)
   p = STR(ASC(g)+13,2)
   q = STR(ASC(h)+15,2)
   r = STR(ASC(i)+17,2)
RETURN j+k+l+m+n+o+p+q+r

Function User()
  local cClr := setcolor()
  local cScr := savescreen()
  local id1  := space(1)
  local id2  := space(1)
  local id3  := space(1)
  local id4  := space(1)
  local id5  := space(1)
  local id6  := space(1)
  local id7  := space(1)
  local id8  := space(1)
  local id9  := space(1)
  local jd1  := space(1)
  local jd2  := space(1)
  local jd3  := space(1)
  local jd4  := space(1)
  local jd5  := space(1)
  local jd6  := space(1)
  local jd7  := space(1)
  local jd8  := space(1)
  local jd9  := space(1)
  local xname := space(9)
  local xid,xjd,ok
  local GetList  :={}
    logo()
    sbox(03,03,06,27)
    @ 03,05 SAY ' New Password '
    DO WHILE .T.
      setcolor("gr+/b+,gr+/b+")
      @ 04,05 SAY 'User Name :          '
      @ 05,05 SAY 'Password  :          '
      setcolor("gr+/b+,gr+/b+")
      SET CURSOR ON
      @ 04,17 GET XNAME PICT '@!'
      READ
      IF LASTKEY() = 27
         OK = .F.
         EXIT
      ENDIF
      USE register
      LOCA FOR register->USERNAME = XNAME
      IF .NOT. FOUND()
         alert('User Not Yet Registered!')
         LOOP
      ENDIF
      setcolor("gr+/b+,gr+/b+")
      DO WHILE .T.
         setcolor("n/n,n/n")
         @ 05,17 GET ID1 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,17 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,18 GET ID2 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,18 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,19 GET ID3 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,19 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,20 GET ID4 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,20 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,21 GET ID5 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,21 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,22 GET ID6 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,22 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,23 GET ID7 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,23 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,24 GET ID8 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,24 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,25 GET ID9 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,25 SAY 'X'
         EXIT
      ENDDO
      XID := ID1+ID2+ID3+ID4+id5+id6+id7+id8+id9
      setcolor("gr+/b+,gr+/b+")
      @ 03,05 SAY ' Retype Password '
      @ 05,17 say space(9)
      DO WHILE .T.
         @ 04,17 say XNAME PICT '@!'
         setcolor("n/n,n/n")
         @ 05,17 GET jD1 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,17 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,18 GET jD2 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,18 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,19 GET jD3 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,19 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,20 GET jD4 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,20 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,21 GET jD5 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,21 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,22 GET jD6 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,22 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,23 GET jD7 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,23 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,24 GET jD8 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,24 SAY 'X'
         setcolor("n/n,n/n")
         @ 05,25 GET jD9 PICT '!'
         READ
         setcolor("gr+/n+,gr+/n+")
         IF LASTKEY() = 27 .OR. LASTKEY() = 13
            EXIT
         ENDIF
         @ 05,25 SAY 'X'
         EXIT
      ENDDO
      XjD := jD1+jD2+jD3+jD4+jd5+jd6+jd7+jd8+jd9
      IF XID = XJD
         USE Register
         LOCA FOR register->USERNAME = XNAME
         REPL USERID WITH ENCRYPT(XID)
         EXIT
      ENDIF
      alert("INCORRECT PASSWORD!")
    ENDDO
  setcolor ( cClr )
  restscreen ( 0,0,maxrow(),maxcol(), cScr )
  close all
return nil


function logo()
  dispbox( 0,0,24,79,B_DOUBLE+" ","bg+/b+")
  setcolor("bg+/b+")
  *                     1         2         3         4         5         6         7         8
  *            12345678901234567890123456789012345678901234567890123456789012345678901234567890
  @ 10,07 say '฿฿฿฿                 ฿฿               ฿฿                        ฿฿ '
  @ 11,07 say ' ฿฿                  ฿฿             ฿฿  ฿฿                      ฿฿ '
  @ 12,07 say ' ฿฿  ฿฿  ฿฿฿     ฿฿฿ ฿฿   ฿฿฿฿     ฿฿     ฿฿฿฿     ฿฿฿฿     ฿฿฿ ฿฿ '
  @ 13,07 say ' ฿฿  ฿฿ ฿   ฿฿ ฿฿    ฿฿ ฿฿    ฿฿ ฿฿฿฿฿฿ ฿฿    ฿฿ ฿฿    ฿฿ ฿฿    ฿฿ '
  @ 14,07 say ' ฿฿  ฿฿     ฿฿ ฿฿    ฿฿ ฿฿    ฿฿   ฿฿   ฿฿    ฿฿ ฿฿    ฿฿ ฿฿    ฿฿ '
  @ 15,07 say ' ฿฿  ฿฿     ฿฿ ฿฿    ฿฿ ฿฿    ฿฿   ฿฿   ฿฿    ฿฿ ฿฿    ฿฿ ฿฿    ฿฿ '
  @ 16,07 say ' ฿฿  ฿฿     ฿฿ ฿฿    ฿฿ ฿฿    ฿฿   ฿฿   ฿฿    ฿฿ ฿฿    ฿฿ ฿฿    ฿฿ '
  @ 17,07 say '฿฿฿฿ ฿฿     ฿฿   ฿฿฿ ฿฿   ฿฿฿฿     ฿฿     ฿฿฿฿     ฿฿฿฿     ฿฿฿ ฿฿ '
  @ 18,07 say 'อออออออออออออออออออออออออออออ ฿฿  ฿฿ อออออออออออออออออออออออออออออ '
  @ 19,07 say '                                ฿฿                       G R O U P '
  setcolor("bg+/b+")
  @ 22,1 to 22,78 double
  setcolor("gr+/b+")
  @ 23,3 say " BUDGET COST & SALES Program                (c) Copyright by H๏dr” (1998)"
  setcolor("gr+/b+")
return nil
