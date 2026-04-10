<?php

namespace App\Models;

use CodeIgniter\Model;

class PkptKegiatanModel extends Model
{
    protected $table         = 'pkpt_kegiatan';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'pkpt_id', 'kode_kegiatan', 'area_pengawasan', 'jenis_pengawasan',
        'tujuan_sasaran', 'ruang_lingkup', 'risiko_audit',
        'jadwal_rmp', 'jadwal_rpl', 'tanggal_mulai', 'tanggal_selesai',
        'jumlah_laporan', 'sarana_prasarana', 'status',
    ];
    protected $useTimestamps = true;

    public function getDetail(int $id): ?array
    {
        $kegiatan = $this->db->table('pkpt_kegiatan pk')
            ->select('pk.*, p.tahun, p.irban_id, i.nama as irban_nama, i.kode as irban_kode')
            ->join('pkpt p', 'p.id = pk.pkpt_id')
            ->join('irban i', 'i.id = p.irban_id')
            ->where('pk.id', $id)
            ->get()->getRowArray();

        if (!$kegiatan) return null;

        $kegiatan['entitas'] = $this->db->table('pkpt_entitas pe')
            ->select('pe.entitas_id, e.nama as entitas_nama')
            ->join('entitas e', 'e.id = pe.entitas_id')
            ->where('pe.pkpt_kegiatan_id', $id)
            ->get()->getResultArray();

        $kegiatan['tim'] = $this->db->table('pkpt_tim pt')
            ->select('pt.*, s.nama as sdm_nama, s.jabatan_struktural, s.nip, s.pangkat_golongan')
            ->join('sdm s', 's.id = pt.sdm_id')
            ->where('pt.pkpt_kegiatan_id', $id)
            ->orderBy('pt.urutan')
            ->get()->getResultArray();

        $kegiatan['total_hp']       = array_sum(array_column($kegiatan['tim'], 'hp_total'));
        $kegiatan['total_anggaran'] = array_sum(array_column($kegiatan['tim'], 'anggaran'));

        return $kegiatan;
    }

    public function getByPkpt(int $pkptId): array
    {
        return $this->db->table('pkpt_kegiatan pk')
            ->select('pk.*,
                (SELECT COUNT(*) FROM spt WHERE pkpt_kegiatan_id = pk.id) as jumlah_spt,
                (SELECT COUNT(*) FROM spt WHERE pkpt_kegiatan_id = pk.id AND status = "terbit") as spt_terbit,
                (SELECT COALESCE(SUM(hp_total),0) FROM pkpt_tim WHERE pkpt_kegiatan_id = pk.id) as total_hp,
                (SELECT COALESCE(SUM(anggaran),0) FROM pkpt_tim WHERE pkpt_kegiatan_id = pk.id) as total_anggaran')
            ->where('pk.pkpt_id', $pkptId)
            ->orderBy('pk.kode_kegiatan')
            ->get()->getResultArray();
    }

    /**
     * Generate kode kegiatan: PKP-{TAHUN}-{KODE_IRBAN}-{URUTAN 3 DIGIT}
     */
    public function generateKode(int $pkptId): string
    {
        $pkpt = $this->db->table('pkpt p')
            ->select('p.tahun, i.kode as irban_kode')
            ->join('irban i', 'i.id = p.irban_id')
            ->where('p.id', $pkptId)
            ->get()->getRowArray();

        $count = $this->where('pkpt_id', $pkptId)->countAllResults();
        $urut  = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $irbanKode = preg_replace('/[^A-Z0-9]/i', '', $pkpt['irban_kode'] ?? 'XX');

        return "PKP-{$pkpt['tahun']}-{$irbanKode}-{$urut}";
    }
}
