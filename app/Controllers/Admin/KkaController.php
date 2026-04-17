<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KkaModel;
use App\Models\SptModel;

/**
 * KkaController — Kertas Kerja Audit
 *
 * Akses berbasis peran_spt (bukan system role):
 *   AT (anggota tim SPT): hanya bisa lihat & isi KKA miliknya sendiri
 *   KT (ketua tim) + Dalnis + Admin: bisa lihat semua KKA per SPT
 *   Dalnis: tambahan bisa input catatan_dalnis
 *
 * Alur sequential per KKA:
 *   draft → ikhtisar_selesai → simpulan_selesai → selesai
 */
class KkaController extends BaseController
{
    protected KkaModel $kkaModel;
    protected SptModel $sptModel;

    public function __construct()
    {
        $this->kkaModel = new KkaModel();
        $this->sptModel = new SptModel();
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
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        // AT biasa → redirect ke KKA sendiri
        if (!$this->canViewAll($sptId)) {
            $sdmId = getCurrentSdmId();
            if (!$sdmId) return redirect()->to('/admin/spt')->with('error', 'Profil SDM Anda belum terdaftar.');

            $kka = $this->kkaModel->getByAt($sptId, $sdmId);
            if (!$kka) return redirect()->to('/admin/spt/' . $sptId . '/km')
                ->with('error', 'KKA Anda belum dibuat. Tunggu Dalnis menyetujui KM-5.');

            return redirect()->to('/admin/kka/' . $kka['id']);
        }

        return view('admin/kka/index', [
            'title'       => 'KKA — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'         => $spt,
            'kkaList'     => $this->kkaModel->getBySpt($sptId),
            'progress'    => $this->kkaModel->getProgressBySpt($sptId),
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
        if (!canViewSptAudit($kka['spt_id'])) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        if (!$this->canAccessKka($kka)) {
            return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');
        }

        $spt = $this->sptModel->getDetail($kka['spt_id']);
        $db  = \Config\Database::connect();

        // PKA procedures untuk SPT ini (sumber ikhtisar AT)
        $pkaList = $db->table('pka p')
            ->select('p.*, s.nama as pic_nama')
            ->join('sdm s', 's.id = p.pic_sdm_id', 'left')
            ->where('p.spt_id', $kka['spt_id'])
            ->orderBy('p.nomor_urut')
            ->get()->getResultArray();

        // Lookup kode temuan (untuk dropdown simpulan)
        $kodeTemuanList = $db->table('kode_temuan')
            ->orderBy('kode')
            ->get()->getResultArray();

        return view('admin/kka/show', [
            'title'           => 'KKA — ' . $kka['nama'],
            'kka'             => $kka,
            'spt'             => $spt,
            'ikhtisar'        => $this->kkaModel->getIkhtisarByKka($kkaId),
            'simpulan'        => $this->kkaModel->getSimpulanByKka($kkaId),
            'rekomendasi'     => $this->kkaModel->getRekomendasiByKka($kkaId),
            'pkaList'         => $pkaList,
            'kodeTemuanList'  => $kodeTemuanList,
            'statusLabel'     => KkaModel::$statusLabel,
            'statusColor'     => KkaModel::$statusColor,
            'canEdit'         => $this->canEditKka($kka),
            'isDalnis'        => isAuditAdmin() || isDalnisInSpt($kka['spt_id']),
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
            'pka_id'          => $post['pka_id']          ? (int)$post['pka_id'] : null,
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
            'pka_id'          => $post['pka_id']          ? (int)$post['pka_id'] : null,
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
            'kode_temuan_id'   => $post['kode_temuan_id']   ? (int)$post['kode_temuan_id'] : null,
            'nilai_financial'  => $post['nilai_financial']  ? (int)$post['nilai_financial'] : null,
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
            'kode_temuan_id'   => $post['kode_temuan_id']   ? (int)$post['kode_temuan_id'] : null,
            'nilai_financial'  => $post['nilai_financial']  ? (int)$post['nilai_financial'] : null,
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
            'uraian_rekomendasi'           => $post['uraian_rekomendasi']           ?? null,
            'pihak_bertanggung_jawab'      => $post['pihak_bertanggung_jawab']      ?? null,
            'target_penyelesaian'          => $post['target_penyelesaian']          ?: null,
            'tanggapan_auditi'             => $post['tanggapan_auditi']             ?? null,
            'kode_rekomendasi'             => $post['kode_rekomendasi']             ?: null,
            'nilai_rekomendasi_financial'  => $post['nilai_rekomendasi_financial']  ? (int)$post['nilai_rekomendasi_financial'] : null,
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
            'uraian_rekomendasi'           => $post['uraian_rekomendasi']           ?? null,
            'pihak_bertanggung_jawab'      => $post['pihak_bertanggung_jawab']      ?? null,
            'target_penyelesaian'          => $post['target_penyelesaian']          ?: null,
            'tanggapan_auditi'             => $post['tanggapan_auditi']             ?? null,
            'kode_rekomendasi'             => $post['kode_rekomendasi']             ?: null,
            'nilai_rekomendasi_financial'  => $post['nilai_rekomendasi_financial']  ? (int)$post['nilai_rekomendasi_financial'] : null,
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
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka) return redirect()->back()->with('error', 'KKA tidak ditemukan.');

        if (!isAuditAdmin() && !isDalnisInSpt($kka['spt_id'])) {
            return redirect()->back()->with('error', 'Hanya Pengendali Teknis (Dalnis) yang dapat menambah catatan.');
        }

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

    /**
     * Apakah user boleh melihat SEMUA KKA dalam SPT ini?
     * KT, Dalnis, PJ, Admin, lintas-SPT roles → ya.
     * AT biasa → tidak (hanya boleh lihat KKA miliknya).
     */
    private function canViewAll(int $sptId): bool
    {
        if (isAuditAdmin()) return true;
        if (isDalnisInSpt($sptId)) return true;
        if (isKtInSpt($sptId)) return true;
        if (isPjInSpt($sptId)) return true;
        return hasRole('inspektur') || hasRole('sekretaris') ||
               hasRole('evlap')     || hasRole('subbag_evlap') ||
               hasPermission('spt.manage_all');
    }

    /**
     * Apakah user boleh mengakses (lihat) KKA ini?
     * canViewAll → ya. Auditor → hanya jika kka.sdm_id == sdm miliknya.
     */
    private function canAccessKka(array $kka): bool
    {
        if ($this->canViewAll($kka['spt_id'])) return true;
        $sdmId = getCurrentSdmId();
        return $sdmId !== null && (int)$kka['sdm_id'] === $sdmId;
    }

    /**
     * Apakah user boleh mengedit (isi) KKA ini?
     * Admin/Dalnis selalu boleh. AT hanya boleh isi KKA milik sendiri.
     */
    private function canEditKka(array $kka): bool
    {
        if (isAuditAdmin()) return true;
        if (isDalnisInSpt($kka['spt_id'])) return true;
        $sdmId = getCurrentSdmId();
        return $sdmId !== null && (int)$kka['sdm_id'] === $sdmId;
    }
}
