<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToPkptSetting extends Migration
{
    public function up()
    {
        $this->forge->addColumn('pkpt_setting', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'disetujui'],
                'default'    => 'draft',
                'after'      => 'nomor_pkpt',
            ],
            'approved_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'status',
            ],
            'approved_at' => [
                'type'  => 'DATETIME',
                'null'  => true,
                'after' => 'approved_by',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('pkpt_setting', ['status', 'approved_by', 'approved_at']);
    }
}
