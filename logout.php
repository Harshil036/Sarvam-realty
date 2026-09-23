<?php
/**
 * ============================================================
 *  FEATURE: SECURE USER LOGOUT
 *  File   : logout.php
 *  Role   : Completely destroys the active PHP session and
 *           expires the session cookie in the browser, then
 *           redirects the user to the homepage.
 *
 *  Security steps performed:
 *   1. $_SESSION = []          → wipe all session variables
 *   2. setcookie(..., -42000)  → expire the cookie in browser
 *   3. session_destroy()       → delete session data on server
 *   4. Redirect to index.php   → send user back to homepage
 * ============================================================
 */
require_once __DIR__ . '/config/db.php';

/* ----------------------------------------------------------
 | STEP 1: Clear all session variables from memory
 ---------------------------------------------------------- */
$_SESSION = [];

/* ----------------------------------------------------------
 | STEP 2: Expire the session cookie in the user's browser
 |   Setting time() - 42000 makes the cookie expire immediately
 ---------------------------------------------------------- */
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

/* ----------------------------------------------------------
 | STEP 3: Destroy the server-side session file/data
 ---------------------------------------------------------- */
session_destroy();

/* ----------------------------------------------------------
 | STEP 4: Redirect to homepage after successful logout
 ---------------------------------------------------------- */
header("Location: " . SITE_URL . "/index.php");
exit();
?>
