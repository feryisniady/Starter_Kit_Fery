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
    protected UserModel  $userModel;

    public function __construct()
    {
        $this->sdmModel   = new SdmModel();
        $this->irbanModel = new IrbanModel();
        $this->userModel  = new UserModel();
    }

    public function index()
    {
        // Users yang belum di-link ke SDM manapun
        $db        = \Config\Database::connect();
        $linkedIds = array_column(
            $db->table('sdm')->select('user_id')->where('user_id IS NOT NULL')->get()->getResultArray(),
            'user_id'
        );
        $users = $this->userModel->select('id, username, name')
            ->when($linkedIds, fn($q) => $q->whereNotIn('id', $linkedIds))
            ->orderBy('name')
            ->findAll();

        return view('admin/master/sdm/index', [
            'title' => 'Master SDM Pengawas',
            'irban' => $this->irbanModel->getDropdown(),
            'users' => $users,
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

        $userId = (int)$this->request->getPost('user_id') ?: null;
        // Pastikan user_id tidak sudah dipakai SDM lain
        if ($userId) {
            $existing = $this->sdmModel->where('user_id', $userId)->first();
            if ($existing) {
                return $this->response->setJSON(['success' => false, 'message' => 'Akun user tersebut sudah terhubung ke SDM lain.']);
            }
        }

        $id = $this->sdmModel->insert([
            'nip'                 => $this->request->getPost('nip'),
            'nama'                => $this->request->getPost('nama'),
            'pangkat_golongan'    => $this->request->getPost('pangkat_golongan'),
            'jabatan_struktural'  => $this->request->getPost('jabatan_struktural'),
            'jabatan_fungsional'  => $this->request->getPost('jabatan_fungsional'),
            'irban_id'            => $this->request->getPost('irban_id') ?: null,
            'user_id'             => $userId,
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

        $userId = (int)$this->request->getPost('user_id') ?: null;
        // Pastikan user_id tidak dipakai SDM lain (kecuali SDM ini sendiri)
        if ($userId) {
            $existing = $this->sdmModel->where('user_id', $userId)->where('id !=', $id)->first();
            if ($existing) {
                return $this->response->setJSON(['success' => false, 'message' => 'Akun user tersebut sudah terhubung ke SDM lain.']);
            }
        }

        $this->sdmModel->update($id, [
            'nip'                 => $this->request->getPost('nip'),
            'nama'                => $this->request->getPost('nama'),
            'pangkat_golongan'    => $this->request->getPost('pangkat_golongan'),
            'jabatan_struktural'  => $this->request->getPost('jabatan_struktural'),
            'jabatan_fungsional'  => $this->request->getPost('jabatan_fungsional'),
            'irban_id'            => $this->request->getPost('irban_id') ?: null,
            'user_id'             => $userId,
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
