<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title><?= $title ?? 'Dashboard' ?> — SIP Inspektorat</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.min.css">
  <!-- Admin CSS -->
  <link rel="stylesheet" href="/assets/_main/css/admin.css">
  <link rel="stylesheet" href="/assets/_main/css/admin-extra.css">

  <!-- CSS Tambahan Per Halaman -->
  <?= $this->renderSection('styles') ?>
</head>
<body>
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
  <!-- ===== SIDEBAR ===== -->
  <aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
      <div class="brand-icon">
        <i class="fas fa-shield-halved"></i>
      </div>
      <div class="brand-text">
        <span>Inspektorat Daerah</span>
        <strong>Kab. Sampang</strong>
      </div>
    </div>

    <!-- Nav -->
    <nav class="sidebar-nav">
      <?php
      $menus      = getMenus();
      $currentUrl = '/' . service('request')->getUri()->getPath();

    // Pisahkan parent dan children
      $parents  = array_filter($menus, fn($m) => empty($m['parent_id']));
      $children = array_filter($menus, fn($m) => !empty($m['parent_id']));

    // Group parent berdasarkan section
      $menuGroups = [];
      foreach ($parents as $menu) {
        $section = $menu['section'] ?? 'main';
        $menuGroups[$section][] = $menu;
      }
      ?>

      <?php foreach($menuGroups as $section => $items): ?>

        <div class="nav-section-title"><?= $section ?></div>

        <?php foreach($items as $menu): ?>
          <?php
        // Ambil children dari semua menu (bukan hanya per section)
          $menuChildren = array_filter($children, fn($c) => $c['parent_id'] == $menu['id']);

        // Cek active — aktif jika URL sama atau salah satu child aktif
          $isActive = $currentUrl === $menu['url'];
          foreach ($menuChildren as $child) {
            if ($currentUrl === $child['url'] || str_starts_with($currentUrl, $child['url'] . '/')) {
              $isActive = true;
              break;
            }
          }
          ?>

          <?php if(!empty($menuChildren)): ?>
            <!-- Parent dengan submenu -->
            <div class="nav-item">
              <?php if($menu['url'] === '#' || empty($menu['url'])): ?>
                <!-- Parent HANYA container — seluruh area toggle submenu -->
                <div class="nav-link <?= $isActive ? 'open' : '' ?>"
                  onclick="toggleSubmenu(this)">
                  <div class="nav-icon"><i class="<?= $menu['icon'] ?>"></i></div>
                  <span class="nav-label"><?= $menu['label'] ?></span>
                  <i class="fas fa-chevron-right nav-arrow"></i>
                </div>

              <?php else: ?>
                <!-- Parent PUNYA URL — link + tombol toggle terpisah -->
                <div class="nav-link <?= $isActive ? 'open' : '' ?>">
                  <a href="<?= $menu['url'] ?>"
                    style="display:flex;align-items:center;gap:12px;
                    flex:1;color:inherit;text-decoration:none">
                    <div class="nav-icon"><i class="<?= $menu['icon'] ?>"></i></div>
                    <span class="nav-label"><?= $menu['label'] ?></span>
                  </a>
                  <i class="fas fa-chevron-right nav-arrow"
                  onclick="toggleSubmenu(this.closest('.nav-link'))"
                  style="padding:6px;cursor:pointer"></i>
                </div>
              <?php endif; ?>

              <div class="submenu <?= $isActive ? 'open' : '' ?>">
                <?php foreach($menuChildren as $child): ?>
                  <?php $childActive = $currentUrl === $child['url']
                  || str_starts_with($currentUrl, $child['url'] . '/'); ?>
                  <a href="<?= $child['url'] ?>"
                    class="submenu-link <?= $childActive ? 'active' : '' ?>">
                    <?= $child['label'] ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>

          <?php else: ?>
            <!-- Menu tanpa submenu -->
            <div class="nav-item">
              <a href="<?= $menu['url'] ?>"
                class="nav-link <?= $isActive ? 'active' : '' ?>">
                <div class="nav-icon"><i class="<?= $menu['icon'] ?>"></i></div>
                <span class="nav-label"><?= $menu['label'] ?></span>
              </a>
            </div>
          <?php endif; ?>

        <?php endforeach; ?>
      <?php endforeach; ?>

    </nav>

    <!-- Footer Sidebar -->
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar">
          <?= strtoupper(substr(session()->get('user_name') ?? 'A', 0, 1)) ?>
        </div>
        <div class="user-info">
          <div class="name"><?= session()->get('user_name') ?></div>
          <div class="role">
            <?php if(hasRole('superadmin')): ?>Super Admin
            <?php elseif(hasRole('admin')): ?>Administrator
            <?php elseif(hasRole('inspektur')): ?>Inspektur
            <?php elseif(hasRole('irban')): ?>Irban
            <?php elseif(hasRole('auditor')): ?>Auditor
          <?php else: ?>User<?php endif; ?>
        </div>
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
      <span class="current"><?= $title ?? 'Dashboard' ?></span>
    </div>

    <div class="topbar-actions">
      <!-- Notifikasi -->
      <button class="topbar-btn" title="Notifikasi">
        <i class="fas fa-bell"></i>
        <span class="notif-dot"></span>
      </button>

      <div class="topbar-divider"></div>

      <!-- User dropdown -->
      <div class="topbar-user">
        <div class="topbar-avatar">
          <?= strtoupper(substr(session()->get('user_name') ?? 'A', 0, 1)) ?>
        </div>
        <div class="topbar-user-info">
          <div class="name"><?= session()->get('user_name') ?></div>
          <div class="role">
            <?php if(hasRole('superadmin')): ?>Super Admin
            <?php elseif(hasRole('admin')): ?>Administrator
          <?php else: ?>User<?php endif; ?>
        </div>
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
  <span>&copy; <?= date('Y') ?> <strong>Inspektorat Daerah Kabupaten Sampang</strong>. All rights reserved.</span>
  <span>SIP v2.0 — Powered by CodeIgniter 4</span>
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
<script src="/assets/_main/js/admin.js"></script>

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

    // AJAX Delete
    $(document).on('click', '.btn-delete', function() {
      var url = $(this).data('url');
      var row = $(this).closest('tr');
      Swal.fire({
        icon: 'warning',
        title: 'Yakin hapus?',
        text: 'Data yang dihapus tidak bisa dikembalikan!',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: 'Batal'
      }).then(function(result) {
        if (result.isConfirmed) {
          $.ajax({
            url: url,
            type: 'GET',
            success: function(res) {
              if(res.status === 'success') {
                row.fadeOut(300, function() { $(this).remove(); });
                Swal.fire({
                  icon: 'success',
                  title: 'Dihapus!',
                  text: res.message,
                  timer: 1500,
                  showConfirmButton: false,
                  toast: true,
                  position: 'top-end'
                });
              }
            },
            error: function() {
              Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan.' });
            }
          });
        }
      });
    });
  });
</script>

<!-- Scripts Per Halaman -->
<?= $this->renderSection('scripts') ?>

</body>
</html>
