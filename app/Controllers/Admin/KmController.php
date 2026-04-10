<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SptModel;
use App\Models\SptKmModel;
use App\Models\SdmModel;

/**
 * Kelola Kendali Mutu (KM) untuk setiap SPT.
 * KM harus lengkap sebelum SPT dapat diajukan / disetujui.
 */
class KmController extends BaseController
{
    protected SptModel   $sptModel;
    protected SptKmModel $kmModel;
    protected SdmModel   $sdmModel;

    public function __construct()
    {
        $this->sptModel = new SptModel();
        $this->kmModel  = new SptKmModel();
        $this->sdmModel = new SdmModel();
    }

    // --------------------------------------------------
    // Checklist index
    // --------------------------------------------------

    public function index(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        return view('admin/km/index', [
            'title'     => 'Kendali Mutu — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'       => $spt,
            'checklist' => $this->kmModel->getChecklist($sptId),
        ]);
    }

    // --------------------------------------------------
    // KM1 — Kartu Penugasan
    // --------------------------------------------------

    public function km1(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km1')->where('spt_id', $sptId)->get()->getRowArray();

        return view('admin/km/km1', [
            'title' => 'KM1 — Kartu Penugasan',
            'spt'   => $spt,
            'row'   => $row,
        ]);
    }

    public function saveKm1(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $db = \Config\Database::connect();

        $data = [
            'spt_id'            => $sptId,
            'no_kartu'          => $this->request->getPost('no_kartu'),
            'tujuan_satker'     => $this->request->getPost('tujuan_satker'),
            'kegiatan'          => $this->request->getPost('kegiatan'),
            'rencana_mulai'     => $this->request->getPost('rencana_mulai') ?: null,
            'rencana_selesai'   => $this->request->getPost('rencana_selesai') ?: null,
            'rencana_kunjungan' => $this->request->getPost('rencana_kunjungan'),
            'catatan'           => $this->request->getPost('catatan'),
        ];

        $existing = $db->table('spt_km1')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km1')->where('spt_id', $sptId)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km1')->insert($data);
        }

        logActivity('spt.km1.save', 'spt_km1', "Simpan KM1 SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM1 Kartu Penugasan berhasil disimpan.');
    }

    // --------------------------------------------------
    // KM4 — Lembar Perencanaan Pengawasan
    // --------------------------------------------------

    public function km4(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km4')->where('spt_id', $sptId)->get()->getRowArray();

        return view('admin/km/km4', [
            'title' => 'KM4 — Lembar Perencanaan Pengawasan',
            'spt'   => $spt,
            'row'   => $row,
        ]);
    }

    public function saveKm4(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $post = $this->request->getPost();
        $db   = \Config\Database::connect();

        $data = [
            'spt_id'            => $sptId,
            'dasar_penugasan'   => $post['dasar_penugasan'] ?? null,
            'jenis_penugasan'   => $post['jenis_penugasan'] ?? null,
            'tujuan_pengawasan' => $post['tujuan_pengawasan'] ?? null,
            'sasaran'           => $post['sasaran'] ?? null,
            'cek_kp'            => isset($post['cek_kp']) ? 1 : 0,
            'cek_jenis_tujuan'  => isset($post['cek_jenis_tujuan']) ? 1 : 0,
            'cek_misi_tujuan'   => isset($post['cek_misi_tujuan']) ? 1 : 0,
            'cek_informasi'     => isset($post['cek_informasi']) ? 1 : 0,
            'cek_lhp_terakhir'  => isset($post['cek_lhp_terakhir']) ? 1 : 0,
            'cek_lhp_ekstern'   => isset($post['cek_lhp_ekstern']) ? 1 : 0,
            'cek_perundangan'   => isset($post['cek_perundangan']) ? 1 : 0,
            'cek_kertas_kerja'  => isset($post['cek_kertas_kerja']) ? 1 : 0,
            'cek_tao_fao'       => isset($post['cek_tao_fao']) ? 1 : 0,
            'cek_program'       => isset($post['cek_program']) ? 1 : 0,
            'cek_anggaran_waktu'=> isset($post['cek_anggaran_waktu']) ? 1 : 0,
            'catatan_dalnis'    => $post['catatan_dalnis'] ?? null,
        ];

        $existing = $db->table('spt_km4')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km4')->where('spt_id', $sptId)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km4')->insert($data);
        }

        logActivity('spt.km4.save', 'spt_km4', "Simpan KM4 SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM4 Lembar Perencanaan berhasil disimpan.');
    }

    // --------------------------------------------------
    // KM6 — Notulensi Kesepakatan
    // --------------------------------------------------

    public function km6(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km6')->where('spt_id', $sptId)->get()->getRowArray();

        return view('admin/km/km6', [
            'title' => 'KM6 — Notulensi Kesepakatan',
            'spt'   => $spt,
            'row'   => $row,
        ]);
    }

    public function saveKm6(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $db = \Config\Database::connect();

        $data = [
            'spt_id'          => $sptId,
            'waktu_rapat'     => $this->request->getPost('waktu_rapat') ?: null,
            'waktu_sp'        => $this->request->getPost('waktu_sp') ?: null,
            'rencana_laporan' => $this->request->getPost('rencana_laporan') ?: null,
            'jabatan_auditi'  => $this->request->getPost('jabatan_auditi'),
            'nama_auditi'     => $this->request->getPost('nama_auditi'),
            'nip_auditi'      => $this->request->getPost('nip_auditi'),
            'cp'              => $this->request->getPost('cp'),
            'tlp_cp'          => $this->request->getPost('tlp_cp'),
            'catatan'         => $this->request->getPost('catatan'),
        ];

        $existing = $db->table('spt_km6')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km6')->where('spt_id', $sptId)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km6')->insert($data);
        }

        logActivity('spt.km6.save', 'spt_km6', "Simpan KM6 SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM6 Notulensi Kesepakatan berhasil disimpan.');
    }

    // --------------------------------------------------
    // Anggaran Waktu
    // --------------------------------------------------

    public function anggaranWaktu(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        $db   = \Config\Database::connect();
        $rows = $db->table('spt_anggaran_waktu aw')
            ->select('aw.*, s.nama as sdm_nama, s.jabatan_struktural')
            ->join('sdm s', 's.id = aw.sdm_id')
            ->where('aw.spt_id', $sptId)
            ->get()->getResultArray();

        // Index by sdm_id for easy lookup in view
        $awMap = array_column($rows, null, 'sdm_id');

        return view('admin/km/anggaran_waktu', [
            'title'  => 'Formulir Anggaran Waktu',
            'spt'    => $spt,
            'awMap'  => $awMap,
        ]);
    }

    public function saveAnggaranWaktu(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $db     = \Config\Database::connect();
        $sdmIds = $this->request->getPost('sdm_id') ?? [];
        $now    = date('Y-m-d H:i:s');

        foreach ((array)$sdmIds as $sdmId) {
            $sdmId = (int)$sdmId;
            if (!$sdmId) continue;

            $data = [
                'spt_id'             => $sptId,
                'sdm_id'             => $sdmId,
                'persiapan_start'    => $this->request->getPost("persiapan_start_{$sdmId}") ?: null,
                'persiapan_end'      => $this->request->getPost("persiapan_end_{$sdmId}") ?: null,
                'pelaksanaan_start'  => $this->request->getPost("pelaksanaan_start_{$sdmId}") ?: null,
                'pelaksanaan_end'    => $this->request->getPost("pelaksanaan_end_{$sdmId}") ?: null,
                'penyelesaian_start' => $this->request->getPost("penyelesaian_start_{$sdmId}") ?: null,
                'penyelesaian_end'   => $this->request->getPost("penyelesaian_end_{$sdmId}") ?: null,
            ];

            $existing = $db->table('spt_anggaran_waktu')
                ->where('spt_id', $sptId)
                ->where('sdm_id', $sdmId)
                ->get()->getRowArray();

            if ($existing) {
                $data['updated_at'] = $now;
                $db->table('spt_anggaran_waktu')
                    ->where('spt_id', $sptId)
                    ->where('sdm_id', $sdmId)
                    ->update($data);
            } else {
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                $db->table('spt_anggaran_waktu')->insert($data);
            }
        }

        logActivity('spt.aw.save', 'spt_anggaran_waktu', "Simpan Anggaran Waktu SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'Formulir Anggaran Waktu berhasil disimpan.');
    }

    // --------------------------------------------------
    // Pernyataan Independensi
    // --------------------------------------------------

    public function independensi(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        $db   = \Config\Database::connect();
        $rows = $db->table('spt_independensi ind')
            ->select('ind.*, s.nama as sdm_nama')
            ->join('sdm s', 's.id = ind.sdm_id')
            ->where('ind.spt_id', $sptId)
            ->get()->getResultArray();

        $indMap = array_column($rows, null, 'sdm_id');

        return view('admin/km/independensi', [
            'title'  => 'Pernyataan Independensi & Integritas',
            'spt'    => $spt,
            'indMap' => $indMap,
        ]);
    }

    public function saveIndepensi(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $db     = \Config\Database::connect();
        $sdmIds = $this->request->getPost('sdm_id') ?? [];
        $now    = date('Y-m-d H:i:s');

        foreach ((array)$sdmIds as $sdmId) {
            $sdmId = (int)$sdmId;
            if (!$sdmId) continue;

            $hubKeluarga   = (int)(bool)$this->request->getPost("hubungan_keluarga_{$sdmId}");
            $kepFinansial  = (int)(bool)$this->request->getPost("kepentingan_finansial_{$sdmId}");
            $hubSebelumnya = (int)(bool)$this->request->getPost("hubungan_sebelumnya_{$sdmId}");
            $isIndependent = ($hubKeluarga || $kepFinansial || $hubSebelumnya) ? 0 : 1;

            $data = [
                'spt_id'                    => $sptId,
                'sdm_id'                    => $sdmId,
                'ada_hubungan_keluarga'     => $hubKeluarga,
                'ada_kepentingan_finansial' => $kepFinansial,
                'ada_hubungan_sebelumnya'   => $hubSebelumnya,
                'is_independent'            => $isIndependent,
                'catatan'                   => $this->request->getPost("catatan_{$sdmId}"),
            ];

            $existing = $db->table('spt_independensi')
                ->where('spt_id', $sptId)->where('sdm_id', $sdmId)
                ->get()->getRowArray();

            if ($existing) {
                $data['updated_at'] = $now;
                $db->table('spt_independensi')
                    ->where('spt_id', $sptId)->where('sdm_id', $sdmId)
                    ->update($data);
            } else {
                $data['created_at'] = $now;
                $data['updated_at'] = $now;
                $db->table('spt_independensi')->insert($data);
            }
        }

        logActivity('spt.ind.save', 'spt_independensi', "Simpan Independensi SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'Pernyataan Independensi berhasil disimpan.');
    }
}
