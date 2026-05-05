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
        $auditKpi          = null;
        $sptPerBulan       = [];
        $tlProgress        = [];
        $kpiIrban          = [];
        $topEntitasOverdue = [];

        if (hasPermission('spt.view')) {
            $sptModel = new SptModel();
            $kkaModel = new KkaModel();
            $nhpModel = new NhpModel();
            $tahun    = (int) date('Y');
            $db       = \Config\Database::connect();

            $auditKpi = [
                'spt'            => $sptModel->getDashboardStats($tahun),
                'kka_pending_kt' => $kkaModel->getPendingKtReviewCount(),
                'nhp_pending'    => $nhpModel->getPendingTanggapanCount(),
                'tl_overdue'     => $nhpModel->getOverdueTlCount(),
                'tahun'          => $tahun,
            ];

            // ── SPT per bulan (12 bulan tahun berjalan) ──────────────────
            $bulanData = array_fill(0, 12, 0);
            foreach ($db->query(
                "SELECT MONTH(created_at) AS b, COUNT(*) AS n FROM spt WHERE YEAR(created_at) = ? GROUP BY b",
                [$tahun]
            )->getResultArray() as $r) {
                $bulanData[(int)$r['b'] - 1] = (int)$r['n'];
            }
            $sptPerBulan = $bulanData;

            // ── Progress TL tahun berjalan ────────────────────────────────
            $tlProgress = $db->query("
                SELECT
                    COALESCE(SUM(r.status = 'selesai'), 0) AS selesai,
                    COALESCE(SUM(r.status = 'proses'),  0) AS proses,
                    COALESCE(SUM(r.status = 'belum'),   0) AS belum
                FROM rekomendasi r
                JOIN temuan t  ON t.id  = r.temuan_id
                JOIN spt    s  ON s.id  = t.spt_id
                WHERE YEAR(s.created_at) = ?
            ", [$tahun])->getRowArray() ?? ['selesai' => 0, 'proses' => 0, 'belum' => 0];

            // ── KPI per Irban ─────────────────────────────────────────────
            $kpiIrban = $db->query("
                SELECT
                    i.nama                                                              AS irban_nama,
                    COUNT(DISTINCT sp.id)                                               AS total_spt,
                    COUNT(DISTINCT tm.id)                                               AS total_temuan,
                    COALESCE(SUM(r.status = 'selesai'), 0)                             AS tl_selesai,
                    COALESCE(SUM(r.status = 'proses'),  0)                             AS tl_proses,
                    COALESCE(SUM(r.status = 'belum'),   0)                             AS tl_belum,
                    COALESCE(SUM(r.batas_waktu IS NOT NULL
                                 AND r.batas_waktu < CURDATE()
                                 AND r.status != 'selesai'), 0)                        AS tl_overdue
                FROM irban i
                LEFT JOIN sdm sdm      ON sdm.irban_id = i.id
                LEFT JOIN spt_tim st   ON st.sdm_id = sdm.id AND st.peran_spt = 'Ketua Tim'
                LEFT JOIN spt sp       ON sp.id = st.spt_id AND YEAR(sp.created_at) = {$tahun}
                LEFT JOIN temuan tm    ON tm.spt_id = sp.id
                LEFT JOIN rekomendasi r ON r.temuan_id = tm.id
                GROUP BY i.id, i.nama
                ORDER BY i.nama
            ")->getResultArray();

            // ── Top 5 entitas dengan TL overdue ──────────────────────────
            $topEntitasOverdue = $db->query("
                SELECT
                    e.nama                                                              AS entitas_nama,
                    COUNT(DISTINCT r.id)                                                AS total_rek,
                    COALESCE(SUM(r.status != 'selesai'), 0)                            AS tl_belum,
                    COALESCE(SUM(r.batas_waktu IS NOT NULL
                                 AND r.batas_waktu < CURDATE()
                                 AND r.status != 'selesai'), 0)                        AS tl_overdue
                FROM entitas e
                JOIN (
                    SELECT pe.entitas_id, sp.id AS spt_id
                    FROM pkpt_entitas pe
                    JOIN spt sp ON sp.pkpt_kegiatan_id = pe.pkpt_kegiatan_id
                    WHERE YEAR(sp.created_at) = {$tahun}
                    UNION
                    SELECT sp.entitas_id, sp.id FROM spt sp
                    WHERE sp.entitas_id IS NOT NULL AND YEAR(sp.created_at) = {$tahun}
                ) spt_e ON spt_e.entitas_id = e.id
                JOIN temuan tm      ON tm.spt_id = spt_e.spt_id
                JOIN rekomendasi r  ON r.temuan_id = tm.id
                GROUP BY e.id, e.nama
                HAVING tl_overdue > 0
                ORDER BY tl_overdue DESC
                LIMIT 5
            ")->getResultArray();
        }

        return view('dashboard', [
            'title'              => 'Dashboard',
            'totalUsers'         => $userModel->countAll(),
            'totalRoles'         => $roleModel->countAll(),
            'totalMenus'         => $menuModel->countAll(),
            'totalPerms'         => $permissionModel->countAll(),
            'actToday'           => $actStats['today'],
            'loginToday'         => $actStats['login_today'],
            'failToday'          => $actStats['failed_today'],
            'totalAct'           => $actStats['total'],
            'chart7days'         => $activityModel->getLast7DaysStats(),
            'recentLogs'         => $activityModel->getRecent(8),
            'auditKpi'           => $auditKpi,
            'sptPerBulan'        => $sptPerBulan,
            'tlProgress'         => $tlProgress,
            'kpiIrban'           => $kpiIrban,
            'topEntitasOverdue'  => $topEntitasOverdue,
        ]);
    }
}
