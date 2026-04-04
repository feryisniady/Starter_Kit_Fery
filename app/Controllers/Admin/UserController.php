<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;



class UserController extends BaseController
{
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    // List semua user
    public function index()
    {
        $data = [
            'title' => 'Manajemen Users',
            'users' => $this->userModel->getUsersWithRoles(),
        ];
        return view('admin/users/index', $data);
    }

    // Form tambah user
    public function create()
    {
        $data = [
            'title' => 'Tambah User',
            'roles' => $this->roleModel->findAll(),
        ];
        return view('admin/users/create', $data);
    }

    // Simpan user baru
    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'roles'    => 'required',
        ];

        $messages = [
            'name'     => [
                'required'   => 'Nama wajib diisi.',
                'min_length' => 'Nama minimal 3 karakter.',
            ],
            'email'    => [
                'required'    => 'Email wajib diisi.',
                'valid_email' => 'Format email tidak valid.',
                'is_unique'   => 'Email sudah digunakan.',
            ],
            'password' => [
                'required'   => 'Password wajib diisi.',
                'min_length' => 'Password minimal 6 karakter.',
            ],
            'roles'    => [
                'required' => 'Pilih minimal 1 role.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            // Gabungkan semua pesan error menjadi list dengan baris baru (<br>)
            $errorString = implode('<br>', $this->validator->getErrors());
            
            return redirect()->back()->withInput()
                ->with('error', $errorString); // Kita gunakan key 'error' agar ditangkap layout
        }

        $newData = [
            'name'   => $this->request->getPost('name'),
            'email'  => $this->request->getPost('email'),
            'status' => $this->request->getPost('status') ?? 'active',
        ];

        $userId = $this->userModel->insert(array_merge($newData, [
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ]));

        $roleIds = $this->request->getPost('roles') ?? [];
        $this->userModel->syncRoles($userId, $roleIds);

        logActivity('user.create', 'user', "Tambah user baru: {$newData['name']} ({$newData['email']})", null, null, [
            'after' => $newData,
        ]);

        return redirect()->to('/admin/users')->with('success', 'User berhasil ditambahkan!');
    }

    // Form edit user
    public function edit(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("User tidak ditemukan.");
        }

        $data = [
            'title'      => 'Edit User',
            'user'       => $user,
            'roles'      => $this->roleModel->findAll(),
            'userRoles'  => array_column($this->userModel->getUserRoles($id), 'id'),
        ];
        return view('admin/users/edit', $data);
    }

    // Update user
    public function update(int $id)
    {
        $oldUser = $this->userModel->find($id);
        $rules = [
            'name'  => 'required|min_length[3]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'roles' => 'required',
        ];

        $messages = [
            'name'  => [
                'required'   => 'Nama wajib diisi.',
                'min_length' => 'Nama minimal 3 karakter.',
            ],
            'email' => [
                'required'    => 'Email wajib diisi.',
                'valid_email' => 'Format email tidak valid.',
                'is_unique'   => 'Email sudah digunakan.',
            ],
            'roles' => [
                'required' => 'Pilih minimal 1 role.',
            ],
        ];

        if (!$this->validate($rules, $messages)) {
            // Gabungkan semua pesan error menjadi list dengan baris baru (<br>)
            $errorString = implode('<br>', $this->validator->getErrors());
            
            return redirect()->back()->withInput()
                ->with('error', $errorString); // Kita gunakan key 'error' agar ditangkap layout
        }

        $dataUpdate = [
            'name'   => $this->request->getPost('name'),
            'email'  => $this->request->getPost('email'),
            'status' => $this->request->getPost('status'),
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            if (strlen($password) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password minimal 6 karakter.');
            }
            $dataUpdate['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $dataUpdate);
        $roleIds = $this->request->getPost('roles') ?? [];
        $this->userModel->syncRoles($id, $roleIds);

        logActivity('user.update', 'user', "Update user ID:{$id} — {$dataUpdate['name']}", null, null, [
            'before' => ['name' => $oldUser['name'], 'email' => $oldUser['email'], 'status' => $oldUser['status']],
            'after'  => ['name' => $dataUpdate['name'], 'email' => $dataUpdate['email'], 'status' => $dataUpdate['status']],
        ]);

        return redirect()->to('/admin/users')->with('success', 'User berhasil diupdate!');
    }

    // Hapus user
    public function delete(int $id)
    {
        // Cek apakah request AJAX
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/users');
        }

        $user = $this->userModel->find($id);
        $this->userModel->delete($id);

        logActivity('user.delete', 'user', "Hapus user ID:{$id}" . ($user ? " — {$user['name']}" : ''), null, null, [
            'before' => $user ? ['name' => $user['name'], 'email' => $user['email'], 'status' => $user['status']] : [],
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'User berhasil dihapus!'
        ]);
    }
}