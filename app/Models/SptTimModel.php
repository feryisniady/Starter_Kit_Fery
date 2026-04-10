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
