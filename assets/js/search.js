/**
 * Real-time Search Logic
 * Handles debounced typing and result rendering
 */

document.addEventListener('DOMContentLoaded', () => {
    SearchSystem.init();
});

const SearchSystem = {
    debounceTimer: null,

    init() {
        const input = document.getElementById('navSearchInput');
        const results = document.getElementById('navSearchResults');

        if (!input || !results) return;

        // Input Listener
        input.addEventListener('input', (e) => {
            clearTimeout(this.debounceTimer);
            const query = e.target.value.trim();

            if (query.length === 0) {
                results.classList.remove('show');
                results.innerHTML = '';
                return;
            }

            // Debounce 300ms
            this.debounceTimer = setTimeout(() => {
                this.performSearch(query);
            }, 300);
        });

        // Focus Listener (Show previous results if query exists)
        input.addEventListener('focus', () => {
            if (input.value.trim().length > 0 && results.children.length > 0) {
                results.classList.add('show');
            }
        });

        // Click Outside Listener to Close
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !results.contains(e.target)) {
                results.classList.remove('show');
            }
        });
    },

    async performSearch(query) {
        const results = document.getElementById('navSearchResults');

        // Show Loading State if first time or replace?
        // Let's just keep typing flow smooth. Maybe a small spinner icon in input right?

        try {
            const res = await fetch(`/api/search_users.php?query=${encodeURIComponent(query)}`);
            const data = await res.json();

            if (data.status === 'success') {
                this.renderResults(data.data);
            } else {
                console.error(data.message);
            }
        } catch (e) {
            console.error(e);
        }
    },

    renderResults(users) {
        const results = document.getElementById('navSearchResults');
        results.innerHTML = '';

        if (users.length === 0) {
            results.innerHTML = `
                <div class="p-3 text-center text-muted small">
                    No users found.
                </div>
            `;
        } else {
            let html = '<div class="list-group list-group-flush">';
            users.forEach(user => {
                html += `
                    <a href="/user/profile.php?username=${user.username}" class="list-group-item list-group-item-action d-flex align-items-center gap-2 border-0 px-3 py-2">
                        <img src="${user.profile_pic}" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                        <div class="lh-1">
                            <div class="fw-bold small text-dark">${user.username}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">${user.full_name}</div>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            results.innerHTML = html;
        }

        results.classList.add('show');
    }
};
