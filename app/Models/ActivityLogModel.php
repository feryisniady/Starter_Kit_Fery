<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table      = 'activity_logs';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'user_id', 'user_name', 'action', 'module', 'description', 'ip_address', 'user_agent',
    ];

    protected $useTimestamps  = true;
    protected $updatedField   = ''; // hanya created_at
    protected $createdField   = 'created_at';

    // Ambil log dengan filter — DataTables handle paginasi di sisi client
    public function getLogsFiltered(array $filters = []): array
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if (!empty($filters['module'])) {
            $builder->where('module', $filters['module']);
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
