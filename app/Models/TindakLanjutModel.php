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

    /** Semua TL untuk panel verifikasi admin BPKP */
    public function getAllForAdmin(?string $status = null): array
    {
        $q = $this->db->table('tindak_lanjut tl')
            ->select('tl.*, r.isi_rekomendasi, r.batas_waktu, r.nilai_rekomendasi, r.id as rekomendasi_id,
                      t.judul as temuan_judul, t.nilai_temuan,
                      sp.nomor_naskah as spt_nomor, sp.id as spt_id,
                      e.nama as entitas_nama, uv.name as verified_by_nama')
            ->join('rekomendasi r', 'r.id = tl.rekomendasi_id')
            ->join('temuan t', 't.id = r.temuan_id')
            ->join('spt sp', 'sp.id = t.spt_id')
            ->join('entitas e', 'e.id = tl.entitas_id')
            ->join('users uv', 'uv.id = tl.verified_by', 'left')
            ->orderBy('tl.created_at', 'DESC');

        if ($status) {
            $q->where('tl.status_verifikasi', $status);
        }

        return $q->get()->getResultArray();
    }

    /** Detail satu TL lengkap + dokumen + riwayat semua TL untuk rekomendasi yang sama */
    public function getDetailForAdmin(int $tlId): ?array
    {
        $row = $this->db->table('tindak_lanjut tl')
            ->select('tl.*, r.isi_rekomendasi, r.batas_waktu, r.nilai_rekomendasi, r.id as rekomendasi_id,
                      r.status as rekomendasi_status,
                      t.judul as temuan_judul, t.kondisi, t.sebab, t.akibat, t.nilai_temuan,
                      sp.nomor_naskah as spt_nomor, sp.id as spt_id,
                      e.nama as entitas_nama, e.id as entitas_id_ref,
                      uv.name as verified_by_nama')
            ->join('rekomendasi r', 'r.id = tl.rekomendasi_id')
            ->join('temuan t', 't.id = r.temuan_id')
            ->join('spt sp', 'sp.id = t.spt_id')
            ->join('entitas e', 'e.id = tl.entitas_id')
            ->join('users uv', 'uv.id = tl.verified_by', 'left')
            ->where('tl.id', $tlId)
            ->get()->getRowArray();

        if (!$row) return null;

        $dokModel = new TindakLanjutDokumenModel();
        $row['dokumen'] = $dokModel->getByTl($tlId);
        $row['riwayat'] = $this->getByRekomendasi($row['rekomendasi_id']);

        return $row;
    }
}
