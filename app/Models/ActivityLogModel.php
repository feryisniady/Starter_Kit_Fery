<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table      = 'activity_logs';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id', 'user_name', 'action', 'module', 'description', 'meta', 'ip_address', 'user_agent',
    ];

    protected $useTimestamps  = true;
    protected $updatedField   = ''; // hanya created_at
    protected $createdField   = 'created_at';

    // Stats untuk summary cards
    public function getStats(): array
    {
        $today = date('Y-m-d');
        return [
            'total'         => $this->countAll(),
            'today'         => $this->where('DATE(created_at)', $today)->countAllResults(),
            'login_today'   => $this->where('action', 'login')->where('DATE(created_at)', $today)->countAllResults(),
            'failed_today'  => $this->like('action', 'login_failed')->where('DATE(created_at)', $today)->countAllResults(),
            'top_user'      => $this->select('user_name, COUNT(*) as total')
                                    ->where('user_name IS NOT NULL')
                                    ->groupBy('user_name')
                                    ->orderBy('total', 'DESC')
                                    ->first(),
        ];
    }

    // Statistik 7 hari terakhir untuk chart
    public function getLast7DaysStats(): array
    {
        $results = $this->select("DATE(created_at) as date, COUNT(*) as total")
            ->where('created_at >=', date('Y-m-d', strtotime('-6 days')) . ' 00:00:00')
            ->groupBy('DATE(created_at)')
            ->orderBy('date', 'ASC')
            ->findAll();

        $map = [];
        foreach ($results as $r) {
            $map[$r['date']] = (int) $r['total'];
        }

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date   = date('Y-m-d', strtotime("-$i days"));
            $label  = date('d M', strtotime($date));
            $data[] = ['date' => $date, 'label' => $label, 'total' => $map[$date] ?? 0];
        }
        return $data;
    }

    // Log terbaru (untuk recent activity di dashboard)
    public function getRecent(int $limit = 5): array
    {
        return $this->orderBy('created_at', 'DESC')->findAll($limit);
    }

    // Ambil log dengan filter — DataTables handle paginasi di sisi client
    public function getLogsFiltered(array $filters = []): array
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if (!empty($filters['module'])) {
            $builder->where('module', $filters['module']);
        }
        if (!empty($filters['user_id'])) {
            $builder->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['date_from'])) {
            $builder->where('created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $builder->where('created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        return $builder->findAll();
    }
}
