<?php
/**
 * ============================================================
 *  FEATURE: USER REGISTRATION
 *  File   : register.php
 *  Role   : Collects new user details, validates all input,
 *           hashes the password AND security answer with bcrypt,
 *           inserts the user, then auto-logs them in.
 *
 *  Validations applied:
 *   • All required fields must be non-empty
 *   • Email must pass PHP filter_var(FILTER_VALIDATE_EMAIL)
 *   • Password minimum length: 8 characters
 *   • Password & Confirm Password must match
 *   • Email uniqueness checked via prepared statement
 *
 *  Security features:
 *   • password_hash(PASSWORD_BCRYPT) for password storage
 *   • password_hash(strtolower(answer)) for security answer
 *   • session_regenerate_id(true) prevents session fixation
 *   • PDO prepared statements prevent SQL Injection
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header("Location: " . SITE_URL . "/dashboard.php");
    exit();
}

$error = '';
$first_name = '';
$last_name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $security_question = trim($_POST['security_question'] ?? '');
    $security_answer = trim($_POST['security_answer'] ?? '');

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || empty($security_question) || empty($security_answer)) {
        $error = 'All fields marked with an asterisk (*) are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists using PDO prepared statements
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $exists = $stmt->fetch();

        if ($exists) {
            $error = 'This email address is already registered.';
        } else {
            // Hash password and security answer before storing
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $hashed_answer   = password_hash(strtolower(trim($security_answer)), PASSWORD_BCRYPT);
            $status = 'active';

            $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, phone, security_question, security_answer, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $inserted = $insert_stmt->execute([$first_name, $last_name, $email, $hashed_password, $phone, $security_question, $hashed_answer, $status]);

            if ($inserted) {
                $new_user_id = $conn->lastInsertId();

                // Prevent session fixation on auto-login after registration
                session_regenerate_id(true);
                $_SESSION['user_id']    = $new_user_id;
                $_SESSION['user_name']  = $first_name . ' ' . $last_name;
                $_SESSION['user_email'] = $email;
                $_SESSION['login_time'] = time();

                header("Location: " . SITE_URL . "/dashboard.php");
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
                $insert_stmt = null;
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper d-flex align-items-center justify-content-center py-5">
    <div class="auth-card card-sarvam bg-white border-0 rounded-4 p-4 mx-auto w-100 shadow-sarvam" style="max-width: 580px;">
        <div class="text-center mb-4">
            <div class="auth-icon-wrap rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center bg-sarvam-ice" style="width: 60px; height: 60px;">
                <i class="bi bi-person-plus fs-3 text-sarvam"></i>
            </div>
            <h2 class="fw-bold text-heading mb-1">Create Account</h2>
            <p class="text-muted small">Register to save wishlist and connect with agents</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 small badge-danger">
                <i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="form-sarvam">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-medium text-secondary">First Name *</label>
                    <input type="text" name="first_name" class="form-control" placeholder="Rahul" required value="<?= htmlspecialchars($first_name) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium text-secondary">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" placeholder="Sharma" required value="<?= htmlspecialchars($last_name) ?>">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-medium text-secondary">Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required value="<?= htmlspecialchars($email) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label small fw-medium text-secondary">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="e.g. +91 98765 43210" value="<?= htmlspecialchars($phone) ?>">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-medium text-secondary">Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required minlength="8">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-medium text-secondary">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required minlength="8">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-medium text-secondary">Security Question *</label>
                <select name="security_question" class="form-select text-secondary small" required>
                    <option value="">Select Security Question...</option>
                    <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                    <option value="What was your first pet's name?">What was your first pet's name?</option>
                    <option value="What city were you born in?">What city were you born in?</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-medium text-secondary">Security Answer *</label>
                <input type="text" name="security_answer" class="form-control" placeholder="Your Answer" required>
            </div>

            <button type="submit" class="btn btn-sarvam w-100 py-2 rounded-3 fw-semibold mb-3">Register</button>
            
            <p class="text-center text-secondary small mb-0">
                Already have an account? <a href="<?= SITE_URL ?>/login.php" class="text-decoration-none fw-medium text-sarvam">Login Here</a>
            </p>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
