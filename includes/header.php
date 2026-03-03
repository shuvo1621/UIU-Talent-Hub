<?php
/*
 * top bar
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($root)) {
    $root = "";
}

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $is_logged_in ? $_SESSION['user_name'] : "";
?>
<header class="global-header">
    <div class="t-l">
        <div class="logo-container">
            <img src="<?php echo $root; ?>assets/images/UIUTELENTHUBLOGO.png" alt="UIU Logo">
        </div>
        <div class="brand-text">
            <h5>UIU TalentHUB</h5>
            <h6>Showcase Your Genius</h6>
        </div>
    </div>
    <div class="t-r">
        <?php if ($is_logged_in): ?>
            <div class="user-greeting">
                <span class="greeting-text">Hi, <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>!</span>
                <a href="<?php echo $root; ?>pages/profile/Profile.php" class="header-profile-link">
                    <?php
                    $student_id = $_SESSION['student_id'] ?? 'default';
                    $colors = ['#ff6b6b', '#4facfe', '#66bb6a', '#ffa726', '#ab47bc', '#26a69a', '#ec407a', '#5c6bc0'];
                    $color_index = hexdec(substr(md5($student_id), 0, 8)) % count($colors);
                    $avatar_color = $colors[$color_index];
                    $initial = strtoupper(substr($user_name, 0, 1));
                    ?>
                    <div class="header-avatar" style="background-color: <?php echo $avatar_color; ?>">
                        <?php echo $initial; ?>
                    </div>
                </a>
                <a href="<?php echo $root; ?>Auth/logout.php" class="logout-btn">Logout</a>
            </div>
        <?php else: ?>
            <a href="<?php echo $root; ?>Auth/signup.php" class="join-btn">Join Now</a>
        <?php endif; ?>
    </div>
</header>
<div id="header-container"></div>

<style>
    .user-greeting {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .header-profile-link {
        text-decoration: none;
        transition: transform 0.2s;
    }

    .header-profile-link:hover {
        transform: scale(1.1);
    }

    .header-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        font-weight: 800;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        border: 2px solid white;
    }

    .greeting-text {
        font-size: 14px;
        font-weight: 600;
        color: #444;
        padding: 8px 14px;
        background: #f8f9fa;
        border-radius: 20px;
    }

    .logout-btn {
        font-size: 13px;
        color: #ff5e00;
        text-decoration: none;
        font-weight: 700;
        border: 2px solid #ff5e00;
        padding: 7px 16px;
        border-radius: 22px;
        transition: all 0.3s ease;
    }

    .logout-btn:hover {
        background: #ff5e00;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 3px 10px rgba(255, 94, 0, 0.25);
    }
</style>