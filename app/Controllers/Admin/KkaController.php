<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KkaModel;
use App\Models\NhpModel;
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
    protected NhpModel $nhpModel;
    protected SptModel $sptModel;

    public function __construct()
    {
        $this->kkaModel = new KkaModel();
        $this->nhpModel = new NhpModel();
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
            'canViewAll'  => true,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // KT Compiled View — semua simpulan AT per SPT
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Rekapitulasi semua simpulan AT untuk KT / Dalnis.
     * Hanya dapat diakses oleh KT, Dalnis, PJ, dan Admin.
     */
    public function compiled(int $sptId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!$this->canViewAll($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $allSimpulan = $this->nhpModel->getAllSimpulanBySpt($sptId);

        // Hitung statistik
        $totalNilai = 0;
        $byJenis    = [];
        foreach ($allSimpulan as $s) {
            $totalNilai += (int)($s['nilai_financial'] ?? 0);
            $jenis = $s['kode_temuan_jenis'] ?? 'lainnya';
            if (!isset($byJenis[$jenis])) $byJenis[$jenis] = ['count' => 0, 'nilai' => 0];
            $byJenis[$jenis]['count']++;
            $byJenis[$jenis]['nilai'] += (int)($s['nilai_financial'] ?? 0);
        }

        $nhpList = $this->nhpModel->getBySpt($sptId);

        return view('admin/kka/compiled', [
            'title'       => 'Rekapitulasi Simpulan KKA — ' . ($spt['nomor_naskah'] ?: '#' . $sptId),
            'spt'         => $spt,
            'allSimpulan' => $allSimpulan,
            'nhpList'     => $nhpList,
            'totalNilai'  => $totalNilai,
            'byJenis'     => $byJenis,
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

        // PKA procedures: AT hanya lihat yang di-assign ke dia (via pka_assignment)
        // KT / Dalnis / Admin lihat semua prosedur SPT
        $pkaQuery = $db->table('pka p')
            ->select('p.*')
            ->where('p.spt_id', $kka['spt_id']);
        if (!isAuditAdmin() && !isDalnisInSpt($kka['spt_id']) && !isKtInSpt($kka['spt_id'])) {
            $pkaQuery->join('pka_assignment pa', 'pa.pka_id = p.id')
                     ->where('pa.sdm_id', $kka['sdm_id']);
        }
        $pkaList = $pkaQuery->orderBy('p.fase')->orderBy('p.nomor_urut')->get()->getResultArray();

        // Lookup kode temuan (untuk dropdown simpulan)
        $kodeTemuanList = $db->table('kode_temuan')
            ->orderBy('kode')
            ->get()->getResultArray();

        $isKt = isAuditAdmin() || isKtInSpt($kka['spt_id']) || isDalnisInSpt($kka['spt_id']);
        $isAt = !$isKt && $this->canAccessKka($kka);

        $statusKka  = $kka['status_kka'] ?? 'draft';
        $kkaApproved= $statusKka === 'approved';
        $canEdit    = $this->canEditKka($kka)
                      && in_array($kka['status'], ['draft','ikhtisar_selesai','simpulan_selesai'])
                      && !$kkaApproved;

        return view('admin/kka/show', [
            'title'            => 'KKA — ' . $kka['nama'],
            'kka'              => $kka,
            'spt'              => $spt,
            'prosedurData'     => $this->kkaModel->getProsedurData($kkaId, $pkaList),
            'pkaList'          => $pkaList,
            'kodeTemuanList'   => $kodeTemuanList,
            'statusLabel'      => KkaModel::$statusLabel,
            'statusColor'      => KkaModel::$statusColor,
            'statusKkaLabel'   => KkaModel::$statusKkaLabel,
            'statusKkaColor'   => KkaModel::$statusKkaColor,
            'canEdit'          => $canEdit,
            'isDalnis'         => isAuditAdmin() || isDalnisInSpt($kka['spt_id']),
            'isKt'             => $isKt,
            'isAt'             => $isAt,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Unified save per prosedur (alur baru)
    // ──────────────────────────────────────────────────────────────────────

    public function saveProsedur(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka || !$this->canEditKka($kka)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        if (!in_array($kka['status'], ['draft', 'ikhtisar_selesai', 'simpulan_selesai'])) {
            return redirect()->back()->with('error', 'KKA sudah selesai, tidak bisa diedit.');
        }
        if (($kka['status_kka'] ?? 'draft') === 'approved') {
            return redirect()->back()->with('error', 'KKA sudah disetujui KT, tidak bisa diedit.');
        }

        $this->kkaModel->saveProsedurUnified($kkaId, $this->request->getPost());
        logActivity('kka.prosedur.save', 'kka', "Save prosedur unified kka_id={$kkaId}");
        return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'Prosedur berhasil disimpan.');
    }

    /** AT menyelesaikan KKA (draft → selesai) */
    public function selesaikanKka(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka || !$this->canEditKka($kka)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        $jumlahIkh = count($this->kkaModel->getIkhtisarByKka($kkaId));
        if ($jumlahIkh === 0) {
            return redirect()->back()->with('error', 'Isi minimal 1 prosedur sebelum menyelesaikan KKA.');
        }

        if ($this->kkaModel->selesaikanKka($kkaId)) {
            logActivity('kka.selesai', 'kka', "KKA selesai kka_id={$kkaId}");
            return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'KKA selesai. Silakan kirim ke Ketua Tim.');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui status KKA.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Ikhtisar CRUD (lama — tetap berjalan untuk data existing)
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
    // Submit / Review KKA (AT → KT)
    // ──────────────────────────────────────────────────────────────────────

    /** AT mengajukan KKA ke KT */
    public function submitKka(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka || !$this->canAccessKka($kka)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        if ($kka['status'] !== 'selesai') {
            return redirect()->back()->with('error', 'KKA harus diselesaikan (semua tahap) sebelum dikirim ke KT.');
        }

        if ($this->kkaModel->submitKka($kkaId)) {
            logActivity('kka.submit', 'kka', "KKA submitted ke KT kka_id={$kkaId}");
            return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'KKA berhasil dikirim ke Ketua Tim untuk direview.');
        }

        return redirect()->back()->with('error', 'Gagal mengirim KKA. Pastikan status KKA sudah selesai.');
    }

    /** KT menyetujui KKA */
    public function approveKka(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka) return redirect()->back()->with('error', 'KKA tidak ditemukan.');

        if (!isAuditAdmin() && !isKtInSpt($kka['spt_id']) && !isDalnisInSpt($kka['spt_id'])) {
            return redirect()->back()->with('error', 'Hanya Ketua Tim yang dapat menyetujui KKA.');
        }

        $catatan = $this->request->getPost('catatan_review') ?: null;
        if ($this->kkaModel->approveKka($kkaId, $catatan)) {
            logActivity('kka.approve', 'kka', "KKA disetujui KT kka_id={$kkaId}");
            return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'KKA disetujui.');
        }

        return redirect()->back()->with('error', 'Gagal menyetujui KKA. Pastikan KKA sudah disubmit.');
    }

    /** KT mengembalikan KKA ke AT */
    public function rejectKka(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka) return redirect()->back()->with('error', 'KKA tidak ditemukan.');

        if (!isAuditAdmin() && !isKtInSpt($kka['spt_id']) && !isDalnisInSpt($kka['spt_id'])) {
            return redirect()->back()->with('error', 'Hanya Ketua Tim yang dapat mengembalikan KKA.');
        }

        $catatan = trim($this->request->getPost('catatan_review') ?? '');
        if (empty($catatan)) {
            return redirect()->back()->with('error', 'Catatan wajib diisi saat mengembalikan KKA.');
        }

        if ($this->kkaModel->rejectKka($kkaId, $catatan)) {
            logActivity('kka.reject', 'kka', "KKA dikembalikan KT kka_id={$kkaId}");
            return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'KKA dikembalikan ke Anggota Tim.');
        }

        return redirect()->back()->with('error', 'Gagal mengembalikan KKA.');
    }

    /** AT membuka kembali KKA rejected agar bisa diperbaiki lalu submit ulang */
    public function reopenKka(int $kkaId)
    {
        $kka = $this->kkaModel->find($kkaId);
        if (!$kka) return redirect()->back()->with('error', 'KKA tidak ditemukan.');

        if (!$this->canEditKka($kka)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }

        if (($kka['status_kka'] ?? '') !== 'rejected') {
            return redirect()->back()->with('error', 'KKA tidak dalam status dikembalikan.');
        }

        if ($this->kkaModel->reopenKka($kkaId)) {
            logActivity('kka.reopen', 'kka', "KKA dibuka kembali oleh AT kka_id={$kkaId}");
            return redirect()->to('/admin/kka/' . $kkaId)->with('success', 'KKA dibuka kembali. Silakan perbaiki dan kirim ulang ke Ketua Tim.');
        }

        return redirect()->back()->with('error', 'Gagal membuka kembali KKA.');
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
