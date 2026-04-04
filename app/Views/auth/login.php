<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — <?= esc(app_setting('app_name')) ?></title>
    <?php if(app_setting('favicon_path')): ?>
    <link rel="icon" href="<?= base_url(esc(app_setting('favicon_path'))) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/_main/css/auth.css">
    <style>
        /* Lockout Alert */
        .alert-lockout {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            border-left: 4px solid #dc2626;
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 20px;
        }
        .lockout-icon {
            font-size: 22px;
            color: #dc2626;
            margin-top: 2px;
        }
        .lockout-body strong {
            display: block;
            color: #991b1b;
            font-size: 14px;
            margin-bottom: 2px;
        }
        .lockout-body p {
            color: #6b7280;
            font-size: 13px;
            margin: 0 0 6px;
        }
        .lockout-timer {
            font-size: 13px;
            color: #374151;
        }
        /* Attempt Progress Bar */
        .attempt-bar {
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .attempt-bar-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .attempt-bar-track {
            background: #f3f4f6;
            border-radius: 99px;
            height: 6px;
            overflow: hidden;
        }
        .attempt-bar-fill {
            background: linear-gradient(90deg, #f59e0b, #dc2626);
            height: 100%;
            border-radius: 99px;
            transition: width .4s ease;
        }
        /* Ilustrasi kecil di atas form login (panel kiri) */
        .left-illustration {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .left-illus-ring {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            border: 3px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,.1);
        }
        .left-illus-ring img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        /* Services grid — 2 kolom full width di panel kanan */
        .services-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: rgba(255,255,255,.45);
            margin-bottom: 14px;
        }
        /* Services card grid — 2 kolom */
        .services-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            width: 100%;
        }
        .service-card {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 12px;
            padding: 12px 14px;
            text-decoration: none;
            color: inherit;
            transition: background .2s, border-color .2s;
            position: relative;
        }
        .service-card:hover {
            background: rgba(255,255,255,.13);
            border-color: rgba(255,255,255,.25);
        }
        .service-icon {
            width: 36px;
            height: 36px;
            background: rgba(99,102,241,.3);
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .service-icon i { color: #a5b4fc; font-size: 15px; }
        .service-body { flex: 1; min-width: 0; }
        .service-name {
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,.9);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .service-desc {
            font-size: 11px;
            color: rgba(255,255,255,.5);
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .service-badge {
            font-size: 10px;
            padding: 2px 7px;
            border-radius: 99px;
            background: rgba(251,191,36,.2);
            color: #fde68a;
            flex-shrink: 0;
            align-self: flex-start;
        }
        .service-badge-pub {
            background: rgba(34,197,94,.15);
            color: #86efac;
        }
        /* Disabled state */
        .btn-login:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            opacity: .8;
        }
        input:disabled {
            background: #f3f4f6 !important;
            cursor: not-allowed;
        }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel" <?php if(app_setting('login_bg_path')): ?>style="padding-top:28px"<?php endif; ?>>

    <?php if(app_setting('login_bg_path')): ?>
    <div class="left-illustration">
        <div class="left-illus-ring">
            <img src="<?= base_url(esc(app_setting('login_bg_path'))) ?>" alt="Ilustrasi">
        </div>
    </div>
    <?php endif; ?>

    <div class="logo">
        <div class="logo-icon">
            <i class="fas fa-shield-halved"></i>
        </div>
        <div class="logo-text">
            <span><?= esc(app_setting('login_title') ?: app_setting('org_name')) ?></span>
            <strong><?= esc(app_setting('login_subtitle') ?: app_setting('org_short')) ?></strong>
        </div>
    </div>

    <div class="welcome">
        <p>Selamat datang di,</p>
        <h1><?= esc(app_setting('login_tagline') ?: app_setting('app_tagline')) ?></h1>
    </div>

    <?php
        $lockoutUntil = session()->getFlashdata('lockout_until');
        $attempts     = (int) session()->getFlashdata('attempts');
        $isLocked     = $lockoutUntil && ($lockoutUntil - time()) > 0;
        $maxAttempts  = 5;
    ?>

    <?php if($isLocked): ?>
    <!-- Alert Lockout -->
    <div class="alert-lockout" id="alert-lockout">
        <div class="lockout-icon"><i class="fas fa-lock"></i></div>
        <div class="lockout-body">
            <strong>Akses Diblokir Sementara</strong>
            <p>Terlalu banyak percobaan login yang gagal.</p>
            <div class="lockout-timer">
                Coba lagi dalam: <span id="countdown" style="font-weight:700;color:#dc2626"></span>
            </div>
        </div>
    </div>
    <?php elseif(session()->getFlashdata('error')): ?>
    <!-- Alert Error biasa -->
    <div class="alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
    <?php if($attempts > 0): ?>
    <!-- Progress bar percobaan -->
    <div class="attempt-bar">
        <div class="attempt-bar-label">
            <span>Percobaan ke-<?= $attempts ?> dari <?= $maxAttempts ?></span>
            <span style="color:#dc2626;font-weight:600"><?= $maxAttempts - $attempts ?>x tersisa</span>
        </div>
        <div class="attempt-bar-track">
            <div class="attempt-bar-fill" style="width:<?= ($attempts / $maxAttempts) * 100 ?>%"></div>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <form action="/login" method="post" id="login-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Email <span>*</span></label>
            <div class="input-wrap">
                <input type="email" name="email"
                    value="<?= old('email') ?>"
                    placeholder="Masukkan email anda"
                    <?= $isLocked ? 'disabled' : '' ?>
                    required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label>Password <span>*</span></label>
            <div class="input-wrap">
                <input type="password" name="password"
                    id="password-input"
                    placeholder="Masukkan password anda"
                    <?= $isLocked ? 'disabled' : '' ?>
                    required>
                <button type="button" class="toggle-pw" onclick="togglePassword()">
                    <i class="fas fa-eye" id="pw-icon"></i>
                </button>
            </div>
            <label class="show-pw" onclick="togglePassword()">
                <i class="fas fa-eye" style="font-size:12px;color:#9ca3af"></i>
                Show Password
            </label>
        </div>

        <button type="submit" class="btn-login" id="btn-login" <?= $isLocked ? 'disabled' : '' ?>>
            <i class="fas fa-right-to-bracket"></i>
            <?= $isLocked ? 'Akses Diblokir' : 'Login' ?>
        </button>

        <div class="form-footer">
            <span style="color:#6b7280;font-size:13px">
                Butuh bantuan? Hubungi Admin
            </span>
            <a href="#">Lupa password?</a>
        </div>
    </form>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="right-content">
        <h2><?= esc(app_setting('login_tagline') ?: app_setting('app_name')) ?></h2>
        <p><?= esc(app_setting('login_desc') ?: app_setting('app_tagline')) ?></p>

        <?php if(!empty($services)): ?>
        <p class="services-label">Daftar Layanan</p>
        <div class="services-grid">
            <?php foreach($services as $svc): ?>
            <a href="<?= esc($svc['url'] ?: '#') ?>" class="service-card"
               <?= ($svc['url'] && str_starts_with($svc['url'], 'http')) ? 'target="_blank"' : '' ?>>
                <div class="service-icon"><i class="<?= esc($svc['icon']) ?>"></i></div>
                <div class="service-body">
                    <div class="service-name"><?= esc($svc['name']) ?></div>
                    <?php if($svc['description']): ?>
                    <div class="service-desc"><?= esc($svc['description']) ?></div>
                    <?php endif; ?>
                </div>
                <span class="service-badge <?= $svc['require_login'] ? '' : 'service-badge-pub' ?>">
                    <?= $svc['require_login'] ? 'Login' : 'Publik' ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePassword() {
    var input = document.getElementById('password-input');
    var icon  = document.getElementById('pw-icon');
    if (input.type === 'password') {
        input.type    = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type    = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Countdown lockout timer
<?php if($isLocked): ?>
(function() {
    var lockoutUntil = <?= (int) $lockoutUntil ?>;
    var countdown    = document.getElementById('countdown');
    var btnLogin     = document.getElementById('btn-login');

    function updateTimer() {
        var remaining = lockoutUntil - Math.floor(Date.now() / 1000);
        if (remaining <= 0) {
            // Waktu habis — reload halaman
            window.location.reload();
            return;
        }
        var m = Math.floor(remaining / 60);
        var s = remaining % 60;
        countdown.textContent = (m > 0 ? m + ' menit ' : '') + s + ' detik';
    }

    updateTimer();
    var timer = setInterval(updateTimer, 1000);
})();
<?php endif; ?>
</script>

</body>
</html>