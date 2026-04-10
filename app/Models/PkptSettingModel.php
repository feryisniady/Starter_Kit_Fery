<?php

namespace App\Models;

use CodeIgniter\Model;

class PkptSettingModel extends Model
{
    protected $table         = 'pkpt_setting';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tahun', 'total_hp_tahunan', 'tarif_hp', 'tanggal_pkpt', 'nomor_pkpt'];
    protected $useTimestamps = true;

    public function getByTahun(int $tahun): ?array
    {
        return $this->where('tahun', $tahun)->first();
    }

    public function getTahunAktif(): int
    {
        $row = $this->orderBy('tahun', 'DESC')->first();
        return $row ? (int)$row['tahun'] : (int)date('Y');
    }
}
