<?php

namespace App\Models;

use CodeIgniter\Model;

class KkaProsedurDokumenModel extends Model
{
    protected $table         = 'kka_prosedur_dokumen';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['kka_ikhtisar_id', 'nama_file', 'path_file', 'ukuran', 'keterangan', 'uploaded_by'];
    protected $useTimestamps = true;

    public function getByIkhtisar(int $ikhId): array
    {
        return $this->where('kka_ikhtisar_id', $ikhId)->orderBy('id')->findAll();
    }

    public function getByKkaIkhIds(array $ikhIds): array
    {
        if (empty($ikhIds)) return [];
        return $this->whereIn('kka_ikhtisar_id', $ikhIds)->orderBy('id')->findAll();
    }
}
