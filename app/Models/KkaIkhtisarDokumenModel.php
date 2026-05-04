<?php

namespace App\Models;

use CodeIgniter\Model;

class KkaIkhtisarDokumenModel extends Model
{
    protected $table         = 'kka_ikhtisar_dokumen';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'kka_ikhtisar_id', 'nama_file', 'path_file',
        'ukuran', 'keterangan', 'uploaded_by', 'uploaded_at',
    ];
    protected $useTimestamps = false;

    /** Semua dokumen untuk satu baris kka_ikhtisar */
    public function getByIkhtisar(int $ikhtisarId): array
    {
        return $this->where('kka_ikhtisar_id', $ikhtisarId)
                    ->orderBy('uploaded_at', 'ASC')
                    ->findAll();
    }

    /** Semua dokumen untuk satu KKA (join ke kka_ikhtisar) */
    public function getByKka(int $kkaId): array
    {
        return $this->db->table('kka_ikhtisar_dokumen d')
            ->select('d.*, ki.pka_id, ki.kka_id')
            ->join('kka_ikhtisar ki', 'ki.id = d.kka_ikhtisar_id')
            ->where('ki.kka_id', $kkaId)
            ->orderBy('d.kka_ikhtisar_id')->orderBy('d.uploaded_at')
            ->get()->getResultArray();
    }

    /** Hapus file fisik + record */
    public function hapus(int $id): bool
    {
        $row = $this->find($id);
        if (!$row) return false;

        $path = WRITEPATH . 'uploads/kka-dokumen/' . $row['path_file'];
        if (is_file($path)) @unlink($path);

        return (bool) $this->delete($id);
    }
}
