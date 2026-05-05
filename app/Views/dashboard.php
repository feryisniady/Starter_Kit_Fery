<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Dashboard</h1>
        <p>Selamat datang, <strong><?= esc(session()->get('user_name')) ?></strong> — <?= date('l, d F Y') ?></p>
    </div>
</div>

<!-- ===== STAT CARDS ROW 1: System ===== -->
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="label">Total User</div>
            <div class="value"><?= number_format($totalUsers) ?></div>
            <div class="sub">Pengguna terdaftar</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-shield-halved"></i></div>
        <div class="stat-info">
            <div class="label">Total Role</div>
            <div class="value"><?= number_format($totalRoles) ?></div>
            <div class="sub">Hak akses tersedia</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-bars"></i></div>
        <div class="stat-info">
            <div class="label">Total Menu</div>
            <div class="value"><?= number_format($totalMenus) ?></div>
            <div class="sub">Item navigasi</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-key"></i></div>
        <div class="stat-info">
            <div class="label">Total Permission</div>
            <div class="value"><?= number_format($totalPerms) ?></div>
            <div class="sub">Izin akses</div>
        </div>
    </div>
</div>

<!-- ===== STAT CARDS ROW 2: Activity ===== -->
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-chart-line"></i></div>
        <div class="stat-info">
            <div class="label">Total Aktivitas</div>
            <div class="value"><?= number_format($totalAct) ?></div>
            <div class="sub">Seluruh waktu</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <div class="label">Aktivitas Hari Ini</div>
            <div class="value"><?= number_format($actToday) ?></div>
            <div class="sub">Log tercatat hari ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-right-to-bracket"></i></div>
        <div class="stat-info">
            <div class="label">Login Hari Ini</div>
            <div class="value"><?= number_format($loginToday) ?></div>
            <div class="sub">Sesi berhasil</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-info">
            <div class="label">Login Gagal</div>
            <div class="value"><?= number_format($failToday) ?></div>
            <div class="sub">Percobaan gagal hari ini</div>
        </div>
    </div>
</div>

<!-- ===== AUDIT KPI (hanya tampil jika user punya akses audit) ===== -->
<?php if (!empty($auditKpi)): ?>
<div style="margin-bottom: 8px">
    <h3 style="font-size:14px;font-weight:600;color:#475569;margin:0 0 12px;display:flex;align-items:center;gap:8px">
        <i class="fas fa-magnifying-glass-chart" style="color:#3b82f6"></i>
        Monitoring Pengawasan <?= $auditKpi['tahun'] ?>
        <a href="/admin/laporan" style="font-size:12px;font-weight:400;color:#3b82f6;margin-left:4px">
            Lihat Laporan <i class="fas fa-arrow-right"></i>
        </a>
    </h3>
</div>
<div class="grid-4 mb-4">
    <div class="stat-card" style="border-left:4px solid #3b82f6">
        <div class="stat-icon blue"><i class="fas fa-file-signature"></i></div>
        <div class="stat-info">
            <div class="label">SPT Berjalan</div>
            <div class="value"><?= number_format((int)($auditKpi['spt']['berjalan'] ?? 0)) ?></div>
            <div class="sub">
                <span style="color:#94a3b8"><?= number_format((int)($auditKpi['spt']['total'] ?? 0)) ?> total SPT</span>
                <?php if (($auditKpi['spt']['pending_approval'] ?? 0) > 0): ?>
                &nbsp;·&nbsp; <span style="color:#f59e0b"><?= $auditKpi['spt']['pending_approval'] ?> menunggu acc</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="stat-card" style="border-left:4px solid #f59e0b">
        <div class="stat-icon orange"><i class="fas fa-file-pen"></i></div>
        <div class="stat-info">
            <div class="label">KKA Pending Review KT</div>
            <div class="value"><?= number_format($auditKpi['kka_pending_kt']) ?></div>
            <div class="sub">
                <?php if ($auditKpi['kka_pending_kt'] > 0): ?>
                    <a href="/admin/spt" style="color:#f59e0b;font-size:12px">Perlu direview segera</a>
                <?php else: ?>
                    <span style="color:#22c55e">Semua KKA sudah direview</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="stat-card" style="border-left:4px solid #8b5cf6">
        <div class="stat-icon" style="background:#ede9fe;color:#8b5cf6"><i class="fas fa-envelope-open-text"></i></div>
        <div class="stat-info">
            <div class="label">NHP Menunggu Tanggapan</div>
            <div class="value"><?= number_format($auditKpi['nhp_pending']) ?></div>
            <div class="sub">
                <?php if ($auditKpi['nhp_pending'] > 0): ?>
                    <span style="color:#f59e0b">Auditi belum merespons</span>
                <?php else: ?>
                    <span style="color:#22c55e">Semua NHP sudah ditanggapi</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="stat-card" style="border-left:4px solid <?= $auditKpi['tl_overdue'] > 0 ? '#ef4444' : '#22c55e' ?>">
        <div class="stat-icon <?= $auditKpi['tl_overdue'] > 0 ? 'red' : 'green' ?>">
            <i class="fas fa-<?= $auditKpi['tl_overdue'] > 0 ? 'triangle-exclamation' : 'check-circle' ?>"></i>
        </div>
        <div class="stat-info">
            <div class="label">Tindak Lanjut Overdue</div>
            <div class="value" style="color:<?= $auditKpi['tl_overdue'] > 0 ? '#ef4444' : 'inherit' ?>">
                <?= number_format($auditKpi['tl_overdue']) ?>
            </div>
            <div class="sub">
                <?php if ($auditKpi['tl_overdue'] > 0): ?>
                    <a href="/admin/laporan/rekap-tl" style="color:#ef4444;font-size:12px">Rekomendasi melewati batas waktu</a>
                <?php else: ?>
                    <span style="color:#22c55e">Tidak ada TL overdue</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===== GRAFIK TREND PENGAWASAN ===== -->
