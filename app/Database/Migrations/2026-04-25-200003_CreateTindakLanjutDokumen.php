<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateTindakLanjutDokumen extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tindak_lanjut_id' => ['type' => 'INT', 'unsigned' => true],
            'nama_file'        => ['type' => 'VARCHAR', 'constraint' => 255],
            'path_file'        => ['type' => 'VARCHAR', 'constraint' => 500],
            'ukuran'           => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'keterangan'       => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
            'uploaded_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('tindak_lanjut_id');
        $this->forge->addForeignKey('tindak_lanjut_id', 'tindak_lanjut', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tindak_lanjut_dokumen');
    }

    public function down(): void
    {
        $this->forge->dropTable('tindak_lanjut_dokumen', true);
    }
}
