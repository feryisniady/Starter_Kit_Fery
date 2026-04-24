<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class MigratePicToPkaAssignment extends Migration
{
    public function up()
    {
        // Salin data pic_sdm_id yang sudah ada ke pka_assignment
        $this->db->query("
            INSERT IGNORE INTO pka_assignment (pka_id, sdm_id, created_at, updated_at)
            SELECT id, pic_sdm_id, NOW(), NOW()
            FROM pka
            WHERE pic_sdm_id IS NOT NULL
        ");
    }

    public function down()
    {
        // Tidak perlu rollback — pic_sdm_id tetap ada sebagai backup
        $this->db->query("DELETE FROM pka_assignment");
    }
}
