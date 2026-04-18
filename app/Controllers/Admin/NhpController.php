<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NhpModel;
use App\Models\SptModel;

/**
 * NhpController — Notisi Hasil Pemeriksaan
 *
 * Alur: KT buat NHP dari simpulan AT → kirim ke entitas → entitas tanggapi
 * Status per item: pending → sesuai (tutup) | tidak_sesuai (masuk Matriks/LHP)
 *
 * Akses: KT, Dalnis, Admin (AT hanya bisa lihat Matriks Temuan)
 */
class NhpController extends BaseController
{
    protected NhpModel $nhpModel;
    protected SptModel $sptModel;

    public function __construct()
    {
        $this->nhpModel = new NhpModel();
        $this->sptModel = new SptModel();
    }

    // ──────────────────────────────────────────────────────────────────────
    // NHP Index per SPT
    // ──────────────────────────────────────────────────────────────────────

    public function index(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $nhpList    = $this->nhpModel->getBySpt($sptId);
        $allSimpulan= $this->nhpModel->getAllSimpulanBySpt($sptId);
        $matriks    = $this->nhpModel->getMatriksTemuan($sptId);

        return view('admin/nhp/index', [
            'title'      => 'NHP — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'        => $spt,
            'nhpList'    => $nhpList,
            'allSimpulan'=> $allSimpulan,
            'matriks'    => $matriks,
            'canManage'  => $this->canManage($sptId),
            'statusLabel'=> NhpModel::$statusLabel,
            'statusColor'=> NhpModel::$statusColor,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Create NHP
    // ──────────────────────────────────────────────────────────────────────

    public function create(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!$this->canManage($sptId)) return redirect()->back()->with('error', 'Akses ditolak.');

        $simpulanBelumNhp = $this->nhpModel->getSimpulanBelumNhp($sptId);

        return view('admin/nhp/create', [
            'title'           => 'Buat NHP — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'             => $spt,
            'simpulanBelumNhp'=> $simpulanBelumNhp,
        ]);
    }

    public function store(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt || !$this->canManage($sptId)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $post = $this->request->getPost();

        // Buat NHP header
        $nhpId = $this->nhpModel->create($sptId, [
            'tanggal_nhp' => $post['tanggal_nhp'] ?: null,
            'perihal'     => $post['perihal']      ?: null,
            'catatan'     => $post['catatan']      ?: null,
        ]);

        // Tambahkan item dari simpulan yang dipilih
        $selected = $post['simpulan_ids'] ?? [];
        if (!empty($selected)) {
            $db = \Config\Database::connect();
            $noUrut = 1;
            foreach ($selected as $simpulanId) {
                $simpulan = $db->table('kka_simpulan ks')
                    ->select('ks.*')
                    ->where('ks.id', (int)$simpulanId)
                    ->get()->getRowArray();

                if ($simpulan) {
                    $this->nhpModel->addItem($nhpId, [
                        'kka_simpulan_id' => (int)$simpulanId,
                        'nomor_urut'      => $noUrut++,
                        'judul_temuan'    => $post['judul_temuan'][$simpulanId] ?? null,
                        'kondisi'         => $simpulan['kondisi'],
                        'kriteria'        => $simpulan['kriteria'],
                        'sebab'           => $simpulan['sebab'],
                        'akibat'          => $simpulan['akibat'],
                        'rekomendasi'     => $simpulan['rekomendasi_awal'],
                        'nilai_temuan'    => $simpulan['nilai_financial'] ?? null,
                        'status_tanggapan'=> 'pending',
                    ]);
                }
            }
        }

        logActivity('nhp.create', 'nhp', "Buat NHP spt_id={$sptId}");
        return redirect()->to('/admin/spt/' . $sptId . '/nhp/' . $nhpId)
            ->with('success', 'NHP berhasil dibuat.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Show NHP
    // ──────────────────────────────────────────────────────────────────────

    public function show(int $sptId, int $nhpId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $nhp = $this->nhpModel->find($nhpId);
        if (!$nhp || (int)$nhp['spt_id'] !== $sptId) {
            return redirect()->back()->with('error', 'NHP tidak ditemukan.');
        }

        $items = $this->nhpModel->getItems($nhpId);

        return view('admin/nhp/show', [
            'title'          => 'Detail NHP — ' . ($nhp['nomor_nhp'] ?: '#' . $nhpId),
            'spt'            => $spt,
            'nhp'            => $nhp,
            'items'          => $items,
            'canManage'      => $this->canManage($sptId),
            'statusLabel'    => NhpModel::$statusLabel,
            'statusColor'    => NhpModel::$statusColor,
            'tanggapanLabel' => NhpModel::$tanggapanLabel,
            'tanggapanColor' => NhpModel::$tanggapanColor,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Kirim NHP (ubah status ke terkirim)
    // ──────────────────────────────────────────────────────────────────────

    public function kirim(int $sptId, int $nhpId)
    {
        $nhp = $this->nhpModel->find($nhpId);
        if (!$nhp || (int)$nhp['spt_id'] !== $sptId) {
            return redirect()->back()->with('error', 'NHP tidak ditemukan.');
        }
        if (!$this->canManage($sptId)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $items = $this->nhpModel->getItems($nhpId);
        if (empty($items)) {
            return redirect()->back()->with('error', 'NHP harus memiliki minimal 1 item sebelum dikirim.');
        }

        if ($this->nhpModel->kirim($nhpId)) {
            logActivity('nhp.kirim', 'nhp', "NHP terkirim id={$nhpId}");
            return redirect()->back()->with('success', 'NHP berhasil ditandai sebagai terkirim.');
        }

        return redirect()->back()->with('error', 'Gagal mengubah status NHP.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Catat Tanggapan Entitas per Item
    // ──────────────────────────────────────────────────────────────────────

    public function tanggapi(int $sptId, int $nhpId, int $itemId)
    {
        $nhp = $this->nhpModel->find($nhpId);
        if (!$nhp || (int)$nhp['spt_id'] !== $sptId) {
            return redirect()->back()->with('error', 'NHP tidak ditemukan.');
        }
        if (!$this->canManage($sptId)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        if ($nhp['status'] === 'draft') {
            return redirect()->back()->with('error', 'Kirimkan NHP terlebih dahulu sebelum mencatat tanggapan.');
        }

        $post   = $this->request->getPost();
        $status = in_array($post['status_tanggapan'], ['sesuai', 'tidak_sesuai'])
            ? $post['status_tanggapan']
            : 'pending';

        $this->nhpModel->recordTanggapan(
            $itemId,
            $post['tanggapan_entitas'] ?? '',
            $status,
            $post['tgl_tanggapan'] ?? null
        );

        logActivity('nhp.tanggapi', 'nhp_item', "Tanggapan item id={$itemId} status={$status}");
        return redirect()->to('/admin/spt/' . $sptId . '/nhp/' . $nhpId)
            ->with('success', 'Tanggapan berhasil dicatat.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Tambah Item Manual ke NHP yang sudah ada
    // ──────────────────────────────────────────────────────────────────────

    public function addItem(int $sptId, int $nhpId)
    {
        $nhp = $this->nhpModel->find($nhpId);
        if (!$nhp || (int)$nhp['spt_id'] !== $sptId) {
            return redirect()->back()->with('error', 'NHP tidak ditemukan.');
        }
        if (!$this->canManage($sptId) || $nhp['status'] !== 'draft') {
            return redirect()->back()->with('error', 'Hanya NHP berstatus draft yang bisa diedit.');
        }

        $post = $this->request->getPost();
        $simpulanId = $post['kka_simpulan_id'] ? (int)$post['kka_simpulan_id'] : null;

        $itemData = [
            'kka_simpulan_id' => $simpulanId,
            'judul_temuan'    => $post['judul_temuan']    ?? null,
            'kondisi'         => $post['kondisi']         ?? null,
            'kriteria'        => $post['kriteria']        ?? null,
            'sebab'           => $post['sebab']           ?? null,
            'akibat'          => $post['akibat']          ?? null,
            'rekomendasi'     => $post['rekomendasi']     ?? null,
            'nilai_temuan'    => $post['nilai_temuan']    ? (int)$post['nilai_temuan'] : null,
            'status_tanggapan'=> 'pending',
        ];

        if ($simpulanId) {
            $db = \Config\Database::connect();
            $simpulan = $db->table('kka_simpulan')->where('id', $simpulanId)->get()->getRowArray();
            if ($simpulan) {
                $itemData['kondisi']  = $itemData['kondisi']  ?: $simpulan['kondisi'];
                $itemData['kriteria'] = $itemData['kriteria'] ?: $simpulan['kriteria'];
                $itemData['sebab']    = $itemData['sebab']    ?: $simpulan['sebab'];
                $itemData['akibat']   = $itemData['akibat']   ?: $simpulan['akibat'];
                $itemData['rekomendasi']  = $itemData['rekomendasi'] ?: $simpulan['rekomendasi_awal'];
                $itemData['nilai_temuan'] = $itemData['nilai_temuan'] ?? $simpulan['nilai_financial'];
            }
        }

        $this->nhpModel->addItem($nhpId, $itemData);
        logActivity('nhp.item.add', 'nhp_item', "Tambah item ke nhp_id={$nhpId}");
        return redirect()->to('/admin/spt/' . $sptId . '/nhp/' . $nhpId)
            ->with('success', 'Item temuan berhasil ditambahkan ke NHP.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Selesaikan NHP
    // ──────────────────────────────────────────────────────────────────────

    public function selesai(int $sptId, int $nhpId)
    {
        $nhp = $this->nhpModel->find($nhpId);
        if (!$nhp || (int)$nhp['spt_id'] !== $sptId) {
            return redirect()->back()->with('error', 'NHP tidak ditemukan.');
        }
        if (!$this->canManage($sptId)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        // Cek apakah masih ada item pending
        $items   = $this->nhpModel->getItems($nhpId);
        $pending = array_filter($items, fn($i) => $i['status_tanggapan'] === 'pending');
        if (!empty($pending)) {
            return redirect()->back()->with('error', count($pending) . ' item masih menunggu tanggapan. Selesaikan semua tanggapan terlebih dahulu.');
        }

        if ($this->nhpModel->selesaikan($nhpId)) {
            logActivity('nhp.selesai', 'nhp', "NHP selesai id={$nhpId}");
            return redirect()->back()->with('success', 'NHP telah diselesaikan. Temuan yang tidak sesuai masuk ke Matriks Temuan.');
        }

        return redirect()->back()->with('error', 'Gagal menyelesaikan NHP.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Matriks Temuan (untuk LHP)
    // ──────────────────────────────────────────────────────────────────────

    public function matriks(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $matriks = $this->nhpModel->getMatriksTemuan($sptId);

        $totalNilai = array_sum(array_column($matriks, 'nilai_temuan'));

        return view('admin/nhp/matriks', [
            'title'      => 'Matriks Temuan — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'        => $spt,
            'matriks'    => $matriks,
            'totalNilai' => $totalNilai,
            'canManage'  => $this->canManage($sptId),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    private function canManage(int $sptId): bool
    {
        return isAuditAdmin() || isDalnisInSpt($sptId) || isKtInSpt($sptId);
    }
}
