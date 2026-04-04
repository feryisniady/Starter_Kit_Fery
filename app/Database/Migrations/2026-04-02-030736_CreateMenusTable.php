<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenusTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'            => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'label'         => ['type' => 'VARCHAR', 'constraint' => 100],
            'url'           => ['type' => 'VARCHAR', 'constraint' => 200],
            'icon'          => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'permission'    => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'parent_id'     => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'sort_order'    => ['type' => 'INT', 'default' => 0],
            'is_active'     => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at'    => ['type' => 'DATETIME', 'null' => true],
            'updated_at'    => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('menus');
    }

    public function down()
    {
        $this->forge->dropTable('menus');
    }
}