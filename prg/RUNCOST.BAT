@echo off
rem Run from the COST program directory inside DOSBox.
rem Speed up database initialization and its two formula copies.
config -set "cpu cycles=20000"
set clipper=F150
cost.exe
