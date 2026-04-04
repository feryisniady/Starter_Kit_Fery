<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('UserSeeder');
        $this->call('RoleSeeder');
        $this->call('PermissionSeeder');
        $this->call('UserRoleSeeder');
        $this->call('RolePermissionSeeder');
        $this->call('MenuSeeder');
    }
}