<?php
/**
 * download.php?id=<project id>
 * Any logged-in user may download a submission file (Admin: View Submissions, Student: directory "Report" button).
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (!is_logged_in()) {
    flash('warning', 'Please log in to continue.');
    redirect('login.php');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); die('Invalid request.'); }

$stmt = $conn->prepare('SELECT title, file_path FROM projects WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
$stmt->close();

$full = $p && $p['file_path'] ? UPLOAD_DIR . basename($p['file_path']) : '';
if (!$p || !is_file($full)) {
    http_response_code(404);
    die('File not found.');
}

$ext  = strtolower(pathinfo($full, PATHINFO_EXTENSION));
$safe = trim(preg_replace('/[^A-Za-z0-9_\-]+/', '_', $p['title']), '_') ?: 'submission';
$mime = ['pdf' => 'application/pdf', 'zip' => 'application/zip',
         'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'][$ext] ?? 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $safe . '.' . $ext . '"');
header('Content-Length: ' . filesize($full));
header('X-Content-Type-Options: nosniff');
readfile($full);
exit;
