<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSdmTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'              => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'nip'                  => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'nama'                 => ['type' => 'VARCHAR', 'constraint' => 150],
            'pangkat_golongan'     => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'jabatan_struktural'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'jabatan_fungsional'   => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'irban_id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'aktif'                => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'           => ['type' => 'DATETIME', 'null' => true],
            'updated_at'           => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('irban_id');
        $this->forge->createTable('sdm');
    }

    public function down()
    {
        $this->forge->dropTable('sdm');
    }
}
