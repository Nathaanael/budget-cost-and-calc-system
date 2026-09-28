use yc06te~1
go top
mkdfg=space(8)
mkdrm=space(8)
moutput=space(12)
do while .T. 
   if substr(b,1,8)="Product"
      mkdfg=substr(b,15,8)
   else
      if substr(b,1,8)="Quantity"
         moutput=substr(b,24,15)
      endif
   endif
   mkdrm=substr(c,1,8)
   if Val(mkdrm)>1000
      repl r with mkdfg
      repl s with mkdrm
      repl t with moutput
   endif

   skip
   if eof()
     exit
   endif
enddo
return
