<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\IrbanModel;
use App\Traits\DatatableTrait;

class UserController extends BaseController
{
    use DatatableTrait;

    protected $userModel;
    protected $roleModel;
    protected IrbanModel $irbanModel;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->roleModel  = new RoleModel();
        $this->irbanModel = new IrbanModel();
    }

    // List semua user
    public function index()
    {
        return view('admin/users/index', ['title' => 'Manajemen Users']);
    }

    // AJAX: DataTables server-side
    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw'=>$draw,'start'=>$start,'length'=>$length,'search'=>$search,'order'=>$order] = $this->dtRequest();

        $db = \Config\Database::connect();

        // Total (tanpa filter)
        $total = $db->table('users')->countAllResults();

        // Filtered count
        $countQ = $db->table('users u')
            ->select('u.id')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->groupBy('u.id');
        if ($search) {
            $countQ->groupStart()->like('u.name', $search)->orLike('u.email', $search)->groupEnd();
        }
        $filtered = $search ? count($countQ->get()->getResultArray()) : $total;

        // Data query
        $dataQ = $db->table('users u')
            ->select('u.id, u.name, u.email, u.status, u.created_at, GROUP_CONCAT(r.name SEPARATOR ", ") as roles')
            ->join('user_roles ur', 'ur.user_id = u.id', 'left')
            ->join('roles r', 'r.id = ur.role_id', 'left')
            ->groupBy('u.id');
        if ($search) {
            $dataQ->groupStart()->like('u.name', $search)->orLike('u.email', $search)->groupEnd();
        }

        [$ordCol, $ordDir] = $this->dtOrder($order, [
            0 => 'u.id', 1 => 'u.name', 2 => 'u.email', 3 => 'u.status',
        ], 'u.id');
        $dataQ->orderBy($ordCol, $ordDir);
        if ($length > 0) $dataQ->limit($length, $start);

        $rows = $dataQ->get()->getResultArray();
        $data = [];
        foreach ($rows as $i => $row) {
            $roleBadges = '';
            if ($row['roles']) {
                foreach (explode(',', $row['roles']) as $r) {
                    $roleBadges .= '<span class="badge badge-primary">'.esc(trim($r)).'</span> ';
                }
            } else {
                $roleBadges = '<span class="text-muted">-</span>';
            }
            $statusBadge = '<span class="badge badge-'.($row['status']==='active'?'success':'danger').'">'.esc($row['status']).'</span>';

            $actions = $this->dtActions([
                ['show'=>hasPermission('user.edit'),   'type'=>'info',   'icon'=>'fa-pen',   'title'=>'Edit',  'href'=>'/admin/users/edit/'.$row['id']],
                ['show'=>hasPermission('user.delete'), 'type'=>'danger', 'icon'=>'fa-trash', 'title'=>'Hapus', 'href'=>'/admin/users/delete/'.$row['id'], 'ajax'=>true],
            ]);

            $data[] = [
                'no'     => $start + $i + 1,
                'nama'   => esc($row['name']),
                'email'  => esc($row['email']),
                'role'   => $roleBadges,
                'status' => $statusBadge,
                'aksi'   => $actions,
            ];
        }

        return $this->dtResponse($draw, $total, $filtered, $data);
    }

    // Form tambah user
    public function create()
    {
        return view('admin/users/create', [
            'title'    => 'Tambah User',
            'roles'    => $this->roleModel->findAll(),
            'irbanList'=> $this->irbanModel->findAll(),
        ]);
    }

    // Simpan user baru
    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'roles'    => 'required',
        ];

        $messages = [
            'name'     => ['required' => 'Nama wajib diisi.', 'min_length' => 'Nama minimal 3 karakter.'],
            'email'    => ['required' => 'Email wajib diisi.', 'valid_email' => 'Format email tidak valid.', 'is_unique' => 'Email sudah digunakan.'],
            'password' => ['required' => 'Password wajib diisi.', 'min_length' => 'Password minimal 8 karakter.'],
            'roles'    => ['required' => 'Pilih minimal 1 role.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $name   = $this->request->getPost('name');
        $email  = $this->request->getPost('email');
        $status = $this->request->getPost('status') ?? 'active';

        $userId = $this->userModel->insert([
            'name'     => $name,
            'email'    => $email,
            'status'   => $status,
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        $this->userModel->syncRoles($userId, $this->request->getPost('roles') ?? []);

        // Auto-create SDM record (bisa dilengkapi NIP/jabatan nanti)
        $this->syncSdm($userId, $name, [
            'nip'                => $this->request->getPost('nip'),
            'jabatan_fungsional' => $this->request->getPost('jabatan_fungsional'),
            'jabatan_struktural' => $this->request->getPost('jabatan_struktural'),
            'pangkat_golongan'   => $this->request->getPost('pangkat_golongan'),
            'irban_id'           => $this->request->getPost('irban_id') ?: null,
        ]);

        logActivity('user.create', 'user', "Tambah user baru: {$name} ({$email})");
        return redirect()->to('/admin/users')->with('success', 'User berhasil ditambahkan dan data SDM otomatis terdaftar.');
    }

    // Form edit user
    public function edit(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("User tidak ditemukan.");
        }

        $db  = \Config\Database::connect();
        $sdm = $db->table('sdm')->where('user_id', $id)->get()->getRowArray();

        return view('admin/users/edit', [
            'title'     => 'Edit User',
            'user'      => $user,
            'sdm'       => $sdm,
            'roles'     => $this->roleModel->findAll(),
            'userRoles' => array_column($this->userModel->getUserRoles($id), 'id'),
            'irbanList' => $this->irbanModel->findAll(),
        ]);
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
            'name'  => ['required' => 'Nama wajib diisi.', 'min_length' => 'Nama minimal 3 karakter.'],
            'email' => ['required' => 'Email wajib diisi.', 'valid_email' => 'Format email tidak valid.', 'is_unique' => 'Email sudah digunakan.'],
            'roles' => ['required' => 'Pilih minimal 1 role.'],
        ];

        if (!$this->validate($rules, $messages)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $name   = $this->request->getPost('name');
        $email  = $this->request->getPost('email');
        $status = $this->request->getPost('status');

        $dataUpdate = ['name' => $name, 'email' => $email, 'status' => $status];
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            if (strlen($password) < 8) {
                return redirect()->back()->withInput()->with('error', 'Password minimal 8 karakter.');
            }
            $dataUpdate['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->userModel->update($id, $dataUpdate);
        $this->userModel->syncRoles($id, $this->request->getPost('roles') ?? []);

        // Sync SDM record (buat jika belum ada, update jika sudah)
        $this->syncSdm($id, $name, [
            'nip'                => $this->request->getPost('nip'),
            'jabatan_fungsional' => $this->request->getPost('jabatan_fungsional'),
            'jabatan_struktural' => $this->request->getPost('jabatan_struktural'),
            'pangkat_golongan'   => $this->request->getPost('pangkat_golongan'),
            'irban_id'           => $this->request->getPost('irban_id') ?: null,
        ]);

        logActivity('user.update', 'user', "Update user ID:{$id} — {$name}");
        return redirect()->to('/admin/users')->with('success', 'User berhasil diupdate!');
    }

    // ===================================================
    // HELPER — Sync SDM
    // ===================================================

    /**
     * Buat atau update record SDM yang terhubung ke user ini.
     * Dipanggil otomatis saat store() dan update().
     */
    private function syncSdm(int $userId, string $nama, array $kepegawaian = []): void
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $existing = $db->table('sdm')->where('user_id', $userId)->get()->getRowArray();

        $data = array_filter([
            'nip'                => $kepegawaian['nip']                ?: null,
            'jabatan_fungsional' => $kepegawaian['jabatan_fungsional'] ?: null,
            'jabatan_struktural' => $kepegawaian['jabatan_struktural'] ?: null,
            'pangkat_golongan'   => $kepegawaian['pangkat_golongan']   ?: null,
            'irban_id'           => $kepegawaian['irban_id']           ?: null,
        ], fn($v) => $v !== null);

        if ($existing) {
            // Selalu update nama agar sinkron dengan users.name
            $db->table('sdm')->where('user_id', $userId)->update(array_merge(
                ['nama' => $nama, 'updated_at' => $now],
                $data
            ));
        } else {
            $db->table('sdm')->insert(array_merge(
                ['user_id' => $userId, 'nama' => $nama, 'aktif' => 1, 'created_at' => $now, 'updated_at' => $now],
                $data
            ));
        }
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