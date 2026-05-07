<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="csrf-token-name" content="<?= csrf_token() ?>">
  <title><?= esc($title ?? 'Dashboard') ?> — <?= esc(app_setting('app_name')) ?></title>
  <?php if(app_setting('favicon_path')): ?>
    <link rel="icon" href="<?= base_url(esc(app_setting('favicon_path'))) ?>">
  <?php endif; ?>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.min.css">
  <!-- Admin CSS -->
  <link rel="stylesheet" href="/assets/_main/css/admin.css">
  <!-- DataTables + Buttons CSS — load sebelum admin-extra agar override kita menang -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="/assets/_main/modules/select2/dist/css/select2.min.css">
  <!-- Admin Extra (override DataTables default) — HARUS paling akhir -->
  <link rel="stylesheet" href="/assets/_main/css/admin-extra.css?v=6">
  <!-- Quill WYSIWYG Editor -->
  <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">
  <style>
    /* Quill Editor — tampilan selaras dengan form admin */
    .ql-container { font-family: inherit; font-size: 13px; border-radius: 0 0 6px 6px; }
    .ql-toolbar { border-radius: 6px 6px 0 0; background: #f8fafc; border-color: #e2e8f0 !important; }
    .ql-container.ql-snow { border-color: #e2e8f0 !important; }
    .ql-editor { min-height: 80px; max-height: 320px; overflow-y: auto; line-height: 1.6; }
    .ql-editor.ql-blank::before { color: #94a3b8; font-style: italic; }
    /* Label WYSIWYG badge */
    .wysiwyg-wrap { position: relative; }
    .wysiwyg-badge {
      display: inline-block; font-size: 9px; font-weight: 600; letter-spacing: .3px;
      background: #ede9fe; color: #6d28d9; padding: 1px 6px; border-radius: 4px;
      margin-left: 6px; vertical-align: middle;
    }
  </style>
  <style>
    /* ═══════════════════════════════════════════════════════════
       Select2 — Global Theme (selaras dengan .form-control admin)
       ═══════════════════════════════════════════════════════════ */

    /* Container */
    .select2-container { width: 100% !important; }

    /* ── Normal (.form-control) — height ~38px ── */
    .select2-container .select2-selection--single {
      height: 38px !important;
      border: 1.5px solid #e2e8f0;
      border-radius: 8px;
      background: #f8fafc;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
      line-height: 36px;
      font-size: 13.5px;
      color: #1e293b;
      padding-left: 13px;
      padding-right: 28px;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
      color: #94a3b8;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 36px;
      right: 8px;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
      line-height: 36px;
      font-size: 15px;
      color: #94a3b8;
      margin-right: 4px;
    }

    /* ── Small (.select2-sm → dari form-control-sm) — height 28px ── */
    .select2-sm .select2-selection--single {
      height: 28px !important;
      border-radius: 6px;
    }
    .select2-sm .select2-selection__rendered {
      line-height: 26px !important;
      font-size: 12px !important;
      padding-left: 8px !important;
    }
    .select2-sm .select2-selection__arrow { height: 26px !important; }
    .select2-sm .select2-selection__clear { line-height: 26px !important; }

    /* ── Focus / Open state ── */
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open  .select2-selection--single {
      border-color: #2563eb !important;
      background: #fff !important;
      box-shadow: 0 0 0 3px rgba(37,99,235,.1) !important;
    }

    /* ── Dropdown panel ── */
    .select2-dropdown {
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      box-shadow: 0 8px 28px rgba(0,0,0,.12);
      font-size: 13.5px;
      overflow: hidden;
      z-index: 9999;
    }
    .select2-search--dropdown { padding: 8px 10px 4px; }
    .select2-search--dropdown .select2-search__field {
      border: 1.5px solid #e2e8f0;
      border-radius: 7px;
      font-size: 13px;
      padding: 6px 10px;
      outline: none;
      width: 100%;
      background: #f8fafc;
    }
    .select2-search--dropdown .select2-search__field:focus {
      border-color: #2563eb;
      background: #fff;
      box-shadow: 0 0 0 2px rgba(37,99,235,.1);
    }
    .select2-results__options { padding: 4px 0; }
    .select2-results__option {
      padding: 7px 13px;
      font-size: 13px;
      color: #374151;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background: #2563eb;
      color: #fff;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
      background: #eff6ff;
      color: #1d4ed8;
    }
    .select2-results__option--disabled {
      color: #94a3b8 !important;
      font-style: italic;
    }
    .select2-container--default .select2-results__message {
      color: #94a3b8;
      font-size: 12px;
      padding: 8px 13px;
    }
  </style>

  <!-- CSS Tambahan Per Halaman -->
  <?= $this->renderSection('styles') ?>
</head>
<body>
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
      <?php if(app_setting('logo_path')): ?>
        <img src="<?= base_url(esc(app_setting('logo_path'))) ?>"
        alt="Logo" style="height:36px;width:36px;object-fit:contain;border-radius:8px">
      <?php else: ?>
        <div class="brand-icon">
          <i class="fas fa-shield-halved"></i>
        </div>
      <?php endif; ?>
      <div class="brand-text">
        <span><?= esc(app_setting('app_name')) ?></span>
        <strong><?= esc(app_setting('org_short')) ?></strong>
      </div>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav">
      <?php
      $menus      = getMenus();
      $currentUrl = '/' . service('request')->getUri()->getPath();

      // Build tree
      $menuTree = buildMenuTree($menus);

      // Group by section (ONLY ROOT LEVEL)
      $menuGroups = [];
      foreach ($menuTree as $menu) {
        $section = $menu['section'] ?? 'main';
        $menuGroups[$section][] = $menu;
      }
      ?>

      <?php foreach($menuGroups as $section => $items): ?>

        <div class="nav-section-title"><?= esc($section) ?></div>

        <?= renderMenuTree($items, $currentUrl) ?>

      <?php endforeach; ?>
    </nav>

    <!-- Footer Sidebar -->
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar">
          <?php $avatarPath = session()->get('user_avatar'); ?>
          <?php if($avatarPath): ?>
            <img src="<?= base_url(esc($avatarPath)) ?>" alt="avatar"
            style="width:100%;height:100%;object-fit:cover;border-radius:50%">
          <?php else: ?>
            <?= esc(strtoupper(substr(session()->get('user_name') ?? 'A', 0, 1))) ?>
          <?php endif; ?>
        </div>
        <div class="user-info">
          <div class="name"><?= esc(session()->get('user_name')) ?></div>
          <div class="role"><?= esc(getUserRoleLabel()) ?></div>
        </div>
        <a href="/logout" class="user-logout" title="Logout">
          <i class="fas fa-right-from-bracket"></i>
        </a>
      </div>
    </div>

  </aside>

  <!-- ===== MAIN WRAPPER ===== -->
  <div class="main-wrapper" id="mainWrapper">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
        <i class="fas fa-bars"></i>
      </div>

      <div class="topbar-breadcrumb">
        <a href="/dashboard" style="color:#94a3b8">
          <i class="fas fa-house" style="font-size:12px"></i>
        </a>
        <i class="fas fa-chevron-right"></i>
        <span class="current"><?= esc($title ?? 'Dashboard') ?></span>
      </div>

      <div class="topbar-actions">
        <!-- Notifikasi Bell -->
        <div class="notif-wrap" id="notifWrap">
          <button class="topbar-btn" id="notifBtn" title="Notifikasi" onclick="toggleNotifDropdown()">
            <i class="fas fa-bell"></i>
            <span class="notif-badge" id="notifBadge" style="display:none">0</span>
          </button>

          <!-- Dropdown -->
          <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header">
              <span><i class="fas fa-bell"></i> Notifikasi</span>
              <button class="notif-read-all" id="btnReadAll" onclick="readAllNotif()" title="Tandai semua dibaca">
                <i class="fas fa-check-double"></i> Semua dibaca
              </button>
            </div>
            <div class="notif-list" id="notifList">
              <div class="notif-empty">
                <i class="fas fa-bell-slash"></i>
                <span>Tidak ada notifikasi</span>
              </div>
            </div>
            <div class="notif-footer">
              <a href="/notifications">Lihat semua notifikasi</a>
            </div>
          </div>
        </div>

        <div class="topbar-divider"></div>

        <!-- User dropdown -->
        <div class="topbar-user">
          <a href="/profile" style="text-decoration:none">
            <div class="topbar-avatar">
              <?php if($avatarPath): ?>
                <img src="<?= base_url(esc($avatarPath)) ?>" alt="avatar"
                style="width:100%;height:100%;object-fit:cover;border-radius:50%">
              <?php else: ?>
                <?= esc(strtoupper(substr(session()->get('user_name') ?? 'A', 0, 1))) ?>
              <?php endif; ?>
            </div>
          </a>
          <div class="topbar-user-info">
            <div class="name"><?= esc(session()->get('user_name')) ?></div>
            <div class="role"><?= esc(getUserRoleLabel()) ?></div>
          </div>
        </div>
      </div>
    </header>

    <!-- Page Content -->
    <main class="page-content">

      <?= $this->include('partials/_alert') ?>
      <?= $this->renderSection('content') ?>

    </main>

    <!-- Footer -->
    <footer class="page-footer">
      <?php
      $footerText = app_setting('footer_text');
      if ($footerText): ?>
        <span><?= esc($footerText) ?></span>
      <?php else: ?>
        <span>&copy; <?= date('Y') ?> <strong><?= esc(app_setting('org_name')) ?></strong>. All rights reserved.</span>
        <span><?= esc(app_setting('app_name')) ?> v<?= esc(app_setting('app_version')) ?> — Powered by CodeIgniter 4</span>
      <?php endif; ?>
    </footer>

  </div>

  <!-- Flash Message Script -->
  <?php if(session()->getFlashdata('success')): ?>
  <script>
    window._flashSuccess = "<?= addslashes(session()->getFlashdata('success')) ?>";
  </script>
<?php endif; ?>
<?php if(session()->getFlashdata('success_modal')): ?>
<script>
  window._flashSuccessModal = "<?= addslashes(session()->getFlashdata('success_modal')) ?>";
</script>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
<script>
  window._flashError = "<?= addslashes(session()->getFlashdata('error')) ?>";
</script>
<?php endif; ?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="/assets/_main/modules/select2/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="/assets/_main/js/admin.js?v=6"></script>

<script>
/* ═══════════════════════════════════════════════════════════════════
   initSelect2(target) — Global Select2 initializer
   target: selector string, DOM element, atau jQuery object
   ═══════════════════════════════════════════════════════════════════ */
window.initSelect2 = function(target) {
  $(target).each(function() {
    var $s = $(this);
    if ($s.hasClass('select2-hidden-accessible')) return; // sudah di-init

    var optCount = $s.find('option').length;
    var isSmall  = $s.hasClass('form-control-sm') || parseInt($s.css('height')) <= 30;

    $s.select2({
      placeholder         : $s.find('option[value=""]').first().text() || '— Pilih —',
      allowClear          : $s.prop('required') ? false : true,
      width               : '100%',
      dropdownParent      : $('body'),
      containerCssClass   : isSmall ? 'select2-sm' : '',
      // Tampilkan kotak cari HANYA bila opsi > 6
      minimumResultsForSearch: optCount > 6 ? 0 : Infinity,
      language: {
        noResults : function() { return 'Tidak ditemukan'; },
        searching : function() { return 'Mencari…';        },
      }
    });
  });
};

$(document).ready(function() {
  // Auto-init semua select.form-control kecuali yang opt-out
  initSelect2('select.form-control:not(.no-select2)');
});
</script>

<script>
// CSRF Setup
  $.ajaxSetup({
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

// Sidebar Toggle
  // Sidebar Toggle
  function toggleSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const wrapper  = document.getElementById('mainWrapper');
    const overlay  = document.getElementById('sidebarOverlay');
    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
      sidebar.classList.toggle('mobile-open');
      overlay.classList.toggle('show');
    } else {
      sidebar.classList.toggle('collapsed');
      wrapper.classList.toggle('collapsed');
    }
  }

// Mobile sidebar
  function toggleSidebarMobile() {
    document.getElementById('sidebar').classList.toggle('open');
  }

// Submenu Toggle
  function toggleSubmenu(el) {
    el.classList.toggle('open');
    const submenu = el.nextElementSibling;
    if(submenu) submenu.classList.toggle('open');
  }

// Flash Messages
  $(document).ready(function() {
    // Notifikasi Sukses Toast
    if(window._flashSuccess) {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: window._flashSuccess,
        timer: 2500,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
        timerProgressBar: true
      });
    }
    // Notifikasi Sukses Modal (untuk aksi penting seperti ajukan)
    if(window._flashSuccessModal) {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        html: window._flashSuccessModal,
        confirmButtonText: 'OK',
        confirmButtonColor: '#4f46e5',
        customClass: { popup: 'swal-wide' }
      });
    }

    // UPDATE: Notifikasi Error/Validasi menjadi Toast
    if(window._flashError) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
            html: window._flashError, // Gunakan 'html' agar tag <br> dari controller terbaca
            timer: 5000,               // Beri waktu lebih lama (5 detik) untuk membaca error
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            timerProgressBar: true
          });
    }

    // AJAX Delete ditangani di admin.js (DT-aware reload)
  });

