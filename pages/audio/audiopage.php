<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../../includes/db_connect.php';
$root = "/UIU TalentHub/";
$active_page = "audio";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>UIU TalentHub Audio Page</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../shared/navbar.css">
    <!-- wavesurfer cdn -->
    <script src="https://unpkg.com/wavesurfer.js@7"></script>
</head>

<body>
    <?php include '../../includes/header.php'; ?>

    <div id="page-content">
        <section class="category">
            <div style="display: flex; justify-content: space-between;">
                <h2>Discover music, poetry, podcasts, and more</h2>
                <a href="../upload.php" class="action-btn">Upload Audio</a>
            </div>

            <div class="cards">
                <div class="cat-card yellow" onclick="filterAudio('music')" data-filter="music"></div>
                <div class="cat-card white" onclick="filterAudio('poem')" data-filter="poem"></div>
                <div class="cat-card orange" onclick="filterAudio('podcast')" data-filter="podcast"></div>
                <div class="cat-card gray" onclick="filterAudio('story')" data-filter="story"></div>
            </div>
        </section>

        <section class="audio-section">
            <?php
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            try {
                $sql = "SELECT p.*, u.full_name as author_name,
                        (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ?) as is_liked
                        FROM posts p 
                        JOIN users u ON p.user_id = u.id 
                        WHERE p.type = 'audio' 
                        ORDER BY p.likes_count DESC, p.created_at DESC";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id]);
                $audio_posts = $stmt->fetchAll();

                // debug posts
                echo "<!-- DEBUG: Found " . count($audio_posts) . " audio posts -->";

            } catch (PDOException $e) {
                echo "<!-- ERROR: " . $e->getMessage() . " -->";
                $audio_posts = [];
            }

            if (empty($audio_posts)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; color: #666;">
                    <p>No audio tracks found. Be the first to upload one!</p>
                </div>
            <?php else: ?>
                <?php foreach ($audio_posts as $post):
                    $audioUrl = $post['content_path'];
                    if ($audioUrl === '#' || empty($audioUrl)) {
                        $audioUrl = 'https://wavesurfer.xyz/wavesurfer-code/examples/audio/audio.wav';
                    } else {
                        $audioUrl = $root . htmlspecialchars($audioUrl);
                    }
                    ?>
                    <!-- track card -->
                    <div class="audio-card-long" data-url="<?php echo $audioUrl; ?>" data-id="<?php echo $post['id']; ?>"
                        data-title="<?php echo strtolower(htmlspecialchars($post['title'])); ?>">
                        <!-- art and play -->
                        <div class="ac-left">
                            <img src="<?php echo $root . htmlspecialchars($post['thumbnail_path']); ?>" alt="Art"
                                class="ac-art">
                            <button class="ac-play-btn" onclick="togglePlay(this)">▶</button>
                        </div>

                        <!-- track info -->
                        <div class="ac-right">
                            <h3 class="ac-title"><?php echo htmlspecialchars($post['title']); ?></h3>

                            <!-- waveform -->
                            <div class="ac-wave-wrapper">
                                <span class="time-current">0:00</span>
                                <div class="waveform-container" id="waveform-<?php echo $post['id']; ?>"></div>
                                <span class="time-total">0:00</span>
                            </div>

                            <!-- likes and author -->
                            <div class="ac-meta">
                                <div class="ac-author">
                                    <span class="by">Posted by</span>
                                    <span class="name"><?php echo htmlspecialchars($post['author_name']); ?></span>
                                </div>

                                <div class="like-container">
                                    <button class="like-btn <?php echo ($post['is_liked'] > 0) ? 'liked' : ''; ?>"
                                        onclick="toggleLike(this, <?php echo $post['id']; ?>)">
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


    <?php include '../../includes/navbar.php'; ?>
    <script src="../../shared/navbar.js"></script>
    <script>
        function filterAudio(category) {
            console.log("Filtering for:", category);

            // highlight active tab
            document.querySelectorAll('.cat-card').forEach(card => {
                card.classList.toggle('active', card.getAttribute('data-filter') === category);
            });

            // filter tracks based on prefix
            const cards = document.querySelectorAll('.audio-card-long');
            let found = 0;

            cards.forEach(card => {
                const url = card.getAttribute('data-url').toLowerCase();
                const filename = url.split('/').pop();

                // If category is null or matches the prefix
                if (!category || category === 'all' || filename.startsWith(category + '_')) {
                    card.style.display = 'flex';
                    found++;
                } else {
                    card.style.display = 'none';
                    // Stop playing if it's currently playing
                    const player = players[card.getAttribute('data-id')];
                    if (player && player.isPlaying()) player.pause();
                }
            });

            // 3. Update 'No results' message if needed
            let emptyMsg = document.getElementById('filter-empty-msg');
            if (found === 0) {
                if (!emptyMsg) {
                    emptyMsg = document.createElement('div');
                    emptyMsg.id = 'filter-empty-msg';
                    emptyMsg.style.cssText = "grid-column: 1/-1; text-align: center; padding: 60px; color: #666; width: 100%;";
                    emptyMsg.innerHTML = `<p style="font-size: 18px; font-weight: 600;">No ${category} tracks found yet.</p>`;
                    document.querySelector('.audio-section').appendChild(emptyMsg);
                }
            } else if (emptyMsg) {
                emptyMsg.remove();
            }
        }

        // Add "All" reset capability if user clicks an already active filter
        document.querySelectorAll('.cat-card').forEach(card => {
            card.addEventListener('click', function () {
                if (this.classList.contains('active')) {
                    filterAudio(null);
                }
            });
        });
    </script>
    <script src="like_system.js"></script>

</body>

</html>