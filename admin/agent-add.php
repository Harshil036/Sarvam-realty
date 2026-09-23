<?php
/**
 * Admin - Add Agent
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'agents';
$page_title   = 'Add Agent';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');
    $exp      = (int)($_POST['experience_years'] ?? 0);
    $sold     = (int)($_POST['properties_sold'] ?? 0);
    $rating   = min(5.0, max(0, (float)($_POST['rating'] ?? 4.0)));
    $status   = $_POST['status'] ?? 'active';

    if (!$name) $errors[] = 'Agent name is required.';
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

    $photo = '';
    if (!empty($_FILES['photo']['name'])) {
        $upload_dir = UPLOAD_PATH . 'agents/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp']) && $_FILES['photo']['size'] <= 3000000) {
            $photo = uniqid('agent_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $photo)) {
                $errors[] = 'Failed to upload photo.';
                $photo = '';
            }
        } else { $errors[] = 'Photo must be JPG/PNG/WEBP under 3MB.'; }
    }

    if (empty($errors)) {
        $st = $conn->prepare("INSERT INTO agents (name,email,phone,bio,experience_years,properties_sold,rating,photo,status,created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())");
        if ($st->execute([$name,$email,$phone,$bio,$exp,$sold,$rating,$photo,$status])) {
            header('Location: agents.php?added=1'); exit;
        } else {
            $errors[] = 'Failed to add agent.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i></a>
    <span class="separator">/</span><a href="agents.php">Agents</a>
    <span class="separator">/</span><span>Add Agent</span>
</div>

<?php if (!empty($errors)): ?>
    <div class="admin-alert danger"><i class="bi bi-exclamation-triangle-fill"></i>
        <div><?= implode('<br>',$errors) ?></div></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size:1.3rem;margin:0;color:#2D343D;">Add New Agent</h2>
    <a href="agents.php" class="btn-admin-outline btn-admin-sm"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<form method="POST" enctype="multipart/form-data" class="admin-form">
<div class="row g-4">
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-person-badge"></i> Agent Details</h5>
            </div>
            <div class="admin-card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Full Name *</label>
                        <input type="text" name="name" class="form-control" required placeholder="Rahul Sharma" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Email *</label>
                        <input type="email" name="email" class="form-control" required placeholder="agent@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Experience (Years)</label>
                        <input type="number" name="experience_years" class="form-control" min="0" max="50" value="<?= htmlspecialchars($_POST['experience_years'] ?? '5') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Properties Sold</label>
                        <input type="number" name="properties_sold" class="form-control" min="0" value="<?= htmlspecialchars($_POST['properties_sold'] ?? '0') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Rating (0-5)</label>
                        <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1" value="<?= htmlspecialchars($_POST['rating'] ?? '4.5') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Bio / Description</label>
                        <textarea name="bio" class="form-control" rows="4" placeholder="Write a short bio about the agent..."><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card mb-3">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-camera"></i> Agent Photo</h5>
            </div>
            <div class="admin-card-body">
                <div class="image-upload-box" id="photoBox" style="border:2px dashed #729CA2;border-radius:8px;padding:2rem;text-align:center;background:#ECF3F4;cursor:pointer;position:relative;">
                    <input type="file" name="photo" accept="image/*" onchange="previewPhoto(this)" style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;">
                    <div id="photoPlaceholder">
                        <div class="image-upload-icon" style="color:#729CA2;font-size:2rem;"><i class="bi bi-person-circle"></i></div>
                        <div class="image-upload-text" style="color:#465461;font-size:0.875rem;">Click to upload photo<br><small>JPG, PNG — Max 3MB</small></div>
                    </div>
                    <img id="photoPreview" src="" style="display:none;width:120px;height:120px;border-radius:50%;object-fit:cover;margin:0 auto;border:3px solid #C4DCDF;">
                </div>
            </div>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn-admin-primary" style="padding:0.875rem;"><i class="bi bi-plus-circle"></i> Add Agent</button>
            <a href="agents.php" class="btn-admin-outline" style="padding:0.875rem;text-align:center;">Cancel</a>
        </div>
    </div>
</div>
</form>
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('photoPlaceholder').style.display = 'none';
            const img = document.getElementById('photoPreview');
            img.src = e.target.result;
            img.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
