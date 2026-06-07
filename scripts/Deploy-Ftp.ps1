#Requires -Version 5.1
<#
.SYNOPSIS
    Upload deploy package folders to production via FTP (WinSCP or FtpWebRequest).
#>
function Get-WinScpComPath {
    foreach ($path in @(
        "${env:ProgramFiles}\WinSCP\WinSCP.com",
        "${env:ProgramFiles(x86)}\WinSCP\WinSCP.com"
    )) {
        if (Test-Path $path) {
            return $path
        }
    }

    return $null
}

function Get-WinScpOpenLine {
    param(
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21,
        [bool] $UseTls = $true
    )

    $escapedPassword = $FtpPassword -replace '"', '""'
    $escapedUser = [Uri]::EscapeDataString($FtpUser)
    $scheme = if ($UseTls) { 'ftpes' } else { 'ftp' }

    # -certificate=* auto-accepts host cert (batch mode otherwise answers Cancel)
    return "open ${scheme}://${escapedUser}@${FtpHost}:${FtpPort}/ -password=""$escapedPassword"" -certificate=*"
}

function Send-WinScpFolderSync {
    param(
        [string] $LocalPath,
        [string] $RemotePath,
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21,
        [bool] $UseTls = $true
    )

    $winScp = Get-WinScpComPath
    if (-not $winScp) {
        return $false
    }

    # Relative path (no leading /) — FTP home is /home/minilp; /laravel becomes laravel/laravel
    $remotePath = ($RemotePath -replace '\\', '/').Trim('/')
    if ($remotePath -ne '') {
        $remotePath = $remotePath + '/'
    }
    $scriptPath = [System.IO.Path]::GetTempFileName() + '.txt'
    $logPath = [System.IO.Path]::GetTempFileName() + '.log'

    $openLine = Get-WinScpOpenLine -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort -UseTls $UseTls
    # Never use -delete: server has vendor, storage, .env not in the deploy package
    $script = @"
option batch abort
option confirm off
$openLine
synchronize remote "$LocalPath" "$remotePath"
exit
"@

    try {
        Set-Content -Path $scriptPath -Value $script -Encoding ASCII
        & $winScp "/ini=nul" "/certificate=*" "/log=$logPath" "/script=$scriptPath"
        if ($LASTEXITCODE -ne 0) {
            if (Test-Path $logPath) {
                Get-Content $logPath -Tail 30 | ForEach-Object { Write-Host $_ }
            }
            return $false
        }

        return $true
    }
    finally {
        Remove-Item $scriptPath -Force -ErrorAction SilentlyContinue
        Remove-Item $logPath -Force -ErrorAction SilentlyContinue
    }
}

function New-FtpUri {
    param(
        [string] $FtpHost,
        [int] $FtpPort,
        [string] $RemotePath
    )

    $path = ($RemotePath -replace '\\', '/').TrimStart('/')
    return "ftp://${FtpHost}:${FtpPort}/${path}"
}

