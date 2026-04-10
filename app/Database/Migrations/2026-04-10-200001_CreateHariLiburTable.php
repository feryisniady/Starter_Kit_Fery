<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHariLiburTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tahun'       => ['type' => 'YEAR'],
            'tanggal'     => ['type' => 'DATE'],
            'keterangan'  => ['type' => 'VARCHAR', 'constraint' => 150],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('tanggal');
        $this->forge->addKey('tahun');
        $this->forge->createTable('hari_libur');
    }

    public function down()
    {
        $this->forge->dropTable('hari_libur');
    }
}
