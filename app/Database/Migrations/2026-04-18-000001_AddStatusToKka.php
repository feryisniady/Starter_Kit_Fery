<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToKka extends Migration
{
    public function up(): void
    {
        // Kolom status KKA: draft → submitted → approved / rejected
        $this->forge->addColumn('kka', [
            'status_kka' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'submitted', 'approved', 'rejected'],
                'default'    => 'draft',
                'after'      => 'status',
            ],
            'submitted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'status_kka',
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'submitted_at',
            ],
            'catatan_review' => [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'reviewed_at',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('kka', ['status_kka', 'submitted_at', 'reviewed_at', 'catatan_review']);
    }
}
