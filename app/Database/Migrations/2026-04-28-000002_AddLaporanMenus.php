<?php
namespace App\Database\Migrations;
use CodeIgniter\Database\Migration;

class AddLaporanMenus extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        // Parent: Laporan (dropdown group, sort 400 — setelah SPT di 300)
        $this->db->table('menus')->insert([
            'label'      => 'Laporan',
            'url'        => '#laporan',
            'icon'       => 'fas fa-chart-line',
            'permission' => 'spt.view',
            'parent_id'  => null,
            'sort_order' => 400,
            'section'    => 'pengawasan',
            'is_active'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $parentId = $this->db->insertID();

        // Child menus
        $children = [
            [
                'label'      => 'Ikhtisar LHP',
                'url'        => '/admin/laporan/ikhtisar-lhp',
                'icon'       => 'fas fa-table-list',
                'permission' => 'spt.view',
                'sort_order' => 401,
                'section'    => 'pengawasan',
            ],
            [
                'label'      => 'Rekap SPT',
                'url'        => '/admin/laporan/rekap-spt',
                'icon'       => 'fas fa-file-signature',
                'permission' => 'spt.view',
                'sort_order' => 402,
                'section'    => 'pengawasan',
            ],
            [
                'label'      => 'Rekap Tindak Lanjut',
                'url'        => '/admin/laporan/rekap-tl',
                'icon'       => 'fas fa-list-check',
                'permission' => 'spt.view',
                'sort_order' => 403,
                'section'    => 'pengawasan',
            ],
        ];

        foreach ($children as $child) {
            $this->db->table('menus')->insert(array_merge($child, [
                'parent_id'  => $parentId,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down()
    {
        // Hapus berdasarkan URL yang spesifik agar aman
        $this->db->table('menus')->whereIn('url', [
            '#laporan',
            '/admin/laporan/ikhtisar-lhp',
            '/admin/laporan/rekap-spt',
            '/admin/laporan/rekap-tl',
        ])->delete();
    }
}
