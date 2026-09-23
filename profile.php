<?php
/**
 * ============================================================
 *  FEATURE: USER PROFILE MANAGEMENT
 *  File   : profile.php
 *  Role   : Allows logged-in users to update their personal
 *           information, change their password, and upload a
 *           profile photo.
 *
 *  Sections:
 *   • Profile Info Update — first name, last name, phone
 *   • Password Change — current password verified first,
 *     new password hashed with PASSWORD_BCRYPT
 *   • Profile Image Upload — validates file type (JPG/PNG/GIF/WEBP)
 *     and size (max 2MB), saves to assets/uploads/profiles/
 *
 *  Security:
 *   • requireLogin() → only authenticated users can access
 *   • password_verify() before allowing password change
 *   • PDO prepared statements on all UPDATE queries
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';
requireLogin();

$user_id = (int)$_SESSION['user_id'];
$success_msg = '';
$error_msg = '';
$active_tab = 'profile';

// Fetch current user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Profile Details Update
    if (isset($_POST['update_profile'])) {
        $active_tab = 'profile';
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        if (empty($first_name) || empty($last_name)) {
            $error_msg = 'First name and last name are required fields.';
        } else {
            // Handle Profile Image Upload
            $profile_image = $user['profile_image'];
            if (!empty($_FILES['profile_image']['name'])) {
                $upload = uploadImage($_FILES['profile_image'], 'users');
                if ($upload['success']) {
                    // Delete old profile image if it exists
                    if (!empty($user['profile_image']) && file_exists(UPLOAD_PATH . 'users/' . $user['profile_image'])) {
                        unlink(UPLOAD_PATH . 'users/' . $user['profile_image']);
                    }
                    $profile_image = $upload['filename'];
                    $_SESSION['profile_image'] = $profile_image;
                } else {
                    $error_msg = $upload['error'];
                }
            }

            if (empty($error_msg)) {
                $up_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?, profile_image = ?, updated_at = NOW() WHERE id = ?");
                
                if ($up_stmt->execute([$first_name, $last_name, $phone, $address, $profile_image, $user_id])) {
                    $success_msg = 'Profile details updated successfully!';
                    $_SESSION['user_name'] = $first_name . ' ' . $last_name;
                    
                    // Refresh user data
                    $user['first_name'] = $first_name;
                    $user['last_name'] = $last_name;
                    $user['phone'] = $phone;
                    $user['address'] = $address;
                    $user['profile_image'] = $profile_image;
                } else {
                    $error_msg = 'Failed to update profile details. Please try again.';
                }
            }
        }
    }
    
    // Handle Password Change Update
    if (isset($_POST['change_password'])) {
        $active_tab = 'password';
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_msg = 'All password fields are required.';
        } elseif (strlen($new_password) < 8) {
            $error_msg = 'New password must be at least 8 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error_msg = 'New password and confirm password do not match.';
        } else {
            // Verify current password first
            if (password_verify($current_password, $user['password'])) {
                $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
                $pwd_stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                
                if ($pwd_stmt->execute([$hashed_new_password, $user_id])) {
                    $success_msg = 'Password changed successfully!';
                } else {
                    $error_msg = 'Failed to update password. Please try again.';
                }
            } else {
                $error_msg = 'Incorrect current password.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav class="mb-4 small">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-sarvam text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/dashboard.php" class="text-sarvam text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active text-muted">Profile</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <div class="card-sarvam p-4 text-center mb-4">
                <div class="position-relative d-inline-block mx-auto mb-3">
                    <?php if (!empty($user['profile_image']) && file_exists(UPLOAD_PATH . 'users/' . $user['profile_image'])): ?>
                        <img src="<?= UPLOAD_URL ?>users/<?= $user['profile_image'] ?>" alt="Profile photo" class="rounded-circle shadow-sarvam" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #FF893B;">
                    <?php else: ?>
                        <div class="bg-sarvam-ice text-sarvam rounded-circle d-flex align-items-center justify-content-center mx-auto shadow-sarvam" style="width: 120px; height: 120px; border: 3px solid #FF893B;">
                            <i class="bi bi-person fs-1"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <h4 class="fw-bold font-poppins text-heading mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                <p class="text-muted font-poppins small mb-3"><?= htmlspecialchars($user['email']) ?></p>
                <hr style="border-color:#C4DCDF;">
                <div class="nav flex-column nav-pills text-start" id="profileTabs" role="tablist" style="--bs-nav-pills-link-active-bg: #729CA2;">
                    <button class="nav-link text-heading <?= $active_tab === 'profile' ? 'active text-white' : '' ?>" id="edit-profile-tab" data-bs-toggle="pill" data-bs-target="#edit-profile" type="button" role="tab">
                        <i class="bi bi-person-gear me-2"></i> Edit Profile Details
                    </button>
                    <button class="nav-link text-heading mt-2 <?= $active_tab === 'password' ? 'active text-white' : '' ?>" id="change-pwd-tab" data-bs-toggle="pill" data-bs-target="#change-pwd" type="button" role="tab">
                        <i class="bi bi-shield-lock me-2"></i> Change Password
                    </button>
                </div>
            </div>
        </div>

        <!-- Forms Details -->
        <div class="col-lg-9">
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success font-poppins small mb-4" style="background:#ECFDF5; border-color:#34D399; color:#065F46;">
                    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger font-poppins small mb-4" style="background:#FEF2F2; border-color:#F87171; color:#991B1B;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error_msg) ?>
                </div>
            <?php endif; ?>

            <div class="tab-content" id="profileTabContent">
                <!-- Edit Profile Tab -->
                <div class="tab-pane fade <?= $active_tab === 'profile' ? 'show active' : '' ?>" id="edit-profile" role="tabpanel">
                    <div class="card-sarvam p-4">
                        <h5 class="fw-bold font-poppins text-heading border-bottom pb-3 mb-4"><i class="bi bi-person-lines-fill me-2 text-sarvam"></i>Edit Profile Details</h5>
                        <form method="POST" enctype="multipart/form-data" class="form-sarvam">
                            <input type="hidden" name="update_profile" value="1">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label font-poppins small fw-medium text-heading">First Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-sarvam text-sarvam"><i class="bi bi-person"></i></span>
                                        <input type="text" name="first_name" class="form-control" required value="<?= htmlspecialchars($user['first_name']) ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-poppins small fw-medium text-heading">Last Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-sarvam text-sarvam"><i class="bi bi-person"></i></span>
                                        <input type="text" name="last_name" class="form-control" required value="<?= htmlspecialchars($user['last_name']) ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-poppins small fw-medium text-heading">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-sarvam text-sarvam"><i class="bi bi-telephone"></i></span>
                                    <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-poppins small fw-medium text-heading">Mailing Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-sarvam text-sarvam"><i class="bi bi-geo-alt"></i></span>
                                    <textarea name="address" class="form-control" rows="3" placeholder="Street, City, State, PIN"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label font-poppins small fw-medium text-heading">Update Profile Photo</label>
                                <input type="file" name="profile_image" class="form-control" accept="image/*">
                                <small class="text-muted font-poppins small mt-1 d-block">Supported formats: JPG, JPEG, PNG, WEBP. Max size: 5MB.</small>
                            </div>
                            <button type="submit" class="btn btn-sarvam px-4 py-2 fw-semibold font-poppins"><i class="bi bi-save me-2"></i>Save Profile Changes</button>
                        </form>
                    </div>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-pane fade <?= $active_tab === 'password' ? 'show active' : '' ?>" id="change-pwd" role="tabpanel">
                    <div class="card-sarvam p-4">
                        <h5 class="fw-bold font-poppins text-heading border-bottom pb-3 mb-4"><i class="bi bi-shield-lock me-2 text-sarvam"></i>Change Password</h5>
                        <form method="POST" class="form-sarvam">
                            <input type="hidden" name="change_password" value="1">
                            <div class="mb-3">
                                <label class="form-label font-poppins small fw-medium text-heading">Current Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-sarvam text-sarvam"><i class="bi bi-key"></i></span>
                                    <input type="password" name="current_password" class="form-control" required placeholder="Enter current password">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label font-poppins small fw-medium text-heading">New Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-sarvam text-sarvam"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" name="new_password" class="form-control" required minlength="8" placeholder="At least 8 characters">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label font-poppins small fw-medium text-heading">Confirm New Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-sarvam text-sarvam"><i class="bi bi-shield-check"></i></span>
                                    <input type="password" name="confirm_password" class="form-control" required minlength="8" placeholder="Confirm new password">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sarvam px-4 py-2 fw-semibold font-poppins"><i class="bi bi-check2-circle me-2"></i>Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
