<?php
include 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Неверный ID файла.");
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("SELECT name, content, mime_type FROM documents WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch();

if (!$row) {
    die("Файл не найден.");
}

header("Content-Type: " . $row['mime_type']);
header("Content-Disposition: attachment; filename=\"" . $row['name'] . "\"");
echo $row['content'];
exit;
?>