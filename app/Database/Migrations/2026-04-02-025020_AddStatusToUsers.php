<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
                'after'      => 'password',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'status');
    }
}