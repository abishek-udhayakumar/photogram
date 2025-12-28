/**
 * Photogram Likes System
 * Handles "Liked By" modal functionality
 */

document.addEventListener('DOMContentLoaded', () => {
    LikesSystem.init();
});

const LikesSystem = {
    modalInstance: null,

    init() {
        const modalEl = document.getElementById('usersModal');
        if (modalEl) {
            this.modalInstance = new bootstrap.Modal(modalEl);

            // Cleanup (Optional)
            modalEl.addEventListener('hidden.bs.modal', () => {
                document.getElementById('usersList').innerHTML = '';
            });
        }
        this.bindEvents();
    },

    bindEvents() {
        // Use Delegation for "View Likes" clicks
        document.addEventListener('click', (e) => {
            // Check for direct click or closest parent
            // We support data-action="view-likes" on the container div usually
            const trigger = e.target.closest('[onclick*="openUserList"]');
            // Note: The existing HTML uses inline onclick="openUserList(...)".
            // We should ideally replace that with data attribs, but let's support a global function for backward compat with my plan.
        });
    },

    openLikesModal(postId) {
        if (!this.modalInstance) return;

        // 1. Set Title
        document.getElementById('usersModalTitle').innerText = 'Likes';

        // 2. Show Modal
        this.modalInstance.show();

        // 3. Fetch Data
        this.fetchLikes(postId);
    },

    async fetchLikes(postId) {
        const list = document.getElementById('usersList');
        // Loader
        list.innerHTML = '<div class="d-flex justify-content-center p-4"><div class="spinner-border text-primary spinner-border-sm"></div></div>';

        try {
            const res = await fetch(`/api/fetch_likes.php?post_id=${postId}`);
            const data = await res.json();

            if (data.status === 'success') {
                list.innerHTML = '';
                if (data.data.length === 0) {
                    list.innerHTML = '<div class="text-center p-4 text-muted small">No likes yet.</div>';
                } else {
                    data.data.forEach(user => {
                        list.appendChild(this.createUserElement(user));
                    });
                }
            } else {
                list.innerHTML = '<div class="text-center p-4 text-danger small">Failed to load.</div>';
            }
        } catch (e) {
            console.error(e);
            list.innerHTML = '<div class="text-center p-4 text-danger small">Error loading.</div>';
        }
    },

    createUserElement(user) {
        const div = document.createElement('div');
        div.className = 'list-group-item d-flex align-items-center justify-content-between border-0 px-3 py-2 fade-in';

        // Follow Button Logic
        let actionBtn = '';
        if (!user.is_self) {
            if (user.is_following) {
                actionBtn = `<button class="btn btn-sm btn-outline-secondary px-3 fw-bold rounded-pill" onclick="toggleFollow(${user.id}, this)">Following</button>`;
            } else {
                // Check Mutual for social proof text? 
                // Maybe the button just says "Follow Back" if they follow me?
                let btnText = 'Follow';
                if (user.is_followed_by && !user.is_following) {
                    btnText = 'Follow Back';
                }
                actionBtn = `<button class="btn btn-sm btn-primary px-3 fw-bold rounded-pill" onclick="toggleFollow(${user.id}, this)">${btnText}</button>`;
            }
        }

        // Mutual Badge
        let mutualBadge = '';
        if (user.is_mutual) {
            mutualBadge = `<span class="badge bg-light text-secondary border ms-2" style="font-size: 0.65rem; font-weight: 600;">Mutual</span>`;
        }

        div.innerHTML = `
            <div class="d-flex align-items-center">
                <a href="/user/profile.php?username=${user.username}" class="text-decoration-none">
                    <img src="${user.profile_pic}" class="rounded-circle border me-3" width="40" height="40" style="object-fit:cover;">
                </a>
                <div>
                    <div class="d-flex align-items-center">
                        <a href="/user/profile.php?username=${user.username}" class="text-decoration-none text-dark fw-bold d-block lh-1 small">${user.username}</a>
                        ${mutualBadge}
                    </div>
                    <span class="text-muted small">${user.full_name}</span>
                </div>
            </div>
            ${actionBtn}
        `;
        return div;
    }
};

// Global Exposure for Inline OnClick in index.php
window.openUserList = function (type, id) {
    if (type === 'likes') {
        LikesSystem.openLikesModal(id);
    }
};
