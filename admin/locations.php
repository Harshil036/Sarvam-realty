<?php
/**
 * Admin - Locations CRUD
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'locations';
$page_title   = 'Manage Locations';
$success=''; $errors=[];

// Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $cnt_stmt = $conn->prepare("SELECT COUNT(*) as c FROM properties WHERE location_id=?");
    $cnt_stmt->execute([$did]);
    $cnt = (int)$cnt_stmt->fetchColumn();
    if ($cnt>0) { $errors[]="Cannot delete: $cnt properties use this location."; }
    else { 
        $conn->prepare("DELETE FROM locations WHERE id=?")->execute([$did]); 
        $success='Location deleted.'; 
    }
}

// Toggle
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $tid=(int)$_GET['toggle'];
    $conn->prepare("UPDATE locations SET status=IF(status='active','inactive','active') WHERE id=?")->execute([$tid]);
    $success='Status updated.';
}

// Add
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='add') {
    $city    = trim($_POST['city']??'');
    $state   = trim($_POST['state']??'');
    $country = trim($_POST['country']??'India');
    $pin     = trim($_POST['pincode']??'');
    if (!$city||!$state) { $errors[]='City and State are required.'; }
    if (empty($errors)) {
        try {
            $st=$conn->prepare("INSERT INTO locations (city,state,country,pincode,status) VALUES (?,?,?,?,'active')");
            $st->execute([$city,$state,$country,$pin]);
            $success='Location added.';
        } catch (PDOException $e) {
            $errors[]=$e->getMessage();
        }
    }
}

// Edit
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='edit') {
    $eid  =(int)($_POST['edit_id']??0);
    $ecity=trim($_POST['edit_city']??'');
    $estate=trim($_POST['edit_state']??'');
    $ecountry=trim($_POST['edit_country']??'India');
    $epin=trim($_POST['edit_pincode']??'');
    if ($eid&&$ecity) {
        try {
            $st=$conn->prepare("UPDATE locations SET city=?,state=?,country=?,pincode=? WHERE id=?");
            $st->execute([$ecity,$estate,$ecountry,$epin,$eid]);
            $success='Location updated.';
        } catch (PDOException $e) {
            $errors[]=$e->getMessage();
        }
    }
}

$res=$conn->query("SELECT l.*, (SELECT COUNT(*) FROM properties p WHERE p.location_id=l.id) as prop_count FROM locations l ORDER BY l.city");
$locations = $res ? $res->fetchAll(PDO::FETCH_ASSOC) : [];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i></a>
    <span class="separator">/</span><span>Locations</span>
</div>

<?php if ($success): ?><div class="admin-alert success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="admin-alert danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= implode('<br>',$errors) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-plus-circle"></i> Add New Location</h5>
            </div>
            <div class="admin-card-body">
                <form method="POST" class="admin-form">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">City *</label>
                        <input type="text" name="city" class="form-control" required placeholder="Mumbai">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">State *</label>
                        <input type="text" name="state" class="form-control" required placeholder="Maharashtra">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">Country</label>
                        <input type="text" name="country" class="form-control" value="India">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:#2D343D;font-weight:600;">PIN Code</label>
                        <input type="text" name="pincode" class="form-control" placeholder="400001">
                    </div>
                    <button type="submit" class="btn-admin-primary w-100"><i class="bi bi-plus"></i> Add Location</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="admin-card">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-geo-alt"></i> All Locations</h5>
                <span class="badge" style="background:#FF893B;color:white;"><?= count($locations) ?> locations</span>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr><th>#</th><th>City</th><th>State</th><th>Country</th><th>PIN</th><th>Properties</th><th>Status</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($locations as $l): ?>
                            <tr>
                                <td><?= $l['id'] ?></td>
                                <td style="font-weight:600;color:#2D343D;"><?= htmlspecialchars($l['city']) ?></td>
                                <td><?= htmlspecialchars($l['state']) ?></td>
                                <td><?= htmlspecialchars($l['country']) ?></td>
                                <td style="font-size:0.85rem;color:#465461;"><?= htmlspecialchars($l['pincode'] ?? '—') ?></td>
                                <td><span class="badge" style="background:#ECF3F4;color:#729CA2;border:1px solid #C4DCDF;"><?= $l['prop_count'] ?></span></td>
                                <td><span class="status-badge <?= $l['status']=='active' ? 'active' : 'pending' ?>"><?= ucfirst($l['status']) ?></span></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn-admin-outline btn-admin-sm" onclick="openLocEdit(<?= $l['id'] ?>,'<?= addslashes($l['city']) ?>','<?= addslashes($l['state']) ?>','<?= addslashes($l['country']) ?>','<?= addslashes($l['pincode'] ?? '') ?>')" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <a href="locations.php?toggle=<?= $l['id'] ?>" class="btn-admin-outline btn-admin-sm" title="Toggle Status"><i class="bi <?= $l['status']=='active'?'bi-toggle-on':'bi-toggle-off' ?>"></i></a>
                                        <a href="locations.php?delete=<?= $l['id'] ?>" class="btn-admin-danger btn-admin-sm" title="Delete"
                                           onclick="return confirm('Delete this location?')"><i class="bi bi-trash"></i></a>
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
<div id="locEditModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:2rem;width:90%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <h5 style="margin-bottom:1.25rem;font-family:'Poppins',sans-serif;color:#2D343D;">Edit Location</h5>
        <form method="POST" class="admin-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="edit_id" id="locEditId">
            <div class="mb-3"><label class="form-label" style="color:#2D343D;font-weight:600;">City</label><input type="text" name="edit_city" id="locEditCity" class="form-control" required></div>
            <div class="mb-3"><label class="form-label" style="color:#2D343D;font-weight:600;">State</label><input type="text" name="edit_state" id="locEditState" class="form-control" required></div>
            <div class="mb-3"><label class="form-label" style="color:#2D343D;font-weight:600;">Country</label><input type="text" name="edit_country" id="locEditCountry" class="form-control"></div>
            <div class="mb-3"><label class="form-label" style="color:#2D343D;font-weight:600;">PIN Code</label><input type="text" name="edit_pincode" id="locEditPin" class="form-control"></div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn-admin-primary flex-1">Save</button>
                <button type="button" onclick="document.getElementById('locEditModal').style.display='none'" class="btn-admin-outline flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
function openLocEdit(id,city,state,country,pin) {
    document.getElementById('locEditId').value=id;
    document.getElementById('locEditCity').value=city;
    document.getElementById('locEditState').value=state;
    document.getElementById('locEditCountry').value=country;
    document.getElementById('locEditPin').value=pin;
    document.getElementById('locEditModal').style.display='flex';
}
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
