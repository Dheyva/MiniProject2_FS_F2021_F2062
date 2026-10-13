<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// Route by session/role
if (is_logged_in()) {
    redirect(home_for_role($_SESSION['role']));
}
redirect('login.php');
