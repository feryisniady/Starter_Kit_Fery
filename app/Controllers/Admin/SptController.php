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
use App\Models\PkaModel;
use App\Models\TemuanModel;
use App\Models\SptKmModel;
use App\Traits\DatatableTrait;

class SptController extends BaseController
{
    use DatatableTrait;
    protected SptModel          $sptModel;
    protected SptTimModel       $timModel;
    protected PkaModel          $pkaModel;
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
        $this->pkaModel      = new PkaModel();
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

        // Non-admin dengan akun tidak terhubung ke SDM/Irban — tampilkan kosong
        if (!$isAdmin && $irbanId === null) {
            return $this->dtResponse($draw, 0, 0, []);
        }

        $db = \Config\Database::connect();

        // Total tanpa search (basis filter tahun + irban)
        $totalQ = $db->table('spt s')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id')
            ->join('pkpt p', 'p.id = pk.pkpt_id')
            ->where('p.tahun', $tahun);
        if ($irbanId) $totalQ->where('p.irban_id', $irbanId);
        if ($status)  $totalQ->where('s.status', $status);
        $total = $totalQ->countAllResults();

        // Query data
        $q = $db->table('spt s')
            ->select('s.id, s.nomor_naskah, s.tanggal_mulai, s.tujuan, s.status, pk.kode_kegiatan, i.nama as irban_nama')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id')
            ->join('pkpt p', 'p.id = pk.pkpt_id')
            ->join('irban i', 'i.id = p.irban_id')
            ->where('p.tahun', $tahun);
        if ($irbanId) $q->where('p.irban_id', $irbanId);
        if ($status)  $q->where('s.status', $status);
        if ($search) {
            $q->groupStart()
                ->like('s.nomor_naskah', $search)
                ->orLike('pk.kode_kegiatan', $search)
                ->orLike('s.tujuan', $search)
                ->orLike('i.nama', $search)
                ->groupEnd();
        }

        $filtered = $search ? $q->countAllResults(false) : $total;
        $rows     = $q->orderBy('s.created_at', 'DESC')->limit($length, $start)->get()->getResultArray();

        $sl = SptModel::$statusLabel;
        $sc = SptModel::$statusColor;

        $data = array_map(function ($r) use ($sl, $sc) {
            $badge   = '<span class="badge badge-' . ($sc[$r['status']] ?? 'secondary') . '">'
                     . esc($sl[$r['status']] ?? $r['status']) . '</span>';
            $actions = '<a href="/admin/spt/' . $r['id'] . '" class="btn btn-xs btn-primary">Detail</a>';
            if ($r['status'] === 'terbit') {
                $actions .= ' <a href="/admin/spt/' . $r['id'] . '/word" class="btn btn-xs btn-success"><i class="fas fa-file-word"></i></a>';
            }
            return [
                'nomor_naskah'  => esc($r['nomor_naskah'] ?: '—'),
                'kode_kegiatan' => '<span class="badge badge-primary">' . esc($r['kode_kegiatan']) . '</span>',
                'irban_nama'    => esc($r['irban_nama']),
                'tujuan'        => '<span style="display:block;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="' . esc($r['tujuan']) . '">' . esc($r['tujuan']) . '</span>',
                'tanggal_mulai' => $r['tanggal_mulai'] ? date('d/m/Y', strtotime($r['tanggal_mulai'])) : '—',
                'status'        => $badge,
                'aksi'          => $actions,
            ];
        }, $rows);

        return $this->dtResponse($draw, $total, $filtered, $data);
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

        // Tim default dari PKPT
        $timDefault = $this->timModel->buildFromPkptTim($pkptKegiatanId);

        // Dasar 1 otomatis dari setting
        $dasar1 = '';
        if ($setting && $setting['nomor_pkpt']) {
            $dasar1 = 'Program Kerja Pengawasan Tahunan (PKPT) Inspektorat Daerah Kabupaten Sampang Tahun '
                . $pkpt['tahun'] . ' tanggal '
                . ($setting['tanggal_pkpt'] ? tgl_indo($setting['tanggal_pkpt']) : '...')
                . ' Nomor : ' . $setting['nomor_pkpt'] . ', dengan ini :';
        }