// ── Global Swal Confirm Helper ────────────────────────────────────────────
  function swalConfirm(opts, onConfirm) {
    Swal.fire(Object.assign({
      icon: 'question',
      title: 'Konfirmasi',
      showCancelButton: true,
      confirmButtonColor: '#4f46e5',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Ya, Lanjutkan',
      cancelButtonText: 'Batal',
      reverseButtons: true,
    }, opts)).then(function(r) { if (r.isConfirmed) onConfirm(); });
  }

// Intercept form[data-confirm] on submit (capture phase)
  document.addEventListener('submit', function(e) {
    var form = e.target;
    if (!form.dataset.confirm || form._swalOk) return;
    e.preventDefault();
    var isDel  = form.dataset.confirmType === 'delete';
    swalConfirm({
      title: form.dataset.confirmTitle || (isDel ? 'Hapus Data?' : 'Konfirmasi'),
      html:  form.dataset.confirm,
      icon:  isDel ? 'warning' : 'question',
      confirmButtonColor: isDel ? '#ef4444' : '#4f46e5',
      confirmButtonText:  isDel ? '<i class="fas fa-trash"></i>&nbsp;Ya, Hapus' : (form.dataset.confirmBtn || 'Ya, Lanjutkan'),
    }, function() { form._swalOk = true; form.submit(); });
  }, true);
