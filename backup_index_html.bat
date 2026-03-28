@echo off
REM index.htmlのバックアップを作成
set DATE=%DATE:~0,4%%DATE:~5,2%%DATE:~8,2%_%TIME:~0,2%%TIME:~3,2%%TIME:~6,2%
copy index.html index.html.backup_%DATE%
echo Backup created: index.html.backup_%DATE%