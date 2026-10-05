@echo off
setlocal enabledelayedexpansion
title TROV - launcher
cd /d "%~dp0"

REM TROV's permanent public link (ngrok static domain - never changes).
set "TROVURL=https://grain-maturely-expensive.ngrok-free.dev"

echo ===========================================================
echo    TROV - Kingdom Production Company workplace console
echo    Starting MySQL, the web server, and the public link...
echo ===========================================================
echo.

REM --- 1. MySQL (start hidden only if it is not already running) ---
powershell -NoProfile -Command "if (-not (Get-Process mysqld -ErrorAction SilentlyContinue)) { Start-Process -FilePath 'C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqld.exe' -ArgumentList '--datadir=C:\Users\VINCE\AppData\Local\CPMMS-MySQL\data','--port=3306' -WorkingDirectory 'C:\' -WindowStyle Hidden }"
echo [1/3] MySQL running.

REM --- 2. Laravel web server (own window, stays open) ---
start "TROV server" cmd /k "cd /d "%~dp0" && php artisan serve --host=127.0.0.1 --port=8000"
echo [2/3] Web server starting on http://127.0.0.1:8000
timeout /t 5 /nobreak >nul

REM --- 3. ngrok public tunnel on the permanent domain ---
REM    The URL is always the same, so we just start ngrok and wait for it to
REM    report the tunnel is up (reachability isn't checked from here because
REM    this PC's ISP DNS is unreliable for tunnel names - see DNS note below).
echo [3/3] Opening your permanent public link with ngrok...
taskkill /IM ngrok.exe /F >nul 2>&1
if exist "%~dp0tools\ngrok.log" del "%~dp0tools\ngrok.log" >nul 2>&1
start /MIN "TROV tunnel" "%~dp0tools\ngrok.exe" http --url=%TROVURL% 8000 --log="%~dp0tools\ngrok.log" --log-format=logfmt

set "LINKOK="
for /l %%i in (1,1,30) do (
  if not defined LINKOK (
    timeout /t 1 /nobreak >nul
    findstr /C:"started tunnel" "%~dp0tools\ngrok.log" >nul 2>&1 && set "LINKOK=1"
  )
)

echo ===========================================================
echo    YOUR PERMANENT PUBLIC LINK ^(always the same^):
echo.
echo        %TROVURL%
echo.
<nul set /p "=%TROVURL%"> "%~dp0run-trov-url.txt"
<nul set /p "=%TROVURL%" | clip
start "" "%TROVURL%"
if defined LINKOK (
  echo    ngrok tunnel is live, copied to your clipboard, and opened.
) else (
  echo    Tunnel is starting - if the page is blank, wait a few seconds
  echo    and refresh. ^(Local link below always works.^)
)
echo.
echo    First time ngrok opens the link, you may see a blue ngrok page
echo    with a "Visit Site" button - just click it once.
echo ===========================================================
echo.
echo    Local address:  http://127.0.0.1:8000
echo.
echo    Keep this window open while you use TROV.
echo    Press any key here to STOP the server and the public link.
echo.
pause >nul

REM --- shut down the server and tunnel on exit (MySQL is left running) ---
echo Stopping TROV...
taskkill /IM ngrok.exe /F >nul 2>&1
for /f "tokens=2 delims=," %%P in ('tasklist /v /fi "WINDOWTITLE eq TROV server*" /fo csv /nh 2^>nul') do taskkill /PID %%~P /T /F >nul 2>&1
echo Done. (MySQL is still running in the background.)
timeout /t 2 /nobreak >nul
endlocal
