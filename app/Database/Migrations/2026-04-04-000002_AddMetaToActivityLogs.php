<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMetaToActivityLogs extends Migration
{
    public function up()
    {
        $this->forge->addColumn('activity_logs', [
            'meta' => [
                'type'  => 'JSON',
                'null'  => true,
                'after' => 'description',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('activity_logs', 'meta');
    }
}
