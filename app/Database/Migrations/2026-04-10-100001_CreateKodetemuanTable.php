<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateKodetemuanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kode'       => ['type' => 'VARCHAR', 'constraint' => 15, 'unique' => true],
            'uraian'     => ['type' => 'TEXT'],
            'jenis'      => ['type' => 'TINYINT', 'default' => 1,
                             'comment' => '1=Kerugian/Potensi, 2=Kelemahan SPI, 3=Ketidakefektifan'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('kode_temuan');
    }
    public function down() { $this->forge->dropTable('kode_temuan'); }
}
