<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SptModel;
use App\Models\SptKmModel;
use App\Models\SdmModel;
use App\Models\KkaModel;

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
    // KM3 — Dokumen SPT (auto-prefill, read-only)
    // --------------------------------------------------

    public function km3(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        return view('admin/km/km3', [
            'title' => 'KM-3 — Dokumen SPT',
            'spt'   => $spt,
        ]);
    }

    // --------------------------------------------------
    // KM5b — Entry Meeting (dulu KM6)
    // --------------------------------------------------

    public function km5b(int $sptId)
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

    public function saveKm5b(int $sptId)
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
    // KM5 — Reviu PKA
    // --------------------------------------------------

    public function km5(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km5')->where('spt_id', $sptId)->get()->getRowArray();

        // Ambil PKA untuk ditampilkan
        $pkaList = $db->table('pka')->where('spt_id', $sptId)->get()->getResultArray();

        return view('admin/km/km5', [
            'title'   => 'KM-5 — Reviu PKA',
            'spt'     => $spt,
            'row'     => $row,
            'pkaList' => $pkaList,
        ]);
    }

    public function saveKm5(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $db   = \Config\Database::connect();
        $post = $this->request->getPost();

        $data = [
            'spt_id'           => $sptId,
            'tanggal_reviu'    => $post['tanggal_reviu'] ?: null,
            'status'           => in_array($post['status'] ?? '', ['disetujui', 'dikembalikan']) ? $post['status'] : 'disetujui',
            'catatan_reviu'    => $post['catatan_reviu'] ?? null,
            'saran_perbaikan'  => $post['saran_perbaikan'] ?? null,
            'cek_tujuan'       => isset($post['cek_tujuan']) ? 1 : 0,
            'cek_sasaran'      => isset($post['cek_sasaran']) ? 1 : 0,
            'cek_ruang_lingkup'=> isset($post['cek_ruang_lingkup']) ? 1 : 0,
            'cek_metodologi'   => isset($post['cek_metodologi']) ? 1 : 0,
            'cek_tim'          => isset($post['cek_tim']) ? 1 : 0,
            'cek_waktu'        => isset($post['cek_waktu']) ? 1 : 0,
        ];

        $existing = $db->table('spt_km5')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km5')->where('spt_id', $sptId)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km5')->insert($data);
        }

        // ── Jika PKA disetujui → auto-create KKA per AT ─────────────────
        $msg = 'KM-5 Reviu PKA berhasil disimpan.';
        if ($data['status'] === 'disetujui') {
            $created = (new KkaModel())->autoCreateForSpt($sptId);
            if ($created > 0) {
                $msg .= " KKA otomatis dibuat untuk {$created} anggota tim.";
            }
        }

        logActivity('spt.km5.save', 'spt_km5', "Simpan KM5 Reviu PKA SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', $msg);
    }

    // --------------------------------------------------
    // KM10 — Exit Meeting
    // --------------------------------------------------

    public function km10(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km10')->where('spt_id', $sptId)->get()->getRowArray();

        // Prefill nama/jabatan auditi dari KM-5b jika belum ada
        if (!$row) {
            $km5b = $db->table('spt_km6')->where('spt_id', $sptId)->get()->getRowArray();
            $row  = $km5b ? [
                'nama_auditi'    => $km5b['nama_auditi'] ?? '',
                'jabatan_auditi' => $km5b['jabatan_auditi'] ?? '',
                'nip_auditi'     => $km5b['nip_auditi'] ?? '',
                'cp'             => $km5b['cp'] ?? '',
                'tlp_cp'         => $km5b['tlp_cp'] ?? '',
            ] : null;
        }

        return view('admin/km/km10', [
            'title' => 'KM-10 — Exit Meeting',
            'spt'   => $spt,
            'row'   => $row,
        ]);
    }

    public function saveKm10(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $db   = \Config\Database::connect();
        $post = $this->request->getPost();

        $data = [
            'spt_id'         => $sptId,
            'waktu_meeting'  => $post['waktu_meeting'] ?: null,
            'nama_auditi'    => $post['nama_auditi'] ?? null,
            'jabatan_auditi' => $post['jabatan_auditi'] ?? null,
            'nip_auditi'     => $post['nip_auditi'] ?? null,
            'cp'             => $post['cp'] ?? null,
            'tlp_cp'         => $post['tlp_cp'] ?? null,
            'hasil_meeting'  => $post['hasil_meeting'] ?? null,
            'kesepakatan'    => $post['kesepakatan'] ?? null,
            'catatan'        => $post['catatan'] ?? null,
        ];

        $existing = $db->table('spt_km10')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km10')->where('spt_id', $sptId)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km10')->insert($data);
        }

        logActivity('spt.km10.save', 'spt_km10', "Simpan KM10 Exit Meeting SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM-10 Exit Meeting berhasil disimpan.');
    }

    // --------------------------------------------------
    // KM11 — Reviu Laporan
    // --------------------------------------------------

    public function km11(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        $db  = \Config\Database::connect();
        $row = $db->table('spt_km11')->where('spt_id', $sptId)->get()->getRowArray();

        return view('admin/km/km11', [
            'title' => 'KM-11 — Reviu Laporan',
            'spt'   => $spt,
            'row'   => $row,
        ]);
    }

    public function saveKm11(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $db   = \Config\Database::connect();
        $post = $this->request->getPost();

        $data = [
            'spt_id'          => $sptId,
            'tanggal_reviu'   => $post['tanggal_reviu'] ?: null,
            'status'          => in_array($post['status'] ?? '', ['layak', 'revisi']) ? $post['status'] : 'layak',
            'catatan_reviu'   => $post['catatan_reviu'] ?? null,
            'saran_perbaikan' => $post['saran_perbaikan'] ?? null,
            'cek_sistematika' => isset($post['cek_sistematika']) ? 1 : 0,
            'cek_fakta'       => isset($post['cek_fakta']) ? 1 : 0,
            'cek_rekomendasi' => isset($post['cek_rekomendasi']) ? 1 : 0,
            'cek_bahasa'      => isset($post['cek_bahasa']) ? 1 : 0,
            'cek_lampiran'    => isset($post['cek_lampiran']) ? 1 : 0,
        ];

        $existing = $db->table('spt_km11')->where('spt_id', $sptId)->get()->getRowArray();
        if ($existing) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km11')->where('spt_id', $sptId)->update($data);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $db->table('spt_km11')->insert($data);
        }

        logActivity('spt.km11.save', 'spt_km11', "Simpan KM11 Reviu Laporan SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/km')->with('success', 'KM-11 Reviu Laporan berhasil disimpan.');
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
