<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$students   = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];
$categories = (int)$conn->query('SELECT COUNT(*) FROM categories')->fetch_row()[0];
$subs       = (int)$conn->query('SELECT COUNT(*) FROM projects')->fetch_row()[0];

$recent = $conn->query(
    'SELECT u.full_name, p.title, p.created_at, c.category_name
     FROM projects p
     JOIN users u ON u.id = p.user_id
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC, p.id DESC LIMIT 5'
)->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Admin Dashboard';
$activePage = 'dashboard';
require __DIR__ . '/../includes/header.php';
?>
<h2 class="page-title"><i class="bi bi-speedometer text-primary me-2"></i>Admin Overview</h2>
<small class="text-muted d-block mb-3">Lecturer &amp; Coordinator Evaluation Portal</small>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card stat-card bg-primary"><div class="card-body d-flex justify-content-between align-items-center">
    <div><div class="stat-label">STUDENTS</div><div class="stat-num"><?= $students ?></div></div><i class="bi bi-people-fill stat-icon"></i></div></div></div>
  <div class="col-md-4"><div class="card stat-card bg-success"><div class="card-body d-flex justify-content-between align-items-center">
    <div><div class="stat-label">CATEGORIES</div><div class="stat-num"><?= $categories ?></div></div><i class="bi bi-layers-fill stat-icon"></i></div></div></div>
  <div class="col-md-4"><div class="card stat-card bg-info"><div class="card-body d-flex justify-content-between align-items-center">
    <div><div class="stat-label">SUBMISSIONS</div><div class="stat-num"><?= $subs ?></div></div><i class="bi bi-folder2-open stat-icon"></i></div></div></div>
</div>

<div class="table-card">
  <h5 class="fw-bold mb-3">Recent Submissions</h5>
  <div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
      <thead class="table-light"><tr><th>Student Name</th><th>Title</th><th>Category</th><th>Date</th></tr></thead>
      <tbody>
      <?php if (!$recent): ?>
        <tr><td colspan="4" class="text-center text-muted py-3">No submissions yet.</td></tr>
      <?php endif; ?>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td class="fw-semibold"><?= e($r['full_name']) ?></td>
          <td><?= e($r['title']) ?></td>
          <td><span class="badge bg-secondary"><?= e($r['category_name']) ?></span></td>
          <td class="small"><?= e(date('d M Y', strtotime($r['created_at']))) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
