<?php

namespace App\Models;

use CodeIgniter\Model;

class SptApprovalModel extends Model
{
    protected $table         = 'spt_approval';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['spt_id', 'tahap', 'status', 'approved_by', 'approved_at', 'catatan'];
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    public function getBySpt(int $sptId): array
    {
        return $this->db->table('spt_approval sa')
            ->select('sa.*, u.name as approver_nama')
            ->join('users u', 'u.id = sa.approved_by', 'left')
            ->where('sa.spt_id', $sptId)
            ->orderBy('sa.created_at')
            ->get()->getResultArray();
    }

    public function initApprovals(int $sptId): void
    {
        $tahapList = ['irban', 'evlap', 'sekretaris', 'inspektur'];
        foreach ($tahapList as $tahap) {
            $this->insert([
                'spt_id'     => $sptId,
                'tahap'      => $tahap,
                'status'     => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function approve(int $sptId, string $tahap, int $userId, ?string $catatan = null): void
    {
        $this->where('spt_id', $sptId)
             ->where('tahap', $tahap)
             ->set([
                 'status'      => 'approved',
                 'approved_by' => $userId,
                 'approved_at' => date('Y-m-d H:i:s'),
                 'catatan'     => $catatan,
             ])->update();
    }

    public function reject(int $sptId, string $tahap, int $userId, string $catatan): void
    {
        $this->where('spt_id', $sptId)
             ->where('tahap', $tahap)
             ->set([
                 'status'      => 'rejected',
                 'approved_by' => $userId,
                 'approved_at' => date('Y-m-d H:i:s'),
                 'catatan'     => $catatan,
             ])->update();
    }
}
