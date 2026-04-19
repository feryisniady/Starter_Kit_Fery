<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SptModel;
use App\Models\SptKmModel;
use App\Models\KkaModel;

/**
 * Kelola Kendali Mutu (KM) untuk setiap SPT.
 *
 * Akses berbasis peran_spt (bukan system role):
 *   KM-1, KM-4, KM-5b, KM-10  → KT + Dalnis
 *   KM-5, KM-11                → Dalnis only
 *   KM-2, Independensi         → AT (isi milik sendiri) + KT + Dalnis
 *   KM-3                       → semua anggota tim (read-only)
 */
class KmController extends BaseController
{
    protected SptModel   $sptModel;
    protected SptKmModel $kmModel;

    public function __construct()
    {
        $this->sptModel = new SptModel();
        $this->kmModel  = new SptKmModel();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Checklist index
    // ──────────────────────────────────────────────────────────────────────

    public function index(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        return view('admin/km/index', [
            'title'     => 'Kendali Mutu — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'       => $spt,
            'checklist' => $this->kmModel->getChecklist($sptId),
            'peranSpt'  => getPeranInSpt($sptId),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // KM-1 — Kartu Penugasan format BPKP KM5  (KT + Dalnis)
    // ──────────────────────────────────────────────────────────────────────

    public function km1(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km1')->where('spt_id', $sptId)->get()->getRowArray();
        $km6 = $db->table('spt_km6')->where('spt_id', $sptId)->get()->getRowArray();

        // HP per anggota dari spt_tim + realisasi dari anggaran_waktu
        $timAw = $db->table('spt_tim st')
            ->select('st.sdm_id, st.peran_spt, st.hp_desk, st.hp_field,
                      sdm.nama as sdm_nama,
                      aw.persiapan_realisasi_hari, aw.pelaksanaan_realisasi_hari,
                      aw.penyelesaian_realisasi_hari')
            ->join('sdm', 'sdm.id = st.sdm_id')
            ->join('spt_anggaran_waktu aw', 'aw.spt_id = st.spt_id AND aw.sdm_id = st.sdm_id', 'left')
            ->where('st.spt_id', $sptId)
            ->orderBy('st.urutan')
            ->get()->getResultArray();

        // Cek km5b (entry meeting) untuk status realisasi
        $km5bAda = !empty($km6);

        return view('admin/km/km1', [
            'title'    => 'KM-1 — Kartu Penugasan',
            'spt'      => $spt,
            'row'      => $row,
            'km6'      => $km6,
            'timAw'    => $timAw,
            'km5bAda'  => $km5bAda,
            'canEdit'  => canEditKmInSpt($sptId, 'km1'),
        ]);
    }

    public function saveKm1(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'km1')) return redirect()->back()->with('error', 'Hanya Ketua Tim atau Dalnis yang dapat mengisi KM-1.');

        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $p    = fn(string $k) => $this->request->getPost($k);
        $pd   = fn(string $k) => $this->request->getPost($k) ?: null;
        $db   = \Config\Database::connect();

        $data = [
            'spt_id'                 => $sptId,
            'no_kartu'               => $p('no_kartu'),
            'tingkat_risiko'         => $p('tingkat_risiko'),
            'laporan_kepada'         => $p('laporan_kepada'),
            'tujuan_satker'          => $p('tujuan_satker'),
            'kegiatan'               => $p('kegiatan'),
            'rencana_mulai'          => $pd('rencana_mulai'),
            'rencana_selesai'        => $pd('rencana_selesai'),
            'rencana_kunjungan'      => $p('rencana_kunjungan'),
            'kunjungan_pm_1'         => $pd('kunjungan_pm_1'),
            'kunjungan_pm_2'         => $pd('kunjungan_pm_2'),
            'kunjungan_pm_3'         => $pd('kunjungan_pm_3'),
            'kunjungan_pt_1'         => $pd('kunjungan_pt_1'),
            'kunjungan_pt_2'         => $pd('kunjungan_pt_2'),
            'kunjungan_pt_3'         => $pd('kunjungan_pt_3'),
            'rmp_bulan'              => $pd('rmp_bulan'),
            'rpl_bulan'              => $pd('rpl_bulan'),
            'tanggal_konsep_laporan' => $pd('tanggal_konsep_laporan'),
            'catatan'                => $p('catatan'),
        ];

        $existing = $db->table('spt_km1')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $db->table('spt_km1')->where('spt_id', $sptId)->update(array_merge($data, ['updated_at' => date('Y-m-d H:i:s')]));
        } else {
            $now = date('Y-m-d H:i:s');
            $db->table('spt_km1')->insert(array_merge($data, ['created_at' => $now, 'updated_at' => $now]));
        }

        logActivity('spt.km1.save', 'spt_km1', "Simpan KM-1 Kartu Penugasan SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM-1 Kartu Penugasan berhasil disimpan.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // KM-2 — Anggaran Waktu  (AT isi milik sendiri; KT/Dalnis isi semua)
    // ──────────────────────────────────────────────────────────────────────

    public function anggaranWaktu(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db      = \Config\Database::connect();
        $sdmId   = getCurrentSdmId();
        $isAdmin = isAuditAdmin();
        $isKtDal = isKtInSpt($sptId) || isDalnisInSpt($sptId);

        // AT: tampilkan hanya baris miliknya; KT/Dalnis/Admin: semua
        $query = $db->table('spt_anggaran_waktu aw')
            ->select('aw.*, s.nama as sdm_nama, s.jabatan_fungsional, st.peran_spt')
            ->join('sdm s', 's.id = aw.sdm_id')
            ->join('spt_tim st', 'st.spt_id = aw.spt_id AND st.sdm_id = aw.sdm_id', 'left')
            ->where('aw.spt_id', $sptId);

        if (!$isAdmin && !$isKtDal && $sdmId) {
            $query->where('aw.sdm_id', $sdmId);
        }

        $rows  = $query->orderBy('st.urutan')->get()->getResultArray();
        $awMap = array_column($rows, null, 'sdm_id');

        // Tim yang belum punya baris AW (untuk KT/Dalnis/Admin bisa tambah)
        $timList = ($isAdmin || $isKtDal)
            ? $db->table('spt_tim st')
                ->select('st.sdm_id, s.nama as sdm_nama, s.jabatan_fungsional, st.peran_spt, st.urutan')
                ->join('sdm s', 's.id = st.sdm_id')
                ->where('st.spt_id', $sptId)
                ->orderBy('st.urutan')
                ->get()->getResultArray()
            : [];

        $db2          = \Config\Database::connect();
        $km5bAda      = (bool)$db2->table('spt_km6')->where('spt_id', $sptId)->countAllResults();
        $km10Ada      = (bool)$db2->table('spt_km10')->where('spt_id', $sptId)->countAllResults();
        $canEditBase  = ($isAdmin || $isKtDal) && canEditKmInSpt($sptId, 'km2');
        $canEditRealisasi = $canEditBase && $km5bAda && !$km10Ada;

        return view('admin/km/anggaran_waktu', [
            'title'             => 'KM-2 — Formulir Anggaran Waktu',
            'spt'               => $spt,
            'awMap'             => $awMap,
            'timList'           => $timList,
            'canEdit'           => $canEditBase,
            'canEditRealisasi'  => $canEditRealisasi,
            'km5bAda'           => $km5bAda,
            'km10Ada'           => $km10Ada,
            'isKtDal'           => $isAdmin || $isKtDal,
            'mySdmId'           => $sdmId,
        ]);
    }

    public function saveAnggaranWaktu(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'km2')) return redirect()->back()->with('error', 'Akses ditolak.');

        $db      = \Config\Database::connect();
        $sdmId   = getCurrentSdmId();
        $isAdmin = isAuditAdmin();
        $isKtDal = isKtInSpt($sptId) || isDalnisInSpt($sptId);
        $now     = date('Y-m-d H:i:s');

        // AT hanya boleh simpan baris milik sendiri
        $postedIds = (array)($this->request->getPost('sdm_id') ?? []);
        $allowedIds = ($isAdmin || $isKtDal) ? $postedIds : array_filter($postedIds, fn($id) => (int)$id === $sdmId);

        foreach ($allowedIds as $sid) {
            $sid = (int)$sid;
            if (!$sid) continue;

            $data = [
                'spt_id'                      => $sptId,
                'sdm_id'                      => $sid,
                'persiapan_start'             => $this->request->getPost("persiapan_start_{$sid}") ?: null,
                'persiapan_end'               => $this->request->getPost("persiapan_end_{$sid}") ?: null,
                'persiapan_rencana_hari'      => $this->request->getPost("persiapan_rencana_{$sid}") ?: null,
                'persiapan_realisasi_hari'    => $this->request->getPost("persiapan_realisasi_{$sid}") ?: null,
                'pelaksanaan_start'           => $this->request->getPost("pelaksanaan_start_{$sid}") ?: null,
                'pelaksanaan_end'             => $this->request->getPost("pelaksanaan_end_{$sid}") ?: null,
                'pelaksanaan_rencana_hari'    => $this->request->getPost("pelaksanaan_rencana_{$sid}") ?: null,
                'pelaksanaan_realisasi_hari'  => $this->request->getPost("pelaksanaan_realisasi_{$sid}") ?: null,
                'penyelesaian_start'          => $this->request->getPost("penyelesaian_start_{$sid}") ?: null,
                'penyelesaian_end'            => $this->request->getPost("penyelesaian_end_{$sid}") ?: null,
                'penyelesaian_rencana_hari'   => $this->request->getPost("penyelesaian_rencana_{$sid}") ?: null,
                'penyelesaian_realisasi_hari' => $this->request->getPost("penyelesaian_realisasi_{$sid}") ?: null,
            ];

            // AT tidak bisa mengisi realisasi (hanya KT/Dalnis yang bisa verifikasi)
            if (!$isAdmin && !$isKtDal) {
                unset($data['persiapan_realisasi_hari'], $data['pelaksanaan_realisasi_hari'], $data['penyelesaian_realisasi_hari']);
            }

            $existing = $db->table('spt_anggaran_waktu')
                ->where('spt_id', $sptId)->where('sdm_id', $sid)->get()->getRowArray();

            if ($existing) {
                $db->table('spt_anggaran_waktu')
                    ->where('spt_id', $sptId)->where('sdm_id', $sid)
                    ->update(array_merge($data, ['updated_at' => $now]));
            } else {
                $db->table('spt_anggaran_waktu')->insert(array_merge($data, ['created_at' => $now, 'updated_at' => $now]));
            }
        }

        logActivity('spt.aw.save', 'spt_anggaran_waktu', "Simpan Anggaran Waktu SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'Anggaran Waktu berhasil disimpan.');
    }

    /** KT verifikasi realisasi anggaran waktu */
    public function verifikasiAw(int $sptId)
    {
        if (!isAuditAdmin() && !isKtInSpt($sptId) && !isDalnisInSpt($sptId)) {
            return redirect()->back()->with('error', 'Hanya Ketua Tim atau Dalnis yang dapat memverifikasi.');
        }

        $sdmId = (int)$this->request->getPost('sdm_id');
        $db    = \Config\Database::connect();

        $db->table('spt_anggaran_waktu')
            ->where('spt_id', $sptId)->where('sdm_id', $sdmId)
            ->update([
                'kt_verified'    => 1,
                'kt_verified_at' => date('Y-m-d H:i:s'),
                'kt_verified_by' => getCurrentSdmId(),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

        logActivity('spt.aw.verifikasi', 'spt_anggaran_waktu', "Verifikasi AW sdm_id={$sdmId} SPT id={$sptId}");
        return redirect()->back()->with('success', 'Realisasi anggaran waktu berhasil diverifikasi.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // KM-3 — Dokumen SPT (auto-prefill, read-only, semua tim bisa lihat)
    // ──────────────────────────────────────────────────────────────────────

    public function km3(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        return view('admin/km/km3', [
            'title' => 'KM-3 — Dokumen SPT',
            'spt'   => $spt,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // KM-4 — Lembar Perencanaan Pengawasan  (KT + Dalnis)
    // ──────────────────────────────────────────────────────────────────────

    public function km4(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km4')->where('spt_id', $sptId)->get()->getRowArray();

        return view('admin/km/km4', [
            'title'   => 'KM-4 — Lembar Perencanaan Pengawasan',
            'spt'     => $spt,
            'row'     => $row,
            'canEdit' => canEditKmInSpt($sptId, 'km4'),
        ]);
    }

    public function saveKm4(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'km4')) return redirect()->back()->with('error', 'Hanya Ketua Tim atau Dalnis yang dapat mengisi KM-4.');

        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $post = $this->request->getPost();
        $db   = \Config\Database::connect();

        $data = [
            'spt_id'             => $sptId,
            'dasar_penugasan'    => $post['dasar_penugasan']    ?? null,
            'jenis_penugasan'    => $post['jenis_penugasan']    ?? null,
            'tujuan_pengawasan'  => $post['tujuan_pengawasan']  ?? null,
            'sasaran'            => $post['sasaran']            ?? null,
            'cek_kp'             => isset($post['cek_kp'])             ? 1 : 0,
            'cek_jenis_tujuan'   => isset($post['cek_jenis_tujuan'])   ? 1 : 0,
            'cek_misi_tujuan'    => isset($post['cek_misi_tujuan'])    ? 1 : 0,
            'cek_informasi'      => isset($post['cek_informasi'])      ? 1 : 0,
            'cek_lhp_terakhir'   => isset($post['cek_lhp_terakhir'])   ? 1 : 0,
            'cek_lhp_ekstern'    => isset($post['cek_lhp_ekstern'])    ? 1 : 0,
            'cek_perundangan'    => isset($post['cek_perundangan'])    ? 1 : 0,
            'cek_kertas_kerja'   => isset($post['cek_kertas_kerja'])   ? 1 : 0,
            'cek_tao_fao'        => isset($post['cek_tao_fao'])        ? 1 : 0,
            'cek_program'        => isset($post['cek_program'])        ? 1 : 0,
            'cek_anggaran_waktu' => isset($post['cek_anggaran_waktu']) ? 1 : 0,
            'catatan_dalnis'     => $post['catatan_dalnis']    ?? null,
        ];

        $now = date('Y-m-d H:i:s');
        $existing = $db->table('spt_km4')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $db->table('spt_km4')->where('spt_id', $sptId)->update(array_merge($data, ['updated_at' => $now]));
        } else {
            $db->table('spt_km4')->insert(array_merge($data, ['created_at' => $now, 'updated_at' => $now]));
        }

        logActivity('spt.km4.save', 'spt_km4', "Simpan KM-4 SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM-4 Lembar Perencanaan berhasil disimpan.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // KM-5 — Reviu PKA  (Dalnis ONLY)
    // ──────────────────────────────────────────────────────────────────────

    public function km5(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db      = \Config\Database::connect();
        $row     = $db->table('spt_km5')->where('spt_id', $sptId)->get()->getRowArray();
        $pkaList = $db->table('pka p')
            ->select('p.*, s.nama as pic_nama')
            ->join('sdm s', 's.id = p.pic_sdm_id', 'left')
            ->where('p.spt_id', $sptId)
            ->orderBy('p.nomor_urut')
            ->get()->getResultArray();

        return view('admin/km/km5', [
            'title'   => 'KM-5 — Reviu PKA',
            'spt'     => $spt,
            'row'     => $row,
            'pkaList' => $pkaList,
            'canEdit' => canEditKmInSpt($sptId, 'km5'),
        ]);
    }

    public function saveKm5(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'km5')) return redirect()->back()->with('error', 'Hanya Pengendali Teknis (Dalnis) yang dapat mengisi KM-5.');

        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $db   = \Config\Database::connect();
        $post = $this->request->getPost();

        $data = [
            'spt_id'            => $sptId,
            'tanggal_reviu'     => $post['tanggal_reviu'] ?: null,
            'status'            => in_array($post['status'] ?? '', ['disetujui','dikembalikan']) ? $post['status'] : 'disetujui',
            'catatan_reviu'     => $post['catatan_reviu']    ?? null,
            'saran_perbaikan'   => $post['saran_perbaikan']  ?? null,
            'cek_tujuan'        => isset($post['cek_tujuan'])        ? 1 : 0,
            'cek_sasaran'       => isset($post['cek_sasaran'])       ? 1 : 0,
            'cek_ruang_lingkup' => isset($post['cek_ruang_lingkup']) ? 1 : 0,
            'cek_metodologi'    => isset($post['cek_metodologi'])    ? 1 : 0,
            'cek_tim'           => isset($post['cek_tim'])           ? 1 : 0,
            'cek_waktu'         => isset($post['cek_waktu'])         ? 1 : 0,
        ];

        $now = date('Y-m-d H:i:s');
        $existing = $db->table('spt_km5')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $db->table('spt_km5')->where('spt_id', $sptId)->update(array_merge($data, ['updated_at' => $now]));
        } else {
            $db->table('spt_km5')->insert(array_merge($data, ['created_at' => $now, 'updated_at' => $now]));
        }

        $msg = 'KM-5 Reviu PKA berhasil disimpan.';
        if ($data['status'] === 'disetujui') {
            $created = (new KkaModel())->autoCreateForSpt($sptId);
            if ($created > 0) $msg .= " KKA otomatis dibuat untuk {$created} anggota tim.";
        }

        logActivity('spt.km5.save', 'spt_km5', "Simpan KM-5 SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', $msg);
    }

    // ──────────────────────────────────────────────────────────────────────
    // KM-5b — Entry Meeting  (KT + Dalnis)
    // ──────────────────────────────────────────────────────────────────────

    public function km5b(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km6')->where('spt_id', $sptId)->get()->getRowArray();

        return view('admin/km/km6', [
            'title'   => 'KM-5b — Entry Meeting',
            'spt'     => $spt,
            'row'     => $row,
            'canEdit' => canEditKmInSpt($sptId, 'km5b'),
        ]);
    }

    public function saveKm5b(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'km5b')) return redirect()->back()->with('error', 'Hanya Ketua Tim atau Dalnis yang dapat mengisi KM-5b.');

        $db   = \Config\Database::connect();
        $post = $this->request->getPost();
        $data = [
            'spt_id'          => $sptId,
            'waktu_rapat'     => $post['waktu_rapat']     ?: null,
            'waktu_sp'        => $post['waktu_sp']        ?: null,
            'rencana_laporan' => $post['rencana_laporan'] ?: null,
            'jabatan_auditi'  => $post['jabatan_auditi']  ?? null,
            'nama_auditi'     => $post['nama_auditi']     ?? null,
            'nip_auditi'      => $post['nip_auditi']      ?? null,
            'cp'              => $post['cp']              ?? null,
            'tlp_cp'          => $post['tlp_cp']          ?? null,
            'catatan'         => $post['catatan']         ?? null,
        ];

        $now = date('Y-m-d H:i:s');
        $existing = $db->table('spt_km6')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $db->table('spt_km6')->where('spt_id', $sptId)->update(array_merge($data, ['updated_at' => $now]));
        } else {
            $db->table('spt_km6')->insert(array_merge($data, ['created_at' => $now, 'updated_at' => $now]));
        }

        logActivity('spt.km5b.save', 'spt_km6', "Simpan KM-5b SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM-5b Entry Meeting berhasil disimpan.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Independensi  (AT isi milik sendiri; KT/Dalnis isi semua)
    // ──────────────────────────────────────────────────────────────────────

    public function independensi(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db      = \Config\Database::connect();
        $sdmId   = getCurrentSdmId();
        $isAdmin = isAuditAdmin();
        $isKtDal = isKtInSpt($sptId) || isDalnisInSpt($sptId);

        $query = $db->table('spt_independensi ind')
            ->select('ind.*, s.nama as sdm_nama, st.peran_spt')
            ->join('sdm s', 's.id = ind.sdm_id')
            ->join('spt_tim st', 'st.spt_id = ind.spt_id AND st.sdm_id = ind.sdm_id', 'left')
            ->where('ind.spt_id', $sptId);

        if (!$isAdmin && !$isKtDal && $sdmId) {
            $query->where('ind.sdm_id', $sdmId);
        }

        $indMap = array_column(
            $query->orderBy('st.urutan')->get()->getResultArray(),
            null, 'sdm_id'
        );

        // KT/Dalnis/Admin: semua tim; AT: hanya baris miliknya sendiri
        $timQuery = $db->table('spt_tim st')
            ->select('st.sdm_id, s.nama as sdm_nama, st.peran_spt, st.urutan')
            ->join('sdm s', 's.id = st.sdm_id')
            ->where('st.spt_id', $sptId);
        if (!$isAdmin && !$isKtDal && $sdmId) {
            $timQuery->where('st.sdm_id', $sdmId);
        }
        $timList = $timQuery->orderBy('st.urutan')->get()->getResultArray();

        return view('admin/km/independensi', [
            'title'    => 'Pernyataan Independensi & Integritas',
            'spt'      => $spt,
            'indMap'   => $indMap,
            'timList'  => $timList,
            'canEdit'  => canEditKmInSpt($sptId, 'independensi'),
            'isKtDal'  => $isAdmin || $isKtDal,
            'mySdmId'  => $sdmId,
        ]);
    }

    public function saveIndepensi(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'independensi')) return redirect()->back()->with('error', 'Akses ditolak.');

        $db      = \Config\Database::connect();
        $sdmId   = getCurrentSdmId();
        $isAdmin = isAuditAdmin();
        $isKtDal = isKtInSpt($sptId) || isDalnisInSpt($sptId);
        $now     = date('Y-m-d H:i:s');

        $postedIds  = (array)($this->request->getPost('sdm_id') ?? []);
        $allowedIds = ($isAdmin || $isKtDal) ? $postedIds : array_filter($postedIds, fn($id) => (int)$id === $sdmId);

        foreach ($allowedIds as $sid) {
            $sid = (int)$sid;
            if (!$sid) continue;

            $hubKeluarga   = (int)(bool)$this->request->getPost("hubungan_keluarga_{$sid}");
            $kepFinansial  = (int)(bool)$this->request->getPost("kepentingan_finansial_{$sid}");
            $hubSebelumnya = (int)(bool)$this->request->getPost("hubungan_sebelumnya_{$sid}");

            $data = [
                'spt_id'                    => $sptId,
                'sdm_id'                    => $sid,
                'ada_hubungan_keluarga'     => $hubKeluarga,
                'ada_kepentingan_finansial' => $kepFinansial,
                'ada_hubungan_sebelumnya'   => $hubSebelumnya,
                'is_independent'            => ($hubKeluarga || $kepFinansial || $hubSebelumnya) ? 0 : 1,
                'catatan'                   => $this->request->getPost("catatan_{$sid}"),
            ];

            $existing = $db->table('spt_independensi')
                ->where('spt_id', $sptId)->where('sdm_id', $sid)->get()->getRowArray();

            if ($existing) {
                $db->table('spt_independensi')
                    ->where('spt_id', $sptId)->where('sdm_id', $sid)
                    ->update(array_merge($data, ['updated_at' => $now]));
            } else {
                $db->table('spt_independensi')
                    ->insert(array_merge($data, ['created_at' => $now, 'updated_at' => $now]));
            }
        }

        logActivity('spt.ind.save', 'spt_independensi', "Simpan Independensi SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'Pernyataan Independensi berhasil disimpan.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // KM-10 — Exit Meeting  (KT + Dalnis)
    // ──────────────────────────────────────────────────────────────────────

    public function km10(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km10')->where('spt_id', $sptId)->get()->getRowArray();

        if (!$row) {
            $km5b = $db->table('spt_km6')->where('spt_id', $sptId)->get()->getRowArray();
            if ($km5b) $row = array_intersect_key($km5b, array_flip(['nama_auditi','jabatan_auditi','nip_auditi','cp','tlp_cp']));
        }

        return view('admin/km/km10', [
            'title'   => 'KM-10 — Exit Meeting',
            'spt'     => $spt,
            'row'     => $row,
            'canEdit' => canEditKmInSpt($sptId, 'km10'),
        ]);
    }

    public function saveKm10(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'km10')) return redirect()->back()->with('error', 'Hanya Ketua Tim atau Dalnis yang dapat mengisi KM-10.');

        $db   = \Config\Database::connect();
        $post = $this->request->getPost();
        $data = [
            'spt_id'         => $sptId,
            'waktu_meeting'  => $post['waktu_meeting']  ?: null,
            'nama_auditi'    => $post['nama_auditi']    ?? null,
            'jabatan_auditi' => $post['jabatan_auditi'] ?? null,
            'nip_auditi'     => $post['nip_auditi']     ?? null,
            'cp'             => $post['cp']             ?? null,
            'tlp_cp'         => $post['tlp_cp']         ?? null,
            'hasil_meeting'  => $post['hasil_meeting']  ?? null,
            'kesepakatan'    => $post['kesepakatan']    ?? null,
            'catatan'        => $post['catatan']        ?? null,
        ];

        $now = date('Y-m-d H:i:s');
        $existing = $db->table('spt_km10')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $db->table('spt_km10')->where('spt_id', $sptId)->update(array_merge($data, ['updated_at' => $now]));
        } else {
            $db->table('spt_km10')->insert(array_merge($data, ['created_at' => $now, 'updated_at' => $now]));
        }

        logActivity('spt.km10.save', 'spt_km10', "Simpan KM-10 SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM-10 Exit Meeting berhasil disimpan.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // KM-11 — Reviu Laporan  (Dalnis ONLY)
    // ──────────────────────────────────────────────────────────────────────

    public function km11(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km11')->where('spt_id', $sptId)->get()->getRowArray();

        return view('admin/km/km11', [
            'title'   => 'KM-11 — Reviu Laporan',
            'spt'     => $spt,
            'row'     => $row,
            'canEdit' => canEditKmInSpt($sptId, 'km11'),
        ]);
    }

    public function saveKm11(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'km11')) return redirect()->back()->with('error', 'Hanya Pengendali Teknis (Dalnis) yang dapat mengisi KM-11.');

        $db   = \Config\Database::connect();
        $post = $this->request->getPost();
        $data = [
            'spt_id'          => $sptId,
            'tanggal_reviu'   => $post['tanggal_reviu']   ?: null,
            'status'          => in_array($post['status'] ?? '', ['layak','revisi']) ? $post['status'] : 'layak',
            'catatan_reviu'   => $post['catatan_reviu']   ?? null,
            'saran_perbaikan' => $post['saran_perbaikan'] ?? null,
            'cek_sistematika' => isset($post['cek_sistematika']) ? 1 : 0,
            'cek_fakta'       => isset($post['cek_fakta'])       ? 1 : 0,
            'cek_rekomendasi' => isset($post['cek_rekomendasi']) ? 1 : 0,
            'cek_bahasa'      => isset($post['cek_bahasa'])      ? 1 : 0,
            'cek_lampiran'    => isset($post['cek_lampiran'])    ? 1 : 0,
        ];

        $now = date('Y-m-d H:i:s');
        $existing = $db->table('spt_km11')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $db->table('spt_km11')->where('spt_id', $sptId)->update(array_merge($data, ['updated_at' => $now]));
        } else {
            $db->table('spt_km11')->insert(array_merge($data, ['created_at' => $now, 'updated_at' => $now]));
        }

        logActivity('spt.km11.save', 'spt_km11', "Simpan KM-11 SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM-11 Reviu Laporan berhasil disimpan.');
    }
}
