<?php
namespace App\Models;

use CodeIgniter\Model;

class RoutingSlipModel extends Model
{
    protected $table         = 'spt_routing_slip';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'spt_id', 'judul', 'jenis_dokumen', 'keterangan',
        'pengirim_sdm_id', 'tanggal_kirim',
        'kt_sdm_id',     'kt_tanggal',     'kt_status',     'kt_catatan',
        'dalnis_sdm_id', 'dalnis_tanggal', 'dalnis_status', 'dalnis_catatan',
        'pj_sdm_id',     'pj_tanggal',     'pj_status',     'pj_catatan',
        'status',
    ];

    // ──────────────────────────────────────────────────────────────────
    // Label / konstanta
    // ──────────────────────────────────────────────────────────────────

    public const STATUS_LABEL = [
        'draft'          => 'Draft',
        'kt_review'      => 'Review KT',
        'dalnis_review'  => 'Review Dalnis',
        'pj_review'      => 'Review PJ',
        'selesai'        => 'Selesai',
        'dikembalikan'   => 'Dikembalikan',
    ];

    public const JENIS_LABEL = [
        'kka'       => 'KKA / Kertas Kerja',
        'program'   => 'Program Pengawasan',
        'laporan'   => 'Konsep Laporan',
        'nhp'       => 'NHP',
        'lainnya'   => 'Lainnya',
    ];

    public const JENIS_ICON = [
        'kka'       => 'fa-file-lines',
        'program'   => 'fa-list-check',
        'laporan'   => 'fa-file-alt',
        'nhp'       => 'fa-file-contract',
        'lainnya'   => 'fa-folder',
    ];

    // ──────────────────────────────────────────────────────────────────
    // Query helpers
    // ──────────────────────────────────────────────────────────────────

    /**
     * Ambil semua routing slip untuk satu SPT, beserta nama pengirim & reviewer.
     */
    public function getForSpt(int $sptId): array
    {
        return $this->db->table('spt_routing_slip rs')
            ->select('rs.*,
                sp.nama  as pengirim_nama,
                skt.nama as kt_nama,
                sdl.nama as dalnis_nama,
                spj.nama as pj_nama')
            ->join('sdm sp',  'sp.id  = rs.pengirim_sdm_id', 'left')
            ->join('sdm skt', 'skt.id = rs.kt_sdm_id',      'left')
            ->join('sdm sdl', 'sdl.id = rs.dalnis_sdm_id',  'left')
            ->join('sdm spj', 'spj.id = rs.pj_sdm_id',      'left')
            ->where('rs.spt_id', $sptId)
            ->orderBy('rs.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /**
     * Ambil satu routing slip dengan nama reviewer lengkap.
     */
    public function getDetail(int $id): ?array
    {
        $row = $this->db->table('spt_routing_slip rs')
            ->select('rs.*,
                sp.nama          as pengirim_nama,
                sp.jabatan_fungsional as pengirim_jabatan,
                skt.nama         as kt_nama,
                skt.nip          as kt_nip,
                sdl.nama         as dalnis_nama,
                sdl.nip          as dalnis_nip,
                spj.nama         as pj_nama,
                spj.nip          as pj_nip')
            ->join('sdm sp',  'sp.id  = rs.pengirim_sdm_id', 'left')
            ->join('sdm skt', 'skt.id = rs.kt_sdm_id',      'left')
            ->join('sdm sdl', 'sdl.id = rs.dalnis_sdm_id',  'left')
            ->join('sdm spj', 'spj.id = rs.pj_sdm_id',      'left')
            ->where('rs.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    // ──────────────────────────────────────────────────────────────────
    // Status helpers
    // ──────────────────────────────────────────────────────────────────

    /**
     * Stage reviewer yang sekarang giliran review.
     * Return: 'kt' | 'dalnis' | 'pj' | null (belum submit atau sudah selesai/dikembalikan)
     */
    public static function getCurrentStage(array $slip): ?string
    {
        return match ($slip['status']) {
            'kt_review'     => 'kt',
            'dalnis_review' => 'dalnis',
            'pj_review'     => 'pj',
            default         => null,
        };
    }

    /**
     * Apakah sdm_id tertentu boleh melakukan review di tahap saat ini?
     */
    public static function canReview(array $slip, int $sptId): bool
    {
        $stage = self::getCurrentStage($slip);
        if (!$stage) return false;

        return match ($stage) {
            'kt'     => isKtInSpt($sptId),
            'dalnis' => isDalnisInSpt($sptId),
            'pj'     => isPjInSpt($sptId),
            default  => false,
        };
    }

    /**
     * Apakah slip masih bisa diedit oleh pengirim?
     */
    public static function canEdit(array $slip): bool
    {
        return in_array($slip['status'], ['draft', 'dikembalikan']);
    }

    /**
     * Apakah slip sudah final (tidak perlu aksi lagi)?
     */
    public static function isFinal(array $slip): bool
    {
        return $slip['status'] === 'selesai';
    }

    /**
     * Progress 0–4 untuk visual pipeline.
     * 0=draft,1=kt_review,2=dalnis_review,3=pj_review,4=selesai
     */
    public static function getProgress(array $slip): int
    {
        return match ($slip['status']) {
            'draft'          => 0,
            'kt_review'      => 1,
            'dalnis_review'  => 2,
            'pj_review'      => 3,
            'selesai'        => 4,
            'dikembalikan'   => 0,   // akan ditangani khusus di view
            default          => 0,
        };
    }

    // ──────────────────────────────────────────────────────────────────
    // Aksi review
    // ──────────────────────────────────────────────────────────────────

    /**
     * Proses review: terima atau kembalikan.
     * $stage : 'kt' | 'dalnis' | 'pj'
     * $action: 'diterima' | 'dikembalikan'
     */
    public function processReview(int $id, string $stage, string $action, int $sdmId, string $catatan = ''): bool
    {
        $slip = $this->find($id);
        if (!$slip) return false;

        $now        = date('Y-m-d H:i:s');
        $updateData = [
            "{$stage}_sdm_id"  => $sdmId,
            "{$stage}_tanggal" => $now,
            "{$stage}_status"  => $action,
            "{$stage}_catatan" => $catatan,
        ];

        if ($action === 'dikembalikan') {
            $updateData['status'] = 'dikembalikan';
        } else {
            // Terima → naik ke tahap berikutnya
            $updateData['status'] = match ($stage) {
                'kt'     => 'dalnis_review',
                'dalnis' => 'pj_review',
                'pj'     => 'selesai',
                default  => $slip['status'],
            };
        }

        return $this->update($id, $updateData);
    }

    /**
     * Submit slip dari draft → kt_review.
     */
    public function submit(int $id, int $sdmId): bool
    {
        return $this->update($id, [
            'status'          => 'kt_review',
            'pengirim_sdm_id' => $sdmId,
            'tanggal_kirim'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Resubmit setelah dikembalikan → reset ke kt_review dan bersihkan stage sebelumnya.
     */
    public function resubmit(int $id): bool
    {
        $slip = $this->find($id);
        if (!$slip || $slip['status'] !== 'dikembalikan') return false;

        // Tentukan mulai dari stage mana (stage yang mengembalikan)
        $resetFrom = null;
        foreach (['kt', 'dalnis', 'pj'] as $stage) {
            if ($slip["{$stage}_status"] === 'dikembalikan') {
                $resetFrom = $stage;
                break;
            }
        }

        $clearData = ['status' => 'kt_review'];
        // Reset semua stage dari titik yang dikembalikan
        $stages = ['kt', 'dalnis', 'pj'];
        $idx    = array_search($resetFrom, $stages);
        foreach (array_slice($stages, $idx) as $s) {
            $clearData["{$s}_sdm_id"]  = null;
            $clearData["{$s}_tanggal"] = null;
            $clearData["{$s}_status"]  = 'pending';
            $clearData["{$s}_catatan"] = null;
        }

        return $this->update($id, $clearData);
    }

    // ──────────────────────────────────────────────────────────────────
    // Statistik untuk KM stepper
    // ──────────────────────────────────────────────────────────────────

    public function countForSpt(int $sptId): int
    {
        return $this->where('spt_id', $sptId)->countAllResults();
    }

    public function countSelesai(int $sptId): int
    {
        return $this->where('spt_id', $sptId)->where('status', 'selesai')->countAllResults();
    }
}
