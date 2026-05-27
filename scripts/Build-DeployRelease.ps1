#Requires -Version 5.1
<#
.SYNOPSIS
    Build a complete, consistent production upload package (fixes partial FileZilla uploads).

.DESCRIPTION
    Copies every Laravel folder the live site needs into deploy/out/<timestamp>/laravel/.
    Optionally includes vendor/ after composer install --no-dev.
    Always builds public_html/ (web files + index.php) for the live site folder.

.PARAMETER IncludeVendor
    Run composer install --no-dev and copy vendor/. Use after composer.json/composer.lock
    changes, or when the live site shows 500 / "class not found" errors.

.PARAMETER OpenFolder
    Open the release folder in Explorer when done.

.EXAMPLE
    .\scripts\Build-DeployRelease.ps1

.EXAMPLE
    .\scripts\Build-DeployRelease.ps1 -IncludeVendor
#>
[CmdletBinding()]
param(
    [switch] $IncludeVendor,
    [switch] $OpenFolder
)

$ErrorActionPreference = 'Stop'

$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$ConfigPath = Join-Path $ProjectRoot 'deploy\deploy.config.json'
if (-not (Test-Path $ConfigPath)) {
    throw "Missing deploy/deploy.config.json"
}
$config = Get-Content $ConfigPath -Raw | ConvertFrom-Json

$stamp = Get-Date -Format 'yyyy-MM-dd_HHmmss'
$outRoot = Join-Path $ProjectRoot "deploy\out\$stamp"
$laravelOut = Join-Path $outRoot 'laravel'
$publicHtmlOut = Join-Path $outRoot 'public_html'

