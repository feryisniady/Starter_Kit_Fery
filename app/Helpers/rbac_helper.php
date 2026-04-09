<?php

if (!function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
        // PRIORITAS: ambil dari session
        $permissions = session()->get('user_permissions');

        if (is_array($permissions)) {
            return in_array($permission, $permissions);
        }

        // FALLBACK (legacy support)
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $db = \Config\Database::connect();

        $result = $db->table('role_permissions rp')
            ->join('permissions p', 'p.id = rp.permission_id')
            ->join('user_roles ur', 'ur.role_id = rp.role_id')
            ->where('ur.user_id', $userId)
            ->where('p.name', $permission)
            ->countAllResults();

        return $result > 0;
    }
}

if (!function_exists('hasRole')) {
    function hasRole(string $role): bool
    {
        // PRIORITAS: session
        $roles = session()->get('roles');

        if (is_array($roles)) {
            return in_array($role, $roles);
        }

        // FALLBACK
        $userId = session()->get('user_id');
        if (!$userId) return false;

        $db = \Config\Database::connect();

        $result = $db->table('user_roles ur')
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->where('r.name', $role)
            ->countAllResults();

        return $result > 0;
    }
}

if (!function_exists('getUserRoleLabel')) {
    function getUserRoleLabel(): string
    {
        // Prioritas: ambil dari session (diset saat login, dinamis dari DB)
        $label = session()->get('user_role_label');
        if ($label) return $label;

        // Fallback: ambil dari nama role pertama
        $roles = session()->get('roles') ?? [];
        return !empty($roles) ? ucfirst($roles[0]) : 'User';
    }
}


if (!function_exists('getUserAvatar')) {
    function getUserAvatar(): string
    {
        $avatar = session()->get('user_avatar');

        if ($avatar) {
            return base_url($avatar);
        }

        $name = session()->get('user_name') ?? 'A';
        return strtoupper(substr($name, 0, 1));
    }
}


if (!function_exists('getMenus')) {
    function getMenus(): array
    {
        $menuModel = new \App\Models\MenuModel();
        $allMenus  = $menuModel->getActiveMenus();

        $result = [];

        // Pre-group children by parent_id (O(n))
        $childrenMap = [];
        foreach ($allMenus as $menu) {
            $parentId = $menu['parent_id'] ?? 0;
            $childrenMap[$parentId][] = $menu;
        }

        // Loop utama (O(n))
        foreach ($allMenus as $menu) {
            $menuId   = $menu['id'];
            $parentId = $menu['parent_id'] ?? 0;

            // Cek akses menu utama
            $canAccess = ($menu['permission'] === null || hasPermission($menu['permission']));

            // Cek child (kalau ada)
            $hasAccessibleChild = false;
            if (isset($childrenMap[$menuId])) {
                foreach ($childrenMap[$menuId] as $child) {
                    if ($child['permission'] === null || hasPermission($child['permission'])) {
                        $hasAccessibleChild = true;
                        break;
                    }
                }
            }

            // Tampilkan jika:
            // - bisa akses langsung
            // - atau parent punya child yang bisa diakses
            if ($canAccess || $hasAccessibleChild) {
                $result[] = $menu;
            }
        }

        return $result;
    }
}

if (!function_exists('isActiveMenu')) {
    function isActiveMenu(string $url): string
    {
        $currentUrl = '/' . service('request')->getUri()->getPath();
        return $currentUrl === $url ? 'active' : '';
    }
}


