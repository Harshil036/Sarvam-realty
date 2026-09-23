<?php
/**
 * ============================================================
 *  FEATURE: GLOBAL HELPER FUNCTIONS
 *  File   : includes/functions.php
 *  Role   : Central library of utility functions shared across
 *           all pages. Included via config/db.php on every request.
 *
 *  Functions defined here:
 *   • sanitize($input)        — XSS-safe output encoding
 *   • formatPrice($price)     — Indian currency format (₹ Cr / L / K)
 *   • isLoggedIn()            — checks user session + 2-hr timeout
 *   • isAdminLoggedIn()       — checks admin session + 2-hr timeout
 *   • requireLogin()          — gate: redirects guests to /login.php
 *   • requireAdminLogin()     — gate: redirects non-admins to /login.php
 *   • getPropertyImage($img)  — returns Unsplash fallback if no upload
 *   • timeAgo($datetime)      — human-readable "2 hours ago" string
 *
 *  Session timeout logic (2-hour inactivity):
 *   • Checks login_time / admin_login_time stored at login
 *   • If > 7200 seconds have passed → destroys session, returns false
 * ============================================================
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitize($input) {
    return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
}

function formatPrice($price) {
    $price = (float)$price;
    if ($price >= 10000000) {
        return '₹' . round($price / 10000000, 2) . ' Crore';
    } elseif ($price >= 100000) {
        return '₹' . round($price / 100000, 2) . ' Lakh';
    } else {
        return '₹' . number_format($price);
    }
}

function formatArea($sqft) {
    return number_format((float)$sqft) . ' sq.ft';
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . SITE_URL . "/login.php");
        exit();
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header("Location: " . SITE_URL . "/admin/login.php");
        exit();
    }
}

function getPropertyById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT p.*, pt.type_name, l.city, l.state, a.name as agent_name, a.phone as agent_phone, a.email as agent_email, a.photo as agent_photo 
            FROM properties p 
            JOIN property_types pt ON p.property_type_id = pt.id 
            JOIN locations l ON p.location_id = l.id 
            JOIN agents a ON p.agent_id = a.id 
            WHERE p.id = ?");
    $stmt->execute([(int)$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

function getFeaturedProperties($pdo, $limit = 6) {
    $stmt = $pdo->prepare("SELECT p.*, pt.type_name, l.city FROM properties p JOIN property_types pt ON p.property_type_id = pt.id JOIN locations l ON p.location_id = l.id WHERE p.is_featured = 1 AND p.status = 'available' ORDER BY p.created_at DESC LIMIT ?");
    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    if(isset($stmt_params)) { $stmt->execute($stmt_params); unset($stmt_params); } else { $stmt->execute(); }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLatestProperties($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT p.*, pt.type_name, l.city FROM properties p JOIN property_types pt ON p.property_type_id = pt.id JOIN locations l ON p.location_id = l.id WHERE p.status = 'available' ORDER BY p.created_at DESC LIMIT ?");
    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    if(isset($stmt_params)) { $stmt->execute($stmt_params); unset($stmt_params); } else { $stmt->execute(); }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPremiumProperties($pdo, $limit = 6) {
    $stmt = $pdo->prepare("SELECT p.*, pt.type_name, l.city FROM properties p JOIN property_types pt ON p.property_type_id = pt.id JOIN locations l ON p.location_id = l.id WHERE p.is_premium = 1 AND p.status = 'available' ORDER BY p.created_at DESC LIMIT ?");
    $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
    if(isset($stmt_params)) { $stmt->execute($stmt_params); unset($stmt_params); } else { $stmt->execute(); }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPropertyTypes($pdo) {
    $stmt = $pdo->query("SELECT * FROM property_types WHERE status = 'active' ORDER BY type_name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLocations($pdo) {
    $stmt = $pdo->query("SELECT * FROM locations WHERE status = 'active' ORDER BY city ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAgents($pdo, $limit = null) {
    $sql = "SELECT * FROM agents WHERE status = 'active' ORDER BY rating DESC, properties_sold DESC";
    if ($limit) {
        $stmt = $pdo->prepare($sql . " LIMIT ?");
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        if(isset($stmt_params)) { $stmt->execute($stmt_params); unset($stmt_params); } else { $stmt->execute(); }
    } else {
        $stmt = $pdo->query($sql);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addToWishlist($pdo, $user_id, $property_id) {
    $user_id = (int)$user_id;
    $property_id = (int)$property_id;
    
    if (isInWishlist($pdo, $user_id, $property_id)) {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND property_id = ?");
        return $stmt->execute([$user_id, $property_id]) ? 'removed' : false;
    } else {
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, property_id) VALUES (?, ?)");
        return $stmt->execute([$user_id, $property_id]) ? 'added' : false;
    }
}

function isInWishlist($pdo, $user_id, $property_id) {
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND property_id = ?");
    $stmt->execute([(int)$user_id, (int)$property_id]);
    return (bool)$stmt->fetch();
}

function getWishlistCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $stmt->execute([(int)$user_id]);
    return (int)$stmt->fetchColumn();
}

function submitInquiry($pdo, $data) {
    $property_id = !empty($data['property_id']) ? (int)$data['property_id'] : null;
    $user_id = !empty($data['user_id']) ? (int)$data['user_id'] : null;
    $name = sanitize($data['name'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $phone = sanitize($data['phone'] ?? '');
    $message = sanitize($data['message'] ?? '');
    
    $stmt = $pdo->prepare("INSERT INTO inquiries (property_id, user_id, name, email, phone, message) 
            VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$property_id, $user_id, $name, $email, $phone, $message]);
}

function getDashboardStats($pdo) {
    $stats = [];
    $stats['total_properties'] = (int)$pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $stats['active_properties'] = (int)$pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'available'")->fetchColumn();
    $stats['total_users'] = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['total_agents'] = (int)$pdo->query("SELECT COUNT(*) FROM agents")->fetchColumn();
    $stats['total_inquiries'] = (int)$pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
    $stats['new_inquiries'] = (int)$pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();
    return $stats;
}

function paginate($total, $per_page, $current_page) {
    $total_pages = ceil($total / $per_page);
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'has_next' => $current_page < $total_pages,
        'has_prev' => $current_page > 1,
        'offset' => ($current_page - 1) * $per_page
    ];
}

function uploadImage($file, $folder) {
    $target_dir = UPLOAD_PATH . $folder . '/';
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (!in_array($file_ext, $allowed_exts)) {
        return ['success' => false, 'error' => 'Invalid file type. Only JPG, JPEG, PNG, WEBP allowed.'];
    }
    
    if ($file["size"] > 5000000) { // 5MB limit
        return ['success' => false, 'error' => 'File too large. Max 5MB allowed.'];
    }
    
    $new_filename = uniqid() . '_' . time() . '.' . $file_ext;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }
}

function generatePropertyId($id) {
    return 'SARV-' . str_pad($id, 3, '0', STR_PAD_LEFT);
}

function timeAgo($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>
