<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Tambah field yang dibutuhkan untuk cetak Berita Acara Entry Meeting
 * ke tabel spt_km6 yang sudah ada.
 */
class AddBaFieldsToKm6 extends Migration
{
    public function up()
    {
        $this->forge->addColumn('spt_km6', [
            // Lokasi rapat
            'tempat' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
                'after'      => 'waktu_rapat',
            ],
            // Periode pelaksanaan audit (teks bebas, mis: "2 – 20 Juni 2026")
            'waktu_pelaksanaan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'waktu_sp',
            ],
            // Poin 5 bebas dalam kesepakatan
            'poin_5' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'rencana_laporan',
            ],
            // Kota untuk baris tanda tangan ("Sampang, ……")
            'nama_kota' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => 'Sampang',
                'after'      => 'poin_5',
            ],
            // Daftar anggota tim auditi (JSON: [{nama, jabatan},...])
            'tim_auditi_json' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'nip_auditi',
            ],
            // Perwakilan auditor yang menandatangani BA
            'nama_auditor_ttd' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'tlp_cp',
            ],
            'nip_auditor_ttd' => [
                'type'       => 'VARCHAR',
                'constraint' => 35,
                'null'       => true,
                'after'      => 'nama_auditor_ttd',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('spt_km6', [
            'tempat','waktu_pelaksanaan','poin_5','nama_kota',
            'tim_auditi_json','nama_auditor_ttd','nip_auditor_ttd',
        ]);
    }
}
