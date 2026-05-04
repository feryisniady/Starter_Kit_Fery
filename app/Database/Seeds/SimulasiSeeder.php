?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * ═══════════════════════════════════════════════════════════════════════
 *  SIMULASI LENGKAP — Alur Pengawasan BKPSDM 2026
 *  dari PKPT hingga NHP terkirim ke Auditi
 * ═══════════════════════════════════════════════════════════════════════
 *
 * Data yang dibuat secara otomatis:
 *  • 8 akun pengguna (satu per peran dalam alur)
 *  • 1 Irban, 3 SDM (Dalnis / KT / AT)
 *  • 1 Entitas: Badan Kepegawaian Dan Sumber Daya Manusia (BKPSDM)
 *  • PKPT 2026 → Kegiatan → SPT (terbit) → PKA → KM → KKA (approved) → NHP (draft)
 *
 * Jalankan:
 *   php spark db:seed SimulasiSeeder
 *   (gunakan PHP 8.2+ di Laragon: C:/laragon/bin/php/php-8.3.x/php.exe spark db:seed SimulasiSeeder)
 *
 * ┌────────────────────────┬────────────────────────────────┬───────────┐
 * │ Email                  │ Peran                          │ Password  │
 * ├────────────────────────┼────────────────────────────────┼───────────┤
 * │ inspektur@sim.test     │ Inspektur (Penanggung Jawab)   │ sim123456 │
 * │ sekretaris@sim.test    │ Sekretaris                     │ sim123456 │
 * │ irban1@sim.test        │ Kepala Irban I                 │ sim123456 │
 * │ evlap@sim.test         │ Subbag Evaluasi & Pelaporan    │ sim123456 │
 * │ dalnis@sim.test        │ Pengendali Teknis (Dalnis)     │ sim123456 │
 * │ ketua@sim.test         │ Ketua Tim (KT)                 │ sim123456 │
 * │ at1@sim.test           │ Anggota Tim 1 (AT)             │ sim123456 │
 * │ bkpsdm@sim.test        │ Auditi — BKPSDM                │ sim123456 │
 * └────────────────────────┴────────────────────────────────┴───────────┘
 */
class SimulasiSeeder extends Seeder
{
    private \CodeIgniter\Database\BaseConnection $conn;
    private string $now;
    private string $pass;

    // ─── Entry point ──────────────────────────────────────────────────────

    public function run(): void
    {
        $this->conn = \Config\Database::connect();
        $this->now  = date('Y-m-d H:i:s');
        $this->pass = password_hash('sim123456', PASSWORD_DEFAULT);

        $this->line('');
        $this->line('╔══════════════════════════════════════════════════╗', 'green');
        $this->line('║    SIMULASI PENGAWASAN BKPSDM — PKPT → NHP      ║', 'green');
        $this->line('╚══════════════════════════════════════════════════╝', 'green');
        $this->line('');

        $this->conn->transStart();

        try {
            $roles     = $this->step1Roles();
            $userIds   = $this->step2Users($roles);
            $sdmIds    = $this->step3Sdm($userIds);
            $irbanId   = $this->step4Irban($sdmIds['dalnis']);
            $this->linkSdmToIrban($sdmIds, $irbanId);
            $entitasId = $this->step5Entitas($userIds['bkpsdm']);
            $this->step6PkptSetting($userIds['inspektur']);
            $pkptId    = $this->step7Pkpt($irbanId, $userIds['inspektur']);
            [$kegiatanId, $kodeTemuanId] = $this->step8Kegiatan($pkptId, $sdmIds, $entitasId);
            $sptId     = $this->step9Spt($kegiatanId, $irbanId, $sdmIds, $userIds);
            $pkaIds    = $this->step10Pka($sptId, $sdmIds, $userIds);
            $this->step11KmStages($sptId, $sdmIds);
            $kkaId     = $this->step12Kka($sptId, $sdmIds, $pkaIds, $kodeTemuanId, $userIds);
            $this->step13Nhp($sptId, $kkaId, $userIds);

            $this->conn->transCommit();
            $this->printRingkasan();

        } catch (\Throwable $e) {
            $this->conn->transRollback();
            $this->line('✗ ERROR: ' . $e->getMessage(), 'red');
            $this->line('  ' . $e->getFile() . ':' . $e->getLine(), 'red');
        }
    }

    // ─── STEP 1 : ROLES & PERMISSIONS ────────────────────────────────────

