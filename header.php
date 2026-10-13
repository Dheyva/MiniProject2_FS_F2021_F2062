<?php
/**
 * header.php - navigation + session aware menu.
 * Set $pageTitle and (optionally) $activePage before including.
 * The calling page must already have included db.php and auth.php.
 */
$pageTitle  = $pageTitle  ?? 'Portfolio Showcase';
$activePage = $activePage ?? '';
$role       = $_SESSION['role'] ?? null;
$flash      = get_flash();

function nav_link(string $href, string $icon, string $label, string $key, string $active): string
{
    $cls = $key === $active ? ' active' : '';
    return '<li class="nav-item"><a class="nav-link' . $cls . '" href="' . BASE_URL . '/' . $href . '">'
         . '<i class="bi ' . $icon . ' me-1"></i>' . $label . '</a></li>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Portfolio Showcase</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/<?= $role ? home_for_role($role) : 'login.php' ?>">
      <i class="bi bi-mortarboard-fill text-warning me-2"></i>Portfolio Showcase
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        <?php if ($role === 'student'): ?>
          <?= nav_link('student/dashboard.php',    'bi-house-door-fill', 'Dashboard',     'dashboard', $activePage) ?>
          <?= nav_link('student/submit.php',       'bi-upload',          'Submit Project','submit',    $activePage) ?>
          <?= nav_link('student/my_portfolio.php', 'bi-briefcase-fill',  'My Portfolio',  'portfolio', $activePage) ?>
        <?php elseif ($role === 'admin'): ?>
          <?= nav_link('admin/dashboard.php',   'bi-graph-up',        'Admin Dashboard', 'dashboard',   $activePage) ?>
          <?= nav_link('admin/categories.php',  'bi-folder-fill',     'Categories',      'categories',  $activePage) ?>
          <?= nav_link('admin/submissions.php', 'bi-card-checklist',  'All Submissions', 'submissions', $activePage) ?>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if ($role): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle text-info me-1"></i><?= e($_SESSION['full_name']) ?> (<?= e(ucfirst($role)) ?>)
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <?= nav_link('login.php',    'bi-box-arrow-in-right', 'Login',    'login',    $activePage) ?>
          <?= nav_link('register.php', 'bi-person-plus-fill',   'Register', 'register', $activePage) ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container-fluid px-4 py-4 flex-grow-1">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
