#Requires -Version 5.1
<#
.SYNOPSIS
    One-click deploy: build, upload (SSH or FTP), finish on server.

.EXAMPLE
    cd d:\aamlpproj
    .\scripts\Deploy-ToProduction.ps1

    Or double-click Deploy-Now.bat in the project folder.

SETUP (once): see docs/DEPLOY_ONE_CLICK.md
#>
[CmdletBinding()]
param(
    [switch] $IncludeVendor,
    [switch] $SkipBuild,
    [switch] $SkipMigrate,
    [switch] $FtpOnly,
    [switch] $FtpManual,
    [switch] $ApplyOnly
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

function Invoke-DeployApply([hashtable] $Config) {
    $url = [string] $Config['deployApplyUrl']
    $token = [string] $Config['deployApplyToken']

    if ([string]::IsNullOrWhiteSpace($url) -or [string]::IsNullOrWhiteSpace($token)) {
        Write-Host ""
        Write-Host "Server finish step skipped (not configured yet)." -ForegroundColor Yellow
        Write-Host "One-time setup: see docs/DEPLOY_ONE_CLICK.md section 'Finish on server'." -ForegroundColor Yellow
        Write-Host "Or run in your server terminal:" -ForegroundColor Yellow
        Write-Host "  cd /home/minilp/laravel && php artisan view:clear && php artisan route:clear && php artisan migrate --force" -ForegroundColor White
        return
    }

    Write-Step "Finishing on server (unzip + migrate)"
    $applyUrl = $url.TrimEnd('/') + '?token=' + [Uri]::EscapeDataString($token)

    try {
        $body = ''
        $sawExtract = $false
        for ($attempt = 1; $attempt -le 30; $attempt++) {
            $response = Invoke-WebRequest -Uri $applyUrl -UseBasicParsing -TimeoutSec 90
            $body = $response.Content
            Write-Host $body

            if ($body -match 'EXTRACT_STARTED|EXTRACT_IN_PROGRESS|Starting background unzip') {
                $sawExtract = $true
                Write-Host "Waiting for server unzip ($attempt/30)..." -ForegroundColor Cyan
                Start-Sleep -Seconds 20
                continue
            }

            break
        }

        if ($body -match 'ZIP_EXTRACT_FAILED') {
            throw 'Server cannot unzip. cPanel File Manager: select release.zip -> Extract, then run this bat again.'
        }
        if ($sawExtract -and $body -notmatch 'ZIP_EXTRACT_OK|Deploy apply finished') {
            throw 'Unzip did not finish in time. Wait 2 minutes and run this bat again (do not re-upload).'
        }
        if ($body -notmatch 'Manage Save row button: OK') {
            throw 'Files on server are still old. Re-upload release.zip and run this bat again.'
        }

        Write-Host ''
        Write-Host 'Deploy OK - new files are on the server.' -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host ''
        Write-Host "FAILED: $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$configPath = Join-Path $ProjectRoot 'deploy\deploy.config.json'
$localConfigPath = Join-Path $ProjectRoot 'deploy\deploy.local.json'
$ftpScript = Join-Path $PSScriptRoot 'Deploy-Ftp.ps1'

if (-not (Test-Path $configPath)) {
    throw "Missing deploy/deploy.config.json"
}

if (-not (Test-Path $ftpScript)) {
    throw "Missing scripts/Deploy-Ftp.ps1"
}

. $ftpScript

$baseConfig = Read-JsonFile $configPath
$localConfig = Read-JsonFile $localConfigPath

$config = @{
    remoteHome = '/home/minilp'
    remoteLaravelPath = '/home/minilp/laravel'
    remotePublicHtmlPath = '/home/minilp/public_html'
    remoteLaravelRoot = 'laravel'
    remotePublicHtml = 'public_html'
    liveUrl = 'https://minilicenseplates.com'
    sshHost = ''
    sshUser = 'minilp'
    sshPort = 22
    sshKeyPath = ''
    ftpHost = 'ftp.minilicenseplates.com'
    ftpUser = ''
    ftpPassword = ''
    ftpPort = 21
    deployApplyUrl = 'https://minilicenseplates.com/apply-deploy.php'
    deployApplyToken = ''
    skipSsh = $true
}

$config = Merge-Config $config $baseConfig
$config = Merge-Config $config $localConfig

if ($ApplyOnly) {
    Write-Host ""
    Write-Host 'MLP deploy - finish on server' -ForegroundColor Cyan
    Write-Host '  Use after FileZilla uploaded release.zip to /home/minilp/' -ForegroundColor DarkGray
    Write-Host ""
    if (-not (Invoke-DeployApply -Config $config)) {
        exit 1
    }
    Write-Host ""
    Write-Host "Deploy finished." -ForegroundColor Green
    Write-Host "  Live site: $($config['liveUrl'])"
    Write-Host "  Open the site and press Ctrl+F5." -ForegroundColor Green
    Write-Host ""
    exit 0
}

if ([string]::IsNullOrWhiteSpace($config['remoteLaravelRoot'])) {
    $config['remoteLaravelRoot'] = 'laravel'
}
if ([string]::IsNullOrWhiteSpace($config['remotePublicHtml'])) {
    $config['remotePublicHtml'] = 'public_html'
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
    throw "No deploy/out release folder found."
}

$laravelOut = Join-Path $outRoot.FullName 'laravel'
$publicHtmlOut = Join-Path $outRoot.FullName 'public_html'

if (-not (Test-Path $laravelOut) -or -not (Test-Path $publicHtmlOut)) {
    throw "Release folder is incomplete: $($outRoot.FullName)"
}

# Copy finish helper into public_html each deploy
$applyDeploySource = Join-Path $ProjectRoot 'deploy\apply-deploy.php'
if (Test-Path $applyDeploySource) {
    Copy-Item $applyDeploySource (Join-Path $publicHtmlOut 'apply-deploy.php') -Force
}

$uploaded = $false
$usedSsh = $false
$skipSsh = $FtpOnly -or ($config['skipSsh'] -eq $true)

if ($skipSsh) {
    Write-Host "Using FTP upload (SSH is not used from your PC)." -ForegroundColor Yellow
}

if (-not $skipSsh -and -not [string]::IsNullOrWhiteSpace($config['sshHost'])) {
    $sshToolsOk = @('ssh', 'scp', 'tar') | ForEach-Object { Get-Command $_ -ErrorAction SilentlyContinue } | Where-Object { $_ } | Measure-Object | Select-Object -ExpandProperty Count
    if ($sshToolsOk -eq 3) {
        $sshTarget = "$($config['sshUser'])@$($config['sshHost'])"
        $sshPort = [int] $config['sshPort']
        $sshBaseArgs = @('-p', $sshPort, '-o', 'ConnectTimeout=8', '-o', 'StrictHostKeyChecking=accept-new')
        $scpBaseArgs = @('-P', $sshPort, '-o', 'ConnectTimeout=8', '-o', 'StrictHostKeyChecking=accept-new')

        if (-not [string]::IsNullOrWhiteSpace($config['sshKeyPath']) -and (Test-Path $config['sshKeyPath'])) {
            $sshBaseArgs += @('-i', $config['sshKeyPath'])
            $scpBaseArgs += @('-i', $config['sshKeyPath'])
        }

        $tarName = 'deploy-upload.tar.gz'
        $tarPath = Join-Path $outRoot.FullName $tarName
        $remoteTar = "$($config['remoteHome'])/$tarName"

        Write-Step "Creating upload archive for SSH"
        if (Test-Path $tarPath) { Remove-Item $tarPath -Force }
        Push-Location $outRoot.FullName
        try {
            & tar -czf $tarName laravel public_html
            if ($LASTEXITCODE -ne 0) { throw "tar failed" }
        }
        finally {
            Pop-Location
        }

        Write-Step "Trying SSH upload to $sshTarget"
        $prevEap = $ErrorActionPreference
        $ErrorActionPreference = 'Continue'
        try {
            & scp @scpBaseArgs $tarPath "${sshTarget}:${remoteTar}" 2>$null
            $scpOk = ($LASTEXITCODE -eq 0)
        }
        finally {
            $ErrorActionPreference = $prevEap
        }
        if ($scpOk) {
            $postCommands = @(
                "set -e"
                "cd $($config['remoteHome'])"
                "tar -xzf $tarName"
                "rm -f $tarName"
                "cd $($config['remoteLaravelPath'])"
                "php artisan view:clear"
                "php artisan cache:clear"
                "php artisan route:clear"
                "php artisan config:clear"
            )
            if (-not $SkipMigrate) {
                $postCommands += "php artisan migrate --force"
            }
            $remoteScript = ($postCommands -join ' && ')
            $prevEap = $ErrorActionPreference
            $ErrorActionPreference = 'Continue'
            try {
                & ssh @sshBaseArgs $sshTarget $remoteScript 2>$null
                if ($LASTEXITCODE -eq 0) {
                    $uploaded = $true
                    $usedSsh = $true
                }
            }
            finally {
                $ErrorActionPreference = $prevEap
            }
        }
    }
}

if (-not $uploaded) {
    if ([string]::IsNullOrWhiteSpace($config['ftpUser'])) {
        Write-Host ""
        Write-Host "Automatic upload needs FTP login in deploy/deploy.local.json" -ForegroundColor Yellow
        Write-Host "  Copy deploy/deploy.local.json.example to deploy/deploy.local.json" -ForegroundColor Yellow
        Write-Host "  Set ftpUser and ftpPassword (same as FileZilla)" -ForegroundColor Yellow
        Start-Process explorer.exe $outRoot.FullName
        throw "Upload not configured - see docs/DEPLOY_ONE_CLICK.md"
    }

    if ([string]::IsNullOrWhiteSpace($config['ftpPassword'])) {
        $secure = Read-Host "FTP password for $($config['ftpUser'])" -AsSecureString
        $ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure)
        try {
            $config['ftpPassword'] = [Runtime.InteropServices.Marshal]::PtrToStringAuto($ptr)
        }
        finally {
            [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)
        }
    }

    $laravelZip = Join-Path $outRoot.FullName 'laravel.zip'
    $publicZip = Join-Path $outRoot.FullName 'public_html.zip'
    $releaseZip = Join-Path $outRoot.FullName 'release.zip'

    if (-not (Test-Path $publicZip)) {
        Write-Step "Creating public_html.zip for FTP"
        if (Test-Path $publicZip) { Remove-Item $publicZip -Force }
        Compress-Archive -Path (Join-Path $publicHtmlOut '*') -DestinationPath $publicZip -CompressionLevel Optimal
    }

    if (-not (Test-Path $laravelZip)) {
        Write-Step "Creating laravel.zip for FTP"
        if (Test-Path $laravelZip) { Remove-Item $laravelZip -Force }
        Compress-Archive -Path (Join-Path $laravelOut '*') -DestinationPath $laravelZip -CompressionLevel Optimal
    }

    if ($FtpManual) {
        Write-Host ""
        Write-Host 'FileZilla upload - manual' -ForegroundColor Yellow
        Write-Host '  Upload BOTH files to /home/minilp/ on the server:' -ForegroundColor White
        Write-Host "    $publicZip"
        Write-Host "    $laravelZip"
        Write-Host '  public_html.zip is small; laravel.zip is the big one.' -ForegroundColor DarkGray
        Write-Host ""
        Start-Process explorer.exe $outRoot.FullName
        Read-Host "Press Enter when FileZilla upload is finished"
        $uploaded = $true
        if (-not (Invoke-DeployApply -Config $config)) {
            exit 1
        }
        Write-Host ""
        Write-Host "Deploy finished." -ForegroundColor Green
        Write-Host "  Live site: $($config['liveUrl'])"
        Write-Host "  Open the site and press Ctrl+F5." -ForegroundColor Green
        Write-Host ""
        exit 0
    }

    Write-Step "Uploading via FTP to $($config['ftpHost']) (public_html.zip + laravel.zip)"
    $ftpOk = Send-DeployFtpPackage `
        -LaravelLocal $laravelOut `
        -PublicHtmlLocal $publicHtmlOut `
        -RemoteLaravel $config['remoteLaravelRoot'] `
        -RemotePublicHtml $config['remotePublicHtml'] `
        -FtpHost $config['ftpHost'] `
        -FtpUser $config['ftpUser'] `
        -FtpPassword $config['ftpPassword'] `
        -FtpPort ([int] $config['ftpPort']) `
        -ReleaseZipPath $releaseZip

    if (-not $ftpOk) {
        throw "FTP upload failed."
    }

    $uploaded = $true

    if (-not $usedSsh) {
        if (-not (Invoke-DeployApply -Config $config)) {
            exit 1
        }
    }
}

Write-Host ""
Write-Host "Deploy finished." -ForegroundColor Green
Write-Host "  Live site: $($config['liveUrl'])"
Write-Host "  Open the site and press Ctrl+F5." -ForegroundColor Green
Write-Host ""

if (-not $usedSsh) {
    Write-Host "Package folder: $($outRoot.FullName)" -ForegroundColor DarkGray
}
