<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\IrbanModel;
use App\Models\SdmModel;

class MasterIrbanController extends BaseController
{
    protected IrbanModel $irbanModel;
    protected SdmModel   $sdmModel;

    public function __construct()
    {
        $this->irbanModel = new IrbanModel();
        $this->sdmModel   = new SdmModel();
    }

    public function index()
    {
        return view('admin/master/irban/index', [
            'title' => 'Master Irban',
            'data'  => $this->irbanModel->withKepala(),
        ]);
    }

    public function create()
    {
        return view('admin/master/irban/form', [
            'title' => 'Tambah Irban',
            'sdm'   => $this->sdmModel->getAktif(),
            'row'   => null,
        ]);
    }

    public function store()
    {
        $rules = [
            'kode' => 'required|max_length[20]',
            'nama' => 'required|max_length[100]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $this->irbanModel->insert([
            'kode'          => strtoupper($this->request->getPost('kode')),
            'nama'          => $this->request->getPost('nama'),
            'kepala_sdm_id' => $this->request->getPost('kepala_sdm_id') ?: null,
        ]);

        logActivity('master.irban.create', 'irban', 'Tambah irban: ' . $this->request->getPost('nama'));
        return redirect()->to('/admin/master/irban')->with('success', 'Irban berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $row = $this->irbanModel->find($id);
        if (!$row) return redirect()->to('/admin/master/irban')->with('error', 'Data tidak ditemukan.');

        return view('admin/master/irban/form', [
            'title' => 'Edit Irban',
            'sdm'   => $this->sdmModel->getAktif(),
            'row'   => $row,
        ]);
    }

    public function update(int $id)
    {
        $row = $this->irbanModel->find($id);
        if (!$row) return redirect()->to('/admin/master/irban')->with('error', 'Data tidak ditemukan.');

        $rules = [
            'kode' => 'required|max_length[20]',
            'nama' => 'required|max_length[100]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $this->irbanModel->update($id, [
            'kode'          => strtoupper($this->request->getPost('kode')),
            'nama'          => $this->request->getPost('nama'),
            'kepala_sdm_id' => $this->request->getPost('kepala_sdm_id') ?: null,
        ]);

        logActivity('master.irban.update', 'irban', 'Update irban: ' . $this->request->getPost('nama'));
        return redirect()->to('/admin/master/irban')->with('success', 'Irban berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $row = $this->irbanModel->find($id);
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);

        $this->irbanModel->delete($id);
        logActivity('master.irban.delete', 'irban', 'Hapus irban: ' . $row['nama']);
        return $this->response->setJSON(['success' => true]);
    }
}
