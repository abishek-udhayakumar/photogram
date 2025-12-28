/**
 * Photogram Comments System
 * Modal-Based Architecture
 */

let commentsModalInstance = null;
let currentModalPostId = null;

document.addEventListener('DOMContentLoaded', () => {
    // Initialize interaction
    setupEventDelegation();

    // Initialize Modal Instance (Bootstrap 5)
    const modalEl = document.getElementById('commentsModal');
    if (modalEl) {
        commentsModalInstance = new bootstrap.Modal(modalEl);

        // Focus input after open
        modalEl.addEventListener('shown.bs.modal', () => {
            const input = document.getElementById('modalCommentInput');
            if (input) input.focus();
        });

        // Cleanup on close
        modalEl.addEventListener('hidden.bs.modal', () => {
            currentModalPostId = null;
            const list = document.getElementById('modalCommentsList');
            if (list) list.innerHTML = ''; // Clear for next time
            const img = document.getElementById('modalPostImage');
            if (img) img.src = '';
            // Reset spinner or empty states if needed
        });
    }
});

function setupEventDelegation() {
    // 1. Click Handler (View All, Comment Icon)
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-action="view-all-comments"], [data-action="focus-comment"]');
        if (trigger) {
            e.preventDefault();
            const postId = trigger.getAttribute('data-post-id');
            // Check if we are using Modal or Inline. The requirement is Modal.
            openCommentModal(postId, trigger);
        }
    });

    // 2. Modal Form Submit
    const modalForm = document.getElementById('modalCommentForm');
    if (modalForm) {
        modalForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (currentModalPostId) {
                submitModalComment(currentModalPostId);
            }
        });

        // Input Validation
        const input = document.getElementById('modalCommentInput');
        const btn = modalForm.querySelector('button');
        if (input && btn) {
            input.addEventListener('input', () => {
                btn.disabled = input.value.trim().length === 0;
            });
        }
    }
}

function openCommentModal(postId, triggerEl) {
    if (!commentsModalInstance) return;

    currentModalPostId = postId;

    // 1. Get Data from DOM (Post Card)
    // We try to find the card using robust selectors
    const card = document.querySelector(`.card[data-post-id="${postId}"]`) || document.getElementById(`post-${postId}`);
    if (!card) {
        console.error("Post card not found for ID", postId);
        return;
    }

    const imgEl = card.querySelector('.post-image-content');
    const nameEl = card.querySelector('.username-link');
    const avatarEl = card.querySelector('.user-avatar-md');

    // 2. Populate Modal Static Content
    const modalImg = document.getElementById('modalPostImage');
    const modalName = document.getElementById('modalOwnerName');
    const modalAvatar = document.getElementById('modalOwnerAvatar');

    if (modalImg && imgEl) modalImg.src = imgEl.src;
    if (modalName && nameEl) modalName.innerText = nameEl.innerText;
    if (modalAvatar && avatarEl) modalAvatar.src = avatarEl.src;

    // 3. Show Modal
    commentsModalInstance.show();

    // 4. Load Comments
    // Call the loading function which MUST handle the spinner removal
    loadModalComments(postId);
}

async function loadModalComments(postId) {
    const list = document.getElementById('modalCommentsList');
    if (!list) return;

    // Reset List & Show Loader
    list.innerHTML = '<div class="d-flex justify-content-center mt-5"><div class="spinner-border text-muted"></div></div>';

    try {
        const res = await fetch(`/api/fetch_comments.php?post_id=${postId}&mode=all`);

        // Strict Check: Response must be OK and JSON
        if (!res.ok) throw new Error(`HTTP Error ${res.status}`);

        const contentType = res.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Invalid response format (not JSON)");
        }

        const data = await res.json();

        if (data.status === 'success') {
            list.innerHTML = ''; // Clear loader

            if (data.data.length === 0) {
                list.innerHTML = `
                    <div class="text-center mt-5">
                        <div class="fw-bold">No comments yet.</div>
                        <div class="text-muted small">Start the conversation.</div>
                    </div>
                 `;
            } else {
                data.data.forEach(c => {
                    list.appendChild(createModalCommentItem(c));
                });
                // Auto scroll to bottom
                list.scrollTop = list.scrollHeight;
            }
        } else {
            throw new Error(data.message || "API Error");
        }
    } catch (e) {
        console.error("Load Comments Error:", e);
        // Ensure Spinner is GONE and error shown
        list.innerHTML = `<div class="text-center text-danger mt-5">
                            <div>Failed to load comments</div>
                            <small class="text-muted">${e.message}</small>
                          </div>`;
    }
}

async function submitModalComment(postId) {
    const input = document.getElementById('modalCommentInput');
    const btn = document.querySelector('#modalCommentForm button');
    const text = input.value.trim();
    if (!text) return;

    // Loading State
    input.disabled = true;
    if (btn) {
        btn.disabled = true;
        var originalBtnText = btn.innerText;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }

    try {
        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('comment', text);

        const res = await fetch('/api/comment.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.status === 'success') {
            input.value = '';
            const list = document.getElementById('modalCommentsList');
            // Remove empty state
            if (list.querySelector('.text-center')) list.innerHTML = '';

            // Append new comment
            list.appendChild(createModalCommentItem(data.data)); // data.data is the new comment object
            list.scrollTop = list.scrollHeight;
        } else {
            alert(data.message || 'Error posting.');
        }
    } catch (e) {
        console.error(e);
        alert('Failed to post comment. Check connection.');
    } finally {
        input.disabled = false;
        input.focus();
        if (btn) {
            btn.innerHTML = originalBtnText || 'Post';
            btn.disabled = true; // Input is empty now
        }
    }
}

function createModalCommentItem(data) {
    const div = document.createElement('div');
    div.className = 'd-flex align-items-start mb-3 comment-item-modal';
    div.innerHTML = `
        <a href="/user/profile.php?username=${data.username}">
            <img src="${data.profile_pic}" class="rounded-circle me-3" width="32" height="32" style="object-fit:cover;">
        </a>
        <div>
            <div class="d-inline-block">
                <a href="/user/profile.php?username=${data.username}" class="fw-bold text-dark text-decoration-none me-1" style="font-size:0.9rem;">${data.username}</a>
                <span class="text-dark" style="font-size:0.9rem;">${data.comment}</span>
            </div>
            <div class="text-muted small mt-1" style="font-size:0.75rem;">${data.time_ago}</div>
        </div>
    `;
    return div;
}
