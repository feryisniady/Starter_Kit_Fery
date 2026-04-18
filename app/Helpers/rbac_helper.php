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
        // Cache per-user berdasarkan user_id + permissions hash
        $userId      = session()->get('user_id');
        $permissions = session()->get('user_permissions') ?? [];
        $cacheKey    = 'menus_u' . $userId . '_' . md5(implode(',', $permissions));

        $cache  = \Config\Services::cache();
        $cached = $cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

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

            $canAccess = ($menu['permission'] === null || hasPermission($menu['permission']));

            $hasAccessibleChild = false;
            if (isset($childrenMap[$menuId])) {
                foreach ($childrenMap[$menuId] as $child) {
                    if ($child['permission'] === null || hasPermission($child['permission'])) {
                        $hasAccessibleChild = true;
                        break;
                    }
                }
            }

            if ($canAccess || $hasAccessibleChild) {
                $result[] = $menu;
            }
        }

        $cache->save($cacheKey, $result, 3600);

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

if (!function_exists('clear_menu_cache')) {
    function clear_menu_cache(): void
    {
        \Config\Services::cache()->deleteMatching('menus_u*');
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

// ============================================================
// AUDIT HELPERS — Akses berbasis peran dalam tim SPT
// ============================================================
// Kewenangan modul pengawasan (KM, PKA, KKA) TIDAK mengikuti
// system role, melainkan peran_spt di spt_tim per SPT.
// Satu user bisa KT di SPT A dan AT di SPT B secara bersamaan.
// ============================================================

if (!function_exists('getCurrentSdmId')) {
    /**
     * Ambil sdm.id milik user yang sedang login.
     * Return null jika user tidak punya record SDM.
     */
    function getCurrentSdmId(): ?int
    {
        static $cache = [];
        $userId = (int) session()->get('user_id');
        if (!$userId) return null;

        if (!isset($cache[$userId])) {
            $row = \Config\Database::connect()
                ->table('sdm')->where('user_id', $userId)->get()->getRowArray();
            $cache[$userId] = $row ? (int)$row['id'] : null;
        }
        return $cache[$userId];
    }
}

if (!function_exists('getCurrentSdm')) {
    /**
     * Ambil seluruh row sdm milik user yang sedang login.
     */
    function getCurrentSdm(): ?array
    {
        $userId = (int) session()->get('user_id');
        if (!$userId) return null;
        return \Config\Database::connect()
            ->table('sdm')->where('user_id', $userId)->get()->getRowArray() ?: null;
    }
}

if (!function_exists('getPeranInSpt')) {
    /**
     * Ambil peran_spt user saat ini dalam SPT tertentu.
     * Contoh return: 'Ketua Tim', 'Anggota Tim', 'Pengendali Teknis', dll.
     * Return null jika user tidak ada dalam tim SPT tersebut.
     */
    function getPeranInSpt(int $sptId): ?string
    {
        $sdmId = getCurrentSdmId();
        if (!$sdmId) return null;

        $row = \Config\Database::connect()
            ->table('spt_tim')
            ->where('spt_id', $sptId)
            ->where('sdm_id', $sdmId)
            ->get()->getRowArray();

        return $row ? $row['peran_spt'] : null;
    }
}

if (!function_exists('isInSpt')) {
    /** Apakah user saat ini terdaftar dalam tim SPT ini? */
    function isInSpt(int $sptId): bool
    {
        return getPeranInSpt($sptId) !== null;
    }
}

if (!function_exists('isAuditAdmin')) {
    /**
     * Admin bypass — boleh akses semua modul pengawasan tanpa batasan peran.
     */
    function isAuditAdmin(): bool
    {
        return hasRole('superadmin') || hasRole('admin');
    }
}

if (!function_exists('isDalnisInSpt')) {
    /**
     * Apakah user adalah Pengendali Teknis (Dalnis) dalam SPT ini?
     * Kewenangan: KM-5 (reviu PKA), KM-11 (reviu laporan), catatan KKA.
     */
    function isDalnisInSpt(int $sptId): bool
    {
        $peran = getPeranInSpt($sptId);
        return $peran !== null && str_contains(strtolower($peran), 'pengendali');
    }
}

if (!function_exists('isKtInSpt')) {
    /**
     * Apakah user adalah Ketua Tim dalam SPT ini?
     * Kewenangan: KM-1, KM-4, KM-5b, KM-10, lihat semua KKA, compile temuan.
     */
    function isKtInSpt(int $sptId): bool
    {
        $peran = getPeranInSpt($sptId);
        return $peran !== null && str_contains(strtolower($peran), 'ketua');
    }
}

if (!function_exists('isAtInSpt')) {
    /**
     * Apakah user adalah Anggota Tim dalam SPT ini?
     * Kewenangan: KM-2 (milik sendiri), Independensi (milik sendiri), KKA (milik sendiri).
     */
    function isAtInSpt(int $sptId): bool
    {
        $peran = getPeranInSpt($sptId);
        return $peran !== null && str_contains(strtolower($peran), 'anggota');
    }
}

if (!function_exists('isPjInSpt')) {
    /**
     * Apakah user adalah Penanggung Jawab atau Wakil PJ dalam SPT ini?
     * Kewenangan: lihat semua (view only).
     */
    function isPjInSpt(int $sptId): bool
    {
        $peran = strtolower(getPeranInSpt($sptId) ?? '');
        return str_contains($peran, 'penanggung') || str_contains($peran, 'wakil');
    }
}

if (!function_exists('canEditKmInSpt')) {
    /**
     * Apakah user boleh mengedit KM tertentu dalam SPT ini?
     *
     * Aturan status SPT:
     *  - KM Fase 1 (persiapan): hanya bisa diedit saat SPT masih draft atau setelah terbit
     *    (diajukan/acc_* = sedang approval → lock)
     *  - KM Fase 2 & 3 (pelaksanaan & pelaporan): bebas diedit kapanpun (SPT terbit)
     *
     * @param string $km  'km1','km2','km4','km5','km5b','independensi','km7','km9','km10','km11'
     */
    function canEditKmInSpt(int $sptId, string $km): bool
    {
        if (isAuditAdmin()) return true;

        // Cek status SPT untuk KM Fase 1
        $fase1Items = ['km1','km2','km4','km5','km5b','independensi'];
        if (in_array($km, $fase1Items)) {
            $db  = \Config\Database::connect();
            $spt = $db->table('spt')->select('status')->where('id', $sptId)->get()->getRowArray();
            $statusSpt = $spt['status'] ?? 'draft';
            // Kunci saat SPT sedang dalam proses approval
            if (in_array($statusSpt, ['diajukan','acc_irban','acc_evlap','acc_sekretaris'])) {
                return false;
            }
        }

        return match($km) {
            'km1','km4','km5b','km10'   => isDalnisInSpt($sptId) || isKtInSpt($sptId),
            'km5','km11'                => isDalnisInSpt($sptId),
            'km2','independensi'        => isDalnisInSpt($sptId) || isKtInSpt($sptId) || isAtInSpt($sptId),
            default                     => isInSpt($sptId),
        };
    }
}

if (!function_exists('isSptLocked')) {
    /**
     * Apakah SPT sedang dalam proses approval (tidak bisa diedit kontennya)?
     * true  = diajukan / acc_irban / acc_evlap / acc_sekretaris
     * false = draft atau terbit
     */
    function isSptLocked(int $sptId): bool
    {
        $db  = \Config\Database::connect();
        $spt = $db->table('spt')->select('status')->where('id', $sptId)->get()->getRowArray();
        return in_array($spt['status'] ?? 'draft', ['diajukan','acc_irban','acc_evlap','acc_sekretaris']);
    }
}

if (!function_exists('canViewSptAudit')) {
    /**
     * Apakah user boleh mengakses halaman audit SPT ini sama sekali?
     * (masuk ke KM / PKA / KKA)
     */
    function canViewSptAudit(int $sptId): bool
    {
        if (isAuditAdmin()) return true;
        // Semua anggota tim boleh lihat SPT
        if (isInSpt($sptId)) return true;
        // Pejabat lintas-SPT: inspektur, sekretaris, evlap tetap bisa lihat
        return hasRole('inspektur') || hasRole('sekretaris') ||
               hasRole('evlap')     || hasRole('subbag_evlap') ||
               hasPermission('spt.manage_all');
    }
}
