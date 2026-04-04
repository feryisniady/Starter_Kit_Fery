<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'RBAC Starter' ?></title>

    <!-- Stisla CSS -->
    <!-- General CSS Files -->
    <link rel="stylesheet" href="/assets/modules/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/modules/fontawesome/css/all.min.css">

    <link rel="stylesheet" href="/assets/modules/jqvmap/dist/jqvmap.min.css">
    <link rel="stylesheet" href="/assets/modules/summernote/summernote-bs4.css">
    <link rel="stylesheet" href="/assets/modules/owlcarousel2/dist/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="/assets/modules/owlcarousel2/dist/assets/owl.theme.default.min.css">

    <link rel="stylesheet" href="/assets/modules/bootstrap-daterangepicker/daterangepicker.css">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    

    <!-- Bootstrap & DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <!-- DataTables FixedHeader CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.2/css/fixedHeader.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

    <!-- Template CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/components.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.min.css">
</head>
<body>
    <div id="app">

        <!-- Sidebar -->
        <div class="main-sidebar sidebar-style-2">
            <aside id="sidebar-wrapper">
                <div class="sidebar-brand">
                    <a href="/dashboard">🔐 RBAC Starter</a>
                </div>
                <div class="sidebar-brand sidebar-brand-sm">
                    <a href="/dashboard">RB</a>
                </div>
                <ul class="sidebar-menu">
                    <li class="menu-header">Main Menu</li>
                    <?php foreach(getMenus() as $menu): ?>
                        <li class="<?= isActiveMenu($menu['url']) ? 'active' : '' ?>">
                            <a href="<?= $menu['url'] ?>" class="nav-link">
                                <i class="<?= $menu['icon'] ?>"></i>
                                <span><?= $menu['label'] ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </aside>
        </div>

        <!-- Main Content -->
        <div class="main-content">

            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg main-navbar sticky-top">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
                    </ul>
                </div>
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <i class="fas fa-user-circle fa-lg mr-1"></i>
                            <div class="d-sm-none d-lg-inline-block"><?= session()->get('user_name') ?></div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="/logout" class="btn btn-primary btn-lg btn-block btn-icon-split fw-bold">
                              <i class="fas fa-sign-out-alt"></i> Logout
                          </a>
                      </div>


                  </li>
              </ul>
          </nav>

          <!-- Content -->
          <div class="main-content-inner">
            <div class="container-fluid">
                <div class="row pt-4">
                    <div class="col-12">

                        <?php if(session()->getFlashdata('success')): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: '<?= session()->getFlashdata('success') ?>',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            });
                        </script>
                    <?php endif; ?>

                    <?php if(session()->getFlashdata('error')): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: '<?= session()->getFlashdata('error') ?>',
                            });
                        });
                    </script>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>

            </div>
        </div>
    </div>
</div>

</div>
</div>

<!-- Scripts -->
<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<!-- Inputmask CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>

<!-- Bootstrap dan Plugin yang Bergantung pada jQuery -->
<script src="/assets/modules/popper.js"></script>
<script src="/assets/modules/bootstrap/js/bootstrap.min.js"></script>

<!-- JS Libraies -->
<script src="/assets/modules/summernote/summernote-bs4.js"></script>

<!-- Nicescroll dan Plugin Sidebar -->
<script src="/assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
<script src="/assets/modules/moment.min.js"></script>
<script src="/assets/js/stisla.js"></script>

<!-- PENTING: LOAD TEMPLATE JS FILE DARI STISLA -->
<script src="/assets/js/scripts.js"></script>
<script src="/assets/js/custom.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables FixedHeader JS -->
<script src="https://cdn.datatables.net/fixedheader/3.2.2/js/dataTables.fixedHeader.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>


<!-- Moment.js (wajib untuk daterangepicker) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="/assets/modules/bootstrap-daterangepicker/daterangepicker.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="/assets/modules/jquery-selectric/jquery.selectric.min.js"></script>

<!-- CHART.JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>



<!-- jQuery UI untuk Autocomplete -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.10.1/sweetalert2.min.js"></script>

<!-- Konfirmasi Hapus Global -->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Flash message otomatis
        <?php if(session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?= session()->getFlashdata('success') ?>',
            timer: 2000,
            showConfirmButton: false
        });
    <?php endif; ?>

    <?php if(session()->getFlashdata('error')): ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?= session()->getFlashdata('error') ?>',
    });
<?php endif; ?>

    // AJAX Delete
document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var url = this.dataset.url;
        var row = this.closest('tr');

        Swal.fire({
            icon: 'warning',
            title: 'Yakin hapus?',
            text: 'Data yang dihapus tidak bisa dikembalikan!',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(res) {
                        if (res.status === 'success') {
                            row.remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat menghapus data.'
                        });
                    }
                });
            }
        });
    });
});

});
</script>

</body>
</html>