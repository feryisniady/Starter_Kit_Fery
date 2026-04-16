<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Test User Seeder — user siap pakai untuk development & testing.
 *
 * Semua password: test123
 *
 * ╔══════════════════════════╦══════════════╦═══════════════════════════╗
 * ║ Email                    ║ Role         ║ Keterangan                ║
 * ╠══════════════════════════╬══════════════╬═══════════════════════════╣
 * ║ admin@admin.com          ║ superadmin   ║ user utama (UserSeeder)   ║
 * ║ inspektur@test.com       ║ inspektur    ║ Approve final TTE         ║
 * ║ sekretaris@test.com      ║ sekretaris   ║ Approve tahap sekretaris  ║
 * ║ evlap@test.com           ║ evlap        ║ Approve evlap, kelola PKPT║
 * ║ dalnis@test.com          ║ dalnis       ║ Reviu PKA (KM-5), KKA     ║
 * ║ ka_irban@test.com        ║ ka_irban     ║ Kepala Irban / bisa jadi KT║
 * ║ auditor1@test.com        ║ auditor      ║ Anggota Tim / AT          ║
 * ║ auditor2@test.com        ║ auditor      ║ Anggota Tim / AT          ║
 * ╚══════════════════════════╩══════════════╩═══════════════════════════╝
 *
 * Jalankan: php spark db:seed TestUserSeeder
 * Idempotent — skip jika email sudah ada.
 */
class TestUserSeeder extends Seeder
{
    private array $users = [
        [
            'name'     => 'Inspektur Utama',
            'email'    => 'inspektur@test.com',
            'role'     => 'inspektur',
            'sdm'      => ['nip' => '196801012000011001', 'nama' => 'Drs. Inspektur Utama, M.Si', 'jabatan_struktural' => 'Inspektur'],
        ],
        [
            'name'     => 'Sekretaris Inspektorat',
            'email'    => 'sekretaris@test.com',
            'role'     => 'sekretaris',
            'sdm'      => ['nip' => '197002022001012002', 'nama' => 'Hj. Sekretaris, S.H., M.H.', 'jabatan_struktural' => 'Sekretaris Inspektorat'],
        ],
        [
            'name'     => 'Kepala Subbag Evlap',
            'email'    => 'evlap@test.com',
            'role'     => 'evlap',
            'sdm'      => ['nip' => '197503031998031003', 'nama' => 'Kabag Evaluasi, S.E.', 'jabatan_struktural' => 'Ka. Subbag Evlap'],
        ],
        [
            'name'     => 'Pengendali Teknis',
            'email'    => 'dalnis@test.com',
            'role'     => 'dalnis',
            'sdm'      => ['nip' => '197804042003041004', 'nama' => 'Pengendali Teknis, S.Ak.', 'jabatan_fungsional' => 'Auditor Madya'],
        ],
        [
            'name'     => 'Kepala Irban I',
            'email'    => 'ka_irban@test.com',
            'role'     => 'ka_irban',
            'sdm'      => ['nip' => '198005052006051005', 'nama' => 'Kepala Irban I, S.E., M.M.', 'jabatan_struktural' => 'Kepala Irban I'],
        ],
        [
            'name'     => 'Auditor Senior',
            'email'    => 'auditor1@test.com',
            'role'     => 'auditor',
            'sdm'      => ['nip' => '198506062010061006', 'nama' => 'Budi Santoso, S.E.', 'jabatan_fungsional' => 'Auditor Muda'],
        ],
        [
            'name'     => 'Auditor Junior',
            'email'    => 'auditor2@test.com',
            'role'     => 'auditor',
            'sdm'      => ['nip' => '199007072015071007', 'nama' => 'Sari Dewi, A.Md.', 'jabatan_fungsional' => 'Auditor Pertama'],
        ],
    ];

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $pwd = password_hash('test123', PASSWORD_DEFAULT);

        foreach ($this->users as $u) {
            // ── Skip jika email sudah ada ──────────────────────────────
            $exists = $this->db->table('users')->where('email', $u['email'])->countAllResults();
            if ($exists) {
                echo "  [SKIP] {$u['email']} sudah ada.\n";
                continue;
            }

            // ── Insert user ────────────────────────────────────────────
            $this->db->table('users')->insert([
                'name'       => $u['name'],
                'email'      => $u['email'],
                'password'   => $pwd,
                'status'     => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $userId = (int) $this->db->insertID();

            // ── Assign role ────────────────────────────────────────────
            $role = $this->db->table('roles')->where('name', $u['role'])->get()->getRowArray();
            if ($role) {
                $this->db->table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $role['id'],
                ]);
            } else {
                echo "    WARNING: role '{$u['role']}' tidak ditemukan — lewati.\n";
            }

            // ── Insert SDM ─────────────────────────────────────────────
            $sdm = $u['sdm'];
            $this->db->table('sdm')->insert([
                'user_id'              => $userId,
                'nip'                  => $sdm['nip'],
                'nama'                 => $sdm['nama'],
                'pangkat_golongan'     => $sdm['pangkat_golongan']     ?? null,
                'jabatan_struktural'   => $sdm['jabatan_struktural']   ?? null,
                'jabatan_fungsional'   => $sdm['jabatan_fungsional']   ?? null,
                'irban_id'             => null,   // set manual lewat admin panel
                'aktif'                => 1,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            echo sprintf("  [OK] %-30s role=%-12s user_id=%d\n", $u['email'], $u['role'], $userId);
        }

        echo "\nTestUserSeeder selesai. Password semua user: test123\n";
        echo "Catatan: irban_id SDM masih NULL — set lewat menu Master > SDM.\n";
    }
}
