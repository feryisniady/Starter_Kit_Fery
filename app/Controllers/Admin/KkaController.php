<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KkaModel;
use App\Models\SptModel;
use App\Models\SdmModel;

/**
 * KkaController — Kertas Kerja Audit
 *
 * Akses:
 *   AT (auditor, anggota tim): hanya bisa lihat & isi KKA miliknya sendiri
 *   KT (ketua tim dalam SPT) + dalnis + admin: bisa lihat semua KKA per SPT
 *   Dalnis: tambahan bisa input catatan_dalnis
 *
 * Alur sequential per KKA:
 *   draft → ikhtisar_selesai → simpulan_selesai → selesai
 */
class KkaController extends BaseController
{
    protected KkaModel $kkaModel;
    protected SptModel $sptModel;
    protected SdmModel $sdmModel;

    public function __construct()
    {
        $this->kkaModel = new KkaModel();
        $this->sptModel = new SptModel();
        $this->sdmModel = new SdmModel();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Dashboard KKA per SPT
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Dashboard KKA per SPT.
     * AT: langsung redirect ke KKA miliknya.
     * KT / Dalnis / Admin: tampilkan daftar semua AT.
     */
    public function index(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');

        $sdm = $this->getCurrentSdm();

        // AT biasa → redirect ke KKA sendiri
        if (!$this->canViewAll($sptId, $sdm)) {
            if (!$sdm) return redirect()->to('/admin/spt')->with('error', 'Profil SDM Anda belum terdaftar.');

            $kka = $this->kkaModel->getByAt($sptId, $sdm['id']);
            if (!$kka) return redirect()->to('/admin/spt/' . $sptId . '/km')
                ->with('error', 'KKA Anda belum dibuat. Tunggu Dalnis menyetujui KM-5.');

            return redirect()->to('/admin/kka/' . $kka['id']);
        }

        // KT / Dalnis / Admin
        $kkaList  = $this->kkaModel->getBySpt($sptId);
        $progress = $this->kkaModel->getProgressBySpt($sptId);

        return view('admin/kka/index', [
            'title'    => 'KKA — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'      => $spt,
            'kkaList'  => $kkaList,
            'progress' => $progress,
            'statusLabel' => KkaModel::$statusLabel,
            'statusColor' => KkaModel::$statusColor,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Detail KKA (Ikhtisar / Simpulan / Rekomendasi)
    // ──────────────────────────────────────────────────────────────────────

    public function show(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka) return redirect()->back()->with('error', 'KKA tidak ditemukan.');

        if (!$this->canAccessKka($kka)) {
            return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');
        }

        $spt = $this->sptModel->getDetail($kka['spt_id']);

        return view('admin/kka/show', [
            'title'       => 'KKA — ' . $kka['nama'],
            'kka'         => $kka,
            'spt'         => $spt,
            'ikhtisar'    => $this->kkaModel->getIkhtisarByKka($kkaId),
            'simpulan'    => $this->kkaModel->getSimpulanByKka($kkaId),
            'rekomendasi' => $this->kkaModel->getRekomendasiByKka($kkaId),
            'statusLabel' => KkaModel::$statusLabel,
            'statusColor' => KkaModel::$statusColor,
            'canEdit'     => $this->canEditKka($kka),
            'isDalnis'    => hasRole('dalnis') || hasRole('superadmin') || hasRole('admin'),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Ikhtisar CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function storeIkhtisar(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka || !$this->canEditKka($kka)) {
            return redirect()->back()->with('error', 'Akses ditolak atau KKA tidak ditemukan.');
        }
        if ($kka['status'] !== 'draft') {
            return redirect()->back()->with('error', 'Ikhtisar sudah selesai, tidak dapat ditambah lagi.');
        }

        $post = $this->request->getPost();
        $this->kkaModel->saveIkhtisar($kkaId, [
            'program_kerja'   => $post['program_kerja']   ?? null,
            'langkah_audit'   => $post['langkah_audit']   ?? null,
            'hasil_observasi' => $post['hasil_observasi'] ?? null,
            'simpulan'        => $post['simpulan']        ?? null,
        ]);

        logActivity('kka.ikhtisar.store', 'kka_ikhtisar', "Tambah ikhtisar kka_id={$kkaId}");
        return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'Item ikhtisar berhasil ditambahkan.');
    }

    public function updateIkhtisar(int $id)
    {
        $item = $this->kkaModel->findIkhtisar($id);
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $kka = $this->kkaModel->find($item['kka_id']);
        if (!$kka || !$this->canEditKka($kka) || $kka['status'] !== 'draft') {
            return redirect()->back()->with('error', 'Tidak dapat mengedit — status bukan draft.');
        }

        $post = $this->request->getPost();
        $this->kkaModel->updateIkhtisar($id, [
            'program_kerja'   => $post['program_kerja']   ?? null,
            'langkah_audit'   => $post['langkah_audit']   ?? null,
            'hasil_observasi' => $post['hasil_observasi'] ?? null,
            'simpulan'        => $post['simpulan']        ?? null,
        ]);

        logActivity('kka.ikhtisar.update', 'kka_ikhtisar', "Update ikhtisar id={$id}");
        return redirect()->to('/admin/kka/' . $item['kka_id'])->with('success', 'Ikhtisar berhasil diperbarui.');
    }

    public function deleteIkhtisar(int $id)
    {
        $item = $this->kkaModel->findIkhtisar($id);
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $kka = $this->kkaModel->find($item['kka_id']);
        if (!$kka || !$this->canEditKka($kka) || $kka['status'] !== 'draft') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus.');
        }

        $this->kkaModel->deleteIkhtisar($id);
        logActivity('kka.ikhtisar.delete', 'kka_ikhtisar', "Hapus ikhtisar id={$id}");
        return redirect()->to('/admin/kka/' . $item['kka_id'])->with('success', 'Item ikhtisar dihapus.');
    }

    /** Tandai semua ikhtisar selesai → lanjut ke tahap simpulan */
    public function selesaiIkhtisar(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka || !$this->canEditKka($kka)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $count = count($this->kkaModel->getIkhtisarByKka($kkaId));
        if ($count === 0) {
            return redirect()->back()->with('error', 'Tambahkan minimal 1 item ikhtisar sebelum menyelesaikan.');
        }

        if ($this->kkaModel->selesaikanIkhtisar($kkaId)) {
            logActivity('kka.ikhtisar.selesai', 'kka', "Ikhtisar selesai kka_id={$kkaId}");
            return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'Ikhtisar selesai. Silakan lanjutkan ke Simpulan.');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui status.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Simpulan CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function storeSimpulan(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka || !$this->canEditKka($kka)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        if ($kka['status'] !== 'ikhtisar_selesai') {
            return redirect()->back()->with('error', 'Selesaikan ikhtisar terlebih dahulu.');
        }

        $post = $this->request->getPost();
        $this->kkaModel->saveSimpulan($kkaId, [
            'kondisi'          => $post['kondisi']          ?? null,
            'kriteria'         => $post['kriteria']         ?? null,
            'sebab'            => $post['sebab']            ?? null,
            'akibat'           => $post['akibat']           ?? null,
            'rekomendasi_awal' => $post['rekomendasi_awal'] ?? null,
        ]);

        logActivity('kka.simpulan.store', 'kka_simpulan', "Tambah simpulan kka_id={$kkaId}");
        return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'Simpulan berhasil ditambahkan.');
    }

    public function updateSimpulan(int $id)
    {
        $item = $this->kkaModel->findSimpulan($id);
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $kka = $this->kkaModel->find($item['kka_id']);
        if (!$kka || !$this->canEditKka($kka) || $kka['status'] !== 'ikhtisar_selesai') {
            return redirect()->back()->with('error', 'Tidak dapat mengedit.');
        }

        $post = $this->request->getPost();
        $this->kkaModel->updateSimpulan($id, [
            'kondisi'          => $post['kondisi']          ?? null,
            'kriteria'         => $post['kriteria']         ?? null,
            'sebab'            => $post['sebab']            ?? null,
            'akibat'           => $post['akibat']           ?? null,
            'rekomendasi_awal' => $post['rekomendasi_awal'] ?? null,
        ]);

        logActivity('kka.simpulan.update', 'kka_simpulan', "Update simpulan id={$id}");
        return redirect()->to('/admin/kka/' . $item['kka_id'])->with('success', 'Simpulan berhasil diperbarui.');
    }

    public function deleteSimpulan(int $id)
    {
        $item = $this->kkaModel->findSimpulan($id);
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $kka = $this->kkaModel->find($item['kka_id']);
        if (!$kka || !$this->canEditKka($kka) || $kka['status'] !== 'ikhtisar_selesai') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus.');
        }

        $this->kkaModel->deleteSimpulan($id);
        logActivity('kka.simpulan.delete', 'kka_simpulan', "Hapus simpulan id={$id}");
        return redirect()->to('/admin/kka/' . $item['kka_id'])->with('success', 'Simpulan dihapus.');
    }

    public function selesaiSimpulan(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka || !$this->canEditKka($kka)) return redirect()->back()->with('error', 'Akses ditolak.');

        $count = count($this->kkaModel->getSimpulanByKka($kkaId));
        if ($count === 0) {
            return redirect()->back()->with('error', 'Tambahkan minimal 1 simpulan sebelum menyelesaikan.');
        }

        if ($this->kkaModel->selesaikanSimpulan($kkaId)) {
            logActivity('kka.simpulan.selesai', 'kka', "Simpulan selesai kka_id={$kkaId}");
            return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'Simpulan selesai. Silakan lanjutkan ke Rekomendasi.');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui status.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Rekomendasi CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function storeRekomendasi(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka || !$this->canEditKka($kka)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        if ($kka['status'] !== 'simpulan_selesai') {
            return redirect()->back()->with('error', 'Selesaikan simpulan terlebih dahulu.');
        }

        $post = $this->request->getPost();
        $this->kkaModel->saveRekomendasi($kkaId, [
            'uraian_rekomendasi'      => $post['uraian_rekomendasi']      ?? null,
            'pihak_bertanggung_jawab' => $post['pihak_bertanggung_jawab'] ?? null,
            'target_penyelesaian'     => $post['target_penyelesaian']     ?: null,
            'tanggapan_auditi'        => $post['tanggapan_auditi']        ?? null,
        ]);

        logActivity('kka.rekomendasi.store', 'kka_rekomendasi', "Tambah rekomendasi kka_id={$kkaId}");
        return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'Rekomendasi berhasil ditambahkan.');
    }

    public function updateRekomendasi(int $id)
    {
        $item = $this->kkaModel->findRekomendasi($id);
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $kka = $this->kkaModel->find($item['kka_id']);
        if (!$kka || !$this->canEditKka($kka) || $kka['status'] !== 'simpulan_selesai') {
            return redirect()->back()->with('error', 'Tidak dapat mengedit.');
        }

        $post = $this->request->getPost();
        $this->kkaModel->updateRekomendasi($id, [
            'uraian_rekomendasi'      => $post['uraian_rekomendasi']      ?? null,
            'pihak_bertanggung_jawab' => $post['pihak_bertanggung_jawab'] ?? null,
            'target_penyelesaian'     => $post['target_penyelesaian']     ?: null,
            'tanggapan_auditi'        => $post['tanggapan_auditi']        ?? null,
        ]);

        logActivity('kka.rekomendasi.update', 'kka_rekomendasi', "Update rekomendasi id={$id}");
        return redirect()->to('/admin/kka/' . $item['kka_id'])->with('success', 'Rekomendasi berhasil diperbarui.');
    }

    public function deleteRekomendasi(int $id)
    {
        $item = $this->kkaModel->findRekomendasi($id);
        if (!$item) return redirect()->back()->with('error', 'Data tidak ditemukan.');

        $kka = $this->kkaModel->find($item['kka_id']);
        if (!$kka || !$this->canEditKka($kka) || $kka['status'] !== 'simpulan_selesai') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus.');
        }

        $this->kkaModel->deleteRekomendasi($id);
        logActivity('kka.rekomendasi.delete', 'kka_rekomendasi', "Hapus rekomendasi id={$id}");
        return redirect()->to('/admin/kka/' . $item['kka_id'])->with('success', 'Rekomendasi dihapus.');
    }

