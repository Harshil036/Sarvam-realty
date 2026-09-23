<?php
/**
 * Admin - Manage Inquiries
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'inquiries';
$page_title   = 'Manage Inquiries';

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did=(int)$_GET['delete'];
    $conn->prepare("DELETE FROM inquiries WHERE id=?")->execute([$did]);
    header('Location: inquiries.php?deleted=1'); exit;
}

// Update status via GET
if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $sid=(int)$_GET['id'];
    $newstatus = in_array($_GET['status'],['pending','responded','closed']) ? $_GET['status'] : 'pending';
    $st=$conn->prepare("UPDATE inquiries SET status=? WHERE id=?");
    $st->execute([$newstatus,$sid]);
    header('Location: inquiries.php?updated=1'); exit;
}

// Filters
$filter_status = $_GET['status_filter'] ?? '';
$search        = trim($_GET['search'] ?? '');
$per_page      = 20;
$cur_page      = max(1,(int)($_GET['page'] ?? 1));
$offset        = ($cur_page-1)*$per_page;

$where=['1=1']; $params=[];
if ($filter_status) { $where[]='i.status=?'; $params[]=$filter_status; }
if ($search) { $where[]='(i.name LIKE ? OR i.email LIKE ? OR p.title LIKE ?)'; $like="%$search%"; $params[]=$like;$params[]=$like;$params[]=$like; }
$wsql=implode(' AND ',$where);

$cs=$conn->prepare("SELECT COUNT(*) as c FROM inquiries i LEFT JOIN properties p ON i.property_id=p.id WHERE $wsql");
$cs->execute($params);
$total=(int)$cs->fetchColumn();
$total_pages=ceil($total/$per_page);

$sql="SELECT i.*, p.title as property_title, p.id as prop_id
      FROM inquiries i LEFT JOIN properties p ON i.property_id=p.id
      WHERE $wsql ORDER BY i.created_at DESC LIMIT $per_page OFFSET $offset";
$st=$conn->prepare($sql);
$st->execute($params);
$inquiries=$st->fetchAll(PDO::FETCH_ASSOC);

// Pending count per status for stats
$stats=['pending'=>0,'responded'=>0,'closed'=>0];
$sr=$conn->query("SELECT status, COUNT(*) as c FROM inquiries GROUP BY status");
if ($sr) {
    while ($r=$sr->fetch(PDO::FETCH_ASSOC)) {
        $stats[$r['status']]=(int)$r['c'];
    }
}

// View single inquiry
$view_inq = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $vid=(int)$_GET['view'];
    $vr=$conn->prepare("SELECT i.*,p.title as property_title FROM inquiries i LEFT JOIN properties p ON i.property_id=p.id WHERE i.id=?");
    $vr->execute([$vid]);
    $view_inq=$vr->fetch(PDO::FETCH_ASSOC) ?: null;
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i></a>
    <span class="separator">/</span><span>Inquiries</span>
</div>

<?php if (isset($_GET['deleted'])): ?><div class="admin-alert danger"><i class="bi bi-trash"></i> Inquiry deleted.</div><?php endif; ?>
<?php if (isset($_GET['updated'])): ?><div class="admin-alert success"><i class="bi bi-check-circle"></i> Status updated.</div><?php endif; ?>

<!-- Status Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="admin-stat-card">
            <div class="stat-icon-box orange"><i class="bi bi-clock"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $stats['pending'] ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-stat-card">
            <div class="stat-icon-box green"><i class="bi bi-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $stats['responded'] ?></div>
                <div class="stat-label">Responded</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="admin-stat-card">
            <div class="stat-icon-box teal"><i class="bi bi-x-circle"></i></div>
            <div class="stat-info">
                <div class="stat-number"><?= $stats['closed'] ?></div>
                <div class="stat-label">Closed</div>
            </div>
        </div>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="mb-3 d-flex gap-2" style="overflow-x:auto;padding-bottom:5px;">
    <a href="?status_filter=&search=<?=urlencode($search)?>" 
       style="padding:0.4rem 1rem; <?= !$filter_status ? 'background:#FF893B;color:white;border:none;border-radius:6px;' : 'background:transparent;color:#729CA2;border:1px solid #C4DCDF;border-radius:6px;text-decoration:none;' ?>">
       All Status
    </a>
    <a href="?status_filter=pending&search=<?=urlencode($search)?>" 
       style="padding:0.4rem 1rem; <?= $filter_status=='pending' ? 'background:#FF893B;color:white;border:none;border-radius:6px;' : 'background:transparent;color:#729CA2;border:1px solid #C4DCDF;border-radius:6px;text-decoration:none;' ?>">
       Pending
    </a>
    <a href="?status_filter=responded&search=<?=urlencode($search)?>" 
       style="padding:0.4rem 1rem; <?= $filter_status=='responded' ? 'background:#FF893B;color:white;border:none;border-radius:6px;' : 'background:transparent;color:#729CA2;border:1px solid #C4DCDF;border-radius:6px;text-decoration:none;' ?>">
       Responded
    </a>
    <a href="?status_filter=closed&search=<?=urlencode($search)?>" 
       style="padding:0.4rem 1rem; <?= $filter_status=='closed' ? 'background:#FF893B;color:white;border:none;border-radius:6px;' : 'background:transparent;color:#729CA2;border:1px solid #C4DCDF;border-radius:6px;text-decoration:none;' ?>">
       Closed
    </a>
</div>

<!-- Filters -->
<div class="admin-card mb-4">
    <div class="admin-card-body" style="padding:1.25rem;">
        <form method="GET" class="row g-3 align-items-end admin-form">
            <input type="hidden" name="status_filter" value="<?= htmlspecialchars($filter_status) ?>">
            <div class="col-md-8">
                <div class="search-box position-relative">
                    <i class="bi bi-search position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:#729CA2;"></i>
                    <input type="text" name="search" class="form-control" style="padding-left:2.5rem;" placeholder="Search by name, email, or property..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-admin-primary" style="flex:1;"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="inquiries.php" class="btn-admin-outline"><i class="bi bi-x"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="bi bi-envelope"></i> All Inquiries</h5>
        <span class="badge" style="background:#FF893B;color:white;"><?= $total ?> total</span>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr><th>#</th><th>Name</th><th>Contact</th><th>Property</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($inquiries)): ?>
                    <tr><td colspan="8" class="text-center py-4" style="color:#465461;">No inquiries found.</td></tr>
                <?php else: ?>
                    <?php foreach ($inquiries as $inq): ?>
                        <tr>
                            <td><?= $inq['id'] ?></td>
                            <td>
                                <div style="font-weight:600;color:#2D343D;"><?= htmlspecialchars($inq['name']) ?></div>
                            </td>
                            <td style="font-size:0.8rem;color:#465461;">
                                <?= htmlspecialchars($inq['email']) ?><br>
                                <?= htmlspecialchars($inq['phone'] ?? '') ?>
                            </td>
                            <td style="font-size:0.8rem;">
                                <?php if ($inq['property_title']): ?>
                                    <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $inq['prop_id'] ?>" target="_blank"
                                       style="color:#729CA2;text-decoration:none;font-weight:600;">
                                        <?= htmlspecialchars(substr($inq['property_title'],0,30)) ?>...
                                    </a>
                                <?php else: ?>
                                    <span style="color:#465461;">General Contact</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.8rem;max-width:200px;color:#465461;" title="<?= htmlspecialchars($inq['message']) ?>">
                                <?= htmlspecialchars(substr($inq['message'],0,60)) ?>...
                            </td>
                            <td>
                                <form method="GET" class="admin-form m-0 p-0" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $inq['id'] ?>">
                                    <select name="status" onchange="this.form.submit()"
                                            class="form-select form-select-sm"
                                            style="font-size:0.75rem;padding:3px 8px;border-radius:6px;cursor:pointer;width:auto;display:inline-block;">
                                        <option value="pending" <?= $inq['status']=='pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="responded" <?= $inq['status']=='responded' ? 'selected' : '' ?>>Responded</option>
                                        <option value="closed" <?= $inq['status']=='closed' ? 'selected' : '' ?>>Closed</option>
                                    </select>
                                </form>
                            </td>
                            <td style="font-size:0.8rem;white-space:nowrap;color:#465461;"><?= date('d M Y',strtotime($inq['created_at'])) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="inquiries.php?view=<?= $inq['id'] ?><?= $search?"&search=".urlencode($search):'' ?><?= $filter_status?"&status_filter=$filter_status":'' ?>"
                                       class="btn-admin-outline btn-admin-sm" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="inquiries.php?delete=<?= $inq['id'] ?>" class="btn-admin-danger btn-admin-sm" title="Delete"
                                       onclick="return confirm('Delete this inquiry?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_pages>1): ?>
        <div class="admin-pagination p-3">
            <?php for($i=1;$i<=$total_pages;$i++): ?>
                <a href="?page=<?=$i?>&search=<?=urlencode($search)?>&status_filter=<?=$filter_status?>" class="<?=$i==$cur_page?'active':''?>"><?=$i?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Inquiry Detail Modal -->
<?php if ($view_inq): ?>
<div id="viewModal" style="display:flex;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:2rem;width:90%;max-width:580px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="font-family:'Poppins',sans-serif;margin:0;">Inquiry #<?= $view_inq['id'] ?></h5>
            <a href="inquiries.php" style="font-size:1.5rem;color:var(--text-secondary);">&times;</a>
        </div>
        <table style="width:100%;font-size:0.875rem;border-collapse:collapse;">
            <tr><td style="color:#465461;padding:8px 0;width:35%;">Name</td><td style="font-weight:600;color:#2D343D;"><?= htmlspecialchars($view_inq['name']) ?></td></tr>
            <tr><td style="color:#465461;padding:8px 0;">Email</td><td style="color:#2D343D;"><?= htmlspecialchars($view_inq['email']) ?></td></tr>
            <tr><td style="color:#465461;padding:8px 0;">Phone</td><td style="color:#2D343D;"><?= htmlspecialchars($view_inq['phone'] ?? '—') ?></td></tr>
            <tr><td style="color:#465461;padding:8px 0;">Property</td>
                <td style="color:#2D343D;"><?= $view_inq['property_title'] ? htmlspecialchars($view_inq['property_title']) : 'General Contact' ?></td></tr>
            <tr><td style="color:#465461;padding:8px 0;">Status</td>
                <td><span class="status-badge <?= $view_inq['status']=='responded'?'available':($view_inq['status']=='closed'?'sold':'pending') ?>"><?= ucfirst($view_inq['status']) ?></span></td></tr>
            <tr><td style="color:#465461;padding:8px 0;">Date</td><td style="color:#2D343D;"><?= date('d M Y, h:i A',strtotime($view_inq['created_at'])) ?></td></tr>
        </table>
        <div style="margin-top:1rem;">
            <div style="font-size:0.8rem;font-weight:700;color:#465461;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Message</div>
            <div class="inquiry-message-box" style="background:#ECF3F4;border:1px solid #C4DCDF;border-radius:8px;padding:1rem;color:#2D343D;"><?= nl2br(htmlspecialchars($view_inq['message'])) ?></div>
        </div>
        <div class="d-flex gap-2 mt-3">
            <a href="inquiries.php?id=<?= $view_inq['id'] ?>&status=responded" class="btn-admin-primary btn-admin-sm"><i class="bi bi-check-circle"></i> Mark Responded</a>
            <a href="inquiries.php?id=<?= $view_inq['id'] ?>&status=closed" class="btn-admin-outline btn-admin-sm"><i class="bi bi-x-circle"></i> Close</a>
            <a href="mailto:<?= htmlspecialchars($view_inq['email']) ?>" class="btn-admin-outline btn-admin-sm"><i class="bi bi-envelope"></i> Reply</a>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function updateStatus(id, status) {
    window.location.href = 'inquiries.php?id=' + id + '&status=' + status;
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
