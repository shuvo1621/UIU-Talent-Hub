/* spa navigation logic */

(function () {
    'use strict';

    const NAV_ITEMS = [
        { id: 'trending', icon: 'assets/Logos/Trending_Page.svg', label: 'Trending', path: 'index.php' },
        { id: 'audio', icon: 'assets/Logos/Waves_Page.svg', label: 'Waves', path: 'pages/audio/audiopage.php' },
        { id: 'video', icon: 'assets/Logos/Visuals_Page.svg', label: 'Visuals', path: 'pages/video/videopage.php' },
        { id: 'blog', icon: 'assets/Logos/Journals_Page.svg', label: 'Journals', path: 'pages/blog/blogpage.php' }
    ];

    let isNavigating = false;

    // path helpers
    // Get the base path of the project (e.g., /UIU%20TalentHub/)
    const getProjectBase = () => {
        const path = window.location.pathname;
        const searchStr = '/uiu talenthub/';
        const lowerPath = path.toLowerCase();
        const index = lowerPath.indexOf(searchStr);
        if (index !== -1) {
            return path.substring(0, index + searchStr.length);
        }
        // Fallback for root installations if any
        return '/';
    };

    const PROJECT_BASE = getProjectBase();
    console.log('[SPA] PROJECT_BASE identified as:', PROJECT_BASE);

    const getPath = () => decodeURIComponent(window.location.pathname).replace(/\\/g, '/').toLowerCase();

    // getRoot is now used for assets like images/css that might still need relative paths if not absolute
    const getRoot = () => {
        const path = getPath();
        const subDirs = ['audio page', 'video page', 'blog page', 'profile page', 'auth'];
        return subDirs.some(s => path.includes('/' + s + '/')) ? PROJECT_BASE : PROJECT_BASE;
    };

    const getActiveId = () => {
        const path = getPath();
        if (path.includes('index.php') || path.endsWith(PROJECT_BASE.toLowerCase())) return 'trending';
        if (path.includes('audio')) return 'audio';
        if (path.includes('video')) return 'video';
        if (path.includes('blog')) return 'blog';
        return 'trending';
    };

    // layout loaders
    function injectStyles() {
        const root = getRoot();
        if (document.querySelector(`link[href*="navbar.css"]`)) return;
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = root + 'shared/navbar.css';
        document.head.appendChild(link);
    }

    function syncPageAssets(newDoc, url) {
        const promises = [];

        // 1. Sync Styles
        newDoc.querySelectorAll('link[rel="stylesheet"]').forEach(link => {
            const href = link.getAttribute('href');
            if (href.includes('navbar.css')) return;
            const absoluteHref = new URL(href, url).href;
            if (!document.querySelector(`link[href="${absoluteHref}"]`)) {
                const newLink = document.createElement('link');
                newLink.rel = 'stylesheet';
                newLink.href = absoluteHref;
                document.head.appendChild(newLink);
            }
        });

        newDoc.querySelectorAll('style').forEach(style => {
            document.head.appendChild(style.cloneNode(true));
        });

        // 2. Sync External Scripts (e.g., Wavesurfer)
        newDoc.querySelectorAll('script[src]').forEach(script => {
            const src = script.getAttribute('src');
            // Skip navbar.js itself if re-encountered
            if (src.includes('navbar.js')) return;

            const absoluteSrc = new URL(src, url).href;

            if (!document.querySelector(`script[src="${absoluteSrc}"]`)) {
                const p = new Promise((resolve, reject) => {
                    const newScript = document.createElement('script');
                    newScript.src = absoluteSrc;
                    newScript.onload = () => {
                        console.log('[SPA] Loaded script:', absoluteSrc);
                        resolve();
                    };
                    newScript.onerror = () => {
                        console.error('[SPA] Failed to load script:', absoluteSrc);
                        resolve(); // Resolve anyway to not block navigation
                    };
                    document.head.appendChild(newScript);
                });
                promises.push(p);
            }
        });

        return Promise.all(promises);
    }

    function buildHeaderHTML() {
        const root = PROJECT_BASE;
        return `
            <header class="global-header">
                <div class="t-l">
                    <div class="logo-container">
                        <img src="${root}assets/images/UIUTELENTHUBLOGO.png" alt="UIU Logo">
                    </div>
                    <div class="brand-text">
                        <h5>UIU TalentHUB</h5>
                        <h6>Showcase Your Genius</h6>
                    </div>
                </div>
                <div class="t-r">
                    <a href="${root}pages/profile/Profile.php" class="profile-icon">
                        <img src="${root}assets/images/Icon00.png" alt="User">
                    </a>
                    <a href="${root}Auth/signup.php" class="join-btn">Join Now</a>
                </div>
            </header>
        `;
    }

    function buildNavbarHTML() {
        const activeId = getActiveId();
        const root = PROJECT_BASE;
        const itemsHTML = NAV_ITEMS.map((item, idx) => {
            const isActive = item.id === activeId;
            const href = root + item.path;
            return `
                <a href="${href}" class="nav-item ${isActive ? 'active' : ''}" data-index="${idx}" data-id="${item.id}">
                    <img src="${root}${item.icon}" alt="${item.label}" class="nav-icon">
                    <span class="nav-label">${item.label}</span>
                </a>
            `;
        }).join('');

        return `
            <footer class="bottom-bar">
                <div class="media-nav">
                    <div class="nav-pill"></div>
                    ${itemsHTML}
                </div>
            </footer>
            <iframe id="page-portal" style="display:none;"></iframe>
        `;
    }

    // 3. UI SYNC
    function updatePill(animate = true) {
        const active = document.querySelector('.nav-item.active');
        const pill = document.querySelector('.nav-pill');
        if (!active || !pill) return;
        const targetX = active.offsetLeft;
        if (!animate) pill.style.transition = 'none';
        pill.style.transform = `translateX(${targetX}px)`;
        pill.style.opacity = '1';
        if (!animate) requestAnimationFrame(() => pill.style.transition = '');
    }

    // --- Page Specific Initializers ---
    // Moved here to support SPA navigation
    function initHeroSlider() {
        const sliderWrapper = document.querySelector('.hero-banner .slider-wrapper');
        if (!sliderWrapper) return;

        console.log('[SPA] Initializing Hero Slider (Horizontal)...');

        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        if (slides.length === 0) return;

        let current = 0;
        const intervalTime = 3000;

        function showSlide(index) {
            // Calculate percentage based on 3 slides = 33.333% per slide shift relative to wrapper
            const percent = index * (100 / slides.length);
            sliderWrapper.style.transform = `translateX(-${percent}%)`;

            // Update dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });

            // Make it foolproof: Toggle active class on slides too
            // This ensures backwards compatibility if CSS is cached
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });

            current = index;
        }

        function nextSlide() {
            let next = (current + 1) % slides.length;
            showSlide(next);
        }

        // Global interval management attached to window to survive context but be clearable
        if (window.heroSliderInterval) clearInterval(window.heroSliderInterval);
        window.heroSliderInterval = setInterval(nextSlide, intervalTime);

        dots.forEach((dot, index) => {
            dot.addEventListener('click', (e) => {
                e.stopPropagation();
                if (window.heroSliderInterval) clearInterval(window.heroSliderInterval);
                showSlide(index);
                window.heroSliderInterval = setInterval(nextSlide, intervalTime);
            });
        });
    }

    // --- Audio Page Initializer ---
    function initAudioPage() {
        const cards = document.querySelectorAll('.audio-card-long');
        if (cards.length === 0) return;

        console.log('[SPA] Initializing Audio Page...');

        // Define global helpers for onclick events
        window.audioPlayers = window.audioPlayers || {};

        if (!window.WaveSurfer) {
            console.log("Wavesurfer not ready yet. Attempting lazy load...");
            const scriptUrl = 'https://unpkg.com/wavesurfer.js@7';

            // 1. Inject if missing
            if (!document.querySelector(`script[src="${scriptUrl}"]`)) {
                const s = document.createElement('script');
                s.src = scriptUrl;
                document.head.appendChild(s);
            }

            // 2. Poll for availability
            let attempts = 0;
            const checkInterval = setInterval(() => {
                attempts++;
                if (window.WaveSurfer) {
                    clearInterval(checkInterval);
                    console.log("Wavesurfer loaded via polling. Initializing...");
                    initAudioPage();
                } else if (attempts > 50) { // 5 seconds timeout
                    clearInterval(checkInterval);
                    console.error("Wavesurfer failed to load within timeout.");
                }
            }, 100);
            return; // Stop this run, wait for the next successful poll
        }

        cards.forEach(card => {
            const id = card.dataset.id;
            const url = card.dataset.url;
            const container = `#waveform-${id}`;

            // Clean up stale instance if exists (important for SPA re-mounting)
            if (window.audioPlayers[id]) {
                try {
                    window.audioPlayers[id].destroy();
                } catch (e) {
                    console.debug("Error destroying old player", e);
                }
                delete window.audioPlayers[id];
            }

            // Create a gradient for the progress bar
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 150);
            gradient.addColorStop(0, '#ff6a00');
            gradient.addColorStop(0.7, '#ff0000');
            gradient.addColorStop(1, '#800000');

            // Create a subtle gradient for the unplayed wave
            const waveGradient = ctx.createLinearGradient(0, 0, 0, 150);
            waveGradient.addColorStop(0, '#ddd');
            waveGradient.addColorStop(1, '#bbb');

            try {
                // Check if WaveSurfer is available
                if (typeof WaveSurfer === 'undefined') return;

                const wavesurfer = WaveSurfer.create({
                    container: container,
                    waveColor: waveGradient,
                    progressColor: gradient,
                    cursorColor: 'transparent',
                    barWidth: 4,
                    barGap: 3,
                    barRadius: 4,
                    height: 55,
                    responsive: true,
                    url: url,
                    normalize: true,
                });

                window.audioPlayers[id] = wavesurfer;

                wavesurfer.on('error', (e) => {
                    console.warn("Wavesurfer error for ID " + id, e);
                    const wrapper = document.querySelector(container);
                    if (wrapper) wrapper.innerHTML = '<span style="color:red; font-size:10px;">Audio fail</span>';
                });

                wavesurfer.on('finish', () => {
                    const btn = card.querySelector('.ac-play-btn');
                    if (btn) btn.textContent = '▶';
                    wavesurfer.seekTo(0);
                });

                wavesurfer.on('audioprocess', () => {
                    const currentTime = wavesurfer.getCurrentTime();
                    const el = card.querySelector('.time-current');
                    if (el) el.textContent = formatTime(currentTime);
                });

                wavesurfer.on('ready', () => {
                    const duration = wavesurfer.getDuration();
                    const el = card.querySelector('.time-total');
                    if (el) el.textContent = formatTime(duration);
                });

                wavesurfer.on('interaction', () => {
                    const currentTime = wavesurfer.getCurrentTime();
                    const el = card.querySelector('.time-current');
                    if (el) el.textContent = formatTime(currentTime);
                });

            } catch (err) {
                console.error("Initialization error:", err);
            }
        });

        // Attach global helpers if not already attached
        if (!window.togglePlay) {
            window.togglePlay = function (btn) {
                const card = btn.closest('.audio-card-long');
                const id = card.dataset.id;
                const ws = window.audioPlayers[id];

                if (ws && ws.isPlaying()) {
                    ws.pause();
                    btn.textContent = '▶';
                } else {
                    // Pause others
                    Object.values(window.audioPlayers).forEach(p => {
                        if (p !== ws && p && p.isPlaying()) {
                            p.pause();
                            // Reset other buttons
                            const key = Object.keys(window.audioPlayers).find(k => window.audioPlayers[k] === p);
                            const otherCard = document.querySelector(`.audio-card-long[data-id="${key}"]`);
                            if (otherCard) {
                                const b = otherCard.querySelector('.ac-play-btn');
                                if (b) b.textContent = '▶';
                            }
                        }
                    });
                    if (ws) {
                        ws.play();
                        btn.textContent = '⏸';
                    }
                }
            };
        }

        if (!window.formatTime) {
            window.formatTime = function (seconds) {
                const minutes = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
            };
        }

        if (!window.toggleLike) {
            window.toggleLike = async function (btn, postId) {
                const isLiked = btn.classList.toggle('liked');
                btn.textContent = isLiked ? '❤️' : '❤';

                const countSpan = btn.nextElementSibling;
                let count = parseInt(countSpan.textContent.replace(/,/g, ''));
                if (isNaN(count)) count = 0;
                countSpan.textContent = (isLiked ? count + 1 : Math.max(0, count - 1)).toLocaleString();

                try {
                    const response = await fetch('../api/like.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `post_id=${postId}`
                    });
                    const result = await response.json();
                    if (result.success) {
                        countSpan.textContent = result.new_count.toLocaleString();
                    } else {
                        btn.classList.toggle('liked');
                        btn.textContent = !isLiked ? '❤️' : '❤';
                        countSpan.textContent = count.toLocaleString();
                    }
                } catch (e) {
                    console.error("Like error", e);
                    btn.classList.toggle('liked');
                }
            };
        }

        if (!window.filterAudio) {
            window.currentFilter = null;
            window.filterAudio = function (category) {
                const cards = document.querySelectorAll('.audio-card-long');
                const catCards = document.querySelectorAll('.cat-card');

                if (window.currentFilter === category) {
                    window.currentFilter = null;
                    catCards.forEach(c => c.classList.remove('active'));
                } else {
                    window.currentFilter = category;
                    catCards.forEach(c => {
                        c.classList.toggle('active', c.dataset.filter === category);
                    });
                }

                cards.forEach(card => {
                    if (!window.currentFilter) {
                        card.style.display = 'flex';
                        return;
                    }
                    const cardCategory = card.dataset.category;
                    const match = (cardCategory === window.currentFilter);
                    card.style.display = match ? 'flex' : 'none';
                });
            };
        }
    }

    // 4. NAVIGATION ENGINE
    async function seamlessNavigate(url, targetId, direction) {
        console.log('[SPA] Navigating to:', url);
        if (isNavigating) return;
        isNavigating = true;

        const content = document.getElementById('page-content');
        const portal = document.getElementById('page-portal');
        if (!content || !portal) { window.location.href = url; return; }

        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.toggle('active', item.dataset.id === targetId);
        });
        updatePill(true);

        content.classList.remove('swish-in-left', 'swish-in-right', 'fade-up-in');
        content.classList.add(direction === 'right' ? 'swish-out-left' : 'swish-out-right');

        try {
            let newHTML = '';
            if (window.location.protocol !== 'file:') {
                const res = await fetch(url);
                newHTML = await res.text();
            } else {
                newHTML = await new Promise((resolve, reject) => {
                    portal.onload = () => { try { resolve(portal.contentDocument.documentElement.outerHTML); } catch (e) { reject(e); } };
                    portal.src = url;
                });
            }

            const parser = new DOMParser();
            const doc = parser.parseFromString(newHTML, 'text/html');
            const newContent = doc.querySelector('#page-content') || doc.querySelector('.container') || doc.body;

            setTimeout(async () => {
                await syncPageAssets(doc, url);
                document.title = doc.title;

                // CRITICAL: Filter out any existing shell parts to prevent duplication
                const incomingContent = newContent.cloneNode(true);
                const cleaners = incomingContent.querySelectorAll('.global-header, .utop, .navbar, .bottom-bar, #navbar-container, #header-container');
                cleaners.forEach(c => c.remove());

                content.innerHTML = incomingContent.innerHTML;

                content.className = '';
                content.id = 'page-content';
                content.classList.add(direction === 'right' ? 'swish-in-right' : 'swish-in-left');

                history.pushState({ url, targetId }, '', url);
                isNavigating = false;

                // Re-init page scripts
                initHeroSlider();
                initAudioPage(); // Call audio init

                window.dispatchEvent(new Event('DOMContentLoaded'));
            }, 250);

        } catch (e) {
            window.location.href = url;
        }
    }

    // 5. INITIALIZATION
    function boot() {
        injectStyles();

        // 1. Identify/Create Containers EXPLICITLY outside the content wrapper
        let hContainer = document.getElementById('header-container');
        let nContainer = document.getElementById('navbar-container');

        // Force move them to body if they were accidentally wrapped
        if (hContainer && hContainer.parentElement !== document.body) document.body.appendChild(hContainer);
        if (nContainer && nContainer.parentElement !== document.body) document.body.appendChild(nContainer);

        if (!hContainer) {
            hContainer = document.createElement('div');
            hContainer.id = 'header-container';
            document.body.prepend(hContainer);
        }
        if (!nContainer) {
            nContainer = document.createElement('div');
            nContainer.id = 'navbar-container';
            document.body.appendChild(nContainer);
        }

        // 2. Wrap Page Content if not already wrapped
        if (!document.getElementById('page-content')) {
            const wrapper = document.createElement('div');
            wrapper.id = 'page-content';
            wrapper.classList.add('fade-up-in');
            const nodes = Array.from(document.body.childNodes);
            nodes.forEach(n => {
                if (n !== hContainer && n !== nContainer && n.tagName !== 'SCRIPT') {
                    wrapper.appendChild(n);
                }
            });
            document.body.prepend(wrapper);
        }

        // 3. Clear legacy page headers (only if not our global-header)
        const oldHeaders = document.querySelectorAll('.utop, .navbar, header:not(.global-header)');
        oldHeaders.forEach(h => {
            if (h.closest('#page-content')) h.remove();
        });

        // 4. Hydrate containers
        if (!document.querySelector('.global-header')) hContainer.innerHTML = buildHeaderHTML();
        if (!document.querySelector('.bottom-bar')) nContainer.innerHTML = buildNavbarHTML();

        // 5. Global Listener
        if (!window.navEventsBound) {
            document.body.addEventListener('click', (e) => {
                const link = e.target.closest('.nav-item');
                if (!link || isNavigating) return;
                e.preventDefault();
                const targetId = link.dataset.id;
                const currentActive = document.querySelector('.nav-item.active');
                if (currentActive && targetId === currentActive.dataset.id) return;
                const currIdx = currentActive ? parseInt(currentActive.dataset.index) : 0;
                const direction = parseInt(link.dataset.index) > currIdx ? 'right' : 'left';
                seamlessNavigate(link.href, targetId, direction);
            });
            window.navEventsBound = true;
        }

        window.addEventListener('popstate', () => location.reload());
        window.addEventListener('resize', () => updatePill(false));
        setTimeout(() => updatePill(false), 100);

        // Initial Page Script Run
        initHeroSlider();
        initAudioPage();
    }

    boot();

})();
