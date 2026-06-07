@echo off
title MLP - recreate server .env
cd /d "%~dp0"

echo.
echo 1. FileZilla: upload deploy\setup-env.php to public_html/
echo 2. Get MySQL name, user, password from cPanel - MySQL Databases
echo.
powershell -NoProfile -Command ^

  "$j = Get-Content 'deploy\deploy.local.json' -Raw | ConvertFrom-Json; " ^

  "if (-not $j.deployApplyToken) { throw 'deployApplyToken missing in deploy.local.json' }; " ^

  "Set-Content -Path 'deploy\.setup-env-token' -Value $j.deployApplyToken.Trim() -NoNewline -Encoding ASCII"



echo.

echo Upload these TWO files to public_html/ with FileZilla:

echo   deploy\setup-env.php

echo   deploy\.setup-env-token

echo.

echo Then get MySQL info: cPanel - MySQL Databases

echo.



for /f "usebackq delims=" %%T in (`powershell -NoProfile -Command "(Get-Content 'deploy\deploy.local.json' -Raw | ConvertFrom-Json).deployApplyToken"`) do set TOKEN=%%T



start https://minilicenseplates.com/setup-env.php?token=%TOKEN%

echo Browser opened. Fill the form.

echo.

echo When it says OK:

echo   Delete setup-env.php and .setup-env-token from public_html

echo   Run Deploy-Now.bat