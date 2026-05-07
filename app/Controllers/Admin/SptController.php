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
        $userId   = session()->get('user_id');
        $irbanId  = $this->isAdmin() ? null : $this->getUserIrbanId($userId);
        $slotInfo = $irbanId ? $this->sptModel->getInfoSlot($irbanId) : null;

        return view('admin/spt/index', [
            'title'       => 'Surat Perintah Tugas (SPT)',
            'statusLabel' => SptModel::$statusLabel,
            'tahunAktif'  => $this->settingModel->getTahunAktif(),
            'settings'    => $this->settingModel->orderBy('tahun', 'DESC')->findAll(),
            'slotInfo'    => $slotInfo,
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
                      COALESCE(i_pkpt.nama, i_spt.nama) as irban_nama,
                      (SELECT COUNT(*) FROM spt_lhp sl WHERE sl.spt_id = s.id) as has_lhp');

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
            $lhpHtml = (int)($r['has_lhp'] ?? 0) > 0
                ? '<span class="badge badge-success"><i class="fas fa-check"></i> Ada</span>'
                : '<a href="/admin/spt/' . $r['id'] . '/lhp" class="badge badge-warning" style="cursor:pointer"><i class="fas fa-upload"></i> Upload</a>';
            return [
                'nomor_naskah'  => esc($r['nomor_naskah'] ?: '—'),
                'kode_kegiatan' => $kodeHtml,
                'irban_nama'    => esc($r['irban_nama'] ?? '—'),
                'tujuan'        => '<span style="display:block;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' . esc(strip_tags($r['tujuan'])) . '">' . esc(strip_tags($r['tujuan'])) . '</span>',
                'tanggal_mulai' => $r['tanggal_mulai'] ? date('d/m/Y', strtotime($r['tanggal_mulai'])) : '—',
                'lhp_status'    => $lhpHtml,
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
        $db        = \Config\Database::connect();
        $tahunAktif = $this->settingModel->getTahunAktif();
        $hariLibur  = array_column(
            $db->table('hari_libur')->select('tanggal')->where('tahun', $tahunAktif)->get()->getResultArray(),
            'tanggal'
        );
        return view('admin/spt/form_non_pkpt', [
            'title'       => 'Buat SPT Non-PKPT / Mandatori',
            'irbanList'   => $this->irbanModel->orderBy('kode')->findAll(),
            'sdmAll'      => $this->sdmModel->getAktif(),
            'sdmPenanda'  => $this->sdmModel->getAktif(),
            'jenisOpts'   => SptModel::$jenisNonPkpt,
            'tahunAktif'  => $tahunAktif,
            'spt'         => null,
            'setting'     => $this->settingModel->getByTahun($tahunAktif),
            'entitasList' => $db->table('entitas')->where('aktif', 1)->orderBy('nama')->get()->getResultArray(),
            'hariLibur'   => $hariLibur,
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

        // ── Cek kuota SPT ──
        $jenisNonPkpt   = $this->request->getPost('jenis_non_pkpt');
        $isPengecualian = in_array($jenisNonPkpt, ['Pemeriksaan Kasus/Khusus']);
        if (!$isPengecualian) {
            $irbanIdForQuota = $this->isAdmin()
                ? (int)$this->request->getPost('irban_id')
                : $this->getUserIrbanId(session()->get('user_id'));
            if ($irbanIdForQuota) {
                $kuota = $this->sptModel->canAjukanSpt($irbanIdForQuota);
                if (!$kuota['boleh']) {
                    return redirect()->back()->withInput()->with('error', $kuota['pesan']);
                }
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

        // Hari libur untuk kalkulator HP di form
        $hariLibur = array_column(
            $db->table('hari_libur')->select('tanggal')->where('tahun', (int)$pkpt['tahun'])->get()->getResultArray(),
            'tanggal'
        );

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
            'hariLibur'     => $hariLibur,
        ]);
    }

    public function store(int $pkptKegiatanId)
    {
        $kegiatan = $this->kegiatanModel->find($pkptKegiatanId);
        if (!$kegiatan) return redirect()->back()->with('error', 'Kegiatan tidak ditemukan.');

        // Irban user hanya boleh buat SPT untuk irbannya sendiri
        $pkpt = $this->pkptModel->find($kegiatan['pkpt_id']);
        if (!$this->isAdmin()) {
            $myIrbanId = $this->getUserIrbanId(session()->get('user_id'));
            if (!$pkpt || $myIrbanId === null || (int)$pkpt['irban_id'] !== $myIrbanId) {
                return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');
            }
        }

        // ── Cek kuota SPT (PKPT reguler — bukan investigasi/ADTT) ──
        $irbanIdForQuota = $pkpt ? (int)$pkpt['irban_id'] : null;
        if ($irbanIdForQuota) {
            $kegiatanDetail = $this->kegiatanModel->getDetail($pkptKegiatanId);
            $jenisP         = strtolower($kegiatanDetail['jenis_pengawasan'] ?? '');
            $isPengecualian = str_contains($jenisP, 'investigasi') || str_contains($jenisP, 'adtt');
            if (!$isPengecualian) {
                $kuota = $this->sptModel->canAjukanSpt($irbanIdForQuota);
                if (!$kuota['boleh']) {
                    return redirect()->back()->withInput()->with('error', $kuota['pesan']);
                }
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
            $tahunNp   = (int)($spt['tahun'] ?? $this->settingModel->getTahunAktif());
            $hariLibNp = array_column(
                \Config\Database::connect()->table('hari_libur')->select('tanggal')->where('tahun', $tahunNp)->get()->getResultArray(),
                'tanggal'
            );
            return view('admin/spt/form_non_pkpt', [
                'title'      => 'Edit SPT Non-PKPT — ' . ($spt['nomor_naskah'] ?: '#' . $id),
                'irbanList'  => $this->irbanModel->orderBy('kode')->findAll(),
                'sdmAll'     => $this->sdmModel->getAktif(),
                'sdmPenanda' => $this->sdmModel->getAktif(),
                'jenisOpts'  => SptModel::$jenisNonPkpt,
                'tahunAktif' => $tahunNp,
                'spt'        => $spt,
                'setting'    => $this->settingModel->getByTahun($tahunNp),
                'hariLibur'  => $hariLibNp,
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

        $hariLiburEdit = array_column(
            \Config\Database::connect()->table('hari_libur')->select('tanggal')->where('tahun', (int)$pkpt['tahun'])->get()->getResultArray(),
            'tanggal'
        );

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
            'hariLibur'     => $hariLiburEdit,
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

        // WA ke semua user berperan irban/ka_irban yang punya nomor HP
        $irbanUsers = \Config\Database::connect()
            ->table('users u')
            ->select('u.id, u.phone')
            ->join('user_roles ur', 'ur.user_id = u.id')
            ->join('roles r', 'r.id = ur.role_id')
            ->whereIn('r.slug', ['irban', 'ka_irban', 'admin', 'superadmin'])
            ->where('u.phone IS NOT NULL')
            ->where("u.phone != ''")
            ->get()->getResultArray();
        $waMsg = "*[SIMPAWAN] SPT Diajukan*\n\n"
               . "SPT *{$nomorSpt}* diajukan oleh " . session()->get('user_name') . " dan menunggu persetujuan Kepala Irban.\n\n"
               . "Silakan login ke SIMPAWAN untuk meninjau.";
        foreach ($irbanUsers as $u) {
            send_wa($u['phone'], $waMsg);
        }

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

        // Notifikasi ke pembuat SPT (in-app + WA)
        $createdBy = $this->sptModel->getCreatedBy($id);
        if ($createdBy) {
            $notifMsg = $nextStatus === 'terbit'
                ? 'SPT "' . $nomorSpt . '" telah TERBIT. Silakan lanjutkan ke tahap pelaksanaan audit.'
                : 'SPT "' . $nomorSpt . '" disetujui oleh ' . session()->get('user_name') . '. Status sekarang: ' . $label;
            notify($createdBy, 'SPT Disetujui: ' . $nomorSpt, $notifMsg, '/admin/spt/' . $id, 'success');

            $waEmoji = $nextStatus === 'terbit' ? '✅' : '👍';
            send_wa_to_user($createdBy,
                "*[SIMPAWAN] SPT Disetujui {$waEmoji}*\n\n"
                . $notifMsg . "\n\n"
                . "Silakan login ke SIMPAWAN untuk melanjutkan."
            );
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

        // Notifikasi ke pembuat SPT (in-app + WA)
        $createdBy = $this->sptModel->getCreatedBy($id);
        if ($createdBy) {
            $notifMsg = 'SPT "' . $nomorSpt . '" ditolak oleh ' . session()->get('user_name') . '. Catatan: ' . $catatan;
            notify($createdBy, 'SPT Ditolak: ' . $nomorSpt, $notifMsg, '/admin/spt/' . $id, 'danger');
            send_wa_to_user($createdBy,
                "*[SIMPAWAN] SPT Ditolak ❌*\n\n"
                . $notifMsg . "\n\n"
                . "Silakan login ke SIMPAWAN untuk melakukan revisi."
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

        // Bersihkan HTML dari field Quill editor
        $plain = function(string $v): string {
            $v = preg_replace('/<\/li>/i', ' ', $v);
            $v = preg_replace('/<li[^>]*>/i', '', $v);
            $v = preg_replace('/<br\s*\/?>/i', ' ', $v);
            return trim(preg_replace('/\s+/', ' ', strip_tags($v)));
        };

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $cm  = fn(float $v): int => (int) \PhpOffice\PhpWord\Shared\Converter::cmToTwip($v);
        $jcC = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $jcL = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT];
        $jcJ = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH];

        // Lebar konten = 21 - 3.0 - 2.0 = 16.0 cm
        $W = 16.0;

        $section = $phpWord->addSection([
            'marginTop'    => $cm(2.5),
            'marginBottom' => $cm(2.0),
            'marginLeft'   => $cm(3.0),
            'marginRight'  => $cm(2.0),
        ]);

        $noBorder = [
            'borderTopSize'    => 0, 'borderTopColor'    => 'FFFFFF',
            'borderBottomSize' => 0, 'borderBottomColor' => 'FFFFFF',
            'borderLeftSize'   => 0, 'borderLeftColor'   => 'FFFFFF',
            'borderRightSize'  => 0, 'borderRightColor'  => 'FFFFFF',
        ];

        $phpWord->addTableStyle('noBorder', $noBorder);
        $phpWord->addTableStyle('timBorder', [
            'borderSize'  => 6,
            'borderColor' => '000000',
            'cellMargin'  => 80,
        ]);
        $phpWord->addParagraphStyle('hrThick', [
            'borderBottomSize'  => 18,
            'borderBottomColor' => '000000',
            'spaceBefore'       => 20,
            'spaceAfter'        => 0,
        ]);
        $phpWord->addParagraphStyle('hrThin', [
            'borderBottomSize'  => 4,
            'borderBottomColor' => '000000',
            'spaceBefore'       => 4,
            'spaceAfter'        => 40,
        ]);

        // ── Ambil setting organisasi ───────────────────────────
        $orgNama    = app_setting('org_name',    'PEMERINTAH KABUPATEN SAMPANG');
        $orgUnit    = app_setting('org_unit',    'INSPEKTORAT DAERAH');
        $orgAlamat  = app_setting('org_address', 'Jalan Rajawali, No. 36 Telp/Fax (0323) 321053');
        $orgEmail   = app_setting('org_email',   'itda@sampangkab.go.id');
        $orgWebsite = app_setting('org_website', 'https://itkab.sampangkab.go.id');
        $orgKota    = app_setting('org_city',    'Sampang');

        // ── KOP ───────────────────────────────────────────────
        $logoPath = FCPATH . 'assets/images/logo-sampang.png';
        $hasLogo  = is_file($logoPath);

        $kopTbl = $section->addTable('noBorder');
        $kopTbl->addRow($cm(2.8));

        if ($hasLogo) {
            $lc = $kopTbl->addCell($cm(2.6), $noBorder + ['valign' => 'center']);
            $lc->addImage($logoPath, [
                'width'     => 65,
                'height'    => 65,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            ]);
            $tc = $kopTbl->addCell($cm($W - 2.6), $noBorder + ['valign' => 'center']);
        } else {
            $tc = $kopTbl->addCell($cm($W), $noBorder + ['valign' => 'center']);
        }
        $tc->addText($orgNama, ['bold' => true, 'size' => 11], $jcC);
        $tc->addText($orgUnit, ['bold' => true, 'size' => 14], $jcC);
        $tc->addText($orgAlamat, ['size' => 9], $jcC);
        $tc->addText('Email : ' . $orgEmail . '   Website : ' . $orgWebsite, ['size' => 9], $jcC);

        $section->addText('', null, 'hrThick');
        $section->addText('', null, 'hrThin');

        // ── JUDUL ────────────────────────────────────────────
        $section->addText(
            'SURAT PERINTAH',
            ['bold' => true, 'underline' => 'single', 'size' => 12],
            $jcC + ['spaceBefore' => 80, 'spaceAfter' => 20]
        );
        $section->addText(
            'NOMOR : ' . ($spt['nomor_naskah'] ?: '...............................'),
            ['size' => 12],
            $jcC + ['spaceBefore' => 0, 'spaceAfter' => 120]
        );

        // ── DASAR ────────────────────────────────────────────
        $labelW = 1.8;
        $colonW = 0.4;
        $isiW   = $W - $labelW - $colonW;

        $dasar1 = $plain($spt['dasar_1'] ?? '');
        $dasar2 = $plain($spt['dasar_2'] ?? '');

        $dtbl = $section->addTable('noBorder');
        $dtbl->addRow();
        $dtbl->addCell($cm($labelW), $noBorder)->addText('Dasar', ['size' => 12], $jcL);
        $dtbl->addCell($cm($colonW), $noBorder)->addText(':', ['size' => 12], $jcL);
        if (empty($dasar2)) {
            $dtbl->addCell($cm($isiW), $noBorder)->addText($dasar1, ['size' => 12], $jcJ);
        } else {
            $dc = $dtbl->addCell($cm($isiW), $noBorder);
            $hang = $jcJ + ['indentation' => ['left' => 240, 'hanging' => 240]];
            $dc->addText('1.  ' . $dasar1, ['size' => 12], $hang);
            $dc->addText('2.  ' . $dasar2, ['size' => 12], $hang);
        }
        $section->addTextBreak(1);

        // ── MEMERINTAHKAN ────────────────────────────────────
        $section->addText(
            'MEMERINTAHKAN',
            ['bold' => true, 'underline' => 'single', 'size' => 12],
            $jcC + ['spaceAfter' => 80]
        );

        // ── TABEL TIM ────────────────────────────────────────
        // No=1.0 | Nama=5.5 | Jabatan=4.5 | Desk=2.5 | Field=2.5 → total=16.0 cm
        $hFont = ['bold' => true, 'size' => 12];
        $hBg   = [];

        $timTbl = $section->addTable('timBorder');

        // Baris header 1 (rowspan/colspan)
        $timTbl->addRow(400);
        $timTbl->addCell($cm(1.0), $hBg + ['vMerge' => 'restart'])->addText('No',       $hFont, $jcC);
        $timTbl->addCell($cm(5.5), $hBg + ['vMerge' => 'restart'])->addText('Nama',     $hFont, $jcC);
        $timTbl->addCell($cm(4.5), $hBg + ['vMerge' => 'restart'])->addText('Jabatan',  $hFont, $jcC);
        $timTbl->addCell($cm(5.0), $hBg + ['gridSpan' => 2])->addText('Jumlah Hari',    $hFont, $jcC);

        // Baris header 2
        $timTbl->addRow(340);
        $timTbl->addCell($cm(1.0), $hBg + ['vMerge' => 'continue'])->addText('');
        $timTbl->addCell($cm(5.5), $hBg + ['vMerge' => 'continue'])->addText('');
        $timTbl->addCell($cm(4.5), $hBg + ['vMerge' => 'continue'])->addText('');
        $timTbl->addCell($cm(2.5), $hBg)->addText('On Desk',  $hFont, $jcC);
        $timTbl->addCell($cm(2.5), $hBg)->addText('On Field', $hFont, $jcC);

        $fNorm = ['size' => 12];
        foreach (($spt['tim'] ?? []) as $idx => $t) {
            $timTbl->addRow();
            $timTbl->addCell($cm(1.0))->addText((string)($idx + 1) . '.', $fNorm, $jcC);
            $timTbl->addCell($cm(5.5))->addText($t['sdm_nama'] ?? '', $fNorm);
            $timTbl->addCell($cm(4.5))->addText($t['peran_spt'] ?? '', $fNorm);
            $timTbl->addCell($cm(2.5))->addText((string)($t['hp_desk']  ?? 0), $fNorm, $jcC);
            $timTbl->addCell($cm(2.5))->addText((string)($t['hp_field'] ?? 0), $fNorm, $jcC);
        }
        $section->addTextBreak(1);

        // ── UNTUK ────────────────────────────────────────────
        $tglMulai   = $spt['tanggal_mulai']   ? tgl_indo($spt['tanggal_mulai'])   : '...';
        $tglSelesai = $spt['tanggal_selesai'] ? tgl_indo($spt['tanggal_selesai']) : '...';
        $tujuan     = $plain($spt['tujuan'] ?? '');
        $untukIsi   = $tujuan . ' mulai tanggal ' . $tglMulai . ' s.d. ' . $tglSelesai . '.';

        $utbl = $section->addTable('noBorder');
        $utbl->addRow();
        $utbl->addCell($cm($labelW), $noBorder)->addText('Untuk', ['size' => 12], $jcL);
        $utbl->addCell($cm($colonW), $noBorder)->addText(':', ['size' => 12], $jcL);
        $utbl->addCell($cm($isiW),   $noBorder)->addText($untukIsi, ['size' => 12], $jcJ);
        $section->addTextBreak(1);

        // ── PENUTUP ──────────────────────────────────────────
        $section->addText(
            'Demikian Surat Perintah ini dibuat dengan sebenarnya dan dapat dipergunakan sebagaimana mestinya.',
            ['size' => 12],
            $jcJ + ['spaceAfter' => 80]
        );

        // ── TANDA TANGAN ─────────────────────────────────────
        $tglNaskah   = $spt['tanggal_naskah']        ? tgl_indo($spt['tanggal_naskah']) : '...';
        $jabatan     = $spt['penandatangan_jabatan'] ?? 'Inspektur Daerah';
        $namaPenanda = $spt['penandatangan_nama']    ?? '';
        $pangkat     = $spt['penandatangan_pangkat'] ?? '';
        $nip         = $spt['penandatangan_nip']     ?? '';

        $ttdTbl = $section->addTable('noBorder');
        $ttdTbl->addRow();
        $ttdTbl->addCell($cm(8.5), $noBorder)->addText('');
        $sig = $ttdTbl->addCell($cm(7.5), $noBorder);
        $sig->addText('Ditetapkan di : ' . $orgKota,   ['size' => 12], $jcL);
        $sig->addText('Pada tanggal  : ' . $tglNaskah, ['size' => 12], $jcL);
        $sig->addTextBreak(1);
        $sig->addText($jabatan,                         ['size' => 12], $jcC);
        $sig->addTextBreak(4);
        $sig->addText($namaPenanda, ['bold' => true, 'underline' => 'single', 'size' => 12], $jcC);
        if ($pangkat) {
            $sig->addText($pangkat, ['size' => 11], $jcC);
        }
        $sig->addText('NIP. ' . $nip, ['size' => 12], $jcC);

        // ── TEMBUSAN ─────────────────────────────────────────
        if (!empty($spt['tembusan'])) {
            $section->addTextBreak(2);
            $section->addText('Tembusan :', ['size' => 12]);
            foreach (preg_split('/\r?\n/', trim($spt['tembusan'])) as $line) {
                if (trim($line)) {
                    $section->addText('Yth. ' . trim($line), ['size' => 12]);
                }
            }
        }

        // ── FOOTER ───────────────────────────────────────────
        $footer = $section->addFooter();
        $footer->addText(
            'PEMBERI DAN PENERIMA SUAP SAMA-SAMA KENA SANKSI PIDANA',
            ['bold' => true, 'size' => 9],
            $jcC
        );
        $footer->addText(
            'Pasal 12 UU No.20 Tahun 2001 tentang Pemberantasan Tindak Pidana Korupsi',
            ['size' => 9],
            $jcC
        );

        // ── SIMPAN & UNDUH ───────────────────────────────────
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
    // UPLOAD LHP
    // ===================================================

    public function showUploadLhp(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSpt($spt)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $db       = \Config\Database::connect();
        $existLhp = $db->table('spt_lhp')->where('spt_id', $id)->get()->getRowArray();
        if ($existLhp) {
            return redirect()->to('/admin/spt/' . $id)->with('error', 'LHP untuk SPT ini sudah diupload.');
        }

        $irbanId = (int)($spt['irban_id'] ?? 0);
        if ($irbanId && !$this->isAdmin()) {
            if (!$this->sptModel->cekUrutanLhp($id, $irbanId)) {
                $slot    = $this->sptModel->getInfoSlot($irbanId);
                $pertama = $slot['list_antrian'][0] ?? null;
                $hint    = $pertama
                    ? ' Selesaikan dahulu LHP untuk SPT <strong>' . esc($pertama['nomor_naskah'] ?: '#' . $pertama['id']) . '</strong>.'
                    : '';
                return redirect()->to('/admin/spt')->with('error',
                    'LHP harus diselesaikan secara urut sesuai tanggal SPT.' . $hint);
            }
        }

        return view('admin/spt/upload_lhp', [
            'title'    => 'Upload LHP — ' . ($spt['nomor_naskah'] ?: '#' . $id),
            'spt'      => $spt,
            'slotInfo' => $irbanId ? $this->sptModel->getInfoSlot($irbanId) : null,
        ]);
    }

    public function storeLhp(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSpt($spt)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $irbanId = (int)($spt['irban_id'] ?? 0);
        if ($irbanId && !$this->isAdmin()) {
            if (!$this->sptModel->cekUrutanLhp($id, $irbanId)) {
                return redirect()->to('/admin/spt')->with('error', 'LHP harus diselesaikan secara urut.');
            }
        }

        $rules = [
            'nomor_lhp'   => 'required|max_length[100]',
            'tanggal_lhp' => 'required|valid_date',
            'file_lhp'    => 'uploaded[file_lhp]|ext_in[file_lhp,pdf,doc,docx]|max_size[file_lhp,10240]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $uploadDir = WRITEPATH . 'uploads/lhp/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

        $file    = $this->request->getFile('file_lhp');
        $newName = 'LHP_' . $id . '_' . time() . '.' . $file->getClientExtension();
        $file->move($uploadDir, $newName);

        $db = \Config\Database::connect();
        $db->table('spt_lhp')->insert([
            'spt_id'      => $id,
            'nomor_lhp'   => $this->request->getPost('nomor_lhp'),
            'tanggal_lhp' => $this->request->getPost('tanggal_lhp'),
            'file_lhp'    => 'writable/uploads/lhp/' . $newName,
            'keterangan'  => $this->request->getPost('keterangan'),
            'created_by'  => session()->get('user_id'),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        logActivity('spt.lhp.upload', 'spt', "Upload LHP untuk SPT id={$id}");
        return redirect()->to('/admin/spt/' . $id)
            ->with('success', 'LHP berhasil diupload. Slot SPT tersedia kembali.');
    }

    // ===================================================
    // HELPERS
    // ===================================================

    private function parseTimPost(): array
    {
        $sdmIds    = $this->request->getPost('tim_sdm_id')    ?? [];
        $perans    = $this->request->getPost('tim_peran_spt')  ?? [];
        $desks     = $this->request->getPost('tim_hp_desk')    ?? [];
        $fields    = $this->request->getPost('tim_hp_field')   ?? [];
        $fromPkpts = $this->request->getPost('tim_from_pkpt')  ?? [];

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
    // isAdmin(), getUserIrbanId(), canAccessSpt() diwarisi dari BaseController (protected)
}
