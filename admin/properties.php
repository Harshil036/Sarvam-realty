<?php
/**
 * Admin - Manage Properties
 * List, search, filter, paginate all properties
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'properties';
$page_title   = 'Manage Properties';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    // Delete property images from disk
    $stmt_img = $conn->prepare("SELECT image_path FROM property_images WHERE property_id = ?");
    $stmt_img->execute([$del_id]);
    foreach ($stmt_img->fetchAll() as $img) {
        $f = UPLOAD_PATH . 'properties/' . $img['image_path'];
        if (file_exists($f)) unlink($f);
    }
    // Delete main image
    $stmt_main = $conn->prepare("SELECT main_image FROM properties WHERE id = ?");
    $stmt_main->execute([$del_id]);
    $main = $stmt_main->fetch();
    if ($main && !empty($main['main_image'])) {
        $f = UPLOAD_PATH . 'properties/' . $main['main_image'];
        if (file_exists($f)) unlink($f);
    }
    $conn->prepare("DELETE FROM property_images WHERE property_id = ?")->execute([$del_id]);
    $conn->prepare("DELETE FROM wishlist WHERE property_id = ?")->execute([$del_id]);
    $conn->prepare("DELETE FROM inquiries WHERE property_id = ?")->execute([$del_id]);
    $conn->prepare("DELETE FROM properties WHERE id = ?")->execute([$del_id]);
    header('Location: properties.php?deleted=1');
    exit;
}

// Handle featured/premium toggle
if (isset($_GET['toggle_featured']) && is_numeric($_GET['toggle_featured'])) {
    $tid = (int)$_GET['toggle_featured'];
    $conn->prepare("UPDATE properties SET is_featured = 1 - is_featured WHERE id = ?")->execute([$tid]);
    header('Location: properties.php?toggled=1');
    exit;
}
if (isset($_GET['toggle_premium']) && is_numeric($_GET['toggle_premium'])) {
    $tid = (int)$_GET['toggle_premium'];
    $conn->prepare("UPDATE properties SET is_premium = 1 - is_premium WHERE id = ?")->execute([$tid]);
    header('Location: properties.php?toggled=1');
    exit;
}

// Search / filter params
$search       = trim($_GET['search'] ?? '');
$filter_type  = (int)($_GET['type'] ?? 0);
$filter_list  = $_GET['listing'] ?? '';
$filter_status = $_GET['status'] ?? '';
$per_page     = 20;
$current_pg   = max(1, (int)($_GET['page'] ?? 1));
$offset       = ($current_pg - 1) * $per_page;

// Build WHERE
$where = ['1=1'];
$params = [];

if ($search) {
    $where[] = '(p.title LIKE ? OR p.address LIKE ?)';
    $like = "%$search%";
    $params[] = $like; $params[] = $like;
}
if ($filter_type) {
    $where[] = 'p.property_type_id = ?';
    $params[] = $filter_type;
}
if ($filter_list) {
    $where[] = 'p.listing_type = ?';
    $params[] = $filter_list;
}
if ($filter_status) {
    $where[] = 'p.status = ?';
    $params[] = $filter_status;
}
$where_sql = implode(' AND ', $where);

// Count
$count_sql = "SELECT COUNT(*) FROM properties p WHERE $where_sql";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total = (int)$count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Fetch
$sql = "SELECT p.*, pt.type_name, l.city, a.name as agent_name
        FROM properties p
        JOIN property_types pt ON p.property_type_id = pt.id
        JOIN locations l ON p.location_id = l.id
        LEFT JOIN agents a ON p.agent_id = a.id
        WHERE $where_sql ORDER BY p.created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

$property_types_list = getPropertyTypes($conn);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Breadcrumb -->
<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i> Dashboard</a>
    <span class="separator">/</span>
    <span>Properties</span>
</div>

<!-- Alerts -->
<?php if (isset($_GET['deleted'])): ?>
    <div class="admin-alert danger"><i class="bi bi-trash"></i> Property deleted successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['added'])): ?>
    <div class="admin-alert success"><i class="bi bi-check-circle"></i> Property added successfully.</div>
<?php endif; ?>
<?php if (isset($_GET['updated'])): ?>
    <div class="admin-alert success"><i class="bi bi-check-circle"></i> Property updated successfully.</div>
<?php endif; ?>

<!-- Header Row -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 style="font-size:1.3rem;margin-bottom:4px;color:#2D343D;">All Properties</h2>
        <p style="font-size:0.85rem;color:#465461;margin:0;">
            <?= number_format($total) ?> properties total
        </p>
    </div>
    <a href="property-add.php" class="btn-admin-primary">
        <i class="bi bi-plus-circle"></i> Add New Property
    </a>
</div>

<!-- Filter Bar -->
<div class="admin-card mb-4">
    <div class="admin-card-body" style="padding:1.25rem;">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="Search properties..."
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <?php foreach ($property_types_list as $pt): ?>
                        <option value="<?= $pt['id'] ?>" <?= $filter_type==$pt['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($pt['type_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="listing" class="form-select">
                    <option value="">All Listings</option>
                    <option value="buy" <?= $filter_list=='buy' ? 'selected' : '' ?>>For Sale</option>
                    <option value="rent" <?= $filter_list=='rent' ? 'selected' : '' ?>>For Rent</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="available" <?= $filter_status=='available' ? 'selected' : '' ?>>Available</option>
                    <option value="sold" <?= $filter_status=='sold' ? 'selected' : '' ?>>Sold</option>
                    <option value="rented" <?= $filter_status=='rented' ? 'selected' : '' ?>>Rented</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn-admin-primary flex-1" style="flex:1;">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="properties.php" class="btn-admin-outline">
                        <i class="bi bi-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Properties Table -->
<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table" id="propertiesTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll" style="accent-color:#FF893B;"></th>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Property</th>
                    <th>Type</th>
                    <th>City</th>
                    <th>Price</th>
                    <th>Listing</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Premium</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($properties)): ?>
                    <tr><td colspan="13" class="text-center py-5" style="color:#465461;">
                        <i class="bi bi-building fs-1 d-block mb-3"></i>
                        No properties found.
                        <a href="property-add.php" style="color:#729CA2;">Add one?</a>
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($properties as $p): ?>
                        <tr>
                            <td><input type="checkbox" class="row-check" style="accent-color:#FF893B;"></td>
                            <td><small style="color:#729CA2;"><?= generatePropertyId($p['id']) ?></small></td>
                            <td>
                                <img src="<?= $p['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$p['main_image'] : 'https://placehold.co/80x56/2D343D/fff?text=No+Img' ?>"
                                     class="table-img" alt="" style="width:80px;height:56px;object-fit:cover;border-radius:4px;">
                            </td>
                            <td style="max-width:200px;">
                                <div style="font-weight:600;font-size:0.85rem;color:#2D343D;"><?= htmlspecialchars(substr($p['title'],0,40)) ?>...</div>
                                <small style="color:#465461;"><?= $p['bedrooms'] ?> bed &bull; <?= $p['bathrooms'] ?> bath</small>
                            </td>
                            <td><span class="badge-sarvam"><?= htmlspecialchars($p['type_name']) ?></span></td>
                            <td style="font-size:0.85rem;"><?= htmlspecialchars($p['city']) ?></td>
                            <td style="font-weight:700;color:#2D343D;white-space:nowrap;"><?= formatPrice($p['price']) ?></td>
                            <td>
                                <span class="badge-sarvam">
                                    <?= $p['listing_type']=='buy' ? 'For Sale' : 'For Rent' ?>
                                </span>
                            </td>
                            <td>
                                <?php $sc = ['available'=>'available','sold'=>'sold','rented'=>'rented']; ?>
                                <span class="status-badge <?= $sc[$p['status']] ?? 'available' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="properties.php?toggle_featured=<?= $p['id'] ?>&<?= http_build_query($_GET) ?>"
                                   class="btn-admin-outline btn-admin-sm" title="Toggle Featured" style="border:none;">
                                    <i class="bi <?= $p['is_featured'] ? 'bi-star-fill text-warning' : 'bi-star' ?>"></i>
                                </a>
                            </td>
                            <td>
                                <a href="properties.php?toggle_premium=<?= $p['id'] ?>&<?= http_build_query($_GET) ?>"
                                   class="btn-admin-outline btn-admin-sm" title="Toggle Premium" style="border:none;">
                                    <i class="bi <?= $p['is_premium'] ? 'bi-gem' : 'bi-gem' ?>"
                                       style="color:<?= $p['is_premium'] ? '#FF893B' : 'inherit' ?>;"></i>
                                </a>
                            </td>
                            <td style="font-size:0.8rem;white-space:nowrap;">
                                <?= date('d M Y', strtotime($p['created_at'])) ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="property-edit.php?id=<?= $p['id'] ?>" class="btn-admin-outline btn-admin-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $p['id'] ?>" target="_blank" class="btn-admin-outline btn-admin-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="properties.php?delete=<?= $p['id'] ?>" class="btn-admin-danger btn-admin-sm" title="Delete"
                                       onclick="return confirm('Delete this property? This cannot be undone.')">
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
            <?php if ($current_pg > 1): ?>
                <a href="?page=<?= $current_pg-1 ?>&search=<?= urlencode($search) ?>&type=<?= $filter_type ?>&listing=<?= $filter_list ?>&status=<?= $filter_status ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            <?php endif; ?>
            <?php for ($i=1; $i<=$total_pages; $i++): ?>
                <?php if (abs($i-$current_pg)<3 || $i==1 || $i==$total_pages): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= $filter_type ?>&listing=<?= $filter_list ?>&status=<?= $filter_status ?>"
                       class="<?= $i==$current_pg ? 'active' : '' ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($current_pg < $total_pages): ?>
                <a href="?page=<?= $current_pg+1 ?>&search=<?= urlencode($search) ?>&type=<?= $filter_type ?>&listing=<?= $filter_list ?>&status=<?= $filter_status ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Select all checkboxes
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
