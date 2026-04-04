<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <!-- Primary Meta Tags -->
  <title><?= esc(app_setting('app_name')) ?></title>
  <meta name="title" content="<?= esc(app_setting('app_name')) ?>" />
  <meta name="description" content="<?= esc(app_setting('app_tagline')) ?>" />
  <?php if(app_setting('favicon_path')): ?>
  <link rel="icon" href="<?= base_url(esc(app_setting('favicon_path'))) ?>">
  <?php endif; ?>

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="<?= base_url() ?>" />
  <meta property="og:title" content="<?= esc(app_setting('app_name')) ?>" />
  <meta property="og:description" content="<?= esc(app_setting('app_tagline')) ?>" />

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image" />
  <meta property="twitter:url" content="<?= base_url() ?>" />
  <meta property="twitter:title" content="<?= esc(app_setting('app_name')) ?>" />
  <meta property="twitter:description" content="<?= esc(app_setting('app_tagline')) ?>" />

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="/assets/_landing/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="/assets/_landing/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/_landing/vendor/aos/aos.css" rel="stylesheet">
  <link href="/assets/_landing/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="/assets/_landing/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="/assets/_landing/css/main.css" rel="stylesheet">

</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <div class="logo d-flex align-items-center">
        <?php if(app_setting('logo_path')): ?>
        <img src="<?= base_url(esc(app_setting('logo_path'))) ?>" alt="Logo" style="height:32px">
        <?php endif; ?>
        <span><?= esc(app_setting('org_name')) ?></span>
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
          <div class="col-lg-4 order-lg-last hero-img" data-aos="zoom-out" data-aos-delay="100"></div>
          <div class="col-lg-6 d-flex flex-column justify-content-center" data-aos="fade-in">
            <div data-aos="zoom-out">
              <h1><span><?= esc(app_setting('app_name')) ?></span></h1>
              <h5><?= esc(app_setting('app_tagline')) ?></h5>
              <div class="text-center text-lg-start">
                <a href="/login" class="btn btn-warning mt-3"><strong>Login</strong></a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28 " preserveAspectRatio="none">
        <defs>
          <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
        </defs>
        <g class="wave1"><use xlink:href="#wave-path" x="50" y="3"></use></g>
        <g class="wave2"><use xlink:href="#wave-path" x="50" y="0"></use></g>
        <g class="wave3"><use xlink:href="#wave-path" x="50" y="9"></use></g>
      </svg>

    </section>

    <section id="aplikasi" class="features section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Layanan</h2>
        <div><span>Layanan</span> <span class="description-title">Kami</span></div>
      </div>
      <div class="container">
        <div class="row gy-4">

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="features-item">
              <i class="bi bi-shield-check" style="color: #ffbb2c;"></i>
              <h3><a href="/dashboard" class="stretched-link">Dashboard</a></h3>
            </div>
          </div>

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="features-item">
              <i class="bi bi-people" style="color: #5578ff;"></i>
              <h3><a href="#" class="stretched-link">Manajemen Pengguna</a></h3>
            </div>
          </div>

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="features-item">
              <i class="bi bi-list-check" style="color: #e80368;"></i>
              <h3><a href="#" class="stretched-link">Manajemen Peran</a></h3>
            </div>
          </div>

          <div class="col-md-3 col-md-4" data-aos="fade-up" data-aos-delay="400">
            <div class="features-item">
              <i class="bi bi-activity" style="color: #e361ff;"></i>
              <h3><a href="#" class="stretched-link">Activity Log</a></h3>
            </div>
          </div>

        </div>
      </div>
    </section>

    <section id="tentang">
      <div class="container section-title" data-aos="fade-up">
        <h2>Tentang</h2>
        <div><span><?= esc(app_setting('app_name')) ?></span></div>
      </div>
      <div class="container-fluid">
        <div class="row d-flex">
          <div class="col-xl-5 col-md-4 d-flex justify-content-center align-items-stretch" data-aos="fade-right"></div>
          <div class="col-xl-6 col-md-8 icon-boxes d-flex flex-column align-items-stretch justify-content-center py-5 px-lg-5" data-aos="fade-left">
            <h1 class="fw-bold"><?= esc(app_setting('app_name')) ?></h1>
            <p><?= esc(app_setting('app_tagline')) ?></p>
            <p>Dikelola oleh <strong><?= esc(app_setting('org_name')) ?></strong>.</p>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer id="footer" class="footer">
    <div class="container copyright text-center">
      <p>
        <?php $footerText = app_setting('footer_text'); ?>
        <?php if($footerText): ?>
        <?= esc($footerText) ?>
        <?php else: ?>
        <sup>&copy; <?= date('Y') ?></sup>
        <span><a href="<?= esc(app_setting('org_website','#')) ?>" target="_blank"><?= esc(app_setting('org_name')) ?></a></span>
        &mdash; <?= esc(app_setting('app_name')) ?> v<?= esc(app_setting('app_version')) ?>
        <?php endif; ?>
      </p>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="/assets/_landing/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/_landing/vendor/aos/aos.js"></script>
  <script src="/assets/_landing/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="/assets/_landing/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="/assets/_landing/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="/assets/_landing/js/main.js"></script>

</body>

</html>
