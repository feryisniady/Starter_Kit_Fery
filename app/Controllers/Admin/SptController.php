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
        $spt = $this->sptModel->find($id);
        if (!$spt || $spt['status'] !== 'draft') {
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
        $spt = $this->sptModel->find($id);
        if (!$spt || $spt['status'] !== 'draft') {
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
        return redirect()->to('/admin/spt/' . $id)->with('success', 'SPT berhasil diajukan untuk persetujuan.');
    }

    public function approve(int $id)
    {
        $spt    = $this->sptModel->find($id);
        $userId = session()->get('user_id');

        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $tahap     = $this->sptModel->getNextApprovalTahap($spt['status']);
        $nextStatus = $this->sptModel->getNextStatus($spt['status']);

        if (!$tahap || !$nextStatus) {
            return redirect()->back()->with('error', 'Status SPT tidak bisa di-approve.');
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
        $spt    = $this->sptModel->find($id);
        $userId = session()->get('user_id');

        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $tahap   = $this->sptModel->getNextApprovalTahap($spt['status']);
        $catatan = $this->request->getPost('catatan') ?: 'Ditolak';

        if ($tahap) $this->approvalModel->reject($id, $tahap, $userId, $catatan);
        $this->sptModel->update($id, ['status' => 'draft', 'catatan' => $catatan]);

        logActivity('spt.reject', 'spt', "Tolak SPT id={$id}, catatan: {$catatan}");
        return redirect()->to('/admin/spt/' . $id)->with('error', 'SPT ditolak dan dikembalikan ke draft.');
    }

    // ===================================================
    // GENERATE WORD
    // ===================================================

    public function downloadWord(int $id)
    {
        $spt = $this->sptModel->getDetail($id);
        if (!$spt) return redirect()->back()->with('error', 'SPT tidak ditemukan.');

        $templatePath = FCPATH . 'assets/templates/spt_template.docx';
        if (!is_file($templatePath)) {
            return redirect()->back()->with('error', 'Template SPT tidak ditemukan. Upload terlebih dahulu di folder assets/templates/.');
        }

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

        // Isi placeholder
        $templateProcessor->setValue('nomor_naskah',    $spt['nomor_naskah'] ?? '');
        $templateProcessor->setValue('dasar_penugasan', $spt['dasar_1'] ?? '');
        $templateProcessor->setValue('tujuan',          $spt['tujuan'] ?? '');
        $templateProcessor->setValue('start',           $spt['tanggal_mulai'] ? tgl_indo($spt['tanggal_mulai']) : '');
        $templateProcessor->setValue('end',             $spt['tanggal_selesai'] ? tgl_indo($spt['tanggal_selesai']) : '');
        $templateProcessor->setValue('tanggal_naskah',  $spt['tanggal_naskah'] ? tgl_indo($spt['tanggal_naskah']) : '');
        $templateProcessor->setValue('jabatan_pengirim',$spt['penandatangan_jabatan'] ?? 'Inspektur Daerah');
        $templateProcessor->setValue('nama_pengirim',   $spt['penandatangan_nama'] ?? '');
        $templateProcessor->setValue('nip_pengirim',    'NIP. ' . ($spt['penandatangan_nip'] ?? ''));
        $templateProcessor->setValue('tujuan_naskah',   $spt['tembusan'] ?? '');

        // Baris tim (clone row)
        $tim = $spt['tim'];
        $templateProcessor->cloneRow('nm_pegawai', count($tim));
        foreach ($tim as $i => $t) {
            $no = $i + 1;
            $templateProcessor->setValue("no#{$no}",          $no);
            $templateProcessor->setValue("nm_pegawai#{$no}",  $t['sdm_nama']);
            $templateProcessor->setValue("nm_jabatan_st#{$no}", $t['peran_spt']);
            $templateProcessor->setValue("desk#{$no}",        $t['hp_desk']);
            $templateProcessor->setValue("field#{$no}",       $t['hp_field']);
        }

        // Simpan ke temp
        $filename   = 'SPT_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $spt['nomor_naskah'] ?: $id) . '.docx';
        $outputPath = WRITEPATH . 'uploads/' . $filename;
        $templateProcessor->saveAs($outputPath);

        // Update path di DB
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
        return session()->get('is_superadmin') || hasPermission('spt.manage_all');
    }

    private function getUserIrbanId(int $userId): ?int
    {
        $sdm = $this->sdmModel->where('user_id', $userId)->first();
        return $sdm ? (int)$sdm['irban_id'] : null;
    }
}
