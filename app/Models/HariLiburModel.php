<?php

namespace App\Models;

use CodeIgniter\Model;
use DateTime;
use DateInterval;
use DatePeriod;

class HariLiburModel extends Model
{
    protected $table      = 'hari_libur';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tahun', 'tanggal', 'keterangan'];
    protected $useTimestamps = true;

    /** Semua hari libur untuk 1 tahun, urut tanggal */
    public function getByTahun(int $tahun): array
    {
        return $this->where('tahun', $tahun)->orderBy('tanggal')->findAll();
    }

    /**
     * Hitung total hari kerja efektif dalam setahun.
     * Hari kerja = Senin–Jumat di luar hari libur nasional/cuti bersama.
     */
    public function hitungHariKerjaTahun(int $tahun): int
    {
        // Hitung semua hari Senin–Jumat dalam tahun
        $start    = new DateTime("$tahun-01-01");
        $end      = new DateTime("$tahun-12-31");
        $interval = new DateInterval('P1D');
        $range    = new DatePeriod($start, $interval, (clone $end)->modify('+1 day'));

        $hariKerja = 0;
        foreach ($range as $date) {
            if ((int)$date->format('N') <= 5) {
                $hariKerja++;
            }
        }

        // Kurangi hari libur yang jatuh pada hari kerja
        $libur = $this->getByTahun($tahun);
        foreach ($libur as $l) {
            $tgl = new DateTime($l['tanggal']);
            if ((int)$tgl->format('N') <= 5) {
                $hariKerja--;
            }
        }

        return max(0, $hariKerja);
    }

    /** Ringkasan: total, libur nasional, hari kerja */
    public function getRingkasanTahun(int $tahun): array
    {
        $totalLibur = count($this->getByTahun($tahun));
        $hariKerja  = $this->hitungHariKerjaTahun($tahun);

        // Total hari Senin–Jumat
        $start    = new DateTime("$tahun-01-01");
        $end      = new DateTime("$tahun-12-31");
        $interval = new DateInterval('P1D');
        $range    = new DatePeriod($start, $interval, (clone $end)->modify('+1 day'));
        $senin2jumat = 0;
        foreach ($range as $date) {
            if ((int)$date->format('N') <= 5) $senin2jumat++;
        }

        return [
            'tahun'         => $tahun,
            'total_hari'    => (int)(new DateTime("$tahun-12-31"))->format('z') + 1,
            'senin_jumat'   => $senin2jumat,
            'libur_nasional'=> $totalLibur,
            'hari_kerja'    => $hariKerja,
        ];
    }
}
