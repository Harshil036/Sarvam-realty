<?php
/**
 * Admin - Property Types CRUD
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'property-types';
$page_title   = 'Property Types';
$success=''; $errors=[];

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $cnt_stmt = $conn->prepare("SELECT COUNT(*) as c FROM properties WHERE property_type_id=?");
    $cnt_stmt->execute([$did]);
    $cnt = (int)$cnt_stmt->fetchColumn();
    if ($cnt > 0) { $errors[]="Cannot delete: $cnt properties are assigned to this type."; }
    else { 
        $conn->prepare("DELETE FROM property_types WHERE id=?")->execute([$did]); 
        $success='Property type deleted.'; 
    }
}

// Toggle status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $conn->prepare("UPDATE property_types SET status=IF(status='active','inactive','active') WHERE id=?")->execute([$tid]);
    $success = 'Status updated.';
}

// Add new type
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='add') {
    $type_name = trim($_POST['type_name'] ?? '');
    $icon      = trim($_POST['icon'] ?? 'bi-building');
    $desc      = trim($_POST['description'] ?? '');
    if (!$type_name) $errors[]='Type name is required.';
    if (empty($errors)) {
        try {
            $st = $conn->prepare("INSERT INTO property_types (type_name,icon,description,status) VALUES (?,?,?,'active')");
            $st->execute([$type_name, $icon, $desc]);
            $success='Property type added.';
        } catch (PDOException $e) {
            $errors[]='DB error: ' . $e->getMessage();
        }
    }
}

// Edit type
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='edit') {
    $eid   = (int)($_POST['edit_id'] ?? 0);
    $ename = trim($_POST['edit_name'] ?? '');
    $eicon = trim($_POST['edit_icon'] ?? '');
    $edesc = trim($_POST['edit_desc'] ?? '');
    if ($eid && $ename) {
        try {
            $st = $conn->prepare("UPDATE property_types SET type_name=?,icon=?,description=? WHERE id=?");
            $st->execute([$ename, $eicon, $edesc, $eid]);
            $success='Type updated.';
        } catch (PDOException $e) {
            $errors[]='DB error: ' . $e->getMessage();
        }
    }
}

// Fetch types
$res = $conn->query("SELECT pt.*, (SELECT COUNT(*) FROM properties p WHERE p.property_type_id=pt.id) as prop_count FROM property_types pt ORDER BY pt.type_name");
$types = $res ? $res->fetchAll(PDO::FETCH_ASSOC) : [];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i></a>
    <span class="separator">/</span><span>Property Types</span>
</div>

<?php if ($success): ?><div class="admin-alert success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="admin-alert danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= implode(', ',$errors) ?></div><?php endif; ?>

<div class="row g-4">
    <!-- Add Type Form -->
    <div class="col-md-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-plus-circle"></i> Add New Type</h5>
            </div>
            <div class="admin-card-body">
                <form method="POST" class="admin-form">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Type Name *</label>
                        <input type="text" name="type_name" class="form-control" required placeholder="e.g. Apartment">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Bootstrap Icon</label>
                        <div class="input-group">
                            <span class="input-group-text" style="background:#ECF3F4;border-color:#C4DCDF;color:#729CA2;"><i class="bi bi-building"></i></span>
                            <input type="text" name="icon" class="form-control" placeholder="bi-building" value="bi-building">
                        </div>
                        <small style="color:#465461;">Use Bootstrap Icons class name</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Short description..."></textarea>
                    </div>
                    <button type="submit" class="btn-admin-primary w-100"><i class="bi bi-plus"></i> Add Type</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Types List -->
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-tag"></i> All Property Types</h5>
                <span class="badge" style="background:#FF893B;color:white;"><?= count($types) ?> types</span>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>Icon</th><th>Type Name</th><th>Properties</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($types as $t): ?>
                            <tr>
                                <td><?= $t['id'] ?></td>
                                <td><i class="bi <?= htmlspecialchars($t['icon'] ?? 'bi-building') ?>" style="font-size:1.3rem;color:#729CA2;"></i></td>
                                <td>
                                    <div style="font-weight:600;color:#2D343D;"><?= htmlspecialchars($t['type_name']) ?></div>
                                    <?php if ($t['description']): ?>
                                        <small style="color:#465461;"><?= htmlspecialchars(substr($t['description'],0,50)) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge" style="background:#ECF3F4;color:#729CA2;border:1px solid #C4DCDF;"><?= $t['prop_count'] ?></span></td>
                                <td>
                                    <span class="status-badge <?= $t['status']=='active' ? 'active' : 'pending' ?>">
                                        <?= ucfirst($t['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn-admin-outline btn-admin-sm" title="Edit"
                                                onclick="openEditModal(<?= $t['id'] ?>, '<?= addslashes($t['type_name']) ?>', '<?= addslashes($t['icon'] ?? '') ?>', '<?= addslashes($t['description'] ?? '') ?>')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="property-types.php?toggle=<?= $t['id'] ?>" class="btn-admin-outline btn-admin-sm" title="Toggle Status">
                                            <i class="bi <?= $t['status']=='active' ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                                        </a>
                                        <a href="property-types.php?delete=<?= $t['id'] ?>" class="btn-admin-danger btn-admin-sm" title="Delete"
                                           onclick="return confirm('Delete this property type?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:2rem;width:90%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <h5 style="margin-bottom:1.25rem;font-family:'Poppins',sans-serif;color:#2D343D;">Edit Property Type</h5>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="edit_id" id="editId">
            <div class="mb-3">
                <label class="form-label" style="color:#2D343D;font-weight:600;">Type Name *</label>
                <input type="text" name="edit_name" id="editName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label" style="color:#2D343D;font-weight:600;">Bootstrap Icon</label>
                <input type="text" name="edit_icon" id="editIcon" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label" style="color:#2D343D;font-weight:600;">Description</label>
                <textarea name="edit_desc" id="editDesc" class="form-control" rows="2"></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn-admin-primary flex-1">Save</button>
                <button type="button" onclick="closeEditModal()" class="btn-admin-outline flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(id, name, icon, desc) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editIcon').value = icon;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
