<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRealisasiToKkaIkhtisar extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('kka_ikhtisar', [
            'realisasi_waktu' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'hasil_observasi',
            ],
            'pelaksana_aktual_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'realisasi_waktu',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('kka_ikhtisar', 'realisasi_waktu');
        $this->forge->dropColumn('kka_ikhtisar', 'pelaksana_aktual_id');
    }
}
