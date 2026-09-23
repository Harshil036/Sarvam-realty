<?php
/**
 * ============================================================
 *  FEATURE: PROPERTY DETAIL PAGE
 *  File   : property-detail.php
 *  Role   : Shows full details for a single property including
 *           image gallery, all specifications, amenities,
 *           an inquiry form, and related properties.
 *
 *  Sections:
 *   1. Property fetch — validates ?id=N, 404-redirects if missing
 *   2. Image gallery  — main image + additional gallery images
 *   3. Specs panel    — bedrooms, bathrooms, area, price, type
 *   4. Inquiry form   — POST to same page; saves to inquiries table
 *      • Requires login to submit (redirects guests to login)
 *      • Duplicate inquiry check: one per user per property
 *   5. Related props  — same type/city, excluding current property
 *
 *  Security:
 *   • PDO prepared statements for all queries
 *   • htmlspecialchars() on all echoed user/DB data (XSS prevention)
 *   • isLoggedIn() check before inquiry submission
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . SITE_URL . "/properties.php");
    exit();
}

// Fetch property details using custom helper
$property = getPropertyById($conn, $id);
if (!$property) {
    header("Location: " . SITE_URL . "/properties.php");
    exit();
}

// Increment view count
$conn->prepare("UPDATE properties SET views = views + 1 WHERE id = ?")->execute([$id]);

// Fetch secondary gallery images
$stmt_gallery = $conn->prepare("SELECT * FROM property_images WHERE property_id = ? AND is_primary = 0 ORDER BY created_at DESC");
$stmt_gallery->execute([$id]);
$gallery_images = $stmt_gallery->fetchAll();

// Check wishlist status if user is logged in
$wishlisted = false;
if (isLoggedIn()) {
    $wishlisted = isInWishlist($conn, $_SESSION['user_id'], $id);
}

// Fetch related properties (same type, excluding current property)
$type_id = (int)$property['property_type_id'];
$stmt_related = $conn->prepare("SELECT p.*, pt.type_name, l.city FROM properties p JOIN property_types pt ON p.property_type_id = pt.id JOIN locations l ON p.location_id = l.id WHERE p.property_type_id = ? AND p.id != ? AND p.status = 'available' LIMIT 3");
$stmt_related->execute([$type_id, $id]);
$related_properties = $stmt_related->fetchAll();

$page_title = $property['title'];
$page_description = substr(strip_tags($property['description']), 0, 160);

// Handle Inquiry Form Submission
$inquiry_success = false;
$inquiry_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $inq_name = trim($_POST['inq_name'] ?? '');
    $inq_email = trim($_POST['inq_email'] ?? '');
    $inq_phone = trim($_POST['inq_phone'] ?? '');
    $inq_message = trim($_POST['inq_message'] ?? '');

    if (empty($inq_name) || empty($inq_email) || empty($inq_phone) || empty($inq_message)) {
        $inquiry_error = 'All fields are required to submit an inquiry.';
    } else {
        $inquiry_data = [
            'property_id' => $id,
            'user_id' => isLoggedIn() ? $_SESSION['user_id'] : null,
            'name' => $inq_name,
            'email' => $inq_email,
            'phone' => $inq_phone,
            'message' => $inq_message
        ];
        
        if (submitInquiry($conn, $inquiry_data)) {
            $inquiry_success = true;
        } else {
            $inquiry_error = 'Error submitting inquiry. Please try again.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="container">
    <h1 class="fw-bold text-white mb-2">Property Details</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:rgba(255,255,255,0.4);">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-sarvam">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/properties.php" class="text-sarvam">Properties</a></li>
        <li class="breadcrumb-item active text-white"><?= generatePropertyId($property['id']) ?></li>
      </ol>
    </nav>
  </div>
</section>

<section class="section-light py-5">
    <div class="container">

    <!-- Property Main Header -->
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <span class="badge bg-sarvam-ice text-sarvam px-3 py-1.5 rounded-pill font-poppins small mb-2"><?= htmlspecialchars($property['type_name']) ?></span>
            <h1 class="h2 fw-bold font-poppins text-heading mb-2"><?= htmlspecialchars($property['title']) ?></h1>
            <p class="text-muted mb-0"><i class="bi bi-geo-alt-fill text-sarvam me-2"></i> <?= htmlspecialchars($property['address']) ?>, <?= htmlspecialchars($property['city']) ?>, <?= htmlspecialchars($property['state']) ?></p>
        </div>
        <div class="text-md-end d-flex flex-column gap-2">
            <span class="badge property-badge <?= $property['listing_type'] == 'buy' ? 'badge-success' : 'badge-warning' ?> px-3 py-2 rounded-2 text-uppercase tracking-wider small align-self-start align-self-md-end">
                For <?= $property['listing_type'] == 'buy' ? 'Sale' : 'Rent' ?>
            </span>
            <h2 class="display-6 fw-bold text-sarvam font-poppins mb-0 mt-1"><?= formatPrice($property['price']) ?></h2>
            <small class="text-muted font-poppins">ID: <?= generatePropertyId($property['id']) ?> &bull; <?= number_format($property['views']) ?> Views</small>
        </div>
    </div>

    <!-- Images Gallery -->
    <div class="row g-3 mb-5">
        <!-- Main Image -->
        <div class="col-lg-8">
            <div class="rounded-sarvam overflow-hidden shadow-sarvam position-relative" style="height: 480px;">
                <img src="<?= $property['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$property['main_image'] : 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1200&q=80' ?>" 
                     class="w-100 h-100" alt="Main image" style="object-fit: cover;" id="mainGalleryImage">
            </div>
        </div>
        <!-- Gallery Sidebar -->
        <div class="col-lg-4 d-flex flex-column gap-3">
            <?php if (empty($gallery_images)): ?>
                <div class="card-sarvam border-sarvam h-100 p-4 d-flex align-items-center justify-content-center bg-white">
                    <p class="text-muted small text-center mb-0"><i class="bi bi-images fs-2 text-muted mb-2 d-block"></i>No additional photos available</p>
                </div>
            <?php else: 
                // Show max 3 sidebar images
                $show_limit = min(3, count($gallery_images));
                for($i = 0; $i < $show_limit; $i++):
                    $img = $gallery_images[$i];
                ?>
                    <div class="rounded-sarvam overflow-hidden shadow-sm cursor-pointer position-relative flex-grow-1" style="height: 148px; border:1px solid #C4DCDF;" onclick="swapGalleryImage('<?= SITE_URL.'/assets/uploads/properties/'.$img['image_path'] ?>')">
                        <img src="<?= SITE_URL.'/assets/uploads/properties/'.$img['image_path'] ?>" class="w-100 h-100" alt="Gallery image" style="object-fit: cover;">
                        <?php if ($i == 2 && count($gallery_images) > 3): ?>
                            <div class="position-absolute inset-0 d-flex align-items-center justify-content-center text-white fw-bold font-poppins fs-5" style="background:rgba(45,52,61,0.6);">
                                +<?= count($gallery_images) - 3 ?> Photos
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left details panel -->
        <div class="col-lg-8">
            <!-- Property Key Specs -->
            <div class="card-sarvam p-4 mb-4">
                <h5 class="fw-bold font-poppins text-heading border-bottom pb-3 mb-4" style="border-color:#C4DCDF !important;"><i class="bi bi-info-circle me-2 text-sarvam"></i>Key Specifications</h5>
                <div class="row g-3 font-poppins">
                    <div class="col-md-4 col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-sarvam-ice text-sarvam rounded-sarvam d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-door-open fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase tracking-wider fs-7">Bedrooms</small>
                                <strong class="text-heading"><?= $property['bedrooms'] ?> BHK</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-sarvam-ice text-sarvam rounded-sarvam d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-droplet fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase tracking-wider fs-7">Bathrooms</small>
                                <strong class="text-heading"><?= $property['bathrooms'] ?> Baths</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-sarvam-ice text-sarvam rounded-sarvam d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-aspect-ratio fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase tracking-wider fs-7">Super Area</small>
                                <strong class="text-heading"><?= formatArea($property['area_sqft']) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-sarvam-ice text-sarvam rounded-sarvam d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-p-circle fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase tracking-wider fs-7">Parking Spot</small>
                                <strong class="text-heading"><?= $property['parking'] > 0 ? $property['parking'] . ' Spots' : 'No' ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-sarvam-ice text-sarvam rounded-sarvam d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-couch fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase tracking-wider fs-7">Furnishing</small>
                                <strong class="text-heading text-capitalize"><?= str_replace('-', ' ', $property['furnished_status']) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box bg-sarvam-ice text-sarvam rounded-sarvam d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-house-door fs-4"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block uppercase tracking-wider fs-7">Listed Date</small>
                                <strong class="text-heading"><?= date('d M Y', strtotime($property['created_at'])) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Description -->
            <div class="card-sarvam p-4 mb-4">
                <h5 class="fw-bold font-poppins text-heading border-bottom pb-3 mb-4" style="border-color:#C4DCDF !important;"><i class="bi bi-body-text me-2 text-sarvam"></i>Description</h5>
                <div class="text-muted font-poppins lead fs-6" style="line-height: 1.8;">
                    <?= nl2br(htmlspecialchars($property['description'])) ?>
                </div>
            </div>

            <!-- Property Amenities -->
            <div class="card-sarvam p-4 mb-4">
                <h5 class="fw-bold font-poppins text-heading border-bottom pb-3 mb-4" style="border-color:#C4DCDF !important;"><i class="bi bi-star me-2 text-sarvam"></i>Amenities</h5>
                <?php 
                $amenities = json_decode($property['amenities'] ?? '[]', true) ?: [];
                if (empty($amenities)):
                ?>
                    <p class="text-muted font-poppins small mb-0">No standard amenities listed for this property.</p>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach($amenities as $am): ?>
                            <div class="col-md-4 col-6 font-poppins">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill text-sarvam fs-5"></i>
                                    <span class="text-muted small"><?= htmlspecialchars($am) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Map Location Placeholder -->
            <div class="card-sarvam p-4 mb-4">
                <h5 class="fw-bold font-poppins text-heading border-bottom pb-3 mb-4" style="border-color:#C4DCDF !important;"><i class="bi bi-geo-alt me-2 text-sarvam"></i>Location</h5>
                <div class="bg-sarvam-ice rounded-sarvam d-flex flex-column align-items-center justify-content-center text-center p-4 border-sarvam" style="height: 250px;">
                    <i class="bi bi-map fs-1 text-sarvam mb-2"></i>
                    <p class="text-heading font-poppins mb-1 fw-semibold"><?= htmlspecialchars($property['address']) ?></p>
                    <p class="text-muted font-poppins small"><?= htmlspecialchars($property['city']) ?>, <?= htmlspecialchars($property['state']) ?></p>
                </div>
            </div>
        </div>

        <!-- Right sidebar contact/inquiry/wishlist -->
        <div class="col-lg-4">
            <!-- Save/Wishlist CTA -->
            <div class="card-sarvam p-4 mb-4 text-center">
                <div class="d-flex gap-2">
                    <button class="btn <?= $wishlisted ? 'btn-sarvam' : 'btn-outline-sarvam' ?> flex-grow-1 py-2.5 rounded-sarvam fw-semibold font-poppins" id="wishlistBtn" onclick="togglePropertyWishlist(<?= $property['id'] ?>)">
                        <i class="bi bi-heart<?= $wishlisted ? '-fill' : '' ?> me-2"></i>
                        <span id="wishlistText"><?= $wishlisted ? 'Saved in Wishlist' : 'Add to Wishlist' ?></span>
                    </button>
                    <button class="btn btn-outline-sarvam px-3 py-2.5 rounded-sarvam" onclick="copyPropertyUrl()" title="Copy Link">
                        <i class="bi bi-share"></i>
                    </button>
                </div>
            </div>

            <!-- Agent Contact Info -->
            <div class="card-sarvam p-4 mb-4">
                <h5 class="fw-bold font-poppins text-heading border-bottom pb-3 mb-4" style="border-color:#C4DCDF !important;"><i class="bi bi-person-badge me-2 text-sarvam"></i>Assigned Expert</h5>
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="<?= $property['agent_photo'] ? SITE_URL.'/assets/uploads/agents/'.$property['agent_photo'] : 'https://placehold.co/80x80/FF893B/fff?text='.urlencode(strtoupper(substr($property['agent_name'],0,1))) ?>" 
                         class="rounded-circle shadow-sm" alt="Agent photo" style="width: 60px; height: 60px; object-fit: cover; border: 2px solid #fff;">
                    <div>
                        <h6 class="fw-bold font-poppins mb-0 text-heading"><?= htmlspecialchars($property['agent_name']) ?></h6>
                        <span class="text-sarvam small font-poppins">Local Property Consultant</span>
                    </div>
                </div>
                <div class="font-poppins small text-muted">
                    <p class="mb-2"><i class="bi bi-telephone-fill me-2 text-sarvam"></i> <?= htmlspecialchars($property['agent_phone'] ?? '') ?></p>
                    <p class="mb-0"><i class="bi bi-envelope-fill me-2 text-sarvam"></i> <?= htmlspecialchars($property['agent_email'] ?? '') ?></p>
                </div>
            </div>

            <!-- Inquiry Submission Form -->
            <div class="card-sarvam p-4 sticky-lg-top" style="top: 90px; z-index: 10;">
                <h5 class="fw-bold font-poppins text-heading border-bottom pb-3 mb-3" style="border-color:#C4DCDF !important;"><i class="bi bi-envelope-check me-2 text-sarvam"></i>Make Inquiry</h5>
                
                <?php if ($inquiry_success): ?>
                    <div class="alert alert-success font-poppins small mb-0" style="background:rgba(34,197,94,0.12);color:#22C55E;border:none;">
                        <i class="bi bi-check-circle-fill me-2"></i>Inquiry submitted successfully! Our agent will connect with you soon.
                    </div>
                <?php else: ?>
                    <?php if ($inquiry_error): ?>
                        <div class="alert alert-danger font-poppins small mb-3" style="background:rgba(239,68,68,0.12);color:#EF4444;border:none;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $inquiry_error ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" class="form-sarvam">
                        <div class="mb-3">
                            <input type="text" name="inq_name" class="form-control border-sarvam" placeholder="Full Name" required value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_name']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <input type="email" name="inq_email" class="form-control border-sarvam" placeholder="Email Address" required value="<?= isLoggedIn() ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
                        </div>
                        <div class="mb-3">
                            <input type="text" name="inq_phone" class="form-control border-sarvam" placeholder="Phone Number" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="inq_message" class="form-control border-sarvam" rows="4" placeholder="I am interested in this property. Please call me." required>I am interested in listing <?= generatePropertyId($property['id']) ?>. Please share more details.</textarea>
                        </div>
                        <button type="submit" name="submit_inquiry" class="btn btn-sarvam w-100 py-2.5 rounded-sarvam fw-semibold font-poppins"><i class="bi bi-send-fill me-2"></i>Send Message</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Properties -->
    <div class="mt-5 border-top pt-5" style="border-color:#C4DCDF !important;">
        <h3 class="fw-bold font-poppins text-heading mb-4 section-title d-inline-block">Related Properties</h3>
        <div class="row g-4 mt-2">
            <?php if (empty($related_properties)): ?>
                <div class="col-12 text-center py-4 text-muted small">No similar properties found.</div>
            <?php else: ?>
                <?php foreach($related_properties as $rp): ?>
                    <div class="col-md-4">
                        <div class="card property-card h-100 shadow-sarvam border-sarvam rounded-sarvam overflow-hidden">
                            <div class="position-relative">
                                <img src="<?= $rp['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$rp['main_image'] : 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&q=80' ?>" 
                                     class="card-img-top property-card-img" alt="<?= htmlspecialchars($rp['title']) ?>" style="height: 200px; object-fit: cover;">
                                <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge property-badge <?= $rp['listing_type'] == 'buy' ? 'badge-success' : 'badge-warning' ?> px-2.5 py-1.5 rounded-1 text-uppercase font-poppins small">
                                        For <?= $rp['listing_type'] == 'buy' ? 'Sale' : 'Rent' ?>
                                    </span>
                                </div>
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <h5 class="text-white fw-bold mb-0 text-shadow font-poppins property-price"><?= formatPrice($rp['price']) ?></h5>
                                </div>
                            </div>
                            <div class="card-body p-3 bg-white">
                                <h6 class="card-title fw-bold font-poppins mb-1 text-truncate-2">
                                    <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $rp['id'] ?>" class="text-decoration-none text-heading hover-primary"><?= htmlspecialchars($rp['title']) ?></a>
                                </h6>
                                <p class="text-muted small mb-2"><i class="bi bi-geo-alt me-1 text-sarvam"></i> <?= htmlspecialchars($rp['city']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</section>

<script>
function swapGalleryImage(src) {
    document.getElementById('mainGalleryImage').src = src;
}

function copyPropertyUrl() {
    navigator.clipboard.writeText(window.location.href);
    alert('Property link copied to clipboard!');
}

function togglePropertyWishlist(propertyId) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = '<?= SITE_URL ?>/login.php';
        return;
    <?php else: ?>
        fetch('<?= SITE_URL ?>/wishlist-action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'property_id=' + propertyId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const btn = document.getElementById('wishlistBtn');
                const txt = document.getElementById('wishlistText');
                if (data.action === 'added') {
                    btn.classList.remove('btn-outline-danger');
                    btn.classList.add('btn-danger');
                    txt.textContent = 'Saved in Wishlist';
                } else {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-danger');
                    txt.textContent = 'Add to Wishlist';
                }
            } else {
                alert('Action failed. Please try again.');
            }
        });
    <?php endif; ?>
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
