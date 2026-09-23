<?php
/**
 * ============================================================
 *  FEATURE: AJAX WISHLIST TOGGLE (Add / Remove)
 *  File   : wishlist-action.php
 *  Role   : JSON endpoint called via fetch() from property cards
 *           and the wishlist page. Adds or removes a property
 *           from the logged-in user's wishlist in one request.
 *
 *  Request (POST):
 *   • property_id — integer ID of the property to toggle
 *
 *  Response (JSON):
 *   • { success: true, action: "added"|"removed", count: N }
 *   • { success: false, error: "message" }
 *
 *  Logic:
 *   • If row exists → DELETE (remove from wishlist)
 *   • If row absent → INSERT (add to wishlist)
 *   • Returns updated total wishlist count for badge refresh
 *
 *  Security:
 *   • isLoggedIn() check → 401-style JSON error if guest
 *   • PDO prepared statements prevent SQL Injection
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Please log in to manage your wishlist.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$property_id = (int)($_POST['property_id'] ?? 0);

if ($property_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid property ID.']);
    exit();
}

// Check if property exists first
$prop_check = $conn->prepare("SELECT id FROM properties WHERE id = ? LIMIT 1");
$prop_check->execute([$property_id]);
$prop_exists = $prop_check->fetch();

if (!$prop_exists) {
    echo json_encode(['success' => false, 'error' => 'Property does not exist.']);
    exit();
}

// Toggle wishlist item using addToWishlist helper
$action = addToWishlist($conn, $user_id, $property_id);

if ($action) {
    // Get updated wishlist count
    $count = getWishlistCount($conn, $user_id);

    echo json_encode([
        'success' => true,
        'action' => $action,
        'count' => $count
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update wishlist.']);
}
exit();
?>
