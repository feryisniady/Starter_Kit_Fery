<?php
namespace App\Models;

use CodeIgniter\Model;

class NhpItemDokumenModel extends Model
{
    protected $table      = 'nhp_item_dokumen';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nhp_item_id', 'nama_file', 'path_file', 'ukuran', 'keterangan', 'uploaded_by', 'uploaded_at'];
    protected $useTimestamps = false;

    public function getByItem(int $nhpItemId): array
    {
        return $this->where('nhp_item_id', $nhpItemId)->orderBy('uploaded_at')->findAll();
    }

    public function getByNhp(int $nhpId): array
    {
        return $this->db->table('nhp_item_dokumen d')
            ->select('d.*, ni.nhp_id')
            ->join('nhp_item ni', 'ni.id = d.nhp_item_id')
            ->where('ni.nhp_id', $nhpId)
            ->orderBy('d.nhp_item_id')->orderBy('d.uploaded_at')
            ->get()->getResultArray();
    }
}
