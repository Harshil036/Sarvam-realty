<?php
/**
 * Sarvam Real Estate - Home Page
 */
require_once __DIR__ . '/config/db.php';

$page_title = "Find Your Dream Property with Confidence";
$page_description = "Sarvam Real Estate helps you find premium residential and commercial properties, apartments, villas, and lands across India.";

// Fetch dynamic data from database using helper functions
$featured_properties = getFeaturedProperties($conn, 6);
$latest_properties = getLatestProperties($conn, 8);
$premium_properties = getPremiumProperties($conn, 6);
$property_types = getPropertyTypes($conn);
$locations = getLocations($conn);
$featured_agents = getAgents($conn, 4);

// Fetch active testimonials
$res_testi = $conn->query("SELECT * FROM testimonials WHERE status='active' ORDER BY created_at DESC LIMIT 6");
$testimonials = $res_testi ? $res_testi->fetchAll() : [];

// Fetch property counts per city for popular cities section
$city_counts = [];
$res_cities = $conn->query("SELECT l.city, COUNT(p.id) as count, l.state FROM properties p JOIN locations l ON p.location_id = l.id WHERE p.status = 'available' GROUP BY l.city");
if ($res_cities) {
    foreach ($res_cities->fetchAll() as $row) {
        $city_counts[$row['city']] = $row['count'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section with Search Tab -->
<section class="hero-section position-relative overflow-hidden d-flex align-items-center" style="background: linear-gradient(rgba(45,52,61,0.72), rgba(45,52,61,0.72)), url('https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=1920&q=80') no-repeat center center/cover; min-height: 85vh; padding: 100px 0;">
    <div class="container position-relative z-3 text-white">
        <div class="row justify-content-center text-center">
            <div class="col-lg-10">
                <span class="badge hero-badge px-3 py-2 rounded-pill mb-3 text-uppercase tracking-wider fw-semibold animate-fade-in-down" style="letter-spacing: 2px;">Sarvam Real Estate</span>
                <h1 class="display-3 fw-bold mb-4 font-poppins text-shadow animate-fade-in" style="line-height: 1.2;">
                    Find Your Dream Property<br><span style="color:#FF893B;">With Absolute Confidence</span>
                </h1>
                <p class="lead mb-5 max-w-2xl mx-auto animate-fade-in-up" style="color:rgba(255,255,255,0.8);">
                    Discover verified residential and commercial apartments, villas, and lands across India's top cities with India's most trusted real estate platform.
                </p>

                <!-- Search Bar Container -->
                <div class="hero-search-card card-sarvam p-2 mx-auto rounded-sarvam animate-fade-in-up" style="max-width: 900px;">
                    <ul class="nav nav-pills mb-2" id="searchTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active btn-sarvam rounded-pill px-4 py-2" id="buy-tab" data-bs-toggle="tab" data-bs-target="#buy-search" type="button" role="tab">Buy Property</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-4 py-2 fw-semibold rounded-pill text-sarvam" id="rent-tab" data-bs-toggle="tab" data-bs-target="#rent-search" type="button" role="tab">Rent Property</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content p-3" id="searchTabContent">
                        <!-- Buy Search Form -->
                        <div class="tab-pane fade show active" id="buy-search" role="tabpanel">
                            <form action="<?= SITE_URL ?>/properties.php" method="GET" class="row g-3 form-sarvam">
                                <input type="hidden" name="listing_type" value="buy">
                                <div class="col-md-3">
                                    <label class="form-label text-heading text-start w-100 fw-medium small">Property Type</label>
                                    <select name="type_id" class="form-select border-sarvam">
                                        <option value="">Any Type</option>
                                        <?php foreach ($property_types as $pt): ?>
                                            <option value="<?= $pt['id'] ?>"><?= htmlspecialchars($pt['type_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-heading text-start w-100 fw-medium small">Location/City</label>
                                    <select name="location_id" class="form-select border-sarvam">
                                        <option value="">Any Location</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['city']) ?>, <?= htmlspecialchars($loc['state']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-heading text-start w-100 fw-medium small">Bedrooms</label>
                                    <select name="bedrooms" class="form-select border-sarvam">
                                        <option value="">Any Beds</option>
                                        <option value="1">1 BHK</option>
                                        <option value="2">2 BHK</option>
                                        <option value="3">3 BHK</option>
                                        <option value="4">4 BHK</option>
                                        <option value="5+">5+ BHK</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-heading text-start w-100 fw-medium small">Max Budget</label>
                                    <input type="number" name="max_price" class="form-control border-sarvam" placeholder="e.g. 15000000">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-sarvam w-100 py-2 rounded-sarvam"><i class="bi bi-search me-2"></i>Search</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Rent Search Form -->
                        <div class="tab-pane fade" id="rent-search" role="tabpanel">
                            <form action="<?= SITE_URL ?>/properties.php" method="GET" class="row g-3 form-sarvam">
                                <input type="hidden" name="listing_type" value="rent">
                                <div class="col-md-3">
                                    <label class="form-label text-heading text-start w-100 fw-medium small">Property Type</label>
                                    <select name="type_id" class="form-select border-sarvam">
                                        <option value="">Any Type</option>
                                        <?php foreach ($property_types as $pt): ?>
                                            <option value="<?= $pt['id'] ?>"><?= htmlspecialchars($pt['type_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-heading text-start w-100 fw-medium small">Location/City</label>
                                    <select name="location_id" class="form-select border-sarvam">
                                        <option value="">Any Location</option>
                                        <?php foreach ($locations as $loc): ?>
                                            <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['city']) ?>, <?= htmlspecialchars($loc['state']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-heading text-start w-100 fw-medium small">Bedrooms</label>
                                    <select name="bedrooms" class="form-select border-sarvam">
                                        <option value="">Any Beds</option>
                                        <option value="1">1 BHK</option>
                                        <option value="2">2 BHK</option>
                                        <option value="3">3 BHK</option>
                                        <option value="4">4 BHK</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label text-heading text-start w-100 fw-medium small">Max Monthly Rent</label>
                                    <input type="number" name="max_price" class="form-control border-sarvam" placeholder="e.g. 50000">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-sarvam w-100 py-2 rounded-sarvam"><i class="bi bi-search me-2"></i>Search</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- Stats Counter Section -->
<section class="py-5 section-dark">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6 hero-stat-item border-end-md" style="border-color: rgba(255,255,255,0.1) !important;">
                <h3 class="display-5 fw-bold font-poppins mb-2" style="color:#FF893B;">5,000+</h3>
                <p class="text-white-50 uppercase font-poppins small fw-medium tracking-wide">Properties Listed</p>
            </div>
            <div class="col-md-3 col-6 hero-stat-item border-end-md" style="border-color: rgba(255,255,255,0.1) !important;">
                <h3 class="display-5 fw-bold font-poppins mb-2" style="color:#FF893B;">2,000+</h3>
                <p class="text-white-50 uppercase font-poppins small fw-medium tracking-wide">Happy Families</p>
            </div>
            <div class="col-md-3 col-6 hero-stat-item border-end-md" style="border-color: rgba(255,255,255,0.1) !important;">
                <h3 class="display-5 fw-bold font-poppins mb-2" style="color:#FF893B;">500+</h3>
                <p class="text-white-50 uppercase font-poppins small fw-medium tracking-wide">Verified Agents</p>
            </div>
            <div class="col-md-3 col-6 hero-stat-item">
                <h3 class="display-5 fw-bold font-poppins mb-2" style="color:#FF893B;">15+</h3>
                <p class="text-white-50 uppercase font-poppins small fw-medium tracking-wide">Years of Excellence</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-5 section-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <span class="badge-sarvam px-3 py-2 rounded-pill uppercase tracking-wider small fw-semibold">Handpicked Listings</span>
                <h2 class="h1 fw-bold font-poppins mt-2 mb-0 section-title text-heading d-inline-block">Featured Properties</h2>
            </div>
            <a href="<?= SITE_URL ?>/properties.php" class="btn btn-sarvam">Browse All Properties <i class="bi bi-arrow-right ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php if (empty($featured_properties)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No featured properties available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($featured_properties as $prop): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card property-card h-100 shadow-sarvam border-sarvam rounded-sarvam overflow-hidden">
                            <div class="position-relative">
                                <img src="<?= $prop['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$prop['main_image'] : 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=600&q=80' ?>" 
                                     class="card-img-top property-card-img" alt="<?= htmlspecialchars($prop['title']) ?>" style="height: 240px; object-fit: cover;">
                                <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2">
                                    <span class="badge property-badge badge-sarvam px-2.5 py-1.5 rounded-1 text-uppercase font-poppins small">Featured</span>
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
                                <h5 class="card-title fw-bold font-poppins mb-2 text-heading text-truncate-2">
                                    <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $prop['id'] ?>" class="text-decoration-none text-heading hover-primary"><?= htmlspecialchars($prop['title']) ?></a>
                                </h5>
                                <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1 text-sarvam"></i> <?= htmlspecialchars($prop['address'] ?? $prop['city']) ?></p>
                                
                                <div class="d-flex justify-content-between border-top pt-3 text-muted font-poppins small" style="border-color: #C4DCDF !important;">
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
    </div>
</section>

<!-- Property Categories -->
<section class="py-5 section-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge-sarvam px-3 py-2 rounded-pill uppercase tracking-wider small fw-semibold">Categories</span>
            <h2 class="h1 fw-bold font-poppins mt-2 text-heading section-title d-inline-block">Find by Property Type</h2>
            <p class="text-muted max-w-xl mx-auto mt-3">Explore properties cataloged under specific property types to quickly narrow down your search.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php foreach ($property_types as $pt): 
                // Count properties for this type
                $t_id = (int)$pt['id'];
                $count_res = $conn->query("SELECT COUNT(*) as c FROM properties WHERE property_type_id=$t_id AND status='available'");
                $p_count = $count_res ? (int)$count_res->fetchColumn() : 0;
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="<?= SITE_URL ?>/properties.php?type_id=<?= $pt['id'] ?>" class="text-decoration-none">
                        <div class="category-card card-sarvam text-center p-4 rounded-sarvam h-100 border-sarvam">
                            <div class="category-icon bg-sarvam-ice text-sarvam rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 70px; height: 70px;">
                                <i class="bi <?= htmlspecialchars($pt['icon'] ?? 'bi-building') ?> fs-2"></i>
                            </div>
                            <h5 class="fw-bold font-poppins text-heading mb-1"><?= htmlspecialchars($pt['type_name']) ?></h5>
                            <p class="text-muted small mb-0"><?= $p_count ?> Properties</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Premium Properties Section -->
<section class="py-5 section-white">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-5 flex-wrap gap-3">
            <div>
                <span class="badge-sarvam px-3 py-2 rounded-pill uppercase tracking-wider small fw-semibold">Elite Collection</span>
                <h2 class="h1 fw-bold font-poppins mt-2 mb-0 text-heading section-title d-inline-block">Premium Collections</h2>
            </div>
            <a href="<?= SITE_URL ?>/properties.php?premium=1" class="btn btn-outline-sarvam">View Premium Properties <i class="bi bi-gem ms-1"></i></a>
        </div>

        <div class="row g-4">
            <?php if (empty($premium_properties)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No premium properties available currently.</p>
                </div>
            <?php else: ?>
                <?php foreach ($premium_properties as $prop): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card property-card h-100 shadow-sarvam border-sarvam rounded-sarvam overflow-hidden position-relative">
                            <div class="position-relative">
                                <img src="<?= $prop['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$prop['main_image'] : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&q=80' ?>" 
                                     class="card-img-top property-card-img" alt="<?= htmlspecialchars($prop['title']) ?>" style="height: 240px; object-fit: cover;">
                                <div class="position-absolute top-0 start-0 m-3 d-flex flex-column gap-2">
                                    <span class="badge property-badge badge-teal px-2.5 py-1.5 rounded-1 text-uppercase font-poppins small d-flex align-items-center gap-1">
                                        <i class="bi bi-gem"></i> Premium
                                    </span>
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
                                <h5 class="card-title fw-bold font-poppins mb-2 text-heading text-truncate-2">
                                    <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $prop['id'] ?>" class="text-decoration-none text-heading hover-primary"><?= htmlspecialchars($prop['title']) ?></a>
                                </h5>
                                <p class="text-muted small mb-3"><i class="bi bi-geo-alt me-1 text-sarvam"></i> <?= htmlspecialchars($prop['address'] ?? $prop['city']) ?></p>
                                
                                <div class="d-flex justify-content-between border-top pt-3 text-muted font-poppins small" style="border-color: #C4DCDF !important;">
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
    </div>
</section>

<!-- Latest Properties -->
<section class="py-5 section-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge-sarvam px-3 py-2 rounded-pill uppercase tracking-wider small fw-semibold">New Listings</span>
            <h2 class="h1 fw-bold font-poppins mt-2 text-heading section-title d-inline-block">Latest Additions</h2>
            <p class="text-muted max-w-xl mx-auto mt-3">Explore recently added properties available for immediate purchase or rent across India.</p>
        </div>

        <div class="row g-4">
            <?php if (empty($latest_properties)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No new additions available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($latest_properties as $prop): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="card property-card h-100 shadow-sarvam border-sarvam rounded-sarvam overflow-hidden">
                            <div class="position-relative">
                                <img src="<?= $prop['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$prop['main_image'] : 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=600&q=80' ?>" 
                                     class="card-img-top property-card-img" alt="<?= htmlspecialchars($prop['title']) ?>" style="height: 200px; object-fit: cover;">
                                <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge property-badge <?= $prop['listing_type'] == 'buy' ? 'badge-success' : 'badge-warning' ?> px-2.5 py-1.5 rounded-1 text-uppercase font-poppins small">
                                        For <?= $prop['listing_type'] == 'buy' ? 'Sale' : 'Rent' ?>
                                    </span>
                                </div>
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <h5 class="text-white fw-bold mb-0 text-shadow font-poppins property-price"><?= formatPrice($prop['price']) ?></h5>
                                </div>
                            </div>
                            <div class="card-body p-3 bg-white">
                                <span class="badge bg-sarvam-ice text-sarvam uppercase font-poppins small mb-1" style="font-size: 0.7rem;"><?= htmlspecialchars($prop['type_name']) ?></span>
                                <h6 class="card-title fw-bold font-poppins mb-1 text-truncate-2">
                                    <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $prop['id'] ?>" class="text-decoration-none text-heading hover-primary"><?= htmlspecialchars($prop['title']) ?></a>
                                </h6>
                                <p class="text-muted small mb-2 text-truncate" style="font-size: 0.75rem;"><i class="bi bi-geo-alt me-1 text-sarvam"></i> <?= htmlspecialchars($prop['address'] ?? $prop['city']) ?></p>
                                
                                <div class="d-flex justify-content-between border-top pt-2 text-muted font-poppins small" style="font-size: 0.75rem; border-color: #C4DCDF !important;">
                                    <span><?= $prop['bedrooms'] ?> BHK</span>
                                    <span><?= formatArea($prop['area_sqft']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Popular Cities -->
<section class="py-5 section-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge-sarvam px-3 py-2 rounded-pill uppercase tracking-wider small fw-semibold">Locations</span>
            <h2 class="h1 fw-bold font-poppins mt-2 text-heading section-title d-inline-block">Popular Cities</h2>
            <p class="text-muted max-w-xl mx-auto mt-3">Explore premium real estate listings categorized by India's most prominent real estate hubs.</p>
        </div>

        <div class="row g-4">
            <?php 
            $cities = [
                ['name' => 'Mumbai', 'state' => 'Maharashtra', 'img' => 'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?w=600&q=80'],
                ['name' => 'Delhi', 'state' => 'NCR', 'img' => 'https://images.unsplash.com/photo-1587474260584-136574528ed5?w=600&q=80'],
                ['name' => 'Bangalore', 'state' => 'Karnataka', 'img' => 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?w=600&q=80'],
                ['name' => 'Pune', 'state' => 'Maharashtra', 'img' => 'https://images.unsplash.com/photo-1601999109332-542b18dbec57?w=600&q=80'],
                ['name' => 'Chennai', 'state' => 'Tamil Nadu', 'img' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?w=600&q=80'],
                ['name' => 'Hyderabad', 'state' => 'Telangana', 'img' => 'https://images.unsplash.com/photo-1605007493699-af65834f8a00?w=600&q=80'],
                ['name' => 'Ahmedabad', 'state' => 'Gujarat', 'img' => 'https://images.unsplash.com/photo-1627993077759-e93246ebec58?w=600&q=80'],
                ['name' => 'Kolkata', 'state' => 'West Bengal', 'img' => 'https://images.unsplash.com/photo-1558431382-27e303142255?w=600&q=80']
            ];
            foreach ($cities as $city):
                $c_count = $city_counts[$city['name']] ?? 0;
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="<?= SITE_URL ?>/properties.php?search=<?= urlencode($city['name']) ?>" class="text-decoration-none">
                        <div class="card bg-dark text-white text-center border-sarvam city-card rounded-sarvam overflow-hidden position-relative">
                            <img src="<?= $city['img'] ?>" class="card-img w-100 h-100" alt="<?= $city['name'] ?>">
                            <div class="city-overlay"></div>
                            <div class="card-img-overlay d-flex flex-column justify-content-center z-2 city-info">
                                <h4 class="card-title fw-bold mb-1 font-poppins text-shadow text-white"><?= $city['name'] ?></h4>
                                <span class="small text-white-50 text-uppercase tracking-wider font-poppins mb-2"><?= $city['state'] ?></span>
                                <span class="badge bg-sarvam-ice text-sarvam align-self-center px-3 py-1 rounded-pill small"><?= $c_count ?> Properties</span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5 section-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge-sarvam px-3 py-2 rounded-pill uppercase tracking-wider small fw-semibold">Our Values</span>
            <h2 class="h1 fw-bold font-poppins mt-2 text-heading section-title d-inline-block">Why Choose Sarvam Real Estate</h2>
            <p class="text-muted max-w-xl mx-auto mt-3">We provide premium real estate services tailored to fit your specific expectations and requirements.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6 text-center">
                <div class="icon-box bg-sarvam-ice text-sarvam rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 80px; height: 80px;">
                    <i class="bi bi-shield-check fs-1"></i>
                </div>
                <h4 class="fw-bold font-poppins mb-3 text-heading">100% Verified properties</h4>
                <p class="text-muted small">Every property listed on our site undergoes strict quality verification checks by our certified experts.</p>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div class="icon-box bg-sarvam-ice text-sarvam rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 80px; height: 80px;">
                    <i class="bi bi-person-check fs-1"></i>
                </div>
                <h4 class="fw-bold font-poppins mb-3 text-heading">Expert Agents</h4>
                <p class="text-muted small">Access verified local experts and professional agents to guide you seamlessly through the entire property transaction.</p>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div class="icon-box bg-sarvam-ice text-sarvam rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 80px; height: 80px;">
                    <i class="bi bi-cash-coin fs-1"></i>
                </div>
                <h4 class="fw-bold font-poppins mb-3 text-heading">Best Deals</h4>
                <p class="text-muted small">We strive to secure the most competitive prices, premium discounts, and market deals for your properties.</p>
            </div>
            <div class="col-lg-3 col-md-6 text-center">
                <div class="icon-box bg-sarvam-ice text-sarvam rounded-circle d-inline-flex align-items-center justify-content-center mb-4 shadow-sm" style="width: 80px; height: 80px;">
                    <i class="bi bi-lightning-charge fs-1"></i>
                </div>
                <h4 class="fw-bold font-poppins mb-3 text-heading">Easy Documentation</h4>
                <p class="text-muted small">Say goodbye to stressful legal paperworks. Our dedicated team manages the entire legal documentations process.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Agents -->
<section class="py-5 section-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge-sarvam px-3 py-2 rounded-pill uppercase tracking-wider small fw-semibold">Agents</span>
            <h2 class="h1 fw-bold font-poppins mt-2 text-heading section-title d-inline-block">Meet Our Certified Agents</h2>
            <p class="text-muted max-w-xl mx-auto mt-3">Get professional support from our highest-rated agents to assist you in making informed decisions.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php if (empty($featured_agents)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No agents available.</p>
                </div>
            <?php else: ?>
                <?php foreach ($featured_agents as $agent): ?>
                    <div class="col-lg-3 col-md-6">
                        <div class="agent-card">
                            <img src="<?= $agent['photo'] ? SITE_URL.'/assets/uploads/agents/'.$agent['photo'] : 'https://placehold.co/120x120/FF893B/fff?text='.urlencode(strtoupper(substr($agent['name'],0,1))) ?>" 
                                 class="agent-photo" alt="<?= htmlspecialchars($agent['name']) ?>">
                            <h5 class="fw-bold font-poppins text-heading mb-1"><?= htmlspecialchars($agent['name']) ?></h5>
                            <p class="text-sarvam small mb-3 fw-medium"><?= $agent['experience_years'] ?> Years Experience</p>
                            
                            <div class="d-flex justify-content-center gap-1 text-warning mb-3">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= round($agent['rating']) ? '-fill' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <span class="badge-success px-3 py-1.5 rounded-pill font-poppins small"><?= $agent['properties_sold'] ?> Properties Sold</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 section-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="badge-sarvam px-3 py-2 rounded-pill uppercase tracking-wider small fw-semibold">Reviews</span>
            <h2 class="h1 fw-bold font-poppins mt-2 text-heading section-title d-inline-block">What Our Clients Say</h2>
            <p class="text-muted max-w-xl mx-auto mt-3">Read real feedback from home-buyers, sellers, and tenants who found success with Sarvam Real Estate.</p>
        </div>

        <div class="row g-4">
            <?php if (empty($testimonials)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No testimonials available.</p>
                </div>
            <?php else: ?>
                <?php foreach ($testimonials as $t): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="testimonial-card">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="<?= $t['user_photo'] ? SITE_URL.'/assets/uploads/testimonials/'.$t['user_photo'] : 'https://placehold.co/50x50/FF893B/fff?text='.urlencode(strtoupper(substr($t['user_name'],0,1))) ?>" 
                                     class="rounded-circle shadow-sm" alt="<?= htmlspecialchars($t['user_name']) ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                <div>
                                    <h6 class="fw-bold font-poppins mb-0 text-heading"><?= htmlspecialchars($t['user_name']) ?></h6>
                                    <span class="text-muted small"><?= htmlspecialchars($t['property_type']) ?> Owner</span>
                                </div>
                            </div>
                            
                            <div class="d-flex text-warning mb-3">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= $t['rating'] ? '-fill' : '' ?> small"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <p class="text-muted small mb-0 font-poppins italic">
                                "<?= htmlspecialchars($t['review']) ?>"
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-5 section-dark text-white position-relative overflow-hidden">
    <div class="container py-4 position-relative z-2 text-center">
        <h2 class="display-5 fw-bold font-poppins mb-3">Are You Looking to Sell or Rent Your Property?</h2>
        <p class="lead text-white-50 max-w-2xl mx-auto mb-4">Connect with our certified local agents to list your residential or commercial spaces with India's fastest growing property portal.</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-sarvam px-4 py-2.5 fw-semibold font-poppins"><i class="bi bi-envelope me-2"></i>Contact Us Today</a>
            <a href="<?= SITE_URL ?>/about.php" class="btn btn-outline-sarvam px-4 py-2.5 fw-semibold font-poppins" style="color:white; border-color:white;">Learn More About Us</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