    private function step1Roles(): array
    {
        $this->line('[1/9] Setup roles & permissions...');

        $rolesNeeded = [
            'inspektur'  => 'Inspektur',
            'sekretaris' => 'Sekretaris',
            'ka_irban'   => 'Kepala Irban',
            'evlap'      => 'Subbag Evaluasi & Pelaporan',
            'dalnis'     => 'Pengendali Teknis (Dalnis)',
            'auditor'    => 'Auditor / Anggota Tim',
            'auditi'     => 'Auditi (Entitas)',
        ];

        $roleIds = [];
        foreach ($rolesNeeded as $name => $label) {
            $ex = $this->conn->table('roles')->where('name', $name)->get()->getRowArray();
            if ($ex) {
                $roleIds[$name] = (int) $ex['id'];
            } else {
                $this->conn->table('roles')->insert(['name' => $name, 'label' => $label]);
                $roleIds[$name] = (int) $this->conn->insertID();
                $this->line("      + role '{$name}'");
            }
        }

        // Permissions yang dibutuhkan
        $permsNeeded = [
            'master.view','master.manage','pkpt.view','pkpt.input','pkpt.manage',
            'spt.view','spt.create','spt.approve','spt.manage_all','data_entitas.view',
        ];
        $permIds = [];
        foreach ($permsNeeded as $p) {
            $ex = $this->conn->table('permissions')->where('name', $p)->get()->getRowArray();
            if ($ex) {
                $permIds[$p] = (int) $ex['id'];
            } else {
                $this->conn->table('permissions')->insert(['name' => $p]);
                $permIds[$p] = (int) $this->conn->insertID();
            }
        }

        // Assign permissions ke roles
        $assignment = [
            'inspektur'  => ['master.view','pkpt.view','spt.view','spt.manage_all','spt.approve'],
            'sekretaris' => ['master.view','pkpt.view','spt.view','spt.approve'],
            'ka_irban'   => ['master.view','pkpt.view','pkpt.input','spt.view','spt.create','spt.approve'],
            'evlap'      => ['master.view','pkpt.view','pkpt.input','pkpt.manage','spt.view','spt.manage_all','spt.approve'],
            'dalnis'     => ['master.view','pkpt.view','spt.view','spt.create','spt.manage_all'],
            'auditor'    => ['master.view','pkpt.view','spt.view','spt.create'],
            'auditi'     => ['data_entitas.view'],
        ];

        foreach ($assignment as $role => $perms) {
            $rid = $roleIds[$role] ?? null;
            if (!$rid) continue;
            foreach ($perms as $p) {
                $pid = $permIds[$p] ?? null;
                if (!$pid) continue;
                if (!$this->conn->table('role_permissions')
                        ->where('role_id', $rid)->where('permission_id', $pid)->countAllResults()) {
                    $this->conn->table('role_permissions')->insert(['role_id' => $rid, 'permission_id' => $pid]);
                }
            }
        }

        return $roleIds;
    }

    // ─── STEP 2 : USERS ──────────────────────────────────────────────────

    private function step2Users(array $roleIds): array
    {
        $this->line('[2/9] Membuat akun pengguna...');

        $users = [
            'inspektur'  => ['Drs. H. Bambang Surya, M.Si',   'inspektur@sim.test',  'inspektur'],
            'sekretaris' => ['Hj. Sriningsih, SH, MH',         'sekretaris@sim.test', 'sekretaris'],
            'irban'      => ['Dra. Endang Kusumawati, MM',      'irban1@sim.test',     'ka_irban'],
            'evlap'      => ['Fajar Nugroho, SE, M.Ak',         'evlap@sim.test',      'evlap'],
            'dalnis'     => ['Drs. Ahmad Fauzan, M.Si',         'dalnis@sim.test',     'dalnis'],
            'ketua'      => ['Siti Rahayu, SE',                  'ketua@sim.test',      'auditor'],
            'at1'        => ['Budi Santoso, S.AP',               'at1@sim.test',        'auditor'],
            'bkpsdm'     => ['Kepala BKPSDM',                    'bkpsdm@sim.test',     'auditi'],
        ];

        $ids = [];
        foreach ($users as $key => [$name, $email, $role]) {
            $ex = $this->conn->table('users')->where('email', $email)->get()->getRowArray();
            if ($ex) {
                $uid = (int) $ex['id'];
                $this->line("      ~ {$email} sudah ada");
            } else {
                $this->conn->table('users')->insert([
                    'name'       => $name,
                    'email'      => $email,
                    'password'   => $this->pass,
                    'status'     => 'active',
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]);
                $uid = (int) $this->conn->insertID();
                $this->line("      + {$email}");
            }
            $ids[$key] = $uid;

            // Assign role
            $rid = $roleIds[$role] ?? null;
            if ($rid && !$this->conn->table('user_roles')
                    ->where('user_id', $uid)->where('role_id', $rid)->countAllResults()) {
                $this->conn->table('user_roles')->insert(['user_id' => $uid, 'role_id' => $rid]);
            }
        }

        return $ids;
    }

    // ─── STEP 3 : SDM ────────────────────────────────────────────────────

    private function step3Sdm(array $userIds): array
    {
        $this->line('[3/9] Membuat data SDM (Dalnis, KT, AT)...');

        $sdmData = [
            'dalnis' => [
                'user_id'            => $userIds['dalnis'],
                'nip'                => '197501012000031001',
                'nama'               => 'Drs. Ahmad Fauzan, M.Si',
                'pangkat_golongan'   => 'Pembina / IV-a',
                'jabatan_struktural' => null,
                'jabatan_fungsional' => 'Auditor Madya',
            ],
            'ketua' => [
                'user_id'            => $userIds['ketua'],
                'nip'                => '198203152006042002',
                'nama'               => 'Siti Rahayu, SE',
                'pangkat_golongan'   => 'Penata Tk.I / III-d',
                'jabatan_struktural' => null,
                'jabatan_fungsional' => 'Auditor Muda',
            ],
            'at1' => [
                'user_id'            => $userIds['at1'],
                'nip'                => '199007202015031003',
                'nama'               => 'Budi Santoso, S.AP',
                'pangkat_golongan'   => 'Penata Muda / III-a',
                'jabatan_struktural' => null,
                'jabatan_fungsional' => 'Auditor Pertama',
            ],
        ];

        $ids = [];
        foreach ($sdmData as $key => $data) {
            $ex = $this->conn->table('sdm')->where('user_id', $data['user_id'])->get()->getRowArray();
            if ($ex) {
                $ids[$key] = (int) $ex['id'];
                $this->line("      ~ SDM {$data['nama']} sudah ada");
            } else {
                $this->conn->table('sdm')->insert(array_merge($data, [
                    'irban_id'   => null,
                    'aktif'      => 1,
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]));
                $ids[$key] = (int) $this->conn->insertID();
                $this->line("      + SDM {$data['nama']}");
            }
        }

        return $ids;
    }

