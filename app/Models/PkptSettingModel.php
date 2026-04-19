<?php

namespace App\Models;

use CodeIgniter\Model;

class PkptSettingModel extends Model
{
    protected $table         = 'pkpt_setting';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'tahun', 'total_hp_tahunan', 'tarif_hp', 'tanggal_pkpt', 'nomor_pkpt',
        'status', 'approved_by', 'approved_at',
    ];
    protected $useTimestamps = true;

    public static array $statusLabel = ['draft' => 'Draft', 'disetujui' => 'Disetujui'];
    public static array $statusColor = ['draft' => 'warning', 'disetujui' => 'success'];

    public function getByTahun(int $tahun): ?array
    {
        return $this->where('tahun', $tahun)->first();
    }

    public function getTahunAktif(): int
    {
        $current = (int)date('Y');
        // Prioritas: tahun sekarang atau tahun terbaru yang ≤ sekarang
        $row = $this->where('tahun <=', $current)->orderBy('tahun', 'DESC')->first();
        if ($row) return (int)$row['tahun'];
        // Fallback: tahun terkecil yang ada (jika semua > sekarang)
        $row = $this->orderBy('tahun', 'ASC')->first();
        return $row ? (int)$row['tahun'] : $current;
    }

    /** Ambil HP efektif: dari hari_libur jika ada, fallback ke total_hp_tahunan */
    public function getHpEfektif(int $tahun): int
    {
        $hp = (new HariLiburModel())->hitungHariKerjaTahun($tahun);
        if ($hp > 0) return $hp;

        $setting = $this->getByTahun($tahun);
        return $setting ? (int)$setting['total_hp_tahunan'] : 0;
    }

    public function approve(int $id, int $userId): void
    {
        $this->update($id, [
            'status'      => 'disetujui',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function revokApproval(int $id): void
    {
        $this->update($id, [
            'status'      => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }
}
