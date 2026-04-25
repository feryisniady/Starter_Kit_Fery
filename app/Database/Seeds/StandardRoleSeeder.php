<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ============================================================
 * STANDARD ROLE SEEDER — Inspektorat Daerah
 * ============================================================
 * Menggantikan: RoleSeeder, PkptRoleSeeder, InspektoratRoleSeeder,
 *               PengawasanSeeder, FixAuditorRolePermissionSeeder
 *
 * Jalankan: php spark db:seed StandardRoleSeeder
 *
 * Idempotent — aman dijalankan berulang (upsert).
 *
 * ╔══════════════╦══════════════════════╦════════════════════════════════╗
 * ║ Role         ║ Label                ║ Kewenangan Utama               ║
 * ╠══════════════╬══════════════════════╬════════════════════════════════╣
 * ║ superadmin   ║ Super Admin          ║ Bypass semua permission        ║
 * ║ admin        ║ Administrator        ║ Semua operasional              ║
 * ║ inspektur    ║ Inspektur            ║ Approve final (TTE), lintas irban║
 * ║ sekretaris   ║ Sekretaris           ║ Approve tahap sekretaris       ║
 * ║ kepala_irban ║ Kepala Irban         ║ Kelola PKPT & SPT irban sendiri ║
 * ║ evlap        ║ Subbag Evlap         ║ Approve & kelola semua PKPT/SPT║
 * ║ dalnis       ║ Pengendali Teknis    ║ Review PKA, pantau SPT tim     ║
 * ║ auditor      ║ Auditor              ║ Input PKPT, buat SPT, input KKA║
 * ╚══════════════╩══════════════════════╩════════════════════════════════╝
 *
 * CATATAN DESAIN:
 * - "Ketua Tim" dan "Anggota Tim" BUKAN system role.
 *   Peran dalam SPT disimpan di spt_tim.peran_spt (Ketua Tim / Anggota Tim).
 *   Satu auditor bisa KT di SPT A dan AT di SPT B secara bersamaan.
 *   Logic akses per-SPT ditangani oleh helper: isKtInSpt(), isAtInSpt().
 * ============================================================
 */
