</div> <!-- End Main Content -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- App Scripts -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js?v=2.0"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/comments.js?v=2.1"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/likes.js?v=1.0"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/search.js?v=1.0"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/create_post.js?v=1.0"></script>

<script>
    // Simple Theme Toggler
    document.addEventListener('DOMContentLoaded', () => {
        const toggleBtn = document.getElementById('theme-toggle');
        const html = document.documentElement;

        // Check local storage
        if (localStorage.getItem('theme') === 'dark') {
            html.setAttribute('data-theme', 'dark');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                if (html.getAttribute('data-theme') === 'dark') {
                    html.setAttribute('data-theme', 'light');
                    localStorage.setItem('theme', 'light');
                } else {
                    html.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                }
            });
        }
    });
</script>

</body>

</html>