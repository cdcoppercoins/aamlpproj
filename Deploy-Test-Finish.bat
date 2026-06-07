@echo off
cd /d "%~dp0"
for /f "usebackq delims=" %%T in (`powershell -NoProfile -Command "(Get-Content 'deploy\deploy.local.json' -Raw | ConvertFrom-Json).deployApplyToken"`) do set TOKEN=%%T
start https://minilicenseplates.com/apply-deploy.php?token=%TOKEN%
echo Opened finish URL in browser. You should see DEPLOY_APPLY_OK at the end.
pause
