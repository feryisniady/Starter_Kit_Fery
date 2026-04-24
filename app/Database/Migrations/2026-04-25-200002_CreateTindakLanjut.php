<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class CreateTindakLanjut extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'rekomendasi_id'      => ['type' => 'INT', 'unsigned' => true],
            'entitas_id'          => ['type' => 'INT', 'unsigned' => true],
            'uraian'              => ['type' => 'TEXT'],
            'status_verifikasi'   => [
                'type'       => 'ENUM',
                'constraint' => ['menunggu', 'diterima', 'revisi'],
                'default'    => 'menunggu',
            ],
            'catatan_verifikasi'  => ['type' => 'TEXT', 'null' => true],
            'verified_by'         => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'verified_at'         => ['type' => 'DATETIME', 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('rekomendasi_id');
        $this->forge->addKey('entitas_id');
        $this->forge->addForeignKey('rekomendasi_id', 'rekomendasi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('entitas_id', 'entitas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tindak_lanjut');
    }

    public function down(): void
    {
        $this->forge->dropTable('tindak_lanjut', true);
    }
}