<?php if (!empty($auditKpi)): ?>
<div class="grid-2 mb-4">

    <!-- Chart: SPT per Bulan -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-bar" style="color:#3b82f6"></i> SPT per Bulan — <?= $auditKpi['tahun'] ?></div>
        </div>
        <div class="card-body" style="padding-top:8px">
            <canvas id="sptBulanChart" height="120"></canvas>
        </div>
    </div>

    <!-- Chart: Progress TL (donut) -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-pie" style="color:#22c55e"></i> Progress Tindak Lanjut</div>
        </div>
        <div class="card-body" style="display:flex;align-items:center;gap:24px;padding:16px">
            <div style="position:relative;width:140px;flex-shrink:0">
                <canvas id="tlDonutChart"></canvas>
            </div>
            <?php
            $tlTotal   = (int)($tlProgress['selesai'] ?? 0) + (int)($tlProgress['proses'] ?? 0) + (int)($tlProgress['belum'] ?? 0);
            $tlPct     = $tlTotal > 0 ? round($tlProgress['selesai'] / $tlTotal * 100) : 0;
            ?>
            <div style="flex:1">
                <div style="font-size:28px;font-weight:700;color:#22c55e;line-height:1"><?= $tlPct ?>%</div>
                <div style="font-size:12px;color:#64748b;margin-bottom:12px">rekomendasi selesai</div>
                <div style="display:flex;flex-direction:column;gap:6px;font-size:12px">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span><span style="display:inline-block;width:10px;height:10px;background:#22c55e;border-radius:2px;margin-right:6px"></span>Selesai</span>
                        <strong><?= number_format((int)($tlProgress['selesai'] ?? 0)) ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span><span style="display:inline-block;width:10px;height:10px;background:#f59e0b;border-radius:2px;margin-right:6px"></span>Dalam Proses</span>
                        <strong><?= number_format((int)($tlProgress['proses'] ?? 0)) ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <span><span style="display:inline-block;width:10px;height:10px;background:#ef4444;border-radius:2px;margin-right:6px"></span>Belum</span>
                        <strong><?= number_format((int)($tlProgress['belum'] ?? 0)) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== KPI PER IRBAN ===== -->
