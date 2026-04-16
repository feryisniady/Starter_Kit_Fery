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
        $tahun   = (int)($this->request->getGet('tahun') ?? $this->settingModel->getTahunAktif());
        $isAdmin = $this->isAdmin();

        $myPkpt = null;
        if (!$isAdmin) {
            $irbanId = $this->getUserIrbanId(session()->get('user_id'));
            if ($irbanId) {
                $myPkpt = $this->pkptModel->getByIrbanTahun($irbanId, $tahun);
            }
        }

        return view('admin/pkpt/index', [
            'title'          => 'PKPT ' . $tahun,
            'tahun'          => $tahun,
            'settings'       => $this->settingModel->orderBy('tahun', 'DESC')->findAll(),
            'currentSetting' => $this->settingModel->getByTahun($tahun),
            'irbanList'      => (new IrbanModel())->orderBy('kode')->findAll(),
            'isAdmin'        => $isAdmin,
            'myPkpt'         => $myPkpt,
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

        $setting          = $this->settingModel->getByTahun((int)$pkpt['tahun']);
        $hpGlobalTerpakai = $this->kegiatanModel->getTotalHpByTahun((int)$pkpt['tahun']);
        $hpEfektif        = (new HariLiburModel())->hitungHariKerjaTahun((int)$pkpt['tahun']);
        if ($hpEfektif === 0 && $setting) $hpEfektif = (int)$setting['total_hp_tahunan'];

        return view('admin/pkpt/show', [
            'title'           => 'Detail PKPT — ' . $pkpt['irban_nama'] . ' ' . $pkpt['tahun'],
            'pkpt'            => $pkpt,
            'kegiatan'        => $this->kegiatanModel->getByPkpt($id),
            'setting'         => $setting,
            'hpEfektif'       => $hpEfektif,
            'hpGlobalTerpakai'=> $hpGlobalTerpakai,
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

        $hpTerpakai = $this->kegiatanModel->getTotalHpByTahun((int)$pkpt['tahun']);

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
        // 1. Validasi Akses & Eksistensi PKPT
        $pkpt = $this->pkptModel->getWithIrban($pkptId);
        if (!$pkpt || !$this->canAccessPkpt($pkpt)) {
            return redirect()->to('/admin/pkpt')->with('error', 'Akses ditolak atau data tidak ditemukan.');
        }

        // 2. Definisi Rules Validasi (Termasuk Logic Tanggal)
        $rules = [
            'area_pengawasan'  => 'required',
            'jenis_pengawasan' => 'required',
            'tujuan_sasaran'   => 'required',
            'tanggal_mulai'    => 'required|valid_date',
            'tanggal_selesai'  => 'required|valid_date',
            'entitas_ids'      => 'required', // Minimal pilih satu entitas
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
            ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        // Validasi tambahan: Tanggal Selesai tidak boleh sebelum Tanggal Mulai
        $tglMulai   = $this->request->getPost('tanggal_mulai');
        $tglSelesai = $this->request->getPost('tanggal_selesai');
        if (strtotime($tglSelesai) < strtotime($tglMulai)) {
            return redirect()->back()->withInput()->with('error', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
        }

        // 3. Kalkulasi & Cek Sisa Hari Pengawasan (HP)
        $tahun      = (int)$pkpt['tahun'];
        $setting    = $this->settingModel->getByTahun($tahun);
        $hpEfektif  = (new HariLiburModel())->hitungHariKerjaTahun($tahun);

        if ($hpEfektif === 0 && $setting) {
            $hpEfektif = (int)$setting['total_hp_tahunan'];
        }

        $hpTerpakai = $this->kegiatanModel->getTotalHpByTahun($tahun);
        $hpSisa     = max(0, $hpEfektif - $hpTerpakai);

        $timData    = $this->parseTimPost();
        $hpDiajukan = array_sum(array_column($timData, 'hp_total'));

        if ($hpDiajukan > $hpSisa) {
            return redirect()->back()->withInput()
            ->with('error', "Total HP tim ({$hpDiajukan} hari) melebihi sisa HP PKPT ({$hpSisa} hari).");
        }

        // 4. Proses Simpan dengan Database Transaction (Atomic Operation)
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $kode = $this->kegiatanModel->generateKode($pkptId);

            // Simpan Main Kegiatan
            $kegiatanData = [
                'pkpt_id'          => $pkptId,
                'kode_kegiatan'    => $kode,
                'area_pengawasan'  => $this->request->getPost('area_pengawasan'),
                'jenis_pengawasan' => $this->request->getPost('jenis_pengawasan'),
                'tujuan_sasaran'   => $this->request->getPost('tujuan_sasaran'),
                'ruang_lingkup'    => $this->request->getPost('ruang_lingkup'),
                'risiko_audit'     => $this->request->getPost('risiko_audit') ?: 'sedang',
                'jadwal_rmp'       => $this->request->getPost('jadwal_rmp'),
                'jadwal_rpl'       => $this->request->getPost('jadwal_rpl'),
                'tanggal_mulai'    => $tglMulai,
                'tanggal_selesai'  => $tglSelesai,
                'jumlah_laporan'   => (int)$this->request->getPost('jumlah_laporan') ?: 1,
                'sarana_prasarana' => $this->request->getPost('sarana_prasarana'),
                'status'           => 'aktif',
                'created_by'       => session()->get('user_id'),
            ];

            $kegiatanId = $this->kegiatanModel->insert($kegiatanData);
            if (!$kegiatanId) throw new \Exception("Gagal menyimpan data kegiatan.");

            // Simpan Entitas (Multi-insert)
            $entitasIds = (array)$this->request->getPost('entitas_ids');
            $batchEntitas = [];
            foreach ($entitasIds as $eid) {
                $batchEntitas[] = [
                    'pkpt_kegiatan_id' => $kegiatanId,
                    'entitas_id'       => (int)$eid
                ];
            }
            $db->table('pkpt_entitas')->insertBatch($batchEntitas);

            // Simpan Tim & Hitung Anggaran Berdasarkan Tarif
            $tarif = $setting ? (int)$setting['tarif_hp'] : 160000;
            if (!empty($timData)) {
                $this->timModel->saveTimKegiatan((int)$kegiatanId, $timData, $tarif);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Transaksi database gagal (rollback).");
            }

            // 5. Logging & Response
            logActivity('pkpt.kegiatan.create', 'pkpt_kegiatan', "Tambah kegiatan {$kode} pada PKPT ID {$pkptId}");
            return redirect()->to('/admin/pkpt/' . $pkptId)->with('success', "Kegiatan {$kode} berhasil ditambahkan.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function viewKegiatan(int $id)
    {
        $kegiatan = $this->kegiatanModel->getDetail($id);
        if (!$kegiatan) return $this->response->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);

        $pkpt = $this->pkptModel->getWithIrban($kegiatan['pkpt_id']);
        if (!$this->canAccessPkpt($pkpt)) return $this->response->setStatusCode(403);

        return $this->response->setJSON(['success' => true, 'data' => $kegiatan]);
    }

    public function editKegiatan(int $id)
    {
        $kegiatan = $this->kegiatanModel->getDetail($id);
        if (!$kegiatan) {
            return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');
        }

        $pkpt = $this->pkptModel->getWithIrban($kegiatan['pkpt_id']);
        if (!$this->canAccessPkpt($pkpt)) {
            return redirect()->to('/admin/pkpt')->with('error', 'Akses ditolak.');
        }

        $tahun = (int)$pkpt['tahun'];
        $setting = $this->settingModel->getByTahun($tahun);

        // Ambil HP Efektif (Fresh dari HariLibur atau Fallback ke Setting)
        $hpEfektif = (new HariLiburModel())->hitungHariKerjaTahun($tahun);
        if ($hpEfektif === 0 && $setting) {
            $hpEfektif = (int)$setting['total_hp_tahunan'];
        }

        // Hitung sisa HP organisasi (kecualikan kegiatan ini sendiri agar tidak double counting)
        $hpTerpakai = $this->kegiatanModel->getTotalHpByTahun($tahun, $id);

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
        if (!$kegiatan) {
            return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');
        }

        $pkpt = $this->pkptModel->getWithIrban($kegiatan['pkpt_id']);
        if (!$this->canAccessPkpt($pkpt)) {
            return redirect()->to('/admin/pkpt')->with('error', 'Akses ditolak.');
        }

    // 1. Validasi Input Dasar
        $rules = [
            'area_pengawasan'  => 'required',
            'jenis_pengawasan' => 'required',
            'tujuan_sasaran'   => 'required',
            'tanggal_mulai'    => 'required|valid_date',
            'tanggal_selesai'  => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode('<br>', $this->validator->getErrors()));
        }

    // Validasi Logika Tanggal
        $tglMulai   = $this->request->getPost('tanggal_mulai');
        $tglSelesai = $this->request->getPost('tanggal_selesai');
        if (strtotime($tglSelesai) < strtotime($tglMulai)) {
            return redirect()->back()->withInput()->with('error', 'Tanggal selesai tidak boleh sebelum tanggal mulai.');
        }

    // 2. Cek Sisa HP Organisasi (Excluding current activity)
        $tahun      = (int)$pkpt['tahun'];
        $setting    = $this->settingModel->getByTahun($tahun);
        $hpEfektif  = (new HariLiburModel())->hitungHariKerjaTahun($tahun);
        if ($hpEfektif === 0 && $setting) $hpEfektif = (int)$setting['total_hp_tahunan'];

        $hpTerpakai = $this->kegiatanModel->getTotalHpByTahun($tahun, $id);
        $hpSisa     = max(0, $hpEfektif - $hpTerpakai);

        $timData    = $this->parseTimPost();
        $hpDiajukan = array_sum(array_column($timData, 'hp_total'));

        if ($hpDiajukan > $hpSisa) {
            return redirect()->back()->withInput()
            ->with('error', "Total HP tim ({$hpDiajukan} hari) melebihi sisa HP PKPT ({$hpSisa} hari).");
        }

        // 3. Eksekusi Update dengan Transaksi
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update Table Utama
            $this->kegiatanModel->update($id, [
                'area_pengawasan'  => $this->request->getPost('area_pengawasan'),
                'jenis_pengawasan' => $this->request->getPost('jenis_pengawasan'),
                'tujuan_sasaran'   => $this->request->getPost('tujuan_sasaran'),
                'ruang_lingkup'    => $this->request->getPost('ruang_lingkup'),
                'risiko_audit'     => $this->request->getPost('risiko_audit') ?: 'sedang',
                'jadwal_rmp'       => $this->request->getPost('jadwal_rmp'),
                'jadwal_rpl'       => $this->request->getPost('jadwal_rpl'),
                'tanggal_mulai'    => $tglMulai,
                'tanggal_selesai'  => $tglSelesai,
                'jumlah_laporan'   => (int)$this->request->getPost('jumlah_laporan') ?: 1,
                'sarana_prasarana' => $this->request->getPost('sarana_prasarana'),
                'updated_by'       => session()->get('user_id')
            ]);

            // Update Entitas (Delete old, Insert Batch new)
            $db->table('pkpt_entitas')->where('pkpt_kegiatan_id', $id)->delete();
            $entitasIds = (array)$this->request->getPost('entitas_ids');
            if (!empty($entitasIds)) {
                $batchEntitas = array_map(fn($eid) => [
                    'pkpt_kegiatan_id' => $id,
                    'entitas_id'       => (int)$eid
                ], $entitasIds);
                $db->table('pkpt_entitas')->insertBatch($batchEntitas);
            }

            // Update Tim
            $tarif = $setting ? (int)$setting['tarif_hp'] : 160000;
            $this->timModel->saveTimKegiatan($id, $timData, $tarif);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Gagal melakukan update pada database.");
            }

            logActivity('pkpt.kegiatan.update', 'pkpt_kegiatan', "Update kegiatan ID {$id}");
            return redirect()->to('/admin/pkpt/' . $pkpt['id'])->with('success', 'Kegiatan berhasil diperbarui.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * AJAX DataTable: daftar kegiatan per PKPT
     */
    public function getDataKegiatan(int $pkptId)
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        $pkpt = $this->pkptModel->getWithIrban($pkptId);
        if (!$pkpt || !$this->canAccessPkpt($pkpt)) return $this->response->setStatusCode(403);

        ['draw'=>$draw,'start'=>$start,'length'=>$length,'search'=>$search,'order'=>$order] = $this->dtRequest();

        $db    = \Config\Database::connect();
        $baseQ = $db->table('pkpt_kegiatan pk')
            ->select("pk.id, pk.kode_kegiatan, pk.area_pengawasan, pk.jenis_pengawasan,
                      pk.risiko_audit, pk.tanggal_mulai, pk.tanggal_selesai, pk.status,
                      COALESCE(SUM(pt.hp_total),0) as total_hp,
                      COALESCE(SUM(pt.anggaran),0) as total_anggaran,
                      COUNT(DISTINCT s.id) as jumlah_spt,
                      SUM(CASE WHEN s.status='terbit' THEN 1 ELSE 0 END) as spt_terbit")
            ->join('pkpt_tim pt', 'pt.pkpt_kegiatan_id = pk.id', 'left')
            ->join('spt s',       's.pkpt_kegiatan_id = pk.id', 'left')
            ->where('pk.pkpt_id', $pkptId)
            ->groupBy('pk.id');

        $total = (clone $baseQ)->countAllResults(false);

        if ($search) {
            $baseQ->groupStart()
                ->like('pk.area_pengawasan', $search)
                ->orLike('pk.jenis_pengawasan', $search)
                ->orLike('pk.kode_kegiatan', $search)
                ->groupEnd();
        }

        $filtered = $search ? (clone $baseQ)->countAllResults(false) : $total;
        $rows = $baseQ->orderBy('pk.kode_kegiatan')->limit($length, $start)->get()->getResultArray();

        $riskColor = ['rendah'=>'success','sedang'=>'warning','tinggi'=>'danger'];

        $data = [];
        foreach ($rows as $i => $row) {
            $riskBadge = '<span class="badge badge-'.($riskColor[$row['risiko_audit']]??'secondary').'">'.ucfirst($row['risiko_audit']).'</span>';
            $sptBadge  = $row['spt_terbit'] > 0
                ? '<span class="badge badge-success"><i class="fas fa-check"></i> Terbit</span>'
                : ($row['jumlah_spt'] > 0
                    ? '<span class="badge badge-info">'.$row['jumlah_spt'].' Proses</span>'
                    : '<span class="badge badge-secondary">Belum ada SPT</span>');
            $periode = $row['tanggal_mulai']
                ? date('d/m/Y', strtotime($row['tanggal_mulai'])).' s.d. '.date('d/m/Y', strtotime($row['tanggal_selesai']))
                : '<span style="color:#94a3b8">—</span>';

            $data[] = [
                'no'      => $start + $i + 1,
                'kode'    => '<span class="badge badge-primary">'.esc($row['kode_kegiatan']).'</span>',
                'area'    => '<div style="font-size:13px;font-weight:500;color:#1e293b">'.esc($row['area_pengawasan']).'</div>'
                           . '<div style="font-size:11px;color:#64748b">'.esc($row['jenis_pengawasan']).'</div>',
                'risiko'  => $riskBadge,
                'periode' => '<span style="font-size:12px">'.$periode.'</span>',
                'hp'      => '<span style="font-weight:700;color:#6366f1">'.$row['total_hp'].'</span> <span style="font-size:11px;color:#94a3b8">hari</span>',
                'spt'     => $sptBadge,
                'aksi'    => $this->dtActions([
                    ['type'=>'info',    'icon'=>'fa-eye',           'title'=>'Detail',      'href'=>'#', 'extra'=>'onclick="viewKegiatan('.$row['id'].')" '],
                    ['type'=>'warning', 'icon'=>'fa-edit',          'title'=>'Edit',        'href'=>'/admin/pkpt/kegiatan/edit/'.$row['id']],
                    ['type'=>'success', 'icon'=>'fa-file-signature','title'=>'Buat SPT',    'href'=>'/admin/spt/create/'.$row['id']],
                    ['type'=>'danger',  'icon'=>'fa-trash',         'title'=>'Hapus',       'href'=>'#', 'extra'=>'onclick="delKegiatan('.$row['id'].')" '],
                ]),
            ];
        }

        return $this->dtResponse($draw, $total, $filtered, $data);
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
     * AJAX: monitoring sisa HP organisasi per irban untuk tahun tertentu.
     */
    public function hpMonitor()
    {
        $tahun   = (int)($this->request->getGet('tahun') ?? $this->settingModel->getTahunAktif());
        $setting = $this->settingModel->getByTahun($tahun);

        // Hitung HP efektif secara dinamis dari data hari libur (selalu fresh)
        $hpEfektif = (new HariLiburModel())->hitungHariKerjaTahun($tahun);
        if ($hpEfektif === 0 && $setting) $hpEfektif = (int)$setting['total_hp_tahunan'];

        $db   = \Config\Database::connect();
        $rows = $db->table('irban i')
        ->select("i.id, i.kode as irban_kode, i.nama as irban_nama,
            COUNT(DISTINCT pk.id) as jumlah_kegiatan,
            COALESCE(SUM(pt.hp_total), 0) as hp_terpakai")
        ->join('pkpt p',          "p.irban_id = i.id AND p.tahun = {$tahun}", 'left')
        ->join('pkpt_kegiatan pk',"pk.pkpt_id = p.id AND pk.status != 'batal'", 'left')
        ->join('pkpt_tim pt',     'pt.pkpt_kegiatan_id = pk.id', 'left')
        ->groupBy('i.id, i.kode, i.nama')
        ->orderBy('i.kode')
        ->get()->getResultArray();

        $hpTerpakai = (int)array_sum(array_column($rows, 'hp_terpakai'));
        $hpSisa     = max(0, $hpEfektif - $hpTerpakai);
        $pct        = $hpEfektif > 0 ? round(($hpTerpakai / $hpEfektif) * 100, 1) : 0;
        $irbanAktif = count(array_filter($rows, fn($r) => (int)$r['hp_terpakai'] > 0));

        return $this->response->setJSON([
            'tahun'              => $tahun,
            'hp_efektif'         => $hpEfektif,
            'hp_terpakai_global' => $hpTerpakai,
            'hp_sisa'            => $hpSisa,
            'pct'                => $pct,
            'irban_aktif'        => $irbanAktif,
            'updated_at'         => date('d M Y H:i:s'),
            'irbans'             => array_map(fn($r) => [
                'kode'        => $r['irban_kode'],
                'nama'        => $r['irban_nama'],
                'kegiatan'    => (int)$r['jumlah_kegiatan'],
                'hp_terpakai' => (int)$r['hp_terpakai'],
                'pct'         => $hpEfektif > 0 ? round(((int)$r['hp_terpakai'] / $hpEfektif) * 100, 1) : 0,
            ], $rows),
        ]);
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
        return hasRole('superadmin') || hasRole('admin') || hasPermission('pkpt.manage_all');
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
