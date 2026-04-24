<?php
namespace App\Models;

use CodeIgniter\Model;

class TindakLanjutModel extends Model
{
    protected $table      = 'tindak_lanjut';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = [
        'rekomendasi_id', 'entitas_id', 'uraian',
        'status_verifikasi', 'catatan_verifikasi', 'verified_by', 'verified_at',
    ];

    public static array $verifikasiLabel = [
        'menunggu' => 'Menunggu Verifikasi',
        'diterima' => 'Diterima',
        'revisi'   => 'Perlu Perbaikan',
    ];

    public static array $verifikasiColor = [
        'menunggu' => 'warning',
        'diterima' => 'success',
        'revisi'   => 'danger',
    ];

    /** Semua TL untuk satu rekomendasi, dengan dokumen */
    public function getByRekomendasi(int $rekId): array
    {
        $rows = $this->where('rekomendasi_id', $rekId)
                     ->orderBy('created_at', 'DESC')
                     ->findAll();

        $dokModel = new TindakLanjutDokumenModel();
        foreach ($rows as &$row) {
            $row['dokumen'] = $dokModel->getByTl($row['id']);
        }
        return $rows;
    }

    /** Semua rekomendasi + status TL terbaru untuk satu entitas */
    public function getRekomendasiByEntitas(int $entitasId): array
    {
        $rows = $this->db->table('rekomendasi r')
            ->select('r.*, t.judul, t.kondisi, t.akibat, t.nilai_temuan,
                      sp.nomor_naskah as spt_nomor, sp.id as spt_id,
                      tl.id as tl_id, tl.status_verifikasi, tl.created_at as tl_tgl')
            ->join('temuan t', 't.id = r.temuan_id')
            ->join('spt sp', 'sp.id = t.spt_id')
            ->join('pkpt_kegiatan pk', 'pk.id = sp.pkpt_kegiatan_id')
            ->join('pkpt_entitas pe', 'pe.pkpt_kegiatan_id = pk.id')
            ->join('tindak_lanjut tl',
                   "tl.rekomendasi_id = r.id AND tl.id = (SELECT MAX(id) FROM tindak_lanjut WHERE rekomendasi_id = r.id)",
                   'left')
            ->where('pe.entitas_id', $entitasId)
            ->where('t.status_temuan', 'buka')
            ->orderBy('r.batas_waktu')
            ->get()->getResultArray();

        return $rows;
    }

    /** Summary untuk dashboard */
    public function getSummaryByEntitas(int $entitasId): array
    {
        $all = $this->getRekomendasiByEntitas($entitasId);
        return [
            'total'    => count($all),
            'belum'    => count(array_filter($all, fn($r) => $r['tl_id'] === null)),
            'menunggu' => count(array_filter($all, fn($r) => $r['status_verifikasi'] === 'menunggu')),
            'revisi'   => count(array_filter($all, fn($r) => $r['status_verifikasi'] === 'revisi')),
            'diterima' => count(array_filter($all, fn($r) => $r['status_verifikasi'] === 'diterima')),
        ];
    }
}
