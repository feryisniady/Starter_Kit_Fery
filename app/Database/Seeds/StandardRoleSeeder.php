<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ============================================================
 * STANDARD ROLE SEEDER — Satu-satunya sumber kebenaran RBAC
 * ============================================================
 * Menggantikan SEMUA seeder role/permission lama:
 *   RoleSeeder, PkptRoleSeeder, InspektoratRoleSeeder,
 *   PengawasanSeeder, FixAuditorRolePermissionSeeder,
 *   PermissionSeeder, PkptPermissionSeeder, RolePermissionSeeder
 *
 * Jalankan: php spark db:seed StandardRoleSeeder
 * Idempotent — aman dijalankan berulang.
 *
 * ╔══════════════════╦══════════════════════════════════════════════╗
 * ║ TIPE USER        ║ ROLE                                         ║
 * ╠══════════════════╬══════════════════════════════════════════════╣
 * ║ Sistem           ║ superadmin, admin                            ║
 * ║ Inspektorat      ║ inspektur, sekretaris, kepala_irban,         ║
 * ║                  ║ evlap, dalnis, auditor                       ║
 * ║ Eksternal        ║ auditi (portal terpisah, tidak ada di sini)  ║
 * ╚══════════════════╩══════════════════════════════════════════════╝
 *
 * CATATAN DESAIN:
 *   "Ketua Tim" & "Anggota Tim" BUKAN system role.
 *   Peran dalam SPT disimpan di spt_tim.peran_spt.
 *   Satu auditor bisa KT di SPT-A dan AT di SPT-B bersamaan.
 *   Cek peran via: isKtInSpt($sptId), isAtInSpt($sptId).
 * ============================================================
 */
class StandardRoleSeeder extends Seeder
{
    // =========================================================================
    // 1. MASTER PERMISSION LIST
    //    Format: {modul}.{aksi}
    //    Aksi standar: view, create, edit, delete, manage, manage_all, input,
    //                  approve
    // =========================================================================
    private array $allPermissions = [
        // ── Sistem Administrasi ──────────────────────────────────────────────
        'user.view',         'user.create',        'user.edit',          'user.delete',
        'role.view',         'role.create',         'role.edit',          'role.delete',
        'menu.view',         'menu.create',         'menu.edit',          'menu.delete',
        'permission.view',   'permission.create',   'permission.delete',
        'setting.manage',
        'activitylog.view',

        // ── Master Data ──────────────────────────────────────────────────────
        'master.view',       'master.manage',

        // ── PKPT & Penjadwalan ───────────────────────────────────────────────
        'hari_libur.manage',
        'pkpt.view',         'pkpt.input',          'pkpt.manage',

        // ── SPT ──────────────────────────────────────────────────────────────
        'spt.view',          'spt.create',          'spt.manage_all',     'spt.approve',

        // ── Pengawasan (PKA, KKA, Temuan) ────────────────────────────────────
        'pka.view',          'pka.manage',
        'kka.view',          'kka.manage',
        'temuan.view',       'temuan.input',        'temuan.manage',
        'kode_temuan.view',
    ];

