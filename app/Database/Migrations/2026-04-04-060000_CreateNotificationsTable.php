<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Target user penerima notifikasi',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['info', 'success', 'warning', 'danger'],
                'default'    => 'info',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Link tujuan saat notif diklik',
            ],
            'is_read' => [
                'type'    => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('is_read');
        $this->forge->createTable('notifications');
    }

    public function down(): void
    {
        $this->forge->dropTable('notifications', true);
    }
}
