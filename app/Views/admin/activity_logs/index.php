<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Activity Log</h1>
        <p>Rekam jejak aktivitas pengguna sistem</p>
    </div>
    <div class="page-actions">
        <a href="/admin/activity-logs/export" class="btn btn-secondary">
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
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Modul</label>
                <select id="filter_module" class="form-control" onchange="dtApplyFilter()">
                    <option value="">Semua Modul</option>
                    <?php foreach($modules as $mod): ?>
                        <option value="<?= esc($mod) ?>"><?= esc(ucfirst($mod)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">User</label>
                <select id="filter_user_id" class="form-control" onchange="dtApplyFilter()">
                    <option value="">Semua User</option>
                    <?php foreach($users as $u): ?>
                        <option value="<?= esc($u['id']) ?>"><?= esc($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Dari Tanggal</label>
                <input type="date" id="filter_date_from" class="form-control" onchange="dtApplyFilter()">
            </div>
            <div class="form-group">
                <label class="form-label">Sampai Tanggal</label>
                <input type="date" id="filter_date_to" class="form-control" onchange="dtApplyFilter()">
            </div>
            <div class="form-group" style="display:flex;align-items:flex-end">
                <button onclick="dtResetFilter()" class="btn btn-secondary">
                    <i class="fas fa-rotate"></i> Reset
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tabel -->
<div class="card">
    <div class="card-body">
        <table id="dt-actlogs" data-url="/admin/activity-logs/data" class="w-100">
            <thead>
                <tr>
                    <th class="dt-nosort dt-nosearch" data-dt="no" width="50">#</th>
                    <th data-dt="waktu">Waktu</th>
                    <th data-dt="user">User</th>
                    <th data-dt="action">Aksi</th>
                    <th data-dt="description">Deskripsi</th>
                    <th class="dt-nosort dt-nosearch" data-dt="detail" width="60">Detail</th>
                    <th class="dt-nosort" data-dt="ip">IP Address</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function dtApplyFilter() {
    DT_EXTRA_PARAMS['dt-actlogs'] = {
        filter_module:    document.getElementById('filter_module').value,
        filter_user_id:   document.getElementById('filter_user_id').value,
        filter_date_from: document.getElementById('filter_date_from').value,
        filter_date_to:   document.getElementById('filter_date_to').value,
    };
    dtReload('dt-actlogs');
}
function dtResetFilter() {
    ['filter_module','filter_user_id','filter_date_from','filter_date_to'].forEach(id => {
        document.getElementById(id).value = '';
    });
    DT_EXTRA_PARAMS['dt-actlogs'] = {};
    dtReload('dt-actlogs');
}
$(document).on('click', '.btn-detail', function() {
    var raw  = $(this).attr('data-meta');
    var meta = {};
    try { meta = JSON.parse(raw); } catch(e) {}
    const hasBefore = meta.before && Object.keys(meta.before).length;
    const hasAfter  = meta.after  && Object.keys(meta.after).length;
    const allKeys   = [...new Set([...(hasBefore?Object.keys(meta.before):[]),...(hasAfter?Object.keys(meta.after):[])])];
    let html = '<div style="font-family:inherit">';
    if (hasBefore && hasAfter) {
        html += `<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:4px">
            <div style="text-align:center;font-size:11px;font-weight:700;color:#dc2626"><i class="fas fa-circle-minus"></i> SEBELUM</div>
            <div style="text-align:center;font-size:11px;font-weight:700;color:#16a34a"><i class="fas fa-circle-plus"></i> SESUDAH</div></div>`;
        allKeys.forEach(key => {
            const before=meta.before[key]??'',after=meta.after[key]??'',changed=before!=after;
            html+=`<div style="display:grid;grid-template-columns:1fr 1fr;gap:0;margin-bottom:6px;border-radius:8px;overflow:hidden;border:1px solid ${changed?'#fca5a5':'#e2e8f0'}">
                <div style="background:${changed?'#fef2f2':'#f8fafc'};padding:8px 12px"><div style="font-size:10px;color:#9ca3af;margin-bottom:2px;text-transform:uppercase">${key}</div>
                <div style="font-size:13px;color:${changed?'#dc2626':'#374151'};word-break:break-word">${before!==''?before:'<span style="color:#d1d5db;font-style:italic">kosong</span>'}</div></div>
                <div style="background:${changed?'#f0fdf4':'#f8fafc'};padding:8px 12px;border-left:1px solid ${changed?'#bbf7d0':'#e2e8f0'}"><div style="font-size:10px;color:#9ca3af;margin-bottom:2px;text-transform:uppercase">${key}</div>
                <div style="font-size:13px;font-weight:${changed?'600':'400'};color:${changed?'#16a34a':'#374151'};word-break:break-word">${after!==''?after:'<span style="color:#d1d5db;font-style:italic">kosong</span>'}${changed?'<span style="font-size:10px;background:#dcfce7;color:#16a34a;padding:1px 6px;border-radius:99px;margin-left:6px">berubah</span>':''}</div></div></div>`;
        });
    } else if (hasAfter) {
        html+=`<div style="font-size:11px;font-weight:700;color:#16a34a;margin-bottom:10px"><i class="fas fa-circle-plus"></i> DATA DITAMBAHKAN</div>`;
        Object.entries(meta.after).forEach(([k,v])=>{html+=`<div style="display:flex;justify-content:space-between;padding:8px 12px;background:#f0fdf4;border-radius:8px;margin-bottom:6px;border:1px solid #bbf7d0"><span style="font-size:12px;color:#6b7280;text-transform:uppercase;min-width:80px">${k}</span><span style="font-size:13px;color:#15803d;font-weight:600;text-align:right;word-break:break-word">${v}</span></div>`;});
    } else if (hasBefore) {
        html+=`<div style="font-size:11px;font-weight:700;color:#dc2626;margin-bottom:10px"><i class="fas fa-circle-minus"></i> DATA DIHAPUS</div>`;
        Object.entries(meta.before).forEach(([k,v])=>{html+=`<div style="display:flex;justify-content:space-between;padding:8px 12px;background:#fef2f2;border-radius:8px;margin-bottom:6px;border:1px solid #fecaca"><span style="font-size:12px;color:#6b7280;text-transform:uppercase;min-width:80px">${k}</span><span style="font-size:13px;color:#dc2626;font-weight:600;text-align:right;word-break:break-word">${v}</span></div>`;});
    }
    html+='</div>';
    Swal.fire({title:'<span style="font-size:16px;font-weight:700">Detail Perubahan</span>',html:html||'<p style="color:#9ca3af;font-size:13px">Tidak ada detail</p>',width:hasBefore&&hasAfter?560:420,padding:'24px',showConfirmButton:false,showCloseButton:true});
});
</script>
<?= $this->endSection() ?>
