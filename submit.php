<?php
/**
 * submit.php        -> create a new project submission (file upload required)
 * submit.php?id=N   -> edit own submission (new file optional, replaces old one)
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_role('student');

$userId = $_SESSION['user_id'];
$editId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$existing = null;

if ($editId) {
    $stmt = $conn->prepare('SELECT * FROM projects WHERE id = ? AND user_id = ?');   // ownership check
    $stmt->bind_param('ii', $editId, $userId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$existing) {
        flash('danger', 'Submission not found.');
        redirect('student/my_portfolio.php');
    }
}

$categories = $conn->query('SELECT id, category_name FROM categories ORDER BY category_name')->fetch_all(MYSQLI_ASSOC);
$validCats  = array_column($categories, 'id');

$errors = [];
$old = [
    'title'       => $existing['title']       ?? '',
    'category_id' => $existing['category_id'] ?? '',
    'tech_stack'  => $existing['tech_stack']  ?? '',
    'description' => $existing['description'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) $old[$k] = post_str($k);

    if (!csrf_valid()) $errors['general'] = 'Invalid form submission. Please try again.';

    $t = mb_strlen($old['title']);
    if ($old['title'] === '')       $errors['title'] = 'Project title is required.';
    elseif ($t < 3 || $t > 200)     $errors['title'] = 'Project title must be 3-200 characters.';

    if ($old['category_id'] === '' || !ctype_digit($old['category_id']) || !in_array((int)$old['category_id'], array_map('intval', $validCats), true))
        $errors['category_id'] = 'Please select a valid category.';

    if ($old['tech_stack'] === '')              $errors['tech_stack'] = 'Tech stack is required.';
    elseif (mb_strlen($old['tech_stack']) > 255) $errors['tech_stack'] = 'Tech stack must not exceed 255 characters.';

    if ($old['description'] === '')                    $errors['description'] = 'Description is required.';
    elseif (mb_strlen($old['description']) < 20)       $errors['description'] = 'Description must be at least 20 characters.';

    // File upload validation (type + size) - required for new submissions
    $newPath = null;
    if (!$errors) {
        [$newPath, $fileErr] = handle_upload('document', !$existing);
        if ($fileErr) $errors['document'] = $fileErr;
    }

    if (!$errors) {
        $cat = (int)$old['category_id'];
        try {
            if ($existing) {
                $path = $newPath ?? $existing['file_path'];
                $stmt = $conn->prepare('UPDATE projects SET category_id=?, title=?, description=?, tech_stack=?, file_path=? WHERE id=? AND user_id=?');
                $stmt->bind_param('issssii', $cat, $old['title'], $old['description'], $old['tech_stack'], $path, $editId, $userId);
                $stmt->execute();
                if ($newPath) delete_stored_file($existing['file_path']);
                flash('success', 'Project updated successfully.');
            } else {
                $stmt = $conn->prepare('INSERT INTO projects (user_id, category_id, title, description, tech_stack, file_path) VALUES (?,?,?,?,?,?)');
                $stmt->bind_param('iissss', $userId, $cat, $old['title'], $old['description'], $old['tech_stack'], $newPath);
                $stmt->execute();
                flash('success', 'Project submitted successfully!');
            }
            redirect('student/my_portfolio.php');
        } catch (mysqli_sql_exception $ex) {
            delete_stored_file($newPath);                 // do not leave orphan files
            $errors['general'] = 'Could not save your project. Please try again.';
        }
    }
}

$pageTitle = $existing ? 'Edit Project' : 'Submit Project';
$activePage = 'submit';
require __DIR__ . '/../includes/header.php';
function fe(array $errors, string $k): string { return isset($errors[$k]) ? e($errors[$k]) : ''; }
?>
<div class="card form-card">
  <div class="card-body p-4">
    <h4 class="text-primary fw-bold mb-3">
      <i class="bi bi-cloud-arrow-up-fill me-2"></i><?= $existing ? 'Edit Portfolio Project' : 'Submit Portfolio Project' ?>
    </h4>

    <?php if (!empty($errors['general'])): ?>
      <div class="alert alert-danger"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" data-validate="project" novalidate
          action="submit.php<?= $existing ? '?id=' . (int)$editId : '' ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="MAX_FILE_SIZE" value="<?= MAX_UPLOAD_BYTES ?>">

      <div class="mb-3">
        <label for="title" class="form-label">Project Title</label>
        <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="title" name="title"
               value="<?= e($old['title']) ?>" maxlength="200">
        <div class="invalid-feedback"><?= fe($errors, 'title') ?></div>
      </div>

      <div class="mb-3">
        <label for="category_id" class="form-label">Category</label>
        <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" id="category_id" name="category_id">
          <option value="">-- Select Category --</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (string)$old['category_id'] === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['category_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div class="invalid-feedback"><?= fe($errors, 'category_id') ?></div>
      </div>

      <div class="mb-3">
        <label for="tech_stack" class="form-label">Tech Stack (Comma Separated)</label>
        <input type="text" class="form-control <?= isset($errors['tech_stack']) ? 'is-invalid' : '' ?>" id="tech_stack" name="tech_stack"
               placeholder="e.g. PHP, MySQL, Bootstrap 5, AJAX" value="<?= e($old['tech_stack']) ?>" maxlength="255">
        <div class="invalid-feedback"><?= fe($errors, 'tech_stack') ?></div>
      </div>

      <div class="mb-3">
        <label for="description" class="form-label">Project Description</label>
        <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="4"><?= e($old['description']) ?></textarea>
        <div class="invalid-feedback"><?= fe($errors, 'description') ?></div>
      </div>

      <div class="mb-3">
        <label for="document" class="form-label">Upload Documentation (PDF, DOCX, or ZIP - Max 5MB)
          <?= $existing ? '<span class="text-muted">- leave empty to keep the current file</span>' : '' ?></label>
        <input type="file" class="form-control <?= isset($errors['document']) ? 'is-invalid' : '' ?>" id="document" name="document"
               accept=".pdf,.docx,.zip" data-required="<?= $existing ? '0' : '1' ?>">
        <div class="invalid-feedback"><?= fe($errors, 'document') ?></div>
      </div>

      <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill me-1"></i><?= $existing ? 'Save Changes' : 'Submit Entry' ?></button>
      <a href="my_portfolio.php" class="btn btn-outline-secondary ms-1">Cancel</a>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
