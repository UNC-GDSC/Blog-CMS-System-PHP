        </div>
    </main>

    <!-- Footer -->
    <footer class="text-center text-muted">
        <div class="container">
            <p class="mb-0">
                &copy; <?= date('Y') ?> <?= htmlspecialchars(App\Helpers\Env::get('APP_NAME', 'Blog CMS System')) ?>.
                All rights reserved.
            </p>
            <p class="mb-0 small">
                Built with <i class="bi bi-heart-fill text-danger"></i> using PHP and Bootstrap 5
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });

        // Confirm delete actions
        function confirmDelete(postId, postTitle) {
            return confirm(`Are you sure you want to delete "${postTitle}"?\n\nThis action cannot be undone.`);
        }
    </script>
</body>
</html>
