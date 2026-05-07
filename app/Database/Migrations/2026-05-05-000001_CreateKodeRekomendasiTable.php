<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateKodeRekomendasiTable extends Migration
{
    public function up()
    {
        // Tabel master kode rekomendasi (14 kode, PermenpanRB 42/2011)
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kode'       => ['type' => 'VARCHAR', 'constraint' => 5, 'unique' => true],
            'uraian'     => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('kode_rekomendasi');

        // Tambah kolom alternatif ke kode_temuan
        // Menyimpan kode_rekomendasi yang relevan (comma-separated), contoh: "R.01,R.02,R.04"
        $this->forge->addColumn('kode_temuan', [
            'alternatif' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'jenis',
                'comment'    => 'Kode rekomendasi relevan (comma-separated), contoh: R.01,R.03',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('kode_rekomendasi', true);
        $this->forge->dropColumn('kode_temuan', 'alternatif');
    }
}
