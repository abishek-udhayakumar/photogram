/**
 * Photogram Comments System (Rebuilt)
 * 
 * Logic Flow:
 * 1. Event Delegation for Clicks (View All, Delete) and Submits (Modal & Inline Forms).
 * 2. Unified API Handling (fetch, post, delete).
 * 3. DOM Updates strictly based on Server Response.
 */

document.addEventListener('DOMContentLoaded', () => {
    CommentSystem.init();
});

const CommentSystem = {
    modalInstance: null,
    currentModalPostId: null,

    init() {
        // Initialize Bootstrap Modal
        const modalEl = document.getElementById('commentsModal');
        if (modalEl) {
            this.modalInstance = new bootstrap.Modal(modalEl);

            // Events for focus and cleanup
            modalEl.addEventListener('shown.bs.modal', () => {
                const input = document.getElementById('modalCommentInput');
                if (input) input.focus();
            });
            modalEl.addEventListener('hidden.bs.modal', () => {
                this.currentModalPostId = null;
                document.getElementById('modalCommentsList').innerHTML = '';
            });
        }

        this.bindEvents();
    },

    bindEvents() {
        // 1. Click Handling (Delegation)
        document.addEventListener('click', (e) => {
            // Open Modal
            if (e.target.closest('[data-action="view-all-comments"]')) {
                e.preventDefault();
                const postId = e.target.closest('[data-action="view-all-comments"]').getAttribute('data-post-id');
                this.openModal(postId);
            }
            // Focus Comment Input
            if (e.target.closest('[data-action="focus-comment"]')) {
                e.preventDefault();
                const postId = e.target.closest('[data-action="focus-comment"]').getAttribute('data-post-id');
                // Use inline focus if available, else modal
                const inlineInput = document.querySelector(`form[data-post-id="${postId}"] input`);
                if (inlineInput) inlineInput.focus();
                else this.openModal(postId);
            }
        });

        // 2. Form Submission (Delegation)
        document.addEventListener('submit', (e) => {
            // Feed Inline Form
            if (e.target.matches('[data-action="submit-comment"]')) {
                e.preventDefault();
                this.handleCommentSubmit(e.target, 'inline');
            }
            // Modal Form
            if (e.target.id === 'modalCommentForm') {
                e.preventDefault();
                this.handleCommentSubmit(e.target, 'modal');
            }
        });

        // 3. Input validation
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('comment-input') || e.target.id === 'modalCommentInput') {
                const btn = e.target.closest('form').querySelector('button');
                if (btn) btn.disabled = e.target.value.trim().length === 0;
            }
        });
    },

    openModal(postId) {
        if (!this.modalInstance) return;
        this.currentModalPostId = postId;

        // 1. Populate Static Info (from Feed Card)
        const card = document.querySelector(`.card-feed[data-post-id="${postId}"]`);
        if (card) {
            const imgEl = card.querySelector('.post-image-content');
            const nameEl = card.querySelector('.username-link');
            const avatarEl = card.querySelector('.user-avatar-md');

            if (imgEl) document.getElementById('modalPostImage').src = imgEl.src;
            if (nameEl) document.getElementById('modalOwnerName').innerText = nameEl.innerText;
            if (avatarEl) document.getElementById('modalOwnerAvatar').src = avatarEl.src;

            // Likes count is dynamic, we could fetch or grab from DOM
            const likeCount = card.querySelector('.likes-count span');
            if (likeCount) document.getElementById('modalLikeCount').innerText = likeCount.innerText;
        }

        // 2. Show Modal
        this.modalInstance.show();

        // 3. Fetch Comments
        this.fetchComments(postId);
    },

    async fetchComments(postId) {
        const list = document.getElementById('modalCommentsList');
        if (!list) return;

        list.innerHTML = '<div class="d-flex justify-content-center mt-5"><div class="spinner-border text-primary spinner-border-sm"></div></div>';

        try {
            const res = await fetch(`/api/fetch_comments.php?post_id=${postId}&mode=all`);
            const data = await res.json();

            if (data.status === 'success') {
                list.innerHTML = '';
                if (data.data.length === 0) {
                    list.innerHTML = `
                        <div class="text-center mt-5">
                            <div class="fw-bold text-dark">No comments yet.</div>
                            <small class="text-muted">Start the conversation.</small>
                        </div>`;
                } else {
                    data.data.forEach(c => {
                        list.appendChild(this.buildCommentElement(c));
                    });
                    list.scrollTop = list.scrollHeight;
                }
            } else {
                list.innerHTML = '<div class="text-center text-danger mt-5">Failed to load.</div>';
            }
        } catch (e) {
            console.error(e);
            list.innerHTML = '<div class="text-center text-danger mt-5">Error loading comments.</div>';
        }
    },

    async handleCommentSubmit(form, mode) {
        const input = form.querySelector('input');
        const btn = form.querySelector('button');
        const text = input.value.trim();

        let postId = null;
        if (mode === 'inline') {
            postId = form.getAttribute('data-post-id');
        } else {
            postId = this.currentModalPostId;
        }

        if (!text || !postId) return;

        // UI Loading
        const originalText = btn.innerHTML;
        input.disabled = true;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        try {
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('comment', text);

            const res = await fetch('/api/comment.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                input.value = '';
                // Ensure data has can_delete=true for the author
                data.data.can_delete = true;

                // 1. Update Counts everywhere
                this.updateCounts(postId, data.new_count);

                // 2. Render in Modal if open
                const modalList = document.getElementById('modalCommentsList');
                if (modalList && this.currentModalPostId == postId) {
                    // Remove empty state
                    if (modalList.querySelector('.text-center')) modalList.innerHTML = '';
                    const newEl = this.buildCommentElement(data.data);
                    // Highlight
                    newEl.style.backgroundColor = "rgba(99, 102, 241, 0.1)";
                    modalList.appendChild(newEl);
                    modalList.scrollTop = modalList.scrollHeight;
                    setTimeout(() => newEl.style.backgroundColor = 'transparent', 2000);
                }

                // 3. Render in Preview (Feed)
                // We keep only top 2 usually? Or just append? 
                // Let's just append to the preview container if it exists
                const previewDiv = document.getElementById(`comments-preview-${postId}`);
                if (previewDiv) {
                    const previewHtml = `
                        <div class="d-flex align-items-baseline mb-1 fade-in">
                            <span class="fw-bold me-2 small">${data.data.username}</span>
                            <span class="text-secondary small text-truncate" style="max-width: 250px;">${data.data.comment}</span>
                        </div>`;
                    previewDiv.insertAdjacentHTML('beforeend', previewHtml);
                }

            } else {
                alert(data.message || 'Error posting');
            }
        } catch (e) {
            console.error(e);
            alert('Connection Error');
        } finally {
            input.disabled = false;
            btn.disabled = false; // re-enable but it should ideally be disabled if empty?
            // Actually, we cleared the value, so we should allow user to type again.
            // But validation listener only fires on input. So we should force disable?
            // Let's just create a synthetic event or set disabled=true.
            if (input.value.length === 0) btn.disabled = true;
            input.focus();
            btn.innerHTML = originalBtnText;
        }
    },

    // Delete Comment
    async deleteComment(commentId, btnEl) {
        if (!confirm("Delete this comment?")) return;

        const item = btnEl.closest('.comment-item-modal');
        if (item) item.style.opacity = '0.5';

        try {
            const formData = new FormData();
            formData.append('comment_id', commentId);

            const res = await fetch('/api/delete_comment.php', { method: 'POST', body: formData });
            const data = await res.json();

            if (data.status === 'success') {
                if (item) {
                    item.remove();
                }
                // Update Count if we know the post ID
                // We can get it from specific logic or just trust the Modal is open
                if (this.currentModalPostId && data.new_count !== undefined) {
                    this.updateCounts(this.currentModalPostId, data.new_count);
                }
            } else {
                alert('Failed to delete');
                if (item) item.style.opacity = '1';
            }
        } catch (e) {
            console.error(e);
            if (item) item.style.opacity = '1';
        }
    },

    buildCommentElement(data) {
        const div = document.createElement('div');
        div.className = 'd-flex align-items-start mb-3 comment-item-modal fade-in';
        // We pass 'this' to onclick to refer to the global scope usually, 
        // but here we are inside an object. We need to attach the function globally or use event delegation for delete too.
        // Let's use inline onclick pointing to a global wrapper OR upgrade delegation.
        // EASIER: Global wrapper.

        const deleteBtn = data.can_delete ?
            `<button class="btn btn-link text-secondary p-0 ms-2 small" onclick="CommentSystem.deleteComment(${data.id}, this)" title="Delete">
                <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
             </button>` : '';

        div.innerHTML = `
            <a href="/user/profile.php?username=${data.username}">
                <img src="${data.profile_pic}" class="rounded-circle me-3 border" width="36" height="36" style="object-fit:cover;">
            </a>
            <div class="flex-grow-1">
                <div class="d-inline-block bg-light rounded-3 px-3 py-2">
                    <a href="/user/profile.php?username=${data.username}" class="fw-bold text-dark text-decoration-none me-1 small">${data.username}</a>
                    <span class="text-dark small">${data.comment}</span>
                </div>
                <div class="d-flex align-items-center mt-1 ms-1">
                     <span class="text-muted small" style="font-size:0.7rem;">${data.time_ago}</span>
                     ${deleteBtn}
                </div>
            </div>
        `;
        return div;
    },

    updateCounts(postId, newCount) {
        // Feed Link
        const link = document.querySelector(`.card-feed[data-post-id="${postId}"] .view-all-link`);
        const container = document.getElementById(`post-comments-section-${postId}`);

        if (newCount > 2) {
            if (link) {
                link.innerText = `View all ${newCount} comments`;
            } else if (container) {
                // Create it
                const newLink = document.createElement('a');
                newLink.href = '#';
                newLink.className = 'text-secondary small fw-medium text-decoration-none mb-2 d-block view-all-link';
                newLink.setAttribute('data-action', 'view-all-comments');
                newLink.setAttribute('data-post-id', postId);
                newLink.innerText = `View all ${newCount} comments`;
                container.prepend(newLink);
            }
        } else {
            if (link) link.remove();
        }
    }
};

// Expose to window for inline onclicks if needed (though we prefer delegation)
window.CommentSystem = CommentSystem;
