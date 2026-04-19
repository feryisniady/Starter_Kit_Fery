<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNonPkptToSpt extends Migration
{
    public function up(): void
    {
        // 1. Buat pkpt_kegiatan_id nullable
        $this->forge->modifyColumn('spt', [
            'pkpt_kegiatan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
        ]);

        // 2. Tambah kolom Non-PKPT
        $this->forge->addColumn('spt', [
            'jenis_spt'      => [
                'type'       => 'ENUM',
                'constraint' => ['pkpt', 'non_pkpt'],
                'default'    => 'pkpt',
                'null'       => false,
                'after'      => 'pkpt_kegiatan_id',
            ],
            'irban_id'       => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'jenis_spt',
            ],
            'tahun'          => [
                'type'       => 'SMALLINT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'irban_id',
            ],
            'jenis_non_pkpt' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
                'default'    => null,
                'after'      => 'tahun',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('spt', ['jenis_spt', 'irban_id', 'tahun', 'jenis_non_pkpt']);
        $this->forge->modifyColumn('spt', [
            'pkpt_kegiatan_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
        ]);
    }
}
