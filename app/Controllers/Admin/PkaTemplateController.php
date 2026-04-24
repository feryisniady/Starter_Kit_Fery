<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PkaTemplateModel;

class PkaTemplateController extends BaseController
{
    protected PkaTemplateModel $model;

    public function __construct()
    {
        $this->model = new PkaTemplateModel();
    }

    /** Daftar semua template */
    public function index()
    {
        return view('admin/pka/template_list', [
            'title'    => 'Library Template PKA',
            'templates'=> $this->model->getAll(),
        ]);
    }

    /** Form buat template baru */
    public function create()
    {
        return view('admin/pka/template_form', [
            'title'  => 'Buat Template PKA Baru',
            'tpl'    => null,
            'items'  => ['persiapan' => [], 'pelaksanaan' => [], 'pelaporan' => []],
            'action' => '/admin/pka-template/store',
        ]);
    }

    /** Simpan template baru */
    public function store()
    {
        if (!$this->validate(['nama' => 'required|max_length[255]'])) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $tplId = $this->model->insert([
            'nama'        => $this->request->getPost('nama'),
            'jenis_audit' => $this->request->getPost('jenis_audit') ?: null,
            'deskripsi'   => $this->request->getPost('deskripsi') ?: null,
            'created_by'  => session()->get('user_id'),
        ]);

        $this->saveItems($db, (int)$tplId, $now);

        logActivity('pka_template.create', 'pka_template', "Buat template PKA id={$tplId}");
        return redirect()->to('/admin/pka-template/' . $tplId . '/edit')
            ->with('success', 'Template PKA berhasil dibuat.');
    }

    /** Form edit template */
    public function edit(int $id)
    {
        $tpl = $this->model->find($id);
        if (!$tpl) return redirect()->to('/admin/pka-template')->with('error', 'Template tidak ditemukan.');

        return view('admin/pka/template_form', [
            'title'  => 'Edit Template PKA — ' . esc($tpl['nama']),
            'tpl'    => $tpl,
            'items'  => $this->model->getItemsGrouped($id),
            'action' => '/admin/pka-template/' . $id . '/update',
        ]);
    }

    /** Update template */
    public function update(int $id)
    {
        $tpl = $this->model->find($id);
        if (!$tpl) return redirect()->to('/admin/pka-template')->with('error', 'Template tidak ditemukan.');

        if (!$this->validate(['nama' => 'required|max_length[255]'])) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $this->model->update($id, [
            'nama'        => $this->request->getPost('nama'),
            'jenis_audit' => $this->request->getPost('jenis_audit') ?: null,
            'deskripsi'   => $this->request->getPost('deskripsi') ?: null,
        ]);

        // Hapus items lama, replace dengan yang baru
        $db->table('pka_template_item')->where('template_id', $id)->delete();
        $this->saveItems($db, $id, $now);

        logActivity('pka_template.update', 'pka_template', "Update template PKA id={$id}");
        return redirect()->to('/admin/pka-template/' . $id . '/edit')
            ->with('success', 'Template PKA berhasil disimpan.');
    }

    /** Hapus template */
    public function delete(int $id)
    {
        $db = \Config\Database::connect();
        $db->table('pka_template_item')->where('template_id', $id)->delete();
        $this->model->delete($id);

        logActivity('pka_template.delete', 'pka_template', "Hapus template PKA id={$id}");
        return redirect()->to('/admin/pka-template')->with('success', 'Template dihapus.');
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function saveItems($db, int $tplId, string $now): void
    {
        $phases  = ['persiapan', 'pelaksanaan', 'pelaporan'];
        $uraians = (array)($this->request->getPost('uraian') ?? []);
        $fases   = (array)($this->request->getPost('item_fase') ?? []);
        $nomorMap = array_fill_keys($phases, 0);

        foreach ($uraians as $i => $uraian) {
            $uraian = trim($uraian);
            if (!$uraian) continue;

            $fase = $fases[$i] ?? 'pelaksanaan';
            if (!in_array($fase, $phases)) $fase = 'pelaksanaan';
            $nomorMap[$fase]++;

            $db->table('pka_template_item')->insert([
                'template_id'     => $tplId,
                'fase'            => $fase,
                'nomor_urut'      => $nomorMap[$fase],
                'uraian_prosedur' => $uraian,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }
}
