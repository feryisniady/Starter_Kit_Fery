<?php
namespace App\Models;

use CodeIgniter\Model;

class TindakLanjutDokumenModel extends Model
{
    protected $table      = 'tindak_lanjut_dokumen';
    protected $primaryKey = 'id';
    protected $useTimestamps = false;
    protected $allowedFields = ['tindak_lanjut_id', 'nama_file', 'path_file', 'ukuran', 'keterangan', 'uploaded_at'];

    public function getByTl(int $tlId): array
    {
        return $this->where('tindak_lanjut_id', $tlId)->orderBy('uploaded_at')->findAll();
    }
}
