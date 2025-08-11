<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['word_file'])) {
        $file = $_FILES['word_file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            die("Ошибка при загрузке файла.");
        }

        $tmp_name = $file['tmp_name'];
        $original_name = $file['name'];

        // Проверяем MIME-тип
        $mime = mime_content_type($tmp_name);
        $allowed_mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

        if ($mime !== $allowed_mime) {
            die("Недопустимый тип файла. Разрешены только .docx");
        }

        // Читаем содержимое файла
        $content = file_get_contents($tmp_name);

        try {
            $stmt = $pdo->prepare("INSERT INTO documents (name, content, mime_type) VALUES (?, ?, ?)");
            $stmt->execute([$original_name, $content, $mime]);

            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            die("Ошибка при сохранении в БД: " . $e->getMessage());
        }
    } else {
        die("Файл не выбран.");
    }
}
?>