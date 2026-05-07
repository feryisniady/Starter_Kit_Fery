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

    // AJAX: DataTables server-side
    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw'=>$draw,'start'=>$start,'length'=>$length,'search'=>$search,'order'=>$order] = $this->dtRequest();

        $db = \Config\Database::connect();

        $baseQ = $db->table('irban i')
            ->select('i.id, i.kode, i.nama, s.nama as kepala_nama')
            ->join('sdm s', 's.id = i.kepala_sdm_id', 'left');

        $total = $db->table('irban')->countAllResults();

        $countQ = clone $baseQ;
        if ($search) {
            $countQ->groupStart()->like('i.kode', $search)->orLike('i.nama', $search)->groupEnd();
        }
        $filtered = $search ? count($countQ->get()->getResultArray()) : $total;

        $dataQ = clone $baseQ;
        if ($search) {
            $dataQ->groupStart()->like('i.kode', $search)->orLike('i.nama', $search)->groupEnd();
        }

        [$ordCol, $ordDir] = $this->dtOrder($order, [0=>'i.id', 1=>'i.kode', 2=>'i.nama', 3=>'s.nama'], 'i.kode');
        $dataQ->orderBy($ordCol, $ordDir);
        if ($length > 0) $dataQ->limit($length, $start);

        $rows = $dataQ->get()->getResultArray();
        $data = [];
        foreach ($rows as $i => $row) {
            $initial = strtoupper(mb_substr($row['nama'], 0, 1));
            $kepala = $row['kepala_nama']
                ? '<div style="display:flex;align-items:center;gap:8px">'
                  . '<div style="width:30px;height:30px;border-radius:50%;background:#e0e7ff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#6366f1;flex-shrink:0">' . $initial . '</div>'
                  . '<span>' . esc($row['kepala_nama']) . '</span></div>'
                : '<span style="color:#94a3b8;font-style:italic">— Belum diisi —</span>';

            $kodeBadge = '<span style="background:#ede9fe;color:#6d28d9;padding:3px 10px;border-radius:6px;font-size:12px;font-weight:700;letter-spacing:.5px">'
                       . esc($row['kode']) . '</span>';

            $actions = $this->dtActions([
                ['show' => hasPermission('master.edit'),   'type' => 'warning', 'icon' => 'fa-pen',   'title' => 'Edit',  'href' => '/admin/master/irban/edit/' . $row['id']],
                ['show' => hasPermission('master.delete'), 'type' => 'danger',  'icon' => 'fa-trash', 'title' => 'Hapus',
                    'extra' => 'data-id="' . $row['id'] . '" data-nama="' . esc($row['nama']) . '"', 'ajax' => false,
                    'href' => '#',
                ],
            ]);
            // Override aksi — tombol hapus butuh JS SweetAlert, bukan default btn-delete AJAX
            $actions = '<div style="display:flex;gap:4px;align-items:center">'
                . '<a href="/admin/master/irban/edit/' . $row['id'] . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-pen"></i></a>'
                . '<button class="btn btn-sm btn-danger btn-del" data-id="' . $row['id'] . '" data-nama="' . esc($row['nama']) . '" title="Hapus"><i class="fas fa-trash"></i></button>'
                . '</div>';

            $data[] = [
                'no'     => $start + $i + 1,
                'kode'   => $kodeBadge,
                'nama'   => '<span style="font-weight:600;color:#1e293b">' . esc($row['nama']) . '</span>',
                'kepala' => $kepala,
                'aksi'   => $actions,
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
