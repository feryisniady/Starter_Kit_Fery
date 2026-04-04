<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\UserModel;

class ActivityLogController extends BaseController
{
    protected $logModel;

    public function __construct()
    {
        $this->logModel = new ActivityLogModel();
    }

    public function index()
    {
        $filters = [
            'module'    => $this->request->getGet('module'),
            'user_id'   => $this->request->getGet('user_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
        ];

        $logs    = $this->logModel->getLogsFiltered($filters);
        $modules = $this->logModel->distinct()->select('module')->where('module IS NOT NULL')->findAll();
        $users   = (new UserModel())->select('id, name')->orderBy('name')->findAll();
        $stats   = $this->logModel->getStats();

        return view('admin/activity_logs/index', [
            'title'   => 'Activity Log',
            'logs'    => $logs,
            'modules' => array_column($modules, 'module'),
            'users'   => $users,
            'filters' => $filters,
            'stats'   => $stats,
        ]);
    }

    public function export()
    {
        $filters = [
            'module'    => $this->request->getGet('module'),
            'user_id'   => $this->request->getGet('user_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
        ];

        $logs = $this->logModel->getLogsFiltered($filters);

        $filename = 'activity_log_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM UTF-8 agar Excel bisa baca karakter Indonesia
        fputs($out, "\xEF\xBB\xBF");
        fputcsv($out, ['#', 'Waktu', 'User', 'Aksi', 'Modul', 'Deskripsi', 'IP Address']);

        foreach ($logs as $i => $log) {
            fputcsv($out, [
                $i + 1,
                $log['created_at'],
                $log['user_name'] ?? 'Guest',
                $log['action'],
                $log['module'],
                $log['description'],
                $log['ip_address'],
            ]);
        }

        fclose($out);
        exit;
    }
}
