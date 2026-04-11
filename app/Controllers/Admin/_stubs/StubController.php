<?php
/**
 * ============================================================
 * STUB CONTROLLER — copy ke app/Controllers/Admin/{NamaModul}Controller.php
 * Cari-ganti: StubController → {NamaModul}Controller
 *             StubModel      → {NamaModul}Model
 *             stub           → {resource_key}  (misal: irban, kegiatan)
 *             /admin/stub    → /admin/{url_resource}
 *             stub.          → {modul}.         (prefix logActivity & permission)
 * ============================================================
 */

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\StubModel;          // ← ganti model
use App\Traits\DatatableTrait;

class StubController extends BaseController
{
    use DatatableTrait;

    protected StubModel $model;    // ← ganti tipe

    public function __construct()
    {
        $this->model = new StubModel();
    }

    // =========================================================
    // INDEX — render halaman dengan DataTable auto-init
    // =========================================================

    public function index()
    {
        // Cek permission jika perlu:
        // if (!hasPermission('stub.view')) return redirect()->to('/admin')->with('error', 'Akses ditolak.');

        return view('admin/stub/index', [    // ← ganti path view
            'title' => 'Judul Modul',
        ]);
    }

    // =========================================================
    // GET DATA — endpoint AJAX untuk DataTables (POST)
    // =========================================================

    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search] = $this->dtRequest();

        $db    = \Config\Database::connect();
        $total = $db->table('stub')->countAllResults();  // ← ganti tabel

        $q = $db->table('stub s')
            ->select('s.*')
            // ->join('tabel_lain t', 't.id = s.foreign_id', 'left')
            ->orderBy('s.nama');

        if ($search) {
            $q->groupStart()
                ->like('s.nama', $search)
                // ->orLike('s.keterangan', $search)
              ->groupEnd();
        }

        $filtered = $search ? $q->countAllResults(false) : $total;
        $rows     = $q->limit($length, $start)->get()->getResultArray();

        $sl = StubModel::$statusLabel;  // ← ganti model
        $sc = StubModel::$statusColor;  // ← ganti model

        $data = [];
        foreach ($rows as $i => $r) {
            $data[] = [
                'no'          => $start + $i + 1,
                'nama'        => esc($r['nama']),
                // 'kolom_lain' => esc($r['kolom_lain'] ?: '—'),
                'status'      => '<span class="badge badge-' . ($sc[$r['status']] ?? 'secondary') . '">'
                               . esc($sl[$r['status']] ?? $r['status']) . '</span>',
                'aksi'        => '<button class="btn btn-xs btn-primary  btn-detail" data-id="' . $r['id'] . '" title="Detail"><i class="fas fa-eye"></i></button> '
                               . '<button class="btn btn-xs btn-warning  btn-edit"   data-id="' . $r['id'] . '" title="Edit"><i class="fas fa-edit"></i></button> '
                               . '<button class="btn btn-xs btn-danger   btn-delete" data-id="' . $r['id'] . '" title="Hapus"><i class="fas fa-trash"></i></button>',
            ];
        }

        return $this->dtResponse($draw, $total, $filtered, $data);
    }

    // =========================================================
    // SHOW — AJAX: ambil satu record untuk isi form edit
    // =========================================================

    public function show(int $id)
    {
        $row = $this->model->find($id);
        return $this->response->setJSON($row ?: ['error' => 'Data tidak ditemukan.']);
    }

    // =========================================================
    // STORE — AJAX: simpan record baru
    // =========================================================

    public function store()
    {
        $rules = [
            'nama'        => 'required|max_length[150]',
            // 'kolom_lain' => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $id = $this->model->insert([
            'nama'       => $this->request->getPost('nama'),
            // 'keterangan' => $this->request->getPost('keterangan'),
            'status'     => 'aktif',
            'aktif'      => 1,
            'created_by' => session()->get('user_id'),
        ]);

        logActivity('stub.create', 'stub', 'Tambah: ' . $this->request->getPost('nama'));
        return $this->response->setJSON(['success' => true, 'id' => $id]);
    }

    // =========================================================
    // UPDATE — AJAX: update record
    // =========================================================

    public function update(int $id)
    {
        $row = $this->model->find($id);
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        }

        $rules = ['nama' => 'required|max_length[150]'];
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $this->model->update($id, [
            'nama'       => $this->request->getPost('nama'),
            // 'keterangan' => $this->request->getPost('keterangan'),
            'status'     => $this->request->getPost('status') ?? $row['status'],
            'aktif'      => (int)$this->request->getPost('aktif'),
        ]);

        logActivity('stub.update', 'stub', 'Update: ' . $this->request->getPost('nama'));
        return $this->response->setJSON(['success' => true]);
    }

    // =========================================================
    // DELETE — AJAX: hapus record
    // =========================================================

    public function delete(int $id)
    {
        $row = $this->model->find($id);
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        }

        $this->model->delete($id);

        logActivity('stub.delete', 'stub', 'Hapus: ' . $row['nama']);
        return $this->response->setJSON(['success' => true]);
    }

    // =========================================================
    // HELPERS (hapus jika modul tidak butuh scoping per irban)
    // =========================================================

    private function isAdmin(): bool
    {
        return hasRole('superadmin') || hasRole('admin') || hasPermission('stub.manage_all');
    }
}
