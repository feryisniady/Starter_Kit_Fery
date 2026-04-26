<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PkaModel;
use App\Models\PkaTemplateModel;
use App\Models\SptModel;

class PkaController extends BaseController
{
    protected PkaModel         $pkaModel;
    protected PkaTemplateModel $tplModel;
    protected SptModel         $sptModel;

    public function __construct()
    {
        $this->pkaModel = new PkaModel();
        $this->tplModel = new PkaTemplateModel();
        $this->sptModel = new SptModel();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Index — Daftar PKA per SPT, grouped by fase
    // ──────────────────────────────────────────────────────────────────────

    public function index(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db      = \Config\Database::connect();
        $sdmList = $this->getSdmTim($sptId);

        // Budget HP dari KM-2 per SDM
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

        $sdmInfoMap = [];
        foreach ($sdmList as $s) {
            $sdmInfoMap[(int)$s['id']] = ['nama' => $s['nama'], 'peran_spt' => $s['peran_spt']];
        }

        // Hanya AT yang bisa di-assign prosedur
        $atList = array_values(array_filter($sdmList, fn($s) => $s['peran_spt'] === 'Anggota Tim'));

        // Template library
        $templateList = $db->table('pka_template')
            ->orderBy('jenis_audit')->orderBy('nama')
            ->get()->getResultArray();

        $pkaGrouped = $this->pkaModel->getBySptGrouped($sptId);
        $pkaList    = $this->pkaModel->getBySpt($sptId);

        // Build assignment map: pka_id → [sdm_id1, sdm_id2, ...]
        $assignmentMap = [];
        foreach ($pkaGrouped as $rows) {
            foreach ($rows as $row) {
                $assignmentMap[(int)$row['id']] = array_column($row['assigned_sdm'] ?? [], 'sdm_id');
            }
        }

        return view('admin/pka/index', [
            'title'         => 'Program Pengawasan (PKA) — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'           => $spt,
            'pkaGrouped'    => $pkaGrouped,
            'pkaList'       => $pkaList,
            'sdmList'       => $sdmList,
            'atList'        => $atList,
            'sdmInfoMap'    => $sdmInfoMap,
            'awBudgetMap'   => $awBudgetMap,
            'stats'         => $this->pkaModel->getStatsBySpt($sptId),
            'templateList'  => $templateList,
            'canEdit'       => canEditKmInSpt($sptId, 'km4'),
            'jenisAudit'    => $spt['jenis_pengawasan'] ?? null,
            'assignmentMap' => $assignmentMap,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Tambah prosedur
    // ──────────────────────────────────────────────────────────────────────

    public function store(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');
        if (!canEditKmInSpt($sptId, 'km4')) return redirect()->back()->with('error', 'Hanya Ketua Tim atau Dalnis yang dapat menambah prosedur PKA.');

        if (!$this->validate(['uraian_prosedur' => 'required|max_length[1000]'])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $fase  = in_array($this->request->getPost('fase'), ['persiapan','pelaksanaan','pelaporan'])
               ? $this->request->getPost('fase') : 'pelaksanaan';

        $pkaId = $this->pkaModel->insert([
            'spt_id'          => $sptId,
            'fase'            => $fase,
            'nomor_urut'      => $this->pkaModel->nextNomor($sptId, $fase),
            'uraian_prosedur' => $this->request->getPost('uraian_prosedur'),
            'rencana_waktu'   => $this->request->getPost('rencana_waktu') ?: null,
            'status'          => 'belum',
            'created_by'      => session()->get('user_id'),
        ]);

        $sdmIds = array_filter((array)$this->request->getPost('assign_sdm_ids'), 'is_numeric');
        if ($sdmIds) {
            $this->pkaModel->saveAssignment((int)$pkaId, $sdmIds, (int)session()->get('user_id'));
        }

        logActivity('pka.create', 'pka', "Tambah PKA id={$pkaId} fase={$fase} SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/pka')->with('success', 'Prosedur PKA ditambahkan.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Update prosedur (AJAX)
    // ──────────────────────────────────────────────────────────────────────

    public function update(int $id)
    {
        $pka = $this->pkaModel->find($id);
        if (!$pka) return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        if (!canEditKmInSpt($pka['spt_id'], 'km4')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya Ketua Tim atau Dalnis yang dapat mengedit PKA.']);
        }

        $fase = in_array($this->request->getPost('fase'), ['persiapan','pelaksanaan','pelaporan'])
              ? $this->request->getPost('fase') : ($pka['fase'] ?? 'pelaksanaan');

        $this->pkaModel->update($id, [
            'uraian_prosedur' => $this->request->getPost('uraian_prosedur'),
            'fase'            => $fase,
            'rencana_waktu'   => $this->request->getPost('rencana_waktu') ?: null,
        ]);

        $sdmIds = array_filter((array)$this->request->getPost('assign_sdm_ids'), 'is_numeric');
        $this->pkaModel->saveAssignment($id, $sdmIds, (int)session()->get('user_id'));

        logActivity('pka.update', 'pka', "Update PKA id={$id}");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->to('/admin/spt/' . $pka['spt_id'] . '/pka')->with('success', 'PKA diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Toggle dikerjakan
    // ──────────────────────────────────────────────────────────────────────

    public function selesai(int $id)
    {
        $pka = $this->pkaModel->find($id);
        if (!$pka) return $this->response->setJSON(['success' => false]);
        if (!canEditKmInSpt($pka['spt_id'], 'km4')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya Ketua Tim atau Dalnis yang dapat mengubah status PKA.']);
        }

        $newStatus = $pka['status'] === 'selesai' ? 'belum' : 'selesai';
        $this->pkaModel->update($id, ['status' => $newStatus]);

        logActivity('pka.selesai', 'pka', "Toggle PKA id={$id} → {$newStatus}");
        return $this->response->setJSON(['success' => true, 'status' => $newStatus]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Hapus prosedur
    // ──────────────────────────────────────────────────────────────────────

    public function delete(int $id)
    {
        $pka = $this->pkaModel->find($id);
        if (!$pka) return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
        if (!canEditKmInSpt($pka['spt_id'], 'km4')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya Ketua Tim atau Dalnis yang dapat menghapus PKA.']);
        }

        $sptId = $pka['spt_id'];
        $fase  = $pka['fase'] ?? 'pelaksanaan';

        \Config\Database::connect()->table('pka_assignment')->where('pka_id', $id)->delete();
        $this->pkaModel->delete($id);

        $rows = $this->pkaModel->where('spt_id', $sptId)->where('fase', $fase)
            ->orderBy('nomor_urut')->findAll();
        foreach ($rows as $i => $r) {
            $this->pkaModel->update($r['id'], ['nomor_urut' => $i + 1]);
        }

        logActivity('pka.delete', 'pka', "Hapus PKA id={$id} SPT id={$sptId}");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->to('/admin/spt/' . $sptId . '/pka')->with('success', 'Prosedur dihapus.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Bulk save assignments dari Tab Penugasan (AJAX)
    // ──────────────────────────────────────────────────────────────────────

    public function saveAssignments(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'km4')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak.']);
        }

        $assignments = $this->request->getPost('assignments') ?? [];
        if (!is_array($assignments)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak valid.']);
        }

        $userId = (int)session()->get('user_id');
        $saved  = 0;

        foreach ($assignments as $pkaId => $sdmIds) {
            $pka = $this->pkaModel->find((int)$pkaId);
            if (!$pka || (int)$pka['spt_id'] !== $sptId) continue;
            $sdmIds = array_filter((array)$sdmIds, 'is_numeric');
            $this->pkaModel->saveAssignment((int)$pkaId, $sdmIds, $userId);
            $saved++;
        }

        logActivity('pka.assignments.save', 'pka', "Bulk save assignments SPT id={$sptId}, {$saved} prosedur");
        return $this->response->setJSON(['success' => true, 'message' => "Penugasan {$saved} prosedur berhasil disimpan."]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Terapkan template ke SPT
    // ──────────────────────────────────────────────────────────────────────

    public function applyTemplate(int $sptId)
    {
        if (!canEditKmInSpt($sptId, 'km4')) return redirect()->back()->with('error', 'Akses ditolak.');

        $templateId = (int)$this->request->getPost('template_id');
        if (!$templateId) return redirect()->back()->with('error', 'Pilih template terlebih dahulu.');

        $count = $this->tplModel->applyToSpt($templateId, $sptId, (int)session()->get('user_id'));

        logActivity('pka.apply_template', 'pka', "Apply template id={$templateId} ke SPT id={$sptId}, {$count} prosedur");
        return redirect()->to('/admin/spt/' . $sptId . '/pka')
            ->with('success', "{$count} prosedur dari template berhasil ditambahkan ke PKA.");
    }

    // ──────────────────────────────────────────────────────────────────────
    // Print Formulir KM-6
    // ──────────────────────────────────────────────────────────────────────

    public function printKm6(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db  = \Config\Database::connect();
        $km1 = $db->table('spt_km1')->where('spt_id', $sptId)->get()->getRowArray();

        $pmSdm = null;
        $ktSdm = null;
        foreach (($spt['tim'] ?? []) as $t) {
            if (!$pmSdm && in_array($t['peran_spt'], ['PJ','WPJ','Pengendali Mutu'])) $pmSdm = $t;
            if (!$ktSdm && $t['peran_spt'] === 'Ketua Tim') $ktSdm = $t;
        }

        return view('admin/km/print_km6_pka', [
            'spt'     => $spt,
            'km1'     => $km1,
            'grouped' => $this->pkaModel->getBySptGrouped($sptId),
            'pmSdm'   => $pmSdm,
            'ktSdm'   => $ktSdm,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

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