    // =========================================================================
    // 2. DEFINISI ROLE → PERMISSIONS
    //    Matrix lengkap sesuai kebutuhan Inspektorat Daerah
    // =========================================================================
    private array $roles = [

        // ── SISTEM ───────────────────────────────────────────────────────────

        'superadmin' => [
            'label' => 'Super Admin',
            'permissions' => [
                // Sistem administrasi (semua)
                'user.view', 'user.create', 'user.edit', 'user.delete',
                'role.view', 'role.create', 'role.edit', 'role.delete',
                'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
                'permission.view', 'permission.create', 'permission.delete',
                'setting.manage', 'activitylog.view',
                // Master
                'master.view', 'master.manage',
                // PKPT
                'hari_libur.manage', 'pkpt.view', 'pkpt.input', 'pkpt.manage',
                // SPT
                'spt.view', 'spt.create', 'spt.manage_all', 'spt.approve',
                // Pengawasan
                'pka.view', 'pka.manage',
                'kka.view', 'kka.manage',
                'temuan.view', 'temuan.input', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],

        'admin' => [
            'label' => 'Administrator',
            'permissions' => [
                // Sistem administrasi (semua)
                'user.view', 'user.create', 'user.edit', 'user.delete',
                'role.view', 'role.create', 'role.edit', 'role.delete',
                'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
                'permission.view', 'permission.create', 'permission.delete',
                'setting.manage', 'activitylog.view',
                // Master
                'master.view', 'master.manage',
                // PKPT
                'hari_libur.manage', 'pkpt.view', 'pkpt.input', 'pkpt.manage',
                // SPT
                'spt.view', 'spt.create', 'spt.manage_all', 'spt.approve',
                // Pengawasan
                'pka.view', 'pka.manage',
                'kka.view', 'kka.manage',
                'temuan.view', 'temuan.input', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],

        // ── INSPEKTORAT ──────────────────────────────────────────────────────

        'inspektur' => [
            'label' => 'Inspektur',
            // TTE (approve final), lihat semua SPT lintas irban, pantau temuan
            'permissions' => [
                'master.view',
                'pkpt.view',
                'spt.view', 'spt.manage_all', 'spt.approve',
                'pka.view',
                'kka.view',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
                'activitylog.view',
            ],
        ],

        'sekretaris' => [
            'label' => 'Sekretaris',
            // Approve tahap sekretaris, lihat semua SPT, tidak kelola data
            'permissions' => [
                'master.view',
                'pkpt.view',
                'spt.view', 'spt.manage_all', 'spt.approve',
                'pka.view',
                'kka.view',
                'temuan.view',
                'kode_temuan.view',
            ],
        ],

        'kepala_irban' => [
            'label' => 'Kepala Irban',
            // Kelola PKPT irban sendiri, approve SPT tahap irban,
            // pantau PKA & KKA tim, kelola status temuan
            'permissions' => [
                'master.view',
                'pkpt.view', 'pkpt.input', 'pkpt.manage',
                'spt.view', 'spt.create', 'spt.approve',
                'pka.view', 'pka.manage',
                'kka.view',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
                'activitylog.view',
            ],
        ],

        'evlap' => [
            'label' => 'Subbag Evlap',
            // Approve tahap evlap, kelola semua PKPT & SPT lintas irban,
            // monitoring & rekap temuan, hari libur
            'permissions' => [
                'master.view',
                'hari_libur.manage',
                'pkpt.view', 'pkpt.input', 'pkpt.manage',
                'spt.view', 'spt.manage_all', 'spt.approve',
                'pka.view',
                'kka.view',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
                'activitylog.view',
            ],
        ],

        'dalnis' => [
            'label' => 'Pengendali Teknis',
            // Review PKA (KM-5), pantau semua SPT dalam tim,
            // beri catatan KKA, kelola status temuan
            'permissions' => [
                'master.view',
                'pkpt.view', 'pkpt.input',
                'spt.view', 'spt.manage_all',
                'pka.view', 'pka.manage',
                'kka.view', 'kka.manage',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],

        'auditor' => [
            'label' => 'Auditor',
            // Input PKPT, buat SPT, input & kelola KKA sendiri,
            // input temuan. Peran KT/AT ditentukan per SPT di spt_tim.peran_spt
            'permissions' => [
                'master.view',
                'pkpt.view', 'pkpt.input',
                'spt.view', 'spt.create',
                'pka.view',
                'kka.view', 'kka.manage',
                'temuan.view', 'temuan.input',
                'kode_temuan.view',
            ],
        ],
    ];

    // =========================================================================
    // 3. MIGRASI ROLE LAMA → ROLE BARU (rename)
    //    User yang punya role lama otomatis dipindah ke role baru
    // =========================================================================
    private array $roleRenames = [
        'ka_irban'     => 'kepala_irban',  // typo lama
        'subbag_evlap' => 'evlap',
        'staff_evlap'  => 'evlap',
        'admin_evlap'  => 'evlap',
        'admin_program'=> 'evlap',
        'admin_tu'     => 'auditor',
    ];

    // =========================================================================
    // 4. ROLE OBSOLETE (hapus, user-nya dipindah ke 'auditor')
    // =========================================================================
    private array $obsoleteRoles = [
        // Abbreviasi yang bikin bingung
        'kt', 'ketua_tim', 'anggota_tim', 'at',
        // Role legacy sistem dasar
        'manager', 'user',
        // Role lain yang tidak terpakai
        'kepala_bagian', 'pejabat_auditee',
    ];

    // =========================================================================
    // RUNNER
    // =========================================================================

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $db  = $this->db;

        echo "\n";
        echo "╔══════════════════════════════════════════════╗\n";
        echo "║        StandardRoleSeeder — Mulai            ║\n";
        echo "╚══════════════════════════════════════════════╝\n\n";

        // ── STEP 1: Sync semua permissions ──────────────────────────────────
        echo "[ 1/4 ] Sinkronisasi " . count($this->allPermissions) . " permissions...\n";
        $permMap = $this->syncPermissions($now);
        echo "        ✓ Selesai — " . count($permMap) . " permissions siap.\n\n";

        // ── STEP 2: Upsert 8 standard roles + assign permissions ────────────
        echo "[ 2/4 ] Upsert " . count($this->roles) . " standard roles...\n";
        $roleMap = $this->upsertRoles($permMap, $now);
        echo "        ✓ Selesai.\n\n";

        // ── STEP 3: Migrasi role lama → role baru ───────────────────────────
        echo "[ 3/4 ] Migrasi role lama → role baru...\n";
        $this->migrateRoles($roleMap, $now);
        echo "        ✓ Selesai.\n\n";

        // ── STEP 4: Hapus role obsolete ─────────────────────────────────────
        echo "[ 4/4 ] Hapus role obsolete...\n";
        $this->removeObsoleteRoles($roleMap);
        echo "        ✓ Selesai.\n\n";

        // ── Ringkasan ────────────────────────────────────────────────────────
        $totalRoles = $db->table('roles')->countAllResults();
        $totalPerms = $db->table('permissions')->countAllResults();

        echo "╔══════════════════════════════════════════════╗\n";
        echo "║  SELESAI                                     ║\n";
        echo "║  Roles      : {$totalRoles} (target: 7)                  ║\n";
        echo "║  Permissions: {$totalPerms} (target: " . count($this->allPermissions) . ")                ║\n";
        echo "╚══════════════════════════════════════════════╝\n";
        echo "\n⚠  PENTING: Semua user harus logout → login ulang.\n";
        echo "   Session permission lama tidak akan diperbarui otomatis.\n\n";
    }

    // ── Helper: sync permissions ─────────────────────────────────────────────
    private function syncPermissions(string $now): array
    {
        $permMap = [];
        foreach ($this->allPermissions as $name) {
            $row = $this->db->table('permissions')->where('name', $name)->get()->getRowArray();
            if ($row) {
                $permMap[$name] = (int)$row['id'];
            } else {
                $this->db->table('permissions')->insert([
                    'name' => $name, 'created_at' => $now, 'updated_at' => $now,
                ]);
                $permMap[$name] = (int)$this->db->insertID();
                echo "        [+] permission '{$name}'\n";
            }
        }
        return $permMap;
    }

    // ── Helper: upsert roles ─────────────────────────────────────────────────
    private function upsertRoles(array $permMap, string $now): array
    {
        $roleMap = [];

        foreach ($this->roles as $name => $def) {
            $existing = $this->db->table('roles')->where('name', $name)->get()->getRowArray();

            if ($existing) {
                $this->db->table('roles')->where('id', $existing['id'])
                    ->update(['label' => $def['label'], 'updated_at' => $now]);
                $roleId = (int)$existing['id'];
                echo "        [≈] {$name} (ID:{$roleId})\n";
            } else {
                $this->db->table('roles')->insert([
                    'name' => $name, 'label' => $def['label'],
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $roleId = (int)$this->db->insertID();
                echo "        [+] {$name} (ID:{$roleId}) dibuat\n";
            }

            $roleMap[$name] = $roleId;

            // Reset lalu assign ulang semua permission untuk role ini
            $this->db->table('role_permissions')->where('role_id', $roleId)->delete();
            $inserts = [];
            foreach ($def['permissions'] as $perm) {
                if (!isset($permMap[$perm])) {
                    echo "        [!] WARNING: permission '{$perm}' tidak ada di permMap\n";
                    continue;
                }
                $inserts[] = ['role_id' => $roleId, 'permission_id' => $permMap[$perm]];
            }
            if ($inserts) {
                $this->db->table('role_permissions')->insertBatch($inserts);
            }
            echo "            → " . count($inserts) . " permissions di-assign\n";
        }

        return $roleMap;
    }

    // ── Helper: migrasi role lama → role baru ────────────────────────────────
    private function migrateRoles(array $roleMap, string $now): void
    {
        foreach ($this->roleRenames as $oldName => $newName) {
            $oldRole = $this->db->table('roles')->where('name', $oldName)->get()->getRowArray();
            if (!$oldRole) {
                echo "        [skip] '{$oldName}' tidak ada di DB\n";
                continue;
            }

            $newRoleId = $roleMap[$newName] ?? null;
            if (!$newRoleId) continue;

            // Pindahkan user_roles yang pakai role lama ke role baru
            $users     = $this->db->table('user_roles')->where('role_id', $oldRole['id'])->get()->getResultArray();
            $migrated  = 0;
            foreach ($users as $ur) {
                $exists = $this->db->table('user_roles')
                    ->where('user_id', $ur['user_id'])->where('role_id', $newRoleId)->countAllResults();
                if (!$exists) {
                    $this->db->table('user_roles')->insert([
                        'user_id' => $ur['user_id'], 'role_id' => $newRoleId,
                    ]);
                    $migrated++;
                }
            }

            // Hapus role lama + semua relasinya
            $this->db->table('role_permissions')->where('role_id', $oldRole['id'])->delete();
            $this->db->table('user_roles')->where('role_id', $oldRole['id'])->delete();
            $this->db->table('roles')->where('id', $oldRole['id'])->delete();
            echo "        [→] '{$oldName}' → '{$newName}' ({$migrated} user dipindah)\n";
        }
    }

    // ── Helper: hapus role obsolete ──────────────────────────────────────────
    private function removeObsoleteRoles(array $roleMap): void
    {
        $fallbackId = $roleMap['auditor'] ?? null;

        foreach ($this->obsoleteRoles as $name) {
            $role = $this->db->table('roles')->where('name', $name)->get()->getRowArray();
            if (!$role) {
                echo "        [skip] '{$name}' tidak ada\n";
                continue;
            }

            // Pindahkan user yang masih pakai role ini ke 'auditor'
            if ($fallbackId) {
                $users = $this->db->table('user_roles')
                    ->where('role_id', $role['id'])->get()->getResultArray();
                foreach ($users as $ur) {
                    $exists = $this->db->table('user_roles')
                        ->where('user_id', $ur['user_id'])->where('role_id', $fallbackId)->countAllResults();
                    if (!$exists) {
                        $this->db->table('user_roles')->insert([
                            'user_id' => $ur['user_id'], 'role_id' => $fallbackId,
                        ]);
                    }
                }
                $count = count($users);
                if ($count > 0) echo "        [→] '{$name}' → 'auditor' ({$count} user dipindah)\n";
            }

            $this->db->table('role_permissions')->where('role_id', $role['id'])->delete();
            $this->db->table('user_roles')->where('role_id', $role['id'])->delete();
            $this->db->table('roles')->where('id', $role['id'])->delete();
            echo "        [✗] role '{$name}' dihapus\n";
        }
    }
}
