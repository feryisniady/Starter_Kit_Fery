<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/**
 * Model KKA (Kertas Kerja Audit)
 *
 * Satu KKA header per AT (sdm) per SPT.
 * Auto-dibuat saat Dalnis menyetujui KM-5.
 *
 * Alur sequential per KKA:
 *   draft → ikhtisar_selesai → simpulan_selesai → selesai
 */
class KkaModel
{
    protected BaseConnection $db;

    public static array $statusLabel = [
        'draft'             => 'Draft',
        'ikhtisar_selesai'  => 'Ikhtisar Selesai',
        'simpulan_selesai'  => 'Simpulan Selesai',
        'selesai'           => 'Selesai',
    ];

    public static array $statusColor = [
        'draft'             => 'secondary',
        'ikhtisar_selesai'  => 'info',
        'simpulan_selesai'  => 'warning',
        'selesai'           => 'success',
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Auto-create KKA per AT saat KM-5 disetujui
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Buat record kka untuk setiap anggota tim dalam SPT.
     * Idempotent — lewati jika sudah ada.
     */
    public function autoCreateForSpt(int $sptId): int
    {
        $tim = $this->db->table('spt_tim')
            ->where('spt_id', $sptId)
            ->get()->getResultArray();

        $created = 0;
        $now     = date('Y-m-d H:i:s');

        foreach ($tim as $member) {
            $exists = $this->db->table('kka')
                ->where('spt_id', $sptId)
                ->where('sdm_id', $member['sdm_id'])
                ->countAllResults();

            if (!$exists) {
                $this->db->table('kka')->insert([
                    'spt_id'     => $sptId,
                    'sdm_id'     => $member['sdm_id'],
                    'status'     => 'draft',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $created++;
            }
        }

        return $created;
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET helpers
    // ──────────────────────────────────────────────────────────────────────

    /** Semua KKA per SPT (untuk KT / Dalnis) dengan nama SDM */
    public function getBySpt(int $sptId): array
    {
        return $this->db->table('kka k')
            ->select('k.*, s.nama, s.nip, st.peran_spt')
            ->join('sdm s', 's.id = k.sdm_id')
            ->join('spt_tim st', 'st.spt_id = k.spt_id AND st.sdm_id = k.sdm_id', 'left')
            ->where('k.spt_id', $sptId)
            ->orderBy('st.urutan')
            ->get()->getResultArray();
    }

    /** KKA milik satu AT (untuk AT yang sedang login) */
    public function getByAt(int $sptId, int $sdmId): ?array
    {
        return $this->db->table('kka k')
            ->select('k.*, s.nama, s.nip, st.peran_spt')
            ->join('sdm s', 's.id = k.sdm_id')
            ->join('spt_tim st', 'st.spt_id = k.spt_id AND st.sdm_id = k.sdm_id', 'left')
            ->where('k.spt_id', $sptId)
            ->where('k.sdm_id', $sdmId)
            ->get()->getRowArray() ?: null;
    }

    /** Satu KKA by id */
    public function find(int $id): ?array
    {
        return $this->db->table('kka k')
            ->select('k.*, s.nama, s.nip, st.peran_spt, sp.nomor_naskah, sp.id AS spt_id')
            ->join('sdm s', 's.id = k.sdm_id')
            ->join('spt_tim st', 'st.spt_id = k.spt_id AND st.sdm_id = k.sdm_id', 'left')
            ->join('spt sp', 'sp.id = k.spt_id')
            ->where('k.id', $id)
            ->get()->getRowArray() ?: null;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Status transitions
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Tandai semua ikhtisar selesai → pindahkan kka ke status ikhtisar_selesai
     */
    public function selesaikanIkhtisar(int $kkaId): bool
    {
        $kka = $this->db->table('kka')->where('id', $kkaId)->get()->getRowArray();
        if (!$kka || $kka['status'] !== 'draft') return false;

        $this->db->table('kka')->where('id', $kkaId)->update([
            'status'     => 'ikhtisar_selesai',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    /**
     * Tandai simpulan selesai → pindahkan kka ke status simpulan_selesai
     */
    public function selesaikanSimpulan(int $kkaId): bool
    {
        $kka = $this->db->table('kka')->where('id', $kkaId)->get()->getRowArray();
        if (!$kka || $kka['status'] !== 'ikhtisar_selesai') return false;

        $this->db->table('kka')->where('id', $kkaId)->update([
            'status'     => 'simpulan_selesai',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    /**
     * Tandai rekomendasi selesai → kka selesai penuh
     */
    public function selesaikanRekomendasi(int $kkaId): bool
    {
        $kka = $this->db->table('kka')->where('id', $kkaId)->get()->getRowArray();
        if (!$kka || $kka['status'] !== 'simpulan_selesai') return false;

        $this->db->table('kka')->where('id', $kkaId)->update([
            'status'     => 'selesai',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Ikhtisar CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function getIkhtisarByKka(int $kkaId): array
    {
        return $this->db->table('kka_ikhtisar')
            ->where('kka_id', $kkaId)
            ->orderBy('nomor_urut')
            ->get()->getResultArray();
    }

    public function findIkhtisar(int $id): ?array
    {
        return $this->db->table('kka_ikhtisar')->where('id', $id)->get()->getRowArray() ?: null;
    }

    public function saveIkhtisar(int $kkaId, array $data): int
    {
        $now  = date('Y-m-d H:i:s');
        $next = $this->db->table('kka_ikhtisar')->where('kka_id', $kkaId)->countAllResults() + 1;

        $this->db->table('kka_ikhtisar')->insert(array_merge($data, [
            'kka_id'     => $kkaId,
            'nomor_urut' => $next,
            'created_at' => $now,
            'updated_at' => $now,
        ]));

        return (int) $this->db->insertID();
    }

    public function updateIkhtisar(int $id, array $data): void
    {
        $this->db->table('kka_ikhtisar')->where('id', $id)->update(array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function deleteIkhtisar(int $id): void
    {
        $this->db->table('kka_ikhtisar')->where('id', $id)->delete();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Simpulan CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function getSimpulanByKka(int $kkaId): array
    {
        return $this->db->table('kka_simpulan')
            ->where('kka_id', $kkaId)
            ->orderBy('nomor_urut')
            ->get()->getResultArray();
    }

    public function findSimpulan(int $id): ?array
    {
        return $this->db->table('kka_simpulan')->where('id', $id)->get()->getRowArray() ?: null;
    }

    public function saveSimpulan(int $kkaId, array $data): int
    {
        $now  = date('Y-m-d H:i:s');
        $next = $this->db->table('kka_simpulan')->where('kka_id', $kkaId)->countAllResults() + 1;

        $this->db->table('kka_simpulan')->insert(array_merge($data, [
            'kka_id'     => $kkaId,
            'nomor_urut' => $next,
            'created_at' => $now,
            'updated_at' => $now,
        ]));

        return (int) $this->db->insertID();
    }

    public function updateSimpulan(int $id, array $data): void
    {
        $this->db->table('kka_simpulan')->where('id', $id)->update(array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function deleteSimpulan(int $id): void
    {
        $this->db->table('kka_simpulan')->where('id', $id)->delete();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Rekomendasi CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function getRekomendasiByKka(int $kkaId): array
    {
        return $this->db->table('kka_rekomendasi')
            ->where('kka_id', $kkaId)
            ->orderBy('nomor_urut')
            ->get()->getResultArray();
    }

    public function findRekomendasi(int $id): ?array
    {
        return $this->db->table('kka_rekomendasi')->where('id', $id)->get()->getRowArray() ?: null;
    }

    public function saveRekomendasi(int $kkaId, array $data): int
    {
        $now  = date('Y-m-d H:i:s');
        $next = $this->db->table('kka_rekomendasi')->where('kka_id', $kkaId)->countAllResults() + 1;

        $this->db->table('kka_rekomendasi')->insert(array_merge($data, [
            'kka_id'     => $kkaId,
            'nomor_urut' => $next,
            'created_at' => $now,
            'updated_at' => $now,
        ]));

        return (int) $this->db->insertID();
    }

    public function updateRekomendasi(int $id, array $data): void
    {
        $this->db->table('kka_rekomendasi')->where('id', $id)->update(array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s'),
        ]));
    }

    public function deleteRekomendasi(int $id): void
    {
        $this->db->table('kka_rekomendasi')->where('id', $id)->delete();
    }

    // ──────────────────────────────────────────────────────────────────────
    // Summary / progress
    // ──────────────────────────────────────────────────────────────────────

    /** Progres KKA per SPT untuk ditampilkan di dashboard KT/Dalnis */
    public function getProgressBySpt(int $sptId): array
    {
        $kkaList = $this->getBySpt($sptId);
        $total   = count($kkaList);
        $counts  = array_fill_keys(['draft', 'ikhtisar_selesai', 'simpulan_selesai', 'selesai'], 0);

        foreach ($kkaList as $k) {
            $counts[$k['status']] = ($counts[$k['status']] ?? 0) + 1;
        }

        return [
            'total'   => $total,
            'counts'  => $counts,
            'percent' => $total > 0 ? round(($counts['selesai'] / $total) * 100) : 0,
        ];
    }

    /**
     * Apakah semua KKA di SPT sudah selesai?
     * Digunakan sebagai prasyarat sebelum KT mengkompilasi Temuan Sementara.
     */
    public function allSelesai(int $sptId): bool
    {
        $draftCount = $this->db->table('kka')
            ->where('spt_id', $sptId)
            ->whereNotIn('status', ['selesai'])
            ->countAllResults();

        return $draftCount === 0;
    }
}
