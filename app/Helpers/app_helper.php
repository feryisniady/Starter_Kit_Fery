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
