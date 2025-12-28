/**
 * Real-time Post Creation Logic
 * Handles modal interactions, preview, and AJAX submission.
 */

document.addEventListener('DOMContentLoaded', () => {
    CreatePost.init();
});

const CreatePost = {
    init() {
        this.form = document.getElementById('createPostForm');
        this.input = document.getElementById('modalImageInput');
        this.previewState = document.getElementById('previewState');
        this.uploadState = document.getElementById('uploadState');
        this.previewImg = document.getElementById('modalImagePreview');
        this.clearBtn = document.getElementById('clearImageBtn');
        this.shareBtn = document.getElementById('sharePostBtn');
        this.modal = new bootstrap.Modal(document.getElementById('createPostModal'));

        if (!this.form) return;

        this.bindEvents();
    },

    bindEvents() {
        // Image Selection
        this.input.addEventListener('change', (e) => this.handleFileSelect(e));

        // Clear Image
        this.clearBtn.addEventListener('click', () => this.resetForm());

        // Caption Input (Enable Share button)
        this.form.querySelector('textarea').addEventListener('input', (e) => {
            const len = e.target.value.length;
            document.getElementById('captionCount').innerText = len.toLocaleString();
            // Button is enabled if file is selected (which happens in handleFileSelect)
        });

        // Submit
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    },

    handleFileSelect(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.onload = (ev) => {
                this.previewImg.src = ev.target.result;
                this.uploadState.classList.add('d-none');
                this.previewState.classList.remove('d-none');
                this.shareBtn.disabled = false;
            };

            reader.readAsDataURL(file);
        }
    },

    resetForm() {
        this.form.reset();
        this.previewImg.src = '#';
        this.previewState.classList.add('d-none');
        this.uploadState.classList.remove('d-none');
        this.shareBtn.disabled = true;
    },

    async handleSubmit(e) {
        e.preventDefault();

        // Loading State
        const originalText = this.shareBtn.innerText;
        this.shareBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        this.shareBtn.disabled = true;

        const formData = new FormData(this.form);

        try {
            const res = await fetch('/api/create_post.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.status === 'success') {
                // Close modal
                this.modal.hide();
                this.resetForm();

                // Inject into feed
                this.prependPostToFeed(data.data);

                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Show success?
            } else {
                alert(data.message || 'Error creating post');
            }
        } catch (error) {
            console.error(error);
            alert('Connection failed');
        } finally {
            this.shareBtn.innerHTML = originalText;
            this.shareBtn.disabled = false;
        }
    },

    prependPostToFeed(post) {
        const feedContainer = document.querySelector('.col-md-8.col-lg-6');
        const stories = feedContainer.querySelector('.card.p-3.mb-4'); // Stories container if exists

        const html = `
            <div class="card card-feed fade-in" id="post-${post.id}" data-post-id="${post.id}">
                <!-- Premium Header -->
                <div class="post-header">
                    <div class="d-flex align-items-center">
                        <a href="/user/profile.php?username=${post.username}">
                            <img src="${post.profile_pic}" class="user-avatar-md shadow-sm" alt="Avatar">
                        </a>
                        <div class="ms-2">
                            <a href="/user/profile.php?username=${post.username}" class="username-link text-decoration-none">
                                ${post.username}
                            </a>
                        </div>
                    </div>

                    <!-- Options (Glassy Dropdown) -->
                    <div class="dropdown">
                        <button class="btn btn-link text-muted p-0 border-0" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 overflow-hidden mt-2 p-1">
                                <li><a class="dropdown-item text-danger fw-bold rounded-2 px-3 py-2"
                                        href="/api/delete_post.php?id=${post.id}"><i
                                            class="bi bi-trash me-2"></i>Delete</a></li>
                                <li><a class="dropdown-item rounded-2 px-3 py-2" href="#"><i
                                            class="bi bi-archive me-2"></i>Archive</a></li>
                            <li>
                                <hr class="dropdown-divider my-1">
                            </li>
                            <li><a class="dropdown-item rounded-2 px-3 py-2" href="#"><i
                                        class="bi bi-link-45deg me-2"></i>Copy Link</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Image with Double Tap Heart Burst -->
                <div class="post-image-container" ondblclick="handleDoubleTap(${post.id}, this)">
                    <img src="${post.image_url}" class="post-image-content" alt="Post Content" loading="lazy">
                    <i class="bi bi-heart-fill heart-burst"></i>
                </div>

                <!-- Action Bar -->
                <div class="post-actions-bar">
                    <div class="d-flex gap-4">
                        <!-- Like -->
                        <button class="btn-action like-btn" onclick="toggleLike(${post.id})" title="Like" id="like-btn-${post.id}">
                            <i class="bi bi-heart"></i>
                        </button>

                        <!-- Comment -->
                        <button class="btn-action" data-action="focus-comment" data-post-id="${post.id}" title="Comment">
                            <i class="bi bi-chat"></i>
                        </button>

                        <!-- Share -->
                        <button class="btn-action" onclick="alert('Share feature coming soon!')" title="Share">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>

                    <!-- Save -->
                    <div class="action-group-right">
                        <button class="btn-action save-btn" onclick="toggleSave(${post.id})" id="save-btn-${post.id}" title="Save">
                            <i class="bi bi-bookmark"></i>
                        </button>
                    </div>
                </div>

                <!-- Details -->
                <div class="post-details">
                    <div class="likes-count" onclick="openUserList('likes', ${post.id})">
                        <span id="like-count-${post.id}">0</span> likes
                    </div>

                    <div class="caption-text mb-2">
                        <span class="caption-username">${post.username}</span>
                        <span>${post.caption}</span>
                    </div>

                    <div class="comments-preview px-0 mb-2" id="comments-preview-${post.id}"></div>

                    <small class="time-ago">Just now</small>
                </div>

                <!-- Seamless Comment Input -->
                <div class="comment-input-area">
                    <form class="d-flex w-100 align-items-center" data-action="submit-comment" data-post-id="${post.id}">
                        <input type="text" class="comment-input" placeholder="Add a comment..." autocomplete="off">
                        <button type="submit" class="post-btn" disabled>Post</button>
                    </form>
                </div>
            </div>
        `;

        // Insert after stories if exists, else top
        if (stories) {
            stories.insertAdjacentHTML('afterend', html);
        } else {
            // If no stories, insert at top of feed container but checking structure
            // feedContainer has stories then posts.
            feedContainer.insertAdjacentHTML('afterbegin', html);
        }
    }
};
