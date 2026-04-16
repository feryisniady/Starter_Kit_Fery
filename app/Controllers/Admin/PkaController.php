<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PkaModel;
use App\Models\SptModel;
use App\Models\SdmModel;

class PkaController extends BaseController
{
    protected PkaModel $pkaModel;
    protected SptModel $sptModel;
    protected SdmModel $sdmModel;

    public function __construct()
    {
        $this->pkaModel = new PkaModel();
        $this->sptModel = new SptModel();
        $this->sdmModel = new SdmModel();
    }

    /** Daftar PKA untuk satu SPT */
    public function index(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSptId($sptId, $spt)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        return view('admin/pka/index', [
            'title'   => 'Program Kerja Audit — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'     => $spt,
            'pkaList' => $this->pkaModel->getBySpt($sptId),
            'sdmList' => $this->getSdmTim($sptId),
            'stats'   => $this->pkaModel->getStatsBySpt($sptId),
        ]);
    }

    /** Tambah prosedur PKA */
    public function store(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSptId($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

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
        if (!$this->canAccessSptId($pka['spt_id'])) return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak.']);

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
        if (!$this->canAccessSptId($pka['spt_id'])) return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak.']);

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
        if (!$this->canAccessSptId($pka['spt_id'])) return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak.']);

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

    private function isAdmin(): bool
    {
        return hasRole('superadmin') || hasRole('admin') || hasPermission('spt.manage_all');
    }

    private function getUserIrbanId(int $userId): ?int
    {
        $sdm = $this->sdmModel->where('user_id', $userId)->first();
        return $sdm ? (int)$sdm['irban_id'] : null;
    }

    /**
     * Cek akses ke SPT (dan semua anak-anaknya: PKA, Temuan).
     * Terima $spt array langsung (opsional) untuk hindari query tambahan.
     */
    private function canAccessSptId(int $sptId, ?array $spt = null): bool
    {
        if ($this->isAdmin()) return true;
        $irbanId = $this->getUserIrbanId(session()->get('user_id'));
        if ($irbanId === null) return false;
        $spt = $spt ?? $this->sptModel->getDetail($sptId);
        return $spt !== null && (int)$spt['irban_id'] === $irbanId;
    }

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
