<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateNhpItemDokumen extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nhp_item_id' => ['type' => 'INT', 'unsigned' => true],
            'nama_file'   => ['type' => 'VARCHAR', 'constraint' => 255],
            'path_file'   => ['type' => 'VARCHAR', 'constraint' => 500],
            'ukuran'      => ['type' => 'INT', 'unsigned' => true, 'default' => 0, 'comment' => 'bytes'],
            'keterangan'  => ['type' => 'VARCHAR', 'constraint' => 300, 'null' => true],
            'uploaded_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'uploaded_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('nhp_item_id');
        $this->forge->addForeignKey('nhp_item_id', 'nhp_item', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('nhp_item_dokumen');
    }

    public function down(): void
    {
        $this->forge->dropTable('nhp_item_dokumen', true);
    }
}
