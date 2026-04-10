<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seeder lengkap modul Pengawasan Inspektorat.
 * Mencakup: Permissions, Roles, Role-Permissions, dan Menus.
 *
 * php spark db:seed PengawasanSeeder
 *
 * Workflow yang di-cover:
 *  Hari Libur → Header PKPT → PKPT per Irban → Kegiatan → SPT → PKA → Temuan → Rekomendasi
 *
 * Roles:
 *  inspektur     = TTE, pantau semua, approve akhir
 *  kepala_irban  = manage PKPT irbannya, approve SPT tahap 1, kelola PKA & Temuan
 *  admin_program = input & manage PKPT, hari libur, header PKPT
 *  admin_tu      = buat & manage SPT
 *  admin_evlap   = approve SPT tahap 2, rekap temuan
 *  sekretaris    = approve SPT tahap 3
 *  auditor       = PKA, temuan, rekomendasi
 */
class PengawasanSeeder extends Seeder
{
    // =========================================================================
    // KONFIGURASI UTAMA
    // =========================================================================

    private array $permissions = [
        // Master Data
        ['name' => 'master.view',            'note' => 'Lihat master data (irban, entitas, sdm)'],
        ['name' => 'master.manage',          'note' => 'Kelola master data'],
        // Hari Libur
        ['name' => 'hari_libur.manage',      'note' => 'Kelola hari libur nasional'],
        // PKPT
        ['name' => 'pkpt.view',              'note' => 'Lihat PKPT'],
        ['name' => 'pkpt.input',             'note' => 'Input kegiatan PKPT'],
        ['name' => 'pkpt.manage',            'note' => 'Kelola header & setting PKPT'],
        ['name' => 'pkpt.approve',           'note' => 'Setujui Header PKPT (tanda tangan Bupati)'],
        // SPT
        ['name' => 'spt.view',               'note' => 'Lihat SPT'],
        ['name' => 'spt.create',             'note' => 'Buat SPT dari kegiatan PKPT'],
        ['name' => 'spt.manage_all',         'note' => 'Lihat & kelola SPT semua irban'],
        ['name' => 'spt.approve',            'note' => 'Approve/reject SPT dalam workflow'],
        // PKA
        ['name' => 'pka.view',               'note' => 'Lihat Program Kerja Audit'],
        ['name' => 'pka.manage',             'note' => 'Kelola PKA (tambah, edit, selesai)'],
        // Temuan & Rekomendasi
        ['name' => 'temuan.view',            'note' => 'Lihat temuan audit'],
        ['name' => 'temuan.input',           'note' => 'Input temuan & rekomendasi'],
        ['name' => 'temuan.manage',          'note' => 'Kelola & update status temuan'],
        // Kode Temuan
        ['name' => 'kode_temuan.view',       'note' => 'Lihat referensi kode temuan PermenpanRB 41/2011'],
    ];

