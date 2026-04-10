<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateRekomendasiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'temuan_id'          => ['type' => 'INT', 'unsigned' => true],
            'nomor_urut'         => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'isi_rekomendasi'    => ['type' => 'TEXT'],
            'batas_waktu'        => ['type' => 'DATE', 'null' => true],
            'nilai_rekomendasi'  => ['type' => 'BIGINT', 'default' => 0],
            'status'             => ['type' => 'ENUM', 'constraint' => ['belum','proses','selesai'], 'default' => 'belum'],
            'created_by'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('temuan_id', 'temuan', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rekomendasi');
    }
    public function down() { $this->forge->dropTable('rekomendasi'); }
}