        return view('admin/spt/form', [
            'title'      => 'Generate SPT — ' . $kegiatan['kode_kegiatan'],
            'kegiatan'   => $kegiatan,
            'pkpt'       => $pkpt,
            'setting'    => $setting,
            'timDefault' => $timDefault,
            'sdmAll'     => $this->sdmModel->getAktif(),
            'sdmPenanda' => $this->sdmModel->getAktif(),
            'dasar1'     => $dasar1,
            'spt'        => null,
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

        return view('admin/spt/show', [
            'title'         => 'Detail SPT — ' . ($spt['nomor_naskah'] ?: '#' . $id),
            'spt'           => $spt,
            'statusLabel'   => SptModel::$statusLabel,
            'statusColor'   => SptModel::$statusColor,
            'pkaStats'      => $this->pkaModel->getStatsBySpt($id),
            'temuanSummary' => $this->temuanModel->getSummaryBySpt($id),
            'kmChecklist'   => $this->kmModel->getChecklist($id),
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

        $kegiatan = $this->kegiatanModel->getDetail($spt['pkpt_kegiatan_id']);
        $pkpt     = $this->pkptModel->getWithIrban($kegiatan['pkpt_id']);

        return view('admin/spt/form', [
            'title'      => 'Edit SPT — ' . $spt['kode_kegiatan'],
            'kegiatan'   => $kegiatan,
            'pkpt'       => $pkpt,
            'setting'    => $this->settingModel->getByTahun((int)$pkpt['tahun']),
            'timDefault' => $spt['tim'],
            'sdmAll'     => $this->sdmModel->getAktif(),
            'sdmPenanda' => $this->sdmModel->getAktif(),
            'dasar1'     => $spt['dasar_1'],
            'spt'        => $spt,
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

        $this->sptModel->update($id, [
            'nomor_naskah'    => $this->request->getPost('nomor_naskah'),
            'tanggal_naskah'  => $this->request->getPost('tanggal_naskah'),
            'dasar_1'         => $this->request->getPost('dasar_1'),
            'dasar_2'         => $this->request->getPost('dasar_2') ?: null,
            'tujuan'          => $this->request->getPost('tujuan'),
            'tanggal_mulai'   => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai' => $this->request->getPost('tanggal_selesai') ?: null,
            'tembusan'        => $this->request->getPost('tembusan'),
            'penandatangan_id'=> $this->request->getPost('penandatangan_id') ?: null,
        ]);

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

        // Cek kelengkapan KM sebelum bisa diajukan
        $missing = $this->kmModel->getMissingLabels($id);
        if (!empty($missing)) {
            $list = implode(', ', $missing);
            return redirect()->to('/admin/spt/' . $id . '/km')
                ->with('error', 'SPT belum dapat diajukan. Lengkapi dokumen KM berikut: ' . $list);
        }

        $this->sptModel->update($id, ['status' => 'diajukan']);
        logActivity('spt.ajukan', 'spt', "Ajukan SPT id={$id}");

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

        $label = SptModel::$statusLabel[$nextStatus] ?? $nextStatus;
        logActivity('spt.approve', 'spt', "Approve SPT id={$id} → {$nextStatus}");
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
        $this->sptModel->update($id, ['status' => 'draft', 'catatan' => $catatan]);

        logActivity('spt.reject', 'spt', "Tolak SPT id={$id}, catatan: {$catatan}");
        return redirect()->to('/admin/spt/' . $id)->with('error', 'SPT ditolak dan dikembalikan ke draft.');
    }

    // ===================================================
    // GENERATE WORD (programmatic — tanpa template file)
    // ===================================================

    public function downloadWord(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');
        if (!$this->canAccessSpt($spt)) return redirect()->back()->with('error', 'Akses ditolak.');

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $cm = fn(float $v) => \PhpOffice\PhpWord\Shared\Converter::cmToTwip($v);

        $section = $phpWord->addSection([
            'marginTop'    => $cm(2.5),
            'marginBottom' => $cm(2.5),
            'marginLeft'   => $cm(3.0),
            'marginRight'  => $cm(2.5),
        ]);

        $center  = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER];
        $bold    = ['bold' => true];
        $boldBig = ['bold' => true, 'size' => 14];
        $small   = ['size' => 10];

        // ── KOP SURAT ───────────────────────────────────────
        $section->addText(
            'PEMERINTAH KABUPATEN SAMPANG',
            ['bold' => true, 'size' => 13],
            $center
        );
        $section->addText(
            'INSPEKTORAT DAERAH',
            ['bold' => true, 'size' => 15, 'allCaps' => true],
            $center
        );
        $section->addText(
            'Jl. Syamsul Arifin No.1 Sampang — Telp (0323) 323456',
            $small,
            $center
        );
        $section->addTextBreak(0);

        // Garis bawah KOP
        $phpWord->addParagraphStyle('hrStyle', ['borderBottomColor' => '000000', 'borderBottomSize' => 12, 'spaceAfter' => 0]);
        $section->addText('', null, 'hrStyle');
        $section->addTextBreak(1);

        // ── JUDUL ───────────────────────────────────────────
        $section->addText('SURAT PERINTAH TUGAS', $boldBig, $center);
        $section->addText(
            'Nomor : ' . ($spt['nomor_naskah'] ?: '....................................'),
            $bold,
            $center
        );
        $section->addTextBreak(1);

        // ── DASAR ───────────────────────────────────────────
        $section->addText('Dasar :', $bold);
        $section->addListItem($spt['dasar_1'] ?? '', 0, null, null, ['spaceAfter' => 60]);
        if (!empty($spt['dasar_2'])) {
            $section->addListItem($spt['dasar_2'], 0, null, null, ['spaceAfter' => 60]);
        }
        $section->addTextBreak(1);

        // ── MENUGASKAN ──────────────────────────────────────
        $section->addText('MENUGASKAN :', $bold);
        $section->addTextBreak(0);
        $section->addText('Kepada :');
        $section->addTextBreak(0);

        // ── TABEL TIM ───────────────────────────────────────
        $tblStyle = [
            'borderColor' => '000000',
            'borderSize'  => 6,
            'cellMargin'  => 80,
        ];
        $table = $section->addTable($tblStyle);

        // Header row
        $hBg = ['bgColor' => 'E2E8F0'];
        $hFont = ['bold' => true, 'size' => 10];
        $table->addRow(400);
        $table->addCell($cm(1.2), $hBg)->addText('No',         $hFont, $center);
        $table->addCell($cm(6.5), $hBg)->addText('Nama / NIP',  $hFont);
        $table->addCell($cm(4.5), $hBg)->addText('Peran / Jabatan', $hFont);
        $table->addCell($cm(2.0), $hBg)->addText('HP Desk',    $hFont, $center);
        $table->addCell($cm(2.0), $hBg)->addText('HP Field',   $hFont, $center);

        $tim = $spt['tim'] ?? [];
        foreach ($tim as $i => $t) {
            $table->addRow();
            $table->addCell($cm(1.2))->addText((string)($i + 1), ['size' => 10], $center);

            $nameCell = $table->addCell($cm(6.5));
            $nameCell->addText($t['sdm_nama'] ?? '', ['bold' => true, 'size' => 10]);
            if (!empty($t['sdm_nip'])) {
                $nameCell->addText('NIP. ' . $t['sdm_nip'], ['size' => 9, 'color' => '64748B']);
            }

            $table->addCell($cm(4.5))->addText($t['peran_spt'] ?? '', ['size' => 10]);
            $table->addCell($cm(2.0))->addText((string)($t['hp_desk'] ?? 0),  ['size' => 10], $center);
            $table->addCell($cm(2.0))->addText((string)($t['hp_field'] ?? 0), ['size' => 10], $center);
        }
        $section->addTextBreak(1);

        // ── KETENTUAN ───────────────────────────────────────
        $left1 = ['indent' => 1];
        $section->addText('Untuk melaksanakan tugas :', $bold);

        $section->addText('1. Tujuan      : ' . ($spt['tujuan'] ?? ''), null, $left1);
        $section->addText(
            '2. Waktu       : ' .
            ($spt['tanggal_mulai']    ? tgl_indo($spt['tanggal_mulai'])    : '...') .
            ' s.d. ' .
            ($spt['tanggal_selesai'] ? tgl_indo($spt['tanggal_selesai']) : '...'),
            null,
            $left1
        );
        if (!empty($spt['tembusan'])) {
            $section->addText('3. Tembusan    : ' . $spt['tembusan'], null, $left1);
        }
        $section->addTextBreak(1);

        // ── TANDA TANGAN ────────────────────────────────────
        $tanggalNaskah = $spt['tanggal_naskah']
            ? 'Sampang, ' . tgl_indo($spt['tanggal_naskah'])
            : 'Sampang, ..............................';

        $ttdTable = $section->addTable(['cellMarginTop' => 0, 'cellMarginBottom' => 0]);
        $ttdTable->addRow();
        $ttdTable->addCell($cm(10))->addText('');   // spacer kiri
        $ttdRight = $ttdTable->addCell($cm(6.5));
        $ttdRight->addText($tanggalNaskah, null, $center);
        $ttdRight->addText($spt['penandatangan_jabatan'] ?? 'Inspektur Daerah', null, $center);
        $ttdRight->addTextBreak(3);
        $ttdRight->addText($spt['penandatangan_nama'] ?? '', $bold, $center);
        $ttdRight->addText('NIP. ' . ($spt['penandatangan_nip'] ?? ''), null, $center);

        // ── SIMPAN ──────────────────────────────────────────
        if (!is_dir(WRITEPATH . 'uploads')) {
            mkdir(WRITEPATH . 'uploads', 0775, true);
        }

        $slug       = preg_replace('/[^A-Za-z0-9_\-]/', '_', $spt['nomor_naskah'] ?: (string)$id);
        $filename   = 'SPT_' . $slug . '.docx';
        $outputPath = WRITEPATH . 'uploads/' . $filename;

        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($outputPath);

        $this->sptModel->update($id, ['file_word' => 'writable/uploads/' . $filename]);

        return $this->response->download($outputPath, null)->setFileName($filename);
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
