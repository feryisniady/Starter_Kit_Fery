<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // ── 1. User admin default ─────────────────────────────────────────────
        $this->call('UserSeeder');

        // ── 2. RBAC: semua roles + permissions (satu sumber kebenaran) ────────
        //    Menggantikan: RoleSeeder, PermissionSeeder, PkptPermissionSeeder,
        //    InspektoratRoleSeeder, PengawasanSeeder, RolePermissionSeeder,
        //    UserRoleSeeder, FixAuditorRolePermissionSeeder
        $this->call('StandardRoleSeeder');

        // ── 3. Menu navigasi (satu sumber kebenaran) ──────────────────────────
        //    Menggantikan: MenuSeeder, PkptMenuSeeder, PengawasanSeeder (menus),
        //    ProfileMenuSeeder
        $this->call('StandardMenuSeeder');

        // ── 4. Referensi kode temuan (89 kode PermenpanRB 41/2011) ───────────
        $this->call('KodetemuanSeeder');

        // ── 5. Test users untuk development ───────────────────────────────────
        //    Hanya jalankan di environment development!
        if (ENVIRONMENT === 'development') {
            $this->call('TestUserSeeder');
        }
    }
}