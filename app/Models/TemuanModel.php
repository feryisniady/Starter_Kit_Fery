<?php
namespace App\Models;
use CodeIgniter\Model;

class TemuanModel extends Model
{
    protected $table      = 'temuan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'spt_id','pka_id','nomor_temuan','judul',
        'kondisi','kriteria','sebab','akibat',
        'kode_temuan_id','nilai_temuan','status_temuan','created_by',
    ];
    protected $useTimestamps = true;

    public static array $statusLabel = ['buka' => 'Terbuka', 'tutup' => 'Tertutup'];
    public static array $statusColor = ['buka' => 'danger',  'tutup' => 'success'];

    /** Semua temuan untuk 1 SPT, join kode_temuan + rekomendasi count */
    public function getBySpt(int $sptId): array
    {
        return $this->db->table('temuan t')
            ->select('t.*, kt.kode as kode_temuan, kt.uraian as uraian_temuan, kt.jenis as jenis_temuan,
                      COUNT(r.id) as jumlah_rekomendasi,
                      SUM(r.nilai_rekomendasi) as total_nilai_rekomendasi')
            ->join('kode_temuan kt', 'kt.id = t.kode_temuan_id', 'left')
            ->join('rekomendasi r', 'r.temuan_id = t.id', 'left')
            ->where('t.spt_id', $sptId)
            ->groupBy('t.id')
            ->orderBy('t.nomor_temuan')
            ->get()->getResultArray();
    }

    /** Detail 1 temuan dengan rekomendasi-nya */
    public function getDetail(int $id): ?array
    {
        $temuan = $this->db->table('temuan t')
            ->select('t.*, kt.kode as kode_temuan_kode, kt.uraian as kode_temuan_uraian, kt.jenis,
                      s.nomor_naskah as spt_nomor, pk.kode_kegiatan, i.nama as irban_nama')
            ->join('kode_temuan kt', 'kt.id = t.kode_temuan_id', 'left')
            ->join('spt s', 's.id = t.spt_id', 'left')
            ->join('pkpt_kegiatan pk', 'pk.id = s.pkpt_kegiatan_id', 'left')
            ->join('pkpt p', 'p.id = pk.pkpt_id', 'left')
            ->join('irban i', 'i.id = p.irban_id', 'left')
            ->where('t.id', $id)
            ->get()->getRowArray();

        if (!$temuan) return null;

        $temuan['rekomendasi'] = (new RekomendasiModel())->where('temuan_id', $id)->orderBy('nomor_urut')->findAll();
        return $temuan;
    }

    /** Nomor temuan berikutnya: T-001, T-002 dst */
    public function nextNomor(int $sptId): string
    {
        $row = $this->where('spt_id', $sptId)->selectMax('nomor_temuan')->first();
        if (!$row || !$row['nomor_temuan']) return 'T-001';
        $last = (int) substr($row['nomor_temuan'], 2);
        return 'T-' . str_pad($last + 1, 3, '0', STR_PAD_LEFT);
    }

    /** Ringkasan temuan untuk header SPT */
    public function getSummaryBySpt(int $sptId): array
    {
        $rows = $this->where('spt_id', $sptId)->findAll();
        $totalNilai = array_sum(array_column($rows, 'nilai_temuan'));
        $buka  = count(array_filter($rows, fn($r) => $r['status_temuan'] === 'buka'));
        return [
            'total'       => count($rows),
            'buka'        => $buka,
            'tutup'       => count($rows) - $buka,
            'total_nilai' => $totalNilai,
        ];
    }
}
