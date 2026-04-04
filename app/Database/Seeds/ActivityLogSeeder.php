<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run()
    {
        // 1. Tambah permission activitylog.view (skip jika sudah ada)
        $exists = $this->db->table('permissions')
            ->where('name', 'activitylog.view')
            ->countAllResults();

        if (!$exists) {
            $this->db->table('permissions')->insert([
                'name'       => 'activitylog.view',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // Ambil ID permission activitylog.view
        $permission = $this->db->table('permissions')
            ->where('name', 'activitylog.view')
            ->get()->getRowArray();

        if (!$permission) return;

        // 2. Assign ke role superadmin dan admin
        $targetRoles = ['superadmin', 'admin'];

        foreach ($targetRoles as $roleName) {
            $role = $this->db->table('roles')
                ->where('name', $roleName)
                ->get()->getRowArray();

            if (!$role) continue;

            // Cek sudah ada belum
            $already = $this->db->table('role_permissions')
                ->where('role_id', $role['id'])
                ->where('permission_id', $permission['id'])
                ->countAllResults();

            if (!$already) {
                $this->db->table('role_permissions')->insert([
                    'role_id'       => $role['id'],
                    'permission_id' => $permission['id'],
                ]);
            }
        }

        // 3. Tambah menu Activity Log (skip jika sudah ada)
        $menuExists = $this->db->table('menus')
            ->where('url', '/admin/activity-logs')
            ->countAllResults();

        if (!$menuExists) {
            // Cari sort_order terbesar lalu +1
            $maxSort = $this->db->table('menus')
                ->selectMax('sort_order')
                ->get()->getRowArray();

            $this->db->table('menus')->insert([
                'label'      => 'Activity Log',
                'url'        => '/admin/activity-logs',
                'icon'       => 'fa-solid fa-clock-rotate-left',
                'permission' => 'activitylog.view',
                'parent_id'  => null,
                'sort_order' => ($maxSort['sort_order'] ?? 0) + 1,
                'is_active'  => 1,
                'section'    => 'main',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo "ActivityLogSeeder: permission & menu berhasil ditambahkan.\n";
    }
}
