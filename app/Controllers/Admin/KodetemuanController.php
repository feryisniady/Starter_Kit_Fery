<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KodetemuanModel;
use App\Traits\DatatableTrait;

class KodetemuanController extends BaseController
{
    use DatatableTrait;

    protected KodetemuanModel $model;

    public function __construct()
    {
        $this->model = new KodetemuanModel();
    }

    public function index()
    {
        return view('admin/kode_temuan/index', [
            'title'      => 'Kode Temuan',
            'jenisLabel' => KodetemuanModel::$jenisLabel,
            'jenisColor' => KodetemuanModel::$jenisColor,
        ]);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search] = $this->dtRequest();

        $jenis = $this->request->getPost('jenis') ?? '';

        $db = \Config\Database::connect();

        $baseQ = $db->table('kode_temuan');
        if ($jenis) $baseQ->where('jenis', (int)$jenis);
        $total = $baseQ->countAllResults(false);

        if ($search) {
            $baseQ->groupStart()
                ->like('kode', $search)
                ->orLike('uraian', $search)
                ->groupEnd();
        }

        $filtered = $search ? $baseQ->countAllResults(false) : $total;
        $rows     = $baseQ->orderBy('kode')->limit($length, $start)->get()->getResultArray();

        $jl = KodetemuanModel::$jenisLabel;
        $jc = KodetemuanModel::$jenisColor;

        $data = array_map(function ($r) use ($jl, $jc) {
            return [
                'kode'   => '<code>' . esc($r['kode']) . '</code>',
                'uraian' => esc($r['uraian']),
                'jenis'  => '<span class="badge badge-' . ($jc[$r['jenis']] ?? 'secondary') . '">'
                          . esc($jl[$r['jenis']] ?? '?') . '</span>',
            ];
        }, $rows);

        return $this->dtResponse($draw, $total, $filtered, $data);
    }
}
