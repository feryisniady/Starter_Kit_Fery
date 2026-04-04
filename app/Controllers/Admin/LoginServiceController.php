<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LoginServiceModel;

class LoginServiceController extends BaseController
{
    protected LoginServiceModel $model;

    public function __construct()
    {
        $this->model = new LoginServiceModel();
    }

    public function index()
    {
        return view('admin/login_services/index', [
            'title'    => 'Daftar Layanan Login',
            'services' => $this->model->orderBy('sort_order')->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'name'       => 'required|max_length[150]',
            'url'        => 'permit_empty|max_length[255]',
            'icon'       => 'permit_empty|max_length[100]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $maxSort = $this->model->selectMax('sort_order')->first()['sort_order'] ?? 0;

        $this->model->insert([
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'url'           => $this->request->getPost('url'),
            'icon'          => $this->request->getPost('icon') ?: 'fa-solid fa-globe',
            'require_login' => $this->request->getPost('require_login') ? 1 : 0,
            'sort_order'    => (int)$maxSort + 1,
            'is_active'     => 1,
        ]);

        logActivity('service.create', 'login_services', 'Layanan login baru ditambahkan: ' . $this->request->getPost('name'));
        return redirect()->to('/admin/login-services')->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function update(int $id)
    {
        $svc = $this->model->find($id);
        if (!$svc) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $this->model->update($id, [
            'name'          => $this->request->getPost('name'),
            'description'   => $this->request->getPost('description'),
            'url'           => $this->request->getPost('url'),
            'icon'          => $this->request->getPost('icon') ?: 'fa-solid fa-globe',
            'require_login' => $this->request->getPost('require_login') ? 1 : 0,
            'is_active'     => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        logActivity('service.update', 'login_services', 'Layanan login diperbarui: ' . $this->request->getPost('name'));
        return redirect()->to('/admin/login-services')->with('success', 'Layanan berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $svc = $this->model->find($id);
        if (!$svc) return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan.']);

        $this->model->delete($id);
        logActivity('service.delete', 'login_services', 'Layanan login dihapus: ' . $svc['name']);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Layanan berhasil dihapus.']);
    }

    public function toggleActive(int $id)
    {
        $svc = $this->model->find($id);
        if (!$svc) return $this->response->setJSON(['status' => 'error']);

        $this->model->update($id, ['is_active' => $svc['is_active'] ? 0 : 1]);
        return $this->response->setJSON(['status' => 'success', 'is_active' => !$svc['is_active']]);
    }
}
