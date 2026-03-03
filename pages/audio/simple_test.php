<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../../includes/db_connect.php';

echo "<!DOCTYPE html><html><head><title>Debug Test</title></head><body>";
echo "<h1>Database Debug Test</h1>";

// Check which database
$db_check = $pdo->query("SELECT DATABASE()")->fetchColumn();
echo "<p><strong>Database:</strong> " . $db_check . "</p>";

// Count all posts
$total = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
echo "<p><strong>Total posts:</strong> " . $total . "</p>";

// Count audio posts
$audio_count = $pdo->query("SELECT COUNT(*) FROM posts WHERE type = 'audio'")->fetchColumn();
echo "<p><strong>Audio posts:</strong> " . $audio_count . "</p>";

// Try simple query
$simple = $pdo->query("SELECT id, title, user_id FROM posts WHERE type = 'audio' LIMIT 5")->fetchAll();
echo "<h2>Direct Query (no JOIN):</h2>";
echo "<p>Found: " . count($simple) . " posts</p>";
if (count($simple) > 0) {
    foreach ($simple as $p) {
        echo "<div>ID: {$p['id']}, Title: {$p['title']}, User ID: {$p['user_id']}</div>";
    }
}

// Try with JOIN
echo "<h2>Query WITH JOIN:</h2>";
$joined = $pdo->query("SELECT p.id, p.title, u.full_name FROM posts p JOIN users u ON p.user_id = u.id WHERE p.type = 'audio' LIMIT 5")->fetchAll();
echo "<p>Found: " . count($joined) . " posts</p>";
if (count($joined) > 0) {
    foreach ($joined as $p) {
        echo "<div>ID: {$p['id']}, Title: {$p['title']}, Author: {$p['full_name']}</div>";
    }
}

echo "</body></html>";
?>