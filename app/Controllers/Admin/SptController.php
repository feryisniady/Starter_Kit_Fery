<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SptModel;
use App\Models\SptTimModel;
use App\Models\SptApprovalModel;
use App\Models\PkptKegiatanModel;
use App\Models\PkptModel;
use App\Models\PkptSettingModel;
use App\Models\SdmModel;
use App\Models\IrbanModel;
use App\Models\TemuanModel;
use App\Models\SptKmModel;
use App\Traits\DatatableTrait;

class SptController extends BaseController
{
    use DatatableTrait;
    protected SptModel          $sptModel;
    protected SptTimModel       $timModel;
    protected TemuanModel       $temuanModel;
    protected SptKmModel        $kmModel;
    protected SptApprovalModel  $approvalModel;
    protected PkptKegiatanModel $kegiatanModel;
    protected PkptModel         $pkptModel;
    protected PkptSettingModel  $settingModel;
    protected SdmModel          $sdmModel;
    protected IrbanModel        $irbanModel;

    public function __construct()
    {
        $this->sptModel      = new SptModel();
        $this->timModel      = new SptTimModel();
        $this->approvalModel = new SptApprovalModel();
        $this->kegiatanModel = new PkptKegiatanModel();
        $this->pkptModel     = new PkptModel();
        $this->settingModel  = new PkptSettingModel();
        $this->sdmModel      = new SdmModel();
        $this->irbanModel    = new IrbanModel();
        $this->temuanModel   = new TemuanModel();
        $this->kmModel       = new SptKmModel();
    }

    // ===================================================
    // LIST
    // ===================================================

    public function index()
    {
        return view('admin/spt/index', [
            'title'       => 'Surat Perintah Tugas (SPT)',
            'statusLabel' => SptModel::$statusLabel,
            'tahunAktif'  => $this->settingModel->getTahunAktif(),
            'settings'    => $this->settingModel->orderBy('tahun', 'DESC')->findAll(),
        ]);
    }

    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw' => $draw, 'start' => $start, 'length' => $length, 'search' => $search] = $this->dtRequest();

        $userId  = session()->get('user_id');
        $isAdmin = $this->isAdmin();
        $tahun   = (int)($this->request->getPost('tahun') ?: $this->settingModel->getTahunAktif());
        $status  = $this->request->getPost('status') ?? '';
        $irbanId = $isAdmin ? null : $this->getUserIrbanId($userId);

        if (!$isAdmin && $irbanId === null) {
            return $this->dtResponse($draw, 0, 0, []);
        }

        $db = \Config\Database::connect();

        // LEFT JOIN agar Non-PKPT (pkpt_kegiatan_id NULL) tetap tampil
        $buildBase = function() use ($db, $tahun, $irbanId, $status) {
            $q = $db->table('spt s')
                ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id', 'left')
                ->join('pkpt p',           'p.id = pk.pkpt_id',          'left')
                ->join('irban i_pkpt',     'i_pkpt.id = p.irban_id',     'left')
                ->join('irban i_spt',      'i_spt.id = s.irban_id',      'left')
                ->groupStart()
                    ->where('p.tahun',  $tahun)
                    ->orWhere('s.tahun', $tahun)
                ->groupEnd();
            if ($irbanId) {
                $q->groupStart()
                    ->where('p.irban_id',  $irbanId)
                    ->orWhere('s.irban_id', $irbanId)
                ->groupEnd();
            }
            if ($status) $q->where('s.status', $status);
            return $q;
        };

        $total = $buildBase()->countAllResults();

        $q = $buildBase()
            ->select('s.id, s.nomor_naskah, s.nama_tim, s.tanggal_mulai, s.tujuan, s.status,
                      s.jenis_spt, s.jenis_non_pkpt,
                      COALESCE(pk.kode_kegiatan, s.jenis_non_pkpt) as kode_kegiatan,
                      COALESCE(i_pkpt.nama, i_spt.nama) as irban_nama');

        if ($search) {
            $q->groupStart()
                ->like('s.nomor_naskah',  $search)
                ->orLike('pk.kode_kegiatan', $search)
                ->orLike('s.jenis_non_pkpt', $search)
                ->orLike('s.tujuan',         $search)
                ->orLike('i_pkpt.nama',      $search)
                ->orLike('i_spt.nama',       $search)
            ->groupEnd();
        }

        $filtered = $search ? $q->countAllResults(false) : $total;
        $rows     = $q->orderBy('s.created_at', 'DESC')->limit($length, $start)->get()->getResultArray();

        $sl = SptModel::$statusLabel;
        $sc = SptModel::$statusColor;

        $data = array_map(function ($r) use ($sl, $sc) {
            $isNonPkpt = ($r['jenis_spt'] ?? 'pkpt') === 'non_pkpt';
            $badge     = '<span class="badge badge-' . ($sc[$r['status']] ?? 'secondary') . '">'
                       . esc($sl[$r['status']] ?? $r['status']) . '</span>';
            $actions   = '<a href="/admin/spt/' . $r['id'] . '" class="btn btn-xs btn-primary">Detail</a>';
            if ($r['status'] === 'terbit') {
                $actions .= ' <a href="/admin/spt/' . $r['id'] . '/word" class="btn btn-xs btn-success"><i class="fas fa-file-word"></i></a>';
            }
            $kodeHtml = $isNonPkpt
                ? '<span class="badge badge-warning" style="font-size:11px"><i class="fas fa-star"></i> Non-PKPT</span>'
                  . '<br><span style="font-size:11px;color:#475569">' . esc($r['kode_kegiatan'] ?? '—') . '</span>'
                : '<span class="badge badge-primary">' . esc($r['kode_kegiatan'] ?? '—') . '</span>';
            if ($r['nama_tim']) {
                $kodeHtml .= ' <span class="badge badge-warning" style="font-size:10px">' . esc($r['nama_tim']) . '</span>';
            }
            return [
                'nomor_naskah'  => esc($r['nomor_naskah'] ?: '—'),
                'kode_kegiatan' => $kodeHtml,
                'irban_nama'    => esc($r['irban_nama'] ?? '—'),
                'tujuan'        => '<span style="display:block;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' . esc($r['tujuan']) . '">' . esc($r['tujuan']) . '</span>',
                'tanggal_mulai' => $r['tanggal_mulai'] ? date('d/m/Y', strtotime($r['tanggal_mulai'])) : '—',
                'status'        => $badge,
                'aksi'          => $actions,
            ];
        }, $rows);

        return $this->dtResponse($draw, $total, $filtered, $data);
    }

