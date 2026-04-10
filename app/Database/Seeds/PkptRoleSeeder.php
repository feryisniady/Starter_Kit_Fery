<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed roles + permissions untuk modul PKPT & SPT (Inspektorat).
 * Jalankan SETELAH PkptPermissionSeeder.
 * php spark db:seed PkptRoleSeeder
 */
class PkptRoleSeeder extends Seeder
{
    public function run()
    {
        // ─── 1. Definisi Roles ───────────────────────────────────────────────
        $roles = [
            [
                'name'  => 'kepala_irban',
                'label' => 'Kepala Irban',
                // Permissions: view master, input & view PKPT sendiri, buat & ajukan SPT, approve tahap irban
                'permissions' => ['master.view', 'pkpt.view', 'pkpt.input', 'spt.view', 'spt.create', 'spt.approve'],
            ],
            [
                'name'  => 'staff_evlap',
                'label' => 'Staff Evlap',
                // Approve SPT tahap Evlap, view semua
                'permissions' => ['master.view', 'pkpt.view', 'spt.view', 'spt.approve'],
            ],
            [
                'name'  => 'sekretaris',
                'label' => 'Sekretaris',
                // Approve SPT tahap Sekretaris
                'permissions' => ['pkpt.view', 'spt.view', 'spt.approve'],
            ],
            [
                'name'  => 'inspektur',
                'label' => 'Inspektur',
                // TTE (final approve), bisa lihat semua SPT lintas irban
                'permissions' => ['master.view', 'pkpt.view', 'spt.view', 'spt.approve', 'spt.manage_all'],
            ],
        ];

        foreach ($roles as $roleData) {
            $perms = $roleData['permissions'];

            // Insert role (skip jika sudah ada)
            $existingRole = $this->db->table('roles')->where('name', $roleData['name'])->get()->getRowArray();
            if (!$existingRole) {
                $this->db->table('roles')->insert([
                    'name'       => $roleData['name'],
                    'label'      => $roleData['label'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $roleId = $this->db->insertID();
                echo "  Role '{$roleData['name']}' dibuat (ID: {$roleId}).\n";
            } else {
                $roleId = $existingRole['id'];
                // Update label jika berubah
                $this->db->table('roles')->where('id', $roleId)->update(['label' => $roleData['label']]);
                echo "  Role '{$roleData['name']}' sudah ada (ID: {$roleId}), label diperbarui.\n";
            }

            // Assign permissions ke role
            foreach ($perms as $permName) {
                $perm = $this->db->table('permissions')->where('name', $permName)->get()->getRowArray();
                if (!$perm) {
                    echo "  WARNING: permission '{$permName}' tidak ditemukan. Jalankan PkptPermissionSeeder terlebih dahulu.\n";
                    continue;
                }

                $exists = $this->db->table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $perm['id'])
                    ->countAllResults();

                if (!$exists) {
                    $this->db->table('role_permissions')->insert([
                        'role_id'       => $roleId,
                        'permission_id' => $perm['id'],
                    ]);
                }
            }
        }

        // ─── 2. Berikan semua permission PKPT/SPT ke superadmin & admin ─────
        $adminRoles = ['superadmin', 'admin'];
        $allPkptPerms = ['master.view', 'master.manage', 'pkpt.view', 'pkpt.input', 'pkpt.manage',
                         'spt.view', 'spt.create', 'spt.manage_all', 'spt.approve'];

        foreach ($adminRoles as $roleName) {
            $role = $this->db->table('roles')->where('name', $roleName)->get()->getRowArray();
            if (!$role) continue;

            foreach ($allPkptPerms as $permName) {
                $perm = $this->db->table('permissions')->where('name', $permName)->get()->getRowArray();
                if (!$perm) continue;

                $exists = $this->db->table('role_permissions')
                    ->where('role_id', $role['id'])
                    ->where('permission_id', $perm['id'])
                    ->countAllResults();

                if (!$exists) {
                    $this->db->table('role_permissions')->insert([
                        'role_id'       => $role['id'],
                        'permission_id' => $perm['id'],
                    ]);
                }
            }
        }

        echo "PkptRoleSeeder: roles & permissions selesai.\n";
    }
}
