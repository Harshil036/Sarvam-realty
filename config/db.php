<?php
/**
 * ============================================================
 *  FEATURE: DATABASE CONFIGURATION & SESSION BOOTSTRAPPING
 *  File   : config/db.php
 *  Role   : Loaded by every page via require_once.
 *           Sets up the PDO database connection, defines
 *           site-wide constants, and starts a secure PHP
 *           session before any output is sent.
 * ============================================================
 */

/* ----------------------------------------------------------
 | FEATURE: SECURE SESSION MANAGEMENT
 |   - cookie_httponly  → blocks JavaScript from reading the
 |                        session cookie (XSS defence)
 |   - use_only_cookies → prevents session IDs appearing in URLs
 |   - cookie_samesite  → Lax mode blocks cross-site CSRF requests
 |   - gc_maxlifetime   → server-side session expires after 2 hours
 |   - session_set_cookie_params → aligns the browser cookie
 |                                   lifetime with the server
 ---------------------------------------------------------- */
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', 7200);
    session_set_cookie_params([
        'lifetime' => 7200,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ----------------------------------------------------------
 | FEATURE: SITE-WIDE CONSTANTS
 |   DB_HOST / DB_USER / DB_PASS / DB_NAME  → MySQL credentials
 |   SITE_URL    → base URL used for all redirect headers
 |   SITE_NAME   → displayed in page titles and emails
 |   UPLOAD_PATH → absolute server path for uploaded files
 |   UPLOAD_URL  → public URL counterpart for <img src="">
 ---------------------------------------------------------- */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sarvam_real_estate');
define('SITE_URL', 'http://localhost/Sarvam-Real-Estate');
define('SITE_NAME', 'Sarvam Real Estate');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');

/* ----------------------------------------------------------
 | FEATURE: PDO DATABASE CONNECTION
 |   Uses PHP Data Objects (PDO) for:
 |   • Prepared statements (prevents SQL Injection)
 |   • ERRMODE_EXCEPTION → throws exceptions on DB errors
 |   • FETCH_ASSOC       → returns rows as key=>value arrays
 |   • EMULATE_PREPARES  → false means real server-side binding
 |   $conn alias keeps all query code consistent throughout app
 ---------------------------------------------------------- */
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $conn = $pdo; // Alias $conn for seamless compatibility
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:30px;background:#fee;border:1px solid #fcc;color:#c00;">
        <h2>Database Connection Failed (PDO)</h2>
        <p>' . htmlspecialchars($e->getMessage()) . '</p>
        <p>Please ensure XAMPP MySQL is running and the database <strong>sarvam_real_estate</strong> has been imported.</p>
    </div>');
}

/* ----------------------------------------------------------
 | Load global helper functions (auth guards, formatters,
 | wishlist, inquiry, upload utilities, pagination, etc.)
 ---------------------------------------------------------- */
require_once __DIR__ . '/../includes/functions.php';
?>
