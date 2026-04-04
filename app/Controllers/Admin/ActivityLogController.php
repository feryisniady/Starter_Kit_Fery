<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;

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
            'action'    => $this->request->getGet('action'),
            'user_id'   => $this->request->getGet('user_id'),
            'date_from' => $this->request->getGet('date_from'),
            'date_to'   => $this->request->getGet('date_to'),
        ];

        $result = $this->logModel->getLogsFiltered($filters, 20);

        // Daftar modul unik untuk filter dropdown
        $modules = $this->logModel->distinct()->select('module')->where('module IS NOT NULL')->findAll();

        return view('admin/activity_logs/index', [
            'title'   => 'Activity Log',
            'logs'    => $result['logs'],
            'pager'   => $result['pager'],
            'modules' => array_column($modules, 'module'),
            'filters' => $filters,
        ]);
    }
}
