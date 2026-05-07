<?php
namespace App\Models;
use CodeIgniter\Model;

class KodeRekomendasiModel extends Model
{
    protected $table      = 'kode_rekomendasi';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode', 'uraian'];
    protected $useTimestamps = true;

    /**
     * Ambil semua sebagai array asosiatif kode => uraian
     */
    public function getMap(): array
    {
        $rows = $this->orderBy('kode')->findAll();
        $map  = [];
        foreach ($rows as $r) {
            $map[$r['kode']] = $r['uraian'];
        }
        return $map;
    }

    /**
     * Ambil rekomen berdasarkan array kode (dari field alternatif)
     *
     * @param string $alternatif  Comma-separated kode, contoh "R.01,R.03,R.07"
     * @return array
     */
    public function getByAlternatif(string $alternatif): array
    {
        if (trim($alternatif) === '') return [];

        $kodes = array_filter(array_map('trim', explode(',', $alternatif)));
        if (empty($kodes)) return [];

        return $this->whereIn('kode', $kodes)->orderBy('kode')->findAll();
    }
}
