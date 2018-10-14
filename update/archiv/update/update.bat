@echo off

c:\xampp\mysql\bin\mysqldump -uroot -pBjk*1903 mundialdb > c:\xampp\htdocs\mundial\api\Backups\dbBackupVorUpdate.sql --routines 
if %errorlevel% == 0 ( GOTO :dbUpdate ) ELSE ( set errorlvl=66 
GOTO :end )

:dbUpdate
c:\xampp\mysql\bin\mysql -uroot -pBjk*1903 mundialdb < C:\xampp\htdocs\mundial\api\Backups\zips\update\sql.sql

if %errorlevel% == 0 ( GOTO :end ) ELSE ( set errorlvl=99 )



:end 
echo %errorlvl%

