<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/**
 * Model NHP (Notisi Hasil Pemeriksaan)
 *
 * Alur: draft → terkirim → ditanggapi → selesai
 * Setiap NHP memiliki nhp_item yang masing-masing merujuk ke kka_simpulan.
 * Tanggapan per item menentukan: sesuai (tutup) vs tidak_sesuai (masuk LHP/Matriks).
 */
class NhpModel
{
    protected BaseConnection $db;

    public static array $statusLabel = [
        'draft'       => 'Draft',
        'terkirim'    => 'Terkirim',
        'ditanggapi'  => 'Ditanggapi',
        'selesai'     => 'Selesai',
    ];

    public static array $statusColor = [
        'draft'       => 'secondary',
        'terkirim'    => 'info',
        'ditanggapi'  => 'warning',
        'selesai'     => 'success',
    ];

    public static array $tanggapanLabel = [
        'pending'        => 'Menunggu',
        'sesuai'         => 'Sesuai (Ditutup)',
        'tidak_sesuai'   => 'Tidak Sesuai (Buka)',
    ];

    public static array $tanggapanColor = [
        'pending'        => 'secondary',
        'sesuai'         => 'success',
        'tidak_sesuai'   => 'danger',
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ──────────────────────────────────────────────────────────────────────
    // NHP Header CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function getBySpt(int $sptId): array
    {
        return $this->db->table('nhp n')
            ->select('n.*, COUNT(ni.id) as jumlah_item,
                      SUM(CASE WHEN ni.status_tanggapan="sesuai" THEN 1 ELSE 0 END) as jumlah_sesuai,
                      SUM(CASE WHEN ni.status_tanggapan="tidak_sesuai" THEN 1 ELSE 0 END) as jumlah_tidak_sesuai,
                      SUM(CASE WHEN ni.status_tanggapan="pending" THEN 1 ELSE 0 END) as jumlah_pending')
            ->join('nhp_item ni', 'ni.nhp_id = n.id', 'left')
            ->where('n.spt_id', $sptId)
            ->groupBy('n.id')
            ->orderBy('n.tanggal_nhp', 'DESC')
            ->get()->getResultArray();
    }

    public function find(int $id): ?array
    {
        return $this->db->table('nhp')->where('id', $id)->get()->getRowArray() ?: null;
    }

    public function create(int $sptId, array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $nomor = $this->nextNomor($sptId);

        $this->db->table('nhp')->insert(array_merge($data, [
            'spt_id'    => $sptId,
            'nomor_nhp' => $nomor,
            'status'    => 'draft',
            'created_by'=> session()->get('user_id'),
            'created_at'=> $now,
            'updated_at'=> $now,
        ]));

        return (int) $this->db->insertID();
    }

    public function update(int $id, array $data): void
    {
        $this->db->table('nhp')->where('id', $id)->update(array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function delete(int $id): void
    {
        $this->db->table('nhp')->where('id', $id)->delete();
    }

    public function nextNomor(int $sptId): string
    {
        $year = date('Y');
        $count = $this->db->table('nhp')
            ->where('spt_id', $sptId)
            ->countAllResults();
        return 'NHP-' . $year . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    public function kirim(int $id): bool
    {
        $nhp = $this->find($id);
        if (!$nhp || $nhp['status'] !== 'draft') return false;
        $this->db->table('nhp')->where('id', $id)->update([
            'status'      => 'terkirim',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    public function selesaikan(int $id): bool
    {
        $nhp = $this->find($id);
        if (!$nhp || $nhp['status'] === 'selesai') return false;
        $this->db->table('nhp')->where('id', $id)->update([
            'status'      => 'selesai',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    // ──────────────────────────────────────────────────────────────────────
    // NHP Items CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function getItems(int $nhpId): array
    {
        return $this->db->table('nhp_item ni')
            ->select('ni.*, ks.kka_id, k.sdm_id, s.nama as at_nama,
                      kt.kode as kode_temuan_kode, kt.uraian as kode_temuan_uraian, kt.jenis as kode_temuan_jenis')
            ->join('kka_simpulan ks', 'ks.id = ni.kka_simpulan_id', 'left')
            ->join('kka k', 'k.id = ks.kka_id', 'left')
            ->join('sdm s', 's.id = k.sdm_id', 'left')
            ->join('kode_temuan kt', 'kt.id = ks.kode_temuan_id', 'left')
            ->where('ni.nhp_id', $nhpId)
            ->orderBy('ni.nomor_urut')
            ->get()->getResultArray();
    }

    public function findItem(int $id): ?array
    {
        return $this->db->table('nhp_item')->where('id', $id)->get()->getRowArray() ?: null;
    }

    public function addItem(int $nhpId, array $data): int
    {
        $now  = date('Y-m-d H:i:s');
        $next = $this->db->table('nhp_item')->where('nhp_id', $nhpId)->countAllResults() + 1;

        $this->db->table('nhp_item')->insert(array_merge($data, [
            'nhp_id'     => $nhpId,
            'nomor_urut' => $next,
            'created_at' => $now,
            'updated_at' => $now,
        ]));

        return (int) $this->db->insertID();
    }

    public function updateItem(int $id, array $data): void
    {
        $this->db->table('nhp_item')->where('id', $id)->update(array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function deleteItem(int $id): void
    {
        $this->db->table('nhp_item')->where('id', $id)->delete();
    }

    public function recordTanggapan(int $itemId, string $tanggapan, string $status, ?string $tglTanggapan): void
    {
        $this->db->table('nhp_item')->where('id', $itemId)->update([
            'tanggapan_entitas' => $tanggapan,
            'status_tanggapan'  => $status,
            'tgl_tanggapan'     => $tglTanggapan ?: date('Y-m-d'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        // Auto-update NHP status ke ditanggapi jika ada item yang sudah ditanggapi
        $item = $this->findItem($itemId);
        if ($item) {
            $nhp = $this->find($item['nhp_id']);
            if ($nhp && $nhp['status'] === 'terkirim') {
                $this->db->table('nhp')->where('id', $item['nhp_id'])->update([
                    'status'     => 'ditanggapi',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Compiled simpulan untuk KT (semua AT simpulan per SPT)
    // ──────────────────────────────────────────────────────────────────────

    public function getAllSimpulanBySpt(int $sptId): array
    {
        return $this->db->table('kka_simpulan ks')
            ->select('ks.id, ks.kka_id, ks.nomor_urut,
                      ks.kondisi, ks.kriteria, ks.sebab, ks.akibat, ks.rekomendasi_awal,
                      ks.kode_temuan_id, ks.nilai_financial, ks.status,
                      ks.created_at, ks.updated_at,
                      k.id as kka_header_id, k.sdm_id, k.status as kka_status,
                      s.nama as at_nama, s.nip as at_nip,
                      st.peran_spt, st.urutan as st_urutan,
                      kt.kode as kode_temuan_kode, kt.uraian as kode_temuan_uraian, kt.jenis as kode_temuan_jenis')
            ->join('kka k', 'k.id = ks.kka_id')
            ->join('sdm s', 's.id = k.sdm_id')
            ->join('spt_tim st', 'st.spt_id = k.spt_id AND st.sdm_id = k.sdm_id', 'left')
            ->join('kode_temuan kt', 'kt.id = ks.kode_temuan_id', 'left')
            ->where('k.spt_id', $sptId)
            ->groupBy('ks.id')
            ->orderBy('COALESCE(st.urutan, 9999), k.sdm_id, ks.nomor_urut')
            ->get()->getResultArray();
    }

    /** Simpulan yang belum dimasukkan ke NHP manapun */
    public function getSimpulanBelumNhp(int $sptId): array
    {
        $all = $this->getAllSimpulanBySpt($sptId);
        $usedIds = $this->db->table('nhp_item ni')
            ->select('ni.kka_simpulan_id')
            ->join('nhp n', 'n.id = ni.nhp_id')
            ->where('n.spt_id', $sptId)
            ->where('ni.kka_simpulan_id IS NOT NULL')
            ->get()->getResultArray();
        $usedSet = array_column($usedIds, 'kka_simpulan_id');

        return array_filter($all, fn($s) => !in_array($s['id'], $usedSet));
    }

    // ──────────────────────────────────────────────────────────────────────
    // Matriks Temuan (simpulan dengan status_tanggapan = tidak_sesuai)
    // ──────────────────────────────────────────────────────────────────────

    public function getMatriksTemuan(int $sptId): array
    {
        return $this->db->table('nhp_item ni')
            ->select('ni.*, n.nomor_nhp, n.tanggal_nhp,
                      ks.kka_id, k.sdm_id, s.nama as at_nama,
                      kt.kode as kode_temuan_kode, kt.uraian as kode_temuan_uraian, kt.jenis as kode_temuan_jenis')
            ->join('nhp n', 'n.id = ni.nhp_id')
            ->join('kka_simpulan ks', 'ks.id = ni.kka_simpulan_id', 'left')
            ->join('kka k', 'k.id = ks.kka_id', 'left')
            ->join('sdm s', 's.id = k.sdm_id', 'left')
            ->join('kode_temuan kt', 'kt.id = ks.kode_temuan_id', 'left')
            ->where('n.spt_id', $sptId)
            ->where('ni.status_tanggapan', 'tidak_sesuai')
            ->orderBy('n.tanggal_nhp')
            ->orderBy('ni.nomor_urut')
            ->get()->getResultArray();
    }
}
