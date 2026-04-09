<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailWaSettings extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');
        $db  = \Config\Database::connect();

        $settings = [
            // ── EMAIL ────────────────────────────────────────────
            ['key' => 'email_driver',       'value' => 'smtp',  'type' => 'text',     'group' => 'email',     'label' => 'Driver',             'description' => 'smtp atau sendmail',                    'sort_order' => 1],
            ['key' => 'email_host',         'value' => null,    'type' => 'text',     'group' => 'email',     'label' => 'SMTP Host',          'description' => 'Contoh: smtp.gmail.com',                'sort_order' => 2],
            ['key' => 'email_port',         'value' => '587',   'type' => 'text',     'group' => 'email',     'label' => 'SMTP Port',          'description' => '587 (TLS) atau 465 (SSL)',              'sort_order' => 3],
            ['key' => 'email_encryption',   'value' => 'tls',   'type' => 'text',     'group' => 'email',     'label' => 'Enkripsi',           'description' => 'tls / ssl / none',                      'sort_order' => 4],
            ['key' => 'email_username',     'value' => null,    'type' => 'text',     'group' => 'email',     'label' => 'Username / Email',   'description' => 'Email akun pengirim',                   'sort_order' => 5],
            ['key' => 'email_password',     'value' => null,    'type' => 'password', 'group' => 'email',     'label' => 'Password / App Key', 'description' => 'Password atau App Password Gmail/Outlook', 'sort_order' => 6],
            ['key' => 'email_from_address', 'value' => null,    'type' => 'text',     'group' => 'email',     'label' => 'Alamat Pengirim',    'description' => 'Alamat email yang tampil di inbox penerima', 'sort_order' => 7],
            ['key' => 'email_from_name',    'value' => null,    'type' => 'text',     'group' => 'email',     'label' => 'Nama Pengirim',      'description' => 'Nama yang tampil di inbox penerima',    'sort_order' => 8],

            // ── WHATSAPP ─────────────────────────────────────────
            ['key' => 'wa_active',    'value' => '0',   'type' => 'boolean',  'group' => 'whatsapp', 'label' => 'Aktifkan WhatsApp',  'description' => 'Aktifkan pengiriman pesan via WhatsApp',  'sort_order' => 1],
            ['key' => 'wa_provider',  'value' => 'fonnte', 'type' => 'text',  'group' => 'whatsapp', 'label' => 'Provider',           'description' => 'fonnte / custom',                         'sort_order' => 2],
            ['key' => 'wa_api_url',   'value' => 'https://api.fonnte.com/send', 'type' => 'text', 'group' => 'whatsapp', 'label' => 'API URL',  'description' => 'Endpoint API gateway WhatsApp',       'sort_order' => 3],
            ['key' => 'wa_token',     'value' => null,  'type' => 'password', 'group' => 'whatsapp', 'label' => 'Token / API Key',    'description' => 'Token otentikasi dari provider',          'sort_order' => 4],
            ['key' => 'wa_sender',    'value' => null,  'type' => 'text',     'group' => 'whatsapp', 'label' => 'Nomor Pengirim',     'description' => 'Format: 6281234567890 (tanpa +)',          'sort_order' => 5],
        ];

        foreach ($settings as $s) {
            $exists = $db->table('app_settings')->where('key', $s['key'])->countAllResults();
            if (!$exists) {
                $db->table('app_settings')->insert(array_merge($s, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }
    }

    public function down()
    {
        $keys = [
            'email_driver','email_host','email_port','email_encryption',
            'email_username','email_password','email_from_address','email_from_name',
            'wa_active','wa_provider','wa_api_url','wa_token','wa_sender',
        ];
        \Config\Database::connect()->table('app_settings')->whereIn('key', $keys)->delete();
    }
}
