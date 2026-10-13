<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/upload.php';
require_role('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valid()) {
    flash('danger', 'Invalid request.');
    redirect('student/my_portfolio.php');
}

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
$uid = $_SESSION['user_id'];

if ($id) {
    $stmt = $conn->prepare('SELECT file_path FROM projects WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $id, $uid);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        $stmt = $conn->prepare('DELETE FROM projects WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
        $stmt->close();
        delete_stored_file($row['file_path']);
        flash('success', 'Submission deleted.');
        redirect('student/my_portfolio.php');
    }
}
flash('danger', 'Submission not found.');
redirect('student/my_portfolio.php');
