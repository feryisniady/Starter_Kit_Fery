<?php

/**
 * ======================================================
 * APP HELPER
 * Utility global (non-RBAC, non-UI spesifik menu)
 * ======================================================
 */

/**
 * ======================================================
 * BREADCRUMB
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
 * APP SETTING (CACHE)
 * ======================================================
 */
if (!function_exists('app_setting')) {
    function app_setting(string $key, $default = null)
    {
        static $settings = null;

        if ($settings === null) {
            $cache = \Config\Services::cache();
            $settings = $cache->get('app_settings');

            if (!$settings) {
                try {
                    $db = \Config\Database::connect();
                    $rows = $db->table('app_settings')->get()->getResultArray();

                    $settings = [];
                    foreach ($rows as $row) {
                        $settings[$row['key']] = $row['value'];
                    }

                    $cache->save('app_settings', $settings, 3600);
                } catch (\Throwable $e) {
                    $settings = [];
                }
            }
        }

        return $settings[$key] ?? $default;
    }
}

/**
 * ======================================================
 * CLEAR CACHE SETTING
 * ======================================================
 */
if (!function_exists('clear_setting_cache')) {
    function clear_setting_cache(): void
    {
        \Config\Services::cache()->delete('app_settings');
    }
}

/**
 * ======================================================
 * LOG ACTIVITY
 * ======================================================
 */
if (!function_exists('logActivity')) {
    function logActivity(
        string $action,
        string $module = '',
        string $description = '',
        ?int $userId = null,
        ?string $userName = null,
        array $meta = []
    ): void {
        $userId   = $userId   ?? session()->get('user_id');
        $userName = $userName ?? session()->get('user_name');

        $request = service('request');

        $model = new \App\Models\ActivityLogModel();
        $model->insert([
            'user_id'     => $userId ?: null,
            'user_name'   => $userName,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'meta'        => !empty($meta) ? json_encode($meta) : null,
            'ip_address'  => $request->getIPAddress(),
            'user_agent'  => $request->getUserAgent()->getAgentString(),
        ]);
    }
}

/**
 * ======================================================
 * NOTIFICATION
 * ======================================================
 */
if (!function_exists('notify')) {
    function notify($userIds, string $title, string $message = '', string $url = '', string $type = 'info'): void
    {
        $model = new \App\Models\NotificationModel();
        $model->send($userIds, $title, $message, $url, $type);
    }
}

/**
 * ======================================================
 * NOTIFY ADMIN
 * ======================================================
 */
if (!function_exists('notifyAdmins')) {
    function notifyAdmins(string $title, string $message = '', string $url = '', string $type = 'info'): void
    {
        $db = \Config\Database::connect();

        $rows = $db->table('user_roles ur')
            ->select('ur.user_id')
            ->join('roles r', 'r.id = ur.role_id')
            ->whereIn('r.name', ['admin', 'superadmin'])
            ->get()->getResultArray();

        if (empty($rows)) return;

        $ids = array_column($rows, 'user_id');

        notify($ids, $title, $message, $url, $type);
    }
}