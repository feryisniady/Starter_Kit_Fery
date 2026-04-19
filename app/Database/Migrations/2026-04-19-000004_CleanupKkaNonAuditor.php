<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CleanupKkaNonAuditor extends Migration
{
    public function up(): void
    {
        // Hapus KKA yang dimiliki PJ / WPJ / Dalnis
        // KKA hanya untuk Ketua Tim dan Anggota Tim
        $this->db->query("
            DELETE k FROM kka k
            INNER JOIN spt_tim st ON st.spt_id = k.spt_id AND st.sdm_id = k.sdm_id
            WHERE st.peran_spt NOT IN ('Ketua Tim', 'Anggota Tim')
        ");
    }

    public function down(): void
    {
        // Tidak bisa di-rollback (data sudah terhapus)
    }
}
