<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PkaModel;
use App\Models\SptModel;

class PkaController extends BaseController
{
    protected PkaModel $pkaModel;
    protected SptModel $sptModel;

    public function __construct()
    {
        $this->pkaModel = new PkaModel();
        $this->sptModel = new SptModel();
    }

    /** Daftar PKA untuk satu SPT */
    public function index(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db      = \Config\Database::connect();
        $sdmList = $this->getSdmTim($sptId);

        // Total HP Rencana (semua fase) per SDM dari KM-2 Anggaran Waktu
        $awRows = $db->table('spt_anggaran_waktu')
            ->select('sdm_id,
                COALESCE(persiapan_rencana_hari,0) +
                COALESCE(pelaksanaan_rencana_hari,0) +
                COALESCE(penyelesaian_rencana_hari,0) as total_rencana')
            ->where('spt_id', $sptId)
            ->get()->getResultArray();
        $awBudgetMap = [];
        foreach ($awRows as $r) {
            $awBudgetMap[(int)$r['sdm_id']] = (float)$r['total_rencana'];
        }

        // Map info SDM untuk JS (nama + peran_spt)
        $sdmInfoMap = [];
        foreach ($sdmList as $s) {
            $sdmInfoMap[(int)$s['id']] = ['nama' => $s['nama'], 'peran_spt' => $s['peran_spt']];
        }

        return view('admin/pka/index', [
            'title'       => 'Program Kerja Audit — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'         => $spt,
            'pkaList'     => $this->pkaModel->getBySpt($sptId),
            'sdmList'     => $sdmList,
            'sdmInfoMap'  => $sdmInfoMap,
            'awBudgetMap' => $awBudgetMap,
            'stats'       => $this->pkaModel->getStatsBySpt($sptId),
            'canEdit'     => canEditKmInSpt($sptId, 'km4'),
        ]);
    }

    /** Tambah prosedur PKA */
    public function store(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');
        if (!canEditKmInSpt($sptId, 'km4')) return redirect()->back()->with('error', 'Hanya Ketua Tim atau Dalnis yang dapat menambah prosedur PKA.');

        if (!$this->validate(['uraian_prosedur' => 'required|max_length[1000]'])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $this->pkaModel->insert([
            'spt_id'           => $sptId,
            'nomor_urut'       => $this->pkaModel->nextNomor($sptId),
            'uraian_prosedur'  => $this->request->getPost('uraian_prosedur'),
            'pic_sdm_id'       => $this->request->getPost('pic_sdm_id') ?: null,
            'rencana_waktu'    => $this->request->getPost('rencana_waktu') ?: null,
            'realisasi_waktu'  => null,
            'status'           => 'belum',
            'created_by'       => session()->get('user_id'),
        ]);

        logActivity('pka.create', 'pka', "Tambah PKA untuk SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/pka')->with('success', 'Prosedur PKA ditambahkan.');
    }

    /** Update prosedur PKA (via POST/AJAX) */
    public function update(int $id)
    {
        $pka = $this->pkaModel->find($id);
        if (!$pka) return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        if (!canEditKmInSpt($pka['spt_id'], 'km4')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya Ketua Tim atau Dalnis yang dapat mengedit PKA.']);
        }

        $this->pkaModel->update($id, [
            'uraian_prosedur' => $this->request->getPost('uraian_prosedur'),
            'pic_sdm_id'      => $this->request->getPost('pic_sdm_id') ?: null,
            'rencana_waktu'   => $this->request->getPost('rencana_waktu') ?: null,
            'realisasi_waktu' => $this->request->getPost('realisasi_waktu') ?: null,
        ]);

        logActivity('pka.update', 'pka', "Update PKA id={$id}");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->to('/admin/spt/' . $pka['spt_id'] . '/pka')->with('success', 'PKA diperbarui.');
    }

    /** Tandai selesai / batalkan selesai */
    public function selesai(int $id)
    {
        $pka = $this->pkaModel->find($id);
        if (!$pka) return $this->response->setJSON(['success' => false]);
        if (!canEditKmInSpt($pka['spt_id'], 'km4')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya Ketua Tim atau Dalnis yang dapat mengubah status PKA.']);
        }

        $newStatus = $pka['status'] === 'selesai' ? 'belum' : 'selesai';
        $this->pkaModel->update($id, ['status' => $newStatus]);

        logActivity('pka.selesai', 'pka', "Toggle selesai PKA id={$id} → {$newStatus}");
        return $this->response->setJSON(['success' => true, 'status' => $newStatus]);
    }

    /** Hapus prosedur PKA */
    public function delete(int $id)
    {
        $pka = $this->pkaModel->find($id);
        if (!$pka) return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        if (!canEditKmInSpt($pka['spt_id'], 'km4')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya Ketua Tim atau Dalnis yang dapat menghapus PKA.']);
        }

        $sptId = $pka['spt_id'];
        $this->pkaModel->delete($id);

        // Reorder nomor_urut
        $rows = $this->pkaModel->where('spt_id', $sptId)->orderBy('nomor_urut')->findAll();
        foreach ($rows as $i => $r) {
            $this->pkaModel->update($r['id'], ['nomor_urut' => $i + 1]);
        }

        logActivity('pka.delete', 'pka', "Hapus PKA id={$id} dari SPT id={$sptId}");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->to('/admin/spt/' . $sptId . '/pka')->with('success', 'Prosedur dihapus.');
    }

    // ===================================================
    // HELPERS
    // ===================================================

    /**
     * Ambil SDM yang ada di tim SPT saja (bukan semua SDM aktif).
     * Dipakai untuk dropdown PIC di form PKA.
     */
    private function getSdmTim(int $sptId): array
    {
        return \Config\Database::connect()
            ->table('spt_tim st')
            ->select('s.id, s.nama, s.nip, st.peran_spt')
            ->join('sdm s', 's.id = st.sdm_id')
            ->where('st.spt_id', $sptId)
            ->orderBy('st.urutan')
            ->get()->getResultArray();
    }
}
