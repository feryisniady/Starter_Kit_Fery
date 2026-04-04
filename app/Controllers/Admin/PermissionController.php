<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PermissionModel;

class PermissionController extends BaseController
{
    protected $permissionModel;

    public function __construct()
    {
        $this->permissionModel = new PermissionModel();
    }

    public function index()
    {
        // Group permission berdasarkan prefix
        $permissions = $this->permissionModel->orderBy('name')->findAll();
        $grouped = [];
        foreach ($permissions as $perm) {
            $prefix = explode('.', $perm['name'])[0];
            $grouped[$prefix][] = $perm;
        }

        return view('admin/permissions/index', [
            'title'       => 'Manajemen Permission',
            'permissions' => $permissions,
            'grouped'     => $grouped,
        ]);
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/permissions');
        }

        $name = strtolower(trim($this->request->getPost('name')));

        // Validasi format: harus berformat "prefix.action"
        if (!preg_match('/^[a-z_]+\.[a-z_]+$/', $name)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Format permission harus: prefix.action (contoh: user.view)'
            ]);
        }

        // Cek duplikat
        $exists = $this->permissionModel->where('name', $name)->first();
        if ($exists) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Permission ' . $name . ' sudah ada!'
            ]);
        }

        $this->permissionModel->insert(['name' => $name]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Permission berhasil ditambahkan!',
            'data'    => ['name' => $name]
        ]);
    }

    public function storeBatch()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/permissions');
        }

        $prefix  = strtolower(trim($this->request->getPost('prefix')));
        $actions = $this->request->getPost('actions') ?? [];

        if (empty($prefix) || empty($actions)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Prefix dan aksi wajib diisi!'
            ]);
        }

        if (!preg_match('/^[a-z_]+$/', $prefix)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Prefix hanya boleh huruf kecil dan underscore!'
            ]);
        }

        $created = [];
        $skipped = [];

        foreach ($actions as $action) {
            $name   = $prefix . '.' . $action;
            $exists = $this->permissionModel->where('name', $name)->first();
            if ($exists) {
                $skipped[] = $name;
            } else {
                $this->permissionModel->insert(['name' => $name]);
                $created[] = $name;
            }
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => count($created) . ' permission berhasil dibuat!',
            'created'  => $created,
            'skipped'  => $skipped,
        ]);
    }

    public function delete(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/permissions');
        }

        // Cek apakah permission dipakai di role_permissions
        $db    = \Config\Database::connect();
        $inUse = $db->table('role_permissions')
        ->where('permission_id', $id)
        ->countAllResults();

        if ($inUse > 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Permission ini sedang digunakan oleh ' . $inUse . ' role, tidak bisa dihapus!'
            ]);
        }

        $this->permissionModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Permission berhasil dihapus!'
        ]);
    }
}