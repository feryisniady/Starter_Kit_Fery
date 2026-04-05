<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogModel;
use App\Models\UserModel;
use App\Traits\DatatableTrait;

class ActivityLogController extends BaseController
{
    use DatatableTrait;

    protected $logModel;

    public function __construct()
    {
        $this->logModel = new ActivityLogModel();
    }

    public function index()
    {
        $modules = $this->logModel->distinct()->select('module')->where('module IS NOT NULL')->findAll();
        $users   = (new UserModel())->select('id, name')->orderBy('name')->findAll();
        $stats   = $this->logModel->getStats();

        return view('admin/activity_logs/index', [
            'title'   => 'Activity Log',
            'modules' => array_column($modules, 'module'),
            'users'   => $users,
            'stats'   => $stats,
        ]);
    }

    // AJAX: DataTables server-side (dengan filter tambahan dari form)
    public function getData()
    {
        if (!$this->request->isAJAX()) return $this->response->setStatusCode(403);

        ['draw'=>$draw,'start'=>$start,'length'=>$length,'search'=>$search,'order'=>$order] = $this->dtRequest();

        // Filter tambahan dari form
        $module   = $this->request->getPost('filter_module')   ?? '';
        $userId   = $this->request->getPost('filter_user_id')  ?? '';
        $dateFrom = $this->request->getPost('filter_date_from') ?? '';
        $dateTo   = $this->request->getPost('filter_date_to')   ?? '';

        $db = \Config\Database::connect();

        // Base builder helper
        $applyFilters = function($q) use ($module, $userId, $dateFrom, $dateTo) {
            if ($module)   $q->where('module', $module);
            if ($userId)   $q->where('user_id', $userId);
            if ($dateFrom) $q->where('created_at >=', $dateFrom.' 00:00:00');
            if ($dateTo)   $q->where('created_at <=', $dateTo.' 23:59:59');
            return $q;
        };

        $total = $db->table('activity_logs')->countAllResults();

        $countQ = $applyFilters($db->table('activity_logs'));
        if ($search) $countQ->groupStart()->like('action', $search)->orLike('description', $search)->orLike('user_name', $search)->groupEnd();
        $filtered = $countQ->countAllResults();

        $dataQ = $applyFilters($db->table('activity_logs al')->select('al.*'));
        if ($search) $dataQ->groupStart()->like('action', $search)->orLike('description', $search)->orLike('user_name', $search)->groupEnd();

        [$ordCol, $ordDir] = $this->dtOrder($order, [0=>'al.id',1=>'al.created_at',2=>'al.user_name',3=>'al.action'], 'al.created_at');
        $dataQ->orderBy($ordCol, $ordDir === 'ASC' ? 'ASC' : 'DESC');
        if ($length > 0) $dataQ->limit($length, $start);

        $rows = $dataQ->get()->getResultArray();
        $data = [];
        foreach ($rows as $i => $row) {
            $badgeColor = match(true) {
                str_starts_with($row['action'], 'login_failed'),
                str_starts_with($row['action'], 'login_lockout'),
                str_starts_with($row['action'], 'login_blocked') => 'danger',
                str_starts_with($row['action'], 'login')         => 'success',
                str_starts_with($row['action'], 'logout')        => 'secondary',
                str_contains($row['action'], '.delete')          => 'danger',
                str_contains($row['action'], '.create')          => 'success',
                str_contains($row['action'], '.update')          => 'info',
                default                                          => 'primary',
            };
            $waktu   = '<div>'.date('d/m/Y', strtotime($row['created_at'])).'</div><div style="color:#94a3b8;font-size:12px">'.date('H:i:s', strtotime($row['created_at'])).'</div>';
            $user    = $row['user_name'] ? '<span style="font-weight:600">'.esc($row['user_name']).'</span>' : '<span class="text-muted">Guest</span>';
            $action  = '<span class="badge badge-'.$badgeColor.'">'.esc($row['action']).'</span>';
            $meta    = '';
            if (!empty($row['meta'])) {
                $meta = '<button class="btn btn-sm btn-secondary btn-detail" data-meta="'.htmlspecialchars($row['meta'], ENT_QUOTES, 'UTF-8').'" title="Lihat detail"><i class="fas fa-eye"></i></button>';
            } else {
                $meta = '<span class="text-muted" style="font-size:12px">—</span>';
            }
            $data[] = [$start+$i+1, $waktu, $user, $action, esc($row['description']), $meta, '<span style="font-family:monospace;font-size:12px">'.esc($row['ip_address']).'</span>'];
        }

        return $this->dtResponse($draw, $total, $filtered, $data);
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
