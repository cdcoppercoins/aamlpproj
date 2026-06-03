@echo off
title MLP Deploy - FileZilla Finish
cd /d "%~dp0"

echo.
echo ============================================
echo   MLP Deploy - finish on server
echo ============================================
echo.
echo This window should show cyan/green text below.
echo If it stays blank for 30+ seconds, something blocked PowerShell.
echo.
echo Starting...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\Deploy-ToProduction.ps1" -ApplyOnly
set PSERR=%ERRORLEVEL%

echo.
if %PSERR% NEQ 0 (
    echo FAILED - exit code %PSERR%. Read the red text above.
) else (
    echo OK - server finish step completed.
)
echo.
pause
