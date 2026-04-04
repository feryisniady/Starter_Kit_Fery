<?php

namespace App\Controllers\Admin;

date_default_timezone_set('Asia/Jakarta');

use App\Controllers\BaseController;
use App\Models\RoleModel;
use App\Models\PermissionModel;

class RoleController extends BaseController
{
    protected $roleModel;
    protected $permissionModel;

    public function __construct()
    {
        $this->roleModel       = new RoleModel();
        $this->permissionModel = new PermissionModel();
    }

    // List semua role
    public function index()
    {
        $data = [
            'title' => 'Manajemen Roles',
            'roles' => $this->roleModel->getRolesWithPermissions(),
        ];
        return view('admin/roles/index', $data);
    }

    // Form tambah role
    public function create()
    {
        $data = [
            'title'       => 'Tambah Role',
            'permissions' => $this->permissionModel->getGrouped(),
        ];
        return view('admin/roles/create', $data);
    }

    // Simpan role baru
    public function store()
    {
        $rules = [
            'name'        => 'required|min_length[3]|is_unique[roles.name]',
            'permissions' => 'required',
        ];

        $messages = [
            'name'        => [
                'required'   => 'Nama role wajib diisi.',
                'min_length' => 'Nama role minimal 3 karakter.',
                'is_unique'  => 'Nama role sudah ada.',
            ],
            'permissions' => [
                'required' => 'Pilih minimal 1 permission.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            // Gabungkan semua pesan error menjadi list dengan baris baru (<br>)
            $errorString = implode('<br>', $this->validator->getErrors());
            
            return redirect()->back()->withInput()
                ->with('error', $errorString); // Kita gunakan key 'error' agar ditangkap layout
        }

        $roleId = $this->roleModel->insert([
            'name' => strtolower($this->request->getPost('name')),
        ]);

        $permissionIds = $this->request->getPost('permissions') ?? [];
        $this->roleModel->syncPermissions($roleId, $permissionIds);

        return redirect()->to('/admin/roles')->with('success', 'Role berhasil ditambahkan!');
    }

    // Form edit role
    public function edit(int $id)
    {
        $role = $this->roleModel->find($id);
        if (!$role) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Role tidak ditemukan.");
        }

        $data = [
            'title'           => 'Edit Role',
            'role'            => $role,
            'permissions'     => $this->permissionModel->getGrouped(),
            'rolePermissions' => array_column($this->roleModel->getRolePermissions($id), 'id'),
        ];
        return view('admin/roles/edit', $data);
    }

    // Update role
    public function update(int $id)
    {
        $rules = [
            'name'        => "required|min_length[3]|is_unique[roles.name,id,{$id}]",
            'permissions' => 'required',
        ];

        $messages = [
            'name'        => [
                'required'   => 'Nama role wajib diisi.',
                'min_length' => 'Nama role minimal 3 karakter.',
                'is_unique'  => 'Nama role sudah ada.',
            ],
            'permissions' => [
                'required' => 'Pilih minimal 1 permission.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            // Gabungkan semua pesan error menjadi list dengan baris baru (<br>)
            $errorString = implode('<br>', $this->validator->getErrors());
            
            return redirect()->back()->withInput()
                ->with('error', $errorString); // Kita gunakan key 'error' agar ditangkap layout
        }

        $this->roleModel->update($id, [
            'name' => strtolower($this->request->getPost('name')),
        ]);

        $permissionIds = $this->request->getPost('permissions') ?? [];
        $this->roleModel->syncPermissions($id, $permissionIds);

        return redirect()->to('/admin/roles')->with('success', 'Role berhasil diupdate!');
    }

    // Hapus role
    public function delete(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/roles');
        }

        $this->roleModel->delete($id);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Role berhasil dihapus!'
        ]);
    }
}