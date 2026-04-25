<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ============================================================
 * STANDARD MENU SEEDER — Satu-satunya sumber kebenaran Menu
 * ============================================================
 * Menggantikan: MenuSeeder, PkptMenuSeeder, PengawasanSeeder (bagian menu),
 *               ProfileMenuSeeder
 *
 * Jalankan: php spark db:seed StandardMenuSeeder
 * Idempotent — upsert berdasarkan URL, aman dijalankan berulang.
 *
 * STRUKTUR SIDEBAR:
 *
 *  SECTION: system  (hanya superadmin/admin)
 *  ├─ Dashboard              permission: null
 *  └─ Manajemen Sistem       permission: user.view
 *     ├─ Users               permission: user.view
 *     ├─ Roles               permission: role.view
 *     ├─ Menus               permission: menu.view
 *     ├─ Permissions         permission: permission.view
 *     ├─ Activity Log        permission: activitylog.view
 *     └─ Pengaturan App      permission: setting.manage
 *
 *  SECTION: pengawasan  (staf inspektorat)
 *  ├─ Master Data            permission: master.view
 *  │  ├─ Irban               permission: master.manage
 *  │  ├─ Entitas / OPD       permission: master.manage
 *  │  ├─ SDM Pemeriksa       permission: master.manage
 *  │  └─ Kode Temuan         permission: kode_temuan.view
 *  ├─ PKPT                   permission: pkpt.view
 *  │  ├─ Hari Libur          permission: hari_libur.manage
 *  │  ├─ Header PKPT         permission: pkpt.manage
 *  │  ├─ Daftar PKPT         permission: pkpt.view
 *  │  └─ Monitoring SDM HP   permission: pkpt.view
 *  └─ SPT                    permission: spt.view
 *
 * LOGIKA DUA LAPIS:
 *   Menu permission  = visibilitas sidebar (UX)
 *   Route filter     = keamanan akses URL (security)
 *   Keduanya pakai string permission yang SAMA agar sinkron.
 * ============================================================
 */
class StandardMenuSeeder extends Seeder
{
    // =========================================================================
    // DEFINISI MENU
    // Format setiap item:
    //   label      : teks di sidebar
    //   url        : path lengkap (/admin/...) atau '#anchor' untuk parent
    //   icon       : class FontAwesome (fas fa-xxx)
    //   permission : string permission ATAU null (tampil untuk semua user login)
    //   section    : 'system' | 'pengawasan' (untuk grouping sidebar)
    //   sort_order : urutan tampil (kelipatan 10 agar mudah sisip)
    //   children   : array item anak (sub-menu)
    // =========================================================================
    private array $menus = [

        // ══════════════════════════════════════════════════════════════════════
        // SECTION: system
        // ══════════════════════════════════════════════════════════════════════

        [
            'label'      => 'Dashboard',
            'url'        => '/dashboard',
            'icon'       => 'fas fa-house',
            'permission' => null,                 // semua user login bisa lihat
            'section'    => 'system',
            'sort_order' => 10,
            'children'   => [],
        ],

        [
            'label'      => 'Manajemen Sistem',
            'url'        => '#sistem',
            'icon'       => 'fas fa-gear',
            'permission' => 'user.view',          // parent hanya tampil jika minimal 1 child accessible
            'section'    => 'system',
            'sort_order' => 20,
            'children'   => [
                [
                    'label'      => 'Users',
                    'url'        => '/admin/users',
                    'icon'       => 'fas fa-users',
                    'permission' => 'user.view',
                    'sort_order' => 21,
                ],
                [
                    'label'      => 'Roles',
                    'url'        => '/admin/roles',
                    'icon'       => 'fas fa-shield-halved',
                    'permission' => 'role.view',
                    'sort_order' => 22,
                ],
                [
                    'label'      => 'Menus',
                    'url'        => '/admin/menus',
                    'icon'       => 'fas fa-bars',
                    'permission' => 'menu.view',
                    'sort_order' => 23,
                ],
                [
                    'label'      => 'Permissions',
                    'url'        => '/admin/permissions',
                    'icon'       => 'fas fa-key',
                    'permission' => 'permission.view',
                    'sort_order' => 24,
                ],
                [
                    'label'      => 'Activity Log',
                    'url'        => '/admin/activity-logs',
                    'icon'       => 'fas fa-clock-rotate-left',
                    'permission' => 'activitylog.view',
                    'sort_order' => 25,
                ],
                [
                    'label'      => 'Pengaturan App',
                    'url'        => '/admin/settings',
                    'icon'       => 'fas fa-sliders',
                    'permission' => 'setting.manage',
                    'sort_order' => 26,
                ],
            ],
        ],

        // ══════════════════════════════════════════════════════════════════════
        // SECTION: pengawasan
        // ══════════════════════════════════════════════════════════════════════

        [
            'label'      => 'Master Data',
            'url'        => '#master-pengawasan',
            'icon'       => 'fas fa-database',
            'permission' => 'master.view',
            'section'    => 'pengawasan',
            'sort_order' => 100,
            'children'   => [
                [
                    'label'      => 'Irban',
                    'url'        => '/admin/master/irban',
                    'icon'       => 'fas fa-sitemap',
                    'permission' => 'master.manage',
                    'sort_order' => 101,
                ],
                [
                    'label'      => 'Entitas / OPD',
                    'url'        => '/admin/master/entitas',
                    'icon'       => 'fas fa-building',
                    'permission' => 'master.manage',
                    'sort_order' => 102,
                ],
                [
                    'label'      => 'SDM Pemeriksa',
                    'url'        => '/admin/master/sdm',
                    'icon'       => 'fas fa-user-tie',
                    'permission' => 'master.manage',
                    'sort_order' => 103,
                ],
                [
                    'label'      => 'Kode Temuan',
                    'url'        => '/admin/master/kode-temuan',
                    'icon'       => 'fas fa-code',
                    'permission' => 'kode_temuan.view',
                    'sort_order' => 104,
                ],
            ],
        ],

        [
            'label'      => 'PKPT',
            'url'        => '#pkpt',
            'icon'       => 'fas fa-clipboard-list',
            'permission' => 'pkpt.view',
            'section'    => 'pengawasan',
            'sort_order' => 200,
            'children'   => [
                [
                    'label'      => 'Hari Libur',
                    'url'        => '/admin/pkpt/hari-libur',
                    'icon'       => 'fas fa-calendar-xmark',
                    'permission' => 'hari_libur.manage',
                    'sort_order' => 201,
                ],
                [
                    'label'      => 'Header PKPT',
                    'url'        => '/admin/pkpt/setting',
                    'icon'       => 'fas fa-file-contract',
                    'permission' => 'pkpt.manage',
                    'sort_order' => 202,
                ],
                [
                    'label'      => 'Daftar PKPT',
                    'url'        => '/admin/pkpt',
                    'icon'       => 'fas fa-list-check',
                    'permission' => 'pkpt.view',
                    'sort_order' => 203,
                ],
                [
                    'label'      => 'Monitoring SDM',
                    'url'        => '/admin/pkpt/monitoring-sdm',
                    'icon'       => 'fas fa-chart-bar',
                    'permission' => 'pkpt.view',
                    'sort_order' => 204,
                ],
            ],
        ],

        [
            'label'      => 'SPT',
            'url'        => '/admin/spt',
            'icon'       => 'fas fa-file-signature',
            'permission' => 'spt.view',
            'section'    => 'pengawasan',
            'sort_order' => 300,
            'children'   => [],
        ],
    ];

