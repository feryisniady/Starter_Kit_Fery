<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LoginServiceModel;
use App\Traits\DatatableTrait;

class LoginServiceController extends BaseController
{
    use DatatableTrait;

    protected LoginServiceModel $model;

    public function __construct()
    {
        $this->model = new LoginServiceModel();
    }

    public function index()
    {
        return view('admin/login_services/index', ['title' => 'Daftar Layanan']);
    }

    // AJAX: DataTables server-side
    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw'=>$draw,'start'=>$start,'length'=>$length,'search'=>$search,'order'=>$order] = $this->dtRequest();

        $db    = \Config\Database::connect();
        $total = $db->table('login_services')->countAllResults();

        $countQ = $db->table('login_services');
        if ($search) $countQ->groupStart()->like('name', $search)->orLike('description', $search)->groupEnd();
        $filtered = $search ? $countQ->countAllResults() : $total;

        $dataQ = $db->table('login_services');
        if ($search) $dataQ->groupStart()->like('name', $search)->orLike('description', $search)->groupEnd();

        [$ordCol, $ordDir] = $this->dtOrder($order, [0=>'id',1=>'name',2=>'sort_order',3=>'is_active'], 'sort_order');
        $dataQ->orderBy($ordCol, $ordDir);
        if ($length > 0) $dataQ->limit($length, $start);

        $rows = $dataQ->get()->getResultArray();
        $data = [];
        foreach ($rows as $i => $row) {
            $icon    = $row['icon'] ? '<i class="'.esc($row['icon']).'" style="font-size:18px;color:#6366f1"></i>' : '-';
            $login   = $row['require_login'] ? '<span class="badge badge-warning">Login</span>' : '<span class="badge badge-info">Publik</span>';
            $status  = '<span class="badge '.($row['is_active']?'badge-success':'badge-gray').'">'.($row['is_active']?'Aktif':'Nonaktif').'</span>';
            $h = fn($v) => htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
            $editExtra = 'data-id="'.$row['id'].'"'
                .' data-name="'.$h($row['name']).'"'
                .' data-description="'.$h($row['description']).'"'
                .' data-url2="'.$h($row['url']).'"'
                .' data-icon="'.$h($row['icon']).'"'
                .' data-require_login="'.(int)$row['require_login'].'"'
                .' data-is_active="'.(int)$row['is_active'].'"'
                .' onclick="openEditFromDT(this)"';
            $actions = $this->dtActions([
                ['show'=>true, 'type'=>'secondary', 'icon'=>'fa-pen',   'title'=>'Edit',  'href'=>'#', 'extra'=>$editExtra],
                ['show'=>true, 'type'=>'danger',    'icon'=>'fa-trash', 'title'=>'Hapus', 'href'=>'/admin/login-services/delete/'.$row['id'], 'ajax'=>true],
            ]);
            $data[] = [
                'no'       => $start + $i + 1,
                'icon'     => $icon,
                'nama'     => esc($row['name']),
                'deskripsi'=> esc($row['description']),
                'url'      => '<span style="font-size:12px;color:#94a3b8">'.esc($row['url']).'</span>',
                'login'    => $login,
                'status'   => $status,
                'aksi'     => $actions,
            ];
        }

        return $this->dtResponse($draw, $total, $filtered, $data);
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
