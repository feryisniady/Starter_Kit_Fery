<?php
namespace App\Models;
use CodeIgniter\Model;

class PkaModel extends Model
{
    protected $table      = 'pka';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'spt_id','nomor_urut','uraian_prosedur',
        'pic_sdm_id','rencana_waktu','realisasi_waktu',
        'status','created_by',
    ];
    protected $useTimestamps = true;

    /** Semua prosedur PKA untuk 1 SPT, join nama SDM */
    public function getBySpt(int $sptId): array
    {
        return $this->db->table('pka p')
            ->select('p.*, s.nama as pic_nama')
            ->join('sdm s', 's.id = p.pic_sdm_id', 'left')
            ->where('p.spt_id', $sptId)
            ->orderBy('p.nomor_urut')
            ->get()->getResultArray();
    }

    /** Nomor urut berikutnya untuk SPT */
    public function nextNomor(int $sptId): int
    {
        $row = $this->where('spt_id', $sptId)->selectMax('nomor_urut')->first();
        return ($row['nomor_urut'] ?? 0) + 1;
    }

    /** Statistik PKA untuk SPT */
    public function getStatsBySpt(int $sptId): array
    {
        $all    = $this->where('spt_id', $sptId)->findAll();
        $selesai = count(array_filter($all, fn($r) => $r['status'] === 'selesai'));
        return [
            'total'   => count($all),
            'selesai' => $selesai,
            'belum'   => count($all) - $selesai,
        ];
    }
}
