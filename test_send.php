<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // если используешь Composer

$mail = new PHPMailer(true);

try {
    // Настройки SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.yandex.ru';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'arm.admweb@yandex.ru'; // твоя рабочая почта
    $mail->Password   = 'dfkackvrwqocwofn';   // пароль для приложений (без пробелов!)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('arm.admweb@yandex.ru', 'Марат Фазуллин веб сайт Личный Кабинет Партнера');
    $mail->addAddress('f.marych@gmail.com', 'Тестовый от ООО АРМ'); // кому

    // Тема и тело письма
    $mail->isHTML(true);
    $mail->Subject = 'Тестовое письмо из разрабатываемого сайта Личный Кабинет Партнера';
    $mail->Body    = '<h1>Привет!</h1><p>Это письмо отправлено через PHPMailer и Яндекс.</p>';
    $mail->AltBody = 'Привет! Это письмо отправлено через PHPMailer и Яндекс.';

    $mail->send();
    echo '✅ Письмо успешно отправлено!';
} catch (Exception $e) {
    echo "❌ Ошибка при отправке письма: {$mail->ErrorInfo}";
}