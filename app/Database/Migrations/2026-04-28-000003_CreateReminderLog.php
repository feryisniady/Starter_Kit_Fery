<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

/**
 * Tabel jadwal pengingat TL yang sudah pre-kalkulasi.
 * Dibuat saat batas_waktu di-set → command tinggal kirim yang trigger_date <= hari ini.
 */
class CreateReminderLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'rekomendasi_id' => ['type' => 'INT', 'unsigned' => true],
            'trigger_date'   => ['type' => 'DATE', 'comment' => 'Tanggal kapan reminder harus dikirim'],
            'hari_trigger'   => ['type' => 'TINYINT', 'comment' => '7=H-7, 3=H-3, 1=H-1, 0=H-0, -1=terlambat'],
            'sent_inapp'     => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'sent_email'     => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'sent_wa'        => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'sent_at'        => ['type' => 'DATETIME', 'null' => true, 'comment' => 'Kapan terakhir diproses'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        // Unique per rekomendasi + hari trigger (tidak boleh duplikat schedule)
        $this->forge->addUniqueKey(['rekomendasi_id', 'hari_trigger']);
        $this->forge->addKey('trigger_date'); // Index untuk query harian
        $this->forge->addForeignKey('rekomendasi_id', 'rekomendasi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rekomendasi_reminder');
    }

    public function down()
    {
        $this->forge->dropTable('rekomendasi_reminder');
    }
}
