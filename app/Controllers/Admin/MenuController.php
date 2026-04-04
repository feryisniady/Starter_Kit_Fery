<?php

namespace App\Controllers\Admin;

date_default_timezone_set('Asia/Jakarta');

use App\Controllers\BaseController;
use App\Models\MenuModel;
use App\Models\PermissionModel;

class MenuController extends BaseController
{
    protected $menuModel;
    protected $permissionModel;

    public function __construct()
    {
        $this->menuModel       = new MenuModel();
        $this->permissionModel = new PermissionModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Menu',
            'menus' => $this->menuModel->orderBy('sort_order')->findAll(),
        ];
        return view('admin/menus/index', $data);
    }

    public function create()
    {
        $data = [
            'title'       => 'Tambah Menu',
            'permissions' => $this->permissionModel->findAll(),
            'parents'     => $this->menuModel->findAll(),
        ];
        return view('admin/menus/create', $data);
    }

    public function store()
    {

        $rules = [
            'label' => 'required|min_length[2]|max_length[100]',
            'icon'  => 'required',
            // URL wajib hanya kalau bukan parent (tidak punya parent_id)
            'url'   => 'permit_empty|max_length[200]',
        ];

        if (!$this->validate($rules)) {
            // Gabungkan semua pesan error menjadi list dengan baris baru (<br>)
            $errorString = implode('<br>', $this->validator->getErrors());
            
            return redirect()->back()->withInput()
                ->with('error', $errorString); // Kita gunakan key 'error' agar ditangkap layout
        }

        $parentId = $this->request->getPost('parent_id') ?: null;

        // Kalau parent (tidak punya parent_id), URL = '#'
        // Kalau child, URL wajib diisi
        $url = $this->request->getPost('url');
        if (empty($parentId) && empty($url)) {
        // Parent tanpa URL → jadikan container '#'
            $url = '#';
        } elseif (!empty($parentId) && empty($url)) {
            return redirect()->back()->withInput()
            ->with('errors', ['url' => 'URL wajib diisi untuk sub menu!']);
        }

        $this->menuModel->insert([
            'label'      => $this->request->getPost('label'),
            'url'        => $url,
            'icon'       => $this->request->getPost('icon'),
            'permission' => $this->request->getPost('permission') ?: null,
            'parent_id'  => $parentId,
            'sort_order' => $this->request->getPost('sort_order') ?? 0,
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
            'section'    => $this->request->getPost('section') ?: 'main',
        ]);

        // Auto generate permission
        if ($this->request->getPost('auto_permission')) {
            $actions = $this->request->getPost('permission_actions') ?? [];
            if (!empty($actions)) {
                $prefix = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_',
                    trim($this->request->getPost('label'))));
                $prefix = trim($prefix, '_');
                foreach ($actions as $action) {
                    $name = $prefix . '.' . $action;
                    if (!$this->permissionModel->where('name', $name)->first()) {
                        $this->permissionModel->insert(['name' => $name]);
                    }
                }
            }
        }

        return redirect()->to('/admin/menus')
        ->with('success', 'Menu berhasil ditambahkan!');
    }


    public function edit(int $id)
    {
        $data = [
            'title'       => 'Edit Menu',
            'menu'        => $this->menuModel->find($id),
            'permissions' => $this->permissionModel->findAll(),
            'parents'     => $this->menuModel->where('id !=', $id)->findAll(),
        ];
        return view('admin/menus/edit', $data);
    }

    public function update(int $id)
    {
        $parentId = $this->request->getPost('parent_id') ?: null;
        $url      = $this->request->getPost('url');

        if (empty($parentId) && empty($url)) {
            $url = '#';
        } elseif (!empty($parentId) && empty($url)) {
            return redirect()->back()->withInput()
            ->with('errors', ['url' => 'URL wajib diisi untuk sub menu!']);
        }

        $rules = ['label' => 'required|min_length[2]|max_length[100]'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
            ->with('errors', $this->validator->getErrors());
        }

        $this->menuModel->update($id, [
            'label'      => $this->request->getPost('label'),
            'url'        => $url,
            'icon'       => $this->request->getPost('icon'),
            'permission' => $this->request->getPost('permission') ?: null,
            'parent_id'  => $parentId,
            'sort_order' => $this->request->getPost('sort_order') ?? 0,
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
            'section'    => $this->request->getPost('section') ?: 'main',
        ]);

        return redirect()->to('/admin/menus')
        ->with('success', 'Menu berhasil diupdate!');
    }

    public function delete(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/menus');
        }

        $this->menuModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Menu berhasil dihapus!'
        ]);
    }
}