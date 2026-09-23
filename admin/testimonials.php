<?php
/**
 * Admin - Manage Testimonials
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'testimonials';
$page_title   = 'Manage Testimonials';
$success=''; $errors=[];

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did=(int)$_GET['delete'];
    $conn->prepare("DELETE FROM testimonials WHERE id=?")->execute([$did]);
    $success='Testimonial deleted.';
}

// Toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $tid=(int)$_GET['toggle'];
    $conn->prepare("UPDATE testimonials SET status=IF(status='active','inactive','active') WHERE id=?")->execute([$tid]);
    $success='Status updated.';
}

// Add
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add') {
    $user_name    = trim($_POST['user_name'] ?? '');
    $rating       = min(5,max(1,(int)($_POST['rating'] ?? 5)));
    $review       = trim($_POST['review'] ?? '');
    $prop_type    = trim($_POST['property_type'] ?? '');

    if (!$user_name || !$review) { $errors[]='Name and review are required.'; }

    $photo='';
    if (!empty($_FILES['user_photo']['name'])) {
        $ud=UPLOAD_PATH.'testimonials/';
        if (!is_dir($ud)) mkdir($ud,0777,true);
        $ext=strtolower(pathinfo($_FILES['user_photo']['name'],PATHINFO_EXTENSION));
        if (in_array($ext,['jpg','jpeg','png','webp'])) {
            $photo=uniqid('testi_').'.'.$ext;
            if (!move_uploaded_file($_FILES['user_photo']['tmp_name'],$ud.$photo)) $photo='';
        }
    }

    if (empty($errors)) {
        try {
            $st=$conn->prepare("INSERT INTO testimonials (user_name,user_photo,rating,review,property_type,status,created_at) VALUES (?,?,?,?,?,'active',NOW())");
            $st->execute([$user_name,$photo,$rating,$review,$prop_type]);
            $success='Testimonial added.';
        } catch (PDOException $e) {
            $errors[]=$e->getMessage();
        }
    }
}

$res=$conn->query("SELECT * FROM testimonials ORDER BY created_at DESC");
$testimonials = $res ? $res->fetchAll(PDO::FETCH_ASSOC) : [];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i></a>
    <span class="separator">/</span><span>Testimonials</span>
</div>

<?php if ($success): ?><div class="admin-alert success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="admin-alert danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= implode('<br>',$errors) ?></div><?php endif; ?>

<div class="row g-4">
    <!-- Add Form -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-plus-circle"></i> Add Testimonial</h5>
            </div>
            <div class="admin-card-body">
                <form method="POST" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Customer Name *</label>
                        <input type="text" name="user_name" class="form-control" required placeholder="Priya Sharma">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Rating (1-5) *</label>
                        <div class="d-flex gap-2 align-items-center">
                            <?php for($r=1;$r<=5;$r++): ?>
                                <label style="cursor:pointer;font-size:1.5rem;color:#FF893B;">
                                    <input type="radio" name="rating" value="<?=$r?>" style="display:none;" <?=$r==5?'checked':''?>>
                                    <i class="bi bi-star-fill"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Property Type</label>
                        <input type="text" name="property_type" class="form-control" placeholder="Apartment / Villa...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Review *</label>
                        <textarea name="review" class="form-control" rows="4" required placeholder="Write their review..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Photo (optional)</label>
                        <input type="file" name="user_photo" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn-admin-primary w-100"><i class="bi bi-plus"></i> Add Testimonial</button>
                </form>
            </div>
        </div>
    </div>

    <!-- List -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-chat-quote"></i> All Testimonials</h5>
                <span class="badge" style="background:#FF893B;color:white;"><?= count($testimonials) ?> total</span>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Photo</th><th>Customer</th><th>Rating</th><th>Review</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($testimonials)): ?>
                            <tr><td colspan="8" class="text-center py-4" style="color:#465461;">No testimonials yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($testimonials as $t): ?>
                                <tr>
                                    <td><?= $t['id'] ?></td>
                                    <td>
                                        <img src="<?= $t['user_photo'] ? SITE_URL.'/assets/uploads/testimonials/'.$t['user_photo'] : 'https://placehold.co/44x44/2563EB/fff?text='.urlencode(strtoupper(substr($t['user_name'],0,1))) ?>"
                                             style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid #C4DCDF;">
                                    </td>
                                    <td>
                                        <div style="font-weight:600;color:#2D343D;"><?= htmlspecialchars($t['user_name']) ?></div>
                                        <small style="color:#465461;"><?= htmlspecialchars($t['property_type'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span style="color:#FF893B;">
                                            <?php for($i=0;$i<$t['rating'];$i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                                        </span>
                                        <small style="color:#465461;">(<?= $t['rating'] ?>/5)</small>
                                    </td>
                                    <td style="font-size:0.8rem;max-width:200px;color:#465461;">
                                        <em>"<?= htmlspecialchars(substr($t['review'],0,80)) ?>..."</em>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $t['status']=='active' ? 'active' : 'pending' ?>">
                                            <?= ucfirst($t['status']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size:0.8rem;color:#465461;"><?= date('d M Y',strtotime($t['created_at'])) ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="testimonials.php?toggle=<?= $t['id'] ?>" class="btn-admin-outline btn-admin-sm" title="Toggle Status">
                                                <i class="bi <?= $t['status']=='active' ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                                            </a>
                                            <a href="testimonials.php?delete=<?= $t['id'] ?>" class="btn-admin-danger btn-admin-sm" title="Delete"
                                               onclick="return confirm('Delete this testimonial?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
