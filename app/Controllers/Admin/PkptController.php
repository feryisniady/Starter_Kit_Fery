<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PkptModel;
use App\Models\PkptKegiatanModel;
use App\Models\PkptTimModel;
use App\Models\PkptSettingModel;
use App\Models\IrbanModel;
use App\Models\EntitasModel;
use App\Models\SdmModel;

class PkptController extends BaseController
{
    protected PkptModel         $pkptModel;
    protected PkptKegiatanModel $kegiatanModel;
    protected PkptTimModel      $timModel;
    protected PkptSettingModel  $settingModel;
    protected IrbanModel        $irbanModel;
    protected EntitasModel      $entitasModel;
    protected SdmModel          $sdmModel;

    public function __construct()
    {
        $this->pkptModel     = new PkptModel();
        $this->kegiatanModel = new PkptKegiatanModel();
        $this->timModel      = new PkptTimModel();
        $this->settingModel  = new PkptSettingModel();
        $this->irbanModel    = new IrbanModel();
        $this->entitasModel  = new EntitasModel();
        $this->sdmModel      = new SdmModel();
    }

    // ===================================================
    // PKPT HEADER
    // ===================================================

    public function index()
    {
        $userId  = session()->get('user_id');
        $isAdmin = $this->isAdmin();
        $tahun   = (int)($this->request->getGet('tahun') ?? $this->settingModel->getTahunAktif());

        $q = $this->pkptModel->db->table('pkpt p')
            ->select('p.*, i.nama as irban_nama, i.kode as irban_kode,
                (SELECT COUNT(*) FROM pkpt_kegiatan WHERE pkpt_id = p.id) as jumlah_kegiatan')
            ->join('irban i', 'i.id = p.irban_id')
            ->where('p.tahun', $tahun);

        if (!$isAdmin) {
            $irbanId = $this->getUserIrbanId($userId);
            if ($irbanId) $q->where('p.irban_id', $irbanId);
        }

        return view('admin/pkpt/index', [
            'title'    => 'PKPT Tahun ' . $tahun,
            'pkptList' => $q->orderBy('i.kode')->get()->getResultArray(),
            'tahun'    => $tahun,
            'settings' => $this->settingModel->orderBy('tahun', 'DESC')->findAll(),
        ]);
    }

    public function show(int $id)
    {
        $pkpt = $this->pkptModel->getWithIrban($id);
        if (!$pkpt) return redirect()->to('/admin/pkpt')->with('error', 'PKPT tidak ditemukan.');
        if (!$this->canAccessPkpt($pkpt)) return redirect()->to('/admin/pkpt')->with('error', 'Akses ditolak.');

        return view('admin/pkpt/show', [
            'title'     => 'Detail PKPT — ' . $pkpt['irban_nama'] . ' ' . $pkpt['tahun'],
            'pkpt'      => $pkpt,
            'kegiatan'  => $this->kegiatanModel->getByPkpt($id),
            'setting'   => $this->settingModel->getByTahun((int)$pkpt['tahun']),
        ]);
    }

    public function createOrGetPkpt()
    {
        $irbanId = (int)$this->request->getPost('irban_id');
        $tahun   = (int)$this->request->getPost('tahun');

        if (!$this->isAdmin()) {
            $irbanId = $this->getUserIrbanId(session()->get('user_id')) ?? 0;
        }

        $existing = $this->pkptModel->getByIrbanTahun($irbanId, $tahun);
        if ($existing) {
            return redirect()->to('/admin/pkpt/' . $existing['id']);
        }

        $id = $this->pkptModel->insert([
            'tahun'      => $tahun,
            'irban_id'   => $irbanId,
            'status'     => 'draft',
            'created_by' => session()->get('user_id'),
        ]);

        logActivity('pkpt.create', 'pkpt', "Buat PKPT irban_id={$irbanId} tahun={$tahun}");
        return redirect()->to('/admin/pkpt/' . $id);
    }

    // ===================================================
    // PKPT KEGIATAN
    // ===================================================

    public function createKegiatan(int $pkptId)
    {
        $pkpt = $this->pkptModel->getWithIrban($pkptId);
        if (!$pkpt || !$this->canAccessPkpt($pkpt)) {
            return redirect()->to('/admin/pkpt')->with('error', 'Akses ditolak.');
        }

        $setting = $this->settingModel->getByTahun((int)$pkpt['tahun']);

        return view('admin/pkpt/kegiatan_form', [
            'title'    => 'Tambah Kegiatan PKPT',
            'pkpt'     => $pkpt,
            'setting'  => $setting,
            'entitas'  => $this->entitasModel->getAktif(),
            'sdm'      => $this->sdmModel->withIrban(),
            'row'      => null,
            'timRows'  => [],
        ]);
    }

    public function storeKegiatan(int $pkptId)
    {
        $pkpt = $this->pkptModel->getWithIrban($pkptId);
        if (!$pkpt || !$this->canAccessPkpt($pkpt)) {
            return redirect()->to('/admin/pkpt')->with('error', 'Akses ditolak.');
        }

        $rules = [
            'area_pengawasan'  => 'required',
            'jenis_pengawasan' => 'required',
            'tujuan_sasaran'   => 'required',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $kode = $this->kegiatanModel->generateKode($pkptId);

        $kegiatanId = $this->kegiatanModel->insert([
            'pkpt_id'          => $pkptId,
            'kode_kegiatan'    => $kode,
            'area_pengawasan'  => $this->request->getPost('area_pengawasan'),
            'jenis_pengawasan' => $this->request->getPost('jenis_pengawasan'),
            'tujuan_sasaran'   => $this->request->getPost('tujuan_sasaran'),
            'ruang_lingkup'    => $this->request->getPost('ruang_lingkup'),
            'risiko_audit'     => $this->request->getPost('risiko_audit') ?: 'sedang',
            'jadwal_rmp'       => $this->request->getPost('jadwal_rmp'),
            'jadwal_rpl'       => $this->request->getPost('jadwal_rpl'),
            'tanggal_mulai'    => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai'  => $this->request->getPost('tanggal_selesai') ?: null,
            'jumlah_laporan'   => (int)$this->request->getPost('jumlah_laporan') ?: 1,
            'sarana_prasarana' => $this->request->getPost('sarana_prasarana'),
            'status'           => 'aktif',
        ]);

        // Simpan entitas (multi)
        $entitasIds = $this->request->getPost('entitas_ids') ?? [];
        $db = \Config\Database::connect();
        foreach ((array)$entitasIds as $eid) {
            $db->table('pkpt_entitas')->insert(['pkpt_kegiatan_id' => $kegiatanId, 'entitas_id' => (int)$eid]);
        }

        // Simpan tim
        $timData = $this->parseTimPost();
        $setting = $this->settingModel->getByTahun((int)$pkpt['tahun']);
        $tarif   = $setting ? (int)$setting['tarif_hp'] : 160000;
        if ($timData) $this->timModel->saveTimKegiatan((int)$kegiatanId, $timData, $tarif);

        logActivity('pkpt.kegiatan.create', 'pkpt_kegiatan', "Tambah kegiatan {$kode}");
        return redirect()->to('/admin/pkpt/' . $pkptId)->with('success', "Kegiatan {$kode} berhasil ditambahkan.");
    }

    public function editKegiatan(int $id)
    {
        $kegiatan = $this->kegiatanModel->getDetail($id);
        if (!$kegiatan) return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');

        $pkpt = $this->pkptModel->getWithIrban($kegiatan['pkpt_id']);
        if (!$this->canAccessPkpt($pkpt)) return redirect()->to('/admin/pkpt')->with('error', 'Akses ditolak.');

        return view('admin/pkpt/kegiatan_form', [
            'title'   => 'Edit Kegiatan PKPT',
            'pkpt'    => $pkpt,
            'setting' => $this->settingModel->getByTahun((int)$pkpt['tahun']),
            'entitas' => $this->entitasModel->getAktif(),
            'sdm'     => $this->sdmModel->withIrban(),
            'row'     => $kegiatan,
            'timRows' => $kegiatan['tim'],
        ]);
    }

    public function updateKegiatan(int $id)
    {
        $kegiatan = $this->kegiatanModel->find($id);
        if (!$kegiatan) return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');

        $pkpt = $this->pkptModel->getWithIrban($kegiatan['pkpt_id']);
        if (!$this->canAccessPkpt($pkpt)) return redirect()->to('/admin/pkpt')->with('error', 'Akses ditolak.');

        $this->kegiatanModel->update($id, [
            'area_pengawasan'  => $this->request->getPost('area_pengawasan'),
            'jenis_pengawasan' => $this->request->getPost('jenis_pengawasan'),
            'tujuan_sasaran'   => $this->request->getPost('tujuan_sasaran'),
            'ruang_lingkup'    => $this->request->getPost('ruang_lingkup'),
            'risiko_audit'     => $this->request->getPost('risiko_audit') ?: 'sedang',
            'jadwal_rmp'       => $this->request->getPost('jadwal_rmp'),
            'jadwal_rpl'       => $this->request->getPost('jadwal_rpl'),
            'tanggal_mulai'    => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai'  => $this->request->getPost('tanggal_selesai') ?: null,
            'jumlah_laporan'   => (int)$this->request->getPost('jumlah_laporan') ?: 1,
            'sarana_prasarana' => $this->request->getPost('sarana_prasarana'),
        ]);

        // Update entitas
        $db = \Config\Database::connect();
        $db->table('pkpt_entitas')->where('pkpt_kegiatan_id', $id)->delete();
        foreach ((array)($this->request->getPost('entitas_ids') ?? []) as $eid) {
            $db->table('pkpt_entitas')->insert(['pkpt_kegiatan_id' => $id, 'entitas_id' => (int)$eid]);
        }

        // Update tim
        $timData = $this->parseTimPost();
        $setting = $this->settingModel->getByTahun((int)$pkpt['tahun']);
        $tarif   = $setting ? (int)$setting['tarif_hp'] : 160000;
        if ($timData) $this->timModel->saveTimKegiatan($id, $timData, $tarif);

        logActivity('pkpt.kegiatan.update', 'pkpt_kegiatan', "Update kegiatan id={$id}");
        return redirect()->to('/admin/pkpt/' . $kegiatan['pkpt_id'])->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function deleteKegiatan(int $id)
    {
        $kegiatan = $this->kegiatanModel->find($id);
        if (!$kegiatan) return $this->response->setJSON(['success' => false]);

        $db = \Config\Database::connect();
        $db->table('pkpt_entitas')->where('pkpt_kegiatan_id', $id)->delete();
        $db->table('pkpt_tim')->where('pkpt_kegiatan_id', $id)->delete();
        $this->kegiatanModel->delete($id);

        logActivity('pkpt.kegiatan.delete', 'pkpt_kegiatan', "Hapus kegiatan id={$id}");
        return $this->response->setJSON(['success' => true]);
    }

    /**
     * AJAX: sisa HP SDM untuk tahun tertentu.
     */
    public function sisaHpSdm()
    {
        $sdmId = (int)$this->request->getGet('sdm_id');
        $tahun = (int)($this->request->getGet('tahun') ?? date('Y'));
        return $this->response->setJSON([
            'sisa_hp' => $this->sdmModel->getSisaHp($sdmId, $tahun),
        ]);
    }

    // ===================================================
    // HELPERS
    // ===================================================

    private function parseTimPost(): array
    {
        $sdmIds = $this->request->getPost('tim_sdm_id') ?? [];
        $perans = $this->request->getPost('tim_peran') ?? [];
        $hps    = $this->request->getPost('tim_hp') ?? [];

        $result = [];
        foreach ((array)$sdmIds as $i => $sdmId) {
            if (!$sdmId) continue;
            $result[] = [
                'sdm_id'   => (int)$sdmId,
                'peran'    => $perans[$i] ?? 'AT',
                'hp_total' => (int)($hps[$i] ?? 0),
            ];
        }
        return $result;
    }

    private function isAdmin(): bool
    {
        return session()->get('is_superadmin') || hasPermission('pkpt.manage_all');
    }

    private function getUserIrbanId(int $userId): ?int
    {
        $sdm = $this->sdmModel->where('user_id', $userId)->first();
        return $sdm ? (int)$sdm['irban_id'] : null;
    }

    private function canAccessPkpt(array $pkpt): bool
    {
        if ($this->isAdmin()) return true;
        $irbanId = $this->getUserIrbanId(session()->get('user_id'));
        return $irbanId && (int)$pkpt['irban_id'] === $irbanId;
    }
}
