<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFieldsToSptKm1 extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('spt_km1', [
            'tingkat_risiko'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'default' => null, 'after' => 'no_kartu'],
            'laporan_kepada'      => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null, 'after' => 'tingkat_risiko'],
            'kunjungan_pm_1'      => ['type' => 'DATE', 'null' => true, 'default' => null, 'after' => 'rencana_kunjungan'],
            'kunjungan_pm_2'      => ['type' => 'DATE', 'null' => true, 'default' => null, 'after' => 'kunjungan_pm_1'],
            'kunjungan_pm_3'      => ['type' => 'DATE', 'null' => true, 'default' => null, 'after' => 'kunjungan_pm_2'],
            'kunjungan_pt_1'      => ['type' => 'DATE', 'null' => true, 'default' => null, 'after' => 'kunjungan_pm_3'],
            'kunjungan_pt_2'      => ['type' => 'DATE', 'null' => true, 'default' => null, 'after' => 'kunjungan_pt_1'],
            'kunjungan_pt_3'      => ['type' => 'DATE', 'null' => true, 'default' => null, 'after' => 'kunjungan_pt_2'],
            'rmp_bulan'           => ['type' => 'TINYINT', 'null' => true, 'default' => null, 'after' => 'kunjungan_pt_3'],
            'rpl_bulan'           => ['type' => 'TINYINT', 'null' => true, 'default' => null, 'after' => 'rmp_bulan'],
            'tanggal_konsep_laporan' => ['type' => 'DATE', 'null' => true, 'default' => null, 'after' => 'rpl_bulan'],
        ]);
    }

    public function down(): void
    {
        foreach ([
            'tingkat_risiko', 'laporan_kepada',
            'kunjungan_pm_1', 'kunjungan_pm_2', 'kunjungan_pm_3',
            'kunjungan_pt_1', 'kunjungan_pt_2', 'kunjungan_pt_3',
            'rmp_bulan', 'rpl_bulan', 'tanggal_konsep_laporan',
        ] as $col) {
            $this->forge->dropColumn('spt_km1', $col);
        }
    }
}
