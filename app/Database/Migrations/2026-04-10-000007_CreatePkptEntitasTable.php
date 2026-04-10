<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePkptEntitasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'pkpt_kegiatan_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'entitas_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pkpt_kegiatan_id');
        $this->forge->createTable('pkpt_entitas');
    }

    public function down()
    {
        $this->forge->dropTable('pkpt_entitas');
    }
}
