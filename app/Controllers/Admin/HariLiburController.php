<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HariLiburModel;
use App\Models\PkptSettingModel;

class HariLiburController extends BaseController
{
    protected HariLiburModel    $model;
    protected PkptSettingModel  $settingModel;

    public function __construct()
    {
        $this->model        = new HariLiburModel();
        $this->settingModel = new PkptSettingModel();
    }

    public function index()
    {
        $tahun    = (int)($this->request->getGet('tahun') ?? date('Y'));
        $settings = $this->settingModel->orderBy('tahun', 'DESC')->findAll();

        return view('admin/pkpt/hari_libur', [
            'title'     => 'Hari Libur & Hari Kerja',
            'tahun'     => $tahun,
            'settings'  => $settings,
            'libur'     => $this->model->getByTahun($tahun),
            'ringkasan' => $this->model->getRingkasanTahun($tahun),
        ]);
    }

    public function store()
    {
        $rules = [
            'tanggal'    => 'required|valid_date[Y-m-d]',
            'keterangan' => 'required|max_length[150]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $tanggal = $this->request->getPost('tanggal');
        $tahun   = (int)date('Y', strtotime($tanggal));

        // Cek duplikat
        $existing = $this->model->where('tanggal', $tanggal)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Tanggal ' . $tanggal . ' sudah terdaftar.');
        }

        $this->model->insert([
            'tahun'      => $tahun,
            'tanggal'    => $tanggal,
            'keterangan' => $this->request->getPost('keterangan'),
        ]);

        logActivity('hari_libur.create', 'hari_libur', "Tambah hari libur: $tanggal");
        return redirect()->to('/admin/pkpt/hari-libur?tahun=' . $tahun)->with('success', 'Hari libur ditambahkan.');
    }

    /** Batch insert dari array tanggal (untuk import) */
    public function storeBatch()
    {
        $rows = $this->request->getPost('rows') ?? [];
        $tahun = (int)($this->request->getPost('tahun') ?? date('Y'));
        $count = 0;

        foreach ((array)$rows as $row) {
            if (empty($row['tanggal']) || empty($row['keterangan'])) continue;
            $existing = $this->model->where('tanggal', $row['tanggal'])->first();
            if ($existing) continue;
            $this->model->insert([
                'tahun'      => $tahun,
                'tanggal'    => $row['tanggal'],
                'keterangan' => $row['keterangan'],
            ]);
            $count++;
        }

        return $this->response->setJSON(['success' => true, 'count' => $count]);
    }

    public function delete(int $id)
    {
        $row = $this->model->find($id);
        if (!$row) return $this->response->setJSON(['success' => false]);

        $this->model->delete($id);
        logActivity('hari_libur.delete', 'hari_libur', "Hapus hari libur id=$id");
        return $this->response->setJSON(['success' => true]);
    }

    /** AJAX: hitung HP kerja untuk tahun tertentu */
    public function hitungHp()
    {
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));
        return $this->response->setJSON($this->model->getRingkasanTahun($tahun));
    }
}
