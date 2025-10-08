<footer class="mt-5 text-muted text-center">
    <hr>
    <p style="font-size:0.9em;color:#666;">&copy; <?php echo date('Y'); ?> Bike Store</p>
</footer>
</main>
<!-- Bootstrap 5 JS bundle: preferir local si existe, si no usar CDN -->
<?php if (file_exists(__DIR__ . '/../assets/js/bootstrap.bundle.min.js')): ?>
    <!-- Local Bootstrap JS with SRI -->
    <script src="/Bike_Store/assets/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<?php else: ?>
    <!-- CDN fallback with SRI -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<?php endif; ?>
</body>
</html>
