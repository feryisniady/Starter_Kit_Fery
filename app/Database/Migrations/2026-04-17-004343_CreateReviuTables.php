<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateReviuTables extends Migration
{
    public function up()
    {
        // Tabel Master Kriteria
        $this->forge->addField([
            'id_kriteria' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_dokumen' => ['type' => 'VARCHAR', 'constraint' => 100],
            'instruksi_ai' => ['type' => 'TEXT'],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_kriteria', true);
        $this->forge->createTable('m_kriteria');

        // Tabel Transaksi Reviu
        $this->forge->addField([
            'id_reviu'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_kriteria'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'file_name'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'hasil_analisis' => ['type' => 'TEXT'],
            'uploaded_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_reviu', true);
        $this->forge->addForeignKey('id_kriteria', 'm_kriteria', 'id_kriteria', 'CASCADE', 'CASCADE');
        $this->forge->createTable('t_reviu');
    }

    public function down()
    {
        $this->forge->dropTable('t_reviu');
        $this->forge->dropTable('m_kriteria');
    }
}