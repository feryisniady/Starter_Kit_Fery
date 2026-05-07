<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SptModel;
use App\Models\SptKmModel;
use App\Models\RoutingSlipModel;

/**
 * Routing Slip — alur review dokumen/KKP per penugasan.
 *
 * Alur: AT/KT buat slip → KT review → Dalnis review → PJ review → Selesai
 * Jika dikembalikan: pengirim revisi → resubmit dari awal
 */
class RoutingSlipController extends BaseController
{
    protected SptModel        $sptModel;
    protected RoutingSlipModel $slipModel;

    public function __construct()
    {
        $this->sptModel  = new SptModel();
        $this->slipModel = new RoutingSlipModel();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helper — ambil SPT + guard
    // ──────────────────────────────────────────────────────────────────────

    private function getSpt(int $sptId): ?array
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt || !canViewSptAudit($sptId)) return null;
        return $spt;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Index — daftar semua routing slip milik SPT ini
    // ──────────────────────────────────────────────────────────────────────

    public function index(int $sptId)
    {
        $spt = $this->getSpt($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $slips = $this->slipModel->getForSpt($sptId);

        return view('admin/routing_slip/index', [
            'title'    => 'Routing Slip — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'      => $spt,
            'slips'    => $slips,
            'peranSpt' => getPeranInSpt($sptId),
            'canCreate'=> isInSpt($sptId) || isAuditAdmin(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Create / store
    // ──────────────────────────────────────────────────────────────────────

    public function create(int $sptId)
    {
        $spt = $this->getSpt($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');
        if (!isInSpt($sptId) && !isAuditAdmin())
            return redirect()->back()->with('error', 'Hanya anggota tim yang dapat membuat routing slip.');

        return view('admin/routing_slip/form', [
            'title'   => 'Buat Routing Slip',
            'spt'     => $spt,
            'row'     => null,
            'isEdit'  => false,
        ]);
    }

    public function store(int $sptId)
    {
        $spt = $this->getSpt($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $rules = [
            'judul'         => 'required|max_length[200]',
            'jenis_dokumen' => 'permit_empty|max_length[50]',
            'keterangan'    => 'permit_empty',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $sdmId = getCurrentSdmId();
        $post  = $this->request->getPost();

        $slipId = $this->slipModel->insert([
            'spt_id'          => $sptId,
            'judul'           => trim($post['judul']),
            'jenis_dokumen'   => $post['jenis_dokumen'] ?? null,
            'keterangan'      => trim($post['keterangan'] ?? ''),
            'pengirim_sdm_id' => $sdmId,
            'tanggal_kirim'   => date('Y-m-d H:i:s'),
            'status'          => 'kt_review',   // langsung submit saat create
        ]);

        logActivity('routing_slip.create', 'spt_routing_slip',
            "Routing slip #{$slipId} dibuat untuk SPT id={$sptId}");

        // Notif ke KT
        $this->notifKt($sptId, $slipId, $post['judul']);

        return redirect()->to("/admin/spt/{$sptId}/routing-slip")
            ->with('success', 'Routing slip berhasil dibuat dan dikirim ke Ketua Tim.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Edit / update (hanya saat draft atau dikembalikan)
    // ──────────────────────────────────────────────────────────────────────

    public function edit(int $sptId, int $id)
    {
        $spt  = $this->getSpt($sptId);
        $slip = $this->slipModel->getDetail($id);
        if (!$spt || !$slip || $slip['spt_id'] != $sptId)
            return redirect()->back()->with('error', 'Data tidak ditemukan.');

        if (!RoutingSlipModel::canEdit($slip) && !isAuditAdmin())
            return redirect()->back()->with('error', 'Routing slip tidak dapat diedit pada status ini.');

        return view('admin/routing_slip/form', [
            'title'  => 'Edit & Resubmit Routing Slip',
            'spt'    => $spt,
            'row'    => $slip,
            'isEdit' => true,
        ]);
    }

    public function update(int $sptId, int $id)
    {
        $spt  = $this->getSpt($sptId);
        $slip = $this->slipModel->find($id);
        if (!$spt || !$slip || $slip['spt_id'] != $sptId)
            return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $rules = [
            'judul'         => 'required|max_length[200]',
            'jenis_dokumen' => 'permit_empty|max_length[50]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $this->slipModel->update($id, [
            'judul'         => trim($post['judul']),
            'jenis_dokumen' => $post['jenis_dokumen'] ?? null,
            'keterangan'    => trim($post['keterangan'] ?? ''),
        ]);

        // Jika dikembalikan → resubmit otomatis
        if ($slip['status'] === 'dikembalikan') {
            $this->slipModel->resubmit($id);
            logActivity('routing_slip.resubmit', 'spt_routing_slip', "Routing slip #{$id} diresubmit");
            $this->notifKt($sptId, $id, $post['judul']);
            return redirect()->to("/admin/spt/{$sptId}/routing-slip")
                ->with('success', 'Routing slip diperbarui dan dikirim ulang ke Ketua Tim.');
        }

        return redirect()->to("/admin/spt/{$sptId}/routing-slip")
            ->with('success', 'Routing slip berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Review — terima atau kembalikan
    // ──────────────────────────────────────────────────────────────────────

    public function review(int $sptId, int $id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post')
            return redirect()->back();

        $spt  = $this->getSpt($sptId);
        $slip = $this->slipModel->find($id);
        if (!$spt || !$slip || $slip['spt_id'] != $sptId)
            return $this->jsonError('Data tidak ditemukan.');

        if (!RoutingSlipModel::canReview($slip, $sptId))
            return $this->jsonError('Anda tidak berwenang me-review pada tahap ini.');

        $action  = $this->request->getPost('action');   // 'diterima' | 'dikembalikan'
        $catatan = trim($this->request->getPost('catatan') ?? '');
        $stage   = RoutingSlipModel::getCurrentStage($slip);
        $sdmId   = getCurrentSdmId();

        if (!in_array($action, ['diterima', 'dikembalikan']))
            return $this->jsonError('Aksi tidak valid.');

        $ok = $this->slipModel->processReview($id, $stage, $action, $sdmId, $catatan);
        if (!$ok) return $this->jsonError('Gagal memproses review.');

        $slipUpdated = $this->slipModel->find($id);
        $statusLabel = RoutingSlipModel::STATUS_LABEL[$slipUpdated['status']] ?? $slipUpdated['status'];

        logActivity("routing_slip.{$action}", 'spt_routing_slip',
            "Routing slip #{$id} di-{$action} oleh stage={$stage} sdm_id={$sdmId}");

        // Notif ke pengirim jika dikembalikan
        if ($action === 'dikembalikan' && $slip['pengirim_sdm_id']) {
            $sdmPengirim = \Config\Database::connect()->table('sdm')
                ->where('id', $slip['pengirim_sdm_id'])->get()->getRowArray();
            if ($sdmPengirim) {
                $userPengirim = \Config\Database::connect()->table('users')
                    ->where('id', $sdmPengirim['user_id'] ?? 0)->get()->getRowArray();
                if ($userPengirim) {
                    notify(
                        $userPengirim['id'],
                        'Routing Slip Dikembalikan',
                        "Routing slip \"{$slip['judul']}\" dikembalikan untuk diperbaiki.",
                        "/admin/spt/{$sptId}/routing-slip/{$id}/edit",
                        'warning'
                    );
                }
            }
        }

        // Notif ke reviewer berikutnya jika diterima
        if ($action === 'diterima') {
            $this->notifNextReviewer($sptId, $id, $slipUpdated['status'], $slip['judul']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Berhasil. Status: {$statusLabel}",
            'status'  => $slipUpdated['status'],
            'label'   => $statusLabel,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Print view
    // ──────────────────────────────────────────────────────────────────────

    public function printSlip(int $sptId, int $id)
    {
        $spt  = $this->getSpt($sptId);
        $slip = $this->slipModel->getDetail($id);
        if (!$spt || !$slip || $slip['spt_id'] != $sptId)
            return redirect()->back()->with('error', 'Data tidak ditemukan.');

        return view('admin/routing_slip/print', [
            'spt'  => $spt,
            'slip' => $slip,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function jsonError(string $msg): \CodeIgniter\HTTP\Response
    {
        return $this->response->setStatusCode(422)->setJSON(['success' => false, 'message' => $msg]);
    }

    private function notifKt(int $sptId, int $slipId, string $judul): void
    {
        $tim = \Config\Database::connect()
            ->table('spt_tim st')
            ->select('u.id as user_id')
            ->join('sdm s', 's.id = st.sdm_id')
            ->join('users u', 'u.id = s.user_id')
            ->where('st.spt_id', $sptId)
            ->where('LOWER(st.peran_spt) LIKE', '%ketua%')
            ->get()->getResultArray();

        $userIds = array_column($tim, 'user_id');
        if ($userIds) {
            notify($userIds,
                'Routing Slip Baru',
                "Routing slip \"{$judul}\" menunggu review Anda.",
                "/admin/spt/{$sptId}/routing-slip",
                'info'
            );
        }
    }

    private function notifNextReviewer(int $sptId, int $slipId, string $newStatus, string $judul): void
    {
        $peranFilter = match ($newStatus) {
            'dalnis_review' => '%pengendali%',
            'pj_review'     => '%penanggung%',
            default         => null,
        };
        if (!$peranFilter) return;

        $tim = \Config\Database::connect()
            ->table('spt_tim st')
            ->select('u.id as user_id')
            ->join('sdm s', 's.id = st.sdm_id')
            ->join('users u', 'u.id = s.user_id')
            ->where('st.spt_id', $sptId)
            ->where('LOWER(st.peran_spt) LIKE', $peranFilter)
            ->get()->getResultArray();

        $userIds = array_column($tim, 'user_id');
        if ($userIds) {
            notify($userIds,
                'Routing Slip Menunggu Review',
                "Routing slip \"{$judul}\" diteruskan ke Anda untuk direview.",
                "/admin/spt/{$sptId}/routing-slip",
                'info'
            );
        }
    }
}
