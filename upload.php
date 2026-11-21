<?php
$uploadDir = __DIR__ . '/uploads/';

function back(string $param, string $text): void {
    header('Location: index.php?' . $param . '=' . urlencode($text));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    back('error', 'Файл нужно отправлять через форму');
}

if (empty($_FILES) && ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    back('error', 'Запрос больше post_max_size (' . ini_get('post_max_size') . ')');
}

$file = $_FILES['userfile'] ?? null;
if ($file === null) {
    back('error', 'Файл не выбран');
}

switch ($file['error']) {
    case UPLOAD_ERR_OK:
        break;
    case UPLOAD_ERR_NO_FILE:
        back('error', 'Файл не выбран');
    case UPLOAD_ERR_INI_SIZE:
        back('error', 'Файл больше upload_max_filesize (' . ini_get('upload_max_filesize') . ')');
    case UPLOAD_ERR_FORM_SIZE:
        back('error', 'Файл больше MAX_FILE_SIZE из формы');
    default:
        back('error', 'Ошибка загрузки, код: ' . $file['error']);
}

$maxSize = 5 * 1024 * 1024;
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt', 'docx', 'xlsx', 'zip'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($file['size'] > $maxSize) {
    back('error', 'Файл больше 5 МБ');
}
if (!in_array($ext, $allowed, true)) {
    back('error', 'Недопустимый тип файла: .' . $ext);
}

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$name = basename($file['name']);
$name = preg_replace('/[^\w.\-]/u', '_', $name);

if (file_exists($uploadDir . $name)) {
    $name = time() . '_' . $name;
}

if (move_uploaded_file($file['tmp_name'], $uploadDir . $name)) {
    back('msg', 'Файл загружен');
} else {
    back('error', 'Не удалось сохранить файл');
}
