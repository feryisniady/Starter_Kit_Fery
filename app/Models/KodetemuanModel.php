<?php
namespace App\Models;
use CodeIgniter\Model;

class KodetemuanModel extends Model
{
    protected $table      = 'kode_temuan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['kode','uraian','jenis'];
    protected $useTimestamps = true;

    public static array $jenisLabel = [
        1 => 'Kerugian / Potensi Kerugian Negara',
        2 => 'Kelemahan Sistem Pengendalian Intern (SPI)',
        3 => 'Ketidakhematan / Ketidakefisienan / Ketidakefektifan',
    ];

    public static array $jenisColor = [
        1 => 'danger',
        2 => 'warning',
        3 => 'info',
    ];

    /** Dropdown untuk select: ['id' => 'kode — uraian'] */
    public function getDropdown(): array
    {
        return $this->orderBy('kode')
            ->findAll();
    }

    /** Grouped by jenis untuk tampilan yang lebih terorganisir */
    public function getGrouped(): array
    {
        $all    = $this->orderBy('kode')->findAll();
        $groups = [];
        foreach ($all as $row) {
            $groups[$row['jenis']][] = $row;
        }
        return $groups;
    }
}
