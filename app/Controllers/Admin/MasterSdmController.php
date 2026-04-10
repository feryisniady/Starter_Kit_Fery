<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SdmModel;
use App\Models\IrbanModel;
use App\Models\UserModel;
use App\Traits\DatatableTrait;

class MasterSdmController extends BaseController
{
    use DatatableTrait;

    protected SdmModel   $sdmModel;
    protected IrbanModel $irbanModel;

    public function __construct()
    {
        $this->sdmModel   = new SdmModel();
        $this->irbanModel = new IrbanModel();
    }

    public function index()
    {
        return view('admin/master/sdm/index', [
            'title' => 'Master SDM Pengawas',
            'irban' => $this->irbanModel->getDropdown(),
        ]);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search] = $this->dtRequest();

        $db    = \Config\Database::connect();
        $total = $db->table('sdm')->countAllResults();

        $q = $db->table('sdm s')
            ->select('s.*, i.nama as irban_nama')
            ->join('irban i', 'i.id = s.irban_id', 'left')
            ->orderBy('i.kode, s.nama');

        if ($search) {
            $q->groupStart()->like('s.nama', $search)->orLike('s.nip', $search)->groupEnd();
        }
        $filtered = $search ? $q->countAllResults(false) : $total;
        $rows     = $q->limit($length, $start)->get()->getResultArray();

        $data = array_map(fn($r) => [
            'nip'                => esc($r['nip']),
            'nama'               => esc($r['nama']),
            'jabatan_struktural' => esc($r['jabatan_struktural']),
            'pangkat_golongan'   => esc($r['pangkat_golongan']),
            'irban'              => esc($r['irban_nama'] ?? '-'),
            'aktif'              => $r['aktif']
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-secondary">Nonaktif</span>',
            'aksi' => '<button class="btn btn-xs btn-warning btn-edit" data-id="' . $r['id'] . '">Edit</button>
                       <button class="btn btn-xs btn-danger btn-delete" data-id="' . $r['id'] . '">Hapus</button>',
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
        $rules = [
            'nama' => 'required|max_length[150]',
            'nip'  => 'permit_empty|max_length[30]',
        ];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'message' => implode(', ', $this->validator->getErrors())]);
        }

        $id = $this->sdmModel->insert([
            'nip'                 => $this->request->getPost('nip'),
            'nama'                => $this->request->getPost('nama'),
            'pangkat_golongan'    => $this->request->getPost('pangkat_golongan'),
            'jabatan_struktural'  => $this->request->getPost('jabatan_struktural'),
            'jabatan_fungsional'  => $this->request->getPost('jabatan_fungsional'),
            'irban_id'            => $this->request->getPost('irban_id') ?: null,
            'aktif'               => 1,
        ]);

        logActivity('master.sdm.create', 'sdm', 'Tambah SDM: ' . $this->request->getPost('nama'));
        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    public function show(int $id)
    {
        return $this->response->setJSON($this->sdmModel->find($id) ?: ['error' => 'Not found']);
    }

    public function update(int $id)
    {
        $rules = ['nama' => 'required|max_length[150]'];
        if (!$this->validate($rules)) {
            return $this->response->setJSON(['success' => false, 'message' => implode(', ', $this->validator->getErrors())]);
        }

        $this->sdmModel->update($id, [
            'nip'                 => $this->request->getPost('nip'),
            'nama'                => $this->request->getPost('nama'),
            'pangkat_golongan'    => $this->request->getPost('pangkat_golongan'),
            'jabatan_struktural'  => $this->request->getPost('jabatan_struktural'),
            'jabatan_fungsional'  => $this->request->getPost('jabatan_fungsional'),
            'irban_id'            => $this->request->getPost('irban_id') ?: null,
            'aktif'               => (int)$this->request->getPost('aktif'),
        ]);

        logActivity('master.sdm.update', 'sdm', 'Update SDM: ' . $this->request->getPost('nama'));
        return $this->response->setJSON(['success' => true]);
    }

    public function delete(int $id)
    {
        $row = $this->sdmModel->find($id);
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);

        $this->sdmModel->delete($id);
        logActivity('master.sdm.delete', 'sdm', 'Hapus SDM: ' . $row['nama']);
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * AJAX: cek sisa HP SDM untuk tahun tertentu.
     */
    public function sisaHp(int $sdmId)
    {
        $tahun   = (int)($this->request->getGet('tahun') ?? date('Y'));
        $sisaHp  = $this->sdmModel->getSisaHp($sdmId, $tahun);
        return $this->response->setJSON(['sisa_hp' => $sisaHp]);
    }
}