    // ===================================================
    // SPT NON-PKPT (Mandatory)
    // ===================================================

    public function createNonPkpt()
    {
        if (!$this->isAdmin()) {
            $irbanId = $this->getUserIrbanId(session()->get('user_id'));
            if ($irbanId === null) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        return view('admin/spt/form_non_pkpt', [
            'title'       => 'Buat SPT Non-PKPT / Mandatori',
            'irbanList'   => $this->irbanModel->orderBy('kode')->findAll(),
            'sdmAll'      => $this->sdmModel->getAktif(),
            'sdmPenanda'  => $this->sdmModel->getAktif(),
            'jenisOpts'   => SptModel::$jenisNonPkpt,
            'tahunAktif'  => $this->settingModel->getTahunAktif(),
            'spt'         => null,
            'setting'     => $this->settingModel->getByTahun($this->settingModel->getTahunAktif()),
            'entitasList' => $db->table('entitas')->where('aktif', 1)->orderBy('nama')->get()->getResultArray(),
        ]);
    }

    public function storeNonPkpt()
    {
        $rules = ['tanggal_naskah' => 'required', 'tujuan' => 'required',
                  'irban_id' => 'required', 'tahun' => 'required'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        if (!$this->isAdmin()) {
            $myIrbanId = $this->getUserIrbanId(session()->get('user_id'));
            $postIrban = (int)$this->request->getPost('irban_id');
            if ($myIrbanId === null || $myIrbanId !== $postIrban) {
                return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');
            }
        }

        $entitasId = (int)($this->request->getPost('entitas_id') ?: 0) ?: null;

        $sptId = $this->sptModel->insert([
            'pkpt_kegiatan_id' => null,
            'jenis_spt'        => 'non_pkpt',
            'irban_id'         => (int)$this->request->getPost('irban_id'),
            'tahun'            => (int)$this->request->getPost('tahun'),
            'jenis_non_pkpt'   => $this->request->getPost('jenis_non_pkpt'),
            'entitas_id'       => $entitasId,
            'nama_tim'         => trim($this->request->getPost('nama_tim') ?? '') ?: null,
            'nomor_naskah'     => $this->request->getPost('nomor_naskah'),
            'tanggal_naskah'   => $this->request->getPost('tanggal_naskah'),
            'dasar_1'          => $this->request->getPost('dasar_1'),
            'dasar_2'          => $this->request->getPost('dasar_2') ?: null,
            'tujuan'           => $this->request->getPost('tujuan'),
            'tanggal_mulai'    => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai'  => $this->request->getPost('tanggal_selesai') ?: null,
            'tembusan'         => $this->request->getPost('tembusan'),
            'penandatangan_id' => $this->request->getPost('penandatangan_id') ?: null,
            'status'           => 'draft',
            'created_by'       => session()->get('user_id'),
        ]);

        $timData = $this->parseTimPost();
        if ($timData) $this->timModel->saveTimSpt((int)$sptId, $timData);
        $this->approvalModel->initApprovals((int)$sptId);

        logActivity('spt.create.non_pkpt', 'spt', "Buat SPT Non-PKPT id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId)->with('success', 'SPT Non-PKPT berhasil dibuat.');
    }

    // ===================================================
    // GENERATE SPT DARI PKPT
    // ===================================================

    public function create(int $pkptKegiatanId)
    {
        $kegiatan = $this->kegiatanModel->getDetail($pkptKegiatanId);
        if (!$kegiatan) return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');

        $pkpt    = $this->pkptModel->getWithIrban($kegiatan['pkpt_id']);

        // Irban user hanya boleh buat SPT untuk irbannya sendiri
        if (!$this->isAdmin()) {
            $myIrbanId = $this->getUserIrbanId(session()->get('user_id'));
            if ($myIrbanId === null || (int)$pkpt['irban_id'] !== $myIrbanId) {
                return redirect()->back()->with('error', 'Akses ditolak.');
            }
        }
        $setting = $this->settingModel->getByTahun((int)$pkpt['tahun']);

        // SPT yang sudah ada untuk kegiatan ini (multi-tim context)
        $db           = \Config\Database::connect();
        $existingSpts = $db->table('spt')
            ->select('id, nomor_naskah, nama_tim, status, tanggal_mulai')
            ->where('pkpt_kegiatan_id', $pkptKegiatanId)
            ->orderBy('id')
            ->get()->getResultArray();

        $hpAllocated  = $this->timModel->getHpAllocatedByKegiatan($pkptKegiatanId);
        $sptCount     = count($existingSpts);

        // Auto-suggest nama tim: Tim A, Tim B, Tim C …
        $timLabels     = range('A', 'Z');
        $suggestedNama = 'Tim ' . ($timLabels[$sptCount] ?? ($sptCount + 1));

        // Tim default: sisa HP jika sudah ada SPT lain, otherwise full HP
        $timDefault = $sptCount > 0
            ? $this->timModel->buildFromPkptTimWithSisa($pkptKegiatanId, $hpAllocated)
            : $this->timModel->buildFromPkptTim($pkptKegiatanId);

        // Dasar 1 otomatis dari setting
        $dasar1 = '';
        if ($setting && $setting['nomor_pkpt']) {
            $dasar1 = 'Program Kerja Pengawasan Tahunan (PKPT) Inspektorat Daerah Kabupaten Sampang Tahun '
                . $pkpt['tahun'] . ' tanggal '
                . ($setting['tanggal_pkpt'] ? tgl_indo($setting['tanggal_pkpt']) : '...')
                . ' Nomor : ' . $setting['nomor_pkpt'] . ', dengan ini :';
        }

        return view('admin/spt/form', [
            'title'         => 'Generate SPT — ' . $kegiatan['kode_kegiatan'],
            'kegiatan'      => $kegiatan,
            'pkpt'          => $pkpt,
            'setting'       => $setting,
            'timDefault'    => $timDefault,
            'sdmAll'        => $this->sdmModel->getAktif(),
            'sdmPenanda'    => $this->sdmModel->getAktif(),
            'dasar1'        => $dasar1,
            'spt'           => null,
            'existingSpts'  => $existingSpts,
            'hpAllocated'   => $hpAllocated,
            'suggestedNama' => $suggestedNama,
        ]);
    }

    public function store(int $pkptKegiatanId)
    {
        $kegiatan = $this->kegiatanModel->find($pkptKegiatanId);
        if (!$kegiatan) return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');

        // Irban user hanya boleh buat SPT untuk irbannya sendiri
        if (!$this->isAdmin()) {
            $pkpt      = $this->pkptModel->find($kegiatan['pkpt_id']);
            $myIrbanId = $this->getUserIrbanId(session()->get('user_id'));
            if (!$pkpt || $myIrbanId === null || (int)$pkpt['irban_id'] !== $myIrbanId) {
                return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');
            }
        }

        $rules = ['tanggal_naskah' => 'required', 'tujuan' => 'required'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $sptId = $this->sptModel->insert([
            'pkpt_kegiatan_id' => $pkptKegiatanId,
            'nama_tim'         => trim($this->request->getPost('nama_tim') ?? '') ?: null,
            'nomor_naskah'     => $this->request->getPost('nomor_naskah'),
            'tanggal_naskah'   => $this->request->getPost('tanggal_naskah'),
            'dasar_1'          => $this->request->getPost('dasar_1'),
            'dasar_2'          => $this->request->getPost('dasar_2') ?: null,
            'tujuan'           => $this->request->getPost('tujuan'),
            'tanggal_mulai'    => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai'  => $this->request->getPost('tanggal_selesai') ?: null,
            'tembusan'         => $this->request->getPost('tembusan'),
            'penandatangan_id' => $this->request->getPost('penandatangan_id') ?: null,
            'status'           => 'draft',
            'created_by'       => session()->get('user_id'),
        ]);

        // Simpan tim SPT
        $timData = $this->parseTimPost();
        if ($timData) $this->timModel->saveTimSpt((int)$sptId, $timData);

        // Init approval records
        $this->approvalModel->initApprovals((int)$sptId);

        logActivity('spt.create', 'spt', "Generate SPT dari kegiatan id={$pkptKegiatanId}");
        return redirect()->to('/admin/spt/' . $sptId)->with('success', 'SPT berhasil dibuat.');
    }

    // ===================================================
    // DETAIL & EDIT
    // ===================================================

    public function show(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSpt($spt)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        // Edit hanya boleh saat draft
        $canEdit = ($spt['status'] === 'draft');

        // Approve hanya untuk role yang sesuai tahap saat ini
        $approvalRoles = [
            'diajukan'       => ['irban', 'ka_irban', 'superadmin', 'admin'],
            'acc_irban'      => ['evlap', 'subbag_evlap', 'superadmin', 'admin'],
            'acc_evlap'      => ['sekretaris', 'superadmin', 'admin'],
            'acc_sekretaris' => ['inspektur', 'superadmin', 'admin'],
        ];
        $canApproveNow = false;
        foreach (($approvalRoles[$spt['status']] ?? []) as $role) {
            if (hasRole($role)) { $canApproveNow = true; break; }
        }

        return view('admin/spt/show', [
            'title'          => 'Detail SPT — ' . ($spt['nomor_naskah'] ?: '#' . $id),
            'spt'            => $spt,
            'statusLabel'    => SptModel::$statusLabel,
            'statusColor'    => SptModel::$statusColor,
            'temuanSummary'  => $this->temuanModel->getSummaryBySpt($id),
            'kmChecklist'    => $this->kmModel->getChecklist($id),
            'canEdit'        => $canEdit,
            'canApproveNow'  => $canApproveNow,
        ]);
    }

    public function edit(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSpt($spt)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        if (!in_array($spt['status'], ['draft'])) {
            return redirect()->to('/admin/spt/' . $id)->with('error', 'SPT sudah diajukan, tidak bisa diedit.');
        }

        if (($spt['jenis_spt'] ?? 'pkpt') === 'non_pkpt') {
            return view('admin/spt/form_non_pkpt', [
                'title'     => 'Edit SPT Non-PKPT — ' . ($spt['nomor_naskah'] ?: '#' . $id),
                'irbanList' => $this->irbanModel->orderBy('kode')->findAll(),
                'sdmAll'    => $this->sdmModel->getAktif(),
                'sdmPenanda'=> $this->sdmModel->getAktif(),
                'jenisOpts' => SptModel::$jenisNonPkpt,
                'tahunAktif'=> (int)($spt['tahun'] ?? $this->settingModel->getTahunAktif()),
                'spt'       => $spt,
                'setting'   => $this->settingModel->getByTahun((int)($spt['tahun'] ?? $this->settingModel->getTahunAktif())),
            ]);
        }

        $kegiatan = $this->kegiatanModel->getDetail($spt['pkpt_kegiatan_id']);
        $pkpt     = $this->pkptModel->getWithIrban($kegiatan['pkpt_id']);

        $db           = \Config\Database::connect();
        $existingSpts = $db->table('spt')
            ->select('id, nomor_naskah, nama_tim, status, tanggal_mulai')
            ->where('pkpt_kegiatan_id', $spt['pkpt_kegiatan_id'])
            ->where('id !=', $id)
            ->orderBy('id')
            ->get()->getResultArray();
        $hpAllocated = $this->timModel->getHpAllocatedByKegiatan($spt['pkpt_kegiatan_id'], $id);

        return view('admin/spt/form', [
            'title'         => 'Edit SPT — ' . $spt['kode_kegiatan'],
            'kegiatan'      => $kegiatan,
            'pkpt'          => $pkpt,
            'setting'       => $this->settingModel->getByTahun((int)$pkpt['tahun']),
            'timDefault'    => $spt['tim'],
            'sdmAll'        => $this->sdmModel->getAktif(),
            'sdmPenanda'    => $this->sdmModel->getAktif(),
            'dasar1'        => $spt['dasar_1'],
            'spt'           => $spt,
            'existingSpts'  => $existingSpts,
            'hpAllocated'   => $hpAllocated,
            'suggestedNama' => $spt['nama_tim'] ?? null,
        ]);
    }

    public function update(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt || !$this->canAccessSpt($spt)) {
            return redirect()->to('/admin/spt')->with('error', !$spt ? 'SPT tidak bisa diedit.' : 'Akses ditolak.');
        }
        if ($spt['status'] !== 'draft') {
            return redirect()->back()->with('error', 'SPT tidak bisa diedit.');
        }

        $updateData = [
            'nama_tim'        => trim($this->request->getPost('nama_tim') ?? '') ?: null,
            'nomor_naskah'    => $this->request->getPost('nomor_naskah'),
            'tanggal_naskah'  => $this->request->getPost('tanggal_naskah'),
            'dasar_1'         => $this->request->getPost('dasar_1'),
            'dasar_2'         => $this->request->getPost('dasar_2') ?: null,
            'tujuan'          => $this->request->getPost('tujuan'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai') ?: null,
            'tembusan'        => $this->request->getPost('tembusan'),
            'penandatangan_id'=> $this->request->getPost('penandatangan_id') ?: null,
        ];
        if (($spt['jenis_spt'] ?? 'pkpt') === 'non_pkpt') {
            $updateData['irban_id']       = (int)$this->request->getPost('irban_id');
            $updateData['tahun']          = (int)$this->request->getPost('tahun');
            $updateData['jenis_non_pkpt'] = $this->request->getPost('jenis_non_pkpt');
            $updateData['entitas_id']     = (int)($this->request->getPost('entitas_id') ?: 0) ?: null;
        }
        $this->sptModel->update($id, $updateData);

        $timData = $this->parseTimPost();
        if ($timData) $this->timModel->saveTimSpt($id, $timData);

        logActivity('spt.update', 'spt', "Update SPT id={$id}");
        return redirect()->to('/admin/spt/' . $id)->with('success', 'SPT berhasil diperbarui.');
    }

    // ===================================================
    // WORKFLOW: AJUKAN & APPROVAL
    // ===================================================

    public function ajukan(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt || !$this->canAccessSpt($spt)) {
            return redirect()->to('/admin/spt')->with('error', !$spt ? 'SPT tidak ditemukan.' : 'Akses ditolak.');
        }
        if ($spt['status'] !== 'draft') {
            return redirect()->back()->with('error', 'SPT tidak dalam status draft.');
        }

        // Cek kelengkapan KM Fase 1 (Persiapan) — syarat SPT terbit
        $missing = $this->kmModel->getMissingPhase1Labels($id);
        if (!empty($missing)) {
            $list = implode(', ', $missing);
            return redirect()->to('/admin/spt/' . $id . '/km')
                ->with('error', 'SPT belum dapat diajukan. Selesaikan Fase 1 KM berikut: ' . $list);
        }

        $this->sptModel->update($id, ['status' => 'diajukan']);
        logActivity('spt.ajukan', 'spt', "Ajukan SPT id={$id}");

        $nomorSpt = $spt['nomor_naskah'] ?: 'SPT #' . $id;
        notifyAllAdmins(
            'SPT Diajukan: ' . $nomorSpt,
            'SPT "' . $nomorSpt . '" diajukan oleh ' . session()->get('user_name') . ' — menunggu persetujuan Kepala Irban.',
            '/admin/spt/' . $id,
            'info'
        );

        $msg = '<div style="line-height:1.7">'
             . 'SPT <strong>' . esc($spt['nomor_naskah'] ?: '#' . $id) . '</strong>'
             . ' berhasil diajukan.<br>'
             . '<span style="font-size:13px;color:#64748b">Menunggu persetujuan <strong>Kepala Irban</strong>.</span>'
             . '</div>';
        return redirect()->to('/admin/spt/' . $id)->with('success_modal', $msg);
    }

    public function approve(int $id)
    {
        $spt    = $this->sptModel->getDetail($id);
        $userId = session()->get('user_id');

        if (!$spt || !$this->canAccessSpt($spt)) {
            return redirect()->back()->with('error', !$spt ? 'SPT tidak ditemukan.' : 'Akses ditolak.');
        }

        $tahap     = $this->sptModel->getNextApprovalTahap($spt['status']);
        $nextStatus = $this->sptModel->getNextStatus($spt['status']);

        if (!$tahap || !$nextStatus) {
            return redirect()->back()->with('error', 'Status SPT tidak bisa di-approve.');
        }

        // Cek role sesuai tahap — approval berjenjang
        $allowedRoles = [
            'irban'      => ['irban', 'ka_irban', 'superadmin', 'admin'],
            'evlap'      => ['evlap', 'subbag_evlap', 'superadmin', 'admin'],
            'sekretaris' => ['sekretaris', 'superadmin', 'admin'],
            'inspektur'  => ['inspektur', 'superadmin', 'admin'],
        ];
        $canApprove = false;
        foreach (($allowedRoles[$tahap] ?? []) as $role) {
            if (hasRole($role)) { $canApprove = true; break; }
        }
        if (!$canApprove) {
            $tahapLabel = ['irban'=>'Kepala Irban','evlap'=>'Subbag Evlap','sekretaris'=>'Sekretaris','inspektur'=>'Inspektur'];
            return redirect()->back()->with('error', 'Anda tidak berwenang menyetujui tahap ini. Tahap ini hanya bisa disetujui oleh: ' . ($tahapLabel[$tahap] ?? $tahap));
        }

        $catatan = $this->request->getPost('catatan');

        $this->approvalModel->approve($id, $tahap, $userId, $catatan);
        $this->sptModel->update($id, ['status' => $nextStatus]);

        $label    = SptModel::$statusLabel[$nextStatus] ?? $nextStatus;
        $nomorSpt = $spt['nomor_naskah'] ?: 'SPT #' . $id;
        logActivity('spt.approve', 'spt', "Approve SPT id={$id} → {$nextStatus}");

        // Notifikasi ke pembuat SPT
        $createdBy = $this->sptModel->getCreatedBy($id);
        if ($createdBy) {
            $notifMsg = $nextStatus === 'terbit'
                ? 'SPT "' . $nomorSpt . '" telah TERBIT. Silakan lanjutkan ke tahap pelaksanaan audit.'
                : 'SPT "' . $nomorSpt . '" disetujui oleh ' . session()->get('user_name') . '. Status sekarang: ' . $label;
            notify($createdBy, 'SPT Disetujui: ' . $nomorSpt, $notifMsg, '/admin/spt/' . $id, 'success');
        }

        return redirect()->to('/admin/spt/' . $id)->with('success', "SPT berhasil di-approve. Status: {$label}");
    }

    public function reject(int $id)
    {
        $spt    = $this->sptModel->getDetail($id);
        $userId = session()->get('user_id');

        if (!$spt || !$this->canAccessSpt($spt)) {
            return redirect()->back()->with('error', !$spt ? 'SPT tidak ditemukan.' : 'Akses ditolak.');
        }

        $tahap = $this->sptModel->getNextApprovalTahap($spt['status']);

        if (!$tahap) {
            return redirect()->back()->with('error', 'Status SPT tidak bisa ditolak.');
        }

        // Cek role sesuai tahap
        $allowedRoles = [
            'irban'      => ['irban', 'ka_irban', 'superadmin', 'admin'],
            'evlap'      => ['evlap', 'subbag_evlap', 'superadmin', 'admin'],
            'sekretaris' => ['sekretaris', 'superadmin', 'admin'],
            'inspektur'  => ['inspektur', 'superadmin', 'admin'],
        ];
        $canReject = false;
        foreach (($allowedRoles[$tahap] ?? []) as $role) {
            if (hasRole($role)) { $canReject = true; break; }
        }
        if (!$canReject) {
            $tahapLabel = ['irban'=>'Kepala Irban','evlap'=>'Subbag Evlap','sekretaris'=>'Sekretaris','inspektur'=>'Inspektur'];
            return redirect()->back()->with('error', 'Anda tidak berwenang menolak tahap ini. Tahap ini hanya bisa ditolak oleh: ' . ($tahapLabel[$tahap] ?? $tahap));
        }

        $catatan = $this->request->getPost('catatan') ?: 'Ditolak';

        $this->approvalModel->reject($id, $tahap, $userId, $catatan);
        $this->sptModel->update($id, ['status' => 'ditolak', 'catatan' => $catatan]);

        $nomorSpt = $spt['nomor_naskah'] ?: 'SPT #' . $id;
        logActivity('spt.reject', 'spt', "Tolak SPT id={$id}, catatan: {$catatan}");

        // Notifikasi ke pembuat SPT
        $createdBy = $this->sptModel->getCreatedBy($id);
        if ($createdBy) {
            notify(
                $createdBy,
                'SPT Ditolak: ' . $nomorSpt,
                'SPT "' . $nomorSpt . '" ditolak oleh ' . session()->get('user_name') . '. Catatan: ' . $catatan,
                '/admin/spt/' . $id,
                'danger'
            );
        }

        return redirect()->to('/admin/spt/' . $id)->with('error', 'SPT ditolak. Silakan revisi sesuai catatan dan ajukan kembali.');
    }

    // ===================================================
    // REVISI — kembalikan SPT ditolak ke draft
    // ===================================================

    public function revisi(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt || !$this->canAccessSpt($spt)) {
            return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');
        }
        if ($spt['status'] !== 'ditolak') {
            return redirect()->to('/admin/spt/' . $id)->with('error', 'SPT tidak dalam status ditolak.');
        }

        $this->sptModel->update($id, ['status' => 'draft', 'catatan' => null]);
        logActivity('spt.revisi', 'spt', "Revisi SPT id={$id}");
        return redirect()->to('/admin/spt/' . $id . '/edit')
            ->with('success', 'SPT dikembalikan ke Draft. Silakan perbaiki dan ajukan kembali.');
    }

    // ===================================================
    // GENERATE WORD — format resmi Inspektorat Daerah Kab. Sampang
    // ===================================================

    public function downloadWord(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSpt($spt)) return redirect()->back()->with('error', 'Akses ditolak.');

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $cm  = fn(float $v): int => (int) \PhpOffice\PhpWord\Shared\Converter::cmToTwip($v);
        $jcC = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $jcL = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT];

        $section = $phpWord->addSection([
            'marginTop'    => $cm(2.5),
            'marginBottom' => $cm(2.5),
            'marginLeft'   => $cm(3.0),
            'marginRight'  => $cm(2.5),
        ]);

        // ── Named styles ─────────────────────────────────────
        $noBorder = [
            'borderTopSize' => 0,    'borderTopColor'    => 'FFFFFF',
            'borderBottomSize' => 0, 'borderBottomColor' => 'FFFFFF',
            'borderLeftSize' => 0,   'borderLeftColor'   => 'FFFFFF',
            'borderRightSize' => 0,  'borderRightColor'  => 'FFFFFF',
        ];

        $phpWord->addTableStyle('noBorder', [
            'borderTopSize' => 0,    'borderTopColor'    => 'FFFFFF',
            'borderBottomSize' => 0, 'borderBottomColor' => 'FFFFFF',
            'borderLeftSize' => 0,   'borderLeftColor'   => 'FFFFFF',
            'borderRightSize' => 0,  'borderRightColor'  => 'FFFFFF',
        ]);

        $phpWord->addTableStyle('timBorder', [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 100,
        ]);

        $phpWord->addParagraphStyle('hrThick', [
            'borderBottomSize'  => 18,
            'borderBottomColor' => '000000',
            'spaceBefore'       => 30,
            'spaceAfter'        => 0,
        ]);
        $phpWord->addParagraphStyle('hrThin', [
            'borderBottomSize'  => 4,
            'borderBottomColor' => '000000',
            'spaceBefore'       => 8,
            'spaceAfter'        => 60,
        ]);

        // ── KOP ──────────────────────────────────────────────
        // Usable width = 21 - 3.0 - 2.5 = 15.5 cm
        $logoPath = FCPATH . 'assets/images/logo-sampang.png';
        $hasLogo  = is_file($logoPath);

        $kopTbl = $section->addTable('noBorder');
        $kopTbl->addRow($cm(3.0));

        if ($hasLogo) {
            $lc = $kopTbl->addCell($cm(2.5), $noBorder + ['valign' => 'center']);
            $lc->addImage($logoPath, [
                'width'     => 65,
                'height'    => 65,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
            $tc = $kopTbl->addCell($cm(13.0), $noBorder + ['valign' => 'center']);
        } else {
            $tc = $kopTbl->addCell($cm(15.5), $noBorder + ['valign' => 'center']);
        }
        $tc->addText('PEMERINTAH KABUPATEN SAMPANG', ['bold' => true, 'size' => 11], $jcC);
        $tc->addText('INSPEKTORAT DAERAH', ['bold' => true, 'size' => 14, 'allCaps' => true], $jcC);
        $tc->addText('Jl. Syamsul Arifin No. 1 Sampang  Telp/Fax (0323) 323456', ['size' => 9], $jcC);
        $tc->addText('Email : inspektorat@sampangkab.go.id', ['size' => 9], $jcC);

        $section->addText('', null, 'hrThick');
        $section->addText('', null, 'hrThin');

        // ── JUDUL ────────────────────────────────────────────
        $section->addText('SURAT PERINTAH', ['bold' => true, 'size' => 14],
            $jcC + ['spaceBefore' => 60, 'spaceAfter' => 20]);
        $section->addText(
            'NOMOR : ' . ($spt['nomor_naskah'] ?: '....................................'),
            ['size' => 12],
            $jcC + ['spaceBefore' => 0, 'spaceAfter' => 100]
        );

        // ── DASAR (borderless 3-col: label | : | isi) ────────
        $dtbl = $section->addTable('noBorder');
        $dtbl->addRow();
        $dtbl->addCell($cm(1.8), $noBorder)->addText('Dasar', ['size' => 12], $jcL);
        $dtbl->addCell($cm(0.3), $noBorder)->addText(':', ['size' => 12], $jcL);
        // Hanging indent agar baris kedua sejajar dengan awal teks (bukan angka "1.")
        $pDasar = ['indentation' => ['left' => 320, 'hanging' => 320]];
        $dtbl->addCell($cm(13.4), $noBorder)->addText('1.  ' . ($spt['dasar_1'] ?? ''), ['size' => 12], $pDasar);
        if (!empty($spt['dasar_2'])) {
            $dtbl->addRow();
            $dtbl->addCell($cm(1.8), $noBorder)->addText('');
            $dtbl->addCell($cm(0.3), $noBorder)->addText('');
            $dtbl->addCell($cm(13.4), $noBorder)->addText('2.  ' . $spt['dasar_2'], ['size' => 12], $pDasar);
        }
        $section->addTextBreak(1);

        // ── MEMERINTAHKAN ────────────────────────────────────
        $section->addText('MEMERINTAHKAN :', ['bold' => true, 'underline' => 'single', 'size' => 12], $jcC);
        $section->addTextBreak(0);

        // ── TABEL TIM ─────────────────────────────────────────
        // Col widths: No=1.2 | Nama=5.0 | Jabatan=3.8 | Desk=2.75 | Field=2.75 → total=15.5cm
        $hFont = ['bold' => true, 'size' => 11];
        $hBg   = ['bgColor' => 'D9D9D9'];

        $timTbl = $section->addTable('timBorder');

        // Header row 1: No(rowspan 2) | Nama(rowspan 2) | Jabatan(rowspan 2) | Jumlah Hari(colspan 2)
        $timTbl->addRow(400);
        $timTbl->addCell($cm(1.2),  $hBg + ['vMerge' => 'restart'])->addText('No',              $hFont, $jcC);
        $timTbl->addCell($cm(5.0),  $hBg + ['vMerge' => 'restart'])->addText('Nama / NIP',      $hFont, $jcC);
        $timTbl->addCell($cm(3.8),  $hBg + ['vMerge' => 'restart'])->addText('Jabatan / Peran', $hFont, $jcC);
        $timTbl->addCell($cm(5.5),  $hBg + ['gridSpan' => 2])->addText('Jumlah Hari',           $hFont, $jcC);

        // Header row 2: vMerge continues | On Desk | On Field
        $timTbl->addRow(360);
        $timTbl->addCell($cm(1.2),  $hBg + ['vMerge' => 'continue'])->addText('');
        $timTbl->addCell($cm(5.0),  $hBg + ['vMerge' => 'continue'])->addText('');
        $timTbl->addCell($cm(3.8),  $hBg + ['vMerge' => 'continue'])->addText('');
        $timTbl->addCell($cm(2.75), $hBg)->addText('On Desk',  $hFont, $jcC);
        $timTbl->addCell($cm(2.75), $hBg)->addText('On Field', $hFont, $jcC);

        $fNorm = ['size' => 11];
        $fSub  = ['size' => 9, 'color' => '555555'];
        foreach (($spt['tim'] ?? []) as $idx => $t) {
            $timTbl->addRow();
            $timTbl->addCell($cm(1.2))->addText((string)($idx + 1), $fNorm, $jcC);
            $nc = $timTbl->addCell($cm(5.0));
            $nc->addText($t['sdm_nama'] ?? '', ['bold' => true, 'size' => 11]);
            if (!empty($t['nip'])) $nc->addText('NIP. ' . $t['nip'], $fSub);
            if (!empty($t['pangkat_golongan'])) $nc->addText($t['pangkat_golongan'], $fSub);
            $timTbl->addCell($cm(3.8))->addText($t['peran_spt'] ?? '', $fNorm);
            $timTbl->addCell($cm(2.75))->addText((string)($t['hp_desk']  ?? 0), $fNorm, $jcC);
            $timTbl->addCell($cm(2.75))->addText((string)($t['hp_field'] ?? 0), $fNorm, $jcC);
        }
        $section->addTextBreak(1);

        // ── UNTUK (borderless 3-col) ──────────────────────────
        $tglMulai   = $spt['tanggal_mulai']   ? tgl_indo($spt['tanggal_mulai'])   : '...';
        $tglSelesai = $spt['tanggal_selesai'] ? tgl_indo($spt['tanggal_selesai']) : '...';

        $utbl = $section->addTable('noBorder');
        $utbl->addRow();
        $utbl->addCell($cm(1.8), $noBorder)->addText('Untuk', ['size' => 12], $jcL);
        $utbl->addCell($cm(0.3), $noBorder)->addText(':', ['size' => 12], $jcL);
        $utbl->addCell($cm(13.4), $noBorder)->addText($spt['tujuan'] ?? '', ['size' => 12]);
        $utbl->addRow();
        $utbl->addCell($cm(1.8), $noBorder)->addText('Waktu', ['size' => 12], $jcL);
        $utbl->addCell($cm(0.3), $noBorder)->addText(':', ['size' => 12], $jcL);
        $utbl->addCell($cm(13.4), $noBorder)->addText($tglMulai . ' s.d. ' . $tglSelesai, ['size' => 12]);
        $section->addTextBreak(1);

        // ── PENUTUP ───────────────────────────────────────────
        $section->addText(
            'Demikian Surat Perintah ini dibuat untuk dapat dilaksanakan dengan penuh rasa tanggung jawab.',
            ['size' => 12]
        );
        $section->addTextBreak(1);

        // ── TANDA TANGAN (kanan) ──────────────────────────────
        $tglNaskah   = $spt['tanggal_naskah']         ? tgl_indo($spt['tanggal_naskah']) : '...';
        $jabatan     = $spt['penandatangan_jabatan']  ?? 'Inspektur Daerah';
        $namaPenanda = $spt['penandatangan_nama']     ?? '';
        $pangkat     = $spt['penandatangan_pangkat']  ?? '';
        $nip         = $spt['penandatangan_nip']      ?? '';

        $ttdTbl = $section->addTable('noBorder');
        $ttdTbl->addRow();
        $ttdTbl->addCell($cm(8.0), $noBorder)->addText('');
        $sig = $ttdTbl->addCell($cm(7.5), $noBorder);
        $sig->addText('Ditetapkan di  : Sampang',        ['size' => 12], $jcL);
        $sig->addText('Pada tanggal    : ' . $tglNaskah, ['size' => 12], $jcL);
        $sig->addTextBreak(1);
        $sig->addText($jabatan . ',',                    ['size' => 12], $jcC);
        $sig->addTextBreak(3);
        $sig->addText($namaPenanda, ['bold' => true, 'underline' => 'single', 'size' => 12], $jcC);
        if ($pangkat) $sig->addText($pangkat,            ['size' => 11], $jcC);
        $sig->addText('NIP. ' . $nip,                   ['size' => 12], $jcC);

        // ── TEMBUSAN ──────────────────────────────────────────
        if (!empty($spt['tembusan'])) {
            $section->addTextBreak(2);
            $section->addText('Tembusan :', ['size' => 12]);
            foreach (preg_split('/\r?\n/', trim($spt['tembusan'])) as $idx => $line) {
                if (trim($line)) {
                    $section->addText(($idx + 1) . '. ' . trim($line), ['size' => 11]);
                }
            }
        }

        // ── FOOTER ────────────────────────────────────────────
        $footer = $section->addFooter();
        $footer->addText(
            '"BERANI JUJUR ITU HEBAT — Tolak Gratifikasi, Tegakkan Integritas"',
            ['bold' => true, 'italic' => true, 'size' => 9, 'color' => '8B0000'],
            $jcL
        );
        $footer->addText(
            'Dokumen ini ditandatangani secara elektronik melalui sistem BSrE BSSN dan sah tanpa tanda tangan basah.',
            ['size' => 8, 'color' => '666666'],
            $jcL
        );

        // ── SIMPAN & UNDUH ────────────────────────────────────
        if (!is_dir(WRITEPATH . 'uploads')) {
            mkdir(WRITEPATH . 'uploads', 0775, true);
        }

        $slug     = preg_replace('/[^A-Za-z0-9_\-]/', '_', $spt['nomor_naskah'] ?: (string)$id);
        $filename = 'SPT_' . $slug . '.docx';
        $path     = WRITEPATH . 'uploads/' . $filename;

        \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007')->save($path);
        $this->sptModel->update($id, ['file_word' => 'writable/uploads/' . $filename]);

        return $this->response->download($path, null)->setFileName($filename);
    }

    // ===================================================
    // HELPERS
    // ===================================================

    private function parseTimPost(): array
    {
        $sdmIds  = $this->request->getPost('tim_sdm_id')   ?? [];
        $perans  = $this->request->getPost('tim_peran_spt') ?? [];
        $desks   = $this->request->getPost('tim_hp_desk')  ?? [];
        $fields  = $this->request->getPost('tim_hp_field') ?? [];
        $fromPkpts = $this->request->getPost('tim_from_pkpt') ?? [];

        $result = [];
        foreach ((array)$sdmIds as $i => $sdmId) {
            if (!$sdmId) continue;
            $result[] = [
                'sdm_id'    => (int)$sdmId,
                'peran_spt' => $perans[$i] ?? 'Anggota Tim',
                'hp_desk'   => (int)($desks[$i] ?? 1),
                'hp_field'  => (int)($fields[$i] ?? 0),
                'from_pkpt' => (int)($fromPkpts[$i] ?? 1),
                'urutan'    => $i,
            ];
        }
        return $result;
    }

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
     * Cek apakah user saat ini boleh mengakses SPT ini.
     * Admin: semua SPT. Irban user: hanya SPT milik irbannya.
     */
    private function canAccessSpt(array $spt): bool
    {
        if ($this->isAdmin()) return true;
        $irbanId = $this->getUserIrbanId(session()->get('user_id'));
        return $irbanId !== null && (int)$spt['irban_id'] === $irbanId;
    }
}
