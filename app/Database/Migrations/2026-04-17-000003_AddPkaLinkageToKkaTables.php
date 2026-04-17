<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPkaLinkageToKkaTables extends Migration
{
    public function up()
    {
        // kka_ikhtisar: link ke prosedur PKA yang spesifik
        $this->forge->addColumn('kka_ikhtisar', [
            'pka_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'kka_id',
            ],
        ]);

        // kka_simpulan: tambah kode temuan (lookup) + nilai financial temuan
        $this->forge->addColumn('kka_simpulan', [
            'kode_temuan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'rekomendasi_awal',
            ],
            'nilai_financial' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'kode_temuan_id',
            ],
        ]);

        // kka_rekomendasi: tambah kode rekomendasi + nilai rekomendasi financial
        $this->forge->addColumn('kka_rekomendasi', [
            'kode_rekomendasi' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'tanggapan_auditi',
            ],
            'nilai_rekomendasi_financial' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'kode_rekomendasi',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('kka_ikhtisar',   'pka_id');
        $this->forge->dropColumn('kka_simpulan',   'kode_temuan_id');
        $this->forge->dropColumn('kka_simpulan',   'nilai_financial');
        $this->forge->dropColumn('kka_rekomendasi','kode_rekomendasi');
        $this->forge->dropColumn('kka_rekomendasi','nilai_rekomendasi_financial');
    }
}
