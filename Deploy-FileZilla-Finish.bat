@echo off
title MLP Deploy - finish only
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0scripts\Deploy-ToProduction.ps1" -ApplyOnly
if %ERRORLEVEL% NEQ 0 (echo FAILED & pause & exit /b 1)
echo OK & pause
