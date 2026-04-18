<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNamaTImToSpt extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('spt', [
            'nama_tim' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'pkpt_kegiatan_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('spt', 'nama_tim');
    }
}
