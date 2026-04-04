<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Role admin dapat semua permission
        $data = [];
        for ($i = 1; $i <= 8; $i++) {
            $data[] = [
                'role_id'       => 1,
                'permission_id' => $i,
            ];
        }

        $this->db->table('role_permissions')->insertBatch($data);
    }
}