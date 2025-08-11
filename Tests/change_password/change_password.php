<?php
// change_password.php

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['old_password']) || !isset($data['new_password'])) {
  echo json_encode(['message' => 'Все поля обязательны']);
  exit;
}

$oldPassword = $data['old_password'];
$newPassword = $data['new_password'];

// Подключение к БД
$conn = new mysqli("localhost", "username", "password", "dbname");
if ($conn->connect_error) {
  die(json_encode(['message' => 'Ошибка подключения']));
}

// Предположим, что мы знаем ID текущего пользователя (например, из сессии)
session_start();
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['message' => 'Вы не авторизованы']);
  exit;
}
$user_id = $_SESSION['user_id'];

// Получаем хэш текущего пароля из БД
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($stored_hash);
$stmt->fetch();

if (!$stored_hash) {
  echo json_encode(['message' => 'Пользователь не найден']);
  exit;
}

// Проверяем текущий пароль
if (!password_verify($oldPassword, $stored_hash)) {
  echo json_encode(['message' => 'Старый пароль неверен']);
  exit;
}

// Хэшируем новый пароль
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

// Обновляем пароль в БД
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $newHash, $user_id);
if ($stmt->execute()) {
  echo json_encode(['message' => 'Пароль успешно изменён']);
} else {
  echo json_encode(['message' => 'Ошибка при изменении пароля']);
}
?>