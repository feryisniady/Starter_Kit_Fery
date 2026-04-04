<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\MenuModel;
use App\Models\PermissionModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $activityModel    = new ActivityLogModel();
        $userModel        = new UserModel();
        $roleModel        = new RoleModel();
        $menuModel        = new MenuModel();
        $permissionModel  = new PermissionModel();

        $actStats = $activityModel->getStats();

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
        ]);
    }
}
