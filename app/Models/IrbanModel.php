<?php

namespace App\Models;

use CodeIgniter\Model;

class IrbanModel extends Model
{
    protected $table         = 'irban';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['kode', 'nama', 'kepala_sdm_id'];
    protected $useTimestamps = true;

    public function withKepala(): array
    {
        return $this->db->table('irban i')
            ->select('i.*, s.nama as kepala_nama')
            ->join('sdm s', 's.id = i.kepala_sdm_id', 'left')
            ->orderBy('i.kode')
            ->get()->getResultArray();
    }

    public function getDropdown(): array
    {
        return array_column($this->orderBy('kode')->findAll(), 'nama', 'id');
    }
}