<?php if (!empty($kpiIrban)): ?>
<div class="card mb-4">
    <div class="card-header" style="justify-content:space-between">
        <div class="card-title"><i class="fas fa-building" style="color:#6366f1"></i> KPI per Bidang / Irban — <?= $auditKpi['tahun'] ?></div>
        <a href="/admin/laporan/matriks-tl?tahun=<?= $auditKpi['tahun'] ?>" class="btn btn-sm btn-secondary" style="font-size:12px">
            Detail <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <style>
        #tbl-kpi-irban th, #tbl-kpi-irban td { border: 1px solid #e2e8f0 !important; }
        #tbl-kpi-irban thead th { background:#f8fafc; color:#475569; font-weight:600; font-size:11.5px; }
        </style>
        <table id="tbl-kpi-irban" class="table table-striped" style="font-size:12.5px;margin:0;width:100%;table-layout:fixed;border-collapse:collapse">
            <thead>
                <tr>
                    <th>Bidang / Irban</th>
                    <th style="text-align:center;width:60px">SPT</th>
                    <th style="text-align:center;width:70px">Temuan</th>
                    <th style="text-align:center;width:80px">TL Selesai</th>
                    <th style="text-align:center;width:75px">TL Proses</th>
                    <th style="text-align:center;width:70px">TL Belum</th>
                    <th style="text-align:center;width:75px">Overdue</th>
                    <th style="width:130px">Progress TL</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($kpiIrban as $irb):
                $total = (int)$irb['tl_selesai'] + (int)$irb['tl_proses'] + (int)$irb['tl_belum'];
                $pct   = $total > 0 ? round($irb['tl_selesai'] / $total * 100) : 0;
            ?>
            <tr>
                <td style="font-weight:600;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= esc($irb['irban_nama']) ?>"><?= esc($irb['irban_nama']) ?></td>
                <td style="text-align:center">
                    <span style="font-weight:600;color:#3b82f6"><?= $irb['total_spt'] ?></span>
                </td>
                <td style="text-align:center">
                    <?php if ($irb['total_temuan'] > 0): ?>
                    <a href="/admin/laporan/rekap-temuan?tahun=<?= $auditKpi['tahun'] ?>" style="color:#6366f1;font-weight:600"><?= $irb['total_temuan'] ?></a>
                    <?php else: ?>
                    <span style="color:#cbd5e1">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;color:#22c55e;font-weight:600"><?= $irb['tl_selesai'] ?></td>
                <td style="text-align:center;color:#f59e0b;font-weight:600"><?= $irb['tl_proses'] ?></td>
                <td style="text-align:center;color:#ef4444;font-weight:600"><?= $irb['tl_belum'] ?></td>
                <td style="text-align:center">
                    <?php if ((int)$irb['tl_overdue'] > 0): ?>
                    <span class="badge badge-danger"><?= $irb['tl_overdue'] ?></span>
                    <?php else: ?>
                    <span style="color:#22c55e;font-size:11px">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px">
                        <div style="flex:1;height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden">
                            <div style="width:<?= $pct ?>%;height:100%;background:<?= $pct >= 80 ? '#22c55e' : ($pct >= 50 ? '#f59e0b' : '#ef4444') ?>;border-radius:99px"></div>
                        </div>
                        <span style="font-size:11px;color:#64748b;flex-shrink:0;min-width:28px"><?= $pct ?>%</span>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ===== TOP ENTITAS TL OVERDUE ===== -->
<?php if (!empty($topEntitasOverdue)): ?>
<div class="card mb-4">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-triangle-exclamation" style="color:#ef4444"></i>
            Top Entitas dengan TL Melewati Batas Waktu
        </div>
        <a href="/admin/laporan/rekap-tl?tahun=<?= $auditKpi['tahun'] ?>&filter=overdue" class="btn btn-sm" style="background:#fef2f2;color:#ef4444;border:1px solid #fecaca;font-size:12px">
            Lihat Semua Overdue
        </a>
    </div>
    <div class="card-body p-0">
        <?php foreach($topEntitasOverdue as $idx => $ent): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid #f1f5f9<?= $idx === count($topEntitasOverdue)-1 ? ';border-bottom:none' : '' ?>">
            <div style="width:26px;height:26px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#ef4444;flex-shrink:0">
                <?= $idx + 1 ?>
            </div>
            <div style="flex:1;font-size:13px;font-weight:500;color:#1e293b"><?= esc($ent['entitas_nama']) ?></div>
            <div style="display:flex;gap:12px;font-size:12px;text-align:center">
                <div>
                    <div style="font-weight:700;color:#ef4444"><?= $ent['tl_overdue'] ?></div>
                    <div style="color:#94a3b8;font-size:10px">Overdue</div>
                </div>
                <div>
                    <div style="font-weight:700;color:#f59e0b"><?= $ent['tl_belum'] ?></div>
                    <div style="color:#94a3b8;font-size:10px">Belum</div>
                </div>
                <div>
                    <div style="font-weight:700;color:#6366f1"><?= $ent['total_rek'] ?></div>
                    <div style="color:#94a3b8;font-size:10px">Total Rek</div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php endif; // end auditKpi ?>

