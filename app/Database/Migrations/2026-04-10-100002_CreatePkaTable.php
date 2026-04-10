<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreatePkaTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'           => ['type' => 'INT', 'unsigned' => true],
            'nomor_urut'       => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'uraian_prosedur'  => ['type' => 'TEXT'],
            'pic_sdm_id'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'rencana_waktu'    => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true],
            'realisasi_waktu'  => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['belum','selesai'], 'default' => 'belum'],
            'created_by'       => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pka');
    }
    public function down() { $this->forge->dropTable('pka'); }
}
