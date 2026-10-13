</main>

<footer class="footer bg-white border-top text-center py-3 mt-auto">
  <small class="text-muted">&copy; 2026 Politeknik Malaysia - DFP40443 Full Stack Web Development Mini Project 2</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/validation.js"></script>
<?php if (!empty($extraScripts)) foreach ($extraScripts as $s): ?>
<script src="<?= BASE_URL ?>/assets/js/<?= e($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>