    // =========================================================================
    // RUNNER
    // =========================================================================

    public function run(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════╗\n";
        echo "║        StandardMenuSeeder — Mulai            ║\n";
        echo "╚══════════════════════════════════════════════╝\n\n";

        $inserted = 0;
        $updated  = 0;

        foreach ($this->menus as $menuData) {
            $parentId = $this->upsertMenu($menuData, null);
            $parentId ? $updated++ : $inserted++;

            foreach (($menuData['children'] ?? []) as $child) {
                $child['section']    = $menuData['section'];
                $this->upsertMenu($child, $parentId);
            }
        }

        $total = $this->db->table('menus')->countAllResults();
        echo "\n✓ StandardMenuSeeder selesai — total {$total} menu di DB.\n";
        echo "  Catatan: menu cache dibersihkan otomatis saat user login.\n\n";
    }

    // ── Upsert satu menu item, return ID ─────────────────────────────────────
    private function upsertMenu(array $data, ?int $parentId): int
    {
        $now      = date('Y-m-d H:i:s');
        $existing = $this->db->table('menus')->where('url', $data['url'])->get()->getRowArray();

        $row = [
            'label'      => $data['label'],
            'url'        => $data['url'],
            'icon'       => $data['icon'],
            'permission' => $data['permission'] ?? null,
            'parent_id'  => $parentId,
            'sort_order' => $data['sort_order'],
            'is_active'  => 1,
            'section'    => $data['section'] ?? 'system',
            'updated_at' => $now,
        ];

        if ($existing) {
            $this->db->table('menus')->where('id', $existing['id'])->update($row);
            $id = (int)$existing['id'];
            echo "  [≈] " . str_pad("'{$data['label']}'", 28) . " (ID:{$id})\n";
        } else {
            $row['created_at'] = $now;
            $this->db->table('menus')->insert($row);
            $id = (int)$this->db->insertID();
            echo "  [+] " . str_pad("'{$data['label']}'", 28) . " (ID:{$id})\n";
        }

        return $id;
    }
}
