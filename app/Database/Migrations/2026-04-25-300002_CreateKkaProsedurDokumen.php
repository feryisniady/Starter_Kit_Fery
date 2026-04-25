<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKkaProsedurDokumen extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kka_ikhtisar_id' => ['type' => 'INT', 'unsigned' => true],
            'nama_file'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'path_file'       => ['type' => 'VARCHAR', 'constraint' => 500],
            'ukuran'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'keterangan'      => ['type' => 'TEXT', 'null' => true],
            'uploaded_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kka_ikhtisar_id');
        $this->forge->addForeignKey('kka_ikhtisar_id', 'kka_ikhtisar', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kka_prosedur_dokumen');
    }

    public function down(): void
    {
        $this->forge->dropTable('kka_prosedur_dokumen', true);
    }
}
