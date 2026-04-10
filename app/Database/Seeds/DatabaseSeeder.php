<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Core RBAC
        $this->call('UserSeeder');
        $this->call('RoleSeeder');
        $this->call('PermissionSeeder');
        $this->call('UserRoleSeeder');
        $this->call('RolePermissionSeeder');
        $this->call('MenuSeeder');

        // Modul Pengawasan Inspektorat
        // (permissions → roles → role-permissions → menus)
        $this->call('PengawasanSeeder');

        // Referensi kode temuan (89 kode PermenpanRB 41/2011)
        $this->call('KodetemuanSeeder');
    }
}