    // ─── STEP 4 : IRBAN ──────────────────────────────────────────────────

    private function step4Irban(int $kepalaSdmId): int
    {
        $this->line('[4/9] Membuat Irban I...');

        $ex = $this->conn->table('irban')->where('kode', 'IRBAN-I')->get()->getRowArray();
        if ($ex) {
            $this->line('      ~ Irban I sudah ada');
            return (int) $ex['id'];
        }

        $this->conn->table('irban')->insert([
            'kode'          => 'IRBAN-I',
            'nama'          => 'Inspektorat Bidang Pemerintahan',
            'kepala_sdm_id' => $kepalaSdmId,
            'created_at'    => $this->now,
            'updated_at'    => $this->now,
        ]);
        $id = (int) $this->conn->insertID();
        $this->line("      + Irban I (id={$id})");
        return $id;
    }

    private function linkSdmToIrban(array $sdmIds, int $irbanId): void
    {
        foreach ($sdmIds as $sdmId) {
            $this->conn->table('sdm')->where('id', $sdmId)->update([
                'irban_id'   => $irbanId,
                'updated_at' => $this->now,
            ]);
        }
    }

    // ─── STEP 5 : ENTITAS ────────────────────────────────────────────────

    private function step5Entitas(int $bkpsdmUserId): int
    {
        $this->line('[5/9] Membuat Entitas BKPSDM...');

        $nama = 'Badan Kepegawaian Dan Sumber Daya Manusia';
        $ex   = $this->conn->table('entitas')->where('nama', $nama)->get()->getRowArray();

        if ($ex) {
            if (!$ex['user_id']) {
                $this->conn->table('entitas')->where('id', $ex['id'])
                    ->update(['user_id' => $bkpsdmUserId, 'updated_at' => $this->now]);
            }
            $this->line('      ~ Entitas BKPSDM sudah ada');
            return (int) $ex['id'];
        }

        $this->conn->table('entitas')->insert([
            'nama'       => $nama,
            'user_id'    => $bkpsdmUserId,
            'aktif'      => 1,
            'created_at' => $this->now,
            'updated_at' => $this->now,
        ]);
        $id = (int) $this->conn->insertID();
        $this->line("      + Entitas BKPSDM (id={$id})");
        return $id;
    }

    // ─── STEP 6 : PKPT SETTING ───────────────────────────────────────────

    private function step6PkptSetting(int $approvedBy): void
    {
        $ex = $this->conn->table('pkpt_setting')->where('tahun', 2026)->get()->getRowArray();
        if ($ex) return;

        $this->conn->table('pkpt_setting')->insert([
            'tahun'           => 2026,
            'total_hp_tahunan'=> 240,
            'tarif_hp'        => 175000,
            'tanggal_pkpt'    => '2026-01-15',
            'nomor_pkpt'      => '700/001/INSP/2026',
            'status'          => 'disetujui',
            'approved_by'     => $approvedBy,
            'approved_at'     => '2026-01-20 08:00:00',
            'created_at'      => $this->now,
            'updated_at'      => $this->now,
        ]);
    }

    // ─── STEP 7 : PKPT ───────────────────────────────────────────────────

    private function step7Pkpt(int $irbanId, int $createdBy): int
    {
        $this->line('[6/9] Membuat PKPT 2026 (disetujui)...');

        $ex = $this->conn->table('pkpt')
            ->where('tahun', 2026)->where('irban_id', $irbanId)->get()->getRowArray();

        if ($ex) {
            $this->line('      ~ PKPT 2026 sudah ada');
            return (int) $ex['id'];
        }

        $this->conn->table('pkpt')->insert([
            'tahun'       => 2026,
            'irban_id'    => $irbanId,
            'status'      => 'disetujui',
            'created_by'  => $createdBy,
            'approved_by' => $createdBy,
            'approved_at' => '2026-01-20 09:00:00',
            'created_at'  => $this->now,
            'updated_at'  => $this->now,
        ]);
        $id = (int) $this->conn->insertID();
        $this->line("      + PKPT 2026 Irban I (id={$id})");
        return $id;
    }

    // ─── STEP 8 : PKPT KEGIATAN ──────────────────────────────────────────

