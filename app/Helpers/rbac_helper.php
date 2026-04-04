<?php

if (!function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
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


/*if (!function_exists('getMenus')) {
    function getMenus(): array
    {
        $menuModel = new \App\Models\MenuModel();
        $allMenus  = $menuModel->getActiveMenus();
        $menus     = [];

        foreach ($allMenus as $menu) {
            if ($menu['permission'] === null || hasPermission($menu['permission'])) {
                $menus[] = $menu;
            }
        }

        return $menus;
    }
}*/

if (!function_exists('getMenus')) {
    function getMenus(): array
    {
        $menuModel = new \App\Models\MenuModel();
        $allMenus  = $menuModel->getActiveMenus();
        $result    = [];

        foreach ($allMenus as $menu) {
            // Cek permission menu ini
            $canAccess = ($menu['permission'] === null || hasPermission($menu['permission']));

            // Cek apakah punya children yang bisa diakses
            $hasAccessibleChild = false;
            foreach ($allMenus as $child) {
                if ($child['parent_id'] == $menu['id']) {
                    if ($child['permission'] === null || hasPermission($child['permission'])) {
                        $hasAccessibleChild = true;
                        break;
                    }
                }
            }

            // Tampilkan jika:
            // 1. Menu ini bisa diakses langsung, ATAU
            // 2. Menu ini adalah parent yang punya child accessible
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
    function logActivity(string $action, string $module = '', string $description = '', ?int $userId = null, ?string $userName = null): void
    {
        // Ambil dari session jika tidak di-pass manual
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
            'ip_address'  => $request->getIPAddress(),
            'user_agent'  => $request->getUserAgent()->getAgentString(),
        ]);
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
            'admin'     => null, // skip
            'users'     => 'Users',
            'roles'     => 'Roles',
            'menus'     => 'Menus',
            'dashboard' => 'Dashboard',
            'create'    => 'Tambah',
            'edit'      => 'Edit',
            'store'     => 'Simpan',
            'update'    => 'Update',
            'delete'    => 'Hapus',
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