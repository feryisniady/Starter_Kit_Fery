<?php

/**
 * ======================================================
 * APP HELPER
 * Utility global (non-RBAC, non-UI spesifik menu)
 * ======================================================
 */

/**
 * ======================================================
 * BREADCRUMB (alias dengan label mapping sederhana)
 * ======================================================
 */
if (!function_exists('app_breadcrumb')) {
    function app_breadcrumb(): string
    {
        $uri      = service('request')->getUri();
        $segments = $uri->getSegments();

        $html  = '<div class="topbar-breadcrumb">';
        $html .= '<a href="/dashboard"><i class="fas fa-house"></i></a>';

        $path = '';
        $validSegments = [];

        foreach ($segments as $seg) {
            if (is_numeric($seg) || $seg === 'admin') continue;
            $validSegments[] = $seg;
        }

        foreach ($validSegments as $i => $seg) {
            $label = ucfirst(str_replace(['-', '_'], ' ', $seg));
            $path .= '/' . $seg;

            $html .= '<i class="fas fa-chevron-right"></i>';

            if ($i === count($validSegments) - 1) {
                $html .= '<span class="current">' . $label . '</span>';
            } else {
                $html .= '<a href="' . $path . '">' . $label . '</a>';
            }
        }

        $html .= '</div>';

        return $html;
    }
}

/**
 * ======================================================
 * NOTIFY ADMINS (alias untuk notifyAllAdmins)
 * ======================================================
 */
if (!function_exists('notifyAdmins')) {
    function notifyAdmins(string $title, string $message = '', string $url = '', string $type = 'info'): void
    {
        notifyAllAdmins($title, $message, $url, $type);
    }
}

/**
 * ======================================================
 * SEND MAIL (via DB settings)
 * ======================================================
 */
if (!function_exists('send_mail')) {
    function send_mail(string $to, string $subject, string $body): bool
    {
        return (new \App\Services\MailService())->send($to, $subject, $body);
    }
}

/**
 * ======================================================
 * SEND WHATSAPP (via DB settings)
 * ======================================================
 */
if (!function_exists('send_wa')) {
    function send_wa(string $phone, string $message): bool
    {
        return (new \App\Services\WaService())->send($phone, $message);
    }
}

/**
 * Render konten dari WYSIWYG (Quill) secara aman.
 * Jika konten adalah HTML (dimulai '<') → strip tag berbahaya, sisakan formatting.
 * Jika plain text → nl2br + esc.
 */
if (!function_exists('render_wysiwyg')) {
    function render_wysiwyg(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') return '';
        if ($html[0] === '<') {
            return strip_tags($html, '<ol><ul><li><p><br><strong><em><u><h2><h3>');
        }
        return nl2br(esc($html));
    }
}

/**
 * Kirim WA ke user berdasarkan user_id (ambil phone dari tabel users).
 * No-op jika phone kosong atau WA tidak dikonfigurasi.
 */
if (!function_exists('send_wa_to_user')) {
    function send_wa_to_user(int $userId, string $message): bool
    {
        $user = \Config\Database::connect()
            ->table('users')->select('phone')->where('id', $userId)->get()->getRowArray();
        if (empty($user['phone'])) return false;
        return send_wa($user['phone'], $message);
    }
}

/**
 * Kirim WA ke SDM berdasarkan sdm_id (via sdm.user_id → users.phone).
 */
if (!function_exists('send_wa_to_sdm')) {
    function send_wa_to_sdm(int $sdmId, string $message): bool
    {
        $sdm = \Config\Database::connect()
            ->table('sdm')->select('user_id')->where('id', $sdmId)->get()->getRowArray();
        if (empty($sdm['user_id'])) return false;
        return send_wa_to_user((int)$sdm['user_id'], $message);
    }
}

/**
 * ======================================================
 * FORMAT TANGGAL INDONESIA
 * ======================================================
 */
if (!function_exists('tgl_indo')) {
    function tgl_indo(string $date): string
    {
        $bulan = [
            1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',
            5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',
            9=>'September',10=>'Oktober',11=>'November',12=>'Desember',
        ];
        $ts = strtotime($date);
        return date('j', $ts) . ' ' . ($bulan[(int)date('n', $ts)] ?? '') . ' ' . date('Y', $ts);
    }
}

/**
 * renderContent($text)
 * Render teks biasa atau HTML dari WYSIWYG secara aman.
 * - Jika dimulai dengan '<' → output sebagai HTML (dari Quill, tidak ada script)
 * - Jika plain text → nl2br + esc
 */
/**
 * ======================================================
 * REMINDER TL — Pre-kalkulasi jadwal pengingat
 * ======================================================
 * Dipanggil setiap kali batas_waktu rekomendasi di-set/diubah.
 * Membuat/update jadwal pengingat di tabel rekomendasi_reminder.
 */
if (!function_exists('buatJadwalReminder')) {
    function buatJadwalReminder(int $rekomendasiId, string $batasWaktu): void
    {
        if (!$batasWaktu) return;

        $db       = \Config\Database::connect();
        $deadline = strtotime($batasWaktu);
        $now      = date('Y-m-d H:i:s');

        // Trigger: hari sebelum deadline (positif = H-N, 0 = hari H, negatif = terlambat)
        $triggers = [7, 3, 1, 0, -1];

        foreach ($triggers as $hari) {
            // target_date = deadline - hari hari
            $targetDate = date('Y-m-d', strtotime("-{$hari} days", $deadline));

            // Upsert: update jika sudah ada (batas_waktu diubah), insert jika belum
            $existing = $db->table('rekomendasi_reminder')
                ->where('rekomendasi_id', $rekomendasiId)
                ->where('hari_trigger', $hari)
                ->get()->getRowArray();

            if ($existing) {
                // Reset sent flags jika jadwal berubah
                if ($existing['trigger_date'] !== $targetDate) {
                    $db->table('rekomendasi_reminder')
                        ->where('id', $existing['id'])
                        ->update([
                            'trigger_date' => $targetDate,
                            'sent_inapp'   => 0,
                            'sent_email'   => 0,
                            'sent_wa'      => 0,
                            'sent_at'      => null,
                        ]);
                }
            } else {
                $db->table('rekomendasi_reminder')->insert([
                    'rekomendasi_id' => $rekomendasiId,
                    'trigger_date'   => $targetDate,
                    'hari_trigger'   => $hari,
                    'sent_inapp'     => 0,
                    'sent_email'     => 0,
                    'sent_wa'        => 0,
                    'sent_at'        => null,
                    'created_at'     => $now,
                ]);
            }
        }
    }
}

if (!function_exists('renderContent')) {
    function renderContent(?string $text, string $emptyPlaceholder = ''): string
    {
        if ($text === null || trim($text) === '') {
            return $emptyPlaceholder
                ? '<span style="color:#94a3b8;font-style:italic">' . esc($emptyPlaceholder) . '</span>'
                : '';
        }
        $trimmed = trim($text);
        // Quill output selalu dimulai dengan tag HTML seperti <p>
        if (str_starts_with($trimmed, '<')) {
            // Strip hanya tag berbahaya, sisanya aman (Quill tidak bisa inject script)
            return strip_tags($trimmed, '<p><br><strong><em><u><s><ol><ul><li><h2><h3><span><div>');
        }
        // Plain text lama — escape + nl2br
        return nl2br(esc($trimmed));
    }
}
