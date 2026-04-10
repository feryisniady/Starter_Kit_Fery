<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateTemuanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'          => ['type' => 'INT', 'unsigned' => true],
            'pka_id'          => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'nomor_temuan'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'judul'           => ['type' => 'VARCHAR', 'constraint' => 500],
            'kondisi'         => ['type' => 'TEXT'],
            'kriteria'        => ['type' => 'TEXT'],
            'sebab'           => ['type' => 'TEXT'],
            'akibat'          => ['type' => 'TEXT'],
            'kode_temuan_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'nilai_temuan'    => ['type' => 'BIGINT', 'default' => 0, 'comment' => 'IDR'],
            'status_temuan'   => ['type' => 'ENUM', 'constraint' => ['buka','tutup'], 'default' => 'buka'],
            'created_by'      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('temuan');
    }
    public function down() { $this->forge->dropTable('temuan'); }
}