function Invoke-FtpMakeDirectory {
    param(
        [string] $RemoteDir,
        [string] $FtpHost,
        [int] $FtpPort,
        [System.Net.NetworkCredential] $Credential
    )

    $uri = New-FtpUri -FtpHost $FtpHost -FtpPort $FtpPort -RemotePath $RemoteDir
    $request = [System.Net.FtpWebRequest]::Create($uri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
    $request.Credentials = $Credential
    $request.UsePassive = $true
    $request.UseBinary = $true

    try {
        $null = $request.GetResponse()
    }
    catch {
        # Directory may already exist.
    }
}

function Send-FtpFile {
    param(
        [string] $LocalFile,
        [string] $RemotePath,
        [string] $FtpHost,
        [int] $FtpPort,
        [System.Net.NetworkCredential] $Credential
    )

    $uri = New-FtpUri -FtpHost $FtpHost -FtpPort $FtpPort -RemotePath $RemotePath
    $request = [System.Net.FtpWebRequest]::Create($uri)
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = $Credential
    $request.UsePassive = $true
    $request.UseBinary = $true
    $request.KeepAlive = $false
    $request.Timeout = 7200000
    $request.ReadWriteTimeout = 7200000

    $fileInfo = Get-Item $LocalFile
    $request.ContentLength = $fileInfo.Length
    $buffer = New-Object byte[] 262144

    $input = [System.IO.File]::OpenRead($LocalFile)
    try {
        $stream = $request.GetRequestStream()
        try {
            while (($read = $input.Read($buffer, 0, $buffer.Length)) -gt 0) {
                $stream.Write($buffer, 0, $read)
            }
        }
        finally {
            $stream.Close()
        }
    }
    finally {
        $input.Close()
    }

    $null = $request.GetResponse()
}

function Send-FtpTree {
    param(
        [string] $LocalRoot,
        [string] $RemoteRoot,
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21
    )

    $credential = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
    $remoteRoot = ($RemoteRoot -replace '\\', '/').Trim('/')
    $files = Get-ChildItem -Path $LocalRoot -Recurse -File
    $total = $files.Count
    $index = 0

    foreach ($file in $files) {
        $index++
        $relative = $file.FullName.Substring($LocalRoot.Length).TrimStart('\', '/')
        $remoteDir = $remoteRoot + '/' + (Split-Path $relative -Parent) -replace '\\', '/'
        $remotePath = "$remoteRoot/$relative" -replace '\\', '/'

        if ($remoteDir -and $remoteDir -ne $remoteRoot) {
            $parts = $remoteDir.Split('/') | Where-Object { $_ -ne '' }
            $built = ''
            foreach ($part in $parts) {
                $built = if ($built) { "$built/$part" } else { $part }
                Invoke-FtpMakeDirectory -RemoteDir $built -FtpHost $FtpHost -FtpPort $FtpPort -Credential $credential
            }
        }

        Send-FtpFile -LocalFile $file.FullName -RemotePath $remotePath -FtpHost $FtpHost -FtpPort $FtpPort -Credential $credential

        if ($index % 100 -eq 0 -or $index -eq $total) {
            Write-Host "  Uploaded $index / $total files..."
        }
    }

    return $true
}

function Send-FtpFileViaCurl {
    param(
        [string] $LocalFile,
        [string] $RemoteFileName,
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21
    )

    $curl = Get-Command curl.exe -ErrorAction SilentlyContinue
    if (-not $curl) {
        return $false
    }

    $remoteName = $RemoteFileName.TrimStart('/')
    $ftpUrl = "ftp://${FtpHost}:${FtpPort}/${remoteName}"
    $sizeMb = [math]::Round((Get-Item $LocalFile).Length / 1MB, 1)
    Write-Host "  Uploading ${remoteName} (${sizeMb} MB) via curl (may take 10-30 min on slow links)..."

    $prevEap = $ErrorActionPreference
    $ErrorActionPreference = 'Continue'
    try {
        & curl.exe --ftp-pasv --connect-timeout 120 --max-time 14400 `
            --speed-time 3600 --speed-limit 100 `
            --retry 3 --retry-delay 5 --retry-all-errors `
            -T $LocalFile $ftpUrl --user "${FtpUser}:${FtpPassword}" 2>&1 | ForEach-Object { Write-Host $_ }
        return ($LASTEXITCODE -eq 0)
    }
    finally {
        $ErrorActionPreference = $prevEap
    }
}

function Send-WinScpFilePut {
    param(
        [string] $LocalFile,
        [string] $RemoteFileName,
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21,
        [bool] $UseTls = $true
    )

    $winScp = Get-WinScpComPath
    if (-not $winScp) {
        return $false
    }

    $openLine = Get-WinScpOpenLine -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort -UseTls $UseTls
    $remoteName = $RemoteFileName.TrimStart('/')
    $scriptPath = [System.IO.Path]::GetTempFileName() + '.txt'
    $script = @"
option batch abort
option confirm off
$openLine
put "$LocalFile" $remoteName
exit
"@

    try {
        Set-Content -Path $scriptPath -Value $script -Encoding ASCII
        & $winScp "/ini=nul" "/certificate=*" "/script=$scriptPath"
        return ($LASTEXITCODE -eq 0)
    }
    finally {
        Remove-Item $scriptPath -Force -ErrorAction SilentlyContinue
    }
}

function Send-FtpZipFile {
    param(
        [string] $LocalFile,
        [string] $RemoteFileName,
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21
    )

    $name = (Split-Path $LocalFile -Leaf)
    $sizeMb = [math]::Round((Get-Item $LocalFile).Length / 1MB, 1)
    Write-Host "  $name (${sizeMb} MB)..." -ForegroundColor Cyan

    if (Send-WinScpFilePut -LocalFile $LocalFile -RemoteFileName $RemoteFileName -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort) {
        return $true
    }

    if (Send-FtpFileViaCurl -LocalFile $LocalFile -RemoteFileName $RemoteFileName -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort) {
        return $true
    }

    Write-Host "  Trying built-in FTP (streamed)..." -ForegroundColor Yellow
    Send-FtpSingleFileWebRequest -LocalFile $LocalFile -RemoteFileName $RemoteFileName -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort

    return $true
}

function Send-FtpSingleFileWebRequest {
    param(
        [string] $LocalFile,
        [string] $RemoteFileName,
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21
    )

    $credential = New-Object System.Net.NetworkCredential($FtpUser, $FtpPassword)
    $remotePath = $RemoteFileName.TrimStart('/')
    Send-FtpFile -LocalFile $LocalFile -RemotePath $remotePath -FtpHost $FtpHost -FtpPort $FtpPort -Credential $credential

    return $true
}

function Send-DeployFtpZipRelease {
    param(
        [string] $ReleaseZipPath = '',
        [string] $LaravelZipPath = '',
        [string] $PublicHtmlZipPath = '',
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21
    )

    $useSplit = (Test-Path $LaravelZipPath) -and (Test-Path $PublicHtmlZipPath)

    if ($useSplit) {
        Write-Host "Uploading public_html.zip + laravel.zip (smaller than one big release.zip)..." -ForegroundColor Cyan
        if (-not (Send-FtpZipFile -LocalFile $PublicHtmlZipPath -RemoteFileName 'public_html.zip' -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort)) {
            return $false
        }
        if (-not (Send-FtpZipFile -LocalFile $LaravelZipPath -RemoteFileName 'laravel.zip' -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort)) {
            return $false
        }

        return $true
    }

    if (-not (Test-Path $ReleaseZipPath)) {
        throw "Missing release.zip at $ReleaseZipPath"
    }

    Write-Host "Uploading release.zip..." -ForegroundColor Cyan

    return (Send-FtpZipFile -LocalFile $ReleaseZipPath -RemoteFileName 'release.zip' -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort)
}

function Send-DeployFtpSync {
    param(
        [string] $LaravelLocal,
        [string] $PublicHtmlLocal,
        [string] $RemoteLaravel,
        [string] $RemotePublicHtml,
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21,
        [bool] $UseTls = $true
    )

    $winScp = Get-WinScpComPath
    if (-not $winScp) {
        return $false
    }

    $remoteLaravel = ($RemoteLaravel -replace '\\', '/').Trim('/')
    $remotePublic = ($RemotePublicHtml -replace '\\', '/').Trim('/')

    $tlsNote = if ($UseTls) { ' (FTPS)' } else { '' }
    Write-Host "WinSCP sync$tlsNote - changed files only..." -ForegroundColor Cyan
    $okLaravel = Send-WinScpFolderSync -LocalPath $LaravelLocal -RemotePath $remoteLaravel -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort -UseTls $UseTls
    $okPublic = Send-WinScpFolderSync -LocalPath $PublicHtmlLocal -RemotePath $remotePublic -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort -UseTls $UseTls

    return ($okLaravel -and $okPublic)
}

function Send-DeployFtpPackage {
    param(
        [string] $LaravelLocal,
        [string] $PublicHtmlLocal,
        [string] $RemoteLaravel,
        [string] $RemotePublicHtml,
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21,
        [string] $ReleaseZipPath = ''
    )

    if (Send-DeployFtpSync -LaravelLocal $LaravelLocal -PublicHtmlLocal $PublicHtmlLocal `
            -RemoteLaravel $RemoteLaravel -RemotePublicHtml $RemotePublicHtml `
            -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort) {
        return $true
    }

    if ($ReleaseZipPath -and (Test-Path $ReleaseZipPath)) {
        $laravelZip = Join-Path (Split-Path $ReleaseZipPath -Parent) 'laravel.zip'
        $publicZip = Join-Path (Split-Path $ReleaseZipPath -Parent) 'public_html.zip'
        return Send-DeployFtpZipRelease `
            -ReleaseZipPath $ReleaseZipPath `
            -LaravelZipPath $laravelZip `
            -PublicHtmlZipPath $publicZip `
            -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort
    }

    return $false
}
