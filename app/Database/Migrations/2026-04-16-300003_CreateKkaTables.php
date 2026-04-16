<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Kertas Kerja Audit (KKA)
 *
 * Dibuat otomatis saat Dalnis menyetujui KM-5 (Reviu PKA).
 * Satu record `kka` per AT (sdm) per SPT.
 *
 * Alur sequential per KKA:
 *   draft → ikhtisar_selesai → simpulan_selesai → selesai
 *
 * AT hanya bisa akses KKA miliknya (sdm_id == session user sdm_id).
 * KT dapat lihat semua KKA per SPT (untuk kompilasi → Temuan Sementara).
 * Dalnis dapat melihat & mereviu semua KKA.
 */
class CreateKkaTables extends Migration
{
    public function up(): void
    {
        // ── kka: header per AT per SPT ─────────────────────────────────────
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'        => ['type' => 'INT', 'unsigned' => true],
            'sdm_id'        => ['type' => 'INT', 'unsigned' => true],
            'no_kka'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            // draft → ikhtisar_selesai → simpulan_selesai → selesai
            'status'        => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'ikhtisar_selesai', 'simpulan_selesai', 'selesai'],
                'default'    => 'draft',
            ],
            'catatan_dalnis'=> ['type' => 'TEXT', 'null' => true],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['spt_id', 'sdm_id']);
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sdm_id', 'sdm', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kka');

        // ── kka_ikhtisar: satu atau lebih per KKA ─────────────────────────
        // Dapat diisi jika kka.status = draft (belum selesai ikhtisar)
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kka_id'          => ['type' => 'INT', 'unsigned' => true],
            'nomor_urut'      => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'program_kerja'   => ['type' => 'TEXT', 'null' => true],   // prosedur PKA yang dijalankan
            'langkah_audit'   => ['type' => 'TEXT', 'null' => true],   // langkah-langkah pengujian
            'hasil_observasi' => ['type' => 'TEXT', 'null' => true],   // fakta/temuan dari lapangan
            'simpulan'        => ['type' => 'TEXT', 'null' => true],   // simpulan ikhtisar
            'dokumen'         => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true], // file lampiran
            'status'          => ['type' => 'ENUM', 'constraint' => ['draft', 'selesai'], 'default' => 'draft'],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kka_id');
        $this->forge->addForeignKey('kka_id', 'kka', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kka_ikhtisar');

        // ── kka_simpulan: kondisi-kriteria-sebab-akibat ────────────────────
        // Hanya bisa diisi jika kka.status = ikhtisar_selesai
        $this->forge->addField([
            'id'               => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kka_id'           => ['type' => 'INT', 'unsigned' => true],
            'nomor_urut'       => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'kondisi'          => ['type' => 'TEXT', 'null' => true],
            'kriteria'         => ['type' => 'TEXT', 'null' => true],
            'sebab'            => ['type' => 'TEXT', 'null' => true],
            'akibat'           => ['type' => 'TEXT', 'null' => true],
            'rekomendasi_awal' => ['type' => 'TEXT', 'null' => true],
            'status'           => ['type' => 'ENUM', 'constraint' => ['draft', 'selesai'], 'default' => 'draft'],
            'created_at'       => ['type' => 'DATETIME', 'null' => true],
            'updated_at'       => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kka_id');
        $this->forge->addForeignKey('kka_id', 'kka', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kka_simpulan');

        // ── kka_rekomendasi: tindak lanjut ────────────────────────────────
        // Hanya bisa diisi jika kka.status = simpulan_selesai
        $this->forge->addField([
            'id'                       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'kka_id'                   => ['type' => 'INT', 'unsigned' => true],
            'nomor_urut'               => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'uraian_rekomendasi'       => ['type' => 'TEXT', 'null' => true],
            'pihak_bertanggung_jawab'  => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'target_penyelesaian'      => ['type' => 'DATE', 'null' => true],
            'tanggapan_auditi'         => ['type' => 'TEXT', 'null' => true],
            'status'                   => ['type' => 'ENUM', 'constraint' => ['draft', 'selesai'], 'default' => 'draft'],
            'created_at'               => ['type' => 'DATETIME', 'null' => true],
            'updated_at'               => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('kka_id');
        $this->forge->addForeignKey('kka_id', 'kka', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('kka_rekomendasi');
    }

    public function down(): void
    {
        $this->forge->dropTable('kka_rekomendasi', true);
        $this->forge->dropTable('kka_simpulan', true);
        $this->forge->dropTable('kka_ikhtisar', true);
        $this->forge->dropTable('kka', true);
    }
}
