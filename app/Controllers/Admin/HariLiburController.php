<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HariLiburModel;
use App\Models\PkptSettingModel;
use App\Traits\DatatableTrait;

class HariLiburController extends BaseController
{
    use DatatableTrait;
    protected HariLiburModel    $model;
    protected PkptSettingModel  $settingModel;

    public function __construct()
    {
        $this->model        = new HariLiburModel();
        $this->settingModel = new PkptSettingModel();
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search] = $this->dtRequest();

        $tahun = (int)($this->request->getPost('tahun') ?? date('Y'));
        $db    = \Config\Database::connect();

        $total = $db->table('hari_libur')->where('tahun', $tahun)->countAllResults();

        $q = $db->table('hari_libur')
            ->where('tahun', $tahun)
            ->orderBy('tanggal', 'ASC');

        if ($search) {
            $q->like('keterangan', $search);
        }
        $filtered = $search ? $q->countAllResults(false) : $total;
        $rows     = $q->limit($length, $start)->get()->getResultArray();

        $hariNama = ['1'=>'Senin','2'=>'Selasa','3'=>'Rabu','4'=>'Kamis','5'=>'Jumat','6'=>'Sabtu','7'=>'Minggu'];

        $data = array_map(function($r) use ($hariNama) {
            $dayNum    = date('N', strtotime($r['tanggal']));
            $isWeekend = in_array($dayNum, [6, 7]);
            $hariColor = $isWeekend ? '#94a3b8' : '#ef4444';
            $hariLabel = $hariNama[$dayNum] ?? '';
            return [
                'tanggal'    => date('d/m/Y', strtotime($r['tanggal'])),
                'hari'       => "<span style='font-size:12px;color:{$hariColor}'>{$hariLabel}</span>",
                'keterangan' => esc($r['keterangan']),
                'aksi'       => '<button class="btn btn-sm btn-danger btn-del-libur" data-id="'.$r['id'].'"><i class="fas fa-trash"></i></button>',
            ];
        }, $rows);

        return $this->dtResponse($draw, $total, $filtered, $data);
    }

    public function index()
    {
        $tahun    = (int)($this->request->getGet('tahun') ?? date('Y'));
        $settings = $this->settingModel->orderBy('tahun', 'DESC')->findAll();

        return view('admin/pkpt/hari_libur', [
            'title'     => 'Hari Libur & Hari Kerja',
            'tahun'     => $tahun,
            'settings'  => $settings,
            'ringkasan' => $this->model->getRingkasanTahun($tahun),
        ]);
    }

    public function store()
    {
        $rules = [
            'tanggal'    => 'required|valid_date[Y-m-d]',
            'keterangan' => 'required|max_length[150]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $tanggal = $this->request->getPost('tanggal');
        $tahun   = (int)date('Y', strtotime($tanggal));

        // Cek duplikat
        $existing = $this->model->where('tanggal', $tanggal)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Tanggal ' . $tanggal . ' sudah terdaftar.');
        }

        $this->model->insert([
            'tahun'      => $tahun,
            'tanggal'    => $tanggal,
            'keterangan' => $this->request->getPost('keterangan'),
        ]);

        logActivity('hari_libur.create', 'hari_libur', "Tambah hari libur: $tanggal");
        return redirect()->to('/admin/pkpt/hari-libur?tahun=' . $tahun)->with('success', 'Hari libur ditambahkan.');
    }

    /** Batch insert dari array tanggal (untuk import) */
    public function storeBatch()
    {
        $rows = $this->request->getPost('rows') ?? [];
        $tahun = (int)($this->request->getPost('tahun') ?? date('Y'));
        $count = 0;

        foreach ((array)$rows as $row) {
            if (empty($row['tanggal']) || empty($row['keterangan'])) continue;
            $existing = $this->model->where('tanggal', $row['tanggal'])->first();
            if ($existing) continue;
            $this->model->insert([
                'tahun'      => $tahun,
                'tanggal'    => $row['tanggal'],
                'keterangan' => $row['keterangan'],
            ]);
            $count++;
        }

        return $this->response->setJSON(['success' => true, 'count' => $count]);
    }

    public function delete(int $id)
    {
        $row = $this->model->find($id);
        if (!$row) return $this->response->setJSON(['success' => false]);

        $this->model->delete($id);
        logActivity('hari_libur.delete', 'hari_libur', "Hapus hari libur id=$id");
        return $this->response->setJSON(['success' => true]);
    }

    /** AJAX: hitung HP kerja untuk tahun tertentu */
    public function hitungHp()
    {
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));
        return $this->response->setJSON($this->model->getRingkasanTahun($tahun));
    }
}
