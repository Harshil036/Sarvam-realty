<?php
/**
 * Admin - Manage Users
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'users';
$page_title   = 'Manage Users';

// Toggle status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $uid = (int)$_GET['toggle'];
    $conn->prepare("UPDATE users SET status = IF(status='active','suspended','active') WHERE id=?")->execute([$uid]);
    header('Location: users.php?toggled=1'); exit;
}

// Delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    $conn->prepare("DELETE FROM wishlist WHERE user_id=?")->execute([$uid]);
    $conn->prepare("DELETE FROM inquiries WHERE user_id=?")->execute([$uid]);
    $conn->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
    header('Location: users.php?deleted=1'); exit;
}

$search   = trim($_GET['search'] ?? '');
$filter   = $_GET['status'] ?? '';
$per_page = 20;
$cur_page = max(1,(int)($_GET['page'] ?? 1));
$offset   = ($cur_page-1)*$per_page;

$where = ['1=1'];
$params=[];
if ($search) { 
    $where[]='(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)'; 
    $like="%$search%"; 
    $params[]=$like;
    $params[]=$like;
    $params[]=$like;
}
if ($filter) { 
    $where[]='u.status=?'; 
    $params[]=$filter; 
}
$wsql = implode(' AND ',$where);

$cs = $conn->prepare("SELECT COUNT(*) as c FROM users u WHERE $wsql");
$cs->execute($params);
$total = (int)$cs->fetchColumn();
$total_pages = ceil($total/$per_page);

$sql = "SELECT u.*,
    (SELECT COUNT(*) FROM wishlist w WHERE w.user_id=u.id) as wishlist_count,
    (SELECT COUNT(*) FROM inquiries i WHERE i.user_id=u.id) as inq_count
    FROM users u WHERE $wsql ORDER BY u.created_at DESC LIMIT $per_page OFFSET $offset";
$st = $conn->prepare($sql);
$st->execute($params);
$users = $st->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i> Dashboard</a>
    <span class="separator">/</span><span>Users</span>
</div>

<?php if (isset($_GET['deleted'])): ?><div class="admin-alert danger"><i class="bi bi-trash"></i> User deleted.</div><?php endif; ?>
<?php if (isset($_GET['toggled'])): ?><div class="admin-alert success"><i class="bi bi-check-circle"></i> Status updated.</div><?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 style="font-size:1.3rem;margin-bottom:4px;color:#2D343D;">All Users</h2>
        <p style="font-size:0.85rem;color:#465461;margin:0;"><?= number_format($total) ?> registered users</p>
    </div>
</div>

<div class="admin-card mb-4">
    <div class="admin-card-body" style="padding:1.25rem;">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" <?= $filter=='active' ? 'selected' : '' ?>>Active</option>
                    <option value="suspended" <?= $filter=='suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-admin-primary" style="flex:1;"><i class="bi bi-funnel"></i> Filter</button>
                    <a href="users.php" class="btn-admin-outline"><i class="bi bi-x"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th><th>Name</th><th>Email</th><th>Phone</th>
                    <th>Wishlist</th><th>Inquiries</th><th>Status</th><th>Joined</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="9" class="text-center py-4" style="color:#465461;">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td>
                                <div style="font-weight:600;color:#2D343D;"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div>
                                <?php if ($u['address']): ?>
                                    <small style="color:#729CA2;"><?= htmlspecialchars(substr($u['address'],0,30)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.85rem;"><?= htmlspecialchars($u['email']) ?></td>
                            <td style="font-size:0.85rem;"><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                            <td><span class="badge-sarvam"><?= $u['wishlist_count'] ?></span></td>
                            <td><span class="badge-sarvam"><?= $u['inq_count'] ?></span></td>
                            <td>
                                <span class="status-badge <?= $u['status']=='active' ? 'active' : 'suspended' ?>">
                                    <?= ucfirst($u['status']) ?>
                                </span>
                            </td>
                            <td style="font-size:0.8rem;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="user-edit.php?id=<?= $u['id'] ?>" class="btn-admin-outline btn-admin-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <a href="users.php?toggle=<?= $u['id'] ?>" class="btn-admin-outline btn-admin-sm"
                                       title="<?= $u['status']=='active' ? 'Suspend' : 'Activate' ?>"
                                       onclick="return confirm('Change user status?')">
                                        <i class="bi <?= $u['status']=='active' ? 'bi-person-x' : 'bi-person-check' ?>"></i>
                                    </a>
                                    <a href="users.php?delete=<?= $u['id'] ?>" class="btn-admin-danger btn-admin-sm" title="Delete"
                                       onclick="return confirm('Delete this user? All their data will be removed.')">
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
    <?php if ($total_pages > 1): ?>
        <div class="admin-pagination p-3">
            <?php for($i=1;$i<=$total_pages;$i++): ?>
                <a href="?page=<?=$i?>&search=<?=urlencode($search)?>&status=<?=$filter?>" class="<?=$i==$cur_page?'active':''?>"><?=$i?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
