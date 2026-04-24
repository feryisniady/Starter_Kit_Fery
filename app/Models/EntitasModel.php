<?php

namespace App\Models;

use CodeIgniter\Model;

class EntitasModel extends Model
{
    protected $table         = 'entitas';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['kode', 'nama', 'alamat', 'kepala', 'aktif', 'user_id'];
    protected $useTimestamps = true;

    public function getAktif(): array
    {
        return $this->where('aktif', 1)->orderBy('nama')->findAll();
    }

    public function getDropdown(): array
    {
        return array_column($this->where('aktif', 1)->orderBy('nama')->findAll(), 'nama', 'id');
    }
}
