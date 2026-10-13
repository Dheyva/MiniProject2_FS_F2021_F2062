<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('student');

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
$extraScripts = ['search.js'];
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h2 class="page-title">Project Showcase Directory</h2>
    <small class="text-muted">Explore student portfolio submissions and FYP projects.</small>
  </div>
  <a href="submit.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Submit New Project</a>
</div>

<div class="card search-box mb-4 border-0">
  <div class="card-body py-2">
    <div class="input-group">
      <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
      <input type="search" id="liveSearch" class="form-control border-0 shadow-none"
             placeholder="Live search by project title, tech stack, or student name..." autocomplete="off" maxlength="100">
    </div>
  </div>
</div>

<div id="searchStatus" class="small text-muted mb-2" aria-live="polite"></div>
<div id="projectList" class="row g-3"
     data-endpoint="<?= BASE_URL ?>/student/ajax_search.php"
     data-download="<?= BASE_URL ?>/download.php?id="></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