    public function selesaiRekomendasi(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka || !$this->canEditKka($kka)) return redirect()->back()->with('error', 'Akses ditolak.');

        $count = count($this->kkaModel->getRekomendasiByKka($kkaId));
        if ($count === 0) {
            return redirect()->back()->with('error', 'Tambahkan minimal 1 rekomendasi sebelum menyelesaikan.');
        }

        if ($this->kkaModel->selesaikanRekomendasi($kkaId)) {
            logActivity('kka.rekomendasi.selesai', 'kka', "KKA selesai kka_id={$kkaId}");
            return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'KKA selesai sepenuhnya.');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui status.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Catatan Dalnis
    // ──────────────────────────────────────────────────────────────────────

    public function saveCatatanDalnis(int $kkaId)
    {
        if (!hasRole('dalnis') && !hasRole('superadmin') && !hasRole('admin')) {
            return redirect()->back()->with('error', 'Hanya Dalnis yang dapat menambah catatan.');
        }

        $kka = $this->kkaModel->find($kkaId);
        if (!$kka) return redirect()->back()->with('error', 'KKA tidak ditemukan.');

        $db = \Config\Database::connect();
        $db->table('kka')->where('id', $kkaId)->update([
            'catatan_dalnis' => $this->request->getPost('catatan_dalnis'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        logActivity('kka.catatan_dalnis', 'kka', "Catatan dalnis kka_id={$kkaId}");
        return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'Catatan Dalnis berhasil disimpan.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────

    /** Ambil record SDM milik user yang sedang login */
    private function getCurrentSdm(): ?array
    {
        $userId = session()->get('user_id');
        return $userId ? $this->sdmModel->where('user_id', $userId)->first() : null;
    }

    /**
     * Apakah user saat ini boleh melihat SEMUA KKA dalam SPT ini?
     * Admin / dalnis / evlap / ka_irban / inspektur / sekretaris → ya.
     * Juga KT dari SPT yang sama (peran_spt berisi 'ketua tim').
     */
    private function canViewAll(int $sptId, ?array $sdm): bool
    {
        if (hasRole('superadmin') || hasRole('admin')   || hasRole('dalnis')   ||
            hasRole('evlap')      || hasRole('subbag_evlap') ||
            hasRole('ka_irban')   || hasRole('inspektur') || hasRole('sekretaris')) {
            return true;
        }

        if (!$sdm) return false;

        // Cek peran dalam SPT ini
        $db     = \Config\Database::connect();
        $member = $db->table('spt_tim')
            ->where('spt_id', $sptId)
            ->where('sdm_id', $sdm['id'])
            ->get()->getRowArray();

        if (!$member) return false;

        // Ketua Tim bisa lihat semua
        return str_contains(strtolower($member['peran_spt']), 'ketua');
    }

    /**
     * Apakah user boleh mengakses KKA ini (lihat)?
     * Canview all → ya.
     * Auditor → hanya jika kka.sdm_id == sdm miliknya.
     */
    private function canAccessKka(array $kka): bool
    {
        $sdm = $this->getCurrentSdm();
        if ($this->canViewAll($kka['spt_id'], $sdm)) return true;

        return $sdm && (int)$kka['sdm_id'] === (int)$sdm['id'];
    }

    /**
     * Apakah user boleh mengedit KKA ini?
     * Hanya AT pemilik KKA yang boleh isi; admin/dalnis juga boleh.
     */
    private function canEditKka(array $kka): bool
    {
        if (hasRole('superadmin') || hasRole('admin')) return true;

        $sdm = $this->getCurrentSdm();
        return $sdm && (int)$kka['sdm_id'] === (int)$sdm['id'];
    }
}
