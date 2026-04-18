<?php

namespace App\Models;

use CodeIgniter\Model;

class SptTimModel extends Model
{
    protected $table         = 'spt_tim';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['spt_id', 'sdm_id', 'peran_spt', 'hp_desk', 'hp_field', 'from_pkpt', 'urutan'];
    protected $useTimestamps = false;

    /**
     * Map peran PKPT ke label peran SPT.
     */
    public static array $peranLabel = [
        'PJ'     => 'Penanggung Jawab',
        'WPJ'    => 'Wakil Penanggung Jawab',
        'Dalnis' => 'Pengendali Teknis',
        'KT'     => 'Ketua Tim',
        'AT'     => 'Anggota Tim',
    ];

    public function saveTimSpt(int $sptId, array $timData): void
    {
        $this->where('spt_id', $sptId)->delete();

        foreach ($timData as $urutan => $item) {
            $hpTotal = (int)($item['hp_desk'] ?? 1) + (int)($item['hp_field'] ?? 0);
            $this->insert([
                'spt_id'    => $sptId,
                'sdm_id'    => (int)$item['sdm_id'],
                'peran_spt' => $item['peran_spt'],
                'hp_desk'   => (int)($item['hp_desk'] ?? 1),
                'hp_field'  => (int)($item['hp_field'] ?? 0),
                'from_pkpt' => (int)($item['from_pkpt'] ?? 1),
                'urutan'    => (int)($item['urutan'] ?? $urutan),
            ]);
        }
    }

    /**
     * HP sudah dialokasikan per SDM dari semua SPT di kegiatan yang sama.
     * $excludeSptId: abaikan SPT ini (berguna saat mode edit).
     * Returns [sdm_id => total_hp_allocated]
     */
    public function getHpAllocatedByKegiatan(int $kegiatanId, int $excludeSptId = 0): array
    {
        $q = $this->db->table('spt_tim st')
            ->select('st.sdm_id, SUM(st.hp_desk + st.hp_field) as total_hp')
            ->join('spt s', 's.id = st.spt_id')
            ->where('s.pkpt_kegiatan_id', $kegiatanId)
            ->groupBy('st.sdm_id');
        if ($excludeSptId) $q->where('st.spt_id !=', $excludeSptId);
        return array_column($q->get()->getResultArray(), 'total_hp', 'sdm_id');
    }

    /**
     * Sama seperti buildFromPkptTim, tapi HP default = sisa (budget PKPT − sudah dialokasikan).
     * Masing-masing row dilengkapi hp_total_pkpt, hp_allocated, hp_sisa untuk info di view.
     */
    public function buildFromPkptTimWithSisa(int $pkptKegiatanId, array $hpAllocated): array
    {
        $pkptTim = $this->db->table('pkpt_tim pt')
            ->select('pt.sdm_id, pt.peran, pt.hp_total, pt.urutan')
            ->where('pt.pkpt_kegiatan_id', $pkptKegiatanId)
            ->orderBy('pt.urutan')
            ->get()->getResultArray();

        return array_map(function ($row) use ($hpAllocated) {
            $allocated = (int)($hpAllocated[$row['sdm_id']] ?? 0);
            $sisa      = max(0, (int)$row['hp_total'] - $allocated);
            $hpDesk    = min(1, $sisa);
            $hpField   = max(0, $sisa - $hpDesk);
            return [
                'sdm_id'         => $row['sdm_id'],
                'peran_spt'      => self::$peranLabel[$row['peran']] ?? $row['peran'],
                'hp_desk'        => $hpDesk,
                'hp_field'       => $hpField,
                'from_pkpt'      => 1,
                'urutan'         => $row['urutan'],
                'hp_total_pkpt'  => (int)$row['hp_total'],
                'hp_allocated'   => $allocated,
                'hp_sisa'        => $sisa,
            ];
        }, $pkptTim);
    }

    /**
     * Build tim SPT default dari pkpt_tim (hp_desk=1, hp_field=sisa).
     */
    public function buildFromPkptTim(int $pkptKegiatanId): array
    {
        $pkptTim = $this->db->table('pkpt_tim pt')
            ->select('pt.sdm_id, pt.peran, pt.hp_total, pt.urutan')
            ->where('pt.pkpt_kegiatan_id', $pkptKegiatanId)
            ->orderBy('pt.urutan')
            ->get()->getResultArray();

        return array_map(function ($row) {
            $hpDesk  = 1;
            $hpField = max(0, (int)$row['hp_total'] - $hpDesk);
            return [
                'sdm_id'    => $row['sdm_id'],
                'peran_spt' => self::$peranLabel[$row['peran']] ?? $row['peran'],
                'hp_desk'   => $hpDesk,
                'hp_field'  => $hpField,
                'from_pkpt' => 1,
                'urutan'    => $row['urutan'],
            ];
        }, $pkptTim);
    }
}
