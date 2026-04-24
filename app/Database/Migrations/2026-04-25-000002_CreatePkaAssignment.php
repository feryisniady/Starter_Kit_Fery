<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePkaAssignment extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE pka_assignment (
                id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                pka_id     INT UNSIGNED NOT NULL,
                sdm_id     INT UNSIGNED NOT NULL,
                assigned_by INT UNSIGNED NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                UNIQUE KEY uq_pka_sdm (pka_id, sdm_id),
                KEY idx_pka_id (pka_id),
                KEY idx_sdm_id (sdm_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS pka_assignment");
    }
}
