<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'label'      => 'Dashboard',
                'url'        => '/dashboard',
                'icon'       => '🏠',
                'permission' => null,
                'parent_id'  => null,
                'sort_order' => 1,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'label'      => 'Users',
                'url'        => '/admin/users',
                'icon'       => '👥',
                'permission' => 'user.view',
                'parent_id'  => null,
                'sort_order' => 2,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'label'      => 'Roles',
                'url'        => '/admin/roles',
                'icon'       => '🛡️',
                'permission' => 'role.view',
                'parent_id'  => null,
                'sort_order' => 3,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'label'      => 'Menus',
                'url'        => '/admin/menus',
                'icon'       => '📋',
                'permission' => 'menu.view',
                'parent_id'  => null,
                'sort_order' => 4,
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('menus')->insertBatch($data);
    }
}