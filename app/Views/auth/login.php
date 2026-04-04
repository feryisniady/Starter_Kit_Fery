<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — SIP Inspektorat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/_main/css/auth.css">
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel">
    <div class="logo">
        <div class="logo-icon">
            <i class="fas fa-shield-halved"></i>
        </div>
        <div class="logo-text">
            <span>Inspektorat Daerah</span>
            <strong>Kabupaten Sampang</strong>
        </div>
    </div>

    <div class="welcome">
        <p>Selamat datang di,</p>
        <h1>Sistem Informasi Pengawasan APIP..!</h1>
    </div>

    <?php if(session()->getFlashdata('error')): ?>
    <div class="alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <?= session()->getFlashdata('error') ?>
    </div>
    <?php endif; ?>

    <form action="/login" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Email <span>*</span></label>
            <div class="input-wrap">
                <input type="email" name="email"
                    value="<?= old('email') ?>"
                    placeholder="Masukkan email anda"
                    required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label>Password <span>*</span></label>
            <div class="input-wrap">
                <input type="password" name="password"
                    id="password-input"
                    placeholder="Masukkan password anda"
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

        <button type="submit" class="btn-login">
            <i class="fas fa-right-to-bracket"></i> Login
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
    <div class="circle circle-3"></div>

    <div class="right-content">
        <h2>Sistem Informasi Pengawasan APIP</h2>
        <p>Platform digital terintegrasi untuk mendukung pelaksanaan tugas pengawasan Inspektorat Daerah Kabupaten Sampang</p>

        <!-- Ilustrasi SVG -->
        <svg class="illustration" viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
            <!-- Meja -->
            <rect x="60" y="220" width="280" height="12" rx="6" fill="rgba(255,255,255,.15)"/>
            <rect x="80" y="232" width="12" height="50" rx="4" fill="rgba(255,255,255,.1)"/>
            <rect x="308" y="232" width="12" height="50" rx="4" fill="rgba(255,255,255,.1)"/>

            <!-- Monitor kiri -->
            <rect x="70" y="140" width="110" height="80" rx="8" fill="rgba(255,255,255,.1)" stroke="rgba(255,255,255,.2)" stroke-width="1.5"/>
            <rect x="78" y="148" width="94" height="56" rx="4" fill="#1e3a8a"/>
            <rect x="115" y="220" width="20" height="8" rx="2" fill="rgba(255,255,255,.15)"/>
            <!-- Konten monitor -->
            <rect x="84" y="154" width="60" height="6" rx="3" fill="rgba(255,255,255,.4)"/>
            <rect x="84" y="164" width="80" height="4" rx="2" fill="rgba(255,255,255,.2)"/>
            <rect x="84" y="172" width="70" height="4" rx="2" fill="rgba(255,255,255,.2)"/>
            <rect x="84" y="180" width="75" height="4" rx="2" fill="rgba(255,255,255,.2)"/>
            <rect x="84" y="190" width="40" height="10" rx="4" fill="#3b82f6"/>

            <!-- Monitor kanan -->
            <rect x="220" y="130" width="120" height="90" rx="8" fill="rgba(255,255,255,.1)" stroke="rgba(255,255,255,.2)" stroke-width="1.5"/>
            <rect x="228" y="138" width="104" height="64" rx="4" fill="#1e3a8a"/>
            <rect x="270" y="220" width="20" height="8" rx="2" fill="rgba(255,255,255,.15)"/>
            <!-- Konten monitor -->
            <circle cx="248" cy="160" r="14" fill="rgba(255,255,255,.15)"/>
            <path d="M242 160 L247 165 L255 155" stroke="#60a5fa" stroke-width="2.5" fill="none" stroke-linecap="round"/>
            <rect x="268" y="152" width="50" height="5" rx="2" fill="rgba(255,255,255,.3)"/>
            <rect x="268" y="161" width="40" height="4" rx="2" fill="rgba(255,255,255,.2)"/>
            <rect x="228" y="178" width="30" height="16" rx="4" fill="#22c55e" opacity=".8"/>
            <rect x="264" y="178" width="30" height="16" rx="4" fill="#f59e0b" opacity=".8"/>
            <rect x="300" y="178" width="25" height="16" rx="4" fill="#ef4444" opacity=".8"/>

            <!-- Orang kiri -->
            <circle cx="130" cy="105" r="18" fill="#fbbf24"/>
            <rect x="112" y="124" width="36" height="50" rx="8" fill="#3b82f6"/>
            <rect x="100" y="128" width="12" height="35" rx="6" fill="#3b82f6"/>
            <rect x="148" y="128" width="12" height="35" rx="6" fill="#3b82f6"/>
            <rect x="115" y="174" width="13" height="40" rx="6" fill="#1e40af"/>
            <rect x="132" y="174" width="13" height="40" rx="6" fill="#1e40af"/>

            <!-- Orang kanan -->
            <circle cx="270" cy="95" r="18" fill="#f9a8d4"/>
            <rect x="252" y="114" width="36" height="50" rx="8" fill="#ef4444"/>
            <rect x="240" y="118" width="12" height="35" rx="6" fill="#ef4444"/>
            <rect x="288" y="118" width="12" height="35" rx="6" fill="#ef4444"/>
            <rect x="255" y="164" width="13" height="45" rx="6" fill="#b91c1c"/>
            <rect x="272" y="164" width="13" height="45" rx="6" fill="#b91c1c"/>

            <!-- Dekorasi -->
            <circle cx="50" cy="80" r="8" fill="rgba(96,165,250,.3)"/>
            <circle cx="360" cy="100" r="12" fill="rgba(34,197,94,.2)"/>
            <circle cx="190" cy="40" r="6" fill="rgba(251,191,36,.3)"/>
        </svg>

        <div class="features">
            <div class="feature-item">
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                <span>Manajemen PKPT & Surat Perintah Tugas</span>
            </div>
            <div class="feature-item">
                <div class="icon"><i class="fas fa-magnifying-glass-chart"></i></div>
                <span>Monitoring Pelaksanaan Pengawasan</span>
            </div>
            <div class="feature-item">
                <div class="icon"><i class="fas fa-file-shield"></i></div>
                <span>Pengelolaan Temuan & Tindak Lanjut</span>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var input = document.getElementById('password-input');
    var icon = document.getElementById('pw-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>

</body>
</html>