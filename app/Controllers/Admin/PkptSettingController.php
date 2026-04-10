<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PkptSettingModel;

class PkptSettingController extends BaseController
{
    protected PkptSettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new PkptSettingModel();
    }

    public function index()
    {
        return view('admin/pkpt/setting', [
            'title'    => 'Setting PKPT',
            'settings' => $this->settingModel->orderBy('tahun', 'DESC')->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'tahun'            => 'required|integer|min_length[4]|max_length[4]',
            'total_hp_tahunan' => 'required|integer|greater_than[0]',
            'tarif_hp'         => 'required|integer|greater_than[0]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $tahun    = (int)$this->request->getPost('tahun');
        $existing = $this->settingModel->where('tahun', $tahun)->first();

        $data = [
            'tahun'            => $tahun,
            'total_hp_tahunan' => (int)$this->request->getPost('total_hp_tahunan'),
            'tarif_hp'         => (int)$this->request->getPost('tarif_hp'),
            'tanggal_pkpt'     => $this->request->getPost('tanggal_pkpt') ?: null,
            'nomor_pkpt'       => $this->request->getPost('nomor_pkpt') ?: null,
        ];

        if ($existing) {
            $this->settingModel->update($existing['id'], $data);
            $msg = 'Setting PKPT tahun ' . $tahun . ' diperbarui.';
        } else {
            $this->settingModel->insert($data);
            $msg = 'Setting PKPT tahun ' . $tahun . ' ditambahkan.';
        }

        logActivity('pkpt.setting', 'pkpt_setting', $msg);
        return redirect()->to('/admin/pkpt/setting')->with('success', $msg);
    }

    public function delete(int $id)
    {
        $this->settingModel->delete($id);
        return $this->response->setJSON(['success' => true]);
    }
}
