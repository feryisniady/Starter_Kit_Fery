<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBuktiTlToNhpItem extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('nhp_item', [
            'bukti_tl' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'tgl_tanggapan',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('nhp_item', 'bukti_tl');
    }
}
