<?php
/**
 * Admin - Manage Agents
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'agents';
$page_title   = 'Manage Agents';

// Delete agent
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $aid = (int)$_GET['delete'];
    $stmt_a = $conn->prepare("SELECT photo FROM agents WHERE id = ?");
    $stmt_a->execute([$aid]);
    $a = $stmt_a->fetch();
    if ($a && !empty($a['photo'])) {
        $f = UPLOAD_PATH . 'agents/' . $a['photo'];
        if (file_exists($f)) unlink($f);
    }
    $conn->prepare("DELETE FROM agents WHERE id = ?")->execute([$aid]);
    header('Location: agents.php?deleted=1'); exit;
}
// Toggle status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $aid = (int)$_GET['toggle'];
    $conn->prepare("UPDATE agents SET status = IF(status='active','inactive','active') WHERE id = ?")->execute([$aid]);
    header('Location: agents.php?toggled=1'); exit;
}

$res = $conn->query("SELECT a.*, (SELECT COUNT(*) FROM properties p WHERE p.agent_id=a.id) as prop_count FROM agents a ORDER BY a.created_at DESC");
$agents = $res ? $res->fetchAll() : [];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i> Dashboard</a>
    <span class="separator">/</span><span>Agents</span>
</div>

<?php if (isset($_GET['deleted'])): ?><div class="admin-alert danger"><i class="bi bi-trash"></i> Agent deleted.</div><?php endif; ?>
<?php if (isset($_GET['added'])): ?><div class="admin-alert success"><i class="bi bi-check-circle"></i> Agent added.</div><?php endif; ?>
<?php if (isset($_GET['updated'])): ?><div class="admin-alert success"><i class="bi bi-check-circle"></i> Agent updated.</div><?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="font-size:1.3rem;margin-bottom:4px;color:#2D343D;">Agents</h2>
        <p style="font-size:0.85rem;color:#465461;margin:0;"><?= count($agents) ?> agents total</p>
    </div>
    <a href="agent-add.php" class="btn-admin-primary"><i class="bi bi-plus-circle"></i> Add New Agent</a>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="bi bi-people"></i> All Agents</h5>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th><th>Photo</th><th>Name</th><th>Email</th><th>Phone</th>
                    <th>Exp.</th><th>Rating</th><th>Properties</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($agents)): ?>
                    <tr><td colspan="10" class="text-center py-4" style="color:var(--text-secondary);">No agents found.</td></tr>
                <?php else: ?>
                    <?php foreach ($agents as $a): ?>
                        <tr>
                            <td><?= $a['id'] ?></td>
                            <td>
                                <img src="<?= $a['photo'] ? SITE_URL.'/assets/uploads/agents/'.$a['photo'] : 'https://placehold.co/50x50/2563EB/fff?text='.urlencode(strtoupper(substr($a['name'],0,1))) ?>"
                                     style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #C4DCDF;">
                            </td>
                            <td>
                                <div style="font-weight:600;color:#2D343D;"><?= htmlspecialchars($a['name']) ?></div>
                                <small style="color:#465461;"><?= $a['properties_sold'] ?> sold</small>
                            </td>
                            <td style="font-size:0.85rem;color:#465461;"><?= htmlspecialchars($a['email']) ?></td>
                            <td style="font-size:0.85rem;color:#465461;"><?= htmlspecialchars($a['phone'] ?? '—') ?></td>
                            <td style="font-size:0.85rem;color:#465461;"><?= $a['experience_years'] ?> yrs</td>
                            <td>
                                <div style="display:flex;align-items:center;gap:4px;">
                                    <span style="color:#FF893B;"><i class="bi bi-star-fill"></i></span>
                                    <strong style="font-size:0.85rem;color:#2D343D;"><?= number_format($a['rating'],1) ?></strong>
                                </div>
                            </td>
                            <td><span class="badge" style="background:#ECF3F4;color:#729CA2;border:1px solid #C4DCDF;"><?= $a['prop_count'] ?></span></td>
                            <td>
                                <span class="status-badge <?= $a['status']=='active' ? 'active' : 'pending' ?>">
                                    <?= ucfirst($a['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="agent-edit.php?id=<?= $a['id'] ?>" class="btn-admin-outline btn-admin-sm" title="Edit"><i class="bi bi-pencil"></i></a>
                                    <a href="agents.php?toggle=<?= $a['id'] ?>" class="btn-admin-outline btn-admin-sm" title="Toggle Status"><i class="bi <?= $a['status']=='active' ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i></a>
                                    <a href="agents.php?delete=<?= $a['id'] ?>" class="btn-admin-danger btn-admin-sm" title="Delete"
                                       onclick="return confirm('Delete this agent?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
