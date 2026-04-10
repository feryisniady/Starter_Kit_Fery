<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIrbanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode'         => ['type' => 'VARCHAR', 'constraint' => 20],
            'nama'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'kepala_sdm_id'=> ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('irban');
    }

    public function down()
    {
        $this->forge->dropTable('irban');
    }
}
