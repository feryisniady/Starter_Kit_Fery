<?php

namespace App\Models;

use CodeIgniter\Model;

class PkptTimModel extends Model
{
    protected $table         = 'pkpt_tim';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['pkpt_kegiatan_id', 'sdm_id', 'peran', 'hp_total', 'anggaran', 'urutan'];
    protected $useTimestamps = false;

    public function saveTimKegiatan(int $kegiatanId, array $timData, int $tarifHp): void
    {
        // Hapus tim lama
        $this->where('pkpt_kegiatan_id', $kegiatanId)->delete();

        $peranUrutan = ['PJ' => 1, 'WPJ' => 2, 'Dalnis' => 3, 'KT' => 4, 'AT' => 5];

        foreach ($timData as $item) {
            $hp       = (int)($item['hp_total'] ?? 0);
            $this->insert([
                'pkpt_kegiatan_id' => $kegiatanId,
                'sdm_id'           => (int)$item['sdm_id'],
                'peran'            => $item['peran'],
                'hp_total'         => $hp,
                'anggaran'         => $hp * $tarifHp,
                'urutan'           => $peranUrutan[$item['peran']] ?? 9,
            ]);
        }
    }
}
