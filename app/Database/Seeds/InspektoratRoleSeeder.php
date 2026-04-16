<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed lengkap roles Inspektorat beserta assignment permissions.
 *
 * Jalankan SETELAH PermissionSeeder + PkptPermissionSeeder:
 *   php spark db:seed InspektoratRoleSeeder
 *
 * Idempotent — aman dijalankan berulang (upsert, skip jika sudah ada).
 *
 * ╔═══════════════════╦══════════════════════════════════════════════════╗
 * ║ Role              ║ Kewenangan Utama                                 ║
 * ╠═══════════════════╬══════════════════════════════════════════════════╣
 * ║ superadmin        ║ Semua akses tanpa batas                          ║
 * ║ admin             ║ Semua akses operasional                          ║
 * ║ inspektur         ║ Approve final (TTE), lihat semua SPT lintas irban║
 * ║ sekretaris        ║ Approve tahap sekretaris, lihat semua            ║
 * ║ evlap             ║ Approve tahap evlap, kelola PKPT semua irban     ║
 * ║ subbag_evlap      ║ Alias evlap (staf sub-bagian Evlap)              ║
 * ║ ka_irban          ║ Kepala Irban: approve tahap irban, kelola SPT    ║
 * ║ auditor           ║ Staf auditor: buat SPT, input KM, view SPT       ║
 * ╚═══════════════════╩══════════════════════════════════════════════════╝
 */
class InspektoratRoleSeeder extends Seeder
{
    // ──────────────────────────────────────────────────────────────────────
    // Definisi role + permissions yang diterima
    // ──────────────────────────────────────────────────────────────────────
    private array $roles = [
        [
            'name'  => 'superadmin',
            'label' => 'Super Admin',
            'permissions' => [
                'user.view','user.create','user.edit','user.delete',
                'role.view','role.create','role.edit','role.delete',
                'menu.view','menu.create','menu.edit','menu.delete',
                'master.view','master.manage',
                'pkpt.view','pkpt.input','pkpt.manage',
                'spt.view','spt.create','spt.manage_all','spt.approve',
            ],
        ],
        [
            'name'  => 'admin',
            'label' => 'Administrator',
            'permissions' => [
                'user.view','user.create','user.edit','user.delete',
                'role.view','role.create','role.edit','role.delete',
                'menu.view','menu.create','menu.edit','menu.delete',
                'master.view','master.manage',
                'pkpt.view','pkpt.input','pkpt.manage',
                'spt.view','spt.create','spt.manage_all','spt.approve',
            ],
        ],
        [
            'name'  => 'inspektur',
            'label' => 'Inspektur',
            'permissions' => [
                'master.view',
                'pkpt.view',
                'spt.view','spt.manage_all','spt.approve',
            ],
        ],
        [
            'name'  => 'sekretaris',
            'label' => 'Sekretaris',
            'permissions' => [
                'master.view',
                'pkpt.view',
                'spt.view','spt.approve',
            ],
        ],
        [
            'name'  => 'evlap',
            'label' => 'Subbag Evaluasi & Pelaporan',
            'permissions' => [
                'master.view',
                'pkpt.view','pkpt.input','pkpt.manage',
                'spt.view','spt.manage_all','spt.approve',
            ],
        ],
        [
            'name'  => 'subbag_evlap',
            'label' => 'Staf Subbag Evlap',
            // Sama dengan evlap — alias untuk staf (bukan kepala subbag)
            'permissions' => [
                'master.view',
                'pkpt.view','pkpt.input',
                'spt.view','spt.approve',
            ],
        ],
        [
            'name'  => 'ka_irban',
            'label' => 'Kepala Irban',
            'permissions' => [
                'master.view',
                'pkpt.view','pkpt.input',
                'spt.view','spt.create','spt.approve',
            ],
        ],
        [
            'name'  => 'auditor',
            'label' => 'Auditor / Anggota Tim',
            'permissions' => [
                'master.view',
                'pkpt.view',
                'spt.view','spt.create',
            ],
        ],
    ];

    // ──────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        foreach ($this->roles as $roleData) {
            // ── Upsert role ────────────────────────────────────────────────
            $existing = $this->db->table('roles')
                ->where('name', $roleData['name'])
                ->get()->getRowArray();

            if ($existing) {
                $this->db->table('roles')
                    ->where('id', $existing['id'])
                    ->update(['label' => $roleData['label'], 'updated_at' => $now]);
                $roleId = (int) $existing['id'];
                echo sprintf("  [UPDATE] role %-20s (id=%d)\n", "'{$roleData['name']}'", $roleId);
            } else {
                $this->db->table('roles')->insert([
                    'name'       => $roleData['name'],
                    'label'      => $roleData['label'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $roleId = (int) $this->db->insertID();
                echo sprintf("  [INSERT] role %-20s (id=%d)\n", "'{$roleData['name']}'", $roleId);
            }

            // ── Assign permissions ────────────────────────────────────────
            $assigned = 0;
            $skipped  = 0;

            foreach ($roleData['permissions'] as $permName) {
                $perm = $this->db->table('permissions')
                    ->where('name', $permName)
                    ->get()->getRowArray();

                if (!$perm) {
                    echo "    WARNING: permission '{$permName}' tidak ditemukan — lewati.\n";
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
                    $assigned++;
                } else {
                    $skipped++;
                }
            }

            echo sprintf("    permissions: +%d baru, %d sudah ada\n", $assigned, $skipped);
        }

        echo "\nInspektoratRoleSeeder selesai — " . count($this->roles) . " roles diproses.\n";
    }
}
