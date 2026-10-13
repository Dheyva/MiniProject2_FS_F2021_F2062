<?php
/**
 * db.php - database connection (included by every page that needs data).
 * Edit the constants below if your Laragon MySQL settings are different.
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');            // Laragon default = empty password
define('DB_NAME', 'assignment_system');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die('Database connection failed. Please check includes/db.php and make sure the database "'
        . DB_NAME . '" has been imported.');
}

// Web path of the project root (e.g. "/portfolio_showcase"), works in any sub-folder of Laragon's www
$projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$docRoot     = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$base        = ($docRoot && strpos($projectRoot, $docRoot) === 0) ? substr($projectRoot, strlen($docRoot)) : '';
define('BASE_URL', rtrim($base, '/'));
define('UPLOAD_DIR', $projectRoot . '/uploads/');
HAA