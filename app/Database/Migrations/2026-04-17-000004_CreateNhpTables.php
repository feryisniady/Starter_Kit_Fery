<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * NHP — Notisi Hasil Pemeriksaan
 *
 * Alur:
 *   KT kompilasi simpulan AT → buat NHP → kirim ke entitas → entitas tanggapi
 *   Tanggapan sesuai → temuan ditutup
 *   Tanggapan tidak sesuai → temuan masuk Matriks Temuan → LHP
 */
class CreateNhpTables extends Migration
{
    public function up(): void
    {
        // ── nhp: header satu NHP per pengiriman ───────────────────────────
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'      => ['type' => 'INT', 'unsigned' => true],
            'nomor_nhp'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'tanggal_nhp' => ['type' => 'DATE', 'null' => true],
            'perihal'     => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            // draft → terkirim → ditanggapi → selesai
            'status'      => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'terkirim', 'ditanggapi', 'selesai'],
                'default'    => 'draft',
            ],
            'catatan'     => ['type' => 'TEXT', 'null' => true],
            'created_by'  => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('spt_id');
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('nhp');

        // ── nhp_item: satu temuan sementara per baris ─────────────────────
        // Setiap item merujuk ke satu kka_simpulan (temuan AT)
        // Status tanggapan menentukan apakah temuan masuk LHP/Matriks atau ditutup
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nhp_id'            => ['type' => 'INT', 'unsigned' => true],
            'kka_simpulan_id'   => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'nomor_urut'        => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            // Data temuan (disalin dari simpulan + bisa diedit KT)
            'judul_temuan'      => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'kondisi'           => ['type' => 'TEXT', 'null' => true],
            'kriteria'          => ['type' => 'TEXT', 'null' => true],
            'sebab'             => ['type' => 'TEXT', 'null' => true],
            'akibat'            => ['type' => 'TEXT', 'null' => true],
            'rekomendasi'       => ['type' => 'TEXT', 'null' => true],
            'nilai_temuan'      => ['type' => 'BIGINT', 'constraint' => 20, 'null' => true, 'default' => null],
            // Tanggapan entitas
            'tanggapan_entitas' => ['type' => 'TEXT', 'null' => true],
            // pending → sesuai (tutup) | tidak_sesuai (masuk LHP)
            'status_tanggapan'  => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'sesuai', 'tidak_sesuai'],
                'default'    => 'pending',
            ],
            'tgl_tanggapan'     => ['type' => 'DATE', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('nhp_id');
        $this->forge->addForeignKey('nhp_id', 'nhp', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('nhp_item');
    }

    public function down(): void
    {
        $this->forge->dropTable('nhp_item', true);
        $this->forge->dropTable('nhp', true);
    }
}
