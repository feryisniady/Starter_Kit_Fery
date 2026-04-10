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
use App\Models\HariLiburModel;
use App\Traits\DatatableTrait;

class PkptController extends BaseController
{
    use DatatableTrait;

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
        $tahun = (int)($this->request->getGet('tahun') ?? $this->settingModel->getTahunAktif());

        return view('admin/pkpt/index', [
            'title'          => 'PKPT ' . $tahun,
            'tahun'          => $tahun,
            'settings'       => $this->settingModel->orderBy('tahun', 'DESC')->findAll(),
            'currentSetting' => $this->settingModel->getByTahun($tahun),
            'irbanList'      => (new IrbanModel())->orderBy('kode')->findAll(),
        ]);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw'=>$draw,'start'=>$start,'length'=>$length,'search'=>$search,'order'=>$order] = $this->dtRequest();

        $userId  = session()->get('user_id');
        $isAdmin = $this->isAdmin();
        $tahun   = (int)($this->request->getPost('tahun') ?? $this->settingModel->getTahunAktif());

        $db = \Config\Database::connect();

        $baseQ = $db->table('pkpt p')
            ->select('p.*, i.nama as irban_nama, i.kode as irban_kode,
                (SELECT COUNT(*) FROM pkpt_kegiatan WHERE pkpt_id = p.id) as jumlah_kegiatan')
            ->join('irban i', 'i.id = p.irban_id')
            ->where('p.tahun', $tahun);

        if (!$isAdmin) {
            $irbanId = $this->getUserIrbanId($userId);
            if ($irbanId) $baseQ->where('p.irban_id', $irbanId);
        }

        $total = (clone $baseQ)->countAllResults(false);

        if ($search) {
            $baseQ->groupStart()
                ->like('i.kode', $search)
                ->orLike('i.nama', $search)
                ->groupEnd();
        }

        $filtered = $search ? (clone $baseQ)->countAllResults(false) : $total;
        $rows = $baseQ->orderBy('i.kode')->limit($length, $start)->get()->getResultArray();

        $statusColor = ['draft'=>'secondary','diajukan'=>'info','disetujui'=>'success'];
        $statusLabel = ['draft'=>'Draft','diajukan'=>'Diajukan','disetujui'=>'Disetujui'];

        $data = [];
        foreach ($rows as $i => $row) {
            $badge = '<span class="badge badge-'.($statusColor[$row['status']]??'secondary').'">'.($statusLabel[$row['status']]??$row['status']).'</span>';
            $data[] = [
                'no'       => $start + $i + 1,
                'kode'     => '<span class="badge badge-primary">'.esc($row['irban_kode']).'</span>',
                'irban'    => esc($row['irban_nama']),
                'kegiatan' => '<span style="font-weight:600">'.$row['jumlah_kegiatan'].'</span> kegiatan',
                'status'   => $badge,
                'aksi'     => $this->dtActions([
                    ['type'=>'primary', 'icon'=>'fa-list-check', 'title'=>'Kelola Kegiatan', 'href'=>'/admin/pkpt/'.$row['id']],
                ]),
            ];
        }

        return $this->dtResponse($draw, $total, $filtered, $data);
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

        if (!$irbanId) {
            return redirect()->to('/admin/pkpt?tahun=' . $tahun)
                ->with('error', 'Silakan pilih Irban terlebih dahulu.');
        }

        if (!$tahun) {
            return redirect()->to('/admin/pkpt')
                ->with('error', 'Tahun PKPT tidak valid.');
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

        if (!$id) {
            return redirect()->to('/admin/pkpt?tahun=' . $tahun)
                ->with('error', 'Gagal membuat PKPT. Periksa data Irban dan Tahun.');
        }

        logActivity('pkpt.create', 'pkpt', "Buat PKPT irban_id={$irbanId} tahun={$tahun}");
        return redirect()->to('/admin/pkpt/' . $id);
    }

    /** Ubah status PKPT: draft → diajukan → disetujui */
    public function updateStatus(int $id)
    {
        $pkpt = $this->pkptModel->find($id);
        if (!$pkpt) return redirect()->back()->with('error', 'PKPT tidak ditemukan.');

        $flow   = ['draft' => 'diajukan', 'diajukan' => 'disetujui'];
        $status = $this->request->getPost('status') ?? ($flow[$pkpt['status']] ?? null);

        if (!$status || !in_array($status, ['draft', 'diajukan', 'disetujui'])) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $update = ['status' => $status];
        if ($status === 'disetujui') {
            $update['approved_by'] = session()->get('user_id');
            $update['approved_at'] = date('Y-m-d H:i:s');
        }

        $this->pkptModel->update($id, $update);
        logActivity('pkpt.status', 'pkpt', "Update status PKPT id={$id} → {$status}");
        return redirect()->to('/admin/pkpt/' . $id)->with('success', 'Status PKPT diperbarui: ' . ucfirst($status));
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

        $setting   = $this->settingModel->getByTahun((int)$pkpt['tahun']);
        $hpEfektif = (new HariLiburModel())->hitungHariKerjaTahun((int)$pkpt['tahun']);
        if ($hpEfektif === 0 && $setting) $hpEfektif = (int)$setting['total_hp_tahunan'];

        $hpTerpakai = $this->kegiatanModel->getTotalHpByPkpt($pkptId);

        return view('admin/pkpt/kegiatan_form', [
            'title'      => 'Tambah Kegiatan PKPT',
            'pkpt'       => $pkpt,
            'setting'    => $setting,
            'hpEfektif'  => $hpEfektif,
            'hpTerpakai' => $hpTerpakai,
            'hpSisa'     => max(0, $hpEfektif - $hpTerpakai),
            'entitas'    => $this->entitasModel->getAktif(),
            'sdm'        => $this->sdmModel->withIrban(),
            'row'        => null,
            'timRows'    => [],
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

        // Cek sisa HP PKPT
        $setting   = $this->settingModel->getByTahun((int)$pkpt['tahun']);
        $hpEfektif = (new HariLiburModel())->hitungHariKerjaTahun((int)$pkpt['tahun']);
        if ($hpEfektif === 0 && $setting) $hpEfektif = (int)$setting['total_hp_tahunan'];
        $hpTerpakai   = $this->kegiatanModel->getTotalHpByPkpt($pkptId);
        $hpSisa       = max(0, $hpEfektif - $hpTerpakai);
        $hpDiajukan   = array_sum(array_map('intval', (array)($this->request->getPost('tim_hp') ?? [])));
        if ($hpDiajukan > 0 && $hpDiajukan > $hpSisa) {
            return redirect()->back()->withInput()
                ->with('error', "Total HP tim ({$hpDiajukan} hari) melebihi sisa HP PKPT yang tersedia ({$hpSisa} hari).");
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

        $setting    = $this->settingModel->getByTahun((int)$pkpt['tahun']);
        $hpEfektif  = (new HariLiburModel())->hitungHariKerjaTahun((int)$pkpt['tahun']);
        if ($hpEfektif === 0 && $setting) $hpEfektif = (int)$setting['total_hp_tahunan'];
        // Exclude kegiatan ini sendiri saat hitung sisa (mode edit)
        $hpTerpakai = $this->kegiatanModel->getTotalHpByPkpt($kegiatan['pkpt_id'], $id);

        return view('admin/pkpt/kegiatan_form', [
            'title'      => 'Edit Kegiatan PKPT',
            'pkpt'       => $pkpt,
            'setting'    => $setting,
            'hpEfektif'  => $hpEfektif,
            'hpTerpakai' => $hpTerpakai,
            'hpSisa'     => max(0, $hpEfektif - $hpTerpakai),
            'entitas'    => $this->entitasModel->getAktif(),
            'sdm'        => $this->sdmModel->withIrban(),
            'row'        => $kegiatan,
            'timRows'    => $kegiatan['tim'],
        ]);
    }

    public function updateKegiatan(int $id)
    {
        $kegiatan = $this->kegiatanModel->find($id);
        if (!$kegiatan) return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');

        $pkpt = $this->pkptModel->getWithIrban($kegiatan['pkpt_id']);
        if (!$this->canAccessPkpt($pkpt)) return redirect()->to('/admin/pkpt')->with('error', 'Akses ditolak.');

        // Cek sisa HP (kecualikan kegiatan ini sendiri)
        $setting   = $this->settingModel->getByTahun((int)$pkpt['tahun']);
        $hpEfektif = (new HariLiburModel())->hitungHariKerjaTahun((int)$pkpt['tahun']);
        if ($hpEfektif === 0 && $setting) $hpEfektif = (int)$setting['total_hp_tahunan'];
        $hpTerpakai = $this->kegiatanModel->getTotalHpByPkpt($kegiatan['pkpt_id'], $id);
        $hpSisa     = max(0, $hpEfektif - $hpTerpakai);
        $hpDiajukan = array_sum(array_map('intval', (array)($this->request->getPost('tim_hp') ?? [])));
        if ($hpDiajukan > 0 && $hpDiajukan > $hpSisa) {
            return redirect()->back()->withInput()
                ->with('error', "Total HP tim ({$hpDiajukan} hari) melebihi sisa HP PKPT yang tersedia ({$hpSisa} hari).");
        }

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
