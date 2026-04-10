<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePkptTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tahun'       => ['type' => 'YEAR'],
            'irban_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status'      => ['type' => 'ENUM', 'constraint' => ['draft', 'diajukan', 'disetujui'], 'default' => 'draft'],
            'created_by'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'approved_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('irban_id');
        $this->forge->addKey('tahun');
        $this->forge->createTable('pkpt');
    }

    public function down()
    {
        $this->forge->dropTable('pkpt');
    }
}
