<?php
/**
 * Admin - Edit Property
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'properties';
$page_title   = 'Edit Property';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: properties.php'); exit; }

// Fetch existing property
$prop = getPropertyById($conn, $id);
if (!$prop) { header('Location: properties.php'); exit; }

// Fetch gallery images
$stmt_g = $conn->prepare("SELECT * FROM property_images WHERE property_id = ? ORDER BY is_primary DESC");
$stmt_g->execute([$id]);
$gallery = $stmt_g->fetchAll(PDO::FETCH_ASSOC);

$property_types_list = getPropertyTypes($conn);
$locations_list      = getLocations($conn);
$agents_list         = getAgents($conn);

$amenities_list = [
    'Swimming Pool','Gym & Fitness','24/7 Security','Power Backup',
    'Garden/Landscaping','Covered Parking','Elevator/Lift','Club House',
    'Children Play Area','Sports Facility','Intercom','WiFi/Internet',
    'CCTV Surveillance','Fire Safety','Rainwater Harvesting','Solar Power'
];

$existing_amenities = json_decode($prop['amenities'] ?? '[]', true) ?: [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($_POST['title'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $property_type = (int)($_POST['property_type_id'] ?? 0);
    $location_id   = (int)($_POST['location_id'] ?? 0);
    $agent_id      = (int)($_POST['agent_id'] ?? 0);
    $price         = (float)($_POST['price'] ?? 0);
    $area_sqft     = (float)($_POST['area_sqft'] ?? 0);
    $bedrooms      = (int)($_POST['bedrooms'] ?? 0);
    $bathrooms     = (int)($_POST['bathrooms'] ?? 0);
    $parking       = (int)($_POST['parking'] ?? 0);
    $furnished     = $_POST['furnished_status'] ?? 'unfurnished';
    $listing_type  = $_POST['listing_type'] ?? 'buy';
    $status        = $_POST['status'] ?? 'available';
    $is_featured   = isset($_POST['is_featured']) ? 1 : 0;
    $is_premium    = isset($_POST['is_premium']) ? 1 : 0;
    $address       = trim($_POST['address'] ?? '');
    $amenities     = json_encode($_POST['amenities'] ?? []);

    if (!$title) $errors[] = 'Title is required.';

    // Handle new main image upload
    $main_image = $prop['main_image'];
    if (!empty($_FILES['main_image']['name'])) {
        $upload_dir = UPLOAD_PATH . 'properties/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp']) && $_FILES['main_image']['size'] <= 5000000) {
            $new_name = uniqid('prop_') . '.' . $ext;
            if (move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_dir . $new_name)) {
                // Delete old main image
                if ($main_image && file_exists($upload_dir . $main_image)) unlink($upload_dir . $main_image);
                $main_image = $new_name;
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE properties SET
            title=?, description=?, property_type_id=?, location_id=?, agent_id=?,
            price=?, area_sqft=?, bedrooms=?, bathrooms=?, parking=?,
            furnished_status=?, listing_type=?, status=?,
            is_featured=?, is_premium=?, amenities=?, address=?, main_image=?,
            updated_at=NOW()
            WHERE id=?");
        
        $updated = $stmt->execute([
            $title, $description, $property_type, $location_id, $agent_id,
            $price, $area_sqft, $bedrooms, $bathrooms, $parking,
            $furnished, $listing_type, $status,
            $is_featured, $is_premium, $amenities, $address, $main_image, $id
        ]);

        if ($updated) {
            // Delete marked gallery images
            if (!empty($_POST['delete_images'])) {
                $stmt_del_img = $conn->prepare("SELECT image_path FROM property_images WHERE id = ? AND property_id = ?");
                $stmt_do_del = $conn->prepare("DELETE FROM property_images WHERE id = ? AND property_id = ?");
                foreach ($_POST['delete_images'] as $img_id) {
                    $img_id = (int)$img_id;
                    $stmt_del_img->execute([$img_id, $id]);
                    $row = $stmt_del_img->fetch();
                    if ($row) {
                        $f = UPLOAD_PATH . 'properties/' . $row['image_path'];
                        if (file_exists($f)) unlink($f);
                    }
                    $stmt_do_del->execute([$img_id, $id]);
                }
            }
            // Upload new gallery images
            if (!empty($_FILES['gallery_images']['name'][0])) {
                $upload_dir = UPLOAD_PATH . 'properties/';
                $ist = $conn->prepare("INSERT INTO property_images (property_id, image_path, is_primary) VALUES (?,?,0)");
                foreach ($_FILES['gallery_images']['tmp_name'] as $idx => $tmp) {
                    if ($_FILES['gallery_images']['error'][$idx] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($_FILES['gallery_images']['name'][$idx], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg','jpeg','png','webp'])) continue;
                    $fname = uniqid('gal_') . '.' . $ext;
                    if (move_uploaded_file($tmp, $upload_dir . $fname)) {
                        $ist->execute([$id, $fname]);
                    }
                }
            }
            header('Location: properties.php?updated=1');
            exit;
        } else {
            $errors[] = 'Failed to update property. Please check inputs.';
        }
    }
    // Refresh prop data for display
    $prop['title'] = $title; $prop['description'] = $description;
    $existing_amenities = json_decode($amenities, true) ?: [];
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i> Dashboard</a>
    <span class="separator">/</span>
    <a href="properties.php">Properties</a>
    <span class="separator">/</span>
    <span>Edit: <?= htmlspecialchars(substr($prop['title'],0,40)) ?>...</span>
</div>

<?php if (!empty($errors)): ?>
    <div class="admin-alert danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div><strong>Errors:</strong><ul class="mt-1 mb-0">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul></div>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="font-size:1.3rem;margin-bottom:4px;color:#2D343D;">Edit Property</h2>
        <small style="color:#465461;"><?= generatePropertyId($id) ?></small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $id ?>" target="_blank" class="btn-admin-outline btn-admin-sm">
            <i class="bi bi-eye"></i> View Live
        </a>
        <a href="properties.php" class="btn-admin-outline btn-admin-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<form method="POST" enctype="multipart/form-data" class="admin-form">
<div class="row g-4">
    <div class="col-xl-8">
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-info-circle"></i> Basic Information</div>
            <div class="mb-3">
                <label class="form-label">Property Title *</label>
                <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($prop['title']) ?>">
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Property Type *</label>
                    <select name="property_type_id" class="form-select" required>
                        <?php foreach ($property_types_list as $pt): ?>
                            <option value="<?= $pt['id'] ?>" <?= $prop['property_type_id']==$pt['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pt['type_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Location *</label>
                    <select name="location_id" class="form-select" required>
                        <?php foreach ($locations_list as $loc): ?>
                            <option value="<?= $loc['id'] ?>" <?= $prop['location_id']==$loc['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loc['city']) ?>, <?= htmlspecialchars($loc['state']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Full Address</label>
                <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($prop['address']) ?></textarea>
            </div>
            <div class="mt-3">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($prop['description']) ?></textarea>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-layout-text-window"></i> Property Details</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Price (₹) *</label>
                    <input type="number" name="price" class="form-control" required value="<?= $prop['price'] ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Area (sq.ft) *</label>
                    <input type="number" name="area_sqft" class="form-control" required value="<?= $prop['area_sqft'] ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Listing Type</label>
                    <select name="listing_type" class="form-select">
                        <option value="buy" <?= $prop['listing_type']=='buy' ? 'selected' : '' ?>>For Sale</option>
                        <option value="rent" <?= $prop['listing_type']=='rent' ? 'selected' : '' ?>>For Rent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bedrooms</label>
                    <input type="number" name="bedrooms" class="form-control" min="0" value="<?= $prop['bedrooms'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bathrooms</label>
                    <input type="number" name="bathrooms" class="form-control" min="0" value="<?= $prop['bathrooms'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Parking</label>
                    <input type="number" name="parking" class="form-control" min="0" value="<?= $prop['parking'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Furnished</label>
                    <select name="furnished_status" class="form-select">
                        <option value="furnished" <?= $prop['furnished_status']=='furnished' ? 'selected' : '' ?>>Furnished</option>
                        <option value="semi-furnished" <?= $prop['furnished_status']=='semi-furnished' ? 'selected' : '' ?>>Semi-Furnished</option>
                        <option value="unfurnished" <?= $prop['furnished_status']=='unfurnished' ? 'selected' : '' ?>>Unfurnished</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-star"></i> Amenities</div>
            <div class="amenity-grid">
                <?php foreach ($amenities_list as $am): ?>
                    <label class="amenity-checkbox">
                        <input type="checkbox" name="amenities[]" value="<?= htmlspecialchars($am) ?>"
                               <?= in_array($am, $existing_amenities) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($am) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Existing Gallery -->
        <?php if (!empty($gallery)): ?>
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-images"></i> Current Gallery Images</div>
            <div class="image-preview-grid">
                <?php foreach ($gallery as $img): ?>
                    <div class="image-preview-item">
                        <img src="<?= SITE_URL ?>/assets/uploads/properties/<?= htmlspecialchars($img['image_path']) ?>" alt="">
                        <label style="position:absolute;bottom:4px;left:4px;background:rgba(239,68,68,0.9);color:#fff;
                                      font-size:0.65rem;padding:2px 6px;border-radius:4px;cursor:pointer;">
                            <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"
                                   style="margin-right:3px;">Delete
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <small style="color:var(--text-secondary);">Check "Delete" on images you want to remove.</small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column -->
    <div class="col-xl-4">
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-toggles"></i> Status & Agent</div>
            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="available" <?= $prop['status']=='available' ? 'selected' : '' ?>>Available</option>
                    <option value="sold" <?= $prop['status']=='sold' ? 'selected' : '' ?>>Sold</option>
                    <option value="rented" <?= $prop['status']=='rented' ? 'selected' : '' ?>>Rented</option>
                    <option value="inactive" <?= $prop['status']=='inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Agent</label>
                <select name="agent_id" class="form-select">
                    <?php foreach ($agents_list as $ag): ?>
                        <option value="<?= $ag['id'] ?>" <?= $prop['agent_id']==$ag['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ag['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex flex-column gap-2">
                <label class="d-flex align-items-center gap-2" style="cursor:pointer;font-weight:500;font-size:0.875rem;">
                    <input type="checkbox" name="is_featured" value="1" style="accent-color:#FF893B;"
                           <?= $prop['is_featured'] ? 'checked' : '' ?>>
                    Featured Property
                </label>
                <label class="d-flex align-items-center gap-2" style="cursor:pointer;font-weight:500;font-size:0.875rem;">
                    <input type="checkbox" name="is_premium" value="1" style="accent-color:#FF893B;"
                           <?= $prop['is_premium'] ? 'checked' : '' ?>>
                    Premium Property
                </label>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-image"></i> Replace Main Image</div>
            <?php if ($prop['main_image']): ?>
                <img src="<?= SITE_URL ?>/assets/uploads/properties/<?= htmlspecialchars($prop['main_image']) ?>"
                     style="width:100%;border-radius:10px;margin-bottom:10px;object-fit:cover;height:150px;">
            <?php endif; ?>
            <div class="image-upload-box">
                <input type="file" name="main_image" accept="image/*" onchange="previewMainImage(this)">
                <div class="image-upload-icon"><i class="bi bi-cloud-upload"></i></div>
                <div class="image-upload-text">Upload new image to replace</div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-images"></i> Add More Images</div>
            <div class="image-upload-box">
                <input type="file" name="gallery_images[]" accept="image/*" multiple
                       onchange="previewGallery(this)">
                <div class="image-upload-icon"><i class="bi bi-images"></i></div>
                <div class="image-upload-text">Upload additional gallery images</div>
            </div>
            <div class="image-preview-grid" id="galleryPreview"></div>
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn-admin-primary" style="padding:0.875rem;">
                <i class="bi bi-save"></i> Save Changes
            </button>
            <a href="properties.php" class="btn-admin-outline" style="text-align:center;">Cancel</a>
        </div>
    </div>
</div>
</form>

<script>
function previewMainImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = input.closest('.image-upload-box').querySelector('img');
            if (img) { img.src = e.target.result; }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function previewGallery(input) {
    const preview = document.getElementById('galleryPreview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            const div = document.createElement('div');
            div.className = 'image-preview-item';
            div.innerHTML = `<img src="${e.target.result}" alt="">`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
