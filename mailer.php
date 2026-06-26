<?php
// ── Mailer factory ────────────────────────────────────────────────────────────
// Returns a configured PHPMailer instance ready to set To/Subject/Body and send.
// Install: composer require phpmailer/phpmailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . '/vendor/autoload.php';

function getMailer(): PHPMailer {
    $mail = new PHPMailer(true);

    // ── SMTP settings (Gmail example — change to your provider) ───────────────
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your-email@gmail.com';   // ← change
    $mail->Password   = 'your-app-password';       // ← Gmail app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // ── Sender ────────────────────────────────────────────────────────────────
    $mail->setFrom('no-reply@rocklandrentmasters.com', APP_NAME);
    $mail->isHTML(true);

    return $mail;
}

// ── Send a plain notification email ──────────────────────────────────────────
function sendEmail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    try {
        $mail = getMailer();
        $mail->addAddress($toEmail, $toName);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer error: " . $e->getMessage());
        return false;
    }
}