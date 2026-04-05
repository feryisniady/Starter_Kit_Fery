<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RoleModel;
use App\Models\PermissionModel;
use App\Traits\DatatableTrait;

class RoleController extends BaseController
{
    use DatatableTrait;

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
        return view('admin/roles/index', ['title' => 'Manajemen Roles']);
    }

    // AJAX: DataTables server-side
    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw'=>$draw,'start'=>$start,'length'=>$length,'search'=>$search,'order'=>$order] = $this->dtRequest();

        $db = \Config\Database::connect();

        $total = $db->table('roles')->countAllResults();

        $countQ = $db->table('roles r')->select('r.id')->join('role_permissions rp', 'rp.role_id = r.id', 'left')->groupBy('r.id');
        if ($search) $countQ->like('r.name', $search);
        $filtered = $search ? count($countQ->get()->getResultArray()) : $total;

        $dataQ = $db->table('roles r')
            ->select('r.id, r.name, COUNT(rp.permission_id) as total_permissions')
            ->join('role_permissions rp', 'rp.role_id = r.id', 'left')
            ->groupBy('r.id');
        if ($search) $dataQ->like('r.name', $search);

        [$ordCol, $ordDir] = $this->dtOrder($order, [0=>'r.id', 1=>'r.name', 2=>'total_permissions'], 'r.id');
        $dataQ->orderBy($ordCol, $ordDir);
        if ($length > 0) $dataQ->limit($length, $start);

        $rows = $dataQ->get()->getResultArray();
        $data = [];
        foreach ($rows as $i => $row) {
            $actions = $this->dtActions([
                ['show'=>hasPermission('role.edit'),   'type'=>'info',   'icon'=>'fa-pen',   'title'=>'Edit',  'href'=>'/admin/roles/edit/'.$row['id']],
                ['show'=>hasPermission('role.delete'), 'type'=>'danger', 'icon'=>'fa-trash', 'title'=>'Hapus', 'href'=>'/admin/roles/delete/'.$row['id'], 'ajax'=>true],
            ]);
            $data[] = [
                'no'          => $start + $i + 1,
                'nama_role'   => '<span class="badge badge-primary">'.esc($row['name']).'</span>',
                'total_perms' => $row['total_permissions'].' permission',
                'aksi'        => $actions,
            ];
        }

        return $this->dtResponse($draw, $total, $filtered, $data);
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

        logActivity('role.create', 'role', "Tambah role baru: {$this->request->getPost('name')}", null, null, [
            'after' => ['name' => $this->request->getPost('name')],
        ]);

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
        $oldRole = $this->roleModel->find($id);
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

        logActivity('role.update', 'role', "Update role ID:{$id} — {$this->request->getPost('name')}", null, null, [
            'before' => ['name' => $oldRole['name'] ?? ''],
            'after'  => ['name' => $this->request->getPost('name')],
        ]);

        return redirect()->to('/admin/roles')->with('success', 'Role berhasil diupdate!');
    }

    // Hapus role
    public function delete(int $id)
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin/roles');
        }

        $role = $this->roleModel->find($id);
        $this->roleModel->delete($id);

        logActivity('role.delete', 'role', "Hapus role ID:{$id}" . ($role ? " — {$role['name']}" : ''), null, null, [
            'before' => $role ? ['name' => $role['name']] : [],
        ]);

        return $this->response->setJSON([
            'status'  => 'success',
            'message' => 'Role berhasil dihapus!'
        ]);
    }
}