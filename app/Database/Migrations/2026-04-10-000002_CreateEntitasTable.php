<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEntitasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'kode'       => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'nama'       => ['type' => 'VARCHAR', 'constraint' => 200],
            'alamat'     => ['type' => 'TEXT', 'null' => true],
            'kepala'     => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'aktif'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('entitas');
    }

    public function down()
    {
        $this->forge->dropTable('entitas');
    }
}
