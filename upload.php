<?php
/**
 * upload.php - secure file upload helper.
 *
 * Validates: upload error code, size (max 5MB), extension (pdf/docx/zip) and real MIME type.
 * Stores the file in /uploads with a random name (never trusts the client file name).
 *
 * @return array [stored_relative_path|null, error_message|null]
 */
const MAX_UPLOAD_BYTES = 5 * 1024 * 1024;
const ALLOWED_UPLOADS = [
    'pdf'  => ['application/pdf'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
    'zip'  => ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'],
];

function handle_upload(string $field, bool $required): array
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return $required ? [null, 'Please choose a file to upload.'] : [null, null];
    }

    $f = $_FILES[$field];
    switch ($f['error']) {
        case UPLOAD_ERR_OK: break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE: return [null, 'File is too large. Maximum size is 5MB.'];
        default: return [null, 'File upload failed. Please try again.'];
    }

    if ($f['size'] <= 0)                 return [null, 'The selected file is empty.'];
    if ($f['size'] > MAX_UPLOAD_BYTES)   return [null, 'File is too large. Maximum size is 5MB.'];

    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!isset(ALLOWED_UPLOADS[$ext]))   return [null, 'Only PDF, DOCX or ZIP files are allowed.'];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($f['tmp_name']);
    if (!in_array($mime, ALLOWED_UPLOADS[$ext], true)) {
        return [null, 'File content does not match its extension.'];
    }
    if (!is_uploaded_file($f['tmp_name'])) return [null, 'Invalid upload.'];

    if (!is_dir(UPLOAD_DIR) && !mkdir(UPLOAD_DIR, 0755, true)) {
        return [null, 'Server error: upload folder is not available.'];
    }

    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $name)) {
        return [null, 'Could not save the uploaded file.'];
    }
    return ['uploads/' . $name, null];
}

/** Delete a stored file safely (only inside /uploads). */
function delete_stored_file(?string $path): void
{
    if (!$path) return;
    $full = UPLOAD_DIR . basename($path);
    if (is_file($full)) @unlink($full);
}
