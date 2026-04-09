<?php

namespace App\Services;

class MailService
{
    /**
     * Kirim email menggunakan konfigurasi dari app_settings (DB).
     */
    public function send(string $to, string $subject, string $body): bool
    {
        try {
            $config = [
                'protocol'   => app_setting('email_driver')     ?: 'smtp',
                'SMTPHost'   => app_setting('email_host')        ?: '',
                'SMTPPort'   => (int)(app_setting('email_port')  ?: 587),
                'SMTPCrypto' => app_setting('email_encryption')  ?: 'tls',
                'SMTPUser'   => app_setting('email_username')    ?: '',
                'SMTPPass'   => app_setting('email_password')    ?: '',
                'mailType'   => 'html',
                'charset'    => 'utf-8',
                'newline'    => "\r\n",
            ];

            $email = \Config\Services::email(null, false);
            $email->initialize($config);

            $email->setFrom(
                app_setting('email_from_address') ?: app_setting('email_username') ?: 'noreply@example.com',
                app_setting('email_from_name')    ?: app_setting('app_name') ?: 'Aplikasi'
            );
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($body);

            return $email->send(false);
        } catch (\Throwable $e) {
            log_message('error', '[MailService] ' . $e->getMessage());
            return false;
        }
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
