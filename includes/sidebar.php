<?php
/**
 * Sarvam Real Estate - User Dashboard Sidebar Component
 */
$sidebar_current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="user-sidebar-nav bg-white border-sarvam rounded-sarvam p-3 shadow-sarvam">
    <div class="list-group list-group-flush small">
        <a href="<?= SITE_URL ?>/dashboard.php" 
           class="list-group-item list-group-item-action py-3 px-3 border-0 rounded <?= $sidebar_current_page === 'dashboard.php' ? 'active text-white bg-sarvam-teal fw-bold' : 'text-heading' ?>">
            <i class="bi bi-speedometer2 me-2 <?= $sidebar_current_page === 'dashboard.php' ? 'text-white' : 'text-sarvam' ?>"></i> Dashboard
        </a>
        <a href="<?= SITE_URL ?>/profile.php" 
           class="list-group-item list-group-item-action py-3 px-3 border-light border-0 rounded mt-1 <?= $sidebar_current_page === 'profile.php' ? 'active text-white bg-sarvam-teal fw-bold' : 'text-heading' ?>">
            <i class="bi bi-person me-2 <?= $sidebar_current_page === 'profile.php' ? 'text-white' : 'text-sarvam' ?>"></i> My Profile
        </a>
        <a href="<?= SITE_URL ?>/wishlist.php" 
           class="list-group-item list-group-item-action py-3 px-3 border-light border-0 rounded mt-1 <?= $sidebar_current_page === 'wishlist.php' ? 'active text-white bg-sarvam-teal fw-bold' : 'text-heading' ?>">
            <i class="bi bi-heart me-2 <?= $sidebar_current_page === 'wishlist.php' ? 'text-white' : 'text-sarvam' ?>"></i> My Wishlist
        </a>
        <a href="<?= SITE_URL ?>/my-inquiries.php" 
           class="list-group-item list-group-item-action py-3 px-3 border-light border-0 rounded mt-1 <?= $sidebar_current_page === 'my-inquiries.php' ? 'active text-white bg-sarvam-teal fw-bold' : 'text-heading' ?>">
            <i class="bi bi-envelope me-2 <?= $sidebar_current_page === 'my-inquiries.php' ? 'text-white' : 'text-sarvam' ?>"></i> My Inquiries
        </a>
        <a href="<?= SITE_URL ?>/properties.php" 
           class="list-group-item list-group-item-action py-3 px-3 border-light border-0 rounded mt-1 text-heading">
            <i class="bi bi-search me-2 text-sarvam"></i> Browse Properties
        </a>
    </div>
</div>
