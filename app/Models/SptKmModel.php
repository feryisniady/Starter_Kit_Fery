<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

class SptKmModel
{
    protected BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ──────────────────────────────────────────────────────────────
    // Label & config per item KM
    // required = wajib lengkap sebelum SPT dapat diajukan
    // info     = hanya informatif, tidak memblokir ajukan
    // link     = tidak punya form sendiri, redirect ke modul lain
    // ──────────────────────────────────────────────────────────────

    public static array $kmConfig = [
        'km1' => [
            'label'    => 'KM-1 — Peta Pengawasan',
            'icon'     => 'map',
            'required' => true,
            'link'     => false,
        ],
        'km2' => [
            'label'    => 'KM-2 — Anggaran Waktu',
            'icon'     => 'calendar-days',
            'required' => true,
            'link'     => false,
        ],
        'km3' => [
            'label'    => 'KM-3 — Dokumen SPT',
            'icon'     => 'file-contract',
            'required' => true,
            'link'     => false,
            'auto'     => true,   // prefill otomatis dari data SPT
        ],
        'km4' => [
            'label'    => 'KM-4 — PKA (Program Kerja Audit)',
            'icon'     => 'clipboard-list',
            'required' => true,
            'link'     => true,   // redirect ke modul PKA
        ],
        'km5' => [
            'label'    => 'KM-5 — Reviu PKA',
            'icon'     => 'magnifying-glass-chart',
            'required' => true,
            'link'     => false,
        ],
        'km5b' => [
            'label'    => 'KM-5b — Entry Meeting',
            'icon'     => 'handshake',
            'required' => true,
            'link'     => false,
        ],
        'independensi' => [
            'label'    => 'Independensi & Integritas',
            'icon'     => 'user-shield',
            'required' => true,
            'link'     => false,
        ],
        'km7' => [
            'label'    => 'KM-7 — Daftar Temuan',
            'icon'     => 'triangle-exclamation',
            'required' => false,
            'link'     => true,   // redirect ke modul Temuan
            'info'     => true,   // hanya informatif
        ],
        'km10' => [
            'label'    => 'KM-10 — Exit Meeting',
            'icon'     => 'handshake-angle',
            'required' => true,
            'link'     => false,
        ],
        'km11' => [
            'label'    => 'KM-11 — Reviu Laporan',
            'icon'     => 'file-circle-check',
            'required' => true,
            'link'     => false,
        ],
    ];

    // ──────────────────────────────────────────────────────────────
    // Checklist
    // ──────────────────────────────────────────────────────────────

    public function getChecklist(int $sptId): array
    {
        $timCount = $this->db->table('spt_tim')->where('spt_id', $sptId)->countAllResults();

        // KM-1: Peta Pengawasan
        $km1Done   = $this->db->table('spt_km1')->where('spt_id', $sptId)->countAllResults() > 0;

        // KM-2: Anggaran Waktu
        $awCount    = $this->db->table('spt_anggaran_waktu')->where('spt_id', $sptId)->countAllResults();
        $km2Done    = $timCount > 0 && $awCount >= $timCount;
        $km2Detail  = $timCount > 0 ? "{$awCount}/{$timCount} anggota terisi" : 'Belum ada tim';

        // KM-3: Dokumen SPT (auto — lengkap jika nomor + penandatangan terisi)
        $sptRow     = $this->db->table('spt')->where('id', $sptId)->get()->getRowArray();
        $km3Done    = !empty($sptRow['nomor_naskah']) && !empty($sptRow['tanggal_naskah']) && !empty($sptRow['penandatangan_id']);
        $km3Detail  = $km3Done
            ? 'Nomor naskah & penandatangan terisi'
            : 'Nomor naskah / penandatangan belum lengkap';

        // KM-4: PKA (count prosedur)
        $pkaCount   = $this->db->table('pka')->where('spt_id', $sptId)->countAllResults();
        $km4Done    = $pkaCount > 0;
        $km4Detail  = $km4Done ? "{$pkaCount} prosedur PKA tersedia" : 'Belum ada prosedur PKA';

        // KM-5: Reviu PKA
        $km5Done    = $this->db->table('spt_km5')->where('spt_id', $sptId)->countAllResults() > 0;
        $km5Row     = $this->db->table('spt_km5')->where('spt_id', $sptId)->get()->getRowArray();
        $km5Detail  = $km5Done
            ? ($km5Row['status'] === 'disetujui' ? 'PKA disetujui Dalnis' : 'PKA dikembalikan untuk revisi')
            : 'Belum diisi';

        // KM-5b: Entry Meeting (spt_km6)
        $km5bDone   = $this->db->table('spt_km6')->where('spt_id', $sptId)->countAllResults() > 0;

        // Independensi
        $indCount   = $this->db->table('spt_independensi')->where('spt_id', $sptId)->countAllResults();
        $indDone    = $timCount > 0 && $indCount >= $timCount;
        $indDetail  = $timCount > 0 ? "{$indCount}/{$timCount} anggota terisi" : 'Belum ada tim';

        // KM-7: Daftar Temuan (informatif)
        $temuanCount = $this->db->table('temuan')->where('spt_id', $sptId)->countAllResults();

        // KM-10: Exit Meeting
        $km10Done   = $this->db->table('spt_km10')->where('spt_id', $sptId)->countAllResults() > 0;

        // KM-11: Reviu Laporan
        $km11Done   = $this->db->table('spt_km11')->where('spt_id', $sptId)->countAllResults() > 0;
        $km11Row    = $this->db->table('spt_km11')->where('spt_id', $sptId)->get()->getRowArray();
        $km11Detail = $km11Done
            ? ($km11Row['status'] === 'layak' ? 'Laporan dinyatakan layak' : 'Laporan perlu revisi')
            : 'Belum diisi';

        return [
            'km1' => array_merge(self::$kmConfig['km1'], [
                'complete' => $km1Done,
                'detail'   => $km1Done ? 'Sudah diisi' : 'Belum diisi',
            ]),
            'km2' => array_merge(self::$kmConfig['km2'], [
                'complete' => $km2Done,
                'detail'   => $km2Detail,
            ]),
            'km3' => array_merge(self::$kmConfig['km3'], [
                'complete' => $km3Done,
                'detail'   => $km3Detail,
            ]),
            'km4' => array_merge(self::$kmConfig['km4'], [
                'complete' => $km4Done,
                'detail'   => $km4Detail,
            ]),
            'km5' => array_merge(self::$kmConfig['km5'], [
                'complete' => $km5Done,
                'detail'   => $km5Detail,
            ]),
            'km5b' => array_merge(self::$kmConfig['km5b'], [
                'complete' => $km5bDone,
                'detail'   => $km5bDone ? 'Sudah diisi' : 'Belum diisi',
            ]),
            'independensi' => array_merge(self::$kmConfig['independensi'], [
                'complete' => $indDone,
                'detail'   => $indDetail,
            ]),
            'km7' => array_merge(self::$kmConfig['km7'], [
                'complete' => true,
                'detail'   => "{$temuanCount} temuan tercatat",
            ]),
            'km10' => array_merge(self::$kmConfig['km10'], [
                'complete' => $km10Done,
                'detail'   => $km10Done ? 'Sudah diisi' : 'Belum diisi',
            ]),
            'km11' => array_merge(self::$kmConfig['km11'], [
                'complete' => $km11Done,
                'detail'   => $km11Detail,
            ]),
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────

    public function isComplete(int $sptId): bool
    {
        foreach ($this->getChecklist($sptId) as $item) {
            if (($item['required'] ?? true) && !$item['complete']) return false;
        }
        return true;
    }

    public function getMissingLabels(int $sptId): array
    {
        return array_map(
            fn($item) => $item['label'],
            array_filter(
                $this->getChecklist($sptId),
                fn($item) => ($item['required'] ?? true) && !$item['complete']
            )
        );
    }
}
