<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePkptKegiatanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'pkpt_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'kode_kegiatan'    => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'area_pengawasan'  => ['type' => 'VARCHAR', 'constraint' => 200],
            'jenis_pengawasan' => ['type' => 'VARCHAR', 'constraint' => 100],
            'tujuan_sasaran'   => ['type' => 'TEXT'],
            'ruang_lingkup'    => ['type' => 'TEXT', 'null' => true],
            'risiko_audit'     => ['type' => 'ENUM', 'constraint' => ['rendah', 'sedang', 'tinggi'], 'default' => 'sedang'],
            'jadwal_rmp'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'jadwal_rpl'       => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'tanggal_mulai'    => ['type' => 'DATE', 'null' => true],
            'tanggal_selesai'  => ['type' => 'DATE', 'null' => true],
            'jumlah_laporan'   => ['type' => 'INT', 'constraint' => 3, 'default' => 1],
            'sarana_prasarana' => ['type' => 'TEXT', 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['aktif', 'selesai', 'batal'], 'default' => 'aktif'],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('pkpt_id');
        $this->forge->createTable('pkpt_kegiatan');
    }

    public function down()
    {
        $this->forge->dropTable('pkpt_kegiatan');
    }
}
