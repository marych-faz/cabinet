<?php
// MailSender.php в текущей папке auth/
require_once __DIR__ . '/../includes/mail_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailSender {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->setup();
    }
    
    private function setup() {
        $this->mail->isSMTP();
        $this->mail->Host       = SMTP_HOST;
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = SMTP_USERNAME;
        $this->mail->Password   = SMTP_PASSWORD;
        $this->mail->SMTPSecure = SMTP_ENCRYPTION;
        $this->mail->Port       = SMTP_PORT;
        $this->mail->CharSet    = 'UTF-8';
        
        $this->mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $this->mail->isHTML(true);
    }
    
    public function sendPasswordResetLink($toEmail, $toName, $resetLink) {
        try {
            $this->mail->addAddress($toEmail, $toName);
            $this->mail->Subject = 'Восстановление пароля';
            
            $emailBody = "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Восстановление пароля</title></head><body>
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #4361ee;'>Восстановление пароля</h2>
                    <p>Здравствуйте, $toName!</p>
                    <p>Для восстановления пароля перейдите по ссылке ниже:</p>
                    <p style='text-align: center; margin: 30px 0;'>
                        <a href='$resetLink' style='background-color: #4361ee; color: white; padding: 12px 24px; 
                        text-decoration: none; border-radius: 6px; display: inline-block;'>Сбросить пароль</a>
                    </p>
                    <p>Если вы не запрашивали сброс пароля, проигнорируйте это письмо.</p>
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                    <p style='font-size: 12px; color: #666;'>Ссылка действительна в течение 1 часа.</p>
                </div></body></html>";
            
            $this->mail->Body = $emailBody;
            $this->mail->AltBody = "Здравствуйте, $toName!\n\nДля восстановления пароля перейдите по ссылке:\n$resetLink\n\n";
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Ошибка отправки письма: {$this->mail->ErrorInfo}");
            return false;
        }
    }
}
?>