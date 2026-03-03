<?php
/**
 * UIU TalentHUB - Global Navbar
 * $root variable should be defined before including this file
 * $active_page variable can be set to highlight the current item (e.g. $active_page = "trending";)
 */
if (!isset($root)) {
    $root = "";
}
if (!isset($active_page)) {
    $active_page = "trending";
}

$nav_items = [
    ['id' => 'trending', 'icon' => 'assets/Logos/Trending_Page.svg', 'label' => 'Trending', 'path' => $root . 'index.php'],
    ['id' => 'audio', 'icon' => 'assets/Logos/Waves_Page.svg', 'label' => 'Waves', 'path' => $root . 'pages/audio/audiopage.php'],
    ['id' => 'video', 'icon' => 'assets/Logos/Visuals_Page.svg', 'label' => 'Visuals', 'path' => $root . 'pages/video/videopage.php'],
    ['id' => 'blog', 'icon' => 'assets/Logos/Journals_Page.svg', 'label' => 'Journals', 'path' => $root . 'pages/blog/blogpage.php']
];
?>

<footer class="bottom-bar">
    <div class="media-nav">
        <div class="nav-pill" id="global-nav-pill"></div>
        <?php foreach ($nav_items as $index => $item): ?>
            <a href="<?php echo $item['path']; ?>"
                class="nav-item <?php echo ($active_page == $item['id']) ? 'active' : ''; ?>"
                data-index="<?php echo $index; ?>" data-id="<?php echo $item['id']; ?>">
                <img src="<?php echo $root . $item['icon']; ?>" alt="<?php echo $item['label']; ?>" class="nav-icon">
                <span class="nav-label"><?php echo $item['label']; ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</footer>

<!-- Shared Portal for Seamless Transitions -->
<iframe id="page-portal" style="display:none;"></iframe>
<div id="navbar-container"></div>