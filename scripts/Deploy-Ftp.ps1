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

function Send-WinScpFolderSync {
    param(
        [string] $LocalPath,
        [string] $RemotePath,
        [string] $FtpHost,
        [string] $FtpUser,
        [string] $FtpPassword,
        [int] $FtpPort = 21
    )

    $winScp = Get-WinScpComPath
    if (-not $winScp) {
        return $false
    }

    $remotePath = $RemotePath.TrimEnd('/') + '/'
    $scriptPath = [System.IO.Path]::GetTempFileName() + '.txt'
    $logPath = [System.IO.Path]::GetTempFileName() + '.log'

    $escapedPassword = $FtpPassword -replace '"', '""'
    $escapedUser = [Uri]::EscapeDataString($FtpUser)
    $script = @"
option batch abort
option confirm off
open ftp://${escapedUser}@${FtpHost}:${FtpPort}/ -password="$escapedPassword"
synchronize remote -delete "$LocalPath" "$remotePath"
exit
"@

    try {
        Set-Content -Path $scriptPath -Value $script -Encoding ASCII
        & $winScp "/ini=nul" "/log=$logPath" "/script=$scriptPath"
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
        [int] $FtpPort = 21
    )

    $winScp = Get-WinScpComPath
    if (-not $winScp) {
        return $false
    }

    $escapedPassword = $FtpPassword -replace '"', '""'
    $escapedUser = [Uri]::EscapeDataString($FtpUser)
    $remoteName = $RemoteFileName.TrimStart('/')
    $scriptPath = [System.IO.Path]::GetTempFileName() + '.txt'
    $script = @"
option batch abort
option confirm off
open ftp://${escapedUser}@${FtpHost}:${FtpPort}/ -password="$escapedPassword"
put "$LocalFile" $remoteName
exit
"@

    try {
        Set-Content -Path $scriptPath -Value $script -Encoding ASCII
        & $winScp "/ini=nul" "/script=$scriptPath"
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

    if ($ReleaseZipPath -and (Test-Path $ReleaseZipPath)) {
        $laravelZip = Join-Path (Split-Path $ReleaseZipPath -Parent) 'laravel.zip'
        $publicZip = Join-Path (Split-Path $ReleaseZipPath -Parent) 'public_html.zip'
        return Send-DeployFtpZipRelease `
            -ReleaseZipPath $ReleaseZipPath `
            -LaravelZipPath $laravelZip `
            -PublicHtmlZipPath $publicZip `
            -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort
    }

    $remoteLaravel = ($RemoteLaravel -replace '\\', '/').Trim('/')
    $remotePublic = ($RemotePublicHtml -replace '\\', '/').Trim('/')

    if (Get-WinScpComPath) {
        Write-Host "Using WinSCP to upload (faster)..." -ForegroundColor Cyan
        $okLaravel = Send-WinScpFolderSync -LocalPath $LaravelLocal -RemotePath "/$remoteLaravel" -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort
        $okPublic = Send-WinScpFolderSync -LocalPath $PublicHtmlLocal -RemotePath "/$remotePublic" -FtpHost $FtpHost -FtpUser $FtpUser -FtpPassword $FtpPassword -FtpPort $FtpPort
        return ($okLaravel -and $okPublic)
    }

    throw "release.zip missing and WinSCP not installed. Re-run deploy or install WinSCP from winscp.net"
}
