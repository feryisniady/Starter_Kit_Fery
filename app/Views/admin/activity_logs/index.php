<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Activity Log</h1>
        <p>Rekam jejak aktivitas pengguna sistem</p>
    </div>
    <div class="page-actions">
        <?php
            $exportParams = http_build_query(array_filter([
                'module'    => $filters['module'] ?? '',
                'user_id'   => $filters['user_id'] ?? '',
                'date_from' => $filters['date_from'] ?? '',
                'date_to'   => $filters['date_to'] ?? '',
            ]));
        ?>
        <a href="/admin/activity-logs/export?<?= $exportParams ?>" class="btn btn-secondary">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
    </div>
</div>

<!-- Stats Summary -->
<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-list-check"></i></div>
        <div class="stat-info">
            <div class="label">Total Log</div>
            <div class="value"><?= number_format($stats['total']) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <div class="label">Aktivitas Hari Ini</div>
            <div class="value"><?= $stats['today'] ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-right-to-bracket"></i></div>
        <div class="stat-info">
            <div class="label">Login Hari Ini</div>
            <div class="value"><?= $stats['login_today'] ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-info">
            <div class="label">Login Gagal Hari Ini</div>
            <div class="value"><?= $stats['failed_today'] ?></div>
        </div>
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
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-control">
                        <option value="">Semua User</option>
                        <?php foreach($users as $u): ?>
                            <option value="<?= esc($u['id']) ?>" <?= ($filters['user_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                <?= esc($u['name']) ?>
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
                            <th>Detail</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding:40px">
                                    <i class="fas fa-inbox" style="font-size:32px;opacity:.3;display:block;margin-bottom:8px"></i>
                                    Belum ada activity log
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($logs as $i => $log): ?>
                                <?php
                                    $meta = !empty($log['meta']) ? json_decode($log['meta'], true) : null;
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
                                        <span class="badge badge-<?= $badgeColor ?>">
                                            <?= esc($log['action']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($log['description']) ?></td>
                                    <td>
                                        <?php if($meta): ?>
                                            <button class="btn btn-sm btn-secondary btn-detail"
                                                data-meta='<?= esc(json_encode($meta, JSON_UNESCAPED_UNICODE)) ?>'
                                                title="Lihat detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size:12px">—</span>
                                        <?php endif; ?>
                                    </td>
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

<?= $this->section('scripts') ?>
<script>
$(document).on('click', '.btn-detail', function() {
    const meta   = $(this).data('meta');
    const hasBefore = meta.before && Object.keys(meta.before).length;
    const hasAfter  = meta.after  && Object.keys(meta.after).length;

    // Kumpulkan semua key unik
    const allKeys = [...new Set([
        ...(hasBefore ? Object.keys(meta.before) : []),
        ...(hasAfter  ? Object.keys(meta.after)  : []),
    ])];

    let html = '<div style="font-family:inherit">';

    // Mode: ada before DAN after → tampilkan tabel perbandingan
    if (hasBefore && hasAfter) {
        html += `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:4px">
            <div style="text-align:center;font-size:11px;font-weight:700;color:#dc2626;letter-spacing:.5px">
                <i class="fas fa-circle-minus"></i> SEBELUM
            </div>
            <div style="text-align:center;font-size:11px;font-weight:700;color:#16a34a;letter-spacing:.5px">
                <i class="fas fa-circle-plus"></i> SESUDAH
            </div>
        </div>`;

        allKeys.forEach(key => {
            const before  = meta.before[key] ?? '';
            const after   = meta.after[key]  ?? '';
            const changed = before != after;

            html += `
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;margin-bottom:6px;border-radius:8px;overflow:hidden;border:1px solid ${changed ? '#fca5a5' : '#e2e8f0'}">
                <div style="background:${changed ? '#fef2f2' : '#f8fafc'};padding:8px 12px">
                    <div style="font-size:10px;color:#9ca3af;margin-bottom:2px;text-transform:uppercase">${key}</div>
                    <div style="font-size:13px;color:${changed ? '#dc2626' : '#374151'};word-break:break-word">
                        ${before !== '' ? before : '<span style="color:#d1d5db;font-style:italic">kosong</span>'}
                    </div>
                </div>
                <div style="background:${changed ? '#f0fdf4' : '#f8fafc'};padding:8px 12px;border-left:1px solid ${changed ? '#bbf7d0' : '#e2e8f0'}">
                    <div style="font-size:10px;color:#9ca3af;margin-bottom:2px;text-transform:uppercase">${key}</div>
                    <div style="font-size:13px;font-weight:${changed ? '600' : '400'};color:${changed ? '#16a34a' : '#374151'};word-break:break-word">
                        ${after !== '' ? after : '<span style="color:#d1d5db;font-style:italic">kosong</span>'}
                        ${changed ? '<span style="font-size:10px;background:#dcfce7;color:#16a34a;padding:1px 6px;border-radius:99px;margin-left:6px">berubah</span>' : ''}
                    </div>
                </div>
            </div>`;
        });

    // Mode: hanya after (create)
    } else if (hasAfter) {
        html += `<div style="font-size:11px;font-weight:700;color:#16a34a;letter-spacing:.5px;margin-bottom:10px">
            <i class="fas fa-circle-plus"></i> DATA DITAMBAHKAN
        </div>`;
        Object.entries(meta.after).forEach(([key, val]) => {
            html += `
            <div style="display:flex;justify-content:space-between;align-items:flex-start;
                padding:8px 12px;background:#f0fdf4;border-radius:8px;margin-bottom:6px;
                border:1px solid #bbf7d0">
                <span style="font-size:12px;color:#6b7280;text-transform:uppercase;min-width:80px">${key}</span>
                <span style="font-size:13px;color:#15803d;font-weight:600;text-align:right;word-break:break-word">${val}</span>
            </div>`;
        });

    // Mode: hanya before (delete)
    } else if (hasBefore) {
        html += `<div style="font-size:11px;font-weight:700;color:#dc2626;letter-spacing:.5px;margin-bottom:10px">
            <i class="fas fa-circle-minus"></i> DATA DIHAPUS
        </div>`;
        Object.entries(meta.before).forEach(([key, val]) => {
            html += `
            <div style="display:flex;justify-content:space-between;align-items:flex-start;
                padding:8px 12px;background:#fef2f2;border-radius:8px;margin-bottom:6px;
                border:1px solid #fecaca">
                <span style="font-size:12px;color:#6b7280;text-transform:uppercase;min-width:80px">${key}</span>
                <span style="font-size:13px;color:#dc2626;font-weight:600;text-align:right;word-break:break-word">${val}</span>
            </div>`;
        });
    }

    html += '</div>';

    Swal.fire({
        title: '<span style="font-size:16px;font-weight:700">Detail Perubahan</span>',
        html: html || '<p style="color:#9ca3af;font-size:13px">Tidak ada detail tersedia</p>',
        width: hasBefore && hasAfter ? 560 : 420,
        padding: '24px',
        showConfirmButton: false,
        showCloseButton: true,
        customClass: { popup: 'swal-detail-popup' },
    });
});
</script>
<?= $this->endSection() ?>
