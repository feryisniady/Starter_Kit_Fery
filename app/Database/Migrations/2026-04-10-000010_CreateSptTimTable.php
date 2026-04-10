<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSptTimTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'spt_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'sdm_id'     => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'peran_spt'  => ['type' => 'VARCHAR', 'constraint' => 80],
            'hp_desk'    => ['type' => 'INT', 'constraint' => 5, 'default' => 1],
            'hp_field'   => ['type' => 'INT', 'constraint' => 5, 'default' => 0],
            'from_pkpt'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'urutan'     => ['type' => 'INT', 'constraint' => 3, 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('spt_id');
        $this->forge->createTable('spt_tim');
    }

    public function down()
    {
        $this->forge->dropTable('spt_tim');
    }
}
