<?php
/**
 * ajax_search.php?q=term  -> JSON list of projects (live search + dynamic loading).
 * Empty q returns the full list.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Session expired. Please log in again.']);
    exit;
}

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q) > 100) $q = mb_substr($q, 0, 100);

$sql = 'SELECT p.id, p.title, p.description, p.tech_stack, p.created_at, p.file_path,
               c.category_name, u.full_name
        FROM projects p
        JOIN users u ON u.id = p.user_id
        JOIN categories c ON c.id = p.category_id';

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $conn->prepare($sql . ' WHERE p.title LIKE ? OR p.tech_stack LIKE ? OR u.full_name LIKE ? ORDER BY p.created_at DESC, p.id DESC');
    $stmt->bind_param('sss', $like, $like, $like);
} else {
    $stmt = $conn->prepare($sql . ' ORDER BY p.created_at DESC, p.id DESC');
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$out = array_map(function ($r) {
    return [
        'id'          => (int)$r['id'],
        'title'       => $r['title'],
        'description' => mb_strlen($r['description']) > 110 ? mb_substr($r['description'], 0, 110) . '...' : $r['description'],
        'tech_stack'  => array_values(array_filter(array_map('trim', explode(',', $r['tech_stack'])))),
        'category'    => $r['category_name'],
        'student'     => $r['full_name'],
        'date'        => date('d/m/Y', strtotime($r['created_at'])),
        'has_file'    => !empty($r['file_path']),
    ];
}, $rows);

echo json_encode(['count' => count($out), 'projects' => $out], JSON_UNESCAPED_UNICODE);
