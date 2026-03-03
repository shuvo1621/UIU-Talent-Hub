<?php
/*
 * post viewer
 */
require_once '../includes/db_connect.php';
session_start();

$root = "../";
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($post_id === 0) {
    header("Location: index.php");
    exit();
}

// get post data
try {
    $stmt = $pdo->prepare("SELECT p.*, u.full_name as author_name, u.profile_picture as author_pic 
                         FROM posts p 
                         JOIN users u ON p.user_id = u.id 
                         WHERE p.id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post) {
        die("Post not found.");
    }
} catch (PDOException $e) {
    die("Error fetching post data.");
}

// back button url
$back_links = [
    'audio' => 'pages/audio/audiopage.php',
    'video' => 'pages/video/videopage.php',
    'blog' => 'pages/blog/blogpage.php'
];
$back_url = $back_links[$post['type']] ?? 'index.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo htmlspecialchars($post['title']); ?> - UIU TalentHUB
    </title>
    <link rel="stylesheet" href="shared/navbar.css">
    <style>
        body {
            background: #f0f2f5;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        .viewer-container {
            max-width: 900px;
            margin: 100px auto 120px;
            padding: 20px;
        }

        .main-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        /* content layout */
        .media-viewer {
            width: 100%;
            background: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 300px;
        }

        .media-viewer img {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
        }

        .media-viewer video {
            width: 100%;
            max-height: 600px;
        }

        .audio-player-box {
            width: 100%;
            padding: 60px;
            background: linear-gradient(135deg, #ff5e00, #ff9d00);
            text-align: center;
            color: white;
        }

        .audio-player-box audio {
            width: 100%;
            margin-top: 20px;
            filter: grayscale(1) invert(1);
        }

        .post-header {
            padding: 30px;
            border-bottom: 1px solid #eee;
        }

        .post-title {
            font-size: 32px;
            font-weight: 800;
            color: #1a1a1a;
            margin-bottom: 15px;
        }

        .author-box {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .author-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .author-info h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .author-info p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }

        .post-content-text {
            padding: 30px;
            font-size: 18px;
            line-height: 1.8;
            color: #444;
        }

        .back-nav {
            margin-bottom: 20px;
        }

        .back-nav a {
            text-decoration: none;
            color: #666;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .engagement-bar {
            padding: 20px 30px;
            background: #fafafa;
            display: flex;
            gap: 20px;
            border-top: 1px solid #eee;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #666;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="viewer-container">
        <div class="back-nav">
            <a href="<?php echo $back_url; ?>">← Back to Feed</a>
        </div>

        <div class="main-card fade-up-in">
            <!-- show post content -->
            <div class="media-viewer">
                <?php if ($post['type'] == 'video'): ?>
                    <video controls poster="<?php echo $post['thumbnail_path']; ?>">
                        <source src="<?php echo $post['content_path']; ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php elseif ($post['type'] == 'audio'): ?>
                    <div class="audio-player-box">
                        <div style="font-size: 40px;">🎵</div>
                        <h3>Now Playing:
                            <?php echo htmlspecialchars($post['title']); ?>
                        </h3>
                        <audio controls>
                            <source src="<?php echo $post['content_path']; ?>" type="audio/mpeg">
                        </audio>
                    </div>
                <?php else: // Blog ?>
                    <img src="<?php echo $post['thumbnail_path']; ?>" alt="Blog Header">
                <?php endif; ?>
            </div>

            <div class="post-header">
                <h1 class="post-title">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>

                <div class="author-box">
                    <?php
                    // hash name for color fallback
                    $hash_seed = !empty($post['author_name']) ? $post['author_name'] : 'unknown';
                    $colors = ['#ff6b6b', '#4facfe', '#66bb6a', '#ffa726', '#ab47bc', '#26a69a', '#ec407a', '#5c6bc0'];
                    $color_index = hexdec(substr(md5($hash_seed), 0, 8)) % count($colors);
                    $avatar_color = $colors[$color_index];
                    $initial = strtoupper(substr($post['author_name'], 0, 1));
                    ?>
                    <div class="author-avatar" style="background-color: <?php echo $avatar_color; ?>">
                        <?php echo $initial; ?>
                    </div>
                    <div class="author-info">
                        <h4>
                            <?php echo htmlspecialchars($post['author_name']); ?>
                        </h4>
                        <p>Published on
                            <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="post-content-text">
                <?php echo nl2br(htmlspecialchars($post['description'])); ?>
                <?php if ($post['type'] == 'blog'): ?>
                    <div
                        style="margin-top: 30px; border-top: 1px dashed #ddd; padding-top: 20px; font-style: italic; color: #777;">
                        (Full article content would be fetched from content_path if it points to a text file, otherwise
                        description serves as the main content.)
                    </div>
                <?php endif; ?>
            </div>

            <div class="engagement-bar">
                <div class="stat-item">❤️
                    <?php echo number_format($post['likes_count']); ?> Likes
                </div>
                <div class="stat-item">🎬
                    <?php echo ucfirst($post['type']); ?> Content
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/navbar.php'; ?>
    <script src="shared/navbar.js"></script>
</body>

</html>