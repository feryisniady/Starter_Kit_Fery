<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TemuanModel;
use App\Models\RekomendasiModel;
use App\Models\SptModel;
use App\Models\PkaModel;
use App\Models\KodetemuanModel;
use App\Models\SdmModel;

class TemuanController extends BaseController
{
    protected TemuanModel      $temuanModel;
    protected RekomendasiModel $rekomendasiModel;
    protected SptModel         $sptModel;
    protected PkaModel         $pkaModel;
    protected KodetemuanModel  $kodetemuanModel;
    protected SdmModel         $sdmModel;

    public function __construct()
    {
        $this->temuanModel      = new TemuanModel();
        $this->rekomendasiModel = new RekomendasiModel();
        $this->sptModel         = new SptModel();
        $this->pkaModel         = new PkaModel();
        $this->kodetemuanModel  = new KodetemuanModel();
        $this->sdmModel         = new SdmModel();
    }

    // ===================================================
    // LIST per SPT
    // ===================================================

    public function index(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSptId($sptId, $spt)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        return view('admin/temuan/index', [
            'title'       => 'Temuan Audit — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'         => $spt,
            'temuanList'  => $this->temuanModel->getBySpt($sptId),
            'summary'     => $this->temuanModel->getSummaryBySpt($sptId),
            'statusLabel' => TemuanModel::$statusLabel,
            'statusColor' => TemuanModel::$statusColor,
            'jenisLabel'  => KodetemuanModel::$jenisLabel,
            'jenisColor'  => KodetemuanModel::$jenisColor,
        ]);
    }

    // ===================================================
    // CREATE
    // ===================================================

    public function create(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSptId($sptId, $spt)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        return view('admin/temuan/form', [
            'title'          => 'Tambah Temuan — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'            => $spt,
            'temuan'         => null,
            'rekomendasi'    => [],
            'pkaList'        => $this->pkaModel->getBySpt($sptId),
            'kodetemuanList' => $this->kodetemuanModel->getDropdown(),
            'grouped'        => $this->kodetemuanModel->getGrouped(),
            'statusLabel'    => TemuanModel::$statusLabel,
            'jenisLabel'     => KodetemuanModel::$jenisLabel,
        ]);
    }

    public function store(int $sptId)
    {
        $spt = $this->sptModel->find($sptId);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSptId($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        if (!$this->validate(['judul' => 'required|max_length[500]', 'kondisi' => 'required'])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $userId   = session()->get('user_id');
        $temuanId = $this->temuanModel->insert([
            'spt_id'        => $sptId,
            'pka_id'        => $this->request->getPost('pka_id') ?: null,
            'nomor_temuan'  => $this->temuanModel->nextNomor($sptId),
            'judul'         => $this->request->getPost('judul'),
            'kondisi'       => $this->request->getPost('kondisi'),
            'kriteria'      => $this->request->getPost('kriteria'),
            'sebab'         => $this->request->getPost('sebab'),
            'akibat'        => $this->request->getPost('akibat'),
            'kode_temuan_id'=> $this->request->getPost('kode_temuan_id') ?: null,
            'nilai_temuan'  => (int)($this->request->getPost('nilai_temuan') ?? 0),
            'status_temuan' => 'buka',
            'created_by'    => $userId,
        ]);

        // Simpan rekomendasi batch
        $rekom = $this->request->getPost('rekomendasi') ?? [];
        if ($rekom) $this->rekomendasiModel->saveBatch((int)$temuanId, $rekom, $userId);

        logActivity('temuan.create', 'temuan', "Tambah temuan untuk SPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/temuan')->with('success', 'Temuan berhasil ditambahkan.');
    }

    // ===================================================
    // SHOW
    // ===================================================

    public function show(int $id)
    {
        $temuan = $this->temuanModel->getDetail($id);
        if (!$temuan) return redirect()->back()->with('error', 'Temuan tidak ditemukan.');
        if (!$this->canAccessSptId($temuan['spt_id'])) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        return view('admin/temuan/show', [
            'title'           => 'Detail Temuan — ' . $temuan['nomor_temuan'],
            'temuan'          => $temuan,
            'statusLabel'     => TemuanModel::$statusLabel,
            'statusColor'     => TemuanModel::$statusColor,
            'rekomendasiLabel'=> RekomendasiModel::$statusLabel,
            'rekomendasiColor'=> RekomendasiModel::$statusColor,
            'jenisLabel'      => KodetemuanModel::$jenisLabel,
            'jenisColor'      => KodetemuanModel::$jenisColor,
        ]);
    }

    // ===================================================
    // EDIT / UPDATE
    // ===================================================

    public function edit(int $id)
    {
        $temuan = $this->temuanModel->getDetail($id);
        if (!$temuan) return redirect()->back()->with('error', 'Temuan tidak ditemukan.');
        if (!$this->canAccessSptId($temuan['spt_id'])) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $spt = $this->sptModel->getDetail($temuan['spt_id']);

        return view('admin/temuan/form', [
            'title'          => 'Edit Temuan — ' . $temuan['nomor_temuan'],
            'spt'            => $spt,
            'temuan'         => $temuan,
            'rekomendasi'    => $temuan['rekomendasi'],
            'pkaList'        => $this->pkaModel->getBySpt($temuan['spt_id']),
            'kodetemuanList' => $this->kodetemuanModel->getDropdown(),
            'grouped'        => $this->kodetemuanModel->getGrouped(),
            'statusLabel'    => TemuanModel::$statusLabel,
            'jenisLabel'     => KodetemuanModel::$jenisLabel,
        ]);
    }

    public function update(int $id)
    {
        $temuan = $this->temuanModel->find($id);
        if (!$temuan) return redirect()->back()->with('error', 'Temuan tidak ditemukan.');
        if (!$this->canAccessSptId($temuan['spt_id'])) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        if (!$this->validate(['judul' => 'required|max_length[500]', 'kondisi' => 'required'])) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $userId = session()->get('user_id');

        $this->temuanModel->update($id, [
            'pka_id'         => $this->request->getPost('pka_id') ?: null,
            'judul'          => $this->request->getPost('judul'),
            'kondisi'        => $this->request->getPost('kondisi'),
            'kriteria'       => $this->request->getPost('kriteria'),
            'sebab'          => $this->request->getPost('sebab'),
            'akibat'         => $this->request->getPost('akibat'),
            'kode_temuan_id' => $this->request->getPost('kode_temuan_id') ?: null,
            'nilai_temuan'   => (int)($this->request->getPost('nilai_temuan') ?? 0),
            'status_temuan'  => $this->request->getPost('status_temuan') ?? $temuan['status_temuan'],
        ]);

        $rekom = $this->request->getPost('rekomendasi') ?? [];
        $this->rekomendasiModel->saveBatch($id, $rekom, $userId);

        logActivity('temuan.update', 'temuan', "Update temuan id={$id}");
        return redirect()->to('/admin/spt/' . $temuan['spt_id'] . '/temuan')->with('success', 'Temuan berhasil diperbarui.');
    }

    // ===================================================
    // DELETE
    // ===================================================

    public function delete(int $id)
    {
        $temuan = $this->temuanModel->find($id);
        if (!$temuan) return redirect()->back()->with('error', 'Temuan tidak ditemukan.');
        if (!$this->canAccessSptId($temuan['spt_id'])) return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => false, 'message' => 'Akses ditolak.'])
            : redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $sptId = $temuan['spt_id'];
        $this->temuanModel->delete($id); // CASCADE rekomendasi via FK

        logActivity('temuan.delete', 'temuan', "Hapus temuan id={$id}");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->to('/admin/spt/' . $sptId . '/temuan')->with('success', 'Temuan dihapus.');
    }

    // ===================================================
    // HELPERS
    // ===================================================

    protected function isAdmin(): bool
    {
        return hasRole('superadmin') || hasRole('admin') || hasPermission('spt.manage_all');
    }

    protected function getUserIrbanId(int $userId): ?int
    {
        $sdm = $this->sdmModel->where('user_id', $userId)->first();
        return $sdm ? (int)$sdm['irban_id'] : null;
    }

    private function canAccessSptId(int $sptId, ?array $spt = null): bool
    {
        if ($this->isAdmin()) return true;
        $irbanId = $this->getUserIrbanId(session()->get('user_id'));
        if ($irbanId === null) return false;
        $spt = $spt ?? $this->sptModel->getDetail($sptId);
        return $spt !== null && (int)$spt['irban_id'] === $irbanId;
    }
}
