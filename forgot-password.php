<?php
/**
 * ============================================================
 *  FEATURE: 3-STEP PASSWORD RESET (No Email Token)
 *  File   : forgot-password.php
 *  Role   : Lets a user reset their password by verifying their
 *           registered email and a secret security answer.
 *           Does NOT require an email link/token — fully
 *           session-based with 3 sequential form steps.
 *
 *  Step 1 — Email Lookup:
 *    User enters email → system fetches security question
 *    → stores user ID in $_SESSION['reset_user_id']
 *
 *  Step 2 — Security Answer Verification:
 *    User types their answer → password_verify() checks it
 *    against the bcrypt-hashed answer in the database
 *
 *  Step 3 — New Password:
 *    User sets a new password (min 8 chars, confirmed twice)
 *    → password_hash(PASSWORD_BCRYPT) stores the new hash
 *    → $_SESSION['reset_user_id'] is cleared on success
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header("Location: " . SITE_URL . "/dashboard.php");
    exit();
}

$step = 1;
$email = '';
$question = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = (int)($_POST['step'] ?? 1);
    
    if ($step === 1) {
        $email = trim($_POST['email'] ?? '');
        if (empty($email)) {
            $error = 'Please enter your email address.';
        } else {
            // Find user and fetch security question
            $stmt = $conn->prepare("SELECT id, security_question FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['reset_user_id'] = $user['id'];
                $question = $user['security_question'];
                $step = 2;
            } else {
                $error = 'No user account found with that email address.';
            }
        }
    } elseif ($step === 2) {
        $answer = trim($_POST['answer'] ?? '');
        $user_id = (int)($_SESSION['reset_user_id'] ?? 0);

        if ($user_id <= 0) {
            $error = 'Session expired. Please try again.';
            $step = 1;
        } elseif (empty($answer)) {
            $error = 'Please provide the answer to your security question.';
            // Fetch security question again
            $stmt = $conn->prepare("SELECT security_question FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            $question = $user['security_question'] ?? '';
            $step = 2;
        } else {
            // Verify security answer (case-insensitive trim compare)
            $stmt = $conn->prepare("SELECT security_question, security_answer FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($user && password_verify(strtolower(trim($answer)), $user['security_answer'])) {
                $step = 3;
            } else {
                $error = 'Incorrect answer. Please try again.';
                $question = $user['security_question'] ?? '';
                $step = 2;
            }
        }
    } elseif ($step === 3) {
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $user_id = (int)($_SESSION['reset_user_id'] ?? 0);

        if ($user_id <= 0) {
            $error = 'Session expired. Please try again.';
            $step = 1;
        } elseif (empty($password) || empty($confirm_password)) {
            $error = 'Please enter and confirm your new password.';
            $step = 3;
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
            $step = 3;
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
            $step = 3;
        } else {
            // Update password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            
            if ($stmt->execute([$hashed_password, $user_id])) {
                $success = 'Your password has been reset successfully! You can now log in.';
                unset($_SESSION['reset_user_id']);
                $step = 1;
            } else {
                $error = 'Failed to reset password. Please try again.';
                $step = 3;
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper d-flex align-items-center justify-content-center py-5">
    <div class="auth-card card-sarvam bg-white border-0 rounded-4 p-4 mx-auto w-100 shadow-sarvam" style="max-width: 480px;">
        <div class="text-center mb-4">
            <div class="auth-icon-wrap rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center bg-sarvam-ice" style="width: 60px; height: 60px;">
                <i class="bi bi-shield-lock fs-3 text-sarvam"></i>
            </div>
            <h2 class="fw-bold text-heading mb-1">Reset Password</h2>
            <p class="text-muted small">Follow the steps to recover your account</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 small badge-danger">
                <i class="bi bi-exclamation-triangle-fill"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 small badge-success">
                <i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="form-sarvam">
            <input type="hidden" name="step" value="<?= $step ?>">

            <?php if ($step === 1): ?>
                <div class="mb-4">
                    <label class="form-label small fw-medium text-secondary">Registered Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-sarvam"></i></span>
                        <input type="email" name="email" class="form-control border-start-0" placeholder="name@example.com" required value="<?= htmlspecialchars($email) ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-sarvam w-100 py-2 rounded-3 fw-semibold"><i class="bi bi-arrow-right-circle me-2"></i>Continue</button>

            <?php elseif ($step === 2): ?>
                <div class="mb-4">
                    <label class="form-label small fw-medium text-secondary mb-2">Security Question</label>
                    <div class="card bg-light border-0 p-3 mb-3 small fw-medium text-dark text-sarvam">
                        <?= htmlspecialchars($question) ?>
                    </div>
                    <label class="form-label small fw-medium text-secondary">Your Secret Answer</label>
                    <input type="text" name="answer" class="form-control" placeholder="Enter security answer" required>
                </div>
                <button type="submit" class="btn btn-sarvam w-100 py-2 rounded-3 fw-semibold"><i class="bi bi-shield-check me-2"></i>Verify Answer</button>

            <?php elseif ($step === 3): ?>
                <div class="mb-3">
                    <label class="form-label small fw-medium text-secondary">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-medium text-secondary">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password" required minlength="8">
                </div>
                <button type="submit" class="btn btn-sarvam w-100 py-2 rounded-3 fw-semibold"><i class="bi bi-check-circle me-2"></i>Update Password</button>
            <?php endif; ?>
        </form>

        <div class="mt-4 text-center">
            <a href="<?= SITE_URL ?>/login.php" class="text-decoration-none small text-sarvam">
                <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
