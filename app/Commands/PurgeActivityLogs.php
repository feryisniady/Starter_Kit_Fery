<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ActivityLogModel;

class PurgeActivityLogs extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'activitylog:purge';
    protected $description = 'Hapus activity log yang lebih dari N hari (default: 90)';
    protected $usage       = 'activitylog:purge [days]';

    public function run(array $params)
    {
        $days  = (int) ($params[0] ?? 90);
        $model = new ActivityLogModel();

        $cutoff  = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $deleted = $model->where('created_at <', $cutoff)->delete();

        $count = $model->db->affectedRows();
        CLI::write("Purge selesai: {$count} log dihapus (lebih dari {$days} hari).", 'green');
    }
}
