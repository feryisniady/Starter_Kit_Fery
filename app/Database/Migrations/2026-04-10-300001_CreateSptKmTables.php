<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSptKmTables extends Migration
{
    public function up(): void
    {
        // KM1 — Kartu Penugasan (1 per SPT)
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'          => ['type' => 'INT', 'unsigned' => true],
            'no_kartu'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'tujuan_satker'   => ['type' => 'TEXT', 'null' => true],
            'kegiatan'        => ['type' => 'TEXT', 'null' => true],
            'rencana_mulai'   => ['type' => 'DATE', 'null' => true],
            'rencana_selesai' => ['type' => 'DATE', 'null' => true],
            'rencana_kunjungan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'catatan'         => ['type' => 'TEXT', 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('spt_id');
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('spt_km1');

        // KM4 — Lembar Perencanaan Pengawasan (1 per SPT)
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'            => ['type' => 'INT', 'unsigned' => true],
            'dasar_penugasan'   => ['type' => 'TEXT', 'null' => true],
            'jenis_penugasan'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tujuan_pengawasan' => ['type' => 'TEXT', 'null' => true],
            'sasaran'           => ['type' => 'TEXT', 'null' => true],
            // 11 item checklist: 0=Belum, 1=Sudah
            'cek_kp'            => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_jenis_tujuan'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_misi_tujuan'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_informasi'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_lhp_terakhir'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_lhp_ekstern'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_perundangan'   => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_kertas_kerja'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_tao_fao'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_program'       => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'cek_anggaran_waktu'=> ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'catatan_dalnis'    => ['type' => 'TEXT', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('spt_id');
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('spt_km4');

        // KM6 — Notulensi Kesepakatan (1 per SPT)
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'              => ['type' => 'INT', 'unsigned' => true],
            'waktu_rapat'         => ['type' => 'DATETIME', 'null' => true],
            'waktu_sp'            => ['type' => 'DATE', 'null' => true],
            'rencana_laporan'     => ['type' => 'DATE', 'null' => true],
            'jabatan_auditi'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'nama_auditi'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'nip_auditi'          => ['type' => 'VARCHAR', 'constraint' => 35, 'null' => true],
            'cp'                  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'tlp_cp'              => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'catatan'             => ['type' => 'TEXT', 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('spt_id');
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('spt_km6');

        // Anggaran Waktu — per anggota tim per SPT
        $this->forge->addField([
            'id'                  => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'              => ['type' => 'INT', 'unsigned' => true],
            'sdm_id'              => ['type' => 'INT', 'unsigned' => true],
            'persiapan_start'     => ['type' => 'DATE', 'null' => true],
            'persiapan_end'       => ['type' => 'DATE', 'null' => true],
            'pelaksanaan_start'   => ['type' => 'DATE', 'null' => true],
            'pelaksanaan_end'     => ['type' => 'DATE', 'null' => true],
            'penyelesaian_start'  => ['type' => 'DATE', 'null' => true],
            'penyelesaian_end'    => ['type' => 'DATE', 'null' => true],
            'created_at'          => ['type' => 'DATETIME', 'null' => true],
            'updated_at'          => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['spt_id', 'sdm_id']);
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('spt_anggaran_waktu');

        // Pernyataan Independensi — per anggota tim per SPT
        $this->forge->addField([
            'id'                       => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'spt_id'                   => ['type' => 'INT', 'unsigned' => true],
            'sdm_id'                   => ['type' => 'INT', 'unsigned' => true],
            'ada_hubungan_keluarga'    => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'ada_kepentingan_finansial'=> ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'ada_hubungan_sebelumnya'  => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_independent'           => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'catatan'                  => ['type' => 'TEXT', 'null' => true],
            'created_at'               => ['type' => 'DATETIME', 'null' => true],
            'updated_at'               => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['spt_id', 'sdm_id']);
        $this->forge->addForeignKey('spt_id', 'spt', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('spt_independensi');
    }

    public function down(): void
    {
        $this->forge->dropTable('spt_independensi', true);
        $this->forge->dropTable('spt_anggaran_waktu', true);
        $this->forge->dropTable('spt_km6', true);
        $this->forge->dropTable('spt_km4', true);
        $this->forge->dropTable('spt_km1', true);
    }
}
