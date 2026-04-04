<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <!-- Primary Meta Tags -->
  <title>RBAC-Starter</title>
  <meta name="title" content="SiTemanAPIP" />
  <meta name="description" content="Sistem Informasi Pengawasan APIP" />

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= base_url();?>" />
  <meta property="og:title" content="SiTemanAPIP" />
  <meta property="og:description" content="Sistem Informasi Pengawasan APIP" />
  <!-- <meta property="og:image" content="<?= base_url();?>dist/assets/img/logo/trunojoyo.png" /> -->

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image" />
  <meta property="twitter:url" content="<?= base_url();?>" />
  <meta property="twitter:title" content="SiTemanAPIP" />
  <meta property="twitter:description" content="Sistem Informasi Pengawasan APIP" />
  <!-- <meta property="twitter:image" content="<?= base_url();?>dist/assets/img/logo/trunojoyo.png" /> -->

  <!-- Meta Tags Generated with https://metatags.io -->

  <!-- Favicons -->
  <!-- <link rel="shortcut icon" href="<?= base_url();?>dist/assets/img/logo/trunojoyo.png" type="image/x-icon"> -->
  <!-- <link href="<?= base_url();?>dist/beranda/assets/img/apple-touch-icon.png" rel="apple-touch-icon"> -->

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="/assets/_landing/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/_landing/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/_landing/vendor/aos/aos.css" rel="stylesheet">
  <link href="/assets/_landing/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="/assets/_landing/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Bootstrap & DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
  <!-- DataTables FixedHeader CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.2/css/fixedHeader.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.bootstrap5.min.css">

  <!-- Main CSS File -->
  <link href="/assets/_landing/css/main.css" rel="stylesheet">

  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <div class="logo d-flex align-items-center">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <!-- <img class="img-fluid" src="<?= base_url();?>dist/assets/img/logo/trunojoyo.png" alt=""> -->
        <span>Inpektorat Daerah Kabupaten Sampang</span>
      </div>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a class="nav-link active" href="#hero">Beranda</a></li>
          <li><a class="nav-link" href="#aplikasi">Layanan</a></li>
          <li><a class="nav-link" href="#tentang">Tentang</a></li>
        </ul>
        
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

    </div>
  </header>

  <main class="main">
    <section id="hero" class="hero section dark-background">
      <div class="container">
        <div class="row gy-4 justify-content-between">
          <div class="col-lg-4 order-lg-last hero-img" data-aos="zoom-out" data-aos-delay="100">
            <!-- <img src="<?= base_url()?>dist/assets/img/logo/alun_alun.jpg" class="img-fluid animated rounded-circle" alt=""> -->
          </div>
          <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-in">
            <div data-aos="zoom-out">
              <h1><span>`SiTemanAPIP`</span></h1>
              <h5>"Sistem Informasi Pengawasan APIP"</h5>
              <div class="text-center text-lg-start">
                <button class="btn btn-warning scrollto mt-3" data-bs-toggle="modal" data-bs-target="#loginModal"><strong>Login Admin</strong>
                </button>

              </div>
            </div>
          </div>
        </div>
      </div>

      <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28 " preserveAspectRatio="none">
        <defs>
          <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
        </defs>
        <g class="wave1">
          <use xlink:href="#wave-path" x="50" y="3"></use>
        </g>
        <g class="wave2">
          <use xlink:href="#wave-path" x="50" y="0"></use>
        </g>
        <g class="wave3">
          <use xlink:href="#wave-path" x="50" y="9"></use>
        </g>
      </svg>

    </section>
    <section id="aplikasi" class="features section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Layanan</h2>
        <div><span>Layanan</span> <span class="description-title">Kami</span></div>
      </div><!-- End Section Title -->
      <div class="container">
        <div class="row gy-4">

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="features-item">
              <i class="bi bi-database" style="color: #ffbb2c;"></i>
              <h3><a href="<?= base_url('mcsp');?>" class="stretched-link" target="_blank">Pelaporan MCSP KPK</a></h3>
            </div>
          </div><!-- End Feature Item -->

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="features-item">
              <i class="bi bi-infinity" style="color: #5578ff;"></i>
              <h3><a href="https://sampangkab.go.id/sipetjut" target="_blank" class="stretched-link">SIPETJUT</a></h3>
            </div>
          </div><!-- End Feature Item -->

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="features-item">
              <i class="bi bi-copy" style="color: #e80368;"></i>
              <h3><a href="https://sampangkab.go.id/epermenperin" class="stretched-link" target="_blank">TLHP BPK</a></h3>
            </div>
          </div><!-- End Feature Item -->

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="400">
            <div class="features-item">
              <i class="bi bi-nut" style="color: #e361ff;"></i>
              <h3><a href="https://sampangkab.go.id/klikapip" class="stretched-link" target="_blank">Klinik Konsultasi APIP</a></h3>
            </div>
          </div><!-- End Feature Item -->

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="500">
            <div class="features-item">
              <i class="bi bi-copy" style="color: #47aeff;"></i>
              <h3><a href="https://sampangkab.go.id/wbs" class="stretched-link" target="_blank">Pengaduan Berkadar Pengawasan (WBS)</a></h3>
            </div>
          </div><!-- End Feature Item -->

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="600">
            <div class="features-item">
              <i class="bi bi-bookmarks" style="color: #ffa76e;"></i>
              <h3><a href="#" class="stretched-link">Simulasi Try Out PKN STAN</a></h3>
            </div>
          </div><!-- End Feature Item -->

          <!-- End Feature Item -->

        </div>

      </div>
    </section>
    <section id="tentang">
      <div class="container section-title" data-aos="fade-up">
        <h2>Tentang</h2>
        <div><span>SiTemanAPIP</span> <span class="description-title"></span></div>
      </div><!-- End Section Title -->
      <div class="container-fluid">
        <div class="row d-flex">
          <div class="col-xl-5 col-md-4 d-flex justify-content-center align-items-stretch" data-aos="fade-right">
            <!-- <img class="img-fluid" src="<?= base_url();?>dist/assets/img/logo/trunojoyo.png" style="height:300px" alt="trunojoyo"> -->
          </div>
          <div class="col-xl-6 col-md-8 icon-boxes d-flex flex-column align-items-stretch justify-content-center py-5 px-lg-5" data-aos="fade-left">
            <h1 class="fw-bold">SiTemanAPIP ?</h1>
            <p>
              Pengawasan yang efektif dan efisien merupakan salah satu pilar utama dalam tata kelola pemerintahan yang baik. Sebagai pengawas di pemerintahan, Aparat Pengawasan Intern Pemerintah (APIP) dalam hal ini Inspektorat Daerah Kabupaten Sampang harus dapat memberikan keyakinan yang memadai atas ketaatan, kehematan, dan efektivitas pencapaian tujuan penyelenggaraan tugas dan fungsi instansi pemerintah.
            </p>
          </div>
        </div>
      </div>
    </section>
    
  </main>



  <footer id="footer" class="footer">

    <div class="container copyright text-center">
      <p><sup>&copy; 2025</sup> <span><a href="https://inspektorat.sampangkab.go.id" target="_blank">Inspektorat Daerah Kabupaten Sampang</a></span> 
        <strong class="px-1 sitename"></strong>
      </p>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <script type='text/javascript'>
    var baseURL= "<?php echo base_url();?>";
  </script>

  <!-- JQUERY HARUS DIMUAT DULU -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- SWEETALERT2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Vendor JS Files -->
  <script src="/assets/_landing/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/_landing/vendor/php-email-form/validate.js"></script>
  <script src="/assets/_landing/vendor/aos/aos.js"></script>
  <script src="/assets/_landing/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="/assets/_landing/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="/assets/_landing/vendor/swiper/swiper-bundle.min.js"></script>

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

  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

</body>

</html>