if (!function_exists('logActivity')) {
    function logActivity(string $action, string $module = '', string $description = '', ?int $userId = null, ?string $userName = null, array $meta = []): void
    {
        $userId   = $userId   ?? (int) session()->get('user_id');
        $userName = $userName ?? session()->get('user_name');

        $request = service('request');

        $model = new \App\Models\ActivityLogModel();
        $model->insert([
            'user_id'     => $userId ?: null,
            'user_name'   => $userName,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'meta'        => !empty($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'ip_address'  => $request->getIPAddress(),
            'user_agent'  => $request->getUserAgent()->getAgentString(),
        ]);
    }
}

if (!function_exists('app_setting')) {
    /**
     * Ambil nilai pengaturan aplikasi dari DB.
     * Fallback: Brand config → $default
     */
    function app_setting(string $key, mixed $default = null): mixed
    {
        static $map = null;

        if ($map === null) {
            $cache = \Config\Services::cache();
            $map   = $cache->get('app_settings_map');

            if ($map === null) {
                try {
                    $db   = \Config\Database::connect();
                    $rows = $db->table('app_settings')->get()->getResultArray();
                    $map  = [];
                    foreach ($rows as $row) {
                        $map[$row['key']] = $row['value'];
                    }
                    $cache->save('app_settings_map', $map, 3600);
                } catch (\Throwable $e) {
                    $map = [];
                }
            }
        }

        if (array_key_exists($key, $map) && $map[$key] !== null && $map[$key] !== '') {
            return $map[$key];
        }

        // Fallback ke Brand config
        $brand = config('Brand');
        $brandMap = [
            'app_name'    => $brand->appName,
            'app_version' => $brand->appVersion,
            'app_tagline' => $brand->appTagline,
            'org_name'    => $brand->orgName,
            'org_short'   => $brand->orgShort,
            'org_website' => $brand->orgWebsite,
        ];

        return $brandMap[$key] ?? $default;
    }
}

if (!function_exists('clear_setting_cache')) {
    function clear_setting_cache(): void
    {
        \Config\Services::cache()->delete('app_settings_map');
    }
}

if (!function_exists('notify')) {
    /**
     * Kirim notifikasi in-app ke satu atau banyak user.
     *
     * Contoh pemakaian:
     *   notify($userId, 'Judul', 'Pesan singkat', '/link/tujuan', 'info');
     *   notify([1,2,3], 'Dokumen Baru', 'Budi mengupload SK', '/dokumen/5', 'success');
     */
    function notify(int|array $userIds, string $title, string $message = '', string $url = '', string $type = 'info'): void
    {
        $model = new \App\Models\NotificationModel();
        $model->send($userIds, $title, $message, $url, $type);
    }
}

if (!function_exists('notifyAllAdmins')) {
    /**
     * Kirim notifikasi ke semua user yang punya role admin/superadmin.
     */
    function notifyAllAdmins(string $title, string $message = '', string $url = '', string $type = 'info'): void
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

if (!function_exists('breadcrumb')) {
    function breadcrumb(): string
    {
        $uri      = service('request')->getUri();
        $segments = $uri->getSegments();
        $html     = '<div class="topbar-breadcrumb">';
        $html    .= '<a href="/dashboard"><i class="fas fa-house" style="font-size:12px"></i></a>';

        $path  = '';
        $total = count($segments);

        // Label mapping — tambah sesuai kebutuhan
        $labels = [
            'admin'          => null, // skip
            'users'          => 'Users',
            'roles'          => 'Roles',
            'menus'          => 'Menus',
            'permissions'    => 'Permissions',
            'settings'       => 'Pengaturan',
            'activity-logs'  => 'Activity Log',
            'dashboard'      => 'Dashboard',
            'create'         => 'Tambah',
            'edit'           => 'Edit',
            'store'          => 'Simpan',
            'update'         => 'Update',
            'delete'         => 'Hapus',
            'export'         => 'Export',
        ];

        $validSegments = [];
        foreach ($segments as $seg) {
            // Skip angka (ID) dan segment 'admin'
            if (is_numeric($seg) || $seg === 'admin') continue;
            $validSegments[] = $seg;
        }

        foreach ($validSegments as $i => $seg) {
            $label = $labels[$seg] ?? ucfirst(str_replace(['-', '_'], ' ', $seg));
            $path .= '/' . $seg;
            $isLast = ($i === count($validSegments) - 1);
            $html .= '<i class="fas fa-chevron-right" style="font-size:10px;color:#cbd5e1"></i>';
            if ($isLast) {
                $html .= '<span class="current">' . $label . '</span>';
            } else {
                $html .= '<a href="' . $path . '" style="color:#94a3b8">' . $label . '</a>';
            }
        }

        $html .= '</div>';
        return $html;
    }
}