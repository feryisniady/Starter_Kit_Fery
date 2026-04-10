<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSptApprovalTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'spt_id'      => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tahap'       => ['type' => 'ENUM', 'constraint' => ['irban', 'evlap', 'sekretaris', 'inspektur']],
            'status'      => ['type' => 'ENUM', 'constraint' => ['pending', 'approved', 'rejected'], 'default' => 'pending'],
            'approved_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'approved_at' => ['type' => 'DATETIME', 'null' => true],
            'catatan'     => ['type' => 'TEXT', 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('spt_id');
        $this->forge->createTable('spt_approval');
    }

    public function down()
    {
        $this->forge->dropTable('spt_approval');
    }
}
