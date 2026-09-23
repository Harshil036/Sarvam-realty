<?php
/**
 * ============================================================
 *  FEATURE: MY INQUIRIES — PAGINATED HISTORY
 *  File   : my-inquiries.php
 *  Role   : Shows the logged-in user a full paginated list of
 *           every inquiry they have submitted, with the status
 *           (Pending / Responded / Closed) and a linked
 *           property title.
 *
 *  Pagination:
 *   • 10 inquiries per page (configurable via $per_page)
 *   • Page number from $_GET['page'], validated as positive int
 *   • LIMIT/OFFSET applied in the SQL query
 *
 *  Data source:
 *   • inquiries LEFT JOIN properties → ordered by created_at DESC
 *
 *  Access: requireLogin() → guests redirected to /login.php
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';
requireLogin();

$user_id = (int)$_SESSION['user_id'];

// Pagination
$per_page = 15;
$current_page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($current_page - 1) * $per_page;

// Fetch inquiries total count
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM inquiries WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_inquiries = (int)$count_stmt->fetchColumn();
$total_pages = ceil($total_inquiries / $per_page);

// Fetch inquiries list joined with property details
$stmt = $conn->prepare("SELECT i.*, p.title as property_title, p.price, p.main_image, p.id as prop_id 
          FROM inquiries i 
          LEFT JOIN properties p ON i.property_id = p.id 
          WHERE i.user_id = ? 
          ORDER BY i.created_at DESC 
          LIMIT $per_page OFFSET $offset");
$stmt->execute([$user_id]);
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<section class="page-hero">
  <div class="container">
    <h1 class="fw-bold text-white mb-2">My Inquiries</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:rgba(255,255,255,0.4);">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>" class="text-sarvam">Home</a></li>
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/dashboard.php" class="text-sarvam">Dashboard</a></li>
        <li class="breadcrumb-item active text-white">My Inquiries</li>
      </ol>
    </nav>
  </div>
</section>

<section class="section-light">
  <div class="container-fluid py-4">
    <div class="row g-4">
      <!-- Sidebar -->
      <div class="col-lg-3">
        <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
      </div>
      
      <!-- Content -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold font-poppins text-heading mb-0">Inquiry History</h2>
                <p class="text-muted small mb-0">You have sent <?= $total_inquiries ?> inquiries</p>
            </div>
            <a href="<?= SITE_URL ?>/properties.php" class="btn btn-outline-sarvam btn-sm"><i class="bi bi-search me-1"></i> Browse Properties</a>
        </div>

        <!-- Inquiries List Table -->
        <div class="card-sarvam p-4">
        <?php if (empty($inquiries)): ?>
            <div class="text-center py-5">
                <div class="fs-1 text-muted mb-3"><i class="bi bi-inbox text-sarvam"></i></div>
                <h4 class="fw-bold font-poppins text-heading mb-2">No inquiry history found</h4>
                <p class="text-muted small mb-0">Whenever you contact agents regarding properties, details appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive-sarvam">
                <table class="table-sarvam w-100">
                    <thead class="font-poppins small bg-sarvam-ice text-heading border-bottom">
                        <tr>
                            <th class="py-3 px-3 border-0">Property Info</th>
                            <th class="py-3 px-3 border-0">Inquiry Contact</th>
                            <th class="py-3 px-3 border-0">Inquiry Message</th>
                            <th class="py-3 px-3 border-0">Status</th>
                            <th class="py-3 px-3 border-0">Submitted Date</th>
                        </tr>
                    </thead>
                    <tbody class="font-poppins small text-muted">
                        <?php foreach ($inquiries as $inq): ?>
                            <tr class="border-bottom" style="border-color:#C4DCDF !important;">
                                <td class="py-3 px-3 border-0">
                                    <?php if ($inq['property_title']): ?>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= $inq['main_image'] ? SITE_URL.'/assets/uploads/properties/'.$inq['main_image'] : 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=100&q=80' ?>" 
                                                 class="rounded-2 shadow-sm" alt="Property thumbnail" style="width: 60px; height: 45px; object-fit: cover;">
                                            <div>
                                                <a href="<?= SITE_URL ?>/property-detail.php?id=<?= $inq['prop_id'] ?>" class="text-decoration-none fw-semibold text-heading d-block" style="transition: color 0.2s;" onmouseover="this.style.color='#729CA2'" onmouseout="this.style.color=''"> 
                                                    <?= htmlspecialchars(substr($inq['property_title'], 0, 35)) ?>...
                                                </a>
                                                <span class="text-sarvam fw-medium" style="font-size: 0.8rem;"><?= formatPrice($inq['price']) ?></span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fw-semibold">General Contact Form</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 border-0">
                                    <div class="fw-semibold text-heading"><?= htmlspecialchars($inq['name']) ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-telephone text-sarvam me-1"></i><?= htmlspecialchars($inq['phone']) ?></div>
                                </td>
                                <td class="py-3 px-3 border-0" style="max-width: 300px;" title="<?= htmlspecialchars($inq['message']) ?>">
                                    <?= nl2br(htmlspecialchars(substr($inq['message'], 0, 80))) ?><?= strlen($inq['message']) > 80 ? '...' : '' ?>
                                </td>
                                <td class="py-3 px-3 border-0">
                                    <?php if ($inq['status'] === 'responded'): ?>
                                        <span class="badge-success status-badge" style="background:rgba(34,197,94,0.12);color:#22C55E;border:1px solid rgba(34,197,94,0.2);border-radius:50px;padding:0.2rem 0.7rem;font-size:0.75rem;">Responded</span>
                                    <?php elseif ($inq['status'] === 'closed'): ?>
                                        <span class="badge-teal status-badge" style="background:rgba(114,156,162,0.12);color:#729CA2;border:1px solid rgba(114,156,162,0.2);border-radius:50px;padding:0.2rem 0.7rem;font-size:0.75rem;">Closed</span>
                                    <?php else: ?>
                                        <span class="badge-warning status-badge" style="background:rgba(245,158,11,0.12);color:#F59E0B;border:1px solid rgba(245,158,11,0.2);border-radius:50px;padding:0.2rem 0.7rem;font-size:0.75rem;">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 border-0 text-nowrap"><?= date('d M Y, h:i A', strtotime($inq['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination pagination-sarvam justify-content-center mb-0">
                        <?php if ($current_page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $current_page - 1 ?>"><i class="bi bi-chevron-left"></i></a></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?= $current_page + 1 ?>"><i class="bi bi-chevron-right"></i></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
