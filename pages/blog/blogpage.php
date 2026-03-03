<?php
require_once '../../includes/db_connect.php';
session_start();
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$root = "/UIU TalentHub/";
$active_page = "blog";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU TalentHUb Journals</title>
    <link rel="stylesheet" href="stylebp.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../shared/navbar.css">
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div id="page-content">
        <div class="container">
            <!-- leaderboard -->
            <section class="ranking-section">
                <div class="ranking-header">
                    <h2>Top Ranked Journals</h2>
                    <p>The most loved stories from our community this week.</p>
                </div>

                <div class="ranking-grid">
                    <?php
                    try {
                        // Fetch Top 3 specifically for the ranking section
                        $rankSql = "SELECT p.*, u.full_name as author_name,
                                (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked
                                FROM posts p 
                                JOIN users u ON p.user_id = u.id 
                                WHERE p.type = 'blog' 
                                ORDER BY p.likes_count DESC 
                                LIMIT 3";
                        $rankStmt = $pdo->prepare($rankSql);
                        $rankStmt->execute([$user_id]);
                        $top_posts = $rankStmt->fetchAll();
                    } catch (PDOException $e) {
                        $top_posts = [];
                    }

                    foreach ($top_posts as $idx => $post):
                        $rank = $idx + 1;
                        $rankClass = ($rank == 1) ? 'gold' : (($rank == 2) ? 'silver' : 'bronze');
                        ?>
                        <div class="rank-card <?php echo $rankClass; ?>"
                            data-title="<?php echo htmlspecialchars($post['title']); ?>"
                            data-author="<?php echo htmlspecialchars($post['author_name']); ?>"
                            data-description="<?php echo htmlspecialchars($post['description']); ?>"
                            data-thumbnail="<?php echo $root . htmlspecialchars($post['thumbnail_path']); ?>"
                            onclick="openJournalModal(this)">
                            <div class="rank-badge"><?php echo $rank; ?></div>
                            <div class="rank-img">
                                <img src="<?php echo $root . htmlspecialchars($post['thumbnail_path']); ?>" alt="Cover">
                            </div>
                            <div class="rank-info">
                                <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                <div class="rank-meta">
                                    <span>by <?php echo htmlspecialchars($post['author_name']); ?></span>
                                    <span class="rank-likes"><?php echo number_format($post['likes_count']); ?> Likes</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="mid">
                <div class="mid-left">
                    <h2>Discover creative performances from UIU students</h2>
                </div>
                <a href="../upload.php" class="action-btn">Upload Journal</a>
            </div>

            <section class="blog-grid">
                <?php
                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                try {
                    $sql = "SELECT p.*, u.full_name as author_name,
                            (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked
                            FROM posts p 
                            JOIN users u ON p.user_id = u.id 
                            WHERE p.type = 'blog' 
                            ORDER BY p.likes_count DESC, p.created_at DESC";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$user_id]);
                    $blog_posts = $stmt->fetchAll();
                } catch (PDOException $e) {
                    $blog_posts = [];
                }

                if (empty($blog_posts)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">
                        <p>No blog posts found. Share your thoughts!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($blog_posts as $post): ?>
                        <div class="blog-card" data-title="<?php echo htmlspecialchars($post['title']); ?>"
                            data-author="<?php echo htmlspecialchars($post['author_name']); ?>"
                            data-description="<?php echo htmlspecialchars($post['description']); ?>"
                            data-thumbnail="<?php echo $root . htmlspecialchars($post['thumbnail_path']); ?>"
                            onclick="openJournalModal(this)">
                            <!-- top image -->
                            <div class="bc-thumb">
                                <img src="<?php echo $root . htmlspecialchars($post['thumbnail_path']); ?>" alt="Cover">
                                <div class="bc-badge">Journal</div>
                            </div>

                            <!-- bottom text -->
                            <div class="bc-content">
                                <div class="bc-text">
                                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <p><?php echo htmlspecialchars(substr($post['description'], 0, 100)) . "..."; ?></p>
                                </div>

                                <div class="bc-footer">
                                    <div class="bc-author">
                                        <span class="by">By</span>
                                        <span class="name"><?php echo htmlspecialchars($post['author_name']); ?></span>
                                    </div>

                                    <div class="like-container">
                                        <button class="like-btn <?php echo ($post['is_liked'] > 0) ? 'liked' : ''; ?>"
                                            onclick="event.stopPropagation(); toggleLike(this, <?php echo $post['id']; ?>)">
                                            <?php echo ($post['is_liked'] > 0) ? '❤️' : '❤'; ?>
                                        </button>
                                        <span class="like-count"><?php echo number_format($post['likes_count']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </div>

        <!-- blog popup -->
        <div id="journalModal" class="journal-modal">
            <div class="modal-overlay" onclick="closeJournalModal()"></div>
            <div class="modal-container">
                <button class="modal-close" onclick="closeJournalModal()">×</button>
                <div class="modal-content">
                    <div class="modal-header">
                        <img id="modalImg" src="" alt="Cover">
                        <div class="header-overlay">
                            <div class="badge">Journal</div>
                            <h1 id="modalTitle"></h1>
                            <p id="modalAuthor"></p>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div id="modalDescription" class="journal-text"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="footer-actions">
                            <p>End of entry. Thank you for reading.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openJournalModal(card) {
            const modal = document.getElementById('journalModal');
            const title = card.getAttribute('data-title');
            const author = card.getAttribute('data-author');
            const description = card.getAttribute('data-description');
            const thumbnail = card.getAttribute('data-thumbnail');

            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalAuthor').textContent = 'by ' + author;
            document.getElementById('modalDescription').innerHTML = description;
            document.getElementById('modalImg').src = thumbnail;

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // fix z-index for page navigation
            const pageContent = document.getElementById('page-content');
            if (pageContent) pageContent.style.zIndex = "2000000000";
        }

        function closeJournalModal() {
            const modal = document.getElementById('journalModal');
            if (!modal) return;
            modal.classList.remove('active');
            document.body.style.overflow = '';

            // Restore parent z-index
            const pageContent = document.getElementById('page-content');
            if (pageContent) pageContent.style.zIndex = "";
        }

        // Close on ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeJournalModal();
        });

        async function toggleLike(btn, postId) {
            btn.disabled = true;
            try {
                const response = await fetch('../../api/like.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${postId}`
                });
                const result = await response.json();
                if (result.success) {
                    btn.nextElementSibling.textContent = result.new_count.toLocaleString();
                    btn.innerHTML = '❤️';
                    btn.classList.add('liked');
                }
            } catch (e) {
                console.error("Like sync failed");
            }
            btn.disabled = false;
        }
    </script>

    <?php include '../../includes/navbar.php'; ?>
    <script src="../../shared/navbar.js?v=<?php echo time(); ?>"></script>
</body>

</html>