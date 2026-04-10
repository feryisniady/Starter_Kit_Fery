<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed permissions untuk modul PKPT & SPT.
 * Jalankan: php spark db:seed PkptPermissionSeeder
 */
class PkptPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'master.view',
            'master.manage',
            'pkpt.view',
            'pkpt.input',
            'pkpt.manage',
            'spt.view',
            'spt.create',
            'spt.manage_all',
            'spt.approve',
        ];

        foreach ($permissions as $name) {
            $exists = $this->db->table('permissions')->where('name', $name)->countAllResults();
            if (!$exists) {
                $this->db->table('permissions')->insert([
                    'name'       => $name,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        echo "PkptPermissionSeeder: " . count($permissions) . " permissions siap.\n";
    }
}
