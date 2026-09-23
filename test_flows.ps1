# Sarvam Real Estate - E2E Integration and Flow Verification (PowerShell)
$baseUrl = "http://127.0.0.1/Sarvam-Real-Estate"
$global:pass = 0
$global:fail = 0

function chk($label, $condition, $msg="") {
    if ($condition) {
        Write-Host "  [PASS]: $label" -ForegroundColor Green
        $global:pass++
    } else {
        Write-Host "  [FAIL]: $label" -ForegroundColor Red
        if ($msg) { Write-Host "     Note: $msg" -ForegroundColor Yellow }
        $global:fail++
    }
}

Write-Host "=== SARVAM REAL ESTATE - POWERSHELL FLOW VERIFICATION ===" -ForegroundColor Cyan

# 1. Access protected dashboard as guest
Write-Host "`n1. TESTING AUTHENTICATION SHIELD..." -ForegroundColor Yellow
$r = Invoke-WebRequest -Uri "$baseUrl/dashboard.php" -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
$ok = ($r.StatusCode -eq 302 -or ($r.StatusCode -eq 200 -and $r.Content -match "Location:|login.php"))
chk "dashboard.php redirects guest" $ok $r.StatusCode

$r = Invoke-WebRequest -Uri "$baseUrl/admin/index.php" -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
$ok = ($r.StatusCode -eq 302 -or ($r.StatusCode -eq 200 -and $r.Content -match "Location:|login.php"))
chk "admin/index.php redirects guest" $ok $r.StatusCode

# 2. Test User Login Flow
Write-Host "`n2. TESTING USER LOGIN FLOW..." -ForegroundColor Yellow
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$body = @{ email = "john@example.com"; password = "wrongpassword" }
$r = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body $body -WebSession $session -UseBasicParsing -ErrorAction SilentlyContinue
chk "Incorrect user password rejected" ($r.Content -match "Invalid email address or password|error")

$body = @{ email = "john@example.com"; password = "User@123" }
$r = Invoke-WebRequest -Uri "$baseUrl/login.php" -Method Post -Body $body -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
$ok = ($r.StatusCode -eq 302 -and ($r.Headers.Location -match "dashboard.php" -or $r.Content -match "dashboard.php"))
chk "Correct user password accepted" $ok $r.StatusCode

# View dashboard with logged-in session
$r = Invoke-WebRequest -Uri "$baseUrl/dashboard.php" -WebSession $session -UseBasicParsing -ErrorAction SilentlyContinue
$hasError = $r.Content -match "Warning:|Fatal error:|Notice:"
chk "Access dashboard.php after login" ($r.StatusCode -eq 200 -and $r.Content -match "Dashboard|Welcome" -and -not $hasError) "Has Error: $hasError"

# Test AJAX wishlist toggle
$body = @{ property_id = 1 }
$r = Invoke-WebRequest -Uri "$baseUrl/wishlist-action.php" -Method Post -Body $body -WebSession $session -UseBasicParsing -ErrorAction SilentlyContinue
chk "wishlist-action.php responds success" ($r.StatusCode -eq 200 -and $r.Content -match "success") $r.Content

# Logout
$r = Invoke-WebRequest -Uri "$baseUrl/logout.php" -WebSession $session -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
chk "User logout redirects" ($r.StatusCode -eq 302)

# 3. Test Admin Login Flow
Write-Host "`n3. TESTING ADMIN LOGIN FLOW..." -ForegroundColor Yellow
$adminSession = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$body = @{ email = "admin@sarvam.com"; password = "wrongpassword" }
$r = Invoke-WebRequest -Uri "$baseUrl/admin/login.php" -Method Post -Body $body -WebSession $adminSession -UseBasicParsing -ErrorAction SilentlyContinue
chk "Incorrect admin password rejected" ($r.Content -match "Invalid email|error")

$body = @{ email = "admin@sarvam.com"; password = "Admin@123" }
$r = Invoke-WebRequest -Uri "$baseUrl/admin/login.php" -Method Post -Body $body -WebSession $adminSession -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
$ok = ($r.StatusCode -eq 302 -and ($r.Headers.Location -match "index.php" -or $r.Content -match "index.php"))
chk "Correct admin password accepted" $ok $r.StatusCode

# Access admin dashboard with session
$r = Invoke-WebRequest -Uri "$baseUrl/admin/index.php" -WebSession $adminSession -UseBasicParsing -ErrorAction SilentlyContinue
$hasError = $r.Content -match "Warning:|Fatal error:|Notice:"
chk "Access admin/index.php after login" ($r.StatusCode -eq 200 -and $r.Content -match "Dashboard|Total Properties" -and -not $hasError) "Has Error: $hasError"

# 4. Check all admin subpages for PHP errors/warnings
Write-Host "`n4. TESTING ADMIN SUBPAGES ACCESSIBILITY AND STABILITY..." -ForegroundColor Yellow
$adminSubpages = @("properties.php", "users.php", "agents.php", "inquiries.php", "testimonials.php", "reports.php", "property-types.php", "locations.php")
foreach ($page in $adminSubpages) {
    $r = Invoke-WebRequest -Uri "$baseUrl/admin/$page" -WebSession $adminSession -UseBasicParsing -ErrorAction SilentlyContinue
    $hasError = $r.Content -match "Warning:|Fatal error:|Notice:"
    chk "Access admin/$page with no PHP errors" ($r.StatusCode -eq 200 -and -not $hasError) "Status: $($r.StatusCode), Has Error: $hasError"
}

# 5. Check user-side static pages
Write-Host "`n5. TESTING USER PAGES ACCESSIBILITY AND STABILITY..." -ForegroundColor Yellow
$userPages = @("index.php", "properties.php", "about.php", "contact.php", "property-detail.php?id=1")
foreach ($page in $userPages) {
    $r = Invoke-WebRequest -Uri "$baseUrl/$page" -UseBasicParsing -ErrorAction SilentlyContinue
    $hasError = $r.Content -match "Warning:|Fatal error:|Notice:"
    chk "Access /$page with no PHP errors" ($r.StatusCode -eq 200 -and -not $hasError) "Status: $($r.StatusCode), Has Error: $hasError"
}

Write-Host "`n==================================================" -ForegroundColor Cyan
$color = if ($global:fail -eq 0) { "Green" } else { "Red" }
Write-Host "FLOWS CHECK COMPLETE: $global:pass PASSED, $global:fail FAILED" -ForegroundColor $color
Write-Host "==================================================" -ForegroundColor Cyan

if ($global:fail -eq 0) {
    Write-Host "`nE2E FUNCTIONAL INTEGRATION SUCCESSFUL! PLATFORM IS XAMPP READY!`n" -ForegroundColor Green
} else {
    Write-Host "`nE2E FLOW CHECK DETECTED $global:fail FAILURE(S).`n" -ForegroundColor Red
}