</script>

<!-- Notifikasi CSS -->
<style>
/* ---- Notif Wrap ---- */
.notif-wrap { position: relative; }
.notif-badge {
  position: absolute;
  top: 2px; right: 2px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  min-width: 16px;
  height: 16px;
  border-radius: 99px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 3px;
  line-height: 1;
  pointer-events: none;
}

/* ---- Dropdown ---- */
.notif-dropdown {
  display: none;
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 340px;
  background: white;
  border-radius: 14px;
  box-shadow: 0 8px 32px rgba(0,0,0,.14);
  border: 1px solid #e2e8f0;
  z-index: 1100;
  overflow: hidden;
}
.notif-dropdown.open { display: block; }
.notif-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-bottom: 1px solid #f1f5f9;
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}
.notif-read-all {
  background: none;
  border: none;
  font-size: 11px;
  color: #2563eb;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: background .15s;
}
.notif-read-all:hover { background: #eff6ff; }
.notif-list { max-height: 320px; overflow-y: auto; }
.notif-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px 16px;
  border-bottom: 1px solid #f8fafc;
  cursor: pointer;
  transition: background .15s;
  text-decoration: none;
  color: inherit;
}
.notif-item:hover { background: #f8fafc; }
.notif-item.unread { background: #f0f9ff; }
.notif-item.unread:hover { background: #e0f2fe; }
.notif-icon {
  width: 34px; height: 34px;
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: 14px;
  flex-shrink: 0;
  margin-top: 1px;
}
.notif-icon.info    { background: #eff6ff; color: #2563eb; }
.notif-icon.success { background: #f0fdf4; color: #16a34a; }
.notif-icon.warning { background: #fffbeb; color: #d97706; }
.notif-icon.danger  { background: #fef2f2; color: #dc2626; }
.notif-body { flex: 1; min-width: 0; }
.notif-title {
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.notif-msg {
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.notif-time { font-size: 11px; color: #94a3b8; margin-top: 3px; }
.notif-unread-dot {
  width: 7px; height: 7px;
  background: #2563eb;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 5px;
}
.notif-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 32px 16px;
  color: #94a3b8;
  font-size: 13px;
}
.notif-empty i { font-size: 28px; opacity: .4; }
.notif-footer {
  padding: 10px 16px;
  border-top: 1px solid #f1f5f9;
  text-align: center;
}
.notif-footer a {
  font-size: 12px;
  color: #2563eb;
  text-decoration: none;
  font-weight: 500;
}
.notif-footer a:hover { text-decoration: underline; }
</style>

<!-- Notifikasi JS -->
<script>
  var _notifOpen = false;

  function toggleNotifDropdown() {
    _notifOpen = !_notifOpen;
    document.getElementById('notifDropdown').classList.toggle('open', _notifOpen);
    if (_notifOpen) fetchNotif();
  }

// Tutup dropdown kalau klik di luar
  document.addEventListener('click', function(e) {
    var wrap = document.getElementById('notifWrap');
    if (wrap && !wrap.contains(e.target)) {
      _notifOpen = false;
      document.getElementById('notifDropdown').classList.remove('open');
    }
  });

  var _notifIconMap = {
    info:    'fa-circle-info',
    success: 'fa-circle-check',
    warning: 'fa-triangle-exclamation',
    danger:  'fa-circle-xmark',
  };

  function fetchNotif() {
    $.get('/notifications/fetch', function(res) {
    // Update badge
      var badge = document.getElementById('notifBadge');
      if (res.unread > 0) {
        badge.style.display = 'flex';
        badge.textContent   = res.unread > 99 ? '99+' : res.unread;
      } else {
        badge.style.display = 'none';
      }

    // Render list
      var list = document.getElementById('notifList');
      if (!res.notifications || res.notifications.length === 0) {
        list.innerHTML = '<div class="notif-empty"><i class="fas fa-bell-slash"></i><span>Tidak ada notifikasi</span></div>';
        return;
      }

      var html = '';
      res.notifications.forEach(function(n) {
        var icon    = _notifIconMap[n.type] || 'fa-circle-info';
        var unread  = parseInt(n.is_read) === 0;
        var href    = n.url ? '/notifications/read/' + n.id : 'javascript:void(0)';
        var onclick = !n.url ? 'markRead(' + n.id + ');return false;' : '';

        html += '<a href="' + href + '" class="notif-item' + (unread ? ' unread' : '') + '"'
        + (onclick ? ' onclick="' + onclick + '"' : '')
        + ' data-id="' + n.id + '">'
        + '<div class="notif-icon ' + n.type + '"><i class="fas ' + icon + '"></i></div>'
        + '<div class="notif-body">'
        +   '<div class="notif-title">' + escHtml(n.title) + '</div>'
        +   (n.message ? '<div class="notif-msg">' + escHtml(n.message) + '</div>' : '')
        +   '<div class="notif-time">' + n.time_ago + '</div>'
        + '</div>'
        + (unread ? '<div class="notif-unread-dot"></div>' : '')
        + '</a>';
      });
      list.innerHTML = html;
    });
  }

  function markRead(id) {
    $.post('/notifications/read/' + id, {<?= csrf_token() ?>: '<?= csrf_hash() ?>'}, function() {
      $('[data-id="' + id + '"]').removeClass('unread').find('.notif-unread-dot').remove();
      fetchNotif();
    });
  }

  function readAllNotif() {
    $.post('/notifications/read-all', {<?= csrf_token() ?>: '<?= csrf_hash() ?>'}, function() {
      fetchNotif();
    });
  }

  function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

// Poll badge tiap 30 detik
  $(document).ready(function() {
    fetchNotif();
    setInterval(fetchNotif, 30000);
  });
</script>

<!-- Quill WYSIWYG JS -->
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
/**
 * initWysiwyg(container)
 * Inisialisasi semua [data-wysiwyg] di dalam container menjadi Quill editor.
 * Dipanggil lazy saat card di-expand agar tidak boros memory.
 */
  window._quillInstances = window._quillInstances || {};

  const WYSIWYG_TOOLBAR = [
    [{ header: [2, 3, false] }],
    ['bold', 'italic', 'underline'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ indent: '-1' }, { indent: '+1' }],
    ['clean']
    ];

  function initWysiwyg(container) {
    container = container || document;
    container.querySelectorAll('textarea[data-wysiwyg]').forEach(function(ta) {
        if (ta._quillInit || ta.disabled) return; // Skip jika sudah di-init atau read-only
        ta._quillInit = true;

        const id = ta.id || ('qeditor-' + Math.random().toString(36).slice(2));
        if (!ta.id) ta.id = id; // Simpan id agar mudah di-tracking
        const height = ta.getAttribute('data-wysiwyg-height') || '120px';

        // 1. Buat wrapper div
        const wrapper = document.createElement('div');
        // Styling tambahan agar background rapi dan menyatu dengan form
        wrapper.style.cssText = 'margin-bottom: 0; background: #ffffff;';

        // 2. Buat editor div
        const editorDiv = document.createElement('div');
        editorDiv.id = id + '-editor';
        editorDiv.style.minHeight = height;
        // Agar font di dalam editor mengikuti font website (bukan font default bawaan Quill)
        editorDiv.style.fontFamily = 'inherit'; 

        wrapper.appendChild(editorDiv);
        ta.parentNode.insertBefore(wrapper, ta);
        
        // Sembunyikan textarea asli
        ta.style.display = 'none';

        // 3. Inisialisasi Quill
        const quill = new Quill(editorDiv, {
            theme: 'snow', // Wajib ada quill.snow.css
            modules: { toolbar: WYSIWYG_TOOLBAR },
            placeholder: ta.placeholder || 'Ketik di sini...',
          });

        // 4. Pre-fill data lama (jika sedang mode edit)
        const existingVal = ta.value.trim();
        if (existingVal) {
            // Cek apakah isinya mengandung HTML tag
          if (existingVal.includes('<') && existingVal.includes('>')) {
            quill.root.innerHTML = existingVal;
          } else {
                // Jika plain text lama — convert newline ke tag <p> dan <br>
            quill.root.innerHTML = '<p>' + existingVal.replace(/\n\n+/g, '</p><p>').replace(/\n/g, '<br>') + '</p>';
          }
        }

        // 5. Auto-Sync saat user mengetik
        quill.on('text-change', function() {
            // Hindari menyimpan string kosong berformat HTML saat dihapus habis
          ta.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
        });

        // Simpan instance ke global object
        window._quillInstances[id] = quill;
      });
  }

// 6. BACKUP SYNC: Pastikan isi Quill masuk ke Textarea sesaat sebelum Submit Form
// Ini penting untuk mencegah data kosong jika event 'text-change' terlewat oleh browser
  document.addEventListener('submit', function(e) {
    var form = e.target;
    form.querySelectorAll('textarea[data-wysiwyg]').forEach(function(ta) {
      var id = ta.id;
      if (id && window._quillInstances[id]) {
        var quill = window._quillInstances[id];
        ta.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
      }
    });
  }, true);

// 7. Auto-init untuk elemen yang langsung tampil (TIDAK lazy-load)
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('textarea[data-wysiwyg]:not(.wysiwyg-lazy)').forEach(function(ta) {
        // Cek secara mendalam apakah form ini posisinya hidden atau tampil
      var parent = ta.parentElement;
      var isHidden = false;
      while (parent && parent !== document.body) {
        if (window.getComputedStyle(parent).display === 'none') {
          isHidden = true;
          break;
        }
        parent = parent.parentElement;
      }
      
        // Jika form tidak hidden, langsung jadikan Quill editor
      if (!isHidden) {
        initWysiwyg(ta.closest('form') || ta.parentElement);
      }
    });
  });
</script>

<!-- Scripts Per Halaman -->
<?= $this->renderSection('scripts') ?>

</body>
</html>
