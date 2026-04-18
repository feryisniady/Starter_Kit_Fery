<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NhpModel;

/**
 * AuditiController — Dashboard untuk pihak yang diaudit (auditi/entitas).
 *
 * Akses: hanya user dengan role 'auditi' yang sudah dikaitkan ke entitas.
 * Auditi bisa:
 *   - Melihat semua NHP yang ditujukan kepada entitasnya
 *   - Melihat daftar temuan per NHP
 *   - Memberikan tanggapan + upload bukti TL per temuan
 */
class AuditiController extends BaseController
{
    protected NhpModel $nhpModel;

    public function __construct()
    {
        $this->nhpModel = new NhpModel();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Dashboard
    // ──────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        $userId    = session()->get('user_id');
        $entitas   = $this->getMyEntitas($userId);

        if (!$entitas) {
            return view('admin/auditi/no_entitas', [
                'title' => 'Dashboard Auditi',
            ]);
        }

        $nhpList   = $this->nhpModel->getNhpByEntitas((int)$entitas['id']);
        $allItems  = $this->nhpModel->getNhpItemsByEntitas((int)$entitas['id']);

        // Statistik ringkas
        $stats = [
            'total'        => count($allItems),
            'pending'      => count(array_filter($allItems, fn($i) => $i['status_tanggapan'] === 'pending')),
            'sesuai'       => count(array_filter($allItems, fn($i) => $i['status_tanggapan'] === 'sesuai')),
            'tidak_sesuai' => count(array_filter($allItems, fn($i) => $i['status_tanggapan'] === 'tidak_sesuai')),
        ];

        return view('admin/auditi/dashboard', [
            'title'   => 'Dashboard Auditi — ' . $entitas['nama'],
            'entitas' => $entitas,
            'nhpList' => $nhpList,
            'stats'   => $stats,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Detail NHP (daftar temuan + form tanggapan)
    // ──────────────────────────────────────────────────────────────────────

    public function showNhp(int $nhpId)
    {
        $userId  = session()->get('user_id');
        $entitas = $this->getMyEntitas($userId);
        if (!$entitas) return redirect()->to('/admin/auditi/dashboard')->with('error', 'Akun tidak terkait entitas.');

        $db  = \Config\Database::connect();
        $nhp = $db->table('nhp n')
            ->select('n.*, sp.nomor_naskah as spt_nomor')
            ->join('spt sp', 'sp.id = n.spt_id')
            ->where('n.id', $nhpId)
            ->get()->getRowArray();

        if (!$nhp || $nhp['status'] === 'draft') {
            return redirect()->to('/admin/auditi/dashboard')->with('error', 'NHP tidak ditemukan.');
        }

        // Pastikan NHP ini memang untuk entitas user
        if (!$this->nhpBelongsToEntitas($nhpId, (int)$entitas['id'])) {
            return redirect()->to('/admin/auditi/dashboard')->with('error', 'Akses ditolak.');
        }

        $items = $this->nhpModel->getItems($nhpId);

        return view('admin/auditi/nhp_detail', [
            'title'   => 'Detail NHP — ' . $nhp['nomor_nhp'],
            'entitas' => $entitas,
            'nhp'     => $nhp,
            'items'   => $items,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Tanggapan + Upload Bukti TL
    // ──────────────────────────────────────────────────────────────────────

    public function tanggapi(int $itemId)
    {
        $userId  = session()->get('user_id');
        $entitas = $this->getMyEntitas($userId);
        if (!$entitas) return redirect()->back()->with('error', 'Akses ditolak.');

        $item = $this->nhpModel->findItem($itemId);
        if (!$item) return redirect()->back()->with('error', 'Item tidak ditemukan.');

        // Pastikan item ini milik NHP yang terkait entitas user
        if (!$this->nhpBelongsToEntitas((int)$item['nhp_id'], (int)$entitas['id'])) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $post           = $this->request->getPost();
        $tanggapan      = trim($post['tanggapan_entitas'] ?? '');
        $statusTanggapan= in_array($post['status_tanggapan'] ?? '', ['sesuai', 'tidak_sesuai'])
                            ? $post['status_tanggapan'] : 'pending';

        if (empty($tanggapan)) {
            return redirect()->back()->with('error', 'Tanggapan tidak boleh kosong.');
        }

        // Upload bukti TL (opsional)
        $buktiPath = $item['bukti_tl'] ?? null;
        $file      = $this->request->getFile('bukti_tl');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext      = $file->getClientExtension();
            $newName  = 'bukti_' . $itemId . '_' . time() . '.' . $ext;
            $file->move(ROOTPATH . 'public/uploads/bukti_tl', $newName);
            $buktiPath = 'uploads/bukti_tl/' . $newName;
        }

        $updateData = [
            'tanggapan_entitas' => $tanggapan,
            'status_tanggapan'  => $statusTanggapan,
            'tgl_tanggapan'     => $post['tgl_tanggapan'] ?: date('Y-m-d'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];
        if ($buktiPath) $updateData['bukti_tl'] = $buktiPath;

        \Config\Database::connect()->table('nhp_item')->where('id', $itemId)->update($updateData);

        logActivity('auditi.tanggapi', 'nhp_item', "Tanggapan auditi item_id={$itemId}");
        return redirect()->back()->with('success', 'Tanggapan berhasil disimpan.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    private function getMyEntitas(int $userId): ?array
    {
        $db = \Config\Database::connect();
        return $db->table('entitas')
            ->where('user_id', $userId)
            ->where('aktif', 1)
            ->get()->getRowArray() ?: null;
    }

    private function nhpBelongsToEntitas(int $nhpId, int $entitasId): bool
    {
        $db = \Config\Database::connect();
        $count = $db->table('nhp n')
            ->join('spt sp', 'sp.id = n.spt_id')
            ->join('pkpt_kegiatan pk', 'pk.id = sp.pkpt_kegiatan_id')
            ->join('pkpt_entitas pe', 'pe.pkpt_kegiatan_id = pk.id')
            ->where('n.id', $nhpId)
            ->where('pe.entitas_id', $entitasId)
            ->countAllResults();
        return $count > 0;
    }
}
