<?php
/**
 * Sarvam Real Estate - About Us Page
 */
require_once __DIR__ . '/config/db.php';

$page_title = "About Us";
$page_description = "Learn more about Sarvam Real Estate - India's most trusted partner in finding verified properties, apartments, villas, and lands.";

$agents = getAgents($conn, 4);

require_once __DIR__ . '/includes/header.php';
?>
<!-- Page Hero -->
<section class="page-hero">
  <div class="container">
    <h1 class="fw-bold text-white mb-2">About Sarvam Real Estate</h1>
    <p style="color:rgba(255,255,255,0.7);">India's trusted partner for premium property solutions</p>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:rgba(255,255,255,0.4);">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-sarvam">Home</a></li>
        <li class="breadcrumb-item active text-white">About Us</li>
      </ol>
    </nav>
  </div>
</section>

<!-- Mission/Vision section in section-white -->
<div class="section-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="rounded-sarvam overflow-hidden shadow-sarvam">
                    <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80" class="img-fluid w-100" alt="Sarvam Office" style="max-height: 400px; object-fit: cover;">
                </div>
            </div>
            <div class="col-lg-6 font-poppins">
                <h2 class="fw-bold text-heading mb-4 section-title">India's Leading Property Advisors</h2>
                <p class="text-muted leading-relaxed mb-3">
                    Founded in 2008, Sarvam Real Estate was born out of a simple vision: to make the process of buying, selling, and renting properties in India transparent, efficient, and completely stress-free. Over the years, we have helped thousands of families find their perfect home.
                </p>
                <p class="text-muted leading-relaxed mb-4">
                    We believe that a home is more than just four walls; it's a foundation for memories, growth, and security. That's why we verify every single property, match you with certified local experts, and handle all documentation so you can take key decisions with confidence.
                </p>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="card-sarvam p-3 h-100 text-center">
                            <i class="bi bi-bullseye text-sarvam fs-2 mb-2 d-block"></i>
                            <h5 class="fw-bold mb-2 text-heading">Our Mission</h5>
                            <p class="text-muted small mb-0">To deliver outstanding real estate consultancy services, exceeding clients' expectations through transparency and customer care.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card-sarvam p-3 h-100 text-center">
                            <i class="bi bi-eye text-sarvam fs-2 mb-2 d-block"></i>
                            <h5 class="fw-bold mb-2 text-heading">Our Vision</h5>
                            <p class="text-muted small mb-0">To build a trusted ecosystem for real estate in India, matching home buyers and properties with total reliability.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Banner in section-dark -->
<section class="section-dark">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6 hero-stat-item">
                <h3 class="display-5 fw-bold font-poppins stat-card-value" style="color:#FF893B;">5000+</h3>
                <p class="small uppercase tracking-wider mb-0 text-white">Total Properties</p>
            </div>
            <div class="col-md-3 col-6 hero-stat-item">
                <h3 class="display-5 fw-bold font-poppins stat-card-value" style="color:#FF893B;">2000+</h3>
                <p class="small uppercase tracking-wider mb-0 text-white">Happy Clients</p>
            </div>
            <div class="col-md-3 col-6 hero-stat-item">
                <h3 class="display-5 fw-bold font-poppins stat-card-value" style="color:#FF893B;">500+</h3>
                <p class="small uppercase tracking-wider mb-0 text-white">Verified Agents</p>
            </div>
            <div class="col-md-3 col-6 hero-stat-item">
                <h3 class="display-5 fw-bold font-poppins stat-card-value" style="color:#FF893B;">15+</h3>
                <p class="small uppercase tracking-wider mb-0 text-white">Years of Experience</p>
            </div>
        </div>
    </div>
</section>

<!-- Meet the Agents in section-light -->
<section class="section-light">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge-sarvam px-3 py-2 rounded-pill uppercase tracking-wider small fw-semibold">Our Experts</span>
            <h2 class="h1 fw-bold font-poppins mt-2 text-heading section-title d-inline-block">Certified Local Consultants</h2>
            <p class="text-muted max-w-xl mx-auto mt-3">Get connected with our verified, experienced team members who are ready to guide you step-by-step.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php if (empty($agents)): ?>
                <div class="col-12 text-center text-muted py-4">No active agents found.</div>
            <?php else: ?>
                <?php foreach ($agents as $agent): ?>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="agent-card">
                            <img src="<?= $agent['photo'] ? SITE_URL.'/assets/uploads/agents/'.$agent['photo'] : 'https://placehold.co/120x120/FF893B/fff?text='.urlencode(strtoupper(substr($agent['name'],0,1))) ?>" 
                                 class="agent-photo" alt="<?= htmlspecialchars($agent['name']) ?>">
                            <h5 class="fw-bold font-poppins text-heading mb-1"><?= htmlspecialchars($agent['name']) ?></h5>
                            <p class="text-sarvam small mb-3 fw-medium"><?= $agent['experience_years'] ?> Years Experience</p>
                            
                            <div class="d-flex justify-content-center gap-1 text-warning mb-3">
                                <?php for($i=1; $i<=5; $i++): ?>
                                    <i class="bi bi-star<?= $i <= round($agent['rating']) ? '-fill' : '' ?> small"></i>
                                <?php endfor; ?>
                            </div>
                            
                            <span class="badge-success px-3 py-1 rounded-pill font-poppins small"><?= $agent['properties_sold'] ?> Properties Sold</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="section-dark text-center">
    <div class="container">
        <h3 class="fw-bold font-poppins mb-3 text-white">Have any questions about real estate?</h3>
        <p class="small max-w-md mx-auto mb-4" style="color:rgba(255,255,255,0.7);">Our support team and agents are available 24/7 to resolve your inquiries and schedule property visits.</p>
        <a href="<?= SITE_URL ?>/contact.php" class="btn btn-sarvam px-4 py-2.5 rounded-3 fw-semibold font-poppins"><i class="bi bi-envelope me-2"></i>Contact Us Today</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
