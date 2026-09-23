<?php
$current_page = basename($_SERVER['PHP_SELF']);

$wishlist_count = 0;
if (isLoggedIn()) {
    $user_id = (int)$_SESSION['user_id'];
    $wishlist_count = getWishlistCount($conn, $user_id);
}
?>
<nav class="navbar navbar-expand-lg sarvam-navbar sticky-top" id="mainNav">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= SITE_URL ?>">
      <div style="width:34px;height:34px;background:#FF893B;border-radius:8px;display:flex;align-items:center;justify-content:center;">
        <i class="bi bi-house-door-fill text-white" style="font-size:1rem;"></i>
      </div>
      <span class="fw-bold" style="color:#fff;font-family:'Poppins',sans-serif;font-size:1.1rem;letter-spacing:-0.3px;">Sarvam <span style="color:#FF893B;">RE</span></span>
    </a>
    
    <!-- Mobile Toggle -->
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation" style="color:rgba(255,255,255,0.7);">
      <i class="bi bi-list fs-4"></i>
    </button>
    
    <!-- Navbar Content -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
        <li class="nav-item">
          <a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>">Home</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= in_array($current_page, ['properties.php', 'property-detail.php']) ? 'active' : '' ?>" href="#" id="propertiesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Properties
          </a>
          <ul class="dropdown-menu border-0 rounded-3" style="background:#2D343D;min-width:180px;" aria-labelledby="propertiesDropdown">
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php" style="color:rgba(255,255,255,0.75);font-size:0.9rem;">All Properties</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php?type=buy" style="color:rgba(255,255,255,0.75);font-size:0.9rem;">Buy Property</a></li>
            <li><a class="dropdown-item" href="<?= SITE_URL ?>/properties.php?type=rent" style="color:rgba(255,255,255,0.75);font-size:0.9rem;">Rent Property</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page == 'about.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/about.php">About</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $current_page == 'contact.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/contact.php">Contact</a>
        </li>
      </ul>
      
      <!-- Right side -->
      <div class="d-flex align-items-center gap-2 gap-lg-3">
        <!-- Search toggle -->
        <button class="btn p-0" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-expanded="false" aria-controls="searchCollapse" style="color:rgba(255,255,255,0.65);font-size:1.1rem;background:none;border:none;">
          <i class="bi bi-search"></i>
        </button>
        
        <?php if (isLoggedIn()): ?>
          <a href="<?= SITE_URL ?>/wishlist.php" class="position-relative" style="color:rgba(255,255,255,0.65);font-size:1.1rem;text-decoration:none;">
            <i class="bi bi-heart"></i>
            <?php if ($wishlist_count > 0): ?>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background:#FF893B;font-size:0.6rem;"><?= $wishlist_count ?></span>
            <?php endif; ?>
          </a>
          
          <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="color:rgba(255,255,255,0.75);">
              <?php if (!empty($_SESSION['profile_image']) && file_exists(UPLOAD_PATH . 'users/' . $_SESSION['profile_image'])): ?>
                <img src="<?= UPLOAD_URL ?>users/<?= $_SESSION['profile_image'] ?>" alt="Profile" width="30" height="30" class="rounded-circle" style="object-fit:cover;border:2px solid rgba(255,255,255,0.2);">
              <?php else: ?>
                <div style="width:30px;height:30px;background:#FF893B;border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.8rem;"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></div>
              <?php endif; ?>
              <span class="d-none d-lg-inline" style="font-size:0.875rem;"><?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'User')[0]) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end border-0 rounded-3 shadow-lg" style="min-width:180px;" aria-labelledby="profileDropdown">
              <li><h6 class="dropdown-header" style="color:#729CA2;font-size:0.7rem;">Hello, <?= sanitize($_SESSION['user_name'] ?? 'User') ?></h6></li>
              <li><a class="dropdown-item" href="<?= SITE_URL ?>/dashboard.php"><i class="bi bi-grid me-2" style="color:#729CA2;"></i>Dashboard</a></li>
              <li><a class="dropdown-item" href="<?= SITE_URL ?>/profile.php"><i class="bi bi-person me-2" style="color:#729CA2;"></i>Profile</a></li>
              <li><a class="dropdown-item" href="<?= SITE_URL ?>/my-inquiries.php"><i class="bi bi-envelope me-2" style="color:#729CA2;"></i>My Inquiries</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/login.php" class="btn btn-sm btn-outline-sarvam">Login</a>
          <a href="<?= SITE_URL ?>/register.php" class="btn btn-sm btn-sarvam">Register</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Search Bar -->
<div class="collapse search-bar-collapse" id="searchCollapse">
  <div class="container py-3">
    <form action="<?= SITE_URL ?>/properties.php" method="GET" class="d-flex gap-2">
      <input type="text" name="search" class="form-control" placeholder="Search by title, city, or property type..." style="border-color:#C4DCDF;border-radius:8px;" required>
      <button class="btn btn-sarvam px-4" type="submit"><i class="bi bi-search me-1"></i>Search</button>
    </form>
  </div>
</div>
