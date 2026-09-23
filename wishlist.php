<?php
/**
 * ============================================================
 *  FEATURE: USER WISHLIST / SAVED PROPERTIES
 *  File   : wishlist.php
 *  Role   : Displays all properties a logged-in user has saved.
 *           Each card shows property image, title, price, type,
 *           location, and a remove-from-wishlist button.
 *
 *  Data source:
 *   • wishlist JOIN properties JOIN property_types JOIN locations
 *     → ordered by date saved, newest first
 *
 *  Toggle interaction:
 *   • Heart button calls wishlist-action.php via AJAX (no page reload)
 *   • On remove: card fades out and wishlist count badge updates
 *
 *  Access: requireLogin() → guests redirected to /login.php
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';
requireLogin();

$user_id = (int)$_SESSION['user_id'];
$wishlist = [];

// Fetch wishlist properties joined with types and locations
$stmt = $conn->prepare("SELECT w.id as wish_id, p.*, pt.type_name, l.city, l.state 
          FROM wishlist w 
          JOIN properties p ON w.property_id = p.id 
          JOIN property_types pt ON p.property_type_id = pt.id 
          JOIN locations l ON p.location_id = l.id 
          WHERE w.user_id = ? 
          ORDER BY w.created_at DESC");
$stmt->execute([$user_id]);
$wishlist = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-hero section-dark text-white py-4 mb-4">
    <div class="container">
        <h2 class="fw-bold text-white mb-2">My Wishlist</h2>
        <nav class="small breadcrumb-sarvam">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-white text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/dashboard.php" class="text-white text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active text-white-50">My Wishlist</li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <p class="text-secondary small mb-0">You have saved <?= count($wishlist) ?> properties</p>
        </div>
        <a href="<?= SITE_URL ?>/properties.php" class="btn btn-outline-sarvam btn-sm"><i class="bi bi-search me-1"></i> Browse More Properties</a>
    </div>

    <!-- Wishlist Grid -->
    <?php if (empty($wishlist)): ?>
        <div class="card border-sarvam shadow-sarvam p-5 text-center rounded-sarvam bg-white">
            <div class="fs-1 text-sarvam mb-3"><i class="bi bi-heart"></i></div>
            <h4 class="fw-bold text-heading mb-2">Your wishlist is empty</h4>
            <p class="text-secondary small mb-4">Save properties while browsing to view them later here.</p>
            <a href="<?= SITE_URL ?>/properties.php" class="btn btn-sarvam px-4 py-2 rounded-3 fw-semibold">Explore Properties</a>
        </div>
    <?php else: ?>
        <div class="row g-4" id="wishlistGrid">
            <?php foreach ($wishlist as $prop): ?>
                <div class="col-lg-4 col-md-6" id="wishlist-col-<?= $prop['id'] ?>">
                    <div class="property-card bg-white h-100 shadow-sarvam border-sarvam rounded-sarvam overflow-hidden">
                        <div class="position-relative">
                            <img src="<?= $prop['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$prop['main_image'] : 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&q=80' ?>" 
                                 class="property-card-img w-100" alt="<?= htmlspecialchars($prop['title']) ?>" style="height: 200px; object-fit: cover;">
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge-sarvam property-badge px-2 py-1 rounded-1 text-uppercase small">
                                    For <?= $prop['listing_type'] == 'buy' ? 'Sale' : 'Rent' ?>
                                </span>
                            </div>
                            <div class="position-absolute bottom-0 start-0 m-3">
                                <h4 class="text-white property-price fw-bold mb-0 text-shadow"><?= formatPrice($prop['price']) ?></h4>
                            </div>
                        </div>
                        <div class="p-4">
                            <span class="badge bg-sarvam-ice text-sarvam uppercase small mb-2"><?= htmlspecialchars($prop['type_name']) ?></span>
                            <h5 class="fw-bold mb-2 text-truncate-2" style="font-size: 1rem; line-height: 1.4;">
                                <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $prop['id'] ?>" class="text-decoration-none text-heading"><?= htmlspecialchars($prop['title']) ?></a>
                            </h5>
                            <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1 text-sarvam"></i> <?= htmlspecialchars($prop['address'] ?? $prop['city']) ?></p>
                            
                            <div class="d-flex justify-content-between border-top pt-3 text-secondary small mb-3" style="font-size: 0.8rem;">
                                <span><i class="bi bi-door-open me-1 text-sarvam"></i> <?= $prop['bedrooms'] ?> Beds</span>
                                <span><i class="bi bi-droplet me-1 text-sarvam"></i> <?= $prop['bathrooms'] ?> Baths</span>
                                <span><i class="bi bi-aspect-ratio me-1 text-sarvam"></i> <?= formatArea($prop['area_sqft']) ?></span>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $prop['id'] ?>" class="btn btn-outline-sarvam btn-sm flex-grow-1 fw-semibold"><i class="bi bi-eye me-1"></i> View Details</a>
                                <button class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="removeWishlistItem(<?= $prop['id'] ?>)" title="Remove from Wishlist"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function removeWishlistItem(propertyId) {
    if (confirm('Are you sure you want to remove this property from your wishlist?')) {
        fetch('<?= SITE_URL ?>/wishlist-action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'property_id=' + propertyId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.action === 'removed') {
                const col = document.getElementById('wishlist-col-' + propertyId);
                if (col) {
                    col.remove();
                }
                
                // If wishlist is empty, refresh to show the empty state
                const remaining = document.querySelectorAll('#wishlistGrid > div');
                if (remaining.length === 0) {
                    window.location.reload();
                }
            } else {
                alert('Action failed. Please try again.');
            }
        });
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
