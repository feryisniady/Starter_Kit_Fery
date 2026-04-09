<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPasswordTypeToAppSettings extends Migration
{
    public function up()
    {
        // Tambah 'password' ke ENUM type
        $this->db->query("ALTER TABLE app_settings MODIFY COLUMN `type` ENUM('text','textarea','image','boolean','password') NOT NULL DEFAULT 'text'");

        // Perbaiki baris yang type-nya kosong (akibat insert 'password' ke enum lama)
        $this->db->query("UPDATE app_settings SET `type` = 'password' WHERE `key` IN ('email_password', 'wa_token') AND (`type` = '' OR `type` = 'text')");
    }

    public function down()
    {
        // Kembalikan type ke 'text' sebelum hapus enum value
        $this->db->query("UPDATE app_settings SET `type` = 'text' WHERE `type` = 'password'");

        $this->db->query("ALTER TABLE app_settings MODIFY COLUMN `type` ENUM('text','textarea','image','boolean') NOT NULL DEFAULT 'text'");
    }
}
