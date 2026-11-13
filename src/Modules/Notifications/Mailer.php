<?php
declare(strict_types=1);

namespace SmartCanteen\Modules\Notifications;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use SmartCanteen\Core\Env;
use SmartCanteen\Core\Logger;

/** SMTP mail sender. Credentials come from .env - never hard-code them. */
final class Mailer
{
    public function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $user = Env::get('MAIL_USERNAME');
        $pass = Env::get('MAIL_PASSWORD');
        if (!Env::bool('MAIL_ENABLED', true) || !$user || !$pass || $toEmail === '') {
            Logger::info("Mail skipped (disabled / not configured / no recipient): $subject");
            return false;
        }

        require_once APP_ROOT . '/lib/PHPMailer/src/Exception.php';
        require_once APP_ROOT . '/lib/PHPMailer/src/PHPMailer.php';
        require_once APP_ROOT . '/lib/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = Env::get('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int) Env::get('MAIL_PORT', '587');
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(Env::get('MAIL_FROM_ADDRESS', $user), Env::get('MAIL_FROM_NAME', 'Smart Canteen'));
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = trim(strip_tags($htmlBody));
            $mail->send();
            return true;
        } catch (MailException $ex) {
            Logger::error('Mailer error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
