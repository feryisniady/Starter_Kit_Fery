<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\KkaModel;
use App\Models\NhpModel;
use App\Models\NhpItemDokumenModel;
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
    protected KkaModel $kkaModel;
    protected NhpItemDokumenModel $dokModel;

    public function __construct()
    {
        $this->nhpModel  = new NhpModel();
        $this->sptModel  = new SptModel();
        $this->kkaModel  = new KkaModel();
        $this->dokModel  = new NhpItemDokumenModel();
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

        // Gate: SPT harus berstatus Terbit
        if ($spt['status'] !== 'terbit') {
            return redirect()->to('/admin/spt/' . $sptId)
                ->with('error', 'NHP hanya dapat dibuat untuk SPT yang sudah berstatus Terbit.');
        }

        // Gate: semua KKA harus sudah disetujui KT sebelum NHP bisa dibuat
        if (!$this->kkaModel->allApprovedBySpt($sptId)) {
            return redirect()->to('/admin/spt/' . $sptId . '/kka')
                ->with('error', 'Semua KKA tim harus disetujui Ketua Tim sebelum NHP dapat dibuat.');
        }

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

        // Gate: SPT harus berstatus Terbit
        if ($spt['status'] !== 'terbit') {
            return redirect()->to('/admin/spt/' . $sptId)
                ->with('error', 'NHP hanya dapat dibuat untuk SPT yang sudah berstatus Terbit.');
        }

        // Gate: semua KKA harus sudah disetujui KT
        if (!$this->kkaModel->allApprovedBySpt($sptId)) {
            return redirect()->to('/admin/spt/' . $sptId . '/kka')
                ->with('error', 'Semua KKA tim harus disetujui Ketua Tim sebelum NHP dapat dibuat.');
        }

        // Validasi input wajib
        $post = $this->request->getPost();
        $perihal    = trim($post['perihal'] ?? '');
        $tanggalNhp = trim($post['tanggal_nhp'] ?? '');
        $selected   = array_filter((array)($post['simpulan_ids'] ?? []));

        $errors = [];
        if (empty($perihal)) {
            $errors[] = 'Perihal NHP wajib diisi.';
        }
        if (empty($tanggalNhp) || !strtotime($tanggalNhp)) {
            $errors[] = 'Tanggal NHP wajib diisi dengan format tanggal yang valid.';
        }
        if (empty($selected)) {
            $errors[] = 'Pilih minimal 1 simpulan KKA untuk dimasukkan ke NHP.';
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $errors));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Buat NHP header
        $nhpId = $this->nhpModel->create($sptId, [
            'tanggal_nhp' => $tanggalNhp,
            'perihal'     => $perihal,
            'catatan'     => $post['catatan'] ?: null,
        ]);

        // Tambahkan item dari simpulan yang dipilih
        if (!empty($selected)) {
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

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()
                ->with('error', 'Gagal menyimpan NHP. Silakan coba lagi.');
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

        // Inject dokumen per item agar view bisa menampilkan bukti dukung entitas
        $allDok = $this->dokModel->getByNhp($nhpId);
        $dokByItem = [];
        foreach ($allDok as $d) {
            $dokByItem[$d['nhp_item_id']][] = $d;
        }
        foreach ($items as &$item) {
            $item['dokumen'] = $dokByItem[$item['id']] ?? [];
        }

        // Load entitas yang terkait dengan kegiatan PKPT ini
        $db          = \Config\Database::connect();
        $entitasList = [];
        $pkptKegId   = $spt['pkpt_kegiatan_id'] ?? null;
        if ($pkptKegId) {
            $entitasList = $db->table('pkpt_entitas pe')
                ->select('e.id, e.nama, e.kode, e.kepala')
                ->join('entitas e', 'e.id = pe.entitas_id')
                ->where('pe.pkpt_kegiatan_id', $pkptKegId)
                ->get()->getResultArray();
        }

        return view('admin/nhp/show', [
            'title'          => 'Detail NHP — ' . ($nhp['nomor_nhp'] ?: '#' . $nhpId),
            'spt'            => $spt,
            'nhp'            => $nhp,
            'items'          => $items,
            'entitasList'    => $entitasList,
            'canManage'      => $this->canManage($sptId),
            'statusLabel'    => NhpModel::$statusLabel,
            'statusColor'    => NhpModel::$statusColor,
            'tanggapanLabel' => NhpModel::$tanggapanLabel,
            'tanggapanColor' => NhpModel::$tanggapanColor,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Approval Workflow: Ajukan → (Dalnis/Admin) Setujui / Kembalikan
    // ──────────────────────────────────────────────────────────────────────

    /** KT atau Dalnis mengajukan NHP untuk direview Dalnis/Admin sebelum dikirim ke entitas */
    public function ajukan(int $sptId, int $nhpId)
    {
        $nhp = $this->nhpModel->find($nhpId);
        if (!$nhp || (int)$nhp['spt_id'] !== $sptId) {
            return redirect()->back()->with('error', 'NHP tidak ditemukan.');
        }
        if (!$this->canManage($sptId)) {
            return redirect()->back()->with('error', 'Akses ditolak.');
        }
        if ($nhp['status'] !== 'draft') {
            return redirect()->back()->with('error', 'Hanya NHP berstatus Draft yang dapat diajukan.');
        }

        $items = $this->nhpModel->getItems($nhpId);
        if (empty($items)) {
            return redirect()->back()->with('error', 'Tambahkan minimal 1 item sebelum mengajukan NHP.');
        }

        $this->nhpModel->ajukan($nhpId);
        logActivity('nhp.ajukan', 'nhp', "NHP diajukan untuk review id={$nhpId}");

        // Notifikasi ke Dalnis di SPT ini (in-app + WA)
        $dalnisRows = \Config\Database::connect()
            ->table('spt_tim st')
            ->select('s.id as sdm_id, s.user_id, u.id as uid, u.phone')
            ->join('sdm s', 's.id = st.sdm_id')
            ->join('users u', 'u.id = s.user_id', 'left')
            ->where('st.spt_id', $sptId)
            ->where('st.peran_spt', 'Pengendali Teknis')
            ->get()->getResultArray();

        if (!empty($dalnisRows)) {
            $nomorNhp = $nhp['nomor_nhp'] ?? ('NHP #' . $nhpId);
            $perihal  = $nhp['perihal'] ?? $nomorNhp;
            $userIds  = array_filter(array_column($dalnisRows, 'uid'));
            if ($userIds) {
                notify(
                    array_values($userIds),
                    'NHP Menunggu Persetujuan: ' . $nomorNhp,
                    'NHP "' . $perihal . '" telah diajukan dan menunggu persetujuan Anda sebelum dikirim ke entitas.',
                    '/admin/spt/' . $sptId . '/nhp/' . $nhpId,
                    'info'
                );
            }
            $waMsg = "*[SIMPAWAN] NHP Menunggu Persetujuan 📋*\n\n"
                   . "NHP *{$nomorNhp}* ({$perihal}) telah diajukan oleh Ketua Tim dan menunggu persetujuan Anda sebelum dikirim ke entitas.\n\n"
                   . "Silakan login ke SIMPAWAN untuk meninjau.";
            foreach ($dalnisRows as $d) {
                if (!empty($d['phone'])) send_wa($d['phone'], $waMsg);
            }
        }

        return redirect()->back()->with('success', 'NHP berhasil diajukan. Menunggu persetujuan Dalnis/PJ.');
    }

    /** Dalnis/Admin mengembalikan NHP ke KT dengan catatan */
    public function kembalikan(int $sptId, int $nhpId)
    {
        $nhp = $this->nhpModel->find($nhpId);
        if (!$nhp || (int)$nhp['spt_id'] !== $sptId) {
            return redirect()->back()->with('error', 'NHP tidak ditemukan.');
        }
        if (!$this->canApprove($sptId)) {
            return redirect()->back()->with('error', 'Hanya Pengendali Teknis atau Admin yang dapat mengembalikan NHP.');
        }

        $catatan = trim($this->request->getPost('catatan_kembalikan') ?? '');
        if ($catatan) {
            $this->nhpModel->update($nhpId, ['catatan' => $catatan, 'updated_at' => date('Y-m-d H:i:s')]);
        }

        $this->nhpModel->kembalikan($nhpId);
        logActivity('nhp.kembalikan', 'nhp', "NHP dikembalikan id={$nhpId}");
        return redirect()->back()->with('error', 'NHP dikembalikan ke Draft.' . ($catatan ? ' Catatan: ' . $catatan : ''));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Kirim NHP ke Entitas (Dalnis/Admin: langsung; atau setelah disetujui)
    // ──────────────────────────────────────────────────────────────────────

    public function kirim(int $sptId, int $nhpId)
    {
        $nhp = $this->nhpModel->find($nhpId);
        if (!$nhp || (int)$nhp['spt_id'] !== $sptId) {
            return redirect()->back()->with('error', 'NHP tidak ditemukan.');
        }

        // Kirim langsung (draft→terkirim): hanya Dalnis/Admin
        // Setujui & kirim (diajukan→terkirim): hanya Dalnis/Admin
        if (!$this->canApprove($sptId)) {
            return redirect()->back()->with('error', 'Hanya Pengendali Teknis atau Admin yang dapat mengirim NHP ke entitas. Gunakan "Ajukan" jika Anda adalah Ketua Tim.');
        }

        if (!in_array($nhp['status'], ['draft', 'diajukan'])) {
            return redirect()->back()->with('error', 'NHP tidak dalam status yang dapat dikirim.');
        }

        $items = $this->nhpModel->getItems($nhpId);
        if (empty($items)) {
            return redirect()->back()->with('error', 'NHP harus memiliki minimal 1 item sebelum dikirim.');
        }

        if ($this->nhpModel->kirim($nhpId)) {
            logActivity('nhp.kirim', 'nhp', "NHP terkirim id={$nhpId}");

            $nhpData  = $this->nhpModel->find($nhpId);
            $nomorNhp = $nhpData['nomor_nhp'] ?? ('NHP #' . $nhpId);
            $perihal  = $nhpData['perihal'] ?? $nomorNhp;

            // In-app notification ke user portal entitas
            $entitasUserIds = $this->nhpModel->getEntitasUserIdsByNhp($nhpId);
            if (!empty($entitasUserIds)) {
                notify(
                    $entitasUserIds,
                    'NHP Diterima: ' . $nomorNhp,
                    'Anda menerima Notisi Hasil Pemeriksaan "' . $perihal . '". Silakan periksa dan berikan tanggapan.',
                    '/auditi/nhp/' . $nhpId,
                    'warning'
                );
            }

            // WA ke nomor HP user portal setiap entitas
            $db = \Config\Database::connect();
            $entitasUsers = $db->table('pkpt_entitas pe')
                ->select('u.phone')
                ->join('entitas e', 'e.id = pe.entitas_id')
                ->join('users u', 'u.id = e.user_id', 'left')
                ->join('spt sp', 'sp.pkpt_kegiatan_id = pe.pkpt_kegiatan_id')
                ->where('sp.id', $sptId)
                ->where('u.phone IS NOT NULL')
                ->where("u.phone != ''")
                ->get()->getResultArray();
            $waMsg = "*[SIMPAWAN] NHP Diterima 📬*\n\n"
                   . "Anda menerima Notisi Hasil Pemeriksaan:\n"
                   . "*{$nomorNhp}* — {$perihal}\n\n"
                   . "Silakan login ke portal SIMPAWAN untuk memeriksa dan memberikan tanggapan.";
            foreach ($entitasUsers as $eu) {
                send_wa($eu['phone'], $waMsg);
            }

            return redirect()->back()->with('success', 'NHP berhasil dikirim ke entitas.');
        }

        return redirect()->back()->with('error', 'Gagal mengirim NHP.');
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
        if ($nhp['status'] === 'selesai') {
            return redirect()->back()->with('error', 'NHP sudah diselesaikan. Tanggapan tidak dapat diubah.');
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

        $items   = $this->nhpModel->getItems($nhpId);
        $pending = array_filter($items, fn($i) => $i['status_tanggapan'] === 'pending');
        if (!empty($pending)) {
            return redirect()->back()->with('error', count($pending) . ' item masih menunggu tanggapan. Selesaikan semua tanggapan terlebih dahulu.');
        }

        if ($this->nhpModel->selesaikan($nhpId)) {
            // Ambil batas_waktu per item yang dikirim dari form modal
            $batasWaktuMap = $this->request->getPost('batas_waktu') ?? [];

            $jumlahTemuan = $this->autoCreateTemuan($sptId, $nhpId, $items, $batasWaktuMap);
            logActivity('nhp.selesai', 'nhp', "NHP selesai id={$nhpId}, auto-create {$jumlahTemuan} temuan");

            $msg = 'NHP telah diselesaikan.';
            if ($jumlahTemuan > 0) {
                $msg .= " {$jumlahTemuan} temuan tidak sesuai otomatis dibuat dan masuk Matriks Temuan.";
            }
            return redirect()->back()->with('success', $msg);
        }

        return redirect()->back()->with('error', 'Gagal menyelesaikan NHP.');
    }

    // ──────────────────────────────────────────────────────────────────────
    // Auto-create temuan + rekomendasi dari nhp_item tidak_sesuai
    // ──────────────────────────────────────────────────────────────────────

    private function autoCreateTemuan(int $sptId, int $nhpId, array $items, array $batasWaktuMap = []): int
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $tidakSesuai = array_values(array_filter($items, fn($i) => $i['status_tanggapan'] === 'tidak_sesuai'));
        if (empty($tidakSesuai)) return 0;

        // Nomor urut temuan dimulai dari jumlah temuan yang sudah ada di SPT ini
        $existing = $db->table('temuan')->where('spt_id', $sptId)->countAllResults();
        $created  = 0;

        foreach ($tidakSesuai as $item) {
            // Idempotent: skip jika temuan dari nhp_item ini sudah ada
            $sudahAda = $db->table('temuan')
                ->where('nhp_item_id', $item['id'])
                ->countAllResults();
            if ($sudahAda) continue;

            $existing++;
            $nomorTemuan = 'TM-' . date('Y') . '-' . str_pad($existing, 3, '0', STR_PAD_LEFT);

            // Ambil kode_temuan_id dari kka_simpulan sumber (jika ada)
            $kodeTemuanId = null;
            if (!empty($item['kka_simpulan_id'])) {
                $simp = $db->table('kka_simpulan')
                    ->select('kode_temuan_id')
                    ->where('id', $item['kka_simpulan_id'])
                    ->get()->getRowArray();
                $kodeTemuanId = $simp['kode_temuan_id'] ?? null;
            }

            $db->table('temuan')->insert([
                'nhp_item_id'    => $item['id'],
                'spt_id'         => $sptId,
                'kode_temuan_id' => $kodeTemuanId,
                'nomor_temuan'   => $nomorTemuan,
                'judul'          => $item['judul_temuan'] ?: 'Temuan ' . $existing,
                'kondisi'        => $item['kondisi'],
                'kriteria'       => $item['kriteria'],
                'sebab'          => $item['sebab'],
                'akibat'         => $item['akibat'],
                'nilai_temuan'   => (int)($item['nilai_temuan'] ?? 0),
                'status_temuan'  => 'buka',
                'created_by'     => session()->get('user_id'),
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            $temuanId = $db->insertID();

            // Batas waktu dari form modal (keyed by nhp_item.id)
            $batasWaktu = null;
            if (!empty($batasWaktuMap[$item['id']])) {
                $bw = $batasWaktuMap[$item['id']];
                // Validasi format tanggal
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $bw) && strtotime($bw) > time()) {
                    $batasWaktu = $bw;
                }
            }

            // Buat rekomendasi dari teks rekomendasi NHP item
            if (!empty($item['rekomendasi'])) {
                $db->table('rekomendasi')->insert([
                    'temuan_id'        => $temuanId,
                    'nomor_urut'       => 1,
                    'isi_rekomendasi'  => $item['rekomendasi'],
                    'batas_waktu'      => $batasWaktu,
                    'nilai_rekomendasi'=> (int)($item['nilai_temuan'] ?? 0),
                    'status'           => 'belum',
                    'created_by'       => session()->get('user_id'),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);

                // Pre-kalkulasi jadwal pengingat jika ada batas waktu
                if ($batasWaktu) {
                    $rekId = (int) $db->insertID();
                    buatJadwalReminder($rekId, $batasWaktu);
                }
            }

            $created++;
        }

        return $created;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Cetak NHP (print-ready HTML → browser PDF)
    // ──────────────────────────────────────────────────────────────────────

    public function printNhp(int $sptId, int $nhpId)
    {
        $spt = $this->sptModel->getDetail($sptId);
        if (!$spt) return redirect()->to('/admin/spt')->with('error', 'SPT tidak ditemukan.');
        if (!canViewSptAudit($sptId)) return redirect()->to('/admin/spt')->with('error', 'Akses ditolak.');

        $nhp = $this->nhpModel->find($nhpId);
        if (!$nhp || (int)$nhp['spt_id'] !== $sptId) {
            return redirect()->back()->with('error', 'NHP tidak ditemukan.');
        }

        $items = $this->nhpModel->getItems($nhpId);

        $db          = \Config\Database::connect();
        $entitasList = [];
        $pkptKegId   = $spt['pkpt_kegiatan_id'] ?? null;
        if ($pkptKegId) {
            $entitasList = $db->table('pkpt_entitas pe')
                ->select('e.id, e.nama, e.kode, e.kepala')
                ->join('entitas e', 'e.id = pe.entitas_id')
                ->where('pe.pkpt_kegiatan_id', $pkptKegId)
                ->get()->getResultArray();
        }

        $ktSdm = $dalnisSdm = null;
        foreach (($spt['tim'] ?? []) as $t) {
            if (!$ktSdm && $t['peran_spt'] === 'Ketua Tim') $ktSdm = $t;
            if (!$dalnisSdm && $t['peran_spt'] === 'Pengendali Teknis') $dalnisSdm = $t;
        }

        return view('admin/nhp/print_nhp', [
            'nhp'         => $nhp,
            'spt'         => $spt,
            'items'       => $items,
            'entitasList' => $entitasList,
            'ktSdm'       => $ktSdm,
            'dalnisSdm'   => $dalnisSdm,
        ]);
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
    // Download dokumen bukti dukung yang diupload entitas (auth-gated)
    // ──────────────────────────────────────────────────────────────────────

    public function downloadItemDokumen(int $dokId)
    {
        $dok = $this->dokModel->find($dokId);
        if (!$dok) return redirect()->back()->with('error', 'File tidak ditemukan.');

        $path = WRITEPATH . 'uploads/nhp-dokumen/' . $dok['path_file'];
        if (!file_exists($path)) return redirect()->back()->with('error', 'File tidak tersedia di server.');

        return $this->response
            ->setHeader('Content-Type', mime_content_type($path))
            ->setHeader('Content-Disposition', 'inline; filename="' . $dok['nama_file'] . '"')
            ->setBody(file_get_contents($path));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    private function canManage(int $sptId): bool
    {
        return isAuditAdmin() || isDalnisInSpt($sptId) || isKtInSpt($sptId);
    }

    /** Hanya Dalnis atau Admin yang boleh approve/kirim/kembalikan NHP */
    private function canApprove(int $sptId): bool
    {
        return isAuditAdmin() || isDalnisInSpt($sptId);
    }
}
