<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // -------------------------------------------------------------------------
        // 1. Default settings
        // -------------------------------------------------------------------------
        $defaults = [
            // General
            ['key' => 'app_name',    'value' => 'RBAC Starter Kit',                   'type' => 'text',   'group' => 'general',    'label' => 'Nama Aplikasi',          'description' => 'Nama lengkap aplikasi',                   'sort_order' => 1],
            ['key' => 'app_tagline', 'value' => 'Sistem Informasi berbasis CodeIgniter 4', 'type' => 'text', 'group' => 'general', 'label' => 'Tagline / Deskripsi',    'description' => 'Kalimat singkat deskripsi aplikasi',       'sort_order' => 2],
            ['key' => 'app_version', 'value' => '1.0',                                'type' => 'text',   'group' => 'general',    'label' => 'Versi Aplikasi',         'description' => 'Nomor versi yang ditampilkan di footer',   'sort_order' => 3],
            ['key' => 'org_name',    'value' => 'Nama Instansi',                       'type' => 'text',   'group' => 'general',    'label' => 'Nama Organisasi',        'description' => 'Nama lengkap instansi/perusahaan (misal: PEMERINTAH KABUPATEN ...)', 'sort_order' => 4],
            ['key' => 'org_unit',    'value' => 'Nama Unit Kerja',                     'type' => 'text',   'group' => 'general',    'label' => 'Nama Unit Kerja',        'description' => 'Nama unit/satker untuk kop surat (misal: INSPEKTORAT DAERAH)',        'sort_order' => 5],
            ['key' => 'org_short',   'value' => 'Instansi',                            'type' => 'text',   'group' => 'general',    'label' => 'Nama Singkat Organisasi','description' => 'Nama singkat untuk sidebar & header',      'sort_order' => 6],
            ['key' => 'org_website', 'value' => '#',                                   'type' => 'text',   'group' => 'general',    'label' => 'Website Organisasi',     'description' => 'URL website resmi organisasi',             'sort_order' => 7],
            ['key' => 'org_address', 'value' => 'Jl. ... No. ...',                     'type' => 'text',   'group' => 'general',    'label' => 'Alamat Organisasi',      'description' => 'Alamat lengkap termasuk Telp/Fax untuk kop surat',                    'sort_order' => 8],
            ['key' => 'org_email',   'value' => '',                                    'type' => 'text',   'group' => 'general',    'label' => 'Email Organisasi',       'description' => 'Email resmi instansi untuk kop surat',     'sort_order' => 9],
            ['key' => 'org_city',    'value' => '',                                    'type' => 'text',   'group' => 'general',    'label' => 'Kota Kedudukan',         'description' => 'Kota tempat kedudukan (untuk "Ditetapkan di ...")',                    'sort_order' => 10],

            // Appearance
            ['key' => 'logo_path',    'value' => null, 'type' => 'image', 'group' => 'appearance', 'label' => 'Logo Aplikasi',    'description' => 'Logo yang muncul di sidebar (maks 2MB, PNG/JPG)', 'sort_order' => 1],
            ['key' => 'favicon_path', 'value' => null, 'type' => 'image', 'group' => 'appearance', 'label' => 'Favicon',          'description' => 'Icon tab browser (maks 2MB, ICO/PNG)',             'sort_order' => 2],

            // Login Page
            ['key' => 'login_title',    'value' => null, 'type' => 'text',  'group' => 'login', 'label' => 'Judul Halaman Login',  'description' => 'Baris pertama logo login (default: nama org)',     'sort_order' => 1],
            ['key' => 'login_subtitle', 'value' => null, 'type' => 'text',  'group' => 'login', 'label' => 'Sub-judul Login',      'description' => 'Baris kedua logo login (default: nama singkat org)', 'sort_order' => 2],
            ['key' => 'login_tagline',  'value' => null, 'type' => 'text',  'group' => 'login', 'label' => 'Tagline Login',        'description' => 'Judul besar kiri & kanan halaman login',           'sort_order' => 3],
            ['key' => 'login_desc',     'value' => null, 'type' => 'textarea', 'group' => 'login', 'label' => 'Deskripsi Panel Kanan', 'description' => 'Paragraf deskripsi di panel kanan login',       'sort_order' => 4],
            ['key' => 'login_bg_path',  'value' => null, 'type' => 'image', 'group' => 'login', 'label' => 'Background Login',    'description' => 'Gambar latar panel kanan login (maks 2MB)',        'sort_order' => 5],

            // Footer
            ['key' => 'footer_text', 'value' => null, 'type' => 'textarea', 'group' => 'footer', 'label' => 'Teks Footer',       'description' => 'Teks kustom footer (kosongkan untuk default otomatis)', 'sort_order' => 1],
        ];

        foreach ($defaults as $setting) {
            $exists = $this->db->table('app_settings')->where('key', $setting['key'])->countAllResults();
            if (!$exists) {
                $this->db->table('app_settings')->insert(array_merge($setting, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        // -------------------------------------------------------------------------
        // 2. Permission setting.manage
        // -------------------------------------------------------------------------
        $exists = $this->db->table('permissions')->where('name', 'setting.manage')->countAllResults();
        if (!$exists) {
            $this->db->table('permissions')->insert([
                'name'       => 'setting.manage',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permission = $this->db->table('permissions')->where('name', 'setting.manage')->get()->getRowArray();
        if (!$permission) return;

        // Assign ke superadmin saja
        $role = $this->db->table('roles')->where('name', 'superadmin')->get()->getRowArray();
        if ($role) {
            $already = $this->db->table('role_permissions')
                ->where('role_id', $role['id'])
                ->where('permission_id', $permission['id'])
                ->countAllResults();

            if (!$already) {
                $this->db->table('role_permissions')->insert([
                    'role_id'       => $role['id'],
                    'permission_id' => $permission['id'],
                ]);
            }
        }

        // -------------------------------------------------------------------------
        // 3. Menu "Pengaturan"
        // -------------------------------------------------------------------------
        $menuExists = $this->db->table('menus')->where('url', '/admin/settings')->countAllResults();
        if (!$menuExists) {
            $maxSort = $this->db->table('menus')->selectMax('sort_order')->get()->getRowArray();
            $this->db->table('menus')->insert([
                'label'      => 'Pengaturan',
                'url'        => '/admin/settings',
                'icon'       => 'fa-solid fa-gear',
                'permission' => 'setting.manage',
                'parent_id'  => null,
                'sort_order' => ($maxSort['sort_order'] ?? 0) + 1,
                'is_active'  => 1,
                'section'    => 'main',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        echo "AppSettingSeeder: pengaturan default, permission & menu berhasil ditambahkan.\n";
    }
}
