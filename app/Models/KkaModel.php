<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;

/**
 * Model KKA (Kertas Kerja Audit)
 *
 * Satu KKA header per AT (sdm) per SPT.
 * Auto-dibuat saat Dalnis menyetujui KM-5.
 *
 * Alur baru (disederhanakan): draft → selesai
 * (ikhtisar_selesai & simpulan_selesai dipertahankan untuk backward-compat data lama)
 */
class KkaModel
{
    protected BaseConnection $db;

    public static array $statusLabel = [
        'draft'             => 'Sedang Diisi',
        'ikhtisar_selesai'  => 'Sedang Diisi',
        'simpulan_selesai'  => 'Sedang Diisi',
        'selesai'           => 'Selesai',
    ];

    public static array $statusColor = [
        'draft'             => 'secondary',
        'ikhtisar_selesai'  => 'secondary',
        'simpulan_selesai'  => 'secondary',
        'selesai'           => 'success',
    ];

    // Status pengiriman ke KT (status_kka)
    public static array $statusKkaLabel = [
        'draft'     => 'Belum Dikirim',
        'submitted' => 'Menunggu Review KT',
        'approved'  => 'Disetujui KT',
        'rejected'  => 'Dikembalikan KT',
    ];

    public static array $statusKkaColor = [
        'draft'     => 'secondary',
        'submitted' => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
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
            ->whereIn('peran_spt', ['Ketua Tim', 'Anggota Tim'])
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

    /** Semua KKA per SPT (untuk KT / Dalnis) — hanya KT dan Anggota Tim */
    public function getBySpt(int $sptId): array
    {
        return $this->db->table('kka k')
            ->select('k.*, s.nama, s.nip, st.peran_spt')
            ->join('sdm s', 's.id = k.sdm_id')
            ->join('spt_tim st', 'st.spt_id = k.spt_id AND st.sdm_id = k.sdm_id')
            ->where('k.spt_id', $sptId)
            ->whereIn('st.peran_spt', ['Ketua Tim', 'Anggota Tim'])
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
     * Selesaikan KKA langsung: draft → selesai (alur baru yang disederhanakan).
     * Menggantikan alur 3-tahap lama.
     */
    public function selesaikanKka(int $kkaId): bool
    {
        $kka = $this->db->table('kka')->where('id', $kkaId)->get()->getRowArray();
        if (!$kka || $kka['status'] === 'selesai') return false;

        $this->db->table('kka')->where('id', $kkaId)->update([
            'status'     => 'selesai',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    // Backward-compat stubs (lama, tidak dipakai di UI baru)
    public function selesaikanIkhtisar(int $kkaId): bool   { return $this->selesaikanKka($kkaId); }
    public function selesaikanSimpulan(int $kkaId): bool   { return $this->selesaikanKka($kkaId); }
    public function selesaikanRekomendasi(int $kkaId): bool{ return $this->selesaikanKka($kkaId); }

    // ──────────────────────────────────────────────────────────────────────
    // Unified save per prosedur PKA (alur baru)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Simpan satu prosedur PKA secara unified: ikhtisar + simpulan (opsional) sekaligus.
     * Gunakan upsert berdasarkan (kka_id, pka_id).
     */
    public function saveProsedurUnified(int $kkaId, array $data): void
    {
        $now   = date('Y-m-d H:i:s');
        $pkaId = !empty($data['pka_id']) ? (int)$data['pka_id'] : null;

        // ── Upsert kka_ikhtisar ────────────────────────────────────────────
        if ($pkaId) {
            $existIkh = $this->db->table('kka_ikhtisar')
                ->where('kka_id', $kkaId)->where('pka_id', $pkaId)
                ->get()->getRowArray();
        } else {
            $existIkh = null;
        }

        $ikhFields = [
            'hasil_observasi'     => $data['hasil_observasi'] ?? null,
            'realisasi_waktu'     => !empty($data['realisasi_waktu'])     ? (int)$data['realisasi_waktu']     : null,
            'pelaksana_aktual_id' => !empty($data['pelaksana_aktual_id']) ? (int)$data['pelaksana_aktual_id'] : null,
            'updated_at'          => $now,
        ];

        if ($existIkh) {
            $this->db->table('kka_ikhtisar')->where('id', $existIkh['id'])->update($ikhFields);
        } else {
            $next = $this->db->table('kka_ikhtisar')->where('kka_id', $kkaId)->countAllResults() + 1;
            $this->db->table('kka_ikhtisar')->insert(array_merge($ikhFields, [
                'kka_id'     => $kkaId,
                'pka_id'     => $pkaId,
                'nomor_urut' => $next,
                'created_at' => $now,
            ]));
        }

        // ── Upsert atau hapus kka_simpulan ────────────────────────────────
        $adaTemuan = !empty($data['ada_temuan']);

        if ($pkaId) {
            $existSp = $this->db->table('kka_simpulan')
                ->where('kka_id', $kkaId)->where('pka_id', $pkaId)
                ->get()->getRowArray();
        } else {
            $existSp = null;
        }

        if ($adaTemuan) {
            $spFields = [
                'kondisi'          => $data['kondisi']          ?? null,
                'kriteria'         => $data['kriteria']         ?? null,
                'sebab'            => $data['sebab']            ?? null,
                'akibat'           => $data['akibat']           ?? null,
                'rekomendasi_awal' => $data['rekomendasi_awal'] ?? null,
                'kode_temuan_id'   => !empty($data['kode_temuan_id'])  ? (int)$data['kode_temuan_id']  : null,
                'nilai_financial'  => !empty($data['nilai_financial']) ? (int)$data['nilai_financial'] : null,
                'updated_at'       => $now,
            ];
            if ($existSp) {
                $this->db->table('kka_simpulan')->where('id', $existSp['id'])->update($spFields);
            } else {
                $next = $this->db->table('kka_simpulan')->where('kka_id', $kkaId)->countAllResults() + 1;
                $this->db->table('kka_simpulan')->insert(array_merge($spFields, [
                    'kka_id'     => $kkaId,
                    'pka_id'     => $pkaId,
                    'nomor_urut' => $next,
                    'created_at' => $now,
                ]));
            }
        } elseif ($existSp) {
            // Hapus simpulan jika checkbox temuan di-uncheck
            $this->db->table('kka_simpulan')->where('id', $existSp['id'])->delete();
        }
    }

    /**
     * Data per-prosedur PKA untuk view baru (ikhtisar + simpulan + dokumen digabung per pka_id).
     */
    public function getProsedurData(int $kkaId, array $pkaList): array
    {
        // Index ikhtisar by pka_id
        $ikhRows = $this->db->table('kka_ikhtisar')
            ->where('kka_id', $kkaId)->get()->getResultArray();
        $ikhByPka = [];
        $ikhIds   = [];
        foreach ($ikhRows as $r) {
            if ($r['pka_id']) {
                $ikhByPka[(int)$r['pka_id']] = $r;
                $ikhIds[] = (int)$r['id'];
            }
        }

        // Index simpulan by pka_id
        $spRows = $this->db->table('kka_simpulan')
            ->select('ks.*, kt.kode as kode_temuan_kode')
            ->from('kka_simpulan ks')
            ->join('kode_temuan kt', 'kt.id = ks.kode_temuan_id', 'left')
            ->where('ks.kka_id', $kkaId)->get()->getResultArray();
        $spByPka = [];
        foreach ($spRows as $r) {
            if (!empty($r['pka_id'])) $spByPka[(int)$r['pka_id']] = $r;
        }

        // Load dokumen grouped by kka_ikhtisar_id
        $dokByIkh = [];
        if (!empty($ikhIds)) {
            $dokRows = $this->db->table('kka_prosedur_dokumen')
                ->whereIn('kka_ikhtisar_id', $ikhIds)
                ->orderBy('id')
                ->get()->getResultArray();
            foreach ($dokRows as $d) {
                $dokByIkh[(int)$d['kka_ikhtisar_id']][] = $d;
            }
        }

        $result = [];
        foreach ($pkaList as $pka) {
            $id  = (int)$pka['id'];
            $ikh = $ikhByPka[$id] ?? null;
            $result[] = [
                'pka'      => $pka,
                'ikhtisar' => $ikh,
                'simpulan' => $spByPka[$id] ?? null,
                'dokumen'  => $ikh ? ($dokByIkh[(int)$ikh['id']] ?? []) : [],
            ];
        }
        return $result;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Ikhtisar CRUD
    // ──────────────────────────────────────────────────────────────────────

    public function getIkhtisarByKka(int $kkaId): array
    {
        return $this->db->table('kka_ikhtisar ki')
            ->select('ki.*, p.nomor_urut as pka_nomor, p.uraian_prosedur as pka_prosedur, p.rencana_waktu as pka_rencana_waktu')
            ->join('pka p', 'p.id = ki.pka_id', 'left')
            ->where('ki.kka_id', $kkaId)
            ->orderBy('ki.nomor_urut')
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
        return $this->db->table('kka_simpulan ks')
            ->select('ks.*, kt.kode as kode_temuan_kode, kt.uraian as kode_temuan_uraian, kt.jenis as kode_temuan_jenis')
            ->join('kode_temuan kt', 'kt.id = ks.kode_temuan_id', 'left')
            ->where('ks.kka_id', $kkaId)
            ->orderBy('ks.nomor_urut')
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
        $draftCount = $this->db->table('kka k')
            ->join('spt_tim st', 'st.spt_id = k.spt_id AND st.sdm_id = k.sdm_id')
            ->where('k.spt_id', $sptId)
            ->whereIn('st.peran_spt', ['Ketua Tim', 'Anggota Tim'])
            ->whereNotIn('k.status', ['selesai'])
            ->countAllResults();

        return $draftCount === 0;
    }

    // ──────────────────────────────────────────────────────────────────────
    // KKA Submission Flow (AT → KT)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * AT mengajukan KKA ke KT untuk direview.
     * Syarat: kka.status = 'selesai' DAN status_kka = 'draft' atau 'rejected'.
     */
    public function submitKka(int $kkaId): bool
    {
        $kka = $this->db->table('kka')->where('id', $kkaId)->get()->getRowArray();
        if (!$kka) return false;
        if ($kka['status'] !== 'selesai') return false;
        if (!in_array($kka['status_kka'], ['draft', 'rejected'])) return false;

        $this->db->table('kka')->where('id', $kkaId)->update([
            'status_kka'   => 'submitted',
            'submitted_at' => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    /**
     * KT menyetujui KKA.
     */
    public function approveKka(int $kkaId, ?string $catatan = null): bool
    {
        $kka = $this->db->table('kka')->where('id', $kkaId)->get()->getRowArray();
        if (!$kka || $kka['status_kka'] !== 'submitted') return false;

        $this->db->table('kka')->where('id', $kkaId)->update([
            'status_kka'     => 'approved',
            'catatan_review' => $catatan,
            'reviewed_at'    => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    /**
     * KT mengembalikan KKA ke AT (reject).
     * Catatan wajib diisi.
     */
    public function rejectKka(int $kkaId, string $catatan): bool
    {
        $kka = $this->db->table('kka')->where('id', $kkaId)->get()->getRowArray();
        if (!$kka || $kka['status_kka'] !== 'submitted') return false;

        $this->db->table('kka')->where('id', $kkaId)->update([
            'status_kka'     => 'rejected',
            'catatan_review' => $catatan,
            'reviewed_at'    => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    /**
     * AT membuka kembali KKA yang dikembalikan KT agar bisa diperbaiki.
     */
    public function reopenKka(int $kkaId): bool
    {
        $kka = $this->db->table('kka')->where('id', $kkaId)->get()->getRowArray();
        if (!$kka || $kka['status_kka'] !== 'rejected') return false;

        $this->db->table('kka')->where('id', $kkaId)->update([
            'status'     => 'draft',
            'status_kka' => 'draft',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return true;
    }

    /**
     * Apakah semua KKA di SPT sudah disetujui KT?
     * Gate check sebelum KT bisa membuat NHP.
     */
    public function allApprovedBySpt(int $sptId): bool
    {
        $notApproved = $this->db->table('kka k')
            ->join('spt_tim st', 'st.spt_id = k.spt_id AND st.sdm_id = k.sdm_id')
            ->where('k.spt_id', $sptId)
            ->whereIn('st.peran_spt', ['Ketua Tim', 'Anggota Tim'])
            ->where('k.status_kka !=', 'approved')
            ->countAllResults();

        return $notApproved === 0;
    }
}
