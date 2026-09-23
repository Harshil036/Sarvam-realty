<?php
/**
 * Admin - Edit Agent
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'agents';
$page_title   = 'Edit Agent';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: agents.php'); exit; }

$st = $conn->prepare("SELECT * FROM agents WHERE id = ?");
$st->execute([$id]);
$agent = $st->fetch(PDO::FETCH_ASSOC);
if (!$agent) { header('Location: agents.php'); exit; }

$errors=[];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name   = trim($_POST['name'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $phone  = trim($_POST['phone'] ?? '');
    $bio    = trim($_POST['bio'] ?? '');
    $exp    = (int)($_POST['experience_years'] ?? 0);
    $sold   = (int)($_POST['properties_sold'] ?? 0);
    $rating = min(5.0,max(0,(float)($_POST['rating'] ?? 4.0)));
    $status = $_POST['status'] ?? 'active';

    if (!$name) $errors[]='Name is required.';

    $photo = $agent['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $upload_dir = UPLOAD_PATH . 'agents/';
        if (!is_dir($upload_dir)) mkdir($upload_dir,0777,true);
        $ext = strtolower(pathinfo($_FILES['photo']['name'],PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','webp']) && $_FILES['photo']['size']<=3000000) {
            $new = uniqid('agent_').'.'.$ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'],$upload_dir.$new)) {
                if ($photo && file_exists($upload_dir.$photo)) unlink($upload_dir.$photo);
                $photo = $new;
            }
        }
    }

    if (empty($errors)) {
        $st = $conn->prepare("UPDATE agents SET name=?,email=?,phone=?,bio=?,experience_years=?,properties_sold=?,rating=?,photo=?,status=? WHERE id=?");
        if ($st->execute([$name,$email,$phone,$bio,$exp,$sold,$rating,$photo,$status,$id])) {
            header('Location: agents.php?updated=1'); exit;
        } else {
            $errors[] = 'Failed to update agent.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i></a>
    <span class="separator">/</span><a href="agents.php">Agents</a>
    <span class="separator">/</span><span>Edit</span>
</div>

<?php if (!empty($errors)): ?>
    <div class="admin-alert danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= implode(', ',$errors) ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size:1.3rem;margin:0;color:#2D343D;">Edit Agent: <?= htmlspecialchars($agent['name']) ?></h2>
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
                        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($agent['name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Email *</label>
                        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($agent['email']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($agent['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Experience (Years)</label>
                        <input type="number" name="experience_years" class="form-control" min="0" value="<?= $agent['experience_years'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Properties Sold</label>
                        <input type="number" name="properties_sold" class="form-control" min="0" value="<?= $agent['properties_sold'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Rating (0-5)</label>
                        <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1" value="<?= $agent['rating'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= $agent['status']=='active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $agent['status']=='inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Bio</label>
                        <textarea name="bio" class="form-control" rows="4"><?= htmlspecialchars($agent['bio'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-card mb-3">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-camera"></i> Photo</h5>
            </div>
            <div class="admin-card-body text-center">
                <?php if ($agent['photo']): ?>
                    <img src="<?= SITE_URL ?>/assets/uploads/agents/<?= htmlspecialchars($agent['photo']) ?>"
                         style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:15px;display:inline-block;border:3px solid #C4DCDF;">
                <?php endif; ?>
                <div class="image-upload-box" style="border:2px dashed #729CA2;border-radius:8px;padding:1.5rem;text-align:center;background:#ECF3F4;cursor:pointer;position:relative;">
                    <input type="file" name="photo" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;">
                    <div class="image-upload-icon" style="color:#729CA2;font-size:1.5rem;"><i class="bi bi-camera"></i></div>
                    <div class="image-upload-text" style="color:#465461;font-size:0.875rem;">Upload new photo</div>
                </div>
            </div>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn-admin-primary" style="padding:0.875rem;"><i class="bi bi-save"></i> Save Changes</button>
            <a href="agents.php?delete=<?= $id ?>" class="btn-admin-danger" style="text-align:center;padding:0.875rem;"
               onclick="return confirm('Delete this agent?')"><i class="bi bi-trash"></i> Delete Agent</a>
        </div>
    </div>
</div>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
