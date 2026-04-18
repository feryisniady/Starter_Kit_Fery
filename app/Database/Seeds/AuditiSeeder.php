<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Auditi Seeder — user entitas untuk testing dashboard auditi.
 *
 * Jalankan SETELAH InspektoratRoleSeeder + TestUserSeeder:
 *   php spark db:seed AuditiSeeder
 *
 * Idempotent — aman dijalankan berulang.
 *
 * ╔═════════════════════════════════╦══════════════╦═══════════════════════════════╗
 * ║ Email                           ║ Role         ║ Keterangan                    ║
 * ╠═════════════════════════════════╬══════════════╬═══════════════════════════════╣
 * ║ auditi.dinas@test.com           ║ auditi       ║ User dari entitas Dinas A     ║
 * ║ auditi.badan@test.com           ║ auditi       ║ User dari entitas Badan B     ║
 * ╚═════════════════════════════════╩══════════════╩═══════════════════════════════╝
 *
 * Password semua user: test123
 *
 * Yang dilakukan seeder ini:
 *  1. Buat role 'auditi' (jika belum ada)
 *  2. Buat 2 entitas test (jika belum ada)
 *  3. Buat 2 user auditi & link ke entitas masing-masing
 *
 * PRASYARAT: Migration 2026-04-18-000002_AddUserIdToEntitas.php sudah dijalankan.
 */
class AuditiSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $pwd = password_hash('test123', PASSWORD_DEFAULT);

        // ── 1. Buat role 'auditi' ──────────────────────────────────────────
        $roleId = $this->upsertRole('auditi', 'Entitas / Auditi', $now);
        echo "  Role 'auditi' → id={$roleId}\n";

        // ── 2. Assign permissions ke role auditi ───────────────────────────
        // auditi hanya bisa melihat temuan yang terkait entitasnya
        $this->assignPermission($roleId, 'spt.view');

        // ── 3. Buat entitas test ───────────────────────────────────────────
        $entitas = [
            [
                'kode'  => 'DINAS-A',
                'nama'  => 'Dinas Pendidikan dan Kebudayaan',
                'alamat'=> 'Jl. Pendidikan No. 1, Kota',
                'kepala'=> 'Dr. Kepala Dinas A, M.Pd.',
                'email' => 'auditi.dinas@test.com',
                'name'  => 'Kepala Dinas A',
            ],
            [
                'kode'  => 'BADAN-B',
                'nama'  => 'Badan Perencanaan Pembangunan Daerah',
                'alamat'=> 'Jl. Pembangunan No. 5, Kota',
                'kepala'=> 'Ir. Kepala Badan B, M.T.',
                'email' => 'auditi.badan@test.com',
                'name'  => 'Kepala Badan B',
            ],
        ];

        foreach ($entitas as $e) {
            $entitasId = $this->upsertEntitas($e, $now);

            // ── 4. Buat user ───────────────────────────────────────────────
            $exists = $this->db->table('users')->where('email', $e['email'])->get()->getRowArray();
            if ($exists) {
                $userId = (int) $exists['id'];
                echo "  [SKIP] {$e['email']} sudah ada (user_id={$userId})\n";
            } else {
                $this->db->table('users')->insert([
                    'name'       => $e['name'],
                    'email'      => $e['email'],
                    'password'   => $pwd,
                    'status'     => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $userId = (int) $this->db->insertID();

                // Assign role auditi
                $this->db->table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $roleId,
                ]);

                echo sprintf("  [OK] %-35s role=auditi user_id=%d entitas_id=%d\n",
                    $e['email'], $userId, $entitasId);
            }

            // ── 5. Link user ke entitas ────────────────────────────────────
            // Membutuhkan kolom user_id di tabel entitas
            // (Migration: 2026-04-18-000002_AddUserIdToEntitas.php)
            try {
                $this->db->table('entitas')
                    ->where('id', $entitasId)
                    ->update(['user_id' => $userId]);
                echo "    Entitas '{$e['nama']}' → user_id={$userId}\n";
            } catch (\Throwable $ex) {
                echo "    WARNING: Kolom user_id belum ada di tabel entitas — jalankan migration dulu.\n";
                echo "    SQL: ALTER TABLE entitas ADD COLUMN user_id INT NULL;\n";
                echo "         UPDATE entitas SET user_id={$userId} WHERE id={$entitasId};\n";
            }
        }

        echo "\nAuditiSeeder selesai.\n";
        echo "Password: test123\n";
        echo "User: auditi.dinas@test.com / auditi.badan@test.com\n";
    }

    // ──────────────────────────────────────────────────────────────────────

    private function upsertRole(string $name, string $label, string $now): int
    {
        $existing = $this->db->table('roles')->where('name', $name)->get()->getRowArray();
        if ($existing) return (int) $existing['id'];

        $this->db->table('roles')->insert([
            'name'       => $name,
            'label'      => $label,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return (int) $this->db->insertID();
    }

    private function assignPermission(int $roleId, string $permName): void
    {
        $perm = $this->db->table('permissions')->where('name', $permName)->get()->getRowArray();
        if (!$perm) return;

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

    private function upsertEntitas(array $e, string $now): int
    {
        $existing = $this->db->table('entitas')->where('kode', $e['kode'])->get()->getRowArray();
        if ($existing) return (int) $existing['id'];

        $this->db->table('entitas')->insert([
            'kode'       => $e['kode'],
            'nama'       => $e['nama'],
            'alamat'     => $e['alamat'],
            'kepala'     => $e['kepala'],
            'aktif'      => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        return (int) $this->db->insertID();
    }
}
