<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddFaseToPka extends Migration
{
    public function up()
    {
        $this->db->query("
            ALTER TABLE pka
            ADD COLUMN fase ENUM('persiapan','pelaksanaan','pelaporan')
                NOT NULL DEFAULT 'pelaksanaan'
            AFTER spt_id
        ");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE pka DROP COLUMN fase");
    }
}
