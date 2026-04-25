<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Fix permissions untuk role auditor/KT/Ketua Tim yang tidak bisa akses PKPT & SPT.
 *
 * Jalankan: php spark db:seed FixAuditorRolePermissionSeeder
 *
 * Idempotent — aman dijalankan berulang.
 */
class FixAuditorRolePermissionSeeder extends Seeder
{
    /**
     * Role → permissions yang harus dimiliki.
     * Semua role auditor inspektorat butuh minimal pkpt.view + spt.view.
     */
    private array $rolePermissions = [
        'auditor'    => ['master.view', 'pkpt.view', 'spt.view', 'spt.create'],
        'kt'         => ['master.view', 'pkpt.view', 'spt.view', 'spt.create'],
        'ketua_tim'  => ['master.view', 'pkpt.view', 'spt.view', 'spt.create'],
        'anggota_tim'=> ['master.view', 'pkpt.view', 'spt.view', 'spt.create'],
        'at'         => ['master.view', 'pkpt.view', 'spt.view', 'spt.create'],
        'dalnis'     => ['master.view', 'pkpt.view', 'spt.view', 'spt.manage_all'],
    ];

    /** Label default jika role belum ada di DB (akan dibuat otomatis). */
    private array $roleLabels = [
        'auditor'     => 'Auditor / Anggota Tim',
        'kt'          => 'Ketua Tim',
        'ketua_tim'   => 'Ketua Tim',
        'anggota_tim' => 'Anggota Tim',
        'at'          => 'Anggota Tim',
        'dalnis'      => 'Pengendali Teknis (Dalnis)',
    ];

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        echo "\n[FixAuditorRolePermissionSeeder] Memulai...\n";

        foreach ($this->rolePermissions as $roleName => $permissions) {
            // Cek role ada di DB
            $role = $this->db->table('roles')->where('name', $roleName)->get()->getRowArray();

            if (!$role) {
                // Buat role jika belum ada
                $label = $this->roleLabels[$roleName] ?? ucfirst(str_replace('_', ' ', $roleName));
                $this->db->table('roles')->insert([
                    'name'       => $roleName,
                    'label'      => $label,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $roleId = (int) $this->db->insertID();
                echo "  [BARU]  role '{$roleName}' dibuat (ID: {$roleId})\n";
            } else {
                $roleId = (int) $role['id'];
                echo "  [ADA]   role '{$roleName}' (ID: {$roleId})\n";
            }

            // Assign permissions
            $added   = 0;
            $skipped = 0;

            foreach ($permissions as $permName) {
                $perm = $this->db->table('permissions')->where('name', $permName)->get()->getRowArray();
                if (!$perm) {
                    // Buat permission jika belum ada
                    $this->db->table('permissions')->insert([
                        'name'       => $permName,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $perm = ['id' => $this->db->insertID(), 'name' => $permName];
                    echo "    [BARU] permission '{$permName}' dibuat\n";
                }

                $exists = $this->db->table('role_permissions')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $perm['id'])
                    ->countAllResults();

                if (!$exists) {
                    $this->db->table('role_permissions')->insert([
                        'role_id'       => $roleId,
                        'permission_id' => (int) $perm['id'],
                    ]);
                    $added++;
                } else {
                    $skipped++;
                }
            }

            echo "    permissions: +{$added} ditambah, {$skipped} sudah ada\n";
        }

        echo "\n[FixAuditorRolePermissionSeeder] Selesai.\n";
        echo "PENTING: Minta user logout lalu login ulang agar session permission diperbarui.\n\n";
    }
}
