<?php

namespace App\Models;

use CodeIgniter\Model;

class SptModel extends Model
{
    protected $table         = 'spt';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'pkpt_kegiatan_id', 'nama_tim', 'nomor_naskah', 'tanggal_naskah',
        'dasar_1', 'dasar_2', 'tujuan', 'tanggal_mulai', 'tanggal_selesai',
        'tembusan', 'penandatangan_id', 'status', 'file_word', 'catatan', 'created_by',
    ];
    protected $useTimestamps = true;

    public static array $statusLabel = [
        'draft'           => 'Draft',
        'diajukan'        => 'Diajukan',
        'acc_irban'       => 'ACC Irban',
        'acc_evlap'       => 'ACC Evlap',
        'acc_sekretaris'  => 'ACC Sekretaris',
        'terbit'          => 'Terbit',
    ];

    public static array $statusColor = [
        'draft'           => 'secondary',
        'diajukan'        => 'info',
        'acc_irban'       => 'primary',
        'acc_evlap'       => 'warning',
        'acc_sekretaris'  => 'purple',
        'terbit'          => 'success',
    ];

    public function getDetail(int $id): ?array
    {
        $spt = $this->db->table('spt s')
            ->select('s.*, pk.kode_kegiatan, pk.tujuan_sasaran, pk.area_pengawasan, pk.jenis_pengawasan,
                      p.tahun, p.irban_id, i.nama as irban_nama,
                      sdm.nama as penandatangan_nama, sdm.jabatan_struktural as penandatangan_jabatan,
                      sdm.nip as penandatangan_nip, sdm.pangkat_golongan as penandatangan_pangkat')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id')
            ->join('pkpt p', 'p.id = pk.pkpt_id')
            ->join('irban i', 'i.id = p.irban_id')
            ->join('sdm', 'sdm.id = s.penandatangan_id', 'left')
            ->where('s.id', $id)
            ->get()->getRowArray();

        if (!$spt) return null;

        $spt['tim'] = $this->db->table('spt_tim st')
            ->select('st.*, s.nama as sdm_nama, s.jabatan_struktural, s.nip, s.pangkat_golongan')
            ->join('sdm s', 's.id = st.sdm_id')
            ->where('st.spt_id', $id)
            ->orderBy('st.urutan')
            ->get()->getResultArray();

        $spt['approvals'] = $this->db->table('spt_approval')
            ->select('spt_approval.*, u.name as approver_nama')
            ->join('users u', 'u.id = spt_approval.approved_by', 'left')
            ->where('spt_id', $id)
            ->orderBy('created_at')
            ->get()->getResultArray();

        return $spt;
    }

    public function getBySdm(int $sdmId, ?string $status = null): array
    {
        $q = $this->db->table('spt s')
            ->select('s.*, pk.kode_kegiatan, pk.area_pengawasan, i.nama as irban_nama')
            ->join('spt_tim st', 'st.spt_id = s.id')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id')
            ->join('pkpt p', 'p.id = pk.pkpt_id')
            ->join('irban i', 'i.id = p.irban_id')
            ->where('st.sdm_id', $sdmId);

        if ($status) $q->where('s.status', $status);

        return $q->orderBy('s.created_at', 'DESC')->get()->getResultArray();
    }

    /**
     * Tentukan tahap approval berikutnya berdasarkan status saat ini.
     */
    public function getNextApprovalTahap(string $status): ?string
    {
        return match ($status) {
            'diajukan'       => 'irban',
            'acc_irban'      => 'evlap',
            'acc_evlap'      => 'sekretaris',
            'acc_sekretaris' => 'inspektur',
            default          => null,
        };
    }

    public function getNextStatus(string $status): ?string
    {
        return match ($status) {
            'diajukan'       => 'acc_irban',
            'acc_irban'      => 'acc_evlap',
            'acc_evlap'      => 'acc_sekretaris',
            'acc_sekretaris' => 'terbit',
            default          => null,
        };
    }
}
