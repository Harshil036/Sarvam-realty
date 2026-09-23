# Sarvam Real Estate - Frontend Layout, Links and Image Audit Script
$baseUrl = "http://127.0.0.1/Sarvam-Real-Estate"
$root = "C:\xampp\htdocs\Sarvam-Real-Estate"
$global:pass = 0
$global:fail = 0

function audit($label, $condition, $msg="") {
    if ($condition) {
        Write-Host "  [AUDIT-PASS]: $label" -ForegroundColor Green
        $global:pass++
    } else {
        Write-Host "  [AUDIT-FAIL]: $label" -ForegroundColor Red
        if ($msg) { Write-Host "     Details: $msg" -ForegroundColor Yellow }
        $global:fail++
    }
}

Write-Host "=== SARVAM REAL ESTATE - FRONTEND INTEGRITY AUDIT ===" -ForegroundColor Cyan

# 1. Check all local files referenced in the include files
Write-Host "`n1. AUDITING CORE ASSETS AND INCLUDES..." -ForegroundColor Yellow

$headerContent = Get-Content "$root\includes\header.php" -Raw
$footerContent = Get-Content "$root\includes\footer.php" -Raw

# Check stylesheet files exist
audit "style.css file exists" (Test-Path "$root\assets\css\style.css")
audit "admin.css file exists" (Test-Path "$root\assets\css\admin.css")
audit "main.js file exists" (Test-Path "$root\assets\js\main.js")
audit "admin.js file exists" (Test-Path "$root\assets\js\admin.js")

# Check Bootstrap CDN links
audit "header.php includes Bootstrap CSS" ($headerContent -match "bootstrap.min.css")
audit "footer.php includes Bootstrap JS" ($footerContent -match "bootstrap.bundle.min.css|bootstrap.bundle.min.js")
audit "header.php includes Bootstrap Icons" ($headerContent -match "bootstrap-icons")

# 2. Check for old themes/colors in CSS
Write-Host "`n2. AUDITING CSS VARIABLE SCHEMES..." -ForegroundColor Yellow
$styleCss = Get-Content "$root\assets\css\style.css" -Raw
$adminCss = Get-Content "$root\assets\css\admin.css" -Raw

$oldVars = @("--primary:","#2563EB","#1E40AF","#3B82F6")
$foundOldVar = $false
foreach($v in $oldVars) {
    if ($styleCss -match $v) {
        $foundOldVar = $true
        Write-Host "     Found old theme marker in style.css: $v" -ForegroundColor Yellow
    }
}
audit "style.css uses new color palette and has no raw old blue vars" (-not $foundOldVar)

# 3. Check for broken images/icons on the home page and properties list
Write-Host "`n3. AUDITING USER-SIDE HTML RENDER FOR BROKEN ASSETS..." -ForegroundColor Yellow
$html = Invoke-WebRequest -Uri "$baseUrl/index.php" -UseBasicParsing
$matches = [regex]::matches($html.Content, '<img\s+[^>]*src="([^"]+)"')
$brokenImages = 0
foreach ($m in $matches) {
    $imgSrc = $m.Groups[1].Value
    # Resolve relative URL
    if ($imgSrc -match "^http") {
        $imgUrl = $imgSrc
    } else {
        $imgUrl = "$baseUrl/" + $imgSrc.TrimStart('/')
    }
    
    # Verify local file if relative
    if ($imgSrc -notmatch "^http") {
        $cleanPath = $imgSrc.Split('?')[0].TrimStart('/')
        # Replace Upload Url with local path
        $cleanPath = $cleanPath.Replace("assets/", "assets/")
        $localPath = Join-Path $root $cleanPath
        if (-not (Test-Path $localPath)) {
            $brokenImages++
            Write-Host "     Missing local image: $imgSrc (resolved to: $localPath)" -ForegroundColor Yellow
        }
    }
}
audit "All index.php local images exist on disk" ($brokenImages -eq 0) "Broken local images found: $brokenImages"

# 4. Check Bootstrap responsive containers and viewport tags
Write-Host "`n4. AUDITING MOBILE RESPONSIVENESS TAGS..." -ForegroundColor Yellow
audit "header.php has viewport meta tag" ($headerContent -match 'name="viewport"')
$adminHeader = Get-Content "$root\admin\includes\header.php" -Raw
audit "admin header.php has viewport meta tag" ($adminHeader -match 'name="viewport"')

# 5. Check all PHP files for leftovers of class "btn-primary" when they should be "btn-sarvam"
Write-Host "`n5. AUDITING FOR BOOTSTRAP BUTTON CLASS MIGRATE..." -ForegroundColor Yellow
$allPhpFiles = Get-ChildItem "$root" -Filter "*.php" -Recurse | Where-Object { $_.FullName -notmatch "verify_all.php|test_flows.php|run_flows.php|test_conn.php|fix_pwd.php" }
$blueBtns = 0
foreach($f in $allPhpFiles) {
    $c = Get-Content $f.FullName -Raw
    # We check if there's any btn-primary left that hasn't been migrated
    # (Allowing admin files to have btn-admin-primary)
    if ($c -match "btn-primary" -and $f.FullName -notmatch "admin") {
        $blueBtns++
        $relPath = $f.FullName.Replace($root + "\", "")
        Write-Host "     Leftover btn-primary in user file: $relPath" -ForegroundColor Yellow
    }
}
audit "No raw btn-primary button classes remaining in user files" ($blueBtns -eq 0) "Files with btn-primary: $blueBtns"

Write-Host "`n==================================================" -ForegroundColor Cyan
$color = if ($global:fail -eq 0) { "Green" } else { "Red" }
Write-Host "AUDIT COMPLETE: $global:pass PASSED, $global:fail FAILED" -ForegroundColor $color
Write-Host "==================================================" -ForegroundColor Cyan
