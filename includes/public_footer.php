</main>

<footer class="bg-dark text-white pt-5 pb-3 mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <h5 class="fw-bold"><i class="fa-solid fa-graduation-cap me-2"></i><?= SITE_NAME ?></h5>
        <p class="text-muted small"><?= SITE_TAGLINE ?>. Connecting students with knowledge through world-class seminars.</p>
      </div>
      <div class="col-md-4">
        <h6 class="fw-semibold text-uppercase text-muted small mb-3">Quick Links</h6>
        <ul class="list-unstyled small">
          <li><a href="<?= BASE_URL ?>/" class="text-muted text-decoration-none">Home</a></li>
          <li><a href="<?= BASE_URL ?>/seminars.php" class="text-muted text-decoration-none">Browse Seminars</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6 class="fw-semibold text-uppercase text-muted small mb-3">Staff Portal</h6>
        <ul class="list-unstyled small">
          <li><a href="<?= BASE_URL ?>/admin/login.php" class="text-muted text-decoration-none">Admin Login</a></li>
          <li><a href="<?= BASE_URL ?>/teacher/login.php" class="text-muted text-decoration-none">Teacher Login</a></li>
        </ul>
      </div>
    </div>
    <hr class="border-secondary mt-4">
    <p class="text-center text-muted small mb-0">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
  </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
