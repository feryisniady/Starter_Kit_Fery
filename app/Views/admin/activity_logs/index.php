<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Activity Log</h1>
        <p>Rekam jejak aktivitas pengguna sistem</p>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/activity-logs">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Modul</label>
                    <select name="module" class="form-control">
                        <option value="">Semua Modul</option>
                        <?php foreach($modules as $mod): ?>
                            <option value="<?= esc($mod) ?>" <?= ($filters['module'] ?? '') === $mod ? 'selected' : '' ?>>
                                <?= esc(ucfirst($mod)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="form-control"
                        value="<?= esc($filters['date_from'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="form-control"
                        value="<?= esc($filters['date_to'] ?? '') ?>">
                </div>
                <div class="form-group" style="display:flex;align-items:flex-end;gap:8px">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="/admin/activity-logs" class="btn btn-secondary">
                        <i class="fas fa-rotate"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Log -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <div class="table-wrap">
                <table id="tabel-activity-logs" data-datatable class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>Deskripsi</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted" style="padding:40px">
                                    <i class="fas fa-inbox" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px"></i>
                                    Belum ada activity log
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($logs as $i => $log): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td style="white-space:nowrap">
                                        <div><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                                        <div style="color:#94a3b8;font-size:12px"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
                                    </td>
                                    <td>
                                        <?php if($log['user_name']): ?>
                                            <span style="font-weight:600"><?= esc($log['user_name']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Guest</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                            $badgeColor = match(true) {
                                                str_starts_with($log['action'], 'login_failed'),
                                                str_starts_with($log['action'], 'login_lockout'),
                                                str_starts_with($log['action'], 'login_blocked') => 'danger',
                                                str_starts_with($log['action'], 'login')         => 'success',
                                                str_starts_with($log['action'], 'logout')        => 'secondary',
                                                str_contains($log['action'], '.delete')          => 'danger',
                                                str_contains($log['action'], '.create')          => 'success',
                                                str_contains($log['action'], '.update')          => 'info',
                                                default                                          => 'primary',
                                            };
                                        ?>
                                        <span class="badge badge-<?= $badgeColor ?>">
                                            <?= esc($log['action']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($log['description']) ?></td>
                                    <td style="font-family:monospace;font-size:12px">
                                        <?= esc($log['ip_address']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
