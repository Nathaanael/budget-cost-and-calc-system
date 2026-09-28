use hasil1
index on GABU to x
go top
mmax=0
do while .t.
   mmax=U
   mr=r
   do while .t.
      if U>mmax
         mmax = U
      endif
      if r<>mr.or.eof()
         exit
      else
         skip
      endif
   enddo
   seek mr
   do while .t.
      if r=mr
        repl v with mmax
      endif
      if r<>mr.or.eof()
         exit
      else
        skip
      endif
   enddo
   if eof()
      exit
   endif
enddo
return