<!-- ===== CHART + RECENT ACTIVITY ===== -->
<div class="grid-2 mb-4">

    <!-- Chart 7 Hari -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-bar"></i> Aktivitas 7 Hari Terakhir</div>
        </div>
        <div class="card-body" style="padding-top:8px">
            <canvas id="actChart" height="110"></canvas>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header" style="justify-content:space-between">
            <div class="card-title"><i class="fas fa-clock-rotate-left"></i> Aktivitas Terbaru</div>
            <?php if(hasPermission('activitylog.view')): ?>
            <a href="/admin/activity-logs" class="btn btn-sm btn-secondary" style="font-size:12px">
                Lihat Semua <i class="fas fa-arrow-right"></i>
            </a>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if(empty($recentLogs)): ?>
                <div style="text-align:center;padding:32px;color:#94a3b8">
                    <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4"></i>
                    Belum ada aktivitas
                </div>
            <?php else: ?>
            <div class="activity-list">
                <?php foreach($recentLogs as $log):
                    $badgeClass = match(true) {
                        str_starts_with($log['action'], 'login_failed'),
                        str_starts_with($log['action'], 'login_blocked'),
                        str_starts_with($log['action'], 'login_lockout') => 'danger',
                        str_starts_with($log['action'], 'login')         => 'success',
                        str_starts_with($log['action'], 'logout')        => 'gray',
                        str_contains($log['action'],    '.delete')       => 'danger',
                        str_contains($log['action'],    '.create')       => 'success',
                        str_contains($log['action'],    '.update')       => 'info',
                        default                                          => 'primary',
                    };
                    $iconMap = match(true) {
                        str_starts_with($log['action'], 'login_failed'),
                        str_starts_with($log['action'], 'login_blocked') => 'fa-lock',
                        str_starts_with($log['action'], 'login')         => 'fa-right-to-bracket',
                        str_starts_with($log['action'], 'logout')        => 'fa-right-from-bracket',
                        str_contains($log['action'],    '.delete')       => 'fa-trash',
                        str_contains($log['action'],    '.create')       => 'fa-plus',
                        str_contains($log['action'],    '.update')       => 'fa-pen',
                        default                                          => 'fa-circle-info',
                    };
                ?>
                <div class="activity-item">
                    <div class="act-icon badge-<?= $badgeClass ?>">
                        <i class="fas <?= $iconMap ?>"></i>
                    </div>
                    <div class="act-body">
                        <div class="act-desc"><?= esc($log['description'] ?: $log['action']) ?></div>
                        <div class="act-meta">
                            <span><?= esc($log['user_name'] ?: 'Guest') ?></span>
                            <span class="dot">·</span>
                            <span><?= date('d/m H:i', strtotime($log['created_at'])) ?></span>
                        </div>
                    </div>
                    <span class="badge badge-<?= $badgeClass ?>" style="flex-shrink:0;font-size:10px">
                        <?= esc($log['action']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===== QUICK LINKS ===== -->
<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-rocket"></i> Akses Cepat</div>
    </div>
    <div class="card-body">
        <div class="quick-links">
            <?php if(hasPermission('user.view')): ?>
            <a href="/admin/users" class="quick-link-card">
                <div class="ql-icon blue"><i class="fas fa-users"></i></div>
                <span>Kelola User</span>
            </a>
            <?php endif; ?>
            <?php if(hasPermission('role.view')): ?>
            <a href="/admin/roles" class="quick-link-card">
                <div class="ql-icon green"><i class="fas fa-shield-halved"></i></div>
                <span>Kelola Role</span>
            </a>
            <?php endif; ?>
            <?php if(hasPermission('menu.view')): ?>
            <a href="/admin/menus" class="quick-link-card">
                <div class="ql-icon navy"><i class="fas fa-bars"></i></div>
                <span>Kelola Menu</span>
            </a>
            <?php endif; ?>
            <?php if(hasPermission('permission.view')): ?>
            <a href="/admin/permissions" class="quick-link-card">
                <div class="ql-icon orange"><i class="fas fa-key"></i></div>
                <span>Permission</span>
            </a>
            <?php endif; ?>
            <?php if(hasPermission('activitylog.view')): ?>
            <a href="/admin/activity-logs" class="quick-link-card">
                <div class="ql-icon purple"><i class="fas fa-list-check"></i></div>
                <span>Activity Log</span>
            </a>
            <?php endif; ?>
            <?php if(hasPermission('setting.manage')): ?>
            <a href="/admin/settings" class="quick-link-card">
                <div class="ql-icon gray"><i class="fas fa-gear"></i></div>
                <span>Pengaturan</span>
            </a>
            <?php endif; ?>
            <a href="/profile" class="quick-link-card">
                <div class="ql-icon teal"><i class="fas fa-circle-user"></i></div>
                <span>Profil Saya</span>
            </a>
        </div>
    </div>
</div>

<style>
/* ---- Activity list ---- */
.activity-list { display:flex; flex-direction:column; }
.activity-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    border-bottom: 1px solid #f1f5f9;
    transition: background .15s;
}
.activity-item:last-child { border-bottom: none; }
.activity-item:hover { background: #f8fafc; }
.act-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 13px;
}
/* reuse badge colour backgrounds */
.act-icon.badge-success  { background: #dcfce7; color: #16a34a; }
.act-icon.badge-danger   { background: #fee2e2; color: #dc2626; }
.act-icon.badge-info     { background: #e0f2fe; color: #0369a1; }
.act-icon.badge-gray     { background: #f1f5f9; color: #64748b; }
.act-icon.badge-primary  { background: #eff6ff; color: #2563eb; }
.act-body { flex: 1; min-width: 0; }
.act-desc { font-size: 13px; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.act-meta { font-size: 11px; color: #94a3b8; margin-top: 2px; display:flex; gap:4px; }
.act-meta .dot { color: #cbd5e1; }

/* ---- Quick links ---- */
.quick-links {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
.quick-link-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 18px 20px;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    text-decoration: none;
    color: #374151;
    font-size: 13px;
    font-weight: 500;
    min-width: 90px;
    transition: border-color .2s, background .2s, transform .15s;
}
.quick-link-card:hover {
    border-color: #93c5fd;
    background: #f0f9ff;
    transform: translateY(-2px);
    color: #1e40af;
}
.ql-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
}
.ql-icon.blue   { background: #eff6ff; color: #2563eb; }
.ql-icon.green  { background: #f0fdf4; color: #16a34a; }
.ql-icon.orange { background: #fff7ed; color: #ea580c; }
.ql-icon.navy   { background: #eff6ff; color: #1a3c6e; }
.ql-icon.purple { background: #f5f3ff; color: #7c3aed; }
.ql-icon.gray   { background: #f1f5f9; color: #475569; }
.ql-icon.teal   { background: #f0fdfa; color: #0d9488; }
</style>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function() {
    // ── Chart: Aktivitas 7 hari ──────────────────────────────────────────
    var labels = <?= json_encode(array_column($chart7days, 'label')) ?>;
    var data   = <?= json_encode(array_column($chart7days, 'total')) ?>;

    new Chart(document.getElementById('actChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Aktivitas',
                data: data,
                backgroundColor: 'rgba(37,99,235,.15)',
                borderColor: '#2563eb',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(37,99,235,.3)',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,.05)' } },
                x: { ticks: { font: { size: 11 } }, grid: { display: false } }
            }
        }
    });

<?php if (!empty($auditKpi)): ?>

    // ── Chart: SPT per Bulan ─────────────────────────────────────────────
    var bulanLabels = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    var sptData     = <?= json_encode(array_values($sptPerBulan)) ?>;

    new Chart(document.getElementById('sptBulanChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: bulanLabels,
            datasets: [{
                label: 'SPT',
                data: sptData,
                backgroundColor: 'rgba(99,102,241,.18)',
                borderColor: '#6366f1',
                borderWidth: 2,
                borderRadius: 5,
                borderSkipped: false,
                hoverBackgroundColor: 'rgba(99,102,241,.35)',
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function(c) { return ' ' + c.parsed.y + ' SPT'; } } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,.05)' } },
                x: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });

    // ── Chart: Progress TL (donut) ───────────────────────────────────────
    var tlData   = [<?= (int)($tlProgress['selesai']??0) ?>, <?= (int)($tlProgress['proses']??0) ?>, <?= (int)($tlProgress['belum']??0) ?>];
    var tlColors = ['#22c55e', '#f59e0b', '#ef4444'];

    new Chart(document.getElementById('tlDonutChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Selesai', 'Dalam Proses', 'Belum'],
            datasets: [{ data: tlData, backgroundColor: tlColors, borderWidth: 2, hoverOffset: 4 }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: function(c) { return ' ' + c.label + ': ' + c.parsed; } } }
            }
        }
    });

<?php endif; ?>
})();
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
