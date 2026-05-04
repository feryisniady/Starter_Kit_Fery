<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel dokumen bukti per prosedur KKA.
 * Satu prosedur (kka_ikhtisar) bisa punya banyak file lampiran.
 */
class CreateKkaIkhtisarDokumen extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kka_ikhtisar_id' => ['type' => 'INT', 'unsigned' => true],
            'nama_file'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'path_file'       => ['type' => 'VARCHAR', 'constraint' => 255],
            'ukuran'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'keterangan'      => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'uploaded_by'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'uploaded_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kka_ikhtisar_id');
        $this->forge->addForeignKey('kka_ikhtisar_id', 'kka_ikhtisar', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kka_ikhtisar_dokumen');
    }

    public function down(): void
    {
        $this->forge->dropTable('kka_ikhtisar_dokumen', true);
    }
}
