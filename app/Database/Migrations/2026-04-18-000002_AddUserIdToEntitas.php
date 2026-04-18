<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToEntitas extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('entitas', [
            'user_id' => [
                'type'    => 'INT',
                'null'    => true,
                'after'   => 'aktif',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('entitas', 'user_id');
    }
}
