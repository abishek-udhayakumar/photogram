/* assets/js/main.js */

// Basic Double Tap Logic
function handleDoubleTap(postId, container) {
    const heart = container.querySelector('.heart-burst');
    // Trigger animation
    heart.classList.add('active');
    setTimeout(() => heart.classList.remove('active'), 1000);

    // Call toggle like if NOT already liked
    // Note: This relies on the button state. We need to find the like button.
    const btn = document.querySelector(`.card-feed[data-post-id="${postId}"] .like-btn`);
    if (btn && !btn.classList.contains('liked')) {
        toggleLike(postId);
    }
}

async function toggleLike(postId) {
    // Selector for new Premium structure
    let btn = document.querySelector(`.card-feed[data-post-id="${postId}"] .like-btn`);

    if (!btn) {
        console.error(`Like button not found for post ${postId}`);
        return;
    }

    const icon = btn.querySelector('i');
    const countSpan = document.getElementById(`like-count-${postId}`);

    if (!countSpan) return;

    // Optimistic UI update
    const isLiked = btn.classList.contains('liked');
    let currentCount = parseInt(countSpan.innerText.replace(/,/g, '')) || 0;

    if (isLiked) {
        btn.classList.remove('liked');
        if (icon) {
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
        }
        countSpan.innerText = (Math.max(0, currentCount - 1)).toLocaleString();
    } else {
        btn.classList.add('liked');
        // Add subtle pop effect to button
        btn.style.transform = "scale(1.2)";
        setTimeout(() => btn.style.transform = "scale(1)", 200);

        if (icon) {
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
        }
        countSpan.innerText = (currentCount + 1).toLocaleString();
    }

    const formData = new FormData();
    formData.append('post_id', postId);

    try {
        const res = await fetch('/api/like.php', {
            method: 'POST',
            body: formData
        });
        // We generally trust the optimistic update, but could revert on error.
    } catch (e) {
        console.error(e);
    }
}

function focusComment(postId) {
    const input = document.querySelector(`.post-card[data-post-id="${postId}"] input`);
    if (input) input.focus();
}

async function postComment(e, postId) {
    e.preventDefault();
    const form = e.target;
    const input = form.querySelector('input');
    const comment = input.value;

    if (!comment) return;

    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('comment', comment);

    try {
        const res = await fetch('/api/comment.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.status === 'success') {
            input.value = '';
            // Ideally append comment to DOM directly here
            alert('Comment posted!');
        }
    } catch (e) {
        console.error(e);
    }
}

async function toggleFollow(userId, btn) {
    const formData = new FormData();
    formData.append('user_id', userId);

    const originalText = btn.innerText;
    btn.innerText = 'Loading...';
    btn.disabled = true;

    try {
        const res = await fetch('/api/follow.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.status === 'success') {
            if (data.action === 'followed') {
                btn.innerText = 'Unfollow';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-secondary');

                // Update count if on profile
                const countSpan = document.getElementById('follower-count');
                if (countSpan) countSpan.innerText = parseInt(countSpan.innerText) + 1;

            } else {
                btn.innerText = 'Follow';
                btn.classList.remove('btn-secondary');
                btn.classList.add('btn-primary');

                const countSpan = document.getElementById('follower-count');
                if (countSpan) countSpan.innerText = parseInt(countSpan.innerText) - 1;
            }
        } else {
            btn.innerText = originalText;
        }
    } catch (e) {
        btn.innerText = originalText;
    } finally {
        btn.disabled = false;
    }
}

// Modal Logic
let userListModal;
async function openUserList(type, userId) {
    if (!userListModal) {
        userListModal = new bootstrap.Modal(document.getElementById('userListModal'));
    }

    const title = type.charAt(0).toUpperCase() + type.slice(1);
    document.getElementById('userListModalLabel').innerText = title;
    document.getElementById('userListBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

    userListModal.show();

    try {
        const res = await fetch(`/api/get_users.php?type=${type}&user_id=${userId}`);
        const data = await res.json();

        if (data.status === 'success') {
            let html = '<div class="list-group list-group-flush">';
            if (data.data.length === 0) {
                html += '<div class="text-center py-4 text-muted">No users found.</div>';
            } else {
                data.data.forEach(u => {
                    html += `
                        <div class="list-group-item border-0 d-flex align-items-center px-0 py-2">
                             <a href="/user/profile.php?username=${u.username}" class="d-flex align-items-center text-decoration-none text-dark flex-grow-1">
                                <img src="/uploads/${u.profile_pic || 'default_avatar.png'}" class="rounded-circle me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                <div>
                                    <div class="fw-bold small">${u.username}</div>
                                    <div class="text-muted small">${u.full_name}</div>
                                </div>
                             </a>
                             ${u.id != userId ? // Not yourself logic (if viewer is viewer) - logic simplified
                            `<button class="btn btn-sm ${u.is_following ? 'btn-secondary' : 'btn-primary'} fw-bold" onclick="toggleFollow(${u.id}, this)">
                                    ${u.is_following ? 'Unfollow' : 'Follow'}
                                </button>`
                            : ''}
                        </div>
                    `;
                });
            }
            html += '</div>';
            document.getElementById('userListBody').innerHTML = html;
        }
    } catch (e) {
        console.error(e);
        document.getElementById('userListBody').innerHTML = '<div class="text-center text-danger">Failed to load.</div>';
    }
}
