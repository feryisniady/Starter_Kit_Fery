<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\MenuModel;
use App\Models\PermissionModel;
use App\Models\SptModel;
use App\Models\KkaModel;
use App\Models\NhpModel;

class Dashboard extends BaseController
{
    public function index()
    {
        // Auditi users get their own portal dashboard
        if (hasRole('auditi')) {
            return redirect()->to('/auditi/dashboard');
        }

        $activityModel    = new ActivityLogModel();
        $userModel        = new UserModel();
        $roleModel        = new RoleModel();
        $menuModel        = new MenuModel();
        $permissionModel  = new PermissionModel();

        $actStats = $activityModel->getStats();

        // Audit KPI — hanya dimuat jika user punya akses audit
        $auditKpi = null;
        if (hasPermission('spt.view')) {
            $sptModel  = new SptModel();
            $kkaModel  = new KkaModel();
            $nhpModel  = new NhpModel();
            $tahun     = (int) date('Y');

            $auditKpi = [
                'spt'            => $sptModel->getDashboardStats($tahun),
                'kka_pending_kt' => $kkaModel->getPendingKtReviewCount(),
                'nhp_pending'    => $nhpModel->getPendingTanggapanCount(),
                'tl_overdue'     => $nhpModel->getOverdueTlCount(),
                'tahun'          => $tahun,
            ];
        }

        return view('dashboard', [
            'title'       => 'Dashboard',
            'totalUsers'  => $userModel->countAll(),
            'totalRoles'  => $roleModel->countAll(),
            'totalMenus'  => $menuModel->countAll(),
            'totalPerms'  => $permissionModel->countAll(),
            'actToday'    => $actStats['today'],
            'loginToday'  => $actStats['login_today'],
            'failToday'   => $actStats['failed_today'],
            'totalAct'    => $actStats['total'],
            'chart7days'  => $activityModel->getLast7DaysStats(),
            'recentLogs'  => $activityModel->getRecent(8),
            'auditKpi'    => $auditKpi,
        ]);
    }
}
