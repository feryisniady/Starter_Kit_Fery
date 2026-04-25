<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Bersihkan record SDM yang tidak valid:
 * - SDM tanpa irban_id DAN tanpa NIP (bukan staf inspektorat)
 * - Hanya hapus jika SDM tersebut tidak punya data di pkpt_tim atau spt_tim
 *
 * Jalankan: php spark db:seed CleanOrphanSdmSeeder
 *
 * AMAN: tidak hapus SDM yang sudah terlibat dalam PKPT atau SPT.
 */
class CleanOrphanSdmSeeder extends Seeder
{
    public function run(): void
    {
        echo "\n[CleanOrphanSdmSeeder] Memulai pembersihan SDM orphan...\n";

        // SDM tanpa irban_id dan tanpa NIP
        $orphans = $this->db->table('sdm')
            ->where('irban_id IS NULL')
            ->where('(nip IS NULL OR nip = "")')
            ->get()->getResultArray();

        $hapus   = 0;
        $skip    = 0;

        foreach ($orphans as $sdm) {
            $sdmId = $sdm['id'];

            // Cek apakah SDM ini sudah terlibat di pkpt_tim
            $inPkpt = $this->db->table('pkpt_tim')
                ->where('sdm_id', $sdmId)->countAllResults();

            // Cek apakah SDM ini sudah terlibat di spt_tim
            $inSpt = $this->db->table('spt_tim')
                ->where('sdm_id', $sdmId)->countAllResults();

            if ($inPkpt > 0 || $inSpt > 0) {
                echo "  [SKIP]  SDM ID:{$sdmId} '{$sdm['nama']}' — masih dipakai di PKPT/SPT, tidak dihapus.\n";
                $skip++;
                continue;
            }

            $this->db->table('sdm')->where('id', $sdmId)->delete();
            echo "  [HAPUS] SDM ID:{$sdmId} '{$sdm['nama']}' — tidak punya irban/NIP dan tidak dipakai.\n";
            $hapus++;
        }

        echo "\n[CleanOrphanSdmSeeder] Selesai — {$hapus} dihapus, {$skip} dilewati.\n";
        echo "PENTING: Pastikan juga merge branch claude ke Main agar SdmModel pakai INNER JOIN.\n\n";
    }
}
