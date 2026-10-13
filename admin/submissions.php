<?php
/** Admin: all student submissions (SQL JOIN of projects + users + categories) with file download. */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$stmt = $conn->prepare(
    'SELECT p.id, p.title, p.tech_stack, p.file_path, p.created_at,
            u.full_name, u.email, c.category_name
     FROM projects p
     INNER JOIN users u      ON u.id = p.user_id
     INNER JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC, p.id DESC'
);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'All Submissions';
$activePage = 'submissions';
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="page-title"><i class="bi bi-card-checklist text-primary me-2"></i>All Student Submissions</h2>
  <span class="badge bg-primary">Total: <?= count($rows) ?> Projects</span>
</div>

<div class="table-card table-responsive">
  <table class="table table-hover align-middle mb-0">
    <thead class="table-dark">
      <tr><th>Student Name</th><th>Project Title</th><th>Category</th><th>Tech Stack</th><th>Submitted File</th><th>Date Submitted</th></tr>
    </thead>
    <tbody>
    <?php if (!$rows): ?>
      <tr><td colspan="6" class="text-center text-muted py-4">No submissions yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><div class="fw-semibold"><?= e($r['full_name']) ?></div><div class="small text-muted"><?= e($r['email']) ?></div></td>
        <td class="fw-semibold text-primary"><?= e($r['title']) ?></td>
        <td><span class="badge bg-secondary"><?= e($r['category_name']) ?></span></td>
        <td class="small"><?= e($r['tech_stack']) ?></td>
        <td>
          <?php if ($r['file_path']): ?>
            <a class="btn btn-outline-primary btn-sm" href="../download.php?id=<?= (int)$r['id'] ?>"><i class="bi bi-download me-1"></i>File</a>
          <?php else: ?><span class="text-muted small">-</span><?php endif; ?>
        </td>
        <td class="small"><?= e(date('d M Y, h:i A', strtotime($r['created_at']))) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
