<?php

namespace App\Models;

use CodeIgniter\Model;

class SdmModel extends Model
{
    protected $table         = 'sdm';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['user_id', 'nip', 'nama', 'pangkat_golongan', 'jabatan_struktural', 'jabatan_fungsional', 'irban_id', 'aktif'];
    protected $useTimestamps = true;

    public function withIrban(): array
    {
        return $this->db->table('sdm s')
            ->select('s.*, i.nama as irban_nama')
            ->join('irban i', 'i.id = s.irban_id', 'left')
            ->where('s.aktif', 1)
            ->orderBy('i.kode, s.nama')
            ->get()->getResultArray();
    }

    public function getByIrban(int $irbanId): array
    {
        return $this->where('irban_id', $irbanId)->where('aktif', 1)->orderBy('nama')->findAll();
    }

    public function getAktif(): array
    {
        return $this->where('aktif', 1)->orderBy('nama')->findAll();
    }

    /**
     * Hitung sisa HP tersedia untuk SDM dalam tahun tertentu.
     */
    public function getSisaHp(int $sdmId, int $tahun): int
    {
        $setting = (new PkptSettingModel())->where('tahun', $tahun)->first();
        $totalHp = $setting ? (int)$setting['total_hp_tahunan'] : 360;

        $terpakai = $this->db->table('pkpt_tim pt')
            ->join('pkpt_kegiatan pk', 'pk.id = pt.pkpt_kegiatan_id')
            ->join('pkpt p', 'p.id = pk.pkpt_id')
            ->where('pt.sdm_id', $sdmId)
            ->where('p.tahun', $tahun)
            ->where('pk.status !=', 'batal')
            ->selectSum('pt.hp_total')
            ->get()->getRow();

        return $totalHp - (int)($terpakai->hp_total ?? 0);
    }
}
