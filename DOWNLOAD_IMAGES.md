# Download Images from Live Site

Your images are at: https://minilicenseplates.com/plates

## Option 1: Download using PowerShell (Windows)

Run this in PowerShell (as Administrator if needed):

```powershell
cd d:\aamlpproj
$baseUrl = "https://minilicenseplates.com/plates"
$sets = @('c36g','c37g','c38g','c39g','c39w','m39g','c49t','c50t','c52m','m53p','m54p','c53c','m54q','m55l','m59p','m60p','s61t','m63p','s63g','s66g','m68m','m68q','m68p','m70p','m75p','m78p','s78s','m79p','m80p','m81p','m82p','m83p','m84p','m86p','m87p','m88p','m89p','m90p')

foreach ($set in $sets) {
    $setDir = "public\plates\$set"
    New-Item -Path $setDir -ItemType Directory -Force | Out-Null
    Write-Host "Created directory for $set"
}
```

Then use a tool like `wget` or `curl` to download, or manually copy the folders.

## Option 2: Manual Copy

1. Access your live site's FTP/server
2. Download the entire `/plates` folder
3. Copy it to `d:\aamlpproj\public\plates`

## Option 3: Use Laravel to reference remote images

If you want to keep images on the live server, I can update the code to reference them remotely.