class StandardRoleSeeder extends Seeder
{
    // ─── Definisi Role → Permissions ────────────────────────────────────────
    private array $roles = [

        'superadmin' => [
            'label' => 'Super Admin',
            'permissions' => [
                // System
                'user.view', 'user.create', 'user.edit', 'user.delete',
                'role.view', 'role.create', 'role.edit', 'role.delete',
                'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
                'permission.view', 'permission.create', 'permission.delete',
                'activitylog.view', 'setting.manage',
                // Master
                'master.view', 'master.manage',
                // PKPT
                'pkpt.view', 'pkpt.input', 'pkpt.manage',
                'hari_libur.manage',
                // SPT
                'spt.view', 'spt.create', 'spt.manage_all', 'spt.approve',
                // Pengawasan
                'pka.view', 'pka.manage',
                'temuan.view', 'temuan.input', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],

        'admin' => [
            'label' => 'Administrator',
            'permissions' => [
                // System
                'user.view', 'user.create', 'user.edit', 'user.delete',
                'role.view', 'role.create', 'role.edit', 'role.delete',
                'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
                'permission.view', 'permission.create', 'permission.delete',
                'activitylog.view', 'setting.manage',
                // Master
                'master.view', 'master.manage',
                // PKPT
                'pkpt.view', 'pkpt.input', 'pkpt.manage',
                'hari_libur.manage',
                // SPT
                'spt.view', 'spt.create', 'spt.manage_all', 'spt.approve',
                // Pengawasan
                'pka.view', 'pka.manage',
                'temuan.view', 'temuan.input', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],

        'inspektur' => [
            'label' => 'Inspektur',
            // Approve final (TTE), lihat semua SPT lintas irban, tidak input PKPT
            'permissions' => [
                'master.view',
                'pkpt.view',
                'spt.view', 'spt.manage_all', 'spt.approve',
                'pka.view',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],

        'sekretaris' => [
            'label' => 'Sekretaris',
            // Approve tahap sekretaris, lihat semua SPT
            'permissions' => [
                'master.view',
                'pkpt.view',
                'spt.view', 'spt.manage_all', 'spt.approve',
                'pka.view',
                'temuan.view',
                'kode_temuan.view',
            ],
        ],

        'kepala_irban' => [
            'label' => 'Kepala Irban',
            // Kelola PKPT irban sendiri, buat & approve SPT, pantau PKA
            'permissions' => [
                'master.view',
                'pkpt.view', 'pkpt.input', 'pkpt.manage',
                'spt.view', 'spt.create', 'spt.approve',
                'pka.view', 'pka.manage',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],

        'evlap' => [
            'label' => 'Subbag Evlap',
            // Approve tahap evlap, kelola PKPT semua irban, monitoring lintas irban
            'permissions' => [
                'master.view',
                'pkpt.view', 'pkpt.input', 'pkpt.manage',
                'hari_libur.manage',
                'spt.view', 'spt.manage_all', 'spt.approve',
                'pka.view',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
                'activitylog.view',
            ],
        ],

        'dalnis' => [
            'label' => 'Pengendali Teknis',
            // Review PKA (KM-5), pantau semua SPT dalam tim, catatan KKA
            'permissions' => [
                'master.view',
                'pkpt.view', 'pkpt.input',
                'spt.view', 'spt.manage_all',
                'pka.view', 'pka.manage',
                'temuan.view', 'temuan.manage',
                'kode_temuan.view',
            ],
        ],

        'auditor' => [
            'label' => 'Auditor',
            // Buat SPT dari PKPT, input KKA, input temuan
            // Peran dalam SPT (KT/AT) ditentukan oleh spt_tim.peran_spt, bukan role ini
            'permissions' => [
                'master.view',
                'pkpt.view', 'pkpt.input',
                'spt.view', 'spt.create',
                'pka.view',
                'temuan.view', 'temuan.input',
                'kode_temuan.view',
            ],
        ],
    ];

    // ─── Semua permissions yang harus ada di tabel permissions ──────────────
    private array $allPermissions = [
        // System administration
        'user.view', 'user.create', 'user.edit', 'user.delete',
        'role.view', 'role.create', 'role.edit', 'role.delete',
        'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
        'permission.view', 'permission.create', 'permission.delete',
        'activitylog.view',
        'setting.manage',
        // Master data
        'master.view', 'master.manage',
        // PKPT & Scheduling
        'pkpt.view', 'pkpt.input', 'pkpt.manage',
        'hari_libur.manage',
        // SPT
        'spt.view', 'spt.create', 'spt.manage_all', 'spt.approve',
        // Pengawasan / Audit
        'pka.view', 'pka.manage',
        'temuan.view', 'temuan.input', 'temuan.manage',
        'kode_temuan.view',
    ];

    // ─── Role lama yang akan di-rename agar nama seragam ───────────────────
    // Format: 'nama_lama' => 'nama_baru'
    // Catatan: merge dilakukan dengan cara role_permissions dipindah ke role baru
    private array $roleRenames = [
        'ka_irban'     => 'kepala_irban',
        'subbag_evlap' => 'evlap',
        'staff_evlap'  => 'evlap',
        'admin_evlap'  => 'evlap',
    ];

    // ─── Role lama yang dihapus (tidak lagi digunakan) ──────────────────────
    // Auditor ambil alih tugas: kt, ketua_tim, anggota_tim, at
    // manager & user adalah role legacy sistem dasar, tidak relevan untuk inspektorat
    private array $obsoleteRoles = [
        'kt', 'ketua_tim', 'anggota_tim', 'at',
        'manager', 'user',
        'kepala_bagian', 'admin_program', 'admin_tu',
    ];

    // ────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        echo "\n[StandardRoleSeeder] Memulai standardisasi RBAC...\n\n";

        // ── 1. Pastikan semua permissions ada ───────────────────────────────
        echo "── 1. Sinkronisasi Permissions ──\n";
        $permMap = []; // name => id

        foreach ($this->allPermissions as $name) {
            $existing = $this->db->table('permissions')->where('name', $name)->get()->getRowArray();
            if ($existing) {
                $permMap[$name] = (int)$existing['id'];
            } else {
                $this->db->table('permissions')->insert([
                    'name' => $name, 'created_at' => $now, 'updated_at' => $now,
                ]);
                $permMap[$name] = (int)$this->db->insertID();
                echo "  [+] permission '{$name}'\n";
            }
        }
        echo "  Total: " . count($this->allPermissions) . " permissions siap.\n\n";

        // ── 2. Upsert standard roles ─────────────────────────────────────────
        echo "── 2. Upsert Standard Roles ──\n";
        $roleMap = []; // name => id

        foreach ($this->roles as $name => $def) {
            $existing = $this->db->table('roles')->where('name', $name)->get()->getRowArray();

            if ($existing) {
                $this->db->table('roles')->where('id', $existing['id'])->update([
                    'label' => $def['label'], 'updated_at' => $now,
                ]);
                $roleId = (int)$existing['id'];
                echo "  [≈] role '{$name}' (ID:{$roleId}) label diperbarui\n";
            } else {
                $this->db->table('roles')->insert([
                    'name' => $name, 'label' => $def['label'],
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $roleId = (int)$this->db->insertID();
                echo "  [+] role '{$name}' (ID:{$roleId}) dibuat\n";
            }

            $roleMap[$name] = $roleId;

            // Reset & assign permissions
            $this->db->table('role_permissions')->where('role_id', $roleId)->delete();
            foreach ($def['permissions'] as $perm) {
                if (!isset($permMap[$perm])) continue;
                $this->db->table('role_permissions')->insert([
                    'role_id' => $roleId, 'permission_id' => $permMap[$perm],
                ]);
            }
            echo "    → " . count($def['permissions']) . " permissions di-assign\n";
        }

        // ── 3. Migrasi user_roles dari role lama ke role baru ───────────────
        echo "\n── 3. Migrasi Role Lama → Role Baru ──\n";
        foreach ($this->roleRenames as $oldName => $newName) {
            $oldRole = $this->db->table('roles')->where('name', $oldName)->get()->getRowArray();
            if (!$oldRole) {
                echo "  [skip] '{$oldName}' tidak ada di DB\n";
                continue;
            }
            $newRoleId = $roleMap[$newName] ?? null;
            if (!$newRoleId) continue;

            // Pindahkan user_roles yang pakai role lama ke role baru
            $users = $this->db->table('user_roles')->where('role_id', $oldRole['id'])->get()->getResultArray();
            $migrated = 0;
            foreach ($users as $ur) {
                $exists = $this->db->table('user_roles')
                    ->where('user_id', $ur['user_id'])->where('role_id', $newRoleId)->countAllResults();
                if (!$exists) {
                    $this->db->table('user_roles')->insert(['user_id' => $ur['user_id'], 'role_id' => $newRoleId]);
                    $migrated++;
                }
            }

            // Hapus role lama beserta semua relasi
            $this->db->table('role_permissions')->where('role_id', $oldRole['id'])->delete();
            $this->db->table('user_roles')->where('role_id', $oldRole['id'])->delete();
            $this->db->table('roles')->where('id', $oldRole['id'])->delete();
            echo "  [→] '{$oldName}' → '{$newName}' ({$migrated} user dipindah, role lama dihapus)\n";
        }

        // ── 4. Hapus role yang sudah obsolete ────────────────────────────────
        echo "\n── 4. Hapus Role Obsolete ──\n";
        foreach ($this->obsoleteRoles as $name) {
            $role = $this->db->table('roles')->where('name', $name)->get()->getRowArray();
            if (!$role) {
                echo "  [skip] '{$name}' tidak ada\n";
                continue;
            }

            // Cek apakah masih ada user yang memakai role ini
            $userCount = $this->db->table('user_roles')->where('role_id', $role['id'])->countAllResults();
            if ($userCount > 0) {
                // Pindahkan ke 'auditor' (pengganti kt/ketua_tim/anggota_tim/at)
                $auditorId = $roleMap['auditor'] ?? null;
                if ($auditorId) {
                    foreach ($this->db->table('user_roles')->where('role_id', $role['id'])->get()->getResultArray() as $ur) {
                        $exists = $this->db->table('user_roles')
                            ->where('user_id', $ur['user_id'])->where('role_id', $auditorId)->countAllResults();
                        if (!$exists) {
                            $this->db->table('user_roles')->insert(['user_id' => $ur['user_id'], 'role_id' => $auditorId]);
                        }
                    }
                    echo "  [→] '{$name}' — {$userCount} user dipindah ke 'auditor'\n";
                }
            }

            $this->db->table('role_permissions')->where('role_id', $role['id'])->delete();
            $this->db->table('user_roles')->where('role_id', $role['id'])->delete();
            $this->db->table('roles')->where('id', $role['id'])->delete();
            echo "  [✗] role '{$name}' dihapus\n";
        }

        echo "\n[StandardRoleSeeder] Selesai!\n";
        echo "PENTING: Semua user yang login harus logout → login ulang agar session permission diperbarui.\n\n";

        // ── Ringkasan final ─────────────────────────────────────────────────
        $totalRoles = $this->db->table('roles')->countAllResults();
        $totalPerms = $this->db->table('permissions')->countAllResults();
        echo "Ringkasan DB sekarang: {$totalRoles} roles, {$totalPerms} permissions.\n\n";
    }
}
