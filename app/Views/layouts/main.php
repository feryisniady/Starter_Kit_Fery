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
  <!-- Admin Extra (override DataTables default) — HARUS paling akhir -->
  <link rel="stylesheet" href="/assets/_main/css/admin-extra.css?v=6">

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
<?php if(session()->getFlashdata('error')): ?>
<script>
  window._flashError = "<?= addslashes(session()->getFlashdata('error')) ?>";
</script>
<?php endif; ?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="/assets/_main/js/admin.js?v=6"></script>

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
    // Notifikasi Sukses (Sudah benar)
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

<!-- Scripts Per Halaman -->
<?= $this->renderSection('scripts') ?>

</body>
</html>
