<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddNhpItemIdToTemuan extends Migration
{
    public function up()
    {
        // Link temuan ke nhp_item sumber (untuk idempotent check & traceability)
        $this->forge->addColumn('temuan', [
            'nhp_item_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'after'      => 'id',
                'comment'    => 'Sumber nhp_item jika dibuat otomatis dari NHP',
            ],
        ]);

        // Unique: satu nhp_item hanya bisa generate satu temuan
        $this->db->query('ALTER TABLE temuan ADD UNIQUE KEY uq_temuan_nhp_item (nhp_item_id)');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE temuan DROP INDEX uq_temuan_nhp_item');
        $this->forge->dropColumn('temuan', 'nhp_item_id');
    }
}
