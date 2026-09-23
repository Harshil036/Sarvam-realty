<?php
/**
 * Admin - Edit User
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'users';
$page_title   = 'Edit User';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: users.php'); exit; }

$st = $conn->prepare("SELECT * FROM users WHERE id=?");
$st->execute([$id]);
$user = $st->fetch(PDO::FETCH_ASSOC);
if (!$user) { header('Location: users.php'); exit; }

$errors=[];$success='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $fn     = trim($_POST['first_name'] ?? '');
    $ln     = trim($_POST['last_name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $addr   = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if (!$fn||!$ln||!$email) $errors[]='Name and email are required.';

    if (empty($errors)) {
        try {
            $st = $conn->prepare("UPDATE users SET first_name=?,last_name=?,email=?,phone=?,address=?,status=?,updated_at=NOW() WHERE id=?");
            $st->execute([$fn, $ln, $email, $phone, $addr, $status, $id]);
            $success='User updated successfully.'; 
            $user['first_name']=$fn;$user['last_name']=$ln;$user['email']=$email;$user['phone']=$phone;$user['address']=$addr;$user['status']=$status;
        } catch (PDOException $e) {
            $errors[]='DB error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i></a>
    <span class="separator">/</span><a href="users.php">Users</a>
    <span class="separator">/</span><span>Edit</span>
</div>

<?php if ($success): ?><div class="admin-alert success"><i class="bi bi-check-circle"></i> <?= $success ?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="admin-alert danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= implode(', ',$errors) ?></div><?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size:1.3rem;margin:0;color:#2D343D;">Edit User #<?= $id ?></h2>
    <a href="users.php" class="btn-admin-outline btn-admin-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-person"></i> User Information</h5>
            </div>
            <div class="admin-card-body">
                <form method="POST" class="admin-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color:#2D343D;font-weight:600;">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($user['first_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color:#2D343D;font-weight:600;">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($user['last_name']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color:#2D343D;font-weight:600;">Email *</label>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color:#2D343D;font-weight:600;">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="color:#2D343D;font-weight:600;">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color:#2D343D;font-weight:600;">Account Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= $user['status']=='active' ? 'selected' : '' ?>>Active</option>
                                <option value="suspended" <?= $user['status']=='suspended' ? 'selected' : '' ?>>Suspended</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn-admin-primary"><i class="bi bi-save"></i> Save Changes</button>
                        <a href="users.php" class="btn-admin-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-info-circle"></i> Account Info</h5>
            </div>
            <div class="admin-card-body">
                <table class="w-100" style="font-size:0.875rem;">
                    <tr><td style="color:#465461;padding:6px 0;">Member Since</td><td style="color:#2D343D;"><?= date('d M Y',strtotime($user['created_at'])) ?></td></tr>
                    <tr><td style="color:#465461;padding:6px 0;">Status</td>
                        <td><span class="status-badge <?= $user['status']=='active' ? 'active' : 'pending' ?>"><?= ucfirst($user['status']) ?></span></td></tr>
                    <tr><td style="color:#465461;padding:6px 0;">Security Q</td><td style="font-size:0.8rem;color:#2D343D;"><?= htmlspecialchars($user['security_question'] ?? '—') ?></td></tr>
                </table>
                <hr style="border-color:#C4DCDF;">
                <a href="users.php?delete=<?= $id ?>" class="btn-admin-danger w-100 btn-admin-sm"
                   onclick="return confirm('Delete this user permanently?')">
                    <i class="bi bi-trash"></i> Delete User
                </a>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