    private function step8Kegiatan(int $pkptId, array $sdmIds, int $entitasId): array
    {
        $this->line('[7/9] Membuat Kegiatan PKPT + Tim + Entitas...');

        // Kode temuan
        $kt = $this->conn->table('kode_temuan')->where('kode', 'KPG')->get()->getRowArray();
        if (!$kt) {
            $this->conn->table('kode_temuan')->insert([
                'kode'       => 'KPG',
                'uraian'     => 'Kepegawaian',
                'jenis'      => '3E',
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
            $kodeTemuanId = (int) $this->conn->insertID();
        } else {
            $kodeTemuanId = (int) $kt['id'];
        }

        // Kegiatan
        $ex = $this->conn->table('pkpt_kegiatan')
            ->where('pkpt_id', $pkptId)->where('kode_kegiatan', 'P.1.2026')
            ->get()->getRowArray();

        if ($ex) {
            $this->line('      ~ Kegiatan sudah ada');
            return [(int) $ex['id'], $kodeTemuanId];
        }

        $this->conn->table('pkpt_kegiatan')->insert([
            'pkpt_id'          => $pkptId,
            'kode_kegiatan'    => 'P.1.2026',
            'tujuan_sasaran'   => 'Menilai efektivitas pengelolaan kepegawaian pada Badan Kepegawaian Dan Sumber Daya Manusia TA. 2025',
            'area_pengawasan'  => 'Manajemen ASN — Rekrutmen, Penempatan, Pengembangan SDM',
            'jenis_pengawasan' => 'Audit Reguler',
            'created_at'       => $this->now,
            'updated_at'       => $this->now,
        ]);
        $kegiatanId = (int) $this->conn->insertID();
        $this->line("      + Kegiatan P.1.2026 Audit BKPSDM (id={$kegiatanId})");

        // Link entitas
        $this->conn->table('pkpt_entitas')->insert([
            'pkpt_kegiatan_id' => $kegiatanId,
            'entitas_id'       => $entitasId,
        ]);

        // Tim PKPT (HP allocation)
        foreach ([
            [$sdmIds['dalnis'], 5],
            [$sdmIds['ketua'],  10],
            [$sdmIds['at1'],    10],
        ] as [$sdm, $hp]) {
            $this->conn->table('pkpt_tim')->insert([
                'pkpt_kegiatan_id' => $kegiatanId,
                'sdm_id'           => $sdm,
                'hp_total'         => $hp,
                'created_at'       => $this->now,
                'updated_at'       => $this->now,
            ]);
        }

        return [$kegiatanId, $kodeTemuanId];
    }

    // ─── STEP 9 : SPT ────────────────────────────────────────────────────

    private function step9Spt(int $kegiatanId, int $irbanId, array $sdmIds, array $userIds): int
    {
        $this->line('[8/9] Membuat SPT (status: TERBIT)...');

        $ex = $this->conn->table('spt')->where('pkpt_kegiatan_id', $kegiatanId)->get()->getRowArray();
        if ($ex) {
            $this->line('      ~ SPT sudah ada');
            return (int) $ex['id'];
        }

        $this->conn->table('spt')->insert([
            'pkpt_kegiatan_id' => $kegiatanId,
            'jenis_spt'        => 'pkpt',
            'irban_id'         => $irbanId,
            'tahun'            => 2026,
            'nama_tim'         => 'Tim Audit Reguler BKPSDM 2026',
            'nomor_naskah'     => '700/SPT.01/INSP/IV/2026',
            'tanggal_naskah'   => '2026-04-01',
            'dasar_1'          => 'Program Kerja Pengawasan Tahunan (PKPT) Inspektorat Tahun 2026',
            'dasar_2'          => 'Surat Perintah Inspektur Nomor 700/001/INSP/2026',
            'tujuan'           => 'Melaksanakan audit reguler atas pengelolaan kepegawaian pada Badan Kepegawaian Dan Sumber Daya Manusia TA. 2025 untuk menilai efektivitas, efisiensi, dan kepatuhan terhadap peraturan perundangan yang berlaku.',
            'tanggal_mulai'    => '2026-04-07',
            'tanggal_selesai'  => '2026-04-25',
            'tembusan'         => 'Bupati/Walikota c.q. Sekretaris Daerah',
            'penandatangan_id' => $sdmIds['dalnis'],
            'status'           => 'terbit',
            'created_by'       => $userIds['ketua'],
            'created_at'       => $this->now,
            'updated_at'       => $this->now,
        ]);
        $sptId = (int) $this->conn->insertID();
        $this->line("      + SPT 700/SPT.01/INSP/IV/2026 (id={$sptId})");

        // Tim SPT
        foreach ([
            [$sdmIds['dalnis'], 'Pengendali Teknis', 1],
            [$sdmIds['ketua'],  'Ketua Tim',          2],
            [$sdmIds['at1'],    'Anggota Tim',         3],
        ] as [$sdm, $peran, $urut]) {
            $this->conn->table('spt_tim')->insert([
                'spt_id'     => $sptId,
                'sdm_id'     => $sdm,
                'peran_spt'  => $peran,
                'urutan'     => $urut,
                'created_at' => $this->now,
                'updated_at' => $this->now,
            ]);
        }

        // History approval (simulate full approval chain)
        foreach ([
            ['irban',      $userIds['irban'],      '2026-03-28 09:00:00'],
            ['evlap',      $userIds['evlap'],       '2026-03-29 10:30:00'],
            ['sekretaris', $userIds['sekretaris'],  '2026-04-01 08:00:00'],
        ] as [$tahap, $approver, $at]) {
            $this->conn->table('spt_approval')->insert([
                'spt_id'      => $sptId,
                'tahap'       => $tahap,
                'status'      => 'approved',
                'approved_by' => $approver,
                'catatan'     => null,
                'approved_at' => $at,
                'created_at'  => $this->now,
            ]);
        }

        return $sptId;
    }

    // ─── STEP 10 : PKA ───────────────────────────────────────────────────

    private function step10Pka(int $sptId, array $sdmIds, array $userIds): array
    {
        $this->line('      + Membuat PKA (3 prosedur audit kepegawaian)...');

        $prosedurData = [
            [
                'fase'            => 'Persiapan',
                'nomor_urut'      => 1,
                'uraian_prosedur' => 'Pelajari peraturan perundangan terkait manajemen ASN (UU ASN No.5/2014, PP 11/2017, PerBKN) dan dokumen perencanaan kepegawaian BKPSDM.',
                'rencana_waktu'   => 3,
            ],
            [
                'fase'            => 'Pelaksanaan',
                'nomor_urut'      => 1,
                'uraian_prosedur' => 'Uji kesesuaian penempatan pegawai dalam jabatan: bandingkan SK Penempatan dengan persyaratan kompetensi jabatan sesuai ANJAB yang berlaku.',
                'rencana_waktu'   => 5,
            ],
            [
                'fase'            => 'Pelaksanaan',
                'nomor_urut'      => 2,
                'uraian_prosedur' => 'Uji pelaksanaan pengembangan kompetensi ASN: keikutsertaan diklat, nilai SKP, dan realisasi Rencana Pengembangan Individu (IDP) TA. 2025.',
                'rencana_waktu'   => 3,
            ],
        ];

        $pkaIds = [];
        foreach ($prosedurData as $i => $pka) {
            $ex = $this->conn->table('pka')
                ->where('spt_id', $sptId)->where('nomor_urut', $pka['nomor_urut'])
                ->where('fase', $pka['fase'])->get()->getRowArray();

            if ($ex) {
                $pkaIds[$i] = (int) $ex['id'];
            } else {
                $this->conn->table('pka')->insert(array_merge($pka, [
                    'spt_id'     => $sptId,
                    'created_by' => $userIds['ketua'],
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ]));
                $pkaIds[$i] = (int) $this->conn->insertID();

                // Assign ke AT1
                $this->conn->table('pka_assignment')->insert([
                    'pka_id'     => $pkaIds[$i],
                    'sdm_id'     => $sdmIds['at1'],
                    'created_at' => $this->now,
                ]);
            }
        }

        return $pkaIds;
    }

    // ─── STEP 11 : KM STAGES ─────────────────────────────────────────────

    private function step11KmStages(int $sptId, array $sdmIds): void
    {
        $this->line('      + Mengisi tahapan Kendali Mutu (KM-1, KM-2, KM-4, KM-5)...');

        // KM-1 : Kartu Penugasan
        if (!$this->conn->table('spt_km1')->where('spt_id', $sptId)->countAllResults()) {
            $this->conn->table('spt_km1')->insert([
                'spt_id'            => $sptId,
                'no_kartu'          => 'KM1/P.1.2026/BKPSDM',
                'tujuan_satker'     => 'Menilai efektivitas dan ketaatan pengelolaan kepegawaian BKPSDM TA. 2025 sesuai ketentuan yang berlaku.',
                'kegiatan'          => 'Audit Reguler Pengelolaan Kepegawaian',
                'rencana_mulai'     => '2026-04-07',
                'rencana_selesai'   => '2026-04-25',
                'rencana_kunjungan' => 'Kantor BKPSDM — Jl. Pemda No. 1',
                'catatan'           => 'Koordinasi dengan Kepala BKPSDM sudah dilakukan.',
                'created_at'        => $this->now,
                'updated_at'        => $this->now,
            ]);
        }

        // KM-2 : Anggaran Waktu AT1
        foreach ([
            [$sdmIds['at1'],   3, 8, 2],
            [$sdmIds['ketua'], 2, 5, 2],
        ] as [$sdm, $p, $pl, $pn]) {
            if (!$this->conn->table('spt_anggaran_waktu')
                ->where('spt_id', $sptId)->where('sdm_id', $sdm)->countAllResults()) {
                $this->conn->table('spt_anggaran_waktu')->insert([
                    'spt_id'                    => $sptId,
                    'sdm_id'                    => $sdm,
                    'persiapan_start'           => '2026-04-07',
                    'persiapan_end'             => '2026-04-11',
                    'persiapan_rencana_hari'    => $p,
                    'pelaksanaan_start'         => '2026-04-14',
                    'pelaksanaan_end'           => '2026-04-23',
                    'pelaksanaan_rencana_hari'  => $pl,
                    'penyelesaian_start'        => '2026-04-24',
                    'penyelesaian_end'          => '2026-04-25',
                    'penyelesaian_rencana_hari' => $pn,
                    'created_at'                => $this->now,
                    'updated_at'                => $this->now,
                ]);
            }
        }

        // KM-4 : Lembar Perencanaan
        if (!$this->conn->table('spt_km4')->where('spt_id', $sptId)->countAllResults()) {
            $this->conn->table('spt_km4')->insert([
                'spt_id'            => $sptId,
                'dasar_penugasan'   => 'PKPT Inspektorat Tahun 2026 No. 700/001/INSP/2026',
                'jenis_penugasan'   => 'Audit Reguler',
                'tujuan_pengawasan' => 'Menilai kesesuaian pengelolaan kepegawaian dengan peraturan yang berlaku.',
                'sasaran'           => 'Manajemen ASN: rekrutmen, penempatan, dan pengembangan kompetensi.',
                'cek_kp'            => 1, 'cek_jenis_tujuan' => 1, 'cek_misi_tujuan'   => 1,
                'cek_informasi'     => 1, 'cek_lhp_terakhir' => 1, 'cek_lhp_ekstern'   => 0,
                'cek_perundangan'   => 1, 'cek_kertas_kerja' => 1, 'cek_tao_fao'        => 0,
                'cek_program'       => 1, 'cek_anggaran_waktu' => 1,
                'catatan_dalnis'    => 'Fokus pada penempatan jabatan dan pengembangan kompetensi. — Ahmad Fauzan, Dalnis',
                'created_at'        => $this->now,
                'updated_at'        => $this->now,
            ]);
        }

        // KM-5 : Reviu PKA — DISETUJUI (trigger KKA creation di sistem nyata)
        if (!$this->conn->table('spt_km5')->where('spt_id', $sptId)->countAllResults()) {
            $this->conn->table('spt_km5')->insert([
                'spt_id'            => $sptId,
                'tanggal_reviu'     => '2026-04-10',
                'status'            => 'disetujui',
                'catatan_reviu'     => 'PKA sudah memadai. Prosedur mencakup aspek kritis pengelolaan kepegawaian.',
                'saran_perbaikan'   => null,
                'cek_tujuan'        => 1,
                'cek_sasaran'       => 1,
                'cek_ruang_lingkup' => 1,
                'cek_metodologi'    => 1,
                'cek_tim'           => 1,
                'cek_waktu'         => 1,
                'created_at'        => $this->now,
                'updated_at'        => $this->now,
            ]);
        }
    }

    // ─── STEP 12 : KKA ───────────────────────────────────────────────────

    private function step12Kka(int $sptId, array $sdmIds, array $pkaIds, int $kodeTemuanId, array $userIds): int
    {
        $this->line('[9/9] Membuat KKA AT + isi temuan + setujui KT...');

        // KKA header untuk AT1 (sudah approved KT)
        $ex = $this->conn->table('kka')
            ->where('spt_id', $sptId)->where('sdm_id', $sdmIds['at1'])->get()->getRowArray();

        if ($ex) {
            $kkaId = (int) $ex['id'];
            $this->line('      ~ KKA AT1 sudah ada');
        } else {
            $this->conn->table('kka')->insert([
                'spt_id'         => $sptId,
                'sdm_id'         => $sdmIds['at1'],
                'no_kka'         => 'KKA/AT1/P.1.2026/BKPSDM',
                'status'         => 'selesai',
                'status_kka'     => 'approved',
                'catatan_dalnis' => 'KKA sudah lengkap. Temuan didukung bukti yang memadai. — Ahmad Fauzan, Dalnis',
                'submitted_at'   => '2026-04-22 14:00:00',
                'reviewed_at'    => '2026-04-23 09:30:00',
                'catatan_review' => 'KKA disetujui. Temuan dan rekomendasi sudah tepat. — Siti Rahayu, KT',
                'created_at'     => $this->now,
                'updated_at'     => $this->now,
            ]);
            $kkaId = (int) $this->conn->insertID();
            $this->line("      + KKA AT1 (id={$kkaId})");
        }

        // Ikhtisar Prosedur 1 — Tidak ada temuan
        $p0 = $pkaIds[0] ?? null;
        if ($p0 && !$this->conn->table('kka_ikhtisar')
                ->where('kka_id', $kkaId)->where('pka_id', $p0)->countAllResults()) {
            $this->conn->table('kka_ikhtisar')->insert([
                'kka_id'          => $kkaId,
                'pka_id'          => $p0,
                'nomor_urut'      => 1,
                'hasil_observasi' => "Auditor menelaah dokumen: PP No. 11 Tahun 2017 tentang Manajemen PNS, PerBKN No. 1/2019, Analisis Jabatan (ANJAB) BKPSDM, dan dokumen perencanaan kepegawaian TA. 2025.\n\nSemua dokumen regulasi tersedia dan relevan dengan ruang lingkup audit. Tidak ditemukan hambatan dalam tahap persiapan.",
                'simpulan'        => 'Dokumen peraturan tersedia dan dipahami. Tidak ada hal yang menghambat pelaksanaan audit persiapan.',
                'status'          => 'selesai',
                'created_at'      => $this->now,
                'updated_at'      => $this->now,
            ]);
        }

        // Ikhtisar Prosedur 2 — ADA TEMUAN
        $p1 = $pkaIds[1] ?? null;
        $simpulanId = null;
        if ($p1) {
            $exIkh = $this->conn->table('kka_ikhtisar')
                ->where('kka_id', $kkaId)->where('pka_id', $p1)->get()->getRowArray();

            if (!$exIkh) {
                $this->conn->table('kka_ikhtisar')->insert([
                    'kka_id'          => $kkaId,
                    'pka_id'          => $p1,
                    'nomor_urut'      => 2,
                    'hasil_observasi' => "Auditor melakukan pengujian terhadap 20 SK Penempatan Pegawai yang diterbitkan BKPSDM pada Januari—Desember 2025.\n\nDari 20 SK yang diuji:\n• 14 SK (70%): penempatan sesuai persyaratan kompetensi jabatan\n• 6 SK (30%): penempatan TIDAK SESUAI dengan kompetensi yang dipersyaratkan ANJAB\n\nContoh ketidaksesuaian:\n1. Pegawai berlatar belakang pendidikan Teknik Sipil (S1) ditempatkan sebagai Analis SDM Aparatur yang mensyaratkan pendidikan Administrasi Negara/Manajemen SDM\n2. Pegawai tanpa sertifikasi Asesor Kompetensi ditempatkan sebagai pengelola assessment center\n3. Pegawai golongan III/a ditempatkan pada jabatan yang mensyaratkan minimal III/c",
                    'simpulan'        => 'Terdapat ketidaksesuaian signifikan antara kompetensi pegawai yang ditempatkan dengan persyaratan jabatan berdasarkan ANJAB.',
                    'status'          => 'selesai',
                    'created_at'      => $this->now,
                    'updated_at'      => $this->now,
                ]);
            }

            // Simpulan KKSA (temuan)
            $exSim = $this->conn->table('kka_simpulan')
                ->where('kka_id', $kkaId)->where('pka_id', $p1)->get()->getRowArray();

            if (!$exSim) {
                $this->conn->table('kka_simpulan')->insert([
                    'kka_id'           => $kkaId,
                    'pka_id'           => $p1,
                    'nomor_urut'       => 1,
                    'kondisi'          => 'Terdapat 6 dari 20 SK Penempatan Pegawai (30%) yang menempatkan pegawai pada jabatan yang tidak sesuai dengan persyaratan kompetensi dalam Analisis Jabatan (ANJAB). Enam pegawai dimaksud meliputi: (1) pegawai berlatar belakang S1 Teknik Sipil pada Jabatan Analis SDM Aparatur; (2) pegawai tanpa sertifikasi assessor kompetensi pada pengelolaan assessment center; (3-6) pegawai dengan pangkat tidak memenuhi syarat jabatan yang ditempati.',
                    'kriteria'         => "1. Pasal 54 PP No. 11 Tahun 2017 tentang Manajemen PNS: Pengisian jabatan mempertimbangkan kompetensi teknis, manajerial, dan sosial kultural.\n2. PerBKN No. 1 Tahun 2019 tentang Petunjuk Teknis Pangkat dan Jabatan PNS: setiap jabatan memiliki syarat kepangkatan minimal.\n3. Peraturan Daerah/Perbup tentang Analisis Jabatan yang menetapkan persyaratan setiap jabatan.",
                    'sebab'            => "1. BKPSDM belum memiliki sistem pemetaan kompetensi terintegrasi yang menjadi acuan wajib saat penempatan pegawai.\n2. Analisis Jabatan (ANJAB) yang ada belum dimutakhirkan sejak 2022 sehingga tidak mencerminkan kebutuhan aktual.\n3. Proses penempatan lebih mempertimbangkan ketersediaan pegawai (man-power) daripada kecocokan kompetensi.",
                    'akibat'           => "1. Pegawai tidak dapat menjalankan tugas jabatan secara optimal karena ketidaksesuaian kompetensi, berpotensi menurunkan kualitas layanan kepegawaian.\n2. Timbulnya inefisiensi organisasi karena penempatan yang tidak tepat memerlukan waktu adaptasi lebih lama.\n3. Potensi risiko hukum apabila keputusan kepegawaian yang dibuat oleh pejabat tidak berkompeten digugat di pengadilan.",
                    'rekomendasi_awal' => "Kepala BKPSDM agar:\n1. Memutakhirkan Analisis Jabatan dan Analisis Beban Kerja paling lambat 3 (tiga) bulan;\n2. Menerbitkan Standar Operasional Prosedur (SOP) penempatan pegawai yang mewajibkan verifikasi pemenuhan persyaratan kompetensi;\n3. Melakukan reviu dan penyesuaian penempatan 6 (enam) pegawai yang tidak sesuai kompetensi dalam jangka waktu 6 (enam) bulan;\n4. Mengikutsertakan 6 pegawai tersebut dalam diklat kompetensi teknis jabatan yang relevan.",
                    'kode_temuan_id'   => $kodeTemuanId,
                    'nilai_financial'  => null,
                    'status'           => 'selesai',
                    'created_at'       => $this->now,
                    'updated_at'       => $this->now,
                ]);
                $simpulanId = (int) $this->conn->insertID();
            } else {
                $simpulanId = (int) $exSim['id'];
            }
        }

        // Ikhtisar Prosedur 3 — Tidak ada temuan
        $p2 = $pkaIds[2] ?? null;
        if ($p2 && !$this->conn->table('kka_ikhtisar')
                ->where('kka_id', $kkaId)->where('pka_id', $p2)->countAllResults()) {
            $this->conn->table('kka_ikhtisar')->insert([
                'kka_id'          => $kkaId,
                'pka_id'          => $p2,
                'nomor_urut'      => 3,
                'hasil_observasi' => "Auditor menguji keikutsertaan 45 pegawai dalam program diklat TA. 2025.\n\nHasil pengujian:\n• 40 dari 45 pegawai (88,9%) telah mengikuti ≥20 JP diklat pertahun sesuai target\n• Nilai SKP rata-rata: 84,5 (Kategori: Baik)\n• 38 pegawai memiliki IDP yang telah disusun dan direview atasan langsung\n• 5 pegawai (11,1%) belum mencapai target 20 JP karena sakit/cuti panjang",
                'simpulan'        => 'Pengembangan kompetensi ASN BKPSDM secara umum sudah berjalan baik. Tidak terdapat temuan material yang perlu ditindaklanjuti.',
                'status'          => 'selesai',
                'created_at'      => $this->now,
                'updated_at'      => $this->now,
            ]);
        }

        return $kkaId;
    }

    // ─── STEP 13 : NHP ───────────────────────────────────────────────────

    private function step13Nhp(int $sptId, int $kkaId, array $userIds): void
    {
        $this->line('      + Membuat NHP (draft — siap dikirim ke BKPSDM)...');

        $ex = $this->conn->table('nhp')->where('spt_id', $sptId)->get()->getRowArray();
        if ($ex) {
            $this->line('      ~ NHP sudah ada');
            return;
        }

        $this->conn->table('nhp')->insert([
            'spt_id'      => $sptId,
            'nomor_nhp'   => '700/NHP.01/INSP/IV/2026',
            'tanggal_nhp' => '2026-04-24',
            'perihal'     => 'Notisi Hasil Pemeriksaan atas Pengelolaan Kepegawaian pada Badan Kepegawaian Dan Sumber Daya Manusia TA. 2025',
            'status'      => 'draft',
            'catatan'     => 'NHP ini memuat temuan audit terkait ketidaksesuaian penempatan pegawai dengan kompetensi jabatan yang dipersyaratkan.',
            'created_by'  => $userIds['ketua'],
            'created_at'  => $this->now,
            'updated_at'  => $this->now,
        ]);
        $nhpId = (int) $this->conn->insertID();

        // Ambil semua simpulan dari KKA AT1 untuk dijadikan item NHP
        $simpulans = $this->conn->table('kka_simpulan')->where('kka_id', $kkaId)->get()->getResultArray();
        $noUrut = 1;
        foreach ($simpulans as $s) {
            $this->conn->table('nhp_item')->insert([
                'nhp_id'            => $nhpId,
                'kka_simpulan_id'   => $s['id'],
                'nomor_urut'        => $noUrut++,
                'judul_temuan'      => 'Penempatan Pegawai Tidak Sesuai Kompetensi Jabatan yang Dipersyaratkan',
                'kondisi'           => $s['kondisi'],
                'kriteria'          => $s['kriteria'],
                'sebab'             => $s['sebab'],
                'akibat'            => $s['akibat'],
                'rekomendasi'       => $s['rekomendasi_awal'],
                'nilai_temuan'      => null,
                'tanggapan_entitas' => null,
                'status_tanggapan'  => 'pending',
                'created_at'        => $this->now,
                'updated_at'        => $this->now,
            ]);
        }

        $this->line("      + NHP 700/NHP.01/INSP/IV/2026 (id={$nhpId}, " . count($simpulans) . " item temuan)");
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function printRingkasan(): void
    {
        $this->line('');
        $this->line('╔══════════════════════════════════════════════════════════╗', 'green');
        $this->line('║              ✓  SIMULASI BERHASIL DIBUAT!               ║', 'green');
        $this->line('╠══════════════════════════════════════════════════════════╣', 'green');
        $this->line('║  AKUN SIMULASI — password: sim123456                    ║', 'green');
        $this->line('╠════════════════════════════╦═════════════════════════════╣', 'green');
        $this->line('║  inspektur@sim.test        ║  Inspektur (PJ)             ║', 'green');
        $this->line('║  sekretaris@sim.test       ║  Sekretaris                 ║', 'green');
        $this->line('║  irban1@sim.test           ║  Kepala Irban               ║', 'green');
        $this->line('║  evlap@sim.test            ║  Subbag Evlap               ║', 'green');
        $this->line('║  dalnis@sim.test           ║  Pengendali Teknis          ║', 'green');
        $this->line('║  ketua@sim.test            ║  Ketua Tim (KT)             ║', 'green');
        $this->line('║  at1@sim.test              ║  Anggota Tim (AT)           ║', 'green');
        $this->line('║  bkpsdm@sim.test           ║  Auditi — BKPSDM            ║', 'green');
        $this->line('╠════════════════════════════╩═════════════════════════════╣', 'green');
        $this->line('║  SPT  : 700/SPT.01/INSP/IV/2026  (status: TERBIT)       ║', 'green');
        $this->line('║  NHP  : 700/NHP.01/INSP/IV/2026  (status: DRAFT)        ║', 'green');
        $this->line('║  Entitas: Badan Kepegawaian Dan Sumber Daya Manusia      ║', 'green');
        $this->line('╠══════════════════════════════════════════════════════════╣', 'green');
        $this->line('║  Langkah berikutnya:                                     ║', 'green');
        $this->line('║  Login sbg ketua@sim.test → SPT → NHP → [Kirim]         ║', 'green');
        $this->line('║  Login sbg bkpsdm@sim.test → /auditi/nhp → Beri Tanggap ║', 'green');
        $this->line('╚══════════════════════════════════════════════════════════╝', 'green');
        $this->line('');
    }

    private function line(string $text, string $color = ''): void
    {
        $colors = ['green' => "\033[0;32m", 'red' => "\033[0;31m", 'yellow' => "\033[0;33m"];
        $reset  = "\033[0m";
        $c      = $colors[$color] ?? '';
        echo $c . $text . ($c ? $reset : '') . "\n";
    }
}
