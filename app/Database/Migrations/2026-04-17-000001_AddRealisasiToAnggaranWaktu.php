<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tambah kolom rencana_hari dan realisasi_hari per fase ke spt_anggaran_waktu.
 *
 * rencana_hari  = diisi KT/AT saat menyusun AW (KM-2) sebelum audit
 * realisasi_hari= diisi AT setelah fase selesai, diverifikasi KT
 */
class AddRealisasiToAnggaranWaktu extends Migration
{
    public function up(): void
    {
        $fields = [
            'persiapan_rencana_hari'    => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true, 'after' => 'persiapan_end'],
            'persiapan_realisasi_hari'  => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true, 'after' => 'persiapan_rencana_hari'],
            'pelaksanaan_rencana_hari'  => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true, 'after' => 'pelaksanaan_end'],
            'pelaksanaan_realisasi_hari'=> ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true, 'after' => 'pelaksanaan_rencana_hari'],
            'penyelesaian_rencana_hari' => ['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true, 'after' => 'penyelesaian_end'],
            'penyelesaian_realisasi_hari'=>['type' => 'DECIMAL', 'constraint' => '5,1', 'null' => true, 'after' => 'penyelesaian_rencana_hari'],
            'kt_verified'               => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'after' => 'penyelesaian_realisasi_hari'],
            'kt_verified_at'            => ['type' => 'DATETIME', 'null' => true, 'after' => 'kt_verified'],
            'kt_verified_by'            => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'kt_verified_at'],
        ];
        $this->forge->addColumn('spt_anggaran_waktu', $fields);
    }

    public function down(): void
    {
        $this->forge->dropColumn('spt_anggaran_waktu', [
            'persiapan_rencana_hari', 'persiapan_realisasi_hari',
            'pelaksanaan_rencana_hari', 'pelaksanaan_realisasi_hari',
            'penyelesaian_rencana_hari', 'penyelesaian_realisasi_hari',
            'kt_verified', 'kt_verified_at', 'kt_verified_by',
        ]);
    }
}
