<?php
/**
 * ============================================================
 *  FEATURE: USER DASHBOARD
 *  File   : dashboard.php
 *  Role   : Protected page — shows personalised activity summary.
 *           Displays wishlist count, inquiry count, account
 *           status, member-since date, recent inquiries table,
 *           and a 3-card wishlist preview.
 *
 *  Data sources:
 *   • users table         → profile, join date
 *   • wishlist table      → saved properties count
 *   • inquiries table     → total + latest 5 (JOIN properties)
 *   • wishlist JOIN props → latest 3 property cards
 *
 *  Access: requireLogin() redirects guests to /login.php
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';
requireLogin();

$user_id = (int)$_SESSION['user_id'];

// Get user profile details
$user_stmt = $conn->prepare("SELECT created_at, first_name, last_name FROM users WHERE id = ? LIMIT 1");
$user_stmt->execute([$user_id]);
$user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);

$member_since = isset($user_info['created_at']) ? date('M Y', strtotime($user_info['created_at'])) : date('Y');

// Fetch wishlist count
$wishlist_stmt = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
$wishlist_stmt->execute([$user_id]);
$wishlist_count = (int)$wishlist_stmt->fetchColumn();

// Fetch inquiries count
$inquiries_stmt = $conn->prepare("SELECT COUNT(*) FROM inquiries WHERE user_id = ?");
$inquiries_stmt->execute([$user_id]);
$inquiries_count = (int)$inquiries_stmt->fetchColumn();

// Fetch latest inquiries with property titles
$inq_stmt = $conn->prepare("SELECT i.*, p.title as property_title, p.id as prop_id 
              FROM inquiries i 
              LEFT JOIN properties p ON i.property_id = p.id 
              WHERE i.user_id = ? 
              ORDER BY i.created_at DESC 
              LIMIT 5");
$inq_stmt->execute([$user_id]);
$inquiries = $inq_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent wishlist items
$wish_stmt = $conn->prepare("SELECT w.id as wish_id, p.*, pt.type_name, l.city 
               FROM wishlist w 
               JOIN properties p ON w.property_id = p.id 
               JOIN property_types pt ON p.property_type_id = pt.id 
               JOIN locations l ON p.location_id = l.id 
               WHERE w.user_id = ? 
               ORDER BY w.created_at DESC 
               LIMIT 3");
$wish_stmt->execute([$user_id]);
$wishlist_items = $wish_stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid py-4 section-light">
    <!-- Breadcrumb -->
    <nav class="mb-4 small breadcrumb-sarvam">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-decoration-none text-sarvam">Home</a></li>
            <li class="breadcrumb-item active">Dashboard</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="fw-bold text-heading mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>! 👋</h2>
        <span class="text-muted small">Logged in as: <strong class="text-sarvam"><?= htmlspecialchars($_SESSION['user_email']) ?></strong></span>
    </div>

    <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
        </div>
        
        <!-- Content -->
        <div class="col-lg-9">
            <!-- Stats Grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card bg-white border-sarvam rounded-sarvam p-3 shadow-sarvam d-flex align-items-center">
                        <div class="stat-card-icon badge-teal rounded p-3 me-3">
                            <i class="bi bi-heart-fill fs-4 text-white"></i>
                        </div>
                        <div>
                            <h3 class="stat-card-value fw-bold mb-0 text-heading"><?= $wishlist_count ?></h3>
                            <p class="stat-card-label mb-0 small text-muted">Saved Properties</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card bg-white border-sarvam rounded-sarvam p-3 shadow-sarvam d-flex align-items-center">
                        <div class="stat-card-icon badge-sarvam rounded p-3 me-3">
                            <i class="bi bi-envelope-fill fs-4 text-white"></i>
                        </div>
                        <div>
                            <h3 class="stat-card-value fw-bold mb-0 text-heading"><?= $inquiries_count ?></h3>
                            <p class="stat-card-label mb-0 small text-muted">Inquiries Sent</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card bg-white border-sarvam rounded-sarvam p-3 shadow-sarvam d-flex align-items-center">
                        <div class="stat-card-icon badge-success rounded p-3 me-3">
                            <i class="bi bi-person-fill-check fs-4 text-white"></i>
                        </div>
                        <div>
                            <h3 class="stat-card-value fw-bold mb-0 text-heading">Active</h3>
                            <p class="stat-card-label mb-0 small text-muted">Account Status</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stat-card bg-white border-sarvam rounded-sarvam p-3 shadow-sarvam d-flex align-items-center">
                        <div class="stat-card-icon badge-warning rounded p-3 me-3">
                            <i class="bi bi-calendar-event-fill fs-4 text-white"></i>
                        </div>
                        <div>
                            <h3 class="stat-card-value fw-bold mb-0 fs-5 text-heading"><?= htmlspecialchars($member_since) ?></h3>
                            <p class="stat-card-label mb-0 small text-muted">Member Since</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-12">
                    <!-- Inquiries Table -->
                    <div class="bg-white border-sarvam rounded-sarvam shadow-sarvam p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold text-heading mb-0"><i class="bi bi-envelope me-2 text-sarvam"></i>My Recent Inquiries</h5>
                            <a href="<?= SITE_URL ?>/my-inquiries.php" class="text-decoration-none small fw-medium text-sarvam">View All</a>
                        </div>

                        <?php if (empty($inquiries)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-inbox fs-2 text-muted mb-2 d-block"></i>
                                <p class="text-secondary small mb-0">You have not submitted any inquiries yet.</p>
                                <a href="<?= SITE_URL ?>/properties.php" class="btn btn-sarvam btn-sm mt-3">Browse Properties</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive-sarvam">
                                <table class="table-sarvam table align-middle">
                                    <thead class="table-light small">
                                        <tr>
                                            <th>Property</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small text-secondary">
                                        <?php foreach ($inquiries as $inq): ?>
                                            <tr>
                                                <td>
                                                    <?php if ($inq['property_title']): ?>
                                                        <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $inq['prop_id'] ?>" class="text-decoration-none fw-medium text-sarvam">
                                                            <?= htmlspecialchars(substr($inq['property_title'], 0, 30)) ?>...
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">General Inquiry</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars(substr($inq['message'], 0, 45)) ?>...</td>
                                                <td>
                                                    <?php if ($inq['status'] === 'responded'): ?>
                                                        <span class="badge badge-success">Responded</span>
                                                    <?php elseif ($inq['status'] === 'closed'): ?>
                                                        <span class="badge bg-secondary">Closed</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning text-dark">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d M Y', strtotime($inq['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Wishlist -->
                    <div class="bg-white border-sarvam rounded-sarvam shadow-sarvam p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold text-heading mb-0"><i class="bi bi-heart me-2 text-sarvam"></i>Saved Properties</h5>
                            <a href="<?= SITE_URL ?>/wishlist.php" class="text-decoration-none small fw-medium text-sarvam">View All</a>
                        </div>

                        <?php if (empty($wishlist_items)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-heart fs-2 text-muted mb-2 d-block"></i>
                                <p class="text-secondary small mb-0">Your wishlist is empty.</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($wishlist_items as $item): ?>
                                    <div class="col-md-4">
                                        <div class="property-card bg-white border-sarvam rounded-sarvam shadow-sarvam overflow-hidden h-100">
                                            <img src="<?= $item['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$item['main_image'] : 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&q=80' ?>" 
                                                 alt="<?= htmlspecialchars($item['title']) ?>" class="property-card-img w-100" style="height: 120px; object-fit: cover;">
                                            <div class="p-3">
                                                <h6 class="fw-bold mb-1 text-truncate">
                                                    <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $item['id'] ?>" class="text-decoration-none text-heading"><?= htmlspecialchars($item['title']) ?></a>
                                                </h6>
                                                <p class="property-price fw-bold small mb-0 text-sarvam"><?= formatPrice($item['price']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
