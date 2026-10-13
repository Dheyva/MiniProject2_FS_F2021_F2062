<?php
/** Admin: create categories (the "Create Assignment" form - input validation for name & description). */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$errors = [];
$old = ['category_name' => '', 'description' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['category_name'] = post_str('category_name');
    $old['description']   = post_str('description');

    if (!csrf_valid()) $errors['general'] = 'Invalid form submission. Please try again.';

    $n = mb_strlen($old['category_name']);
    if ($old['category_name'] === '')  $errors['category_name'] = 'Category name is required.';
    elseif ($n < 3 || $n > 100)        $errors['category_name'] = 'Category name must be 3-100 characters.';

    if ($old['description'] === '')                   $errors['description'] = 'Description is required.';
    elseif (mb_strlen($old['description']) < 10)      $errors['description'] = 'Description must be at least 10 characters.';

    if (!$errors) {
        $stmt = $conn->prepare('SELECT id FROM categories WHERE category_name = ?');
        $stmt->bind_param('s', $old['category_name']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows) $errors['category_name'] = 'This category already exists.';
        $stmt->close();
    }

    if (!$errors) {
        $stmt = $conn->prepare('INSERT INTO categories (category_name, description) VALUES (?, ?)');
        $stmt->bind_param('ss', $old['category_name'], $old['description']);
        try {
            $stmt->execute();
            flash('success', 'Category added successfully.');
            redirect('admin/categories.php');
        } catch (mysqli_sql_exception $ex) {
            $errors['general'] = 'Could not save the category. Please try again.';
        }
    }
}

$cats = $conn->query('SELECT category_name, description FROM categories ORDER BY id')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Categories';
$activePage = 'categories';
require __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="table-card">
      <h5 class="text-primary fw-bold mb-3"><i class="bi bi-folder-plus me-2"></i>Create Category</h5>
      <?php if (!empty($errors['general'])): ?><div class="alert alert-danger"><?= e($errors['general']) ?></div><?php endif; ?>
      <form method="POST" action="categories.php" data-validate="category" novalidate>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label for="category_name" class="form-label">Category Name</label>
          <input type="text" class="form-control <?= isset($errors['category_name']) ? 'is-invalid' : '' ?>" id="category_name"
                 name="category_name" value="<?= e($old['category_name']) ?>" maxlength="100">
          <div class="invalid-feedback"><?= isset($errors['category_name']) ? e($errors['category_name']) : '' ?></div>
        </div>
        <div class="mb-3">
          <label for="description" class="form-label">Description</label>
          <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="3"><?= e($old['description']) ?></textarea>
          <div class="invalid-feedback"><?= isset($errors['description']) ? e($errors['description']) : '' ?></div>
        </div>
        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg me-1"></i>Add Category</button>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="table-card">
      <h5 class="fw-bold mb-3">Existing Categories</h5>
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light"><tr><th>Category Name</th><th>Description</th></tr></thead>
        <tbody>
        <?php foreach ($cats as $c): ?>
          <tr><td class="fw-semibold text-primary"><?= e($c['category_name']) ?></td><td class="small"><?= e($c['description']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
