<?php
/**
 * ============================================================
 *  FEATURE: PROPERTY LISTINGS WITH DYNAMIC SEARCH & FILTERS
 *  File   : properties.php
 *  Role   : Public browsing page — displays a filterable,
 *           searchable, paginated grid of all active properties.
 *
 *  Filters available (all optional, from GET params):
 *   • keyword  — LIKE search on title + description
 *   • type_id  — property type (Apartment, Villa, etc.)
 *   • listing  — 'buy' or 'rent'
 *   • city     — location filter
 *   • min_price / max_price — price range
 *   • beds     — minimum bedrooms
 *   • sort     — price_asc, price_desc, newest (default)
 *
 *  SQL construction:
 *   • WHERE clause is built dynamically; PDO bound params prevent
 *     SQL Injection regardless of how many filters are active
 *
 *  Pagination:
 *   • 9 properties per page; total count query runs first
 *
 *  Wishlist state:
 *   • If logged in, fetches user's saved property IDs so heart
 *     icons show filled/empty state on every card
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';

$page_title = "Browse Verified Properties";
$page_description = "Explore a wide range of verified apartments, villas, builder floors, and plots available for sale and rent.";

// Fetch filter options
$types = getPropertyTypes($conn);
$locations = getLocations($conn);

// Get query parameters
$search = trim($_GET['search'] ?? '');
$type_id = (int)($_GET['type_id'] ?? 0);
$location_id = (int)($_GET['location_id'] ?? 0);
$listing_type = $_GET['listing_type'] ?? ''; // 'buy' or 'rent'
$bedrooms = $_GET['bedrooms'] ?? '';
$min_price = (float)($_GET['min_price'] ?? 0);
$max_price = (float)($_GET['max_price'] ?? 0);
$premium = (int)($_GET['premium'] ?? 0);
$sort = $_GET['sort'] ?? 'newest';

// Pagination setup
$per_page = 12;
$current_page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $per_page;

// Build SQL dynamically using prepared statements
$where_clauses = ["p.status = 'available'"];
$params = [];
$param_types = '';

if ($search !== '') {
    $where_clauses[] = "(p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ? OR l.city LIKE ?)";
    $search_like = "%$search%";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
    $param_types .= 'ssss';
}

if ($type_id > 0) {
    $where_clauses[] = "p.property_type_id = ?";
    $params[] = $type_id;
    $param_types .= 'i';
}

if ($location_id > 0) {
    $where_clauses[] = "p.location_id = ?";
    $params[] = $location_id;
    $param_types .= 'i';
}

if (in_array($listing_type, ['buy', 'rent'])) {
    $where_clauses[] = "p.listing_type = ?";
    $params[] = $listing_type;
    $param_types .= 's';
}

if ($bedrooms !== '') {
    if ($bedrooms === '5+') {
        $where_clauses[] = "p.bedrooms >= 5";
    } else {
        $where_clauses[] = "p.bedrooms = ?";
        $params[] = (int)$bedrooms;
        $param_types .= 'i';
    }
}

if ($min_price > 0) {
    $where_clauses[] = "p.price >= ?";
    $params[] = $min_price;
    $param_types .= 'd';
}

if ($max_price > 0) {
    $where_clauses[] = "p.price <= ?";
    $params[] = $max_price;
    $param_types .= 'd';
}

if ($premium > 0) {
    $where_clauses[] = "p.is_premium = 1";
}

$where_sql = implode(' AND ', $where_clauses);

// Determine sorting
$order_sql = "p.created_at DESC";
if ($sort === 'price_low') {
    $order_sql = "p.price ASC";
} elseif ($sort === 'price_high') {
    $order_sql = "p.price DESC";
} elseif ($sort === 'area_high') {
    $order_sql = "p.area_sqft DESC";
}

// Fetch total count for pagination
$count_query = "SELECT COUNT(*) FROM properties p JOIN locations l ON p.location_id = l.id WHERE $where_sql";
$count_stmt = $conn->prepare($count_query);
$count_stmt->execute($params);
$total_properties = (int)$count_stmt->fetchColumn();

$total_pages = ceil($total_properties / $per_page);

// Fetch paginated results
$data_query = "SELECT p.*, pt.type_name, l.city, l.state 
               FROM properties p 
               JOIN property_types pt ON p.property_type_id = pt.id 
               JOIN locations l ON p.location_id = l.id 
               WHERE $where_sql 
               ORDER BY $order_sql 
               LIMIT $per_page OFFSET $offset";
$data_stmt = $conn->prepare($data_query);
$data_stmt->execute($params);
$properties = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="container">
    <h1 class="fw-bold text-white mb-2">Properties</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:rgba(255,255,255,0.4);">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-sarvam">Home</a></li>
        <li class="breadcrumb-item active text-white">Properties</li>
      </ol>
    </nav>
  </div>
</section>

<section class="section-light py-5">
    <div class="container">
        <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="filter-panel p-4 sticky-lg-top" style="top: 90px; z-index: 10;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold font-poppins mb-0 text-heading"><i class="bi bi-funnel me-2 text-sarvam"></i>Filters</h5>
                    <a href="<?= SITE_URL ?>/properties.php" class="text-decoration-none small text-sarvam">Reset All</a>
                </div>

                <form method="GET" action="<?= SITE_URL ?>/properties.php" class="form-sarvam">
                    <input type="hidden" name="premium" value="<?= $premium ?>">
                    
                    <!-- Search Input -->
                    <div class="mb-3">
                        <label class="form-label font-poppins small fw-medium text-muted">Keyword Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-sarvam border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-sarvam border-start-0" placeholder="e.g. villa, pool, Bandra" value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>

                    <!-- Listing Type -->
                    <div class="mb-3">
                        <label class="form-label font-poppins small fw-medium text-muted">Listing Type</label>
                        <select name="listing_type" class="form-select form-select-sm border-sarvam">
                            <option value="">All Listings</option>
                            <option value="buy" <?= $listing_type === 'buy' ? 'selected' : '' ?>>For Sale</option>
                            <option value="rent" <?= $listing_type === 'rent' ? 'selected' : '' ?>>For Rent</option>
                        </select>
                    </div>

                    <!-- Property Type -->
                    <div class="mb-3">
                        <label class="form-label font-poppins small fw-medium text-muted">Property Type</label>
                        <select name="type_id" class="form-select form-select-sm border-sarvam">
                            <option value="">Any Type</option>
                            <?php foreach ($types as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $type_id === (int)$t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['type_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Location -->
                    <div class="mb-3">
                        <label class="form-label font-poppins small fw-medium text-muted">Location/City</label>
                        <select name="location_id" class="form-select form-select-sm border-sarvam">
                            <option value="">Any Location</option>
                            <?php foreach ($locations as $l): ?>
                                <option value="<?= $l['id'] ?>" <?= $location_id === (int)$l['id'] ? 'selected' : '' ?>><?= htmlspecialchars($l['city']) ?>, <?= htmlspecialchars($l['state']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Bedrooms -->
                    <div class="mb-3">
                        <label class="form-label font-poppins small fw-medium text-muted">Bedrooms</label>
                        <select name="bedrooms" class="form-select form-select-sm border-sarvam">
                            <option value="">Any Beds</option>
                            <option value="1" <?= $bedrooms === '1' ? 'selected' : '' ?>>1 BHK</option>
                            <option value="2" <?= $bedrooms === '2' ? 'selected' : '' ?>>2 BHK</option>
                            <option value="3" <?= $bedrooms === '3' ? 'selected' : '' ?>>3 BHK</option>
                            <option value="4" <?= $bedrooms === '4' ? 'selected' : '' ?>>4 BHK</option>
                            <option value="5+" <?= $bedrooms === '5+' ? 'selected' : '' ?>>5+ BHK</option>
                        </select>
                    </div>

                    <!-- Price Filters -->
                    <div class="mb-4">
                        <label class="form-label font-poppins small fw-medium text-muted">Price Range (₹)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="min_price" class="form-control form-control-sm border-sarvam" placeholder="Min" value="<?= $min_price > 0 ? $min_price : '' ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" name="max_price" class="form-control form-control-sm border-sarvam" placeholder="Max" value="<?= $max_price > 0 ? $max_price : '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Sort -->
                    <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

                    <button type="submit" class="btn btn-sarvam w-100 py-2.5 rounded-sarvam mb-2 font-poppins small fw-semibold"><i class="bi bi-funnel-fill me-2"></i>Apply Filters</button>
                </form>
            </div>
        </div>

        <!-- Property Grid Results -->
        <div class="col-lg-9">
            <!-- Results Bar -->
            <div class="card-sarvam p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="fw-bold font-poppins mb-0 text-heading">
                        Showing <?= count($properties) ?> of <?= $total_properties ?> Properties
                    </h5>
                    <div class="d-flex align-items-center gap-3">
                        <label class="small text-muted font-poppins text-nowrap">Sort By:</label>
                        <select class="form-select form-select-sm border-sarvam rounded-sarvam" style="width: 180px;" onchange="window.location.href=this.value;">
                            <?php 
                            // Rebuild query for sort values
                            $q_arr = $_GET;
                            ?>
                            <option value="?<?= http_build_query(array_merge($q_arr, ['sort' => 'newest'])) ?>" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="?<?= http_build_query(array_merge($q_arr, ['sort' => 'price_low'])) ?>" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="?<?= http_build_query(array_merge($q_arr, ['sort' => 'price_high'])) ?>" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="?<?= http_build_query(array_merge($q_arr, ['sort' => 'area_high'])) ?>" <?= $sort === 'area_high' ? 'selected' : '' ?>>Area: Large to Small</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Properties Cards -->
            <div class="row g-4">
                <?php if (empty($properties)): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-building fs-1 text-muted mb-3 d-block"></i>
                        <h4 class="fw-bold text-heading font-poppins">No Properties Found</h4>
                        <p class="text-muted max-w-sm mx-auto small">We couldn't find any properties matching your current search filters. Please adjust the filters and try again.</p>
                        <a href="<?= SITE_URL ?>/properties.php" class="btn btn-sarvam btn-sm mt-2">Reset Filters</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($properties as $prop): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card property-card h-100 shadow-sarvam border-sarvam rounded-sarvam overflow-hidden">
                                <div class="position-relative">
                                    <img src="<?= $prop['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$prop['main_image'] : 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&q=80' ?>" 
                                         class="card-img-top property-card-img" alt="<?= htmlspecialchars($prop['title']) ?>" style="height: 200px; object-fit: cover;">
                                    <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2">
                                        <?php if ($prop['is_premium']): ?>
                                            <span class="badge property-badge badge-teal px-2.5 py-1.5 rounded-1 text-uppercase font-poppins small d-flex align-items-center gap-1">
                                                <i class="bi bi-gem"></i> Premium
                                            </span>
                                        <?php elseif ($prop['is_featured']): ?>
                                            <span class="badge property-badge badge-sarvam px-2.5 py-1.5 rounded-1 text-uppercase font-poppins small">Featured</span>
                                        <?php endif; ?>
                                        <span class="badge property-badge <?= $prop['listing_type'] == 'buy' ? 'badge-success' : 'badge-warning' ?> px-2.5 py-1.5 rounded-1 text-uppercase font-poppins small">
                                            For <?= $prop['listing_type'] == 'buy' ? 'Sale' : 'Rent' ?>
                                        </span>
                                    </div>
                                    <div class="position-absolute bottom-0 start-0 m-3">
                                        <h4 class="text-white fw-bold mb-0 text-shadow font-poppins property-price"><?= formatPrice($prop['price']) ?></h4>
                                    </div>
                                </div>
                                <div class="card-body p-4 bg-white">
                                    <span class="badge bg-sarvam-ice text-sarvam uppercase font-poppins small mb-2"><?= htmlspecialchars($prop['type_name']) ?></span>
                                    <h5 class="card-title fw-bold font-poppins mb-2 text-heading text-truncate-2" style="font-size: 1rem; line-height: 1.4;">
                                        <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $prop['id'] ?>" class="text-decoration-none text-heading hover-primary"><?= htmlspecialchars($prop['title']) ?></a>
                                    </h5>
                                    <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1 text-sarvam"></i> <?= htmlspecialchars($prop['address'] ?? $prop['city']) ?></p>
                                    
                                    <div class="d-flex justify-content-between border-top pt-3 text-muted font-poppins small" style="font-size: 0.8rem; border-color:#C4DCDF !important;">
                                        <span><i class="bi bi-door-open me-1 text-sarvam"></i> <?= $prop['bedrooms'] ?> Beds</span>
                                        <span><i class="bi bi-droplet me-1 text-sarvam"></i> <?= $prop['bathrooms'] ?> Baths</span>
                                        <span><i class="bi bi-aspect-ratio me-1 text-sarvam"></i> <?= formatArea($prop['area_sqft']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination Grid -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination pagination-sarvam justify-content-center">
                        <?php 
                        $base_params = $_GET;
                        // Build pagination links
                        if ($current_page > 1): 
                            $base_params['page'] = $current_page - 1;
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query($base_params) ?>" aria-label="Previous">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): 
                            $base_params['page'] = $i;
                        ?>
                            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query($base_params) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php 
                        if ($current_page < $total_pages): 
                            $base_params['page'] = $current_page + 1;
                        ?>
                            <li class="page-item">
                                <a class="page-link" href="?<?= http_build_query($base_params) ?>" aria-label="Next">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
