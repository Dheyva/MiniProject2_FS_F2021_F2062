<?php
/**
 * auth.php - session handling, role guards, CSRF and flash helpers.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_name('PORTFOLIOSHOWCASE');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => defined('BASE_URL') && BASE_URL !== '' ? BASE_URL . '/' : '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
if (isset($_SESSION['role'])) {
    $_SESSION['role'] = strtolower(trim((string)$_SESSION['role']));
}

/** Escape output (prevents XSS). */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role'])
        && in_array($_SESSION['role'], ['admin', 'student'], true);
}

/** Landing page for each role. */
function home_for_role(string $role): string
{
    return $role === 'admin' ? 'admin/dashboard.php' : 'student/dashboard.php';
}

/**
 * Restrict a page to a role. Not logged in -> login page.
 * Wrong role -> that user's own dashboard.
 */
function require_role(string $role): void
{
    if (!is_logged_in()) {
        flash('warning', 'Please log in to continue.');
        redirect('login.php');
    }
    if ($_SESSION['role'] !== $role) {
        flash('danger', 'You do not have permission to access that page.');
        redirect(home_for_role($_SESSION['role']));
    }
}

/** Only for login/register: logged in users are sent to their dashboard. */
function guest_only(): void
{
    if (is_logged_in()) {
        redirect(home_for_role($_SESSION['role']));
    }
}

// ---- Flash messages ------------------------------------------------
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

// ---- CSRF ----------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

/** Cleans a text input: trim only (escaping is done on output, SQL is protected by prepared statements). */
function post_str(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}
