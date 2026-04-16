<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSptKmTahap2Tables extends Migration
{
    public function up(): void
    {
        // KM-5 — Lembar Reviu PKA (1 per SPT, dilakukan Pengendali Teknis)
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'           => ['type' => 'INT', 'unsigned' => true],
            'tanggal_reviu'    => ['type' => 'DATE', 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['disetujui', 'dikembalikan'], 'default' => 'disetujui'],
            'catatan_reviu'    => ['type' => 'TEXT', 'null' => true],
            'saran_perbaikan'  => ['type' => 'TEXT', 'null' => true],
            // Checklist reviu PKA
            'cek_tujuan'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_sasaran'      => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_ruang_lingkup'=> ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_metodologi'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_tim'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_waktu'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('spt_id');
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('spt_km5');

        // KM-10 — Exit Meeting (1 per SPT)
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'         => ['type' => 'INT', 'unsigned' => true],
            'waktu_meeting'  => ['type' => 'DATETIME', 'null' => true],
            'nama_auditi'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'jabatan_auditi' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'nip_auditi'     => ['type' => 'VARCHAR', 'constraint' => 35, 'null' => true],
            'cp'             => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tlp_cp'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'hasil_meeting'  => ['type' => 'TEXT', 'null' => true],
            'kesepakatan'    => ['type' => 'TEXT', 'null' => true],
            'catatan'        => ['type' => 'TEXT', 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('spt_id');
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('spt_km10');

        // KM-11 — Reviu Laporan Hasil Pengawasan (1 per SPT)
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'           => ['type' => 'INT', 'unsigned' => true],
            'tanggal_reviu'    => ['type' => 'DATE', 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['layak', 'revisi'], 'default' => 'layak'],
            'catatan_reviu'    => ['type' => 'TEXT', 'null' => true],
            'saran_perbaikan'  => ['type' => 'TEXT', 'null' => true],
            // Checklist reviu laporan
            'cek_sistematika'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_fakta'        => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_rekomendasi'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_bahasa'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_lampiran'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('spt_id');
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('spt_km11');
    }

    public function down(): void
    {
        $this->forge->dropTable('spt_km11', true);
        $this->forge->dropTable('spt_km10', true);
        $this->forge->dropTable('spt_km5', true);
    }
}
