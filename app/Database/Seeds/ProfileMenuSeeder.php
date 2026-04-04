<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProfileMenuSeeder extends Seeder
{
    public function run()
    {
        $menuExists = $this->db->table('menus')->where('url', '/profile')->countAllResults();
        if (!$menuExists) {
            $maxSort = $this->db->table('menus')->selectMax('sort_order')->get()->getRowArray();
            $this->db->table('menus')->insert([
                'label'      => 'Profil Saya',
                'url'        => '/profile',
                'icon'       => 'fa-solid fa-circle-user',
                'permission' => null, // semua user yang login bisa akses
                'parent_id'  => null,
                'sort_order' => ($maxSort['sort_order'] ?? 0) + 1,
                'is_active'  => 1,
                'section'    => 'main',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        echo "ProfileMenuSeeder: menu Profil Saya berhasil ditambahkan.\n";
    }
}
