<?php
/*
 * simplified profile page
 */
require_once '../../includes/db_connect.php';
session_start();

// check login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Auth/login.php");
    exit();
}

$root = "/UIU TalentHub/";
$active_page = "profile";
$user_id = $_SESSION['user_id'];

// get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// get stats
$stmt = $pdo->prepare("SELECT COUNT(*) as total_posts, SUM(likes_count) as total_likes FROM posts WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// get my posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$my_posts = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['full_name']); ?> - Profile</title>
    <link rel="stylesheet" href="profile.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../shared/navbar.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div id="page-content">
        <div class="profile-container">

            <!-- top header -->
            <div class="user-profile-header">
                <div class="header-main">
                    <?php
                    // color based on student id
                    $colors = ['#ff6b6b', '#4facfe', '#66bb6a', '#ffa726', '#ab47bc', '#26a69a', '#ec407a', '#5c6bc0'];
                    $color_index = hexdec(substr(md5($user['student_id']), 0, 8)) % count($colors);
                    $avatar_color = $colors[$color_index];
                    $initial = strtoupper(substr($user['full_name'], 0, 1));
                    ?>
                    <div class="avatar-circle" style="background-color: <?php echo $avatar_color; ?>">
                        <?php echo $initial; ?>
                    </div>
                    <div class="user-meta">
                        <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                        <p><?php echo htmlspecialchars($user['email']); ?> • ID:
                            <?php echo htmlspecialchars($user['student_id']); ?>
                        </p>
                        <div class="mini-stats">
                            <span><b><?php echo number_format($stats['total_posts']); ?></b> Posts</span>
                            <span><b><?php echo number_format($stats['total_likes'] ?? 0); ?></b> Likes</span>
                            <span>Joined <b><?php echo date('M Y', strtotime($user['created_at'])); ?></b></span>
                        </div>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="../upload.php" class="action-btn">Upload Content</a>
                </div>
            </div>

            <!-- my posts grid -->
            <div class="content-section">
                <div class="section-header">
                    <h2>My Shared Content</h2>
                    <div class="filter-tabs">
                        <button class="filter-btn active" data-type="all">All</button>
                        <button class="filter-btn" data-type="audio">Audio</button>
                        <button class="filter-btn" data-type="video">Video</button>
                        <button class="filter-btn" data-type="blog">Journals</button>
                    </div>
                </div>

                <?php if (empty($my_posts)): ?>
                    <div class="empty-state">
                        <p>You haven't shared anything yet. Start your journey!</p>
                        <a href="../upload.php" class="action-btn">Upload something now</a>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach ($my_posts as $post): ?>
                            <div class="post-item" data-type="<?php echo $post['type']; ?>"
                                id="post-<?php echo $post['id']; ?>">
                                <div class="post-preview">
                                    <img src="<?php echo $root . htmlspecialchars($post['thumbnail_path']); ?>" alt="Cover">
                                    <div class="post-type-tag"><?php echo ucfirst($post['type']); ?></div>
                                    <button class="delete-post-btn" onclick="confirmDelete(<?php echo $post['id']; ?>)"
                                        title="Delete Post">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="post-info">
                                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <div class="post-meta">
                                        <span><?php echo number_format($post['likes_count']); ?> Likes</span>
                                        <span><?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- delete modal -->
    <div id="deleteModal" class="confirm-modal">
        <div class="modal-content">
            <h3>Delete Post?</h3>
            <p>This action cannot be undone. Are you sure you want to remove this piece of work?</p>
            <div class="modal-footer">
                <button onclick="closeDeleteModal()" class="btn-cancel">Cancel</button>
                <button id="confirmDeleteBtn" class="btn-delete">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let currentPostId = null;

        function confirmDelete(postId) {
            currentPostId = postId;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            currentPostId = null;
        }

        document.getElementById('confirmDeleteBtn').onclick = async function () {
            if (!currentPostId) return;

            try {
                const response = await fetch('../../api/delete_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${currentPostId}`
                });

                const result = await response.json();
                if (result.success) {
                    const el = document.getElementById(`post-${currentPostId}`);
                    el.style.opacity = '0';
                    setTimeout(() => {
                        el.remove();
                        if (document.querySelectorAll('.post-item').length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    alert('Error: ' + (result.message || 'Could not delete post'));
                }
            } catch (e) {
                alert('Connection error');
            }
            closeDeleteModal();
        };

        // handle tabs
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.onclick = function () {
                const type = this.getAttribute('data-type');
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                document.querySelectorAll('.post-item').forEach(post => {
                    if (type === 'all' || post.getAttribute('data-type') === type) {
                        post.style.display = 'block';
                    } else {
                        post.style.display = 'none';
                    }
                });
            }
        });
    </script>

    <?php include '../../includes/navbar.php'; ?>
    <script src="../../shared/navbar.js"></script>
</body>

</html>