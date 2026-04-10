<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePkptSettingTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tahun'             => ['type' => 'YEAR'],
            'total_hp_tahunan'  => ['type' => 'INT', 'constraint' => 5, 'default' => 360],
            'tarif_hp'          => ['type' => 'INT', 'constraint' => 11, 'default' => 160000],
            'tanggal_pkpt'      => ['type' => 'DATE', 'null' => true],
            'nomor_pkpt'        => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('tahun');
        $this->forge->createTable('pkpt_setting');
    }

    public function down()
    {
        $this->forge->dropTable('pkpt_setting');
    }
}
