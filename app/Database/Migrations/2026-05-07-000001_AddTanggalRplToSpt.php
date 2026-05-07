<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTanggalRplToSpt extends Migration
{
    public function up()
    {
        $this->forge->addColumn('spt', [
            'tanggal_rpl' => [
                'type'       => 'DATE',
                'null'       => true,
                'default'    => null,
                'after'      => 'tanggal_selesai',
                'comment'    => 'Rencana Penerbitan Laporan',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('spt', 'tanggal_rpl');
    }
}
