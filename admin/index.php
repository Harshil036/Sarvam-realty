<?php
/**
 * ============================================================
 *  FEATURE: ADMIN DASHBOARD
 *  File   : admin/index.php
 *  Role   : Main landing page after admin login. Shows site-wide
 *           summary statistics, recent inquiries, latest property
 *           listings, and a monthly inquiry trend chart.
 *
 *  Statistics displayed:
 *   • Total properties (active count)
 *   • Total registered users
 *   • Total inquiries received
 *   • Pending (unanswered) inquiries
 *
 *  Data panels:
 *   • Latest 5 inquiries with user name and property title
 *   • Latest 6 properties added
 *   • Monthly inquiry counts for the last 6 months (Chart.js bar chart)
 *
 *  Access control:
 *   • requireAdminLogin() → non-admins redirected to /login.php
 * ============================================================
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$current_page = 'dashboard';
$page_title   = 'Dashboard';

// --- Stat Counts ---
$total_properties  = (int)$conn->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$total_users       = (int)$conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_agents      = (int)$conn->query("SELECT COUNT(*) FROM agents WHERE status='active'")->fetchColumn();
$total_inquiries   = (int)$conn->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$featured_props    = (int)$conn->query("SELECT COUNT(*) FROM properties WHERE is_featured=1")->fetchColumn();
$premium_props     = (int)$conn->query("SELECT COUNT(*) FROM properties WHERE is_premium=1")->fetchColumn();
$for_sale          = (int)$conn->query("SELECT COUNT(*) FROM properties WHERE listing_type='buy'")->fetchColumn();
$for_rent          = (int)$conn->query("SELECT COUNT(*) FROM properties WHERE listing_type='rent'")->fetchColumn();
$pending_inq       = (int)$conn->query("SELECT COUNT(*) FROM inquiries WHERE status='pending'")->fetchColumn();

// --- Recent Inquiries (10) ---
$res = $conn->query("SELECT i.*, p.title as property_title, p.id as prop_id
    FROM inquiries i
    LEFT JOIN properties p ON i.property_id = p.id
    ORDER BY i.created_at DESC LIMIT 10");
$recent_inquiries = $res ? $res->fetchAll() : [];

// --- Recent Properties (5) ---
$res2 = $conn->query("SELECT p.*, pt.type_name, l.city
    FROM properties p
    JOIN property_types pt ON p.property_type_id = pt.id
    JOIN locations l ON p.location_id = l.id
    ORDER BY p.created_at DESC LIMIT 5");
$recent_properties = $res2 ? $res2->fetchAll() : [];

// --- Monthly Inquiries (last 6 months for chart) ---
$monthly_data = [];
$month_stmt = $conn->prepare("SELECT COUNT(*) FROM inquiries WHERE DATE_FORMAT(created_at,'%Y-%m') = ?");
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $month_stmt->execute([$month]);
    $count = (int)$month_stmt->fetchColumn();
    $monthly_data[] = ['label' => $label, 'count' => $count];
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Breadcrumb -->
<div class="admin-breadcrumb">
    <i class="bi bi-house-fill" style="color:#729CA2;"></i>
    <span class="separator">/</span>
    <span>Dashboard</span>
</div>

<!-- Welcome Banner -->
<div class="admin-card mb-4" style="background:linear-gradient(135deg,#2D343D,#465461);border:none;">
    <div class="admin-card-body d-flex justify-content-between align-items-center flex-wrap gap-3" style="padding:1.75rem;">
        <div>
            <h2 style="color:#fff;font-size:1.4rem;margin-bottom:4px;">
                Welcome back, <?= htmlspecialchars($_SESSION['admin_name']) ?>! 👋
            </h2>
            <p style="color:rgba(255,255,255,0.75);margin:0;font-size:0.875rem;">
                Here's what's happening with your real estate platform today.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= SITE_URL ?>/admin/property-add.php" class="btn-admin-primary">
                <i class="bi bi-plus-circle"></i> Add Property
            </a>
            <a href="<?= SITE_URL ?>/admin/inquiries.php" class="btn-admin-outline"
               style="background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.3);">
                <i class="bi bi-envelope"></i> View Inquiries
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards Row 1 -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card">
            <div class="stat-icon-box teal"><i class="bi bi-building"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($total_properties) ?></div>
                <div class="stat-label">Total Properties</div>
                <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> <?= $for_sale ?> for sale</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card">
            <div class="stat-icon-box green"><i class="bi bi-people"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($total_users) ?></div>
                <div class="stat-label">Registered Users</div>
                <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> Active members</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card">
            <div class="stat-icon-box orange"><i class="bi bi-person-badge"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($total_agents) ?></div>
                <div class="stat-label">Active Agents</div>
                <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> Verified agents</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card">
            <div class="stat-icon-box red"><i class="bi bi-envelope-open"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($total_inquiries) ?></div>
                <div class="stat-label">Total Inquiries</div>
                <?php if ($pending_inq > 0): ?>
                    <div class="stat-trend down"><i class="bi bi-clock"></i> <?= $pending_inq ?> pending</div>
                <?php else: ?>
                    <div class="stat-trend up"><i class="bi bi-check-circle"></i> All handled</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards Row 2 -->
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card">
            <div class="stat-icon-box teal"><i class="bi bi-star"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $featured_props ?></div>
                <div class="stat-label">Featured Properties</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card">
            <div class="stat-icon-box orange"><i class="bi bi-gem"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $premium_props ?></div>
                <div class="stat-label">Premium Properties</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card">
            <div class="stat-icon-box teal"><i class="bi bi-cart-check"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $for_sale ?></div>
                <div class="stat-label">Properties For Sale</div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="admin-stat-card">
            <div class="stat-icon-box green"><i class="bi bi-house-door"></i></div>
            <div class="stat-info">
                <div class="stat-value"><?= $for_rent ?></div>
                <div class="stat-label">Properties For Rent</div>
            </div>
        </div>
    </div>
</div>

<!-- Charts + Recent Properties -->
<div class="row g-4 mb-4">
    <!-- Monthly Inquiry Chart -->
    <div class="col-xl-8">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-bar-chart-line"></i> Monthly Inquiries (Last 6 Months)</h5>
                <a href="<?= SITE_URL ?>/admin/reports.php" class="btn-admin-outline btn-admin-sm">Full Report</a>
            </div>
            <div class="admin-card-body">
                <div class="chart-container" style="height:240px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-xl-4">
        <div class="admin-card h-100">
            <div class="admin-card-header">
                <h5 class="admin-card-title"><i class="bi bi-pie-chart"></i> Property Overview</h5>
            </div>
            <div class="admin-card-body">
                <canvas id="propertyPieChart" style="max-height:200px;"></canvas>
                <div class="mt-3 d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:0.8rem;display:flex;align-items:center;gap:6px;">
                            <span style="width:10px;height:10px;background:#2D343D;border-radius:3px;display:inline-block;"></span>
                            For Sale
                        </span>
                        <strong style="font-size:0.85rem;"><?= $for_sale ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:0.8rem;display:flex;align-items:center;gap:6px;">
                            <span style="width:10px;height:10px;background:#729CA2;border-radius:3px;display:inline-block;"></span>
                            For Rent
                        </span>
                        <strong style="font-size:0.85rem;"><?= $for_rent ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:0.8rem;display:flex;align-items:center;gap:6px;">
                            <span style="width:10px;height:10px;background:#FF893B;border-radius:3px;display:inline-block;"></span>
                            Featured
                        </span>
                        <strong style="font-size:0.85rem;"><?= $featured_props ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-size:0.8rem;display:flex;align-items:center;gap:6px;">
                            <span style="width:10px;height:10px;background:#E9782C;border-radius:3px;display:inline-block;"></span>
                            Premium
                        </span>
                        <strong style="font-size:0.85rem;"><?= $premium_props ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Inquiries -->
<div class="admin-card mb-4">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="bi bi-envelope"></i> Recent Inquiries</h5>
        <a href="<?= SITE_URL ?>/admin/inquiries.php" class="btn-admin-outline btn-admin-sm">View All</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Property</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_inquiries)): ?>
                    <tr><td colspan="8" class="text-center py-4" style="color:#729CA2;">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>No inquiries yet
                    </td></tr>
                <?php else: ?>
                    <?php foreach ($recent_inquiries as $inq): ?>
                        <tr>
                            <td><small style="color:#729CA2;">#<?= $inq['id'] ?></small></td>
                            <td>
                                <div style="font-weight:600;"><?= htmlspecialchars($inq['name']) ?></div>
                                <small style="color:#729CA2;"><?= htmlspecialchars($inq['phone'] ?? '') ?></small>
                            </td>
                            <td style="font-size:0.8rem;"><?= htmlspecialchars($inq['email']) ?></td>
                            <td>
                                <?php if ($inq['property_title']): ?>
                                    <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $inq['prop_id'] ?>" target="_blank"
                                       style="font-size:0.8rem;color:#729CA2;">
                                        <?= htmlspecialchars(substr($inq['property_title'], 0, 30)) ?>...
                                    </a>
                                <?php else: ?>
                                    <span style="color:#729CA2;font-size:0.8rem;">General</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.8rem;max-width:200px;">
                                <?= htmlspecialchars(substr($inq['message'], 0, 60)) ?>...
                            </td>
                            <td>
                                <?php
                                $sc = ['pending'=>'pending','responded'=>'responded','closed'=>'sold'];
                                $badge = $sc[$inq['status']] ?? 'pending';
                                ?>
                                <span class="status-badge <?= $badge ?>">
                                    <?= ucfirst($inq['status']) ?>
                                </span>
                            </td>
                            <td style="font-size:0.8rem;white-space:nowrap;">
                                <?= date('d M Y', strtotime($inq['created_at'])) ?>
                            </td>
                            <td>
                                <a href="<?= SITE_URL ?>/admin/inquiries.php?view=<?= $inq['id'] ?>" class="btn-admin-outline btn-admin-sm" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recently Added Properties -->
<div class="admin-card">
    <div class="admin-card-header">
        <h5 class="admin-card-title"><i class="bi bi-building-add"></i> Recently Added Properties</h5>
        <a href="<?= SITE_URL ?>/admin/properties.php" class="btn-admin-outline btn-admin-sm">View All</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Property</th>
                    <th>Type</th>
                    <th>City</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_properties as $prop): ?>
                    <tr>
                        <td><small style="color:#729CA2;"><?= generatePropertyId($prop['id']) ?></small></td>
                        <td>
                            <img src="<?= $prop['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$prop['main_image'] : 'https://placehold.co/80x60/2D343D/fff?text=No+Img' ?>"
                                 class="table-img" alt="Property" style="width:80px;height:60px;object-fit:cover;border-radius:4px;">
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:0.875rem;"><?= htmlspecialchars(substr($prop['title'],0,35)) ?>...</div>
                            <small style="color:#729CA2;"><?= ucfirst($prop['listing_type']) ?></small>
                        </td>
                        <td><span class="badge-sarvam"><?= htmlspecialchars($prop['type_name']) ?></span></td>
                        <td style="font-size:0.8rem;"><?= htmlspecialchars($prop['city']) ?></td>
                        <td style="font-weight:700;color:#2D343D;"><?= formatPrice($prop['price']) ?></td>
                        <td>
                            <?php $s = $prop['status']=='available' ? 'available' : 'sold'; ?>
                            <span class="status-badge <?= $s ?>"><?= ucfirst($prop['status']) ?></span>
                        </td>
                        <td style="font-size:0.8rem;"><?= date('d M Y', strtotime($prop['created_at'])) ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?= SITE_URL ?>/admin/property-edit.php?id=<?= $prop['id'] ?>" class="btn-admin-outline btn-admin-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $prop['id'] ?>" target="_blank" class="btn-admin-outline btn-admin-sm" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart Script -->
<script>
// Monthly Inquiries Bar Chart
(function() {
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const labels = <?= json_encode(array_column($monthly_data, 'label')) ?>;
    const data   = <?= json_encode(array_column($monthly_data, 'count')) ?>;
    const maxVal = Math.max(...data, 1);
    const canvas = document.getElementById('monthlyChart');
    canvas.width  = canvas.parentElement.offsetWidth;
    canvas.height = 240;
    const barW = (canvas.width - 80) / labels.length - 10;
    const barMaxH = 180;
    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const grad = ctx.createLinearGradient(0, 0, 0, barMaxH);
        grad.addColorStop(0, '#2563EB');
        grad.addColorStop(1, '#3B82F6');
        labels.forEach((lbl, i) => {
            const x = 40 + i * (barW + 10);
            const h = (data[i] / maxVal) * barMaxH;
            const y = 200 - h;
            // Bar
            ctx.fillStyle = grad;
            roundRect(ctx, x, y, barW, h, 6);
            ctx.fill();
            // Value
            ctx.fillStyle = '#111827';
            ctx.font = 'bold 12px Inter';
            ctx.textAlign = 'center';
            ctx.fillText(data[i], x + barW/2, y - 6);
            // Label
            ctx.fillStyle = '#6B7280';
            ctx.font = '10px Inter';
            ctx.fillText(lbl.split(' ')[0], x + barW/2, 218);
        });
    }
    function roundRect(ctx, x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.lineTo(x + w - r, y);
        ctx.quadraticCurveTo(x + w, y, x + w, y + r);
        ctx.lineTo(x + w, y + h);
        ctx.lineTo(x, y + h);
        ctx.lineTo(x, y + r);
        ctx.quadraticCurveTo(x, y, x + r, y);
        ctx.closePath();
    }
    draw();
    window.addEventListener('resize', draw);
})();

// Property Pie Chart
(function() {
    const canvas = document.getElementById('propertyPieChart');
    const ctx = canvas.getContext('2d');
    const data = [<?= $for_sale ?>, <?= $for_rent ?>, <?= $featured_props ?>, <?= $premium_props ?>];
    const colors = ['#2563EB','#22C55E','#F59E0B','#7C3AED'];
    const total = data.reduce((a, b) => a + b, 0) || 1;
    let startAngle = -Math.PI / 2;
    const cx = canvas.offsetWidth / 2 || 150, cy = 100, r = 80;
    canvas.width = cx * 2;
    canvas.height = 200;
    data.forEach((val, i) => {
        const slice = (val / total) * Math.PI * 2;
        ctx.beginPath();
        ctx.moveTo(cx, cy);
        ctx.arc(cx, cy, r, startAngle, startAngle + slice);
        ctx.closePath();
        ctx.fillStyle = colors[i];
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2;
        ctx.stroke();
        startAngle += slice;
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
