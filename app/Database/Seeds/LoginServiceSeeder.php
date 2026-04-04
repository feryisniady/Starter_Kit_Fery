<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LoginServiceSeeder extends Seeder
{
    public function run()
    {
        $now      = date('Y-m-d H:i:s');
        $services = [
            [
                'name'          => 'Dashboard RBAC',
                'description'   => 'Manajemen pengguna, role, dan permission sistem',
                'url'           => '/dashboard',
                'icon'          => 'fa-solid fa-shield-halved',
                'require_login' => 1,
                'sort_order'    => 1,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Manajemen Pengguna',
                'description'   => 'Kelola data user dan hak akses',
                'url'           => '/admin/users',
                'icon'          => 'fa-solid fa-users',
                'require_login' => 1,
                'sort_order'    => 2,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Activity Log',
                'description'   => 'Rekam jejak aktivitas pengguna sistem',
                'url'           => '/admin/activity-logs',
                'icon'          => 'fa-solid fa-clock-rotate-left',
                'require_login' => 1,
                'sort_order'    => 3,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
            [
                'name'          => 'Pengaturan Aplikasi',
                'description'   => 'Konfigurasi branding dan tampilan sistem',
                'url'           => '/admin/settings',
                'icon'          => 'fa-solid fa-gear',
                'require_login' => 1,
                'sort_order'    => 4,
                'is_active'     => 1,
                'created_at'    => $now,
                'updated_at'    => $now,
            ],
        ];

        foreach ($services as $svc) {
            $exists = $this->db->table('login_services')->where('name', $svc['name'])->countAllResults();
            if (!$exists) {
                $this->db->table('login_services')->insert($svc);
            }
        }

        echo "LoginServiceSeeder: data layanan default berhasil ditambahkan.\n";
    }
}
