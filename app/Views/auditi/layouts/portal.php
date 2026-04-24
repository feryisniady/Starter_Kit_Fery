<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title ?? 'Portal Auditi') ?> — SKIPA</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.min.css">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Arial,sans-serif;font-size:14px;background:#f1f5f9;color:#1e293b;min-height:100vh}

/* ── Topbar ── */
.topbar{background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;padding:0 24px;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;box-shadow:0 2px 8px rgba(0,0,0,.2)}
.topbar-brand{display:flex;align-items:center;gap:12px;font-weight:700;font-size:15px}
.topbar-brand .brand-logo{width:36px;height:36px;background:rgba(255,255,255,.2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px}
.topbar-brand .brand-sub{font-size:11px;font-weight:400;opacity:.8}
.topbar-nav{display:flex;align-items:center;gap:4px}
.topbar-nav a{color:rgba(255,255,255,.85);padding:6px 14px;border-radius:6px;text-decoration:none;font-size:13px;transition:background .15s}
.topbar-nav a:hover,.topbar-nav a.active{background:rgba(255,255,255,.15);color:#fff}
.topbar-nav a i{margin-right:6px}
.topbar-right{display:flex;align-items:center;gap:12px}
.topbar-user{font-size:12px;text-align:right;opacity:.9}
.topbar-user strong{display:block;font-size:13px}
.btn-logout{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);color:#fff;padding:5px 14px;border-radius:6px;font-size:12px;cursor:pointer;text-decoration:none;transition:background .15s}
.btn-logout:hover{background:rgba(255,255,255,.25)}

/* ── Main ── */
.main-wrap{max-width:1000px;margin:0 auto;padding:28px 20px}

/* ── Page header ── */
.page-header{margin-bottom:24px}
.page-header h1{font-size:20px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:10px}
.page-header p{color:#64748b;font-size:13px;margin-top:4px}

/* ── Cards ── */
.card{background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,.07);margin-bottom:20px;overflow:hidden}
.card-header{padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between}
.card-title{font-size:14px;font-weight:700;color:#0f172a}
.card-body{padding:20px}

/* ── Stat cards ── */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;margin-bottom:24px}
.stat-card{background:#fff;border-radius:12px;padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.07);border-left:4px solid #e2e8f0}
.stat-card.blue{border-color:#3b82f6}.stat-card.amber{border-color:#f59e0b}.stat-card.green{border-color:#22c55e}.stat-card.red{border-color:#ef4444}.stat-card.purple{border-color:#8b5cf6}
.stat-num{font-size:28px;font-weight:700;line-height:1}
.stat-label{font-size:11px;color:#64748b;margin-top:4px}

/* ── Tables ── */
.tbl{width:100%;border-collapse:collapse}
.tbl th{background:#f8fafc;padding:10px 14px;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:2px solid #e2e8f0}
.tbl td{padding:12px 14px;border-bottom:1px solid #f1f5f9;font-size:13px;vertical-align:middle}
.tbl tr:last-child td{border-bottom:none}
.tbl tr:hover td{background:#f8fafc}

/* ── Badges ── */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600}
.badge-pending{background:#fef3c7;color:#92400e}
.badge-sesuai{background:#dcfce7;color:#166534}
.badge-tidak-sesuai{background:#fee2e2;color:#991b1b}
.badge-menunggu{background:#fef9c3;color:#854d0e}
.badge-diterima{background:#dcfce7;color:#166534}
.badge-revisi{background:#fee2e2;color:#991b1b}
.badge-terkirim{background:#dbeafe;color:#1e40af}
.badge-ditanggapi{background:#ede9fe;color:#5b21b6}
.badge-selesai{background:#dcfce7;color:#166534}
.badge-draft{background:#f1f5f9;color:#475569}

/* ── Buttons ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:all .15s}
.btn-primary{background:#2563eb;color:#fff}.btn-primary:hover{background:#1d4ed8}
.btn-success{background:#16a34a;color:#fff}.btn-success:hover{background:#15803d}
.btn-warning{background:#d97706;color:#fff}.btn-warning:hover{background:#b45309}
.btn-danger{background:#dc2626;color:#fff}.btn-danger:hover{background:#b91c1c}
.btn-secondary{background:#e2e8f0;color:#475569}.btn-secondary:hover{background:#cbd5e1}
.btn-outline{background:transparent;border:1.5px solid #cbd5e1;color:#475569}.btn-outline:hover{background:#f8fafc}
.btn-sm{padding:5px 12px;font-size:12px}
.btn-xs{padding:3px 9px;font-size:11px}
.w-full{width:100%}

/* ── Forms ── */
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px}
.form-control{width:100%;padding:8px 12px;border:1.5px solid #d1d5db;border-radius:8px;font-size:13px;color:#1e293b;transition:border .15s}
.form-control:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
textarea.form-control{resize:vertical;min-height:90px}

/* ── Alert ── */
.alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;display:flex;gap:10px;align-items:flex-start}
.alert-success{background:#f0fdf4;border:1px solid #86efac;color:#166534}
.alert-error{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b}
.alert-warning{background:#fffbeb;border:1px solid #fcd34d;color:#92400e}
.alert-info{background:#eff6ff;border:1px solid #93c5fd;color:#1e40af}

/* ── Upload zone ── */
.upload-zone{border:2px dashed #cbd5e1;border-radius:10px;padding:24px;text-align:center;cursor:pointer;transition:all .2s;background:#f8fafc}
.upload-zone:hover,.upload-zone.dragover{border-color:#3b82f6;background:#eff6ff}
.upload-zone i{font-size:28px;color:#94a3b8;display:block;margin-bottom:8px}
.upload-zone p{font-size:12px;color:#64748b}

/* ── File list ── */
.file-item{display:flex;align-items:center;gap:10px;padding:8px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:6px;font-size:12px}
.file-item i{color:#6366f1;font-size:16px}
.file-item .file-name{flex:1;font-weight:500;word-break:break-all}
.file-item .file-size{color:#94a3b8;white-space:nowrap}

/* ── Progress bar ── */
.progress-wrap{background:#e2e8f0;border-radius:99px;height:8px;overflow:hidden;margin:6px 0}
.progress-bar{height:100%;border-radius:99px;background:linear-gradient(90deg,#3b82f6,#6366f1);transition:width .4s}

/* ── Timeline ── */
.timeline{position:relative;padding-left:28px}
.timeline::before{content:'';position:absolute;left:8px;top:0;bottom:0;width:2px;background:#e2e8f0}
.tl-item{position:relative;margin-bottom:20px}
.tl-dot{position:absolute;left:-24px;top:3px;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 2px #e2e8f0}
.tl-dot.menunggu{background:#fbbf24}.tl-dot.diterima{background:#22c55e}.tl-dot.revisi{background:#ef4444}
.tl-date{font-size:11px;color:#94a3b8;margin-bottom:4px}
.tl-body{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px}
.tl-uraian{font-size:13px;line-height:1.6;margin-bottom:8px}
.tl-verif{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px}
.tl-verif.menunggu{background:#fef9c3;color:#854d0e}
.tl-verif.diterima{background:#dcfce7;color:#166534}
.tl-verif.revisi{background:#fee2e2;color:#991b1b}
.tl-catatan{margin-top:8px;padding:8px 10px;background:#fff;border-radius:6px;border:1px solid #e2e8f0;font-size:12px;color:#64748b}

/* ── Responsive ── */
@media(max-width:640px){
    .topbar-nav{display:none}
    .main-wrap{padding:16px 12px}
    .stat-grid{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>

<!-- Topbar -->
<nav class="topbar">
    <div class="topbar-brand">
        <div class="brand-logo"><i class="fas fa-shield-halved"></i></div>
        <div>
            SKIPA — Portal Auditi
            <div class="brand-sub"><?= esc($entitas['nama'] ?? '') ?></div>
        </div>
    </div>
    <div class="topbar-nav">
        <a href="/auditi/dashboard" <?= (current_url(true)->getPath() === '/auditi/dashboard') ? 'class="active"' : '' ?>>
            <i class="fas fa-home"></i> Dashboard
        </a>
        <a href="/auditi/nhp" <?= str_starts_with(current_url(true)->getPath(), '/auditi/nhp') ? 'class="active"' : '' ?>>
            <i class="fas fa-file-alt"></i> NHP
        </a>
        <a href="/auditi/tl" <?= str_starts_with(current_url(true)->getPath(), '/auditi/tl') ? 'class="active"' : '' ?>>
            <i class="fas fa-tasks"></i> Tindak Lanjut
        </a>
    </div>
    <div class="topbar-right">
        <div class="topbar-user">
            <strong><?= esc(session()->get('user_name')) ?></strong>
            <?= esc($entitas['nama'] ?? '') ?>
        </div>
        <a href="/logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Keluar</a>
    </div>
</nav>

<!-- Content -->
<div class="main-wrap">

<?php if(session()->getFlashdata('success')): ?>
<div class="alert alert-success"><i class="fas fa-circle-check"></i><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?= $this->renderSection('content') ?>

</div><!-- /main-wrap -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.min.js"></script>
<script>
const CSRF_NAME = '<?= csrf_token() ?>';
const CSRF_HASH = '<?= csrf_hash() ?>';

function swalConfirm(opts, onConfirm) {
    Swal.fire(Object.assign({
        icon:'question', title:'Konfirmasi', showCancelButton:true,
        confirmButtonColor:'#2563eb', cancelButtonColor:'#6b7280',
        confirmButtonText:'Ya, Lanjutkan', cancelButtonText:'Batal', reverseButtons:true,
    }, opts)).then(r => { if(r.isConfirmed) onConfirm(); });
}
document.addEventListener('submit', function(e){
    var form = e.target;
    if(!form.dataset.confirm || form._swalOk) return;
    e.preventDefault();
    var isDel = form.dataset.confirmType==='delete';
    swalConfirm({
        title: form.dataset.confirmTitle||(isDel?'Hapus?':'Konfirmasi'),
        html: form.dataset.confirm,
        icon: isDel?'warning':'question',
        confirmButtonColor: isDel?'#ef4444':'#2563eb',
        confirmButtonText: isDel?'<i class="fas fa-trash"></i>&nbsp;Ya, Hapus':(form.dataset.confirmBtn||'Ya, Lanjutkan'),
    }, function(){ form._swalOk=true; form.submit(); });
}, true);
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