function Write-Step([string] $Message) {
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Test-PathReadable {
    param([string] $Path)
    try {
        $null = Get-Item -LiteralPath $Path -Force -ErrorAction Stop
        return $true
    }
    catch {
        return $false
    }
}

function Copy-VendorRobocopy {
    param([string] $Source, [string] $Destination)
    New-Item -ItemType Directory -Path $Destination -Force | Out-Null
    $null = robocopy $Source $Destination /E /NFL /NDL /NJH /NJS /NC /NS /NP /XD node_modules .git
    if ($LASTEXITCODE -ge 8) {
        throw "robocopy vendor failed with exit code $LASTEXITCODE"
    }
}

function Copy-TreeFiltered {
    param(
        [string] $Source,
        [string] $Destination,
        [string[]] $ExcludeDirNames = @(),
        [string[]] $ExcludeFilePatterns = @()
    )
    if (-not (Test-PathReadable $Source)) {
        Write-Warning "Skip missing or unreadable: $Source"
        return
    }
    $sourceItem = Get-Item -LiteralPath $Source -Force
    if ($sourceItem.PSIsContainer) {
        New-Item -ItemType Directory -Path $Destination -Force | Out-Null
        Get-ChildItem -LiteralPath $Source -Force -ErrorAction SilentlyContinue | ForEach-Object {
            if ($ExcludeDirNames -contains $_.Name) { return }
            if (-not (Test-PathReadable $_.FullName)) { return }
            $rel = $_.Name
            $destChild = Join-Path $Destination $rel
            if ($_.PSIsContainer) {
                Copy-TreeFiltered -Source $_.FullName -Destination $destChild `
                    -ExcludeDirNames $ExcludeDirNames -ExcludeFilePatterns $ExcludeFilePatterns
            }
            else {
                $skip = $false
                foreach ($pat in $ExcludeFilePatterns) {
                    if ($_.Name -like $pat) { $skip = $true; break }
                }
                if (-not $skip) {
                    Copy-Item -LiteralPath $_.FullName -Destination $destChild -Force
                }
            }
        }
    }
    else {
        New-Item -ItemType Directory -Path (Split-Path $Destination) -Force | Out-Null
        Copy-Item -LiteralPath $Source -Destination $Destination -Force
    }
}

Write-Step "Preflight (local)"
Push-Location $ProjectRoot
try {
    if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
        throw "PHP not on PATH. Start from XAMPP shell or add PHP to PATH."
    }
    $phpVersion = (php -r "echo PHP_VERSION;").Trim()
    Write-Host "    PHP $phpVersion"

    if (-not (Test-Path (Join-Path $ProjectRoot '.env'))) {
        throw ".env missing locally. Site must run locally before you deploy."
    }

    php artisan about --only=environment 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        throw "php artisan about failed. Fix local errors before deploying."
    }
    Write-Host "    artisan about OK"
}
finally {
    Pop-Location
}

if ($IncludeVendor) {
    Write-Step "composer install --no-dev (production vendor)"
    Push-Location $ProjectRoot
    try {
        if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
            throw "composer not on PATH."
        }
        composer install --no-dev --optimize-autoloader --no-interaction
        if ($LASTEXITCODE -ne 0) {
            throw "composer install failed."
        }
    }
    finally {
        Pop-Location
    }
}
elseif (-not (Test-Path (Join-Path $ProjectRoot 'vendor\autoload.php'))) {
    Write-Host ""
    Write-Host "WARNING: vendor/ not found locally. Live 500 errors often mean incomplete vendor on the server." -ForegroundColor Yellow
    Write-Host "         Re-run with: .\scripts\Build-DeployRelease.ps1 -IncludeVendor" -ForegroundColor Yellow
    Write-Host ""
}

Write-Step "Building release at deploy\out\$stamp"
New-Item -ItemType Directory -Path $laravelOut -Force | Out-Null

$excludeDirs = @('node_modules', 'tests', '.git', '.github', 'mlp_code', 'deploy')
$excludeFiles = @('*.log', '.env', '.env.*', 'Thumbs.db', '.DS_Store')

# Laravel core (always ship the full tree — never guess which files changed)
$coreDirs = @('app', 'bootstrap', 'config', 'database', 'resources', 'routes')
foreach ($dir in $coreDirs) {
    $src = Join-Path $ProjectRoot $dir
    $dst = Join-Path $laravelOut $dir
    if ($dir -eq 'bootstrap') {
        New-Item -ItemType Directory -Path $dst -Force | Out-Null
        Get-ChildItem -LiteralPath $src -Force | ForEach-Object {
            if ($_.Name -eq 'cache') { return }
            $destChild = Join-Path $dst $_.Name
            if ($_.PSIsContainer) {
                Copy-TreeFiltered -Source $_.FullName -Destination $destChild `
                    -ExcludeDirNames $excludeDirs -ExcludeFilePatterns $excludeFiles
            }
            else {
                Copy-Item -LiteralPath $_.FullName -Destination $destChild -Force
            }
        }
        # bootstrap/cache: only .gitignore (never upload local config cache)
        $cacheSrc = Join-Path $src 'cache'
        $cacheDst = Join-Path $dst 'cache'
        if (Test-Path $cacheSrc) {
            New-Item -ItemType Directory -Path $cacheDst -Force | Out-Null
            $gitignore = Join-Path $cacheSrc '.gitignore'
            if (Test-Path $gitignore) {
                Copy-Item $gitignore (Join-Path $cacheDst '.gitignore') -Force
            }
        }
    }
    else {
        Copy-TreeFiltered -Source $src -Destination $dst -ExcludeDirNames $excludeDirs -ExcludeFilePatterns $excludeFiles
    }
}

# public/ without plates/
$publicSrc = Join-Path $ProjectRoot 'public'
$publicDst = Join-Path $laravelOut 'public'
Copy-TreeFiltered -Source $publicSrc -Destination $publicDst `
    -ExcludeDirNames @('plates', 'build', 'hot') -ExcludeFilePatterns $excludeFiles

# Root files
foreach ($file in @('artisan', 'composer.json', 'composer.lock')) {
    $src = Join-Path $ProjectRoot $file
    if (Test-Path $src) {
        Copy-Item $src (Join-Path $laravelOut $file) -Force
    }
}

if ($IncludeVendor) {
    if (-not (Test-Path (Join-Path $ProjectRoot 'vendor\autoload.php'))) {
        throw "vendor/ missing. Run with -IncludeVendor only after composer install succeeds."
    }
    Write-Step "Copying vendor/ (this may take a few minutes)"
    Copy-VendorRobocopy -Source (Join-Path $ProjectRoot 'vendor') -Destination (Join-Path $laravelOut 'vendor')
}

# public_html = live web root (CSS, images, index.php bootstrap)
Write-Step "Building public_html/ (live site files)"
New-Item -ItemType Directory -Path $publicHtmlOut -Force | Out-Null
foreach ($asset in $config.publicHtmlAssets) {
    $src = Join-Path $ProjectRoot "public\$asset"
    $dst = Join-Path $publicHtmlOut $asset
    if (Test-Path $src) {
        Copy-TreeFiltered -Source $src -Destination $dst -ExcludeDirNames @('plates') -ExcludeFilePatterns $excludeFiles
    }
}
$indexTemplate = Join-Path $ProjectRoot 'deploy\public_html-index.php'
if (Test-Path $indexTemplate) {
    Copy-Item $indexTemplate (Join-Path $publicHtmlOut 'index.php') -Force
}

# Instructions
$remoteLaravel = $config.remoteLaravelRoot
$remotePublic = $config.remotePublicHtml
$remoteHome = if ($config.remoteHome) { $config.remoteHome } else { '/home/minilp' }

$vendorList = if ($IncludeVendor) { ', vendor' } else { '' }
$vendorNote = if ($IncludeVendor) {
    "  This package INCLUDES vendor/ - wait for upload to finish completely.`r`n"
}
else {
    @"
  This package does NOT include vendor/. Only OK if you did not change composer.json.
  If the site 500s, re-run: .\scripts\Build-DeployRelease.ps1 -IncludeVendor

"@
}
$instructions = @"
MiniLicensePlates.com - deploy package $stamp
Generated: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')

WHY THIS EXISTS
  Partial FileZilla uploads (missing app files, half-finished vendor/, cached config)
  cause 500 errors. This package always contains the FULL Laravel app tree.

================================================================
STEP 1 - GitHub Desktop (backup)
================================================================
  Commit and push your work before uploading.

================================================================
STEP 2 - FileZilla: upload Laravel package
================================================================
  Local folder:  deploy\out\$stamp\laravel\
  Remote folder: $remoteLaravel/

  In FileZilla: open the local laravel folder, select ALL items inside it
  (app, bootstrap, config, database, public, resources, routes, artisan,
   composer.json, composer.lock$vendorList),
  drag to $remoteLaravel/

  When prompted: Overwrite - apply to all.
  Do NOT upload .env from your PC (server keeps its own).

  NEVER upload public/plates/ from your PC (photos already on server).

$vendorNote
================================================================
STEP 3 - FileZilla: upload public_html (CSS, images, index.php)
================================================================
  Local:  deploy\out\$stamp\public_html\
  Remote: $remotePublic/

  Upload everything inside public_html/ to $remotePublic/
  (includes index.php, main.css, js, hero, etc.)

  Do NOT upload plates/ from your PC unless you added new photos.

================================================================
STEP 4 - Clear server cache (FileZilla, no SSH)
================================================================
  On server under $remoteLaravel/ delete:
    - bootstrap/cache/*.php  (keep .gitignore)
    - storage/framework/views/*.php  (compiled Blade)

================================================================
STEP 5 - Migrations (only if you added migration files)
================================================================
  Ask host to run once, or cPanel cron:
  php $remoteHome/laravel/artisan migrate --force

================================================================
STEP 6 - Test live
================================================================
  $($config.liveUrl) - Ctrl+F5

  If still 500: download $remoteLaravel/storage/logs/laravel.log
  (last 40 lines). Check public_html/index.php points to ../laravel

================================================================
Optional: one zip (both folders)
================================================================
  Use release.zip: upload to $remoteHome/ and extract there.
  You should get folders laravel/ and public_html/ updated together.
  Or use laravel.zip only into $remoteLaravel/ for code-only updates.

"@

$instructionsPath = Join-Path $outRoot 'UPLOAD_INSTRUCTIONS.txt'
Set-Content -Path $instructionsPath -Value $instructions -Encoding UTF8

# Manifest
$manifestLines = @("Release: $stamp", "IncludeVendor: $IncludeVendor", "")
Get-ChildItem -Path $laravelOut -Recurse -File | ForEach-Object {
    $rel = $_.FullName.Substring($laravelOut.Length + 1)
    $manifestLines += $rel
}
Set-Content -Path (Join-Path $outRoot 'MANIFEST.txt') -Value $manifestLines -Encoding UTF8

# Zip (optional convenience)
$zipPath = Join-Path $outRoot 'laravel.zip'
$releaseZipPath = Join-Path $outRoot 'release.zip'
if (Get-Command Compress-Archive -ErrorAction SilentlyContinue) {
    Write-Step "Creating laravel.zip"
    if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
    Compress-Archive -Path (Join-Path $laravelOut '*') -DestinationPath $zipPath -CompressionLevel Optimal

    Write-Step "Creating release.zip (laravel + public_html)"
    if (Test-Path $releaseZipPath) { Remove-Item $releaseZipPath -Force }
    Compress-Archive -Path $laravelOut, $publicHtmlOut -DestinationPath $releaseZipPath -CompressionLevel Optimal
}

Write-Host ""
Write-Host "Done." -ForegroundColor Green
Write-Host "  Laravel:     deploy\out\$stamp\laravel\  -> server $remoteLaravel/"
Write-Host "  public_html: deploy\out\$stamp\public_html\  -> server $remotePublic/"
Write-Host "  Guide:       deploy\out\$stamp\UPLOAD_INSTRUCTIONS.txt"
if (Test-Path $zipPath) {
    Write-Host "  Zip:         deploy\out\$stamp\laravel.zip"
}
if (Test-Path $releaseZipPath) {
    Write-Host "  Both:        deploy\out\$stamp\release.zip  (extract in $remoteHome/)"
}
Write-Host ""
Write-Host "Next: open UPLOAD_INSTRUCTIONS.txt and follow steps 1-6." -ForegroundColor Green

if ($OpenFolder) {
    Start-Process explorer.exe $outRoot
}
