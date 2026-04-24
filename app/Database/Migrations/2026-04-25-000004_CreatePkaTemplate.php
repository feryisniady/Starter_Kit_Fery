<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePkaTemplate extends Migration
{
    public function up()
    {
        $this->db->query("
            CREATE TABLE pka_template (
                id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                nama        VARCHAR(255)  NOT NULL,
                jenis_audit VARCHAR(100)  NULL COMMENT 'Filter jenis audit dari PKPT',
                deskripsi   TEXT          NULL,
                created_by  INT UNSIGNED  NULL,
                created_at  DATETIME      NULL,
                updated_at  DATETIME      NULL,
                KEY idx_jenis (jenis_audit)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $this->db->query("
            CREATE TABLE pka_template_item (
                id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                template_id      INT UNSIGNED NOT NULL,
                fase             ENUM('persiapan','pelaksanaan','pelaporan') NOT NULL DEFAULT 'pelaksanaan',
                nomor_urut       TINYINT UNSIGNED NOT NULL DEFAULT 1,
                uraian_prosedur  TEXT NOT NULL,
                created_at       DATETIME NULL,
                updated_at       DATETIME NULL,
                KEY idx_template (template_id),
                KEY idx_fase (fase)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function down()
    {
        $this->db->query("DROP TABLE IF EXISTS pka_template_item");
        $this->db->query("DROP TABLE IF EXISTS pka_template");
    }
}
