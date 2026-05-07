<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Routing Slip — lembar pengantar review dokumen/KKP per penugasan.
 * Alur: draft → kt_review → dalnis_review → pj_review → selesai
 *       atau dikembalikan di tahap mana saja.
 */
class CreateSptRoutingSlip extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'spt_id' => [
                'type'     => 'INT',
                'unsigned' => true,
            ],
            // Dokumen yang dikirimkan (judul bebas)
            'judul' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            // Jenis dokumen (kka/program/laporan/lainnya) untuk filter/ikon
            'jenis_dokumen' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'keterangan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // Pengirim (SDM yang membuat routing slip)
            'pengirim_sdm_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'tanggal_kirim' => [
                'type' => 'DATETIME',
                'null' => true,
            ],

            // ── KT Review ───────────────────────────────────────────────
            'kt_sdm_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'kt_tanggal' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'kt_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'diterima', 'dikembalikan'],
                'default'    => 'pending',
            ],
            'kt_catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // ── DALNIS Review ────────────────────────────────────────────
            'dalnis_sdm_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'dalnis_tanggal' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'dalnis_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'diterima', 'dikembalikan'],
                'default'    => 'pending',
            ],
            'dalnis_catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // ── PJ / DALTU Review ────────────────────────────────────────
            'pj_sdm_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'pj_tanggal' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'pj_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'diterima', 'dikembalikan'],
                'default'    => 'pending',
            ],
            'pj_catatan' => [
                'type' => 'TEXT',
                'null' => true,
            ],

            // Status keseluruhan alur
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'kt_review', 'dalnis_review', 'pj_review', 'selesai', 'dikembalikan'],
                'default'    => 'draft',
            ],

            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('spt_id');
        $this->forge->addKey('status');
        $this->forge->createTable('spt_routing_slip');
    }

    public function down()
    {
        $this->forge->dropTable('spt_routing_slip', true);
    }
}
