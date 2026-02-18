# Download plates images from live site
$baseUrl = "https://minilicenseplates.com/plates"
$sets = @('c36g','c37g','c38g','c39g','c39w','m39g','c49t','c50t','c52m','m53p','m54p','c53c','m54q','m55l','m59p','m60p','s61t','m63p','s63g','s66g','m68m','m68q','m68p','m70p','m75p','m78p','s78s','m79p','m80p','m81p','m82p','m83p','m84p','m86p','m87p','m88p','m89p','m90p')

foreach ($set in $sets) {
    $setDir = "public\plates\$set"
    if (-not (Test-Path $setDir)) {
        New-Item -Path $setDir -ItemType Directory -Force | Out-Null
        Write-Host "Created $setDir"
    }
}

Write-Host "`nDirectories created. You can now:"
Write-Host "1. Use FTP/SFTP to download images from $baseUrl to public\plates\"
Write-Host "2. Or manually copy the plates folder from your server"
