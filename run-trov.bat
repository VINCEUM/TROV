@echo off
setlocal enabledelayedexpansion
title TROV - launcher
cd /d "%~dp0"

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

REM --- 3. Cloudflare public tunnel (minimized; link written to a log) ---
if exist "%~dp0tools\tunnel.log" del "%~dp0tools\tunnel.log" >nul 2>&1
start /MIN "TROV tunnel" cmd /c ""%~dp0tools\cloudflared.exe" tunnel --url http://127.0.0.1:8000 --no-autoupdate > "%~dp0tools\tunnel.log" 2>&1"
echo [3/3] Opening public link...
echo.

REM --- wait for the tunnel to print its URL, then read it ---
set "TROVURL="
for /l %%i in (1,1,20) do (
  if not defined TROVURL (
    timeout /t 1 /nobreak >nul
    for /f "delims=" %%U in ('powershell -NoProfile -Command "$m = Select-String -Path '%~dp0tools\tunnel.log' -Pattern 'https://[a-z0-9-]+\.trycloudflare\.com' | Select-Object -First 1; if ($m) { $m.Matches.Value }"') do set "TROVURL=%%U"
  )
)

echo ===========================================================
if defined TROVURL (
  echo    YOUR PUBLIC LINK ^(share this^):
  echo.
  echo        !TROVURL!
  echo.
  echo !TROVURL!> "%~dp0run-trov-url.txt"
  REM copy the link to the clipboard and open it in the default browser
  <nul set /p "=!TROVURL!" | clip
  start "" "!TROVURL!"
  echo    ^(Copied to your clipboard and opened in your browser.^)
) else (
  echo    Link not ready yet. Open the minimized "TROV tunnel"
  echo    window, or re-check tools\tunnel.log in a few seconds.
)
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
taskkill /IM cloudflared.exe /F >nul 2>&1
for /f "tokens=2 delims=," %%P in ('tasklist /v /fi "WINDOWTITLE eq TROV server*" /fo csv /nh 2^>nul') do taskkill /PID %%~P /T /F >nul 2>&1
echo Done. (MySQL is still running in the background.)
timeout /t 2 /nobreak >nul
endlocal
