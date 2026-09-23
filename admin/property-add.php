<?php
/**
 * Admin - Add New Property
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'property-add';
$page_title   = 'Add New Property';

$errors  = [];
$success = '';

// Fetch form data
$property_types_list = getPropertyTypes($conn);
$locations_list      = getLocations($conn);
$agents_list         = getAgents($conn);

$amenities_list = [
    'Swimming Pool','Gym & Fitness','24/7 Security','Power Backup',
    'Garden/Landscaping','Covered Parking','Elevator/Lift','Club House',
    'Children Play Area','Sports Facility','Intercom','WiFi/Internet',
    'CCTV Surveillance','Fire Safety','Rainwater Harvesting','Solar Power'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    $title          = trim($_POST['title'] ?? '');
    $description    = trim($_POST['description'] ?? '');
    $property_type  = (int)($_POST['property_type_id'] ?? 0);
    $location_id    = (int)($_POST['location_id'] ?? 0);
    $agent_id       = (int)($_POST['agent_id'] ?? 0);
    $price          = (float)($_POST['price'] ?? 0);
    $area_sqft      = (float)($_POST['area_sqft'] ?? 0);
    $bedrooms       = (int)($_POST['bedrooms'] ?? 0);
    $bathrooms      = (int)($_POST['bathrooms'] ?? 0);
    $parking        = (int)($_POST['parking'] ?? 0);
    $furnished      = $_POST['furnished_status'] ?? 'unfurnished';
    $listing_type   = $_POST['listing_type'] ?? 'buy';
    $status         = $_POST['status'] ?? 'available';
    $is_featured    = isset($_POST['is_featured']) ? 1 : 0;
    $is_premium     = isset($_POST['is_premium']) ? 1 : 0;
    $address        = trim($_POST['address'] ?? '');
    $amenities      = isset($_POST['amenities']) ? json_encode($_POST['amenities']) : '[]';

    if (!$title)         $errors[] = 'Property title is required.';
    if (!$property_type) $errors[] = 'Property type is required.';
    if (!$location_id)   $errors[] = 'Location is required.';
    if (!$agent_id)      $errors[] = 'Agent is required.';
    if ($price <= 0)     $errors[] = 'Valid price is required.';
    if ($area_sqft <= 0) $errors[] = 'Valid area is required.';

    // Upload main image
    $main_image = '';
    if (!empty($_FILES['main_image']['name'])) {
        // Create directory
        $upload_dir = UPLOAD_PATH . 'properties/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext  = strtolower(pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Main image must be JPG, PNG, or WEBP.';
        } elseif ($_FILES['main_image']['size'] > 5000000) {
            $errors[] = 'Main image must be under 5MB.';
        } else {
            $main_image = uniqid('prop_') . '.' . $ext;
            if (!move_uploaded_file($_FILES['main_image']['tmp_name'], $upload_dir . $main_image)) {
                $errors[] = 'Failed to upload main image.';
                $main_image = '';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO properties
            (title, description, property_type_id, location_id, agent_id, price, area_sqft,
             bedrooms, bathrooms, parking, furnished_status, listing_type, status,
             is_featured, is_premium, amenities, address, main_image, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())");

        $inserted = $stmt->execute([
            $title, $description, $property_type, $location_id, $agent_id,
            $price, $area_sqft, $bedrooms, $bathrooms, $parking,
            $furnished, $listing_type, $status,
            $is_featured, $is_premium, $amenities, $address, $main_image
        ]);

        if ($inserted) {
            $new_id = (int)$conn->lastInsertId();

            // Upload additional images
            if (!empty($_FILES['gallery_images']['name'][0])) {
                $upload_dir = UPLOAD_PATH . 'properties/';
                $ist = $conn->prepare("INSERT INTO property_images (property_id, image_path, is_primary) VALUES (?,?,?)");
                foreach ($_FILES['gallery_images']['tmp_name'] as $idx => $tmp) {
                    if ($_FILES['gallery_images']['error'][$idx] !== UPLOAD_ERR_OK) continue;
                    $ext = strtolower(pathinfo($_FILES['gallery_images']['name'][$idx], PATHINFO_EXTENSION));
                    if (!in_array($ext, ['jpg','jpeg','png','webp'])) continue;
                    $fname = uniqid('gal_') . '.' . $ext;
                    if (move_uploaded_file($tmp, $upload_dir . $fname)) {
                        $is_primary = ($idx === 0 && !$main_image) ? 1 : 0;
                        $ist->execute([$new_id, $fname, $is_primary]);
                    }
                }
            }

            header('Location: properties.php?added=1');
            exit;
        } else {
            $errors[] = 'Database error: ' . $conn->error;
            $stmt = null;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<div class="admin-breadcrumb">
    <a href="index.php"><i class="bi bi-house-fill"></i> Dashboard</a>
    <span class="separator">/</span>
    <a href="properties.php">Properties</a>
    <span class="separator">/</span>
    <span>Add New</span>
</div>

<?php if (!empty($errors)): ?>
    <div class="admin-alert danger">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div><strong>Please fix the following:</strong><ul class="mt-1 mb-0">
            <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul></div>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="font-size:1.3rem;margin:0;color:#2D343D;">Add New Property</h2>
    <a href="properties.php" class="btn-admin-outline btn-admin-sm"><i class="bi bi-arrow-left"></i> Back to List</a>
</div>

<form method="POST" enctype="multipart/form-data" id="addPropertyForm" class="admin-form">
<div class="row g-4">
    <!-- Left Column -->
    <div class="col-xl-8">

        <!-- Basic Info -->
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-info-circle"></i> Basic Information</div>
            <div class="mb-3">
                <label class="form-label">Property Title *</label>
                <input type="text" name="title" class="form-control" required
                       placeholder="e.g. Luxurious 3BHK Apartment in Bandra West"
                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Property Type *</label>
                    <select name="property_type_id" class="form-select" required>
                        <option value="">Select Type</option>
                        <?php foreach ($property_types_list as $pt): ?>
                            <option value="<?= $pt['id'] ?>" <?= ($_POST['property_type_id'] ?? '')==$pt['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pt['type_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Location / City *</label>
                    <select name="location_id" class="form-select" required>
                        <option value="">Select Location</option>
                        <?php foreach ($locations_list as $loc): ?>
                            <option value="<?= $loc['id'] ?>" <?= ($_POST['location_id'] ?? '')==$loc['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($loc['city']) ?>, <?= htmlspecialchars($loc['state']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Full Address</label>
                <textarea name="address" class="form-control" rows="2"
                          placeholder="Street, Area, City, State, PIN"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>
            <div class="mt-3">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-control" rows="5" required
                          placeholder="Write a detailed description of the property..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Property Details -->
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-layout-text-window"></i> Property Details</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Price (₹) *</label>
                    <input type="number" name="price" class="form-control" required min="0" step="1000"
                           placeholder="5000000" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Area (sq.ft) *</label>
                    <input type="number" name="area_sqft" class="form-control" required min="0"
                           placeholder="1200" value="<?= htmlspecialchars($_POST['area_sqft'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Listing Type *</label>
                    <select name="listing_type" class="form-select" required>
                        <option value="buy" <?= ($_POST['listing_type'] ?? 'buy')=='buy' ? 'selected' : '' ?>>For Sale</option>
                        <option value="rent" <?= ($_POST['listing_type'] ?? '')=='rent' ? 'selected' : '' ?>>For Rent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bedrooms</label>
                    <input type="number" name="bedrooms" class="form-control" min="0" max="20"
                           placeholder="3" value="<?= htmlspecialchars($_POST['bedrooms'] ?? '0') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Bathrooms</label>
                    <input type="number" name="bathrooms" class="form-control" min="0" max="20"
                           placeholder="2" value="<?= htmlspecialchars($_POST['bathrooms'] ?? '0') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Parking Spots</label>
                    <input type="number" name="parking" class="form-control" min="0" max="10"
                           placeholder="1" value="<?= htmlspecialchars($_POST['parking'] ?? '0') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Furnished Status</label>
                    <select name="furnished_status" class="form-select">
                        <option value="furnished" <?= ($_POST['furnished_status'] ?? '')=='furnished' ? 'selected' : '' ?>>Furnished</option>
                        <option value="semi-furnished" <?= ($_POST['furnished_status'] ?? '')=='semi-furnished' ? 'selected' : '' ?>>Semi-Furnished</option>
                        <option value="unfurnished" <?= ($_POST['furnished_status'] ?? 'unfurnished')=='unfurnished' ? 'selected' : '' ?>>Unfurnished</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Amenities -->
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-star"></i> Amenities</div>
            <div class="amenity-grid">
                <?php foreach ($amenities_list as $am): ?>
                    <label class="amenity-checkbox">
                        <input type="checkbox" name="amenities[]" value="<?= htmlspecialchars($am) ?>"
                               <?= (isset($_POST['amenities']) && in_array($am, $_POST['amenities'])) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($am) ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="mt-2">
                <button type="button" onclick="selectAllAmenities()" class="btn-admin-outline btn-admin-sm">Select All</button>
                <button type="button" onclick="clearAllAmenities()" class="btn-admin-outline btn-admin-sm">Clear All</button>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-xl-4">

        <!-- Status & Agent -->
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-toggles"></i> Status & Agent</div>
            <div class="mb-3">
                <label class="form-label">Property Status</label>
                <select name="status" class="form-select">
                    <option value="available" <?= ($_POST['status'] ?? 'available')=='available' ? 'selected' : '' ?>>Available</option>
                    <option value="sold" <?= ($_POST['status'] ?? '')=='sold' ? 'selected' : '' ?>>Sold</option>
                    <option value="rented" <?= ($_POST['status'] ?? '')=='rented' ? 'selected' : '' ?>>Rented</option>
                    <option value="inactive" <?= ($_POST['status'] ?? '')=='inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Assign Agent *</label>
                <select name="agent_id" class="form-select" required>
                    <option value="">Select Agent</option>
                    <?php foreach ($agents_list as $ag): ?>
                        <option value="<?= $ag['id'] ?>" <?= ($_POST['agent_id'] ?? '')==$ag['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ag['name']) ?> (<?= $ag['experience_years'] ?>yr)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="d-flex flex-column gap-2">
                <label class="d-flex align-items-center gap-2" style="cursor:pointer;font-weight:500;font-size:0.875rem;">
                    <input type="checkbox" name="is_featured" value="1" style="accent-color:#FF893B;"
                           <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                    Mark as Featured Property
                </label>
                <label class="d-flex align-items-center gap-2" style="cursor:pointer;font-weight:500;font-size:0.875rem;">
                    <input type="checkbox" name="is_premium" value="1" style="accent-color:#FF893B;"
                           <?= isset($_POST['is_premium']) ? 'checked' : '' ?>>
                    Mark as Premium Property
                </label>
            </div>
        </div>

        <!-- Main Image -->
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-image"></i> Main Image</div>
            <div class="image-upload-box" id="mainImgBox">
                <input type="file" name="main_image" id="mainImgInput" accept="image/*"
                       onchange="previewMainImage(this)">
                <div id="mainImgPlaceholder">
                    <div class="image-upload-icon"><i class="bi bi-cloud-upload"></i></div>
                    <div class="image-upload-text">
                        <strong>Click to upload</strong> or drag & drop<br>
                        <small style="color:var(--text-secondary);">JPG, PNG, WEBP — Max 5MB</small>
                    </div>
                </div>
                <img id="mainImgPreview" src="" alt="" style="display:none;width:100%;border-radius:8px;max-height:200px;object-fit:cover;">
            </div>
        </div>

        <!-- Gallery Images -->
        <div class="form-section">
            <div class="form-section-title"><i class="bi bi-images"></i> Gallery Images</div>
            <div class="image-upload-box">
                <input type="file" name="gallery_images[]" id="galleryInput" accept="image/*" multiple
                       onchange="previewGallery(this)">
                <div class="image-upload-icon"><i class="bi bi-images"></i></div>
                <div class="image-upload-text">
                    <strong>Upload multiple images</strong><br>
                    <small style="color:var(--text-secondary);">Select multiple files</small>
                </div>
            </div>
            <div class="image-preview-grid" id="galleryPreview"></div>
        </div>

        <!-- Submit -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn-admin-primary" style="padding:0.875rem;">
                <i class="bi bi-plus-circle"></i> Add Property
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
            document.getElementById('mainImgPreview').src = e.target.result;
            document.getElementById('mainImgPreview').style.display = 'block';
            document.getElementById('mainImgPlaceholder').style.display = 'none';
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
function selectAllAmenities() {
    document.querySelectorAll('.amenity-checkbox input').forEach(cb => cb.checked = true);
}
function clearAllAmenities() {
    document.querySelectorAll('.amenity-checkbox input').forEach(cb => cb.checked = false);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
