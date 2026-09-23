<?php
/**
 * Admin Sidebar Navigation
 * $current_page must be set before including this file
 */
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? 'admin@sarvam.com';
$admin_initial = strtoupper(substr($admin_name, 0, 1));

// Count pending inquiries for badge
$inq_result = $conn->query("SELECT COUNT(*) as c FROM inquiries WHERE status='pending'");
$pending_inquiries = $inq_result ? (int)$inq_result->fetchColumn() : 0;
?>
<!-- Admin Sidebar -->
<nav class="admin-sidebar" id="adminSidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon" style="background:#FF893B;"><i class="bi bi-houses-fill"></i></div>
        <div>
            <div class="sidebar-logo-text">Sarvam</div>
            <div class="sidebar-logo-sub">Admin Panel</div>
        </div>
    </div>

    <!-- Navigation -->
    <div class="sidebar-nav">
        <!-- Dashboard -->
        <a href="<?= SITE_URL ?>/admin/index.php" class="sidebar-link <?= ($current_page=='dashboard') ? 'active' : '' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>

        <!-- Properties -->
        <div class="sidebar-section-title">Properties</div>
        <a href="<?= SITE_URL ?>/admin/properties.php" class="sidebar-link <?= ($current_page=='properties') ? 'active' : '' ?>">
            <i class="bi bi-building"></i> All Properties
        </a>
        <a href="<?= SITE_URL ?>/admin/property-add.php" class="sidebar-link <?= ($current_page=='property-add') ? 'active' : '' ?>">
            <i class="bi bi-plus-circle"></i> Add Property
        </a>
        <a href="<?= SITE_URL ?>/admin/property-types.php" class="sidebar-link <?= ($current_page=='property-types') ? 'active' : '' ?>">
            <i class="bi bi-tag"></i> Property Types
        </a>
        <a href="<?= SITE_URL ?>/admin/locations.php" class="sidebar-link <?= ($current_page=='locations') ? 'active' : '' ?>">
            <i class="bi bi-geo-alt"></i> Locations
        </a>

        <!-- People -->
        <div class="sidebar-section-title">People</div>
        <a href="<?= SITE_URL ?>/admin/users.php" class="sidebar-link <?= ($current_page=='users') ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Users
        </a>
        <a href="<?= SITE_URL ?>/admin/agents.php" class="sidebar-link <?= ($current_page=='agents') ? 'active' : '' ?>">
            <i class="bi bi-person-badge"></i> Agents
        </a>

        <!-- Content -->
        <div class="sidebar-section-title">Content</div>
        <a href="<?= SITE_URL ?>/admin/inquiries.php" class="sidebar-link <?= ($current_page=='inquiries') ? 'active' : '' ?>">
            <i class="bi bi-envelope"></i> Inquiries
            <?php if ($pending_inquiries > 0): ?>
                <span class="badge-pill"><?= $pending_inquiries ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/admin/testimonials.php" class="sidebar-link <?= ($current_page=='testimonials') ? 'active' : '' ?>">
            <i class="bi bi-chat-quote"></i> Testimonials
        </a>
        <a href="<?= SITE_URL ?>/admin/reports.php" class="sidebar-link <?= ($current_page=='reports') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart"></i> Reports
        </a>

        <!-- Site -->
        <div class="sidebar-section-title">Site</div>
        <a href="<?= SITE_URL ?>/" target="_blank" class="sidebar-link">
            <i class="bi bi-box-arrow-up-right"></i> View Website
        </a>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="sidebar-link" style="color: rgba(239,68,68,0.8);">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-admin-avatar"><?= $admin_initial ?></div>
        <div>
            <div class="sidebar-admin-name"><?= htmlspecialchars($admin_name) ?></div>
            <div class="sidebar-admin-role">Administrator</div>
        </div>
    </div>
</nav>

<!-- Main Content Area -->
<div class="admin-main">
    <!-- Top Header -->
    <header class="admin-header">
        <div class="admin-header-left">
            <button class="sidebar-toggle-btn" id="sidebarToggle" onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title"><?= htmlspecialchars($page_title ?? 'Dashboard') ?></h1>
        </div>
        <div class="admin-header-right">
            <a href="<?= SITE_URL ?>/admin/inquiries.php" class="header-btn" title="Inquiries">
                <i class="bi bi-envelope"></i>
                <?php if ($pending_inquiries > 0): ?>
                    <span class="notif-dot"></span>
                <?php endif; ?>
            </a>
            <div class="header-admin-info">
                <div class="header-admin-avatar"><?= $admin_initial ?></div>
                <div class="header-admin-text">
                    <div class="name"><?= htmlspecialchars($admin_name) ?></div>
                    <div class="role">Administrator</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <div class="admin-content">
