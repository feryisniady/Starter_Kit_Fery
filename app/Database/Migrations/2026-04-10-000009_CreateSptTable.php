<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSptTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'pkpt_kegiatan_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nomor_naskah'        => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'tanggal_naskah'      => ['type' => 'DATE', 'null' => true],
            'dasar_1'             => ['type' => 'TEXT', 'null' => true],
            'dasar_2'             => ['type' => 'TEXT', 'null' => true],
            'tujuan'              => ['type' => 'TEXT', 'null' => true],
            'tanggal_mulai'       => ['type' => 'DATE', 'null' => true],
            'tanggal_selesai'     => ['type' => 'DATE', 'null' => true],
            'tembusan'            => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'penandatangan_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'status'              => ['type' => 'ENUM', 'constraint' => ['draft', 'diajukan', 'acc_irban', 'acc_evlap', 'acc_sekretaris', 'terbit'], 'default' => 'draft'],
            'file_word'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'catatan'             => ['type' => 'TEXT', 'null' => true],
            'created_by'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pkpt_kegiatan_id');
        $this->forge->createTable('spt');
    }

    public function down()
    {
        $this->forge->dropTable('spt');
    }
}
