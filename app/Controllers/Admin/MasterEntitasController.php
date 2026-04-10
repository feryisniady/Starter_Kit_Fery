<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EntitasModel;
use App\Traits\DatatableTrait;

class MasterEntitasController extends BaseController
{
    use DatatableTrait;

    protected EntitasModel $entitasModel;

    public function __construct()
    {
        $this->entitasModel = new EntitasModel();
    }

    public function index()
    {
        return view('admin/master/entitas/index', ['title' => 'Master Entitas / OPD']);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search] = $this->dtRequest();

        $db    = \Config\Database::connect();
        $total = $db->table('entitas')->countAllResults();

        $q = $db->table('entitas')->orderBy('nama');
        if ($search) $q->like('nama', $search)->orLike('kode', $search);
        $filtered = $search ? $q->countAllResults(false) : $total;

        $rows = $q->limit($length, $start)->get()->getResultArray();

        $data = array_map(fn($r) => [
            'kode'   => esc($r['kode']),
            'nama'   => esc($r['nama']),
            'kepala' => esc($r['kepala']),
            'aktif'  => $r['aktif']
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-secondary">Nonaktif</span>',
            'aksi'   => '<div style="display:flex;gap:4px;align-items:center">
                <button class="btn btn-sm btn-warning btn-edit" data-id="'.$r['id'].'" title="Edit"><i class="fas fa-pen"></i></button>
                <button class="btn btn-sm btn-danger btn-delete" data-url="/admin/master/entitas/delete/'.$r['id'].'" title="Hapus"><i class="fas fa-trash"></i></button>
                </div>',
        ], $rows);

        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    public function store()
    {
        $rules = ['nama' => 'required|max_length[200]'];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'message' => implode(', ', $this->validator->getErrors())]);
        }

        $id = $this->entitasModel->insert([
            'kode'   => $this->request->getPost('kode'),
            'nama'   => $this->request->getPost('nama'),
            'alamat' => $this->request->getPost('alamat'),
            'kepala' => $this->request->getPost('kepala'),
            'aktif'  => 1,
        ]);

        logActivity('master.entitas.create', 'entitas', 'Tambah entitas: ' . $this->request->getPost('nama'));
        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    public function show(int $id)
    {
        $row = $this->entitasModel->find($id);
        return $this->response->setJSON($row ?: ['error' => 'Not found']);
    }

    public function update(int $id)
    {
        $rules = ['nama' => 'required|max_length[200]'];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'message' => implode(', ', $this->validator->getErrors())]);
        }

        $this->entitasModel->update($id, [
            'kode'   => $this->request->getPost('kode'),
            'nama'   => $this->request->getPost('nama'),
            'alamat' => $this->request->getPost('alamat'),
            'kepala' => $this->request->getPost('kepala'),
            'aktif'  => (int)$this->request->getPost('aktif'),
        ]);

        logActivity('master.entitas.update', 'entitas', 'Update entitas: ' . $this->request->getPost('nama'));
        return $this->response->setJSON(['success' => true]);
    }

    public function delete(int $id)
    {
        $row = $this->entitasModel->find($id);
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);

        $this->entitasModel->delete($id);
        logActivity('master.entitas.delete', 'entitas', 'Hapus entitas: ' . $row['nama']);
        return $this->response->setJSON(['success' => true]);
    }
}
