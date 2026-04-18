<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPkaIdToKkaSimpulan extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('kka_simpulan', [
            'pka_id' => [
                'type'     => 'INT',
                'null'     => true,
                'after'    => 'kka_id',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('kka_simpulan', 'pka_id');
    }
}
