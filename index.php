<?php
require_once 'includes/db_connect.php';
session_start();

$root = "/UIU TalentHub/";
$active_page = "trending";

// get trending posts
try {
    $stmt = $pdo->query("SELECT p.*, u.full_name as author_name 
                         FROM posts p 
                         JOIN users u ON p.user_id = u.id 
                         ORDER BY p.likes_count DESC, RAND()
                         LIMIT 24");
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    // fallback if no db
    $posts = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trending - UIU TalentHUB</title>
    <link rel="stylesheet" href="shared/trending.css">
    <link rel="stylesheet" href="shared/navbar.css">
    <script src="https://unpkg.com/wavesurfer.js@7"></script>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div id="page-content">
        <div class="container">
            <div class="trending-header">
                <h1>🔥 Trending Now</h1>
            </div>

            <div class="bento-grid">
                <?php if (empty($posts)): ?>
                    <!-- demo data if empty -->
                    <div class="bento-item item-p">
                        <div class="badge">Demo</div>
                        <img src="assets/images/trending_p1.png" alt="Demo">
                        <div class="item-info">
                            <div class="info-content">
                                <h3>Setup Database to view Content</h3>
                                <p>by System</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post):
                        $class = "item-s";
                        $is_blog = ($post['type'] == 'blog');
                        
                        if ($post['type'] == 'audio') $class = "item-p";
                        elseif ($is_blog) $class = "item-m blog-entry";
                        elseif ($post['type'] == 'video') $class = "item-s bento-video";
                        ?>
                        <div class="bento-item <?php echo $class; ?>" 
                             data-id="<?php echo $post['id']; ?>"
                             data-type="<?php echo $post['type']; ?>"
                             data-content="<?php echo htmlspecialchars($post['content_path']); ?>"
                             data-title="<?php echo htmlspecialchars($post['title']); ?>"
                             data-author="<?php echo htmlspecialchars($post['author_name']); ?>"
                             data-description="<?php echo htmlspecialchars($post['description']); ?>"
                             data-thumbnail="<?php echo $root . htmlspecialchars($post['thumbnail_path']); ?>"
                             <?php if ($post['type'] == 'video'): ?>
                             onmouseenter="hoverPlay(<?php echo $post['id']; ?>)"
                             onmouseleave="hoverPause(<?php echo $post['id']; ?>)"
                             <?php elseif ($is_blog): ?>
                             onclick="openJournalModal(this)"
                             <?php endif; ?>>

                            <div class="badge"><?php echo ucfirst($post['type']); ?></div>

                            <!-- thumbnail preview -->
                            <div class="item-preview">
                                <img src="<?php echo $root . htmlspecialchars($post['thumbnail_path']); ?>" alt="Thumbnail" class="thumbnail-img">

                                <?php if ($post['type'] == 'video'): ?>
                                    <video class="hover-video" muted playsinline loop preload="auto" 
                                           id="hover-video-<?php echo $post['id']; ?>"
                                           onloadedmetadata="adjustBento(this, <?php echo $post['id']; ?>)">
                                        <source src="<?php echo $root . htmlspecialchars($post['content_path']); ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>

                                <!-- play button overlay -->
                                <?php if ($post['type'] == 'audio' || $post['type'] == 'video'): ?>
                                    <div class="play-overlay"
                                        onclick="playContent(<?php echo $post['id']; ?>, '<?php echo $post['type']; ?>')">
                                        <svg class="play-icon" viewBox="0 0 24 24" fill="white">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Content Player (hidden initially) -->
                            <div class="content-player" id="player-<?php echo $post['id']; ?>" style="display: none;">
                                <?php if ($post['type'] == 'audio'): ?>
                                    <div class="audio-player-inline">
                                        <button class="close-player" onclick="closePlayer(<?php echo $post['id']; ?>)">×</button>
                                        <div class="waveform-inline" id="wave-<?php echo $post['id']; ?>"></div>
                                        <div class="audio-controls">
                                            <button class="play-pause-btn" id="playbtn-<?php echo $post['id']; ?>">▶</button>
                                            <span class="time-display">0:00 / 0:00</span>
                                        </div>
                                    </div>
                                <?php elseif ($post['type'] == 'video'): ?>
                                    <div class="video-player-inline">
                                        <button class="close-player" onclick="closePlayer(<?php echo $post['id']; ?>)">×</button>
                                        <video controls id="video-<?php echo $post['id']; ?>">
                                            <source src="<?php echo $root . htmlspecialchars($post['content_path']); ?>"
                                                type="video/mp4">
                                        </video>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Info Overlay / 50% Section for Blogs -->
                            <div class="item-info">
                                <div class="info-content">
                                    <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                                    <p>by <?php echo htmlspecialchars($post['author_name']); ?></p>
                                </div>
                                <div class="like-container">
                                    <button class="like-btn"
                                        onclick="event.stopPropagation(); toggleLike(this, <?php echo $post['id']; ?>)">❤</button>
                                    <span class="like-count"><?php echo number_format($post['likes_count']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Journal Modal (Unified with blog pages) -->
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
            
            // Critical fix for stacking context
            const pageContent = document.getElementById('page-content');
            if (pageContent) pageContent.style.zIndex = "2000000000";
        }

        function closeJournalModal() {
            const modal = document.getElementById('journalModal');
            if (!modal) return;
            modal.classList.remove('active');
            document.body.style.overflow = ''; 
            
            const pageContent = document.getElementById('page-content');
            if (pageContent) pageContent.style.zIndex = "";
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeJournalModal();
        });

        const wavesurfers = {};

        function adjustBento(video, postId) {
            const card = document.querySelector(`[data-id="${postId}"]`);
            if (!card) return;
            
            const ratio = video.videoWidth / video.videoHeight;
            
            // Fast class switching
            let sizeClass = 'item-s';
            if (ratio > 1.2) sizeClass = 'item-l';
            else if (ratio < 0.7) sizeClass = 'item-tall';
            else if (ratio < 0.9) sizeClass = 'item-m';
            
            if (!card.classList.contains(sizeClass)) {
                card.classList.remove('item-s', 'item-m', 'item-l', 'item-tall');
                card.classList.add(sizeClass);
            }
        }

        function hoverPlay(postId) {
            const hoverVideo = document.getElementById(`hover-video-${postId}`);
            if (hoverVideo) {
                hoverVideo.style.opacity = '1';
                hoverVideo.play().catch(e => console.log("Hover play blocked:", e));
            }
        }

        function hoverPause(postId) {
            const hoverVideo = document.getElementById(`hover-video-${postId}`);
            if (hoverVideo) {
                hoverVideo.style.opacity = '0';
                hoverVideo.pause();
                hoverVideo.currentTime = 0;
            }
        }

        // Play content inline
        function playContent(postId, type) {
            const card = document.querySelector(`[data-id="${postId}"]`);
            const player = document.getElementById(`player-${postId}`);
            const preview = card.querySelector('.item-preview');
            const info = card.querySelector('.item-info');

            if (type === 'video') hoverPause(postId);

            // Show player overlay
            player.style.display = 'block';
            player.style.background = 'rgba(0,0,0,0.3)'; // Dark shadow

            // Hide play overlay and info for focus
            card.querySelector('.play-overlay').style.display = 'none';
            if (info) info.style.opacity = '0';

            if (type === 'audio') {
                initAudioPlayer(postId, card.dataset.content);
            } else if (type === 'video') {
                const video = document.getElementById(`video-${postId}`);
                video.style.opacity = '1';
                video.play();
            }
        }

        // Initialize Wavesurfer for audio
        function initAudioPlayer(postId, audioPath) {
            if (wavesurfers[postId]) {
                wavesurfers[postId].play();
                return;
            }

            const ws = WaveSurfer.create({
                container: `#wave-${postId}`,
                waveColor: 'rgba(255, 255, 255, 0.4)',
                progressColor: '#ff5e00',
                cursorColor: '#ff5e00',
                barWidth: 2,
                barGap: 3,
                height: 50,
                normalize: true,
                autoCenter: true,
                hideScrollbar: true,
            });

            ws.load('/UIU TalentHub/' + audioPath);
            wavesurfers[postId] = ws;

            const playBtn = document.getElementById(`playbtn-${postId}`);
            const timeDisplay = document.querySelector(`#player-${postId} .time-display`);

            const formatTime = (time) => {
                const minutes = Math.floor(time / 60);
                const seconds = Math.floor(time % 60);
                return `${minutes}:${seconds.toString().padStart(2, '0')}`;
            };

            ws.on('ready', () => {
                ws.play();
                timeDisplay.textContent = `0:00 / ${formatTime(ws.getDuration())}`;
                playBtn.textContent = '⏸';
            });

            ws.on('timeupdate', (currentTime) => {
                timeDisplay.textContent = `${formatTime(currentTime)} / ${formatTime(ws.getDuration())}`;
            });

            ws.on('play', () => playBtn.textContent = '⏸');
            ws.on('pause', () => playBtn.textContent = '▶');
            ws.on('finish', () => playBtn.textContent = '▶');

            playBtn.onclick = (e) => {
                e.stopPropagation();
                ws.playPause();
            };
        }

        // Close player
        function closePlayer(postId) {
            const card = document.querySelector(`[data-id="${postId}"]`);
            const player = document.getElementById(`player-${postId}`);
            const info = card.querySelector('.item-info');

            if (wavesurfers[postId]) {
                wavesurfers[postId].pause();
            }
            const video = document.getElementById(`video-${postId}`);
            if (video) video.pause();

            // Reset UI
            player.style.display = 'none';
            card.querySelector('.play-overlay').style.display = 'flex';
            if (info) info.style.opacity = '';
        }

        // Like function
        async function toggleLike(btn, postId) {
            btn.disabled = true;
            try {
                const response = await fetch('api/like.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${postId}`
                });
                const result = await response.json();
                if (result.success) {
                    btn.nextElementSibling.textContent = result.new_count.toLocaleString();
                    btn.textContent = '❤️';
                    btn.classList.add('liked');
                }
            } catch (e) {
                console.error("Like sync failed");
            }
            btn.disabled = false;
        }
    </script>

    <?php include 'includes/navbar.php'; ?>
    <script src="shared/navbar.js"></script>

</body>

</html>