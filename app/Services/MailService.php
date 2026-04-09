<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class MailService
{
    private string $lastError = '';

    /**
     * Kirim email menggunakan PHPMailer + konfigurasi dari app_settings (DB).
     */
    public function send(string $to, string $subject, string $body): bool
    {
        try {
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host       = app_setting('email_host')       ?: 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = app_setting('email_username')   ?: '';
            $mail->Password   = app_setting('email_password')   ?: '';
            $mail->Port       = (int)(app_setting('email_port') ?: 587);

            // Enkripsi
            $encryption = app_setting('email_encryption') ?: 'tls';
            if ($encryption === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($encryption === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } else {
                $mail->SMTPSecure = '';
                $mail->SMTPAutoTLS = false;
            }

            // Bypass SSL verify — diperlukan di banyak server lokal & hosting
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Timeout
            $mail->Timeout = 10;

            // Pengirim
            $fromAddress = app_setting('email_from_address') ?: app_setting('email_username') ?: 'noreply@example.com';
            $fromName    = app_setting('email_from_name')    ?: app_setting('app_name') ?: 'Aplikasi';
            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($to);

            // Konten
            $mail->isHTML(true);
            $mail->CharSet  = 'UTF-8';
            $mail->Subject  = $subject;
            $mail->Body     = $body;
            $mail->AltBody  = strip_tags($body);

            $mail->send();
            return true;

        } catch (PHPMailerException $e) {
            $this->lastError = $e->getMessage();
            log_message('error', '[MailService] PHPMailer error: ' . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            log_message('error', '[MailService] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil pesan error terakhir.
     */
    public function getLastError(): string
    {
        return $this->lastError;
    }

    /**
     * Cek apakah konfigurasi email sudah lengkap.
     */
    public function isConfigured(): bool
    {
        return !empty(app_setting('email_host'))
            && !empty(app_setting('email_username'))
            && !empty(app_setting('email_password'));
    }
}
