<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PkptSettingModel;
use App\Models\HariLiburModel;

class PkptSettingController extends BaseController
{
    protected PkptSettingModel $settingModel;
    protected HariLiburModel   $hariLiburModel;

    public function __construct()
    {
        $this->settingModel   = new PkptSettingModel();
        $this->hariLiburModel = new HariLiburModel();
    }

    public function index()
    {
        $tahun    = (int)($this->request->getGet('tahun') ?? $this->settingModel->getTahunAktif());
        $settings = $this->settingModel->orderBy('tahun', 'DESC')->findAll();

        // Ringkasan HP untuk tahun aktif (dan semua tahun di list)
        $hpRingkasan = [];
        foreach ($settings as $s) {
            $hpRingkasan[$s['tahun']] = $this->hariLiburModel->getRingkasanTahun((int)$s['tahun']);
        }

        return view('admin/pkpt/setting', [
            'title'       => 'Header PKPT',
            'settings'    => $settings,
            'hpRingkasan' => $hpRingkasan,
            'tahun'       => $tahun,
            'statusLabel' => PkptSettingModel::$statusLabel,
            'statusColor' => PkptSettingModel::$statusColor,
        ]);
    }

    public function store()
    {
        $rules = [
            'tahun'   => 'required|integer|min_length[4]|max_length[4]',
            'tarif_hp'=> 'required|integer|greater_than[0]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $tahun    = (int)$this->request->getPost('tahun');
        $existing = $this->settingModel->where('tahun', $tahun)->first();

        // Hitung HP dari hari libur (dengan fallback manual jika diisi)
        $hpDinamis = $this->hariLiburModel->hitungHariKerjaTahun($tahun);
        $hpManual  = (int)($this->request->getPost('total_hp_tahunan') ?: 0);
        $totalHp   = $hpManual > 0 ? $hpManual : ($hpDinamis > 0 ? $hpDinamis : 360);

        $data = [
            'tahun'            => $tahun,
            'total_hp_tahunan' => $totalHp,
            'tarif_hp'         => (int)$this->request->getPost('tarif_hp'),
            'tanggal_pkpt'     => $this->request->getPost('tanggal_pkpt') ?: null,
            'nomor_pkpt'       => $this->request->getPost('nomor_pkpt') ?: null,
        ];

        if ($existing) {
            // Jangan reset status jika sudah disetujui
            if ($existing['status'] === 'disetujui') {
                $data['status'] = 'disetujui';
            }
            $this->settingModel->update($existing['id'], $data);
            $msg = 'Header PKPT tahun ' . $tahun . ' diperbarui.';
        } else {
            $data['status'] = 'draft';
            $this->settingModel->insert($data);
            $msg = 'Header PKPT tahun ' . $tahun . ' ditambahkan.';
        }

        logActivity('pkpt.setting', 'pkpt_setting', $msg);
        return redirect()->to('/admin/pkpt/setting')->with('success', $msg);
    }

    /** Setujui (tanda tangan Bupati) */
    public function approve(int $id)
    {
        $setting = $this->settingModel->find($id);
        if (!$setting) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        if (empty($setting['nomor_pkpt']) || empty($setting['tanggal_pkpt'])) {
            return redirect()->back()->with('error', 'Nomor dan Tanggal PKPT wajib diisi sebelum disetujui.');
        }

        $this->settingModel->approve($id, session()->get('user_id'));
        logActivity('pkpt.approve', 'pkpt_setting', "Setujui PKPT Header id=$id tahun={$setting['tahun']}");
        return redirect()->to('/admin/pkpt/setting')->with('success', 'Header PKPT tahun ' . $setting['tahun'] . ' telah disetujui.');
    }

    /** Batalkan persetujuan (kembalikan ke draft) */
    public function revok(int $id)
    {
        $setting = $this->settingModel->find($id);
        if (!$setting) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $this->settingModel->revokApproval($id);
        logActivity('pkpt.revok', 'pkpt_setting', "Revok PKPT Header id=$id");
        return redirect()->to('/admin/pkpt/setting')->with('success', 'Persetujuan dibatalkan, status kembali ke Draft.');
    }

    public function delete(int $id)
    {
        $this->settingModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }
}
