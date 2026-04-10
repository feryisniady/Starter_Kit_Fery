<?php

namespace App\Models;

use CodeIgniter\Model;

class PkptModel extends Model
{
    protected $table         = 'pkpt';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['tahun', 'irban_id', 'status', 'created_by', 'approved_by', 'approved_at'];
    protected $useTimestamps = true;

    public function getWithIrban(int $id): ?array
    {
        return $this->db->table('pkpt p')
            ->select('p.*, i.nama as irban_nama, i.kode as irban_kode')
            ->join('irban i', 'i.id = p.irban_id')
            ->where('p.id', $id)
            ->get()->getRowArray();
    }

    public function getByIrbanTahun(int $irbanId, int $tahun): ?array
    {
        return $this->where('irban_id', $irbanId)->where('tahun', $tahun)->first();
    }

    /**
     * Total HP terpakai semua SDM dalam 1 PKPT (tahun).
     */
    public function getTotalHpTerpakai(int $tahun): array
    {
        return $this->db->table('pkpt_tim pt')
            ->select('pt.sdm_id, SUM(pt.hp_total) as hp_terpakai')
            ->join('pkpt_kegiatan pk', 'pk.id = pt.pkpt_kegiatan_id')
            ->join('pkpt p', 'p.id = pk.pkpt_id')
            ->where('p.tahun', $tahun)
            ->where('pk.status !=', 'batal')
            ->groupBy('pt.sdm_id')
            ->get()->getResultArray();
    }
}