    private array $roles = [
        [
            'name'  => 'inspektur',
            'label' => 'Inspektur',
            'permissions' => [
                'master.view',
                'pkpt.view', 'pkpt.approve',
                'spt.view', 'spt.approve', 'spt.manage_all',
                'pka.view',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],
        [
            'name'  => 'kepala_irban',
            'label' => 'Kepala Irban',
            'permissions' => [
                'master.view',
                'pkpt.view', 'pkpt.input', 'pkpt.manage',
                'spt.view', 'spt.create', 'spt.approve',
                'pka.view', 'pka.manage',
                'temuan.view', 'temuan.input', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],
        [
            'name'  => 'admin_program',
            'label' => 'Admin Program',
            'permissions' => [
                'master.view', 'master.manage',
                'hari_libur.manage',
                'pkpt.view', 'pkpt.input', 'pkpt.manage',
                'spt.view',
                'kode_temuan.view',
            ],
        ],
        [
            'name'  => 'admin_tu',
            'label' => 'Admin Tata Usaha',
            'permissions' => [
                'master.view', 'master.manage',
                'pkpt.view',
                'spt.view', 'spt.create', 'spt.manage_all',
                'kode_temuan.view',
            ],
        ],
        [
            'name'  => 'admin_evlap',
            'label' => 'Admin Evaluasi & Pelaporan',
            'permissions' => [
                'master.view',
                'pkpt.view',
                'spt.view', 'spt.approve', 'spt.manage_all',
                'pka.view',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],
        [
            'name'  => 'sekretaris',
            'label' => 'Sekretaris',
            'permissions' => [
                'pkpt.view',
                'spt.view', 'spt.approve',
            ],
        ],
        [
            'name'  => 'auditor',
            'label' => 'Auditor',
            'permissions' => [
                'spt.view',
                'pka.view', 'pka.manage',
                'temuan.view', 'temuan.input',
                'kode_temuan.view',
            ],
        ],
    ];

    // Menu struktur — section 'pengawasan'
    private array $menus = [
        // ── MASTER DATA ────────────────────────────────────────────────────
        [
            'label' => 'Master Data',        'url' => '#master-pengawasan',
            'icon'  => 'fas fa-database',    'permission' => 'master.view',
            'parent_url' => null,            'section' => 'pengawasan',
            'children' => [
                ['label'=>'Irban',          'url'=>'/admin/master/irban',        'icon'=>'fas fa-sitemap',     'permission'=>'master.manage'],
                ['label'=>'Entitas / OPD',  'url'=>'/admin/master/entitas',      'icon'=>'fas fa-building',    'permission'=>'master.manage'],
                ['label'=>'SDM Pemeriksa',  'url'=>'/admin/master/sdm',          'icon'=>'fas fa-user-tie',    'permission'=>'master.manage'],
                ['label'=>'Kode Temuan',    'url'=>'/admin/master/kode-temuan',  'icon'=>'fas fa-code',        'permission'=>'kode_temuan.view'],
            ],
        ],
        // ── PKPT ───────────────────────────────────────────────────────────
        [
            'label' => 'PKPT',               'url' => '#pkpt',
            'icon'  => 'fas fa-clipboard-list', 'permission' => 'pkpt.view',
            'parent_url' => null,            'section' => 'pengawasan',
            'children' => [
                ['label'=>'Hari Libur',     'url'=>'/admin/pkpt/hari-libur',     'icon'=>'fas fa-calendar-xmark', 'permission'=>'hari_libur.manage'],
                ['label'=>'Header PKPT',    'url'=>'/admin/pkpt/setting',        'icon'=>'fas fa-file-contract',  'permission'=>'pkpt.manage'],
                ['label'=>'Daftar PKPT',    'url'=>'/admin/pkpt',                'icon'=>'fas fa-list-check',     'permission'=>'pkpt.view'],
            ],
        ],
        // ── SPT ────────────────────────────────────────────────────────────
        [
            'label' => 'SPT',                'url' => '/admin/spt',
            'icon'  => 'fas fa-file-signature', 'permission' => 'spt.view',
            'parent_url' => null,            'section' => 'pengawasan',
            'children' => [],
        ],
    ];

    // =========================================================================
    // RUN
    // =========================================================================

    public function run()
    {
        echo "=== PengawasanSeeder ===\n\n";

        $this->seedPermissions();
        echo "\n";
        $this->seedRoles();
        echo "\n";
        $this->grantAllToAdmins();
        echo "\n";
        $this->seedMenus();

        echo "\n=== Selesai! ===\n";
        echo "Jalankan juga: php spark db:seed KodetemuanSeeder\n";
    }

    // =========================================================================
    // 1. PERMISSIONS
    // =========================================================================

    private function seedPermissions(): void
    {
        echo "--- [1] Permissions ---\n";
        foreach ($this->permissions as $p) {
            $exists = $this->db->table('permissions')->where('name', $p['name'])->countAllResults();
            if (!$exists) {
                $this->db->table('permissions')->insert([
                    'name'       => $p['name'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                echo "  + {$p['name']}\n";
            } else {
                echo "  ~ {$p['name']} (sudah ada)\n";
            }
        }
    }

    // =========================================================================
    // 2. ROLES + ROLE-PERMISSIONS
    // =========================================================================

    private function seedRoles(): void
    {
        echo "--- [2] Roles & Role-Permissions ---\n";
        foreach ($this->roles as $roleData) {
            // Upsert role
            $existing = $this->db->table('roles')->where('name', $roleData['name'])->get()->getRowArray();
            if (!$existing) {
                $this->db->table('roles')->insert([
                    'name'       => $roleData['name'],
                    'label'      => $roleData['label'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $roleId = $this->db->insertID();
                echo "  + Role '{$roleData['label']}' dibuat (ID: $roleId)\n";
            } else {
                $roleId = (int)$existing['id'];
                $this->db->table('roles')->where('id', $roleId)->update(['label' => $roleData['label']]);
                echo "  ~ Role '{$roleData['label']}' sudah ada (ID: $roleId)\n";
            }

            // Assign permissions
            $assigned = 0;
            foreach ($roleData['permissions'] as $permName) {
                $perm = $this->db->table('permissions')->where('name', $permName)->get()->getRowArray();
                if (!$perm) {
                    echo "    WARNING: permission '$permName' tidak ditemukan\n";
                    continue;
                }
                $exists = $this->db->table('role_permissions')
                    ->where('role_id', $roleId)->where('permission_id', $perm['id'])
                    ->countAllResults();
                if (!$exists) {
                    $this->db->table('role_permissions')->insert([
                        'role_id'       => $roleId,
                        'permission_id' => $perm['id'],
                    ]);
                    $assigned++;
                }
            }
            if ($assigned > 0) echo "    → $assigned permission baru di-assign\n";
        }
    }

    // =========================================================================
    // 3. GRANT ALL TO SUPERADMIN & ADMIN
    // =========================================================================

    private function grantAllToAdmins(): void
    {
        echo "--- [3] Grant semua ke superadmin & admin ---\n";
        $allPerms = array_column($this->permissions, 'name');

        foreach (['superadmin', 'admin'] as $roleName) {
            $role = $this->db->table('roles')->where('name', $roleName)->get()->getRowArray();
            if (!$role) {
                echo "  WARNING: role '$roleName' tidak ditemukan\n";
                continue;
            }
            $count = 0;
            foreach ($allPerms as $permName) {
                $perm = $this->db->table('permissions')->where('name', $permName)->get()->getRowArray();
                if (!$perm) continue;
                $exists = $this->db->table('role_permissions')
                    ->where('role_id', $role['id'])->where('permission_id', $perm['id'])
                    ->countAllResults();
                if (!$exists) {
                    $this->db->table('role_permissions')->insert([
                        'role_id'       => $role['id'],
                        'permission_id' => $perm['id'],
                    ]);
                    $count++;
                }
            }
            echo "  '$roleName' → $count permission baru\n";
        }
    }

    // =========================================================================
    // 4. MENUS
    // =========================================================================

    private function seedMenus(): void
    {
        echo "--- [4] Menus ---\n";

        // Hapus menu lama yang sudah ada (berdasarkan URL) agar bisa re-seed bersih
        // Tapi kita tidak hapus — kita pakai upsert logic (skip jika sudah ada)

        $maxSort = (int)($this->db->table('menus')->selectMax('sort_order')->get()->getRowArray()['sort_order'] ?? 0);
        $sort    = $maxSort + 10;

        foreach ($this->menus as $menuData) {
            $parentId = $this->upsertMenu([
                'label'      => $menuData['label'],
                'url'        => $menuData['url'],
                'icon'       => $menuData['icon'],
                'permission' => $menuData['permission'],
                'parent_id'  => null,
                'sort_order' => $sort,
                'section'    => $menuData['section'],
            ]);

            $childSort = $sort + 1;
            foreach ($menuData['children'] as $child) {
                $this->upsertMenu([
                    'label'      => $child['label'],
                    'url'        => $child['url'],
                    'icon'       => $child['icon'],
                    'permission' => $child['permission'],
                    'parent_id'  => $parentId,
                    'sort_order' => $childSort,
                    'section'    => $menuData['section'],
                ]);
                $childSort++;
            }

            $sort += 20;
        }
    }

    private function upsertMenu(array $data): int
    {
        $existing = $this->db->table('menus')->where('url', $data['url'])->get()->getRowArray();

        if ($existing) {
            // Update jika label/icon/permission berubah
            $this->db->table('menus')->where('id', $existing['id'])->update([
                'label'      => $data['label'],
                'icon'       => $data['icon'],
                'permission' => $data['permission'],
                'parent_id'  => $data['parent_id'],
                'section'    => $data['section'],
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            echo "  ~ Menu '{$data['label']}' diperbarui (ID: {$existing['id']})\n";
            return (int)$existing['id'];
        }

        $this->db->table('menus')->insert([
            'label'      => $data['label'],
            'url'        => $data['url'],
            'icon'       => $data['icon'],
            'permission' => $data['permission'],
            'parent_id'  => $data['parent_id'],
            'sort_order' => $data['sort_order'],
            'is_active'  => 1,
            'section'    => $data['section'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $id = $this->db->insertID();
        echo "  + Menu '{$data['label']}' ditambahkan (ID: $id)\n";
        return (int)$id;
    }
}
