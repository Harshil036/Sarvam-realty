<?php
/**
 * ============================================================
 *  FEATURE: COMBINED USER & ADMIN LOGIN
 *  File   : login.php
 *  Role   : Single login page for both regular users and admins.
 *           The system automatically detects the role from the
 *           email address — no separate admin URL needed.
 *
 *  Flow:
 *   1. If already logged in → redirect to correct dashboard
 *   2. On POST: check admin table first, then users table
 *   3. Verify bcrypt password hash with password_verify()
 *   4. Regenerate session ID to prevent session fixation
 *   5. Set session variables and redirect to dashboard
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';

/* ----------------------------------------------------------
 | FEATURE: ALREADY-LOGGED-IN REDIRECT
 |   Prevents a logged-in admin/user from seeing the login form
 ---------------------------------------------------------- */
if (isAdminLoggedIn()) {
    header("Location: " . SITE_URL . "/admin/index.php");
    exit();
}
if (isLoggedIn()) {
    header("Location: " . SITE_URL . "/dashboard.php");
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /* ----------------------------------------------------------
     | FEATURE: INPUT SANITIZATION
     |   trim() removes accidental leading/trailing spaces
     |   Empty-check before any DB query
     ---------------------------------------------------------- */
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        /* ----------------------------------------------------------
         | FEATURE: ADMIN AUTHENTICATION (Priority check)
         |   Queries the separate `admin` table first.
         |   Uses a PDO prepared statement to prevent SQL Injection.
         |   password_verify() safely compares against bcrypt hash.
         ---------------------------------------------------------- */
        $admin_stmt = $conn->prepare("SELECT id, name, email, password FROM admin WHERE email = ? LIMIT 1");
        $admin_stmt->execute([$email]);
        $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            /* ----------------------------------------------------------
             | FEATURE: SESSION FIXATION PREVENTION
             |   session_regenerate_id(true) issues a new session ID
             |   on every successful login so an attacker cannot reuse
             |   a pre-auth session ID they may have obtained.
             ---------------------------------------------------------- */
            session_regenerate_id(true);
            $_SESSION['admin_id']         = $admin['id'];
            $_SESSION['admin_name']       = $admin['name'];
            $_SESSION['admin_email']      = $admin['email'];
            $_SESSION['admin_login_time'] = time(); // used for 2-hour timeout

            header("Location: " . SITE_URL . "/admin/index.php");
            exit();
        }

        /* ----------------------------------------------------------
         | FEATURE: USER AUTHENTICATION
         |   Falls through to user table only if admin check fails.
         |   Also checks account status (banned / inactive) before
         |   verifying the password to give a clear error message.
         ---------------------------------------------------------- */
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, status FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            /* ----------------------------------------------------------
             | FEATURE: ACCOUNT STATUS CHECK
             |   Blocks banned, suspended, or inactive accounts before
             |   password verification to avoid timing-based leaks.
             ---------------------------------------------------------- */
            if (in_array($user['status'], ['banned', 'suspended', 'inactive'])) {
                $error = 'Your account has been suspended. Please contact customer support.';
            } elseif (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['login_time'] = time(); // used for 2-hour inactivity timeout

                header("Location: " . SITE_URL . "/dashboard.php");
                exit();
            } else {
                $error = 'Invalid email address or password. Please try again.';
            }
        } else {
            /* Generic error message — does not reveal whether email exists */
            $error = 'Invalid email address or password. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.role-tab { cursor:pointer; padding:10px 20px; border-radius:8px; font-size:.875rem; font-weight:600; transition:all .2s; border:2px solid transparent; }
.role-tab.active { background:var(--sarvam-primary, #E8671A); color:#fff; border-color:var(--sarvam-primary, #E8671A); }
.role-tab:not(.active) { background:#f8f9fa; color:#6c757d; border-color:#dee2e6; }
.role-tab:not(.active):hover { border-color:var(--sarvam-primary, #E8671A); color:var(--sarvam-primary, #E8671A); }
.role-badge { display:inline-flex; align-items:center; gap:6px; font-size:.7rem; font-weight:700; letter-spacing:.5px; text-transform:uppercase; padding:3px 10px; border-radius:20px; }
.badge-user  { background:#E8F4FD; color:#1a6fc4; }
.badge-admin { background:#FFF3CD; color:#856404; }
</style>
<div class="auth-wrapper d-flex align-items-center justify-content-center py-5">
    <div class="auth-card card-sarvam bg-white border-0 rounded-4 p-4 mx-auto w-100 shadow-sarvam" style="max-width: 480px;">
        <div class="text-center mb-4">
            <div class="auth-icon-wrap rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center bg-sarvam-ice" style="width: 60px; height: 60px;">
                <i class="bi bi-shield-lock fs-3 text-sarvam"></i>
            </div>
            <h2 class="fw-bold text-heading mb-1">Sign In</h2>
            <p class="text-muted small mb-3">One login for Users &amp; Admins</p>

            <!-- Role Tab Switcher -->
            <div class="d-flex gap-2 justify-content-center mb-1" id="roleTabs">
                <div class="role-tab active" id="tabUser" onclick="switchRole('user')">
                    <i class="bi bi-person me-1"></i> User
                </div>
                <div class="role-tab" id="tabAdmin" onclick="switchRole('admin')">
                    <i class="bi bi-shield-shaded me-1"></i> Admin
                </div>
            </div>
            <div id="roleHint" class="text-muted" style="font-size:.75rem; min-height:1.2em;">
                Sign in to browse, save properties &amp; send inquiries
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 small">
                <i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="form-sarvam" id="loginForm">
            <div class="mb-3">
                <label class="form-label small fw-medium text-secondary">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-sarvam"></i></span>
                    <input type="email" name="email" id="emailInput" class="form-control border-start-0"
                           placeholder="name@example.com" required
                           value="<?= htmlspecialchars($email) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-medium text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-sarvam"></i></span>
                    <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                    <button type="button" class="btn btn-light border-start-0 border" onclick="togglePwd(this)" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 small">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label text-secondary" for="rememberMe">Remember me</label>
                </div>
                <a href="<?= SITE_URL ?>/forgot-password.php" class="text-decoration-none text-sarvam" id="forgotLink">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-sarvam w-100 py-2 rounded-3 fw-semibold mb-3" id="loginBtn">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login as User
            </button>

            <div id="registerRow" class="text-center text-secondary small mb-0">
                Don't have an account? <a href="<?= SITE_URL ?>/register.php" class="text-decoration-none fw-medium text-sarvam">Register Here</a>
            </div>
        </form>
    </div>
</div>
<script>
const hints = {
    user:  'Sign in to browse, save properties &amp; send inquiries',
    admin: 'Admin panel access — manage properties, users &amp; inquiries'
};
const placeholders = {
    user:  'name@example.com',
    admin: 'admin@sarvam.com'
};
function switchRole(role) {
    document.getElementById('tabUser').classList.toggle('active', role === 'user');
    document.getElementById('tabAdmin').classList.toggle('active', role === 'admin');
    document.getElementById('roleHint').innerHTML = hints[role];
    document.getElementById('emailInput').placeholder = placeholders[role];
    document.getElementById('loginBtn').innerHTML =
        '<i class="bi bi-box-arrow-in-right me-2"></i>Login as ' +
        (role === 'admin' ? 'Admin' : 'User');
    document.getElementById('registerRow').style.display = role === 'admin' ? 'none' : '';
    document.getElementById('forgotLink').style.display = role === 'admin' ? 'none' : '';
}
function togglePwd(btn) {
    const input = btn.closest('.input-group').querySelector('input[type]');
    const icon  = btn.querySelector('i');
    if (input.type === 'password') { input.type = 'text';     icon.className = 'bi bi-eye-slash'; }
    else                           { input.type = 'password'; icon.className = 'bi bi-eye'; }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
