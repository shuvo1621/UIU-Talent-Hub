<?php
// Simple test script to check if audio posts exist in database
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/db_connect.php';

echo "<h1>Database Test</h1>";

// Test 1: Check connection
echo "<h2>1. Database Connection</h2>";
if (isset($pdo)) {
    echo "✓ PDO connection exists<br>";
} else {
    echo "✗ PDO connection failed<br>";
    die();
}

// Test 2: Count users
echo "<h2>2. Users in Database</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count users<br>";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test 3: Count posts
echo "<h2>3. Posts in Database</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count total posts<br>";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test 4: Count audio posts
echo "<h2>4. Audio Posts</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM posts WHERE type = 'audio'");
    $count = $stmt->fetch()['count'];
    echo "✓ Found $count audio posts<br>";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test 5: Show audio posts
echo "<h2>5. Audio Post Details</h2>";
try {
    $stmt = $pdo->query("SELECT id, title, user_id, content_path FROM posts WHERE type = 'audio'");
    $posts = $stmt->fetchAll();
    if (count($posts) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>User ID</th><th>File Path</th></tr>";
        foreach ($posts as $post) {
            echo "<tr>";
            echo "<td>" . $post['id'] . "</td>";
            echo "<td>" . htmlspecialchars($post['title']) . "</td>";
            echo "<td>" . $post['user_id'] . "</td>";
            echo "<td>" . htmlspecialchars($post['content_path']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No audio posts found";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test 6: Test the exact query from audiopage.php
echo "<h2>6. Test Audiopage Query</h2>";
try {
    $user_id = 0;
    $sql = "SELECT p.*, u.full_name as author_name,
            (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked
            FROM posts p 
            JOIN users u ON p.user_id = u.id 
            WHERE p.type = 'audio' 
            ORDER BY p.likes_count DESC, p.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $audio_posts = $stmt->fetchAll();

    echo "✓ Query executed successfully<br>";
    echo "✓ Found " . count($audio_posts) . " posts<br>";

    if (count($audio_posts) > 0) {
        echo "<pre>";
        print_r($audio_posts[0]); // Show first post
        echo "</pre>";
    }
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}
?>