<?php
namespace App\Models;
use CodeIgniter\Model;

class RekomendasiModel extends Model
{
    protected $table      = 'rekomendasi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'temuan_id','nomor_urut','isi_rekomendasi',
        'batas_waktu','nilai_rekomendasi','status','created_by',
    ];
    protected $useTimestamps = true;

    public static array $statusLabel = [
        'belum'   => 'Belum Ditindaklanjuti',
        'proses'  => 'Dalam Proses',
        'selesai' => 'Selesai',
    ];
    public static array $statusColor = [
        'belum'   => 'danger',
        'proses'  => 'warning',
        'selesai' => 'success',
    ];

    /** Simpan batch rekomendasi dari POST (hapus lama, insert baru) */
    public function saveBatch(int $temuanId, array $rows, int $userId): void
    {
        $this->where('temuan_id', $temuanId)->delete();
        foreach ($rows as $i => $row) {
            if (empty($row['isi_rekomendasi'])) continue;
            $this->insert([
                'temuan_id'          => $temuanId,
                'nomor_urut'         => $i + 1,
                'isi_rekomendasi'    => $row['isi_rekomendasi'],
                'batas_waktu'        => $row['batas_waktu'] ?: null,
                'nilai_rekomendasi'  => (int)($row['nilai_rekomendasi'] ?? 0),
                'status'             => $row['status'] ?? 'belum',
                'created_by'         => $userId,
            ]);
        }
    }
}
