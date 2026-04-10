<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/**
 * KM Checklist aggregator — tracks completion of all KM docs for a given SPT.
 */
class SptKmModel
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Labels for each KM document.
     */
    public static array $kmLabels = [
        'km1'             => 'KM1 — Kartu Penugasan',
        'km4'             => 'KM4 — Lembar Perencanaan Pengawasan',
        'km6'             => 'KM6 — Notulensi Kesepakatan',
        'anggaran_waktu'  => 'Formulir Anggaran Waktu',
        'independensi'    => 'Pernyataan Independensi & Integritas',
    ];

    /**
     * Returns checklist array with keys matching $kmLabels.
     * Each entry: ['label'=>..., 'complete'=>bool, 'detail'=>string]
     */
    public function getChecklist(int $sptId): array
    {
        $timIds = $this->db->table('spt_tim')
            ->select('sdm_id')
            ->where('spt_id', $sptId)
            ->get()->getResultArray();
        $timCount = count($timIds);

        // KM1
        $km1 = $this->db->table('spt_km1')->where('spt_id', $sptId)->countAllResults() > 0;

        // KM4: exists + all 11 checklist = 1
        $km4Row = $this->db->table('spt_km4')->where('spt_id', $sptId)->get()->getRowArray();
        $km4Complete = false;
        $km4Detail   = 'Belum diisi';
        if ($km4Row) {
            $cekFields = ['cek_kp','cek_jenis_tujuan','cek_misi_tujuan','cek_informasi',
                          'cek_lhp_terakhir','cek_lhp_ekstern','cek_perundangan',
                          'cek_kertas_kerja','cek_tao_fao','cek_program','cek_anggaran_waktu'];
            $done = array_sum(array_map(fn($f) => (int)$km4Row[$f], $cekFields));
            $km4Complete = $done === 11;
            $km4Detail   = $km4Complete ? 'Semua checklist terpenuhi' : "{$done}/11 checklist terpenuhi";
        }

        // KM6
        $km6 = $this->db->table('spt_km6')->where('spt_id', $sptId)->countAllResults() > 0;

        // Anggaran Waktu: satu record per anggota tim
        $awCount = $this->db->table('spt_anggaran_waktu')->where('spt_id', $sptId)->countAllResults();
        $awComplete = $timCount > 0 && $awCount >= $timCount;
        $awDetail   = $timCount > 0 ? "{$awCount}/{$timCount} anggota tim terisi" : 'Belum ada tim';

        // Independensi: satu record per anggota tim
        $indCount   = $this->db->table('spt_independensi')->where('spt_id', $sptId)->countAllResults();
        $indComplete = $timCount > 0 && $indCount >= $timCount;
        $indDetail   = $timCount > 0 ? "{$indCount}/{$timCount} anggota tim terisi" : 'Belum ada tim';

        return [
            'km1' => [
                'label'    => self::$kmLabels['km1'],
                'complete' => $km1,
                'detail'   => $km1 ? 'Sudah diisi' : 'Belum diisi',
            ],
            'km4' => [
                'label'    => self::$kmLabels['km4'],
                'complete' => $km4Complete,
                'detail'   => $km4Detail,
            ],
            'km6' => [
                'label'    => self::$kmLabels['km6'],
                'complete' => $km6,
                'detail'   => $km6 ? 'Sudah diisi' : 'Belum diisi',
            ],
            'anggaran_waktu' => [
                'label'    => self::$kmLabels['anggaran_waktu'],
                'complete' => $awComplete,
                'detail'   => $awDetail,
            ],
            'independensi' => [
                'label'    => self::$kmLabels['independensi'],
                'complete' => $indComplete,
                'detail'   => $indDetail,
            ],
        ];
    }

    public function isComplete(int $sptId): bool
    {
        foreach ($this->getChecklist($sptId) as $item) {
            if (!$item['complete']) return false;
        }
        return true;
    }

    public function getMissingLabels(int $sptId): array
    {
        return array_map(
            fn($item) => $item['label'],
            array_filter($this->getChecklist($sptId), fn($item) => !$item['complete'])
        );
    }
}
