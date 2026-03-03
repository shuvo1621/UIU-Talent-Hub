<?php
/**
 * UIU TalentHUB - Like API
 * Handles real-time likes via AJAX
 */
require_once '../includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$post_id = intval($_POST['post_id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// For demo purposes, allow likes even without login
// Use a temporary user_id of 0 for guest likes
if ($user_id === 0) {
    $user_id = 999; // Use a dummy user ID for guests
}

try {
    $pdo->beginTransaction();

    // Always add a like (no toggle - just increment)
    // Insert into likes table (will be duplicate if already liked, but that's ok for demo)
    $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE created_at = NOW()");
    $stmt->execute([$user_id, $post_id]);

    // Increment the likes_count
    $update = $pdo->prepare("UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?");
    $update->execute([$post_id]);

    // Get new count
    $stmt = $pdo->prepare("SELECT likes_count FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $new_count = $stmt->fetchColumn();

    $pdo->commit();
    echo json_encode(['success' => true, 'new_count' => $new_count]);

} catch (PDOException $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>