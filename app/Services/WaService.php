<?php

namespace App\Services;

class WaService
{
    /**
     * Kirim pesan WhatsApp menggunakan konfigurasi dari app_settings (DB).
     *
     * @param string $phone  Format: 6281234567890 (tanpa +)
     * @param string $message
     */
    public function send(string $phone, string $message): bool
    {
        if (app_setting('wa_active') !== '1') {
            return false;
        }

        $url      = app_setting('wa_api_url');
        $token    = app_setting('wa_token');
        $provider = app_setting('wa_provider') ?: 'fonnte';

        if (empty($url) || empty($token)) {
            log_message('error', '[WaService] API URL atau Token belum dikonfigurasi.');
            return false;
        }

        // Normalisasi nomor (buang + dan spasi)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        try {
            return match ($provider) {
                'fonnte' => $this->sendFonnte($phone, $message, $url, $token),
                default  => $this->sendCustom($phone, $message, $url, $token),
            };
        } catch (\Throwable $e) {
            log_message('error', '[WaService] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim via Fonnte (https://fonnte.com)
     * Header: Authorization: <token>
     * Body: target, message
     */
    private function sendFonnte(string $phone, string $message, string $url, string $token): bool
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => ['Authorization: ' . $token],
            CURLOPT_POSTFIELDS     => http_build_query([
                'target'  => $phone,
                'message' => $message,
            ]),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', '[WaService Fonnte] cURL error: ' . $error);
            return false;
        }

        $result = json_decode($response, true);
        return isset($result['status']) && $result['status'] === true;
    }

    /**
     * Kirim via Custom Gateway
     * Header: Authorization: Bearer <token>, Content-Type: application/json
     * Body JSON: { "phone": "...", "message": "..." }
     */
    private function sendCustom(string $phone, string $message, string $url, string $token): bool
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode([
                'phone'   => $phone,
                'message' => $message,
            ]),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', '[WaService Custom] cURL error: ' . $error);
            return false;
        }

        return !empty($response);
    }

    public function isConfigured(): bool
    {
        return app_setting('wa_active') === '1'
            && !empty(app_setting('wa_api_url'))
            && !empty(app_setting('wa_token'));
    }
}
