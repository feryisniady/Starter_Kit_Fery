<?php

namespace App\Models;

use CodeIgniter\Model;

class SptModel extends Model
{
    protected $table         = 'spt';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'pkpt_kegiatan_id', 'jenis_spt', 'irban_id', 'tahun', 'jenis_non_pkpt', 'entitas_id',
        'nama_tim', 'nomor_naskah', 'tanggal_naskah',
        'dasar_1', 'dasar_2', 'tujuan', 'tanggal_mulai', 'tanggal_selesai',
        'tembusan', 'penandatangan_id', 'status', 'file_word', 'catatan', 'created_by',
    ];
    protected $useTimestamps = true;

    public static array $statusLabel = [
        'draft'           => 'Draft',
        'diajukan'        => 'Diajukan',
        'ditolak'         => 'Ditolak',
        'acc_irban'       => 'ACC Irban',
        'acc_evlap'       => 'ACC Evlap',
        'acc_sekretaris'  => 'ACC Sekretaris',
        'terbit'          => 'Terbit',
    ];

    public static array $statusColor = [
        'draft'           => 'secondary',
        'diajukan'        => 'info',
        'ditolak'         => 'danger',
        'acc_irban'       => 'primary',
        'acc_evlap'       => 'warning',
        'acc_sekretaris'  => 'purple',
        'terbit'          => 'success',
    ];

    public static array $jenisNonPkpt = [
        'Reviu LKPD'                   => 'Reviu LKPD',
        'Reviu RKA'                    => 'Reviu RKA',
        'Evaluasi SPIP'                => 'Evaluasi SPIP',
        'Evaluasi SAKIP'               => 'Evaluasi SAKIP',
        'Monitoring TLHP'              => 'Monitoring TLHP',
        'Pemeriksaan Kasus/Khusus'     => 'Pemeriksaan Kasus/Khusus',
        'Asistensi/Pendampingan'       => 'Asistensi/Pendampingan',
        'Reviu Laporan Keuangan'       => 'Reviu Laporan Keuangan',
        'Penugasan Mandatori Lainnya'  => 'Penugasan Mandatori Lainnya',
    ];

    /** Statistik ringkasan untuk dashboard */
    public function getDashboardStats(int $tahun = 0): array
    {
        $tahun = $tahun ?: (int) date('Y');
        $row   = $this->db->query("
            SELECT
                COUNT(*)                                                   AS total,
                SUM(status = 'terbit'   AND tanggal_selesai >= CURDATE())  AS berjalan,
                SUM(status = 'terbit'   AND tanggal_selesai < CURDATE())   AS selesai,
                SUM(status IN ('diajukan','acc_irban','acc_evlap','acc_sekretaris')) AS pending_approval,
                SUM(status = 'ditolak')                                    AS ditolak,
                SUM(status = 'draft')                                      AS draft
            FROM spt
            WHERE YEAR(COALESCE(created_at, NOW())) = ?
        ", [$tahun])->getRowArray();

        return $row ?: [
            'total' => 0, 'berjalan' => 0, 'selesai' => 0,
            'pending_approval' => 0, 'ditolak' => 0, 'draft' => 0,
        ];
    }

    /** Ambil user_id dari pembuat SPT (created_by) */
    public function getCreatedBy(int $sptId): ?int
    {
        $row = $this->select('created_by')->find($sptId);
        return $row ? (int) $row['created_by'] : null;
    }

    public function getDetail(int $id): ?array
    {
        $spt = $this->db->table('spt s')
            ->select('s.*,
                      pk.kode_kegiatan, pk.tujuan_sasaran, pk.area_pengawasan, pk.jenis_pengawasan,
                      COALESCE(p.tahun,  s.tahun)    as tahun,
                      COALESCE(p.irban_id, s.irban_id) as irban_id,
                      COALESCE(i_pkpt.nama, i_spt.nama) as irban_nama,
                      sdm.nama as penandatangan_nama, sdm.jabatan_struktural as penandatangan_jabatan,
                      sdm.nip  as penandatangan_nip,  sdm.pangkat_golongan  as penandatangan_pangkat')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id', 'left')
            ->join('pkpt p',           'p.id = pk.pkpt_id',          'left')
            ->join('irban i_pkpt',     'i_pkpt.id = p.irban_id',     'left')
            ->join('irban i_spt',      'i_spt.id = s.irban_id',      'left')
            ->join('sdm',              'sdm.id = s.penandatangan_id', 'left')
            ->where('s.id', $id)
            ->get()->getRowArray();

        if (!$spt) return null;

        $spt['tim'] = $this->db->table('spt_tim st')
            ->select('st.*, s.nama as sdm_nama, s.jabatan_struktural, s.nip, s.pangkat_golongan')
            ->join('sdm s', 's.id = st.sdm_id')
            ->where('st.spt_id', $id)
            ->orderBy('st.urutan')
            ->get()->getResultArray();

        $spt['approvals'] = $this->db->table('spt_approval')
            ->select('spt_approval.*, u.name as approver_nama')
            ->join('users u', 'u.id = spt_approval.approved_by', 'left')
            ->where('spt_id', $id)
            ->orderBy('created_at')
            ->get()->getResultArray();

        return $spt;
    }

    public function getBySdm(int $sdmId, ?string $status = null): array
    {
        $q = $this->db->table('spt s')
            ->select('s.*,
                      COALESCE(pk.kode_kegiatan, s.jenis_non_pkpt, "Non-PKPT") as kode_kegiatan,
                      COALESCE(pk.area_pengawasan, s.tujuan) as area_pengawasan,
                      COALESCE(i_pkpt.nama, i_spt.nama) as irban_nama')
            ->join('spt_tim st',       'st.spt_id = s.id')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id', 'left')
            ->join('pkpt p',           'p.id = pk.pkpt_id',          'left')
            ->join('irban i_pkpt',     'i_pkpt.id = p.irban_id',     'left')
            ->join('irban i_spt',      'i_spt.id = s.irban_id',      'left')
            ->where('st.sdm_id', $sdmId);

        if ($status) $q->where('s.status', $status);

        return $q->orderBy('s.created_at', 'DESC')->get()->getResultArray();
    }

    public function getNextApprovalTahap(string $status): ?string
    {
        return match ($status) {
            'diajukan'       => 'irban',
            'acc_irban'      => 'evlap',
            'acc_evlap'      => 'sekretaris',
            'acc_sekretaris' => 'inspektur',
            default          => null,
        };
    }

    public function getNextStatus(string $status): ?string
    {
        return match ($status) {
            'diajukan'       => 'acc_irban',
            'acc_irban'      => 'acc_evlap',
            'acc_evlap'      => 'acc_sekretaris',
            'acc_sekretaris' => 'terbit',
            default          => null,
        };
    }

    // =========================================================
    // KUOTA SPT — Maks 3 SPT reguler aktif per Irban
    // =========================================================

    /**
     * Jenis Non-PKPT yang dikecualikan dari kuota (tidak dihitung).
     */
    private const PENGECUALIAN_NONPKPT = ['Pemeriksaan Kasus/Khusus'];
    private const SLOT_MAKS            = 3;

    /**
     * Query antrian: semua SPT reguler (bukan pengecualian) milik irban
     * yang BELUM punya LHP, diurutkan tgl_naskah ASC.
     */
    private function _queryAntrian(int $irbanId): string
    {
        $px = implode("','", self::PENGECUALIAN_NONPKPT);
        return "
            SELECT s.id, s.nomor_naskah, s.tanggal_naskah,
                   COALESCE(pk.kode_kegiatan, s.jenis_non_pkpt) AS label_kegiatan,
                   COALESCE(pk.jenis_pengawasan, s.jenis_non_pkpt) AS jenis_label
            FROM   spt s
            LEFT   JOIN pkpt_kegiatan pk ON pk.id = s.pkpt_kegiatan_id
            LEFT   JOIN pkpt p           ON p.id  = pk.pkpt_id
            LEFT   JOIN spt_lhp sl       ON sl.spt_id = s.id
            WHERE  sl.id IS NULL
              AND  COALESCE(p.irban_id, s.irban_id) = {$irbanId}
              AND  NOT (
                     (s.jenis_spt = 'non_pkpt' AND s.jenis_non_pkpt IN ('{$px}'))
                     OR
                     (s.jenis_spt = 'pkpt' AND (
                         LOWER(IFNULL(pk.jenis_pengawasan,'')) LIKE '%investigasi%'
                         OR LOWER(IFNULL(pk.jenis_pengawasan,'')) LIKE '%adtt%'
                     ))
                   )
            ORDER  BY s.tanggal_naskah ASC, s.id ASC
        ";
    }

    /**
     * Cek apakah Irban boleh mengajukan SPT baru.
     *
     * @return array{boleh: bool, pesan: string, slot_terpakai: int, slot_maks: int}
     */
    public function canAjukanSpt(int $irbanId): array
    {
        $list         = $this->db->query($this->_queryAntrian($irbanId))->getResultArray();
        $slotTerpakai = count($list);
        $slotMaks     = self::SLOT_MAKS;

        if ($slotTerpakai >= $slotMaks) {
            return [
                'boleh'         => false,
                'slot_terpakai' => $slotTerpakai,
                'slot_maks'     => $slotMaks,
                'pesan'         => "Masih ada <strong>{$slotTerpakai}</strong> SPT yang belum memiliki LHP. "
                                 . "Pengajuan SPT reguler dibatasi maksimal {$slotMaks}. "
                                 . "Selesaikan LHP terlebih dahulu sebelum mengajukan SPT baru.",
            ];
        }

        $sisa = $slotMaks - $slotTerpakai;
        return [
            'boleh'         => true,
            'slot_terpakai' => $slotTerpakai,
            'slot_maks'     => $slotMaks,
            'pesan'         => "Sisa slot: {$sisa} dari {$slotMaks}.",
        ];
    }

    /**
     * Validasi apakah SPT tertentu boleh diupload LHP-nya.
     * Aturan: harus urut — SPT tertua yang belum punya LHP harus diupload duluan.
     *
     * @return bool true = boleh upload
     */
    public function cekUrutanLhp(int $sptId, int $irbanId): bool
    {
        $list = $this->db->query($this->_queryAntrian($irbanId))->getResultArray();
        if (empty($list)) return true;
        return (int)$list[0]['id'] === $sptId;
    }

    /**
     * Info slot untuk view (progress bar + antrian banner).
     *
     * @return array{terpakai: int, maks: int, sisa: int, persen: int, list_antrian: array}
     */
    public function getInfoSlot(int $irbanId): array
    {
        $list     = $this->db->query($this->_queryAntrian($irbanId))->getResultArray();
        $terpakai = count($list);
        $maks     = self::SLOT_MAKS;

        return [
            'terpakai'     => $terpakai,
            'maks'         => $maks,
            'sisa'         => max(0, $maks - $terpakai),
            'persen'       => min(100, (int) round($terpakai / $maks * 100)),
            'list_antrian' => $list,
        ];
    }

    /**
     * Cek apakah SPT tertentu adalah jenis pengecualian (tidak masuk kuota).
     */
    public function isPengecualian(int $sptId): bool
    {
        $row = $this->db->query("
            SELECT s.jenis_spt, s.jenis_non_pkpt, IFNULL(pk.jenis_pengawasan,'') AS jenis_pengawasan
            FROM   spt s
            LEFT   JOIN pkpt_kegiatan pk ON pk.id = s.pkpt_kegiatan_id
            WHERE  s.id = ?
            LIMIT  1
        ", [$sptId])->getRowArray();

        if (!$row) return false;

        if ($row['jenis_spt'] === 'non_pkpt') {
            return in_array($row['jenis_non_pkpt'], self::PENGECUALIAN_NONPKPT);
        }

        $j = strtolower($row['jenis_pengawasan']);
        return str_contains($j, 'investigasi') || str_contains($j, 'adtt');
    }

}

