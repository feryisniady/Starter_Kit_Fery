<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePkptTimTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'pkpt_kegiatan_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sdm_id'            => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'peran'             => ['type' => 'ENUM', 'constraint' => ['PJ', 'WPJ', 'Dalnis', 'KT', 'AT']],
            'hp_total'          => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'anggaran'          => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0],
            'urutan'            => ['type' => 'INT', 'constraint' => 3, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pkpt_kegiatan_id');
        $this->forge->addKey('sdm_id');
        $this->forge->createTable('pkpt_tim');
    }

    public function down()
    {
        $this->forge->dropTable('pkpt_tim');
    }
}
