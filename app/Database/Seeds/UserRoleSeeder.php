<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run()
    {
        // Assign role admin (id=1) ke user admin (id=1)
        $this->db->table('user_roles')->insert([
            'user_id' => 1,
            'role_id' => 1,
        ]);
    }
}