<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\IrbanModel;
use App\Models\SdmModel;
use App\Traits\DatatableTrait;

class MasterIrbanController extends BaseController
{
    use DatatableTrait;

    protected IrbanModel $irbanModel;
    protected SdmModel   $sdmModel;

    public function __construct()
    {
        $this->irbanModel = new IrbanModel();
        $this->sdmModel   = new SdmModel();
    }

    public function index()
    {
        return view('admin/master/irban/index', ['title' => 'Master Irban']);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw'=>$draw,'start'=>$start,'length'=>$length,'search'=>$search,'order'=>$order] = $this->dtRequest();

        $db = \Config\Database::connect();

        $baseQ = $db->table('irban i')
            ->select('i.*, s.nama as kepala_nama')
            ->join('sdm s', 's.id = i.kepala_sdm_id', 'left');

        $total = (clone $baseQ)->countAllResults(false);

        if ($search) {
            $baseQ->groupStart()
                ->like('i.kode', $search)
                ->orLike('i.nama', $search)
                ->orLike('s.nama', $search)
                ->groupEnd();
        }

        $filtered = $search ? (clone $baseQ)->countAllResults(false) : $total;

        [$ordCol, $ordDir] = $this->dtOrder($order, [2=>'i.kode', 3=>'i.nama', 4=>'s.nama'], 'i.kode');
        $rows = $baseQ->orderBy($ordCol, $ordDir)->limit($length, $start)->get()->getResultArray();

        $data = [];
        foreach ($rows as $i => $row) {
            $data[] = [
                'no'      => $start + $i + 1,
                'kode'    => '<span class="badge badge-primary">' . esc($row['kode']) . '</span>',
                'nama'    => esc($row['nama']),
                'kepala'  => esc($row['kepala_nama'] ?? '—'),
                'aksi'    => $this->dtActions([
                    ['type'=>'warning', 'icon'=>'fa-pen',   'title'=>'Edit',  'href'=>'/admin/master/irban/edit/'.$row['id']],
                    ['type'=>'danger',  'icon'=>'fa-trash', 'title'=>'Hapus', 'href'=>'/admin/master/irban/delete/'.$row['id'], 'ajax'=>true],
                ]),
            ];
        }

        return $this->dtResponse($draw, $total, $filtered, $data);
    }

    public function create()
    {
        return view('admin/master/irban/form', [
            'title' => 'Tambah Irban',
            'sdm'   => $this->sdmModel->getAktif(),
            'row'   => null,
        ]);
    }

    public function store()
    {
        $rules = [
            'kode' => 'required|max_length[20]',
            'nama' => 'required|max_length[100]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $this->irbanModel->insert([
            'kode'          => strtoupper($this->request->getPost('kode')),
            'nama'          => $this->request->getPost('nama'),
            'kepala_sdm_id' => $this->request->getPost('kepala_sdm_id') ?: null,
        ]);

        logActivity('master.irban.create', 'irban', 'Tambah irban: ' . $this->request->getPost('nama'));
        return redirect()->to('/admin/master/irban')->with('success', 'Irban berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $row = $this->irbanModel->find($id);
        if (!$row) return redirect()->to('/admin/master/irban')->with('error', 'Data tidak ditemukan.');

        return view('admin/master/irban/form', [
            'title' => 'Edit Irban',
            'sdm'   => $this->sdmModel->getAktif(),
            'row'   => $row,
        ]);
    }

    public function update(int $id)
    {
        $row = $this->irbanModel->find($id);
        if (!$row) return redirect()->to('/admin/master/irban')->with('error', 'Data tidak ditemukan.');

        $rules = [
            'kode' => 'required|max_length[20]',
            'nama' => 'required|max_length[100]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $this->irbanModel->update($id, [
            'kode'          => strtoupper($this->request->getPost('kode')),
            'nama'          => $this->request->getPost('nama'),
            'kepala_sdm_id' => $this->request->getPost('kepala_sdm_id') ?: null,
        ]);

        logActivity('master.irban.update', 'irban', 'Update irban: ' . $this->request->getPost('nama'));
        return redirect()->to('/admin/master/irban')->with('success', 'Irban berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $row = $this->irbanModel->find($id);
        if (!$row) return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);

        $this->irbanModel->delete($id);
        logActivity('master.irban.delete', 'irban', 'Hapus irban: ' . $row['nama']);
        return $this->response->setJSON(['success' => true]);
    }
}
