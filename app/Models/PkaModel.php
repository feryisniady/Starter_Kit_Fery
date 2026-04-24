<?php
namespace App\Models;
use CodeIgniter\Model;

class PkaModel extends Model
{
    protected $table      = 'pka';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'spt_id','fase','nomor_urut','uraian_prosedur',
        'pic_sdm_id','rencana_waktu','realisasi_waktu',
        'status','created_by',
    ];
    protected $useTimestamps = true;

    const FASE_LABEL = [
        'persiapan'  => 'Persiapan',
        'pelaksanaan'=> 'Pelaksanaan',
        'pelaporan'  => 'Pelaporan',
    ];

    /** Semua prosedur PKA untuk 1 SPT, join sdm PIC */
    public function getBySpt(int $sptId): array
    {
        return $this->db->table('pka p')
            ->select('p.*, s.nama as pic_nama')
            ->join('sdm s', 's.id = p.pic_sdm_id', 'left')
            ->where('p.spt_id', $sptId)
            ->orderBy('p.fase')->orderBy('p.nomor_urut')
            ->get()->getResultArray();
    }

    /** PKA per SPT dikelompokkan per fase, dengan daftar AT yang sudah di-assign */
    public function getBySptGrouped(int $sptId): array
    {
        $rows = $this->db->table('pka p')
            ->select('p.*')
            ->where('p.spt_id', $sptId)
            ->orderBy('p.fase')->orderBy('p.nomor_urut')
            ->get()->getResultArray();

        if (empty($rows)) return [];

        // Assignment per pka_id → array sdm_id
        $pkaIds = array_column($rows, 'id');
        $assigns = $this->db->table('pka_assignment pa')
            ->select('pa.pka_id, pa.sdm_id, s.nama as sdm_nama')
            ->join('sdm s', 's.id = pa.sdm_id')
            ->whereIn('pa.pka_id', $pkaIds)
            ->get()->getResultArray();

        $assignMap = [];
        foreach ($assigns as $a) {
            $assignMap[(int)$a['pka_id']][] = ['sdm_id' => (int)$a['sdm_id'], 'nama' => $a['sdm_nama']];
        }

        // Kelompokkan per fase
        $grouped = ['persiapan' => [], 'pelaksanaan' => [], 'pelaporan' => []];
        foreach ($rows as $row) {
            $row['assigned_sdm'] = $assignMap[(int)$row['id']] ?? [];
            $grouped[$row['fase'] ?? 'pelaksanaan'][] = $row;
        }
        return $grouped;
    }

    /** Nomor urut berikutnya dalam 1 fase untuk SPT */
    public function nextNomor(int $sptId, string $fase = 'pelaksanaan'): int
    {
        $row = $this->where('spt_id', $sptId)->where('fase', $fase)->selectMax('nomor_urut')->first();
        return ($row['nomor_urut'] ?? 0) + 1;
    }

    /** Simpan assignment AT untuk sebuah prosedur */
    public function saveAssignment(int $pkaId, array $sdmIds, int $assignedBy): void
    {
        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Hapus assignment lama
        $db->table('pka_assignment')->where('pka_id', $pkaId)->delete();

        // Insert yang baru
        foreach (array_unique($sdmIds) as $sdmId) {
            $sdmId = (int)$sdmId;
            if (!$sdmId) continue;
            $db->table('pka_assignment')->insert([
                'pka_id'      => $pkaId,
                'sdm_id'      => $sdmId,
                'assigned_by' => $assignedBy,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    /** Statistik PKA untuk SPT */
    public function getStatsBySpt(int $sptId): array
    {
        $all     = $this->where('spt_id', $sptId)->findAll();
        $selesai = count(array_filter($all, fn($r) => $r['status'] === 'selesai'));
        return [
            'total'   => count($all),
            'selesai' => $selesai,
            'belum'   => count($all) - $selesai,
        ];
    }
}
