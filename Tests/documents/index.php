<?php include 'config.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Загрузка Word-файлов</title>
</head>
<body>
    <h2>Загрузить .docx файл</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="file" name="word_file" accept=".docx">
        <button type="submit">Загрузить</button>
    </form>

    <hr>

    <h3>Список загруженных файлов</h3>
    <?php
    $stmt = $pdo->query("SELECT id, name FROM documents ORDER BY uploaded_at DESC");
    if ($stmt->rowCount() > 0) {
        echo "<ul>";
        while ($row = $stmt->fetch()) {
            echo "<li><a href='download.php?id={$row['id']}'>{$row['name']}</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Файлы не найдены.</p>";
    }
    ?>
</body>
</html>