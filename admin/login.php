<?php
/**
 * ============================================================
 *  FEATURE: ADMIN LOGIN (Standalone Fallback)
 *  File   : admin/login.php
 *  Role   : Dedicated admin login form (accessible directly via
 *           /admin/login.php). In practice the combined /login.php
 *           handles both roles; this page is retained as a direct
 *           admin entry point.
 *
 *  Security:
 *   • PDO prepared statement prevents SQL Injection
 *   • password_verify() checks bcrypt admin password hash
 *   • session_regenerate_id(true) prevents session fixation
 *   • admin_login_time stored for 2-hour inactivity timeout
 * ============================================================
 */
session_start();

// Already logged in → redirect
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Fetch admin by email using prepared statement
        $stmt = $conn->prepare("SELECT id, name, email, password FROM admin WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $admin  = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']         = $admin['id'];
            $_SESSION['admin_name']       = $admin['name'];
            $_SESSION['admin_email']      = $admin['email'];
            $_SESSION['admin_login_time'] = time();
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login — Sarvam Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= defined('SITE_URL') ? SITE_URL : 'http://localhost/Sarvam-Real-Estate' ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body>
<div class="admin-login-page">
    <div class="admin-login-card">
        <!-- Logo -->
        <div class="text-center mb-4">
            <div class="admin-login-logo mb-3">
                <i class="bi bi-houses-fill text-white fs-4"></i>
            </div>
            <h2 style="font-family:'Poppins',sans-serif;font-size:1.5rem;font-weight:700;color:#2D343D;">
                Sarvam Admin
            </h2>
            <p style="font-size:0.875rem;color:#465461;">Sign in to manage your real estate platform</p>
        </div>

        <?php if ($error): ?>
            <div class="admin-alert danger mb-4">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off" id="loginForm" class="admin-form">
            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <div class="position-relative">
                    <i class="bi bi-envelope position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#465461;"></i>
                    <input type="email" name="email" id="email" class="form-control" required
                           style="padding-left:2.5rem;"
                           placeholder="admin@sarvam.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="position-relative">
                    <i class="bi bi-lock position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#465461;"></i>
                    <input type="password" name="password" id="password" class="form-control" required
                           style="padding-left:2.5rem;" placeholder="Enter your password">
                    <button type="button" onclick="togglePwd()" class="position-absolute"
                            style="right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#465461;cursor:pointer;">
                        <i class="bi bi-eye" id="pwdEye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-admin-primary w-100" id="loginBtn" style="padding:0.75rem;font-size:1rem;border:none;border-radius:8px;">
                <i class="bi bi-box-arrow-in-right"></i> Sign In to Admin Panel
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= defined('SITE_URL') ? SITE_URL : 'http://localhost/Sarvam-Real-Estate' ?>/"
               style="font-size:0.8rem;color:#729CA2;text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Back to Website
            </a>
        </div>

    </div>
</div>
<script>
function togglePwd() {
    const p = document.getElementById('password');
    const eye = document.getElementById('pwdEye');
    if (p.type === 'password') {
        p.type = 'text';
        eye.className = 'bi bi-eye-slash';
    } else {
        p.type = 'password';
        eye.className = 'bi bi-eye';
    }
}
document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('loginBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Signing In...';
    document.getElementById('loginBtn').disabled = true;
});
</script>
</body>
</html>
