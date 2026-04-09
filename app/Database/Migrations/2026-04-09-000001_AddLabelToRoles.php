<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLabelToRoles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('roles', [
            'label' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'name',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('roles', 'label');
    }
}
