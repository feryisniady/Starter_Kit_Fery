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
    // phase    = 1:Persiapan (syarat SPT terbit), 2:Pelaksanaan, 3:Pelaporan
    // required = wajib lengkap dalam fase-nya
    // info     = hanya informatif, tidak memblokir
    // link     = redirect ke modul lain
    // ──────────────────────────────────────────────────────────────

    public static array $kmConfig = [
        'km1' => [
            'label'    => 'KM-1 — Peta Pengawasan',
            'icon'     => 'map',
            'phase'    => 1,
            'required' => true,
            'link'     => false,
        ],
        'km2' => [
            'label'    => 'KM-2 — Anggaran Waktu',
            'icon'     => 'calendar-days',
            'phase'    => 1,
            'required' => true,
            'link'     => false,
        ],
        'km3' => [
            'label'    => 'KM-3 — Dokumen SPT',
            'icon'     => 'file-contract',
            'phase'    => 1,
            'required' => true,
            'link'     => false,
            'auto'     => true,
        ],
        'km4' => [
            'label'    => 'KM-4 — PKA (Program Kerja Audit)',
            'icon'     => 'clipboard-list',
            'phase'    => 1,
            'required' => true,
            'link'     => true,
        ],
        'km5' => [
            'label'    => 'KM-5 — Reviu PKA',
            'icon'     => 'magnifying-glass-chart',
            'phase'    => 1,
            'required' => true,
            'link'     => false,
        ],
        'km5b' => [
            'label'    => 'KM-5b — Entry Meeting',
            'icon'     => 'handshake',
            'phase'    => 1,
            'required' => true,
            'link'     => false,
        ],
        'independensi' => [
            'label'    => 'Independensi & Integritas',
            'icon'     => 'user-shield',
            'phase'    => 1,
            'required' => true,
            'link'     => false,
        ],
        'km7' => [
            'label'    => 'KM-7 — KKA (Kertas Kerja Audit)',
            'icon'     => 'file-pen',
            'phase'    => 2,
            'required' => false,
            'link'     => true,
            'info'     => true,
        ],
        'km9' => [
            'label'    => 'KM-9 — NHP (Notisi Hasil Pemeriksaan)',
            'icon'     => 'paper-plane',
            'phase'    => 2,
            'required' => false,
            'link'     => true,
        ],
        'km10' => [
            'label'    => 'KM-10 — Exit Meeting',
            'icon'     => 'handshake-angle',
            'phase'    => 3,
            'required' => true,
            'link'     => false,
        ],
        'km11' => [
            'label'    => 'KM-11 — Reviu Laporan',
            'icon'     => 'file-circle-check',
            'phase'    => 3,
            'required' => true,
            'link'     => false,
        ],
        'routing_slip' => [
            'label'    => 'Routing Slip — Review Dokumen',
            'icon'     => 'route',
            'phase'    => 2,
            'required' => false,   // informatif — tidak memblokir alur
            'link'     => true,
            'info'     => true,
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

        // KM-7: KKA (informatif)
        $kkaCount = $this->db->table('kka')->where('spt_id', $sptId)->countAllResults();

        // KM-9: NHP
        $nhpTotal    = $this->db->table('nhp')->where('spt_id', $sptId)->countAllResults();
        $nhpTerkirim = $this->db->table('nhp')->where('spt_id', $sptId)->whereIn('status', ['terkirim','ditanggapi','selesai'])->countAllResults();
        $nhpSelesai  = $this->db->table('nhp')->where('spt_id', $sptId)->where('status', 'selesai')->countAllResults();
        $km9Done     = $nhpTotal > 0;

        // KM-10: Exit Meeting
        $km10Done   = $this->db->table('spt_km10')->where('spt_id', $sptId)->countAllResults() > 0;

        // Routing Slip
        $rsTotal   = $this->db->table('spt_routing_slip')->where('spt_id', $sptId)->countAllResults();
        $rsSelesai = $this->db->table('spt_routing_slip')->where('spt_id', $sptId)->where('status', 'selesai')->countAllResults();
        $rsPending = $this->db->table('spt_routing_slip')->where('spt_id', $sptId)
                        ->whereNotIn('status', ['selesai','dikembalikan','draft'])->countAllResults();

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
                'detail'   => "{$kkaCount} KKA aktif",
            ]),
            'km9' => array_merge(self::$kmConfig['km9'], [
                'complete' => $km9Done,
                'detail'   => $km9Done
                    ? "{$nhpTotal} NHP ({$nhpTerkirim} terkirim, {$nhpSelesai} selesai)"
                    : 'Belum ada NHP',
            ]),
            'km10' => array_merge(self::$kmConfig['km10'], [
                'complete' => $km10Done,
                'detail'   => $km10Done ? 'Sudah diisi' : 'Belum diisi',
            ]),
            'km11' => array_merge(self::$kmConfig['km11'], [
                'complete' => $km11Done,
                'detail'   => $km11Detail,
            ]),
            'routing_slip' => array_merge(self::$kmConfig['routing_slip'], [
                'complete' => $rsTotal > 0,
                'detail'   => $rsTotal > 0
                    ? "{$rsTotal} slip ({$rsSelesai} selesai" . ($rsPending > 0 ? ", {$rsPending} menunggu review" : '') . ')'
                    : 'Belum ada routing slip',
                'url'      => "/admin/spt/{$sptId}/routing-slip",
            ]),
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────

    /** Cek semua item required (semua fase) — untuk keperluan internal */
    public function isComplete(int $sptId): bool
    {
        foreach ($this->getChecklist($sptId) as $item) {
            if (($item['required'] ?? true) && !$item['complete']) return false;
        }
        return true;
    }

    /** Hanya Fase 1 — syarat SPT dapat diajukan */
    public function isPhase1Complete(int $sptId): bool
    {
        foreach ($this->getChecklist($sptId) as $item) {
            if (($item['phase'] ?? 1) !== 1) continue;
            if (($item['required'] ?? true) && !$item['complete']) return false;
        }
        return true;
    }

    /** Item Fase 1 yang belum selesai */
    public function getMissingPhase1Labels(int $sptId): array
    {
        return array_map(
            fn($item) => $item['label'],
            array_filter(
                $this->getChecklist($sptId),
                fn($item) => ($item['phase'] ?? 1) === 1
                          && ($item['required'] ?? true)
                          && !$item['complete']
            )
        );
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
