@echo off
setlocal
title TROV - desktop monitoring agent
cd /d "%~dp0desktop-agent"

echo ===========================================================
echo    TROV desktop monitoring agent
echo    Captures the editor's screen while they are timed in.
echo ===========================================================
echo.
echo    Before using this:
echo      1) Run run-trov.bat so the TROV server is up.
echo      2) The editor must be TIMED IN in TROV
echo         (devotional submitted, then "Time in").
echo    Screenshots are only captured while a session is open.
echo.

set "EMAIL="
set /p EMAIL="Editor's email (the one they sign in with): "
if "%EMAIL%"=="" ( echo No email entered. & pause & exit /b )

echo.
echo    Starting monitoring for %EMAIL%
echo    You'll be asked for that editor's password next.
echo    A screenshot is taken about every 60 seconds while timed in.
echo    Keep this window open. Press Ctrl+C here to STOP monitoring.
echo.

python trov_agent.py --email "%EMAIL%" --server http://127.0.0.1:8000 --interval 60

echo.
echo Monitoring stopped.
pause
endlocal
