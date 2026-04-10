<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Seed menu navigasi untuk modul PKPT, SPT, dan Master Data pengawasan.
 * php spark db:seed PkptMenuSeeder
 *
 * Struktur:
 *  ▸ Master Data  (parent, url=#)
 *     ├── Irban      /admin/master/irban
 *     ├── Entitas    /admin/master/entitas
 *     └── SDM        /admin/master/sdm
 *  ▸ PKPT  (parent, url=/admin/pkpt)
 *     ├── Setting PKPT  /admin/pkpt/setting
 *     └── Daftar PKPT   /admin/pkpt
 *  ▸ SPT  (single item) /admin/spt
 */
class PkptMenuSeeder extends Seeder
{
    public function run()
    {
        // Ambil sort_order terbesar saat ini
        $maxSort = (int) ($this->db->table('menus')->selectMax('sort_order')->get()->getRowArray()['sort_order'] ?? 0);
        $sort    = $maxSort + 10; // mulai dari (max + 10) agar ada ruang

        // ─── 1. Parent: Master Data ─────────────────────────────────────────
        $parentMasterId = $this->insertMenuIfNotExists([
            'label'      => 'Master Data',
            'url'        => '#',
            'icon'       => 'fas fa-database',
            'permission' => 'master.view',
            'parent_id'  => null,
            'sort_order' => $sort,
            'section'    => 'pengawasan',
        ]);

        // Children Master Data
        $this->insertMenuIfNotExists([
            'label'      => 'Irban',
            'url'        => '/admin/master/irban',
            'icon'       => 'fas fa-sitemap',
            'permission' => 'master.manage',
            'parent_id'  => $parentMasterId,
            'sort_order' => $sort + 1,
            'section'    => 'pengawasan',
        ]);
        $this->insertMenuIfNotExists([
            'label'      => 'Entitas / OPD',
            'url'        => '/admin/master/entitas',
            'icon'       => 'fas fa-building',
            'permission' => 'master.manage',
            'parent_id'  => $parentMasterId,
            'sort_order' => $sort + 2,
            'section'    => 'pengawasan',
        ]);
        $this->insertMenuIfNotExists([
            'label'      => 'SDM Pemeriksa',
            'url'        => '/admin/master/sdm',
            'icon'       => 'fas fa-user-tie',
            'permission' => 'master.manage',
            'parent_id'  => $parentMasterId,
            'sort_order' => $sort + 3,
            'section'    => 'pengawasan',
        ]);

        // ─── 2. Parent: PKPT ────────────────────────────────────────────────
        $sort += 20;
        $parentPkptId = $this->insertMenuIfNotExists([
            'label'      => 'PKPT',
            'url'        => '#',
            'icon'       => 'fas fa-clipboard-list',
            'permission' => 'pkpt.view',
            'parent_id'  => null,
            'sort_order' => $sort,
            'section'    => 'pengawasan',
        ]);

        $this->insertMenuIfNotExists([
            'label'      => 'Setting PKPT',
            'url'        => '/admin/pkpt/setting',
            'icon'       => 'fas fa-sliders',
            'permission' => 'pkpt.manage',
            'parent_id'  => $parentPkptId,
            'sort_order' => $sort + 1,
            'section'    => 'pengawasan',
        ]);
        $this->insertMenuIfNotExists([
            'label'      => 'Daftar PKPT',
            'url'        => '/admin/pkpt',
            'icon'       => 'fas fa-list-check',
            'permission' => 'pkpt.view',
            'parent_id'  => $parentPkptId,
            'sort_order' => $sort + 2,
            'section'    => 'pengawasan',
        ]);

        // ─── 3. SPT (single menu) ───────────────────────────────────────────
        $sort += 20;
        $this->insertMenuIfNotExists([
            'label'      => 'SPT',
            'url'        => '/admin/spt',
            'icon'       => 'fas fa-file-signature',
            'permission' => 'spt.view',
            'parent_id'  => null,
            'sort_order' => $sort,
            'section'    => 'pengawasan',
        ]);

        echo "PkptMenuSeeder: menu Master Data, PKPT, dan SPT berhasil ditambahkan.\n";
    }

    /**
     * Insert menu hanya jika URL belum ada. Return ID menu (baru atau existing).
     */
    private function insertMenuIfNotExists(array $data): int
    {
        $existing = $this->db->table('menus')->where('url', $data['url'])->get()->getRowArray();
        if ($existing) {
            echo "  Menu '{$data['label']}' sudah ada (ID: {$existing['id']}), dilewati.\n";
            return (int) $existing['id'];
        }

        $this->db->table('menus')->insert([
            'label'      => $data['label'],
            'url'        => $data['url'],
            'icon'       => $data['icon'],
            'permission' => $data['permission'],
            'parent_id'  => $data['parent_id'],
            'sort_order' => $data['sort_order'],
            'is_active'  => 1,
            'section'    => $data['section'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $id = $this->db->insertID();
        echo "  Menu '{$data['label']}' ditambahkan (ID: {$id}).\n";
        return (int) $id;
    }
}
