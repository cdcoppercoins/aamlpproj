#Requires -Version 5.1
<#
.SYNOPSIS
    One-command deploy: build package, upload to server, run migrations and clear caches.

.EXAMPLE
    cd d:\aamlpproj
    .\scripts\Deploy-ToProduction.ps1

.EXAMPLE
    .\scripts\Deploy-ToProduction.ps1 -IncludeVendor

SETUP (once):
    Copy deploy\deploy.local.json.example to deploy\deploy.local.json
    Set sshHost and sshUser (use minilp, not root, if possible).
    Set up SSH key login so you are not prompted every time.
#>
[CmdletBinding()]
param(
    [switch] $IncludeVendor,
    [switch] $SkipBuild,
    [switch] $SkipMigrate
)

$ErrorActionPreference = 'Stop'

function Write-Step([string] $Message) {
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Read-JsonFile([string] $Path) {
    if (-not (Test-Path $Path)) {
        return @{}
    }

    return (Get-Content $Path -Raw | ConvertFrom-Json)
}

function Merge-Config([hashtable] $Base, [psobject] $Overlay) {
    if ($null -eq $Overlay) {
        return $Base
    }

    foreach ($prop in $Overlay.PSObject.Properties) {
        if ($null -ne $prop.Value -and "$($prop.Value)" -ne '') {
            $Base[$prop.Name] = $prop.Value
        }
    }

    return $Base
}

$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$configPath = Join-Path $ProjectRoot 'deploy\deploy.config.json'
$localConfigPath = Join-Path $ProjectRoot 'deploy\deploy.local.json'

if (-not (Test-Path $configPath)) {
    throw "Missing deploy/deploy.config.json"
}

$baseConfig = Read-JsonFile $configPath
$localConfig = Read-JsonFile $localConfigPath

$config = @{
    remoteHome = '/home/minilp'
    remoteLaravelPath = '/home/minilp/laravel'
    remotePublicHtmlPath = '/home/minilp/public_html'
    liveUrl = 'https://minilicenseplates.com'
    sshHost = ''
    sshUser = 'minilp'
    sshPort = 22
    sshKeyPath = ''
}

$config = Merge-Config $config $baseConfig
$config = Merge-Config $config $localConfig

if ([string]::IsNullOrWhiteSpace($config['remoteHome'])) {
    $config['remoteHome'] = '/home/minilp'
}
if ([string]::IsNullOrWhiteSpace($config['remoteLaravelPath'])) {
    $config['remoteLaravelPath'] = "$($config['remoteHome'])/laravel"
}
if ([string]::IsNullOrWhiteSpace($config['remotePublicHtmlPath'])) {
    $config['remotePublicHtmlPath'] = "$($config['remoteHome'])/public_html"
}

if ([string]::IsNullOrWhiteSpace($config['sshHost'])) {
    Write-Host ""
    Write-Host "First-time setup:" -ForegroundColor Yellow
    Write-Host "  1. Copy deploy\deploy.local.json.example to deploy\deploy.local.json"
    Write-Host "  2. Set sshHost (e.g. minilicenseplates.com) and sshUser (e.g. minilp)"
    Write-Host "  3. Run this script again"
    Write-Host ""
    throw "Missing sshHost in deploy/deploy.local.json"
}

foreach ($tool in @('ssh', 'scp', 'tar')) {
    if (-not (Get-Command $tool -ErrorAction SilentlyContinue)) {
        throw "$tool not found. Install OpenSSH client (Windows Settings -> Optional features)."
    }
}

$sshTarget = "$($config['sshUser'])@$($config['sshHost'])"
$sshPort = [int] $config['sshPort']
$sshBaseArgs = @('-p', $sshPort, '-o', 'StrictHostKeyChecking=accept-new')
$scpBaseArgs = @('-P', $sshPort, '-o', 'StrictHostKeyChecking=accept-new')

if (-not [string]::IsNullOrWhiteSpace($config['sshKeyPath'])) {
    $keyPath = $config['sshKeyPath']
    if (-not (Test-Path $keyPath)) {
        throw "SSH key not found: $keyPath"
    }
    $sshBaseArgs += @('-i', $keyPath)
    $scpBaseArgs += @('-i', $keyPath)
}

if (-not $SkipBuild) {
    Write-Step "Building release package"
    $buildScript = Join-Path $PSScriptRoot 'Build-DeployRelease.ps1'
    if ($IncludeVendor) {
        & $buildScript -IncludeVendor
    }
    else {
        & $buildScript
    }
}

Write-Step "Finding latest deploy package"
$outRoot = Get-ChildItem (Join-Path $ProjectRoot 'deploy\out') -Directory -ErrorAction Stop |
    Sort-Object Name -Descending |
    Select-Object -First 1

if ($null -eq $outRoot) {
    throw "No deploy/out release folder found. Run without -SkipBuild first."
}

$laravelOut = Join-Path $outRoot.FullName 'laravel'
$publicHtmlOut = Join-Path $outRoot.FullName 'public_html'

if (-not (Test-Path $laravelOut) -or -not (Test-Path $publicHtmlOut)) {
    throw "Release folder is incomplete: $($outRoot.FullName)"
}

$tarName = 'deploy-upload.tar.gz'
$tarPath = Join-Path $outRoot.FullName $tarName

Write-Step "Creating upload archive"
if (Test-Path $tarPath) {
    Remove-Item $tarPath -Force
}
Push-Location $outRoot.FullName
try {
    & tar -czf $tarName laravel public_html
    if ($LASTEXITCODE -ne 0) {
        throw "tar failed with exit code $LASTEXITCODE"
    }
}
finally {
    Pop-Location
}

$remoteTar = "$($config['remoteHome'])/$tarName"

Write-Step "Uploading to $sshTarget"
$scpSucceeded = $false
try {
    & scp @scpBaseArgs $tarPath "${sshTarget}:${remoteTar}"
    if ($LASTEXITCODE -eq 0) {
        $scpSucceeded = $true
    }
}
catch {
    $scpSucceeded = $false
}

if (-not $scpSucceeded) {
    Write-Host ""
    Write-Host "Automatic upload failed (your PC cannot SSH to the server)." -ForegroundColor Yellow
    Write-Host "The deploy package was built successfully. Upload it with FileZilla instead:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  Local folder:  $($outRoot.FullName)" -ForegroundColor White
    Write-Host "    laravel\      -> server /home/minilp/laravel/" -ForegroundColor White
    Write-Host "    public_html\  -> server /home/minilp/public_html/" -ForegroundColor White
    Write-Host ""
    Write-Host "  In FileZilla: select ALL files inside each folder, drag, overwrite all." -ForegroundColor White
    Write-Host ""
    Write-Host "  Then in your server terminal (the one you already use), run:" -ForegroundColor White
    Write-Host "    cd /home/minilp/laravel && php artisan view:clear && php artisan route:clear && php artisan migrate --force" -ForegroundColor White
    Write-Host ""

    $fallback = @"
FileZilla upload (deploy failed over SSH)
Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')

Your PC cannot connect by SSH. Use FileZilla instead.

LOCAL (left side):
  $($outRoot.FullName)\laravel\
  Select everything INSIDE laravel (app, resources, routes, etc.)
  Drag to server folder: /home/minilp/laravel/
  Overwrite all.

  $($outRoot.FullName)\public_html\
  Select everything INSIDE public_html
  Drag to server folder: /home/minilp/public_html/
  Overwrite all.

SERVER TERMINAL (after upload):
  cd /home/minilp/laravel
  php artisan view:clear
  php artisan route:clear
  php artisan migrate --force

TEST:
  https://minilicenseplates.com/collection/reports
  Press Ctrl+F5
"@
    $fallbackPath = Join-Path $outRoot.FullName 'FILEZILLA_NOW.txt'
    Set-Content -Path $fallbackPath -Value $fallback -Encoding UTF8
    Start-Process explorer.exe $outRoot.FullName
    throw "Upload step skipped — follow FILEZILLA_NOW.txt in the folder that just opened."
}

$postCommands = @(
    "set -e"
    "cd $($config['remoteHome'])"
    "tar -xzf $tarName"
    "rm -f $tarName"
)

if ($config['sshUser'] -eq 'root') {
    $postCommands += "chown -R minilp:minilp $($config['remoteLaravelPath']) $($config['remotePublicHtmlPath']) 2>/dev/null || true"
}

$postCommands += @(
    "cd $($config['remoteLaravelPath'])"
    "php artisan view:clear"
    "php artisan cache:clear"
    "php artisan route:clear"
    "php artisan config:clear"
)

if (-not $SkipMigrate) {
    $postCommands += "php artisan migrate --force"
}

$postCommands += @(
    "grep -q 'collection.reports.index' routes/web.php && echo 'Reports routes: OK' || echo 'Reports routes: MISSING'"
    "test -d resources/views/collection/reports && echo 'Reports views: OK' || echo 'Reports views: MISSING'"
)

$remoteScript = ($postCommands -join ' && ')

Write-Step "Applying on server (extract + clear caches + migrate)"
& ssh @sshBaseArgs $sshTarget $remoteScript
if ($LASTEXITCODE -ne 0) {
    throw "Remote deploy commands failed with exit code $LASTEXITCODE"
}

Write-Host ""
Write-Host "Deploy finished." -ForegroundColor Green
Write-Host "  Live site: $($config['liveUrl'])"
Write-Host "  Reports:   $($config['liveUrl'])/collection/reports"
Write-Host ""
Write-Host "Hard refresh the site in your browser (Ctrl+F5)." -ForegroundColor Green
