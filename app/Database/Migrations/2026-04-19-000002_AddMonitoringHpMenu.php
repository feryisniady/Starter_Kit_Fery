<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMonitoringHpMenu extends Migration
{
    public function up(): void
    {
        $db     = \Config\Database::connect();
        $parent = $db->table('menus')->where('url', '#pkpt')->get()->getRowArray();
        if (!$parent) return;

        $maxSort = (int)($db->table('menus')
            ->where('parent_id', $parent['id'])
            ->selectMax('sort_order')
            ->get()->getRowArray()['sort_order'] ?? 0);

        $db->table('menus')->insert([
            'label'      => 'Monitoring HP SDM',
            'url'        => '/admin/pkpt/monitoring-sdm',
            'icon'       => 'fas fa-chart-bar',
            'permission' => 'pkpt.view',
            'parent_id'  => $parent['id'],
            'sort_order' => $maxSort + 1,
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down(): void
    {
        \Config\Database::connect()->table('menus')
            ->where('url', '/admin/pkpt/monitoring-sdm')
            ->delete();
    }
}
