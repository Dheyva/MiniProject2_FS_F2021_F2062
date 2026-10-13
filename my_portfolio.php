<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('student');

$stmt = $conn->prepare(
    'SELECT p.id, p.title, p.tech_stack, p.file_path, p.created_at, c.category_name
     FROM projects p JOIN categories c ON c.id = p.category_id
     WHERE p.user_id = ? ORDER BY p.created_at DESC, p.id DESC'
);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'My Portfolio';
$activePage = 'portfolio';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="page-title"><i class="bi bi-briefcase-fill text-primary me-2"></i>My Portfolio Submissions</h2>
  <a href="submit.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Submission</a>
</div>

<div class="table-card table-responsive">
  <table class="table table-hover align-middle mb-0">
    <thead class="table-light">
      <tr><th>Project Title</th><th>Category</th><th>Tech Stack</th><th>File</th><th>Date</th><th class="text-end">Actions</th></tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="6" class="text-center text-muted py-4">You have not submitted any project yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td class="fw-semibold text-primary"><?= e($r['title']) ?></td>
        <td><span class="badge bg-secondary"><?= e($r['category_name']) ?></span></td>
        <td class="small"><?= e($r['tech_stack']) ?></td>
        <td>
          <?php if ($r['file_path']): ?>
            <a class="btn btn-outline-secondary btn-sm" href="../download.php?id=<?= (int)$r['id'] ?>"><i class="bi bi-download me-1"></i>Download</a>
          <?php else: ?><span class="text-muted small">-</span><?php endif; ?>
        </td>
        <td class="small"><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
        <td class="text-end text-nowrap">
          <a href="submit.php?id=<?= (int)$r['id'] ?>" class="btn btn-warning btn-sm" title="Edit"><i class="bi bi-pencil-square"></i></a>
          <form method="POST" action="delete.php" class="d-inline"
                onsubmit="return confirm('Delete this submission? This cannot be undone.');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="bi bi-trash-fill"></i></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
