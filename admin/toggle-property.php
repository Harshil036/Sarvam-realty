<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $field = $_POST['field'];

    if (!in_array($field, ['is_featured', 'is_premium'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid field']);
        exit;
    }

    // Get current state
    $stmt = $pdo->prepare("SELECT $field FROM properties WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetchColumn();

    if ($current !== false) {
        $newState = $current == 1 ? 0 : 1;
        $update = $pdo->prepare("UPDATE properties SET $field = ? WHERE id = ?");
        $update->execute([$newState, $id]);
        
        echo json_encode(['success' => true, 'newState' => $newState]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Property not found']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
