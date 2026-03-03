/**
 * Like System with Dynamic Re-sorting
 * Updates database and re-orders posts by like count
 */

function toggleLike(button, postId) {
    // Prevent multiple clicks
    if (button.disabled) return;
    button.disabled = true;

    // Send like request to API
    fetch('/UIU TalentHub/api/like.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the like count display
                const likeCount = button.nextElementSibling;
                likeCount.textContent = data.new_count.toLocaleString();

                // Toggle the liked state
                button.classList.toggle('liked');
                button.textContent = button.classList.contains('liked') ? '❤️' : '❤';

                // Re-sort the posts by like count
                resortPostsByLikes();
            }
            button.disabled = false;
        })
        .catch(error => {
            console.error('Like error:', error);
            button.disabled = false;
        });
}

function resortPostsByLikes() {
    const audioSection = document.querySelector('.audio-section');
    if (!audioSection) return;

    // Get all audio cards
    const cards = Array.from(audioSection.querySelectorAll('.audio-card-long'));

    // Sort by like count (descending)
    cards.sort((a, b) => {
        const likesA = parseInt(a.querySelector('.like-count').textContent.replace(/,/g, '')) || 0;
        const likesB = parseInt(b.querySelector('.like-count').textContent.replace(/,/g, '')) || 0;
        return likesB - likesA;
    });

    // Re-append in sorted order
    cards.forEach(card => {
        audioSection.appendChild(card);
    });

    // Add a subtle animation to show re-sorting
    cards.forEach((card, index) => {
        card.style.animation = 'none';
        setTimeout(() => {
            card.style.animation = 'fadeIn 0.3s ease-in-out';
        }, index * 50);
    });
}

// Add fade-in animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from { opacity: 0.5; transform: translateX(-10px); }
        to { opacity: 1; transform: translateX(0); }
    }
`;
document.head.appendChild(style);
