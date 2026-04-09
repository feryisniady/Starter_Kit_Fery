<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Akun — <?= esc(app_setting('app_name')) ?></title>
    <?php if(app_setting('favicon_path')): ?>
        <link rel="icon" href="<?= base_url(esc(app_setting('favicon_path'))) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/_main/css/auth.css">
    <style>
        /* Password Strength */
        .pw-rules {
            margin-top: 10px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }
        .pw-rule {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            color: #9ca3af;
            transition: color .2s;
        }
        .pw-rule i {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 1.5px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            transition: all .2s;
            flex-shrink: 0;
        }
        .pw-rule.ok { color: #16a34a; }
        .pw-rule.ok i {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }
        .pw-strength-bar {
            margin-top: 10px;
            height: 5px;
            background: #f3f4f6;
            border-radius: 99px;
            overflow: hidden;
        }
        .pw-strength-fill {
            height: 100%;
            border-radius: 99px;
            width: 0;
            transition: width .3s, background .3s;
        }
        .pw-strength-label {
            font-size: 11px;
            margin-top: 5px;
            font-weight: 600;
        }
        .strength-0 { width:0; background:#e5e7eb; }
        .strength-1 { width:25%; background:#ef4444; }
        .strength-2 { width:50%; background:#f59e0b; }
        .strength-3 { width:75%; background:#3b82f6; }
        .strength-4 { width:100%; background:#16a34a; }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #dc2626;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: #991b1b;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-error i { margin-top: 1px; color: #dc2626; }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            color: #15803d;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-success i { margin-top: 1px; }
        .form-footer { display:flex; justify-content:space-between; align-items:center; margin-top:18px; font-size:13px; }
        .toggle-pw { background:none;border:none;cursor:pointer;color:#9ca3af;padding:0 8px; }
    </style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel">

    <div class="logo">
        <div class="logo-icon"><i class="fas fa-shield-halved"></i></div>
        <div class="logo-text">
            <span><?= esc(app_setting('org_name')) ?></span>
            <strong><?= esc(app_setting('org_short')) ?></strong>
        </div>
    </div>

    <div class="welcome">
        <p>Bergabung sekarang,</p>
        <h1>Buat Akun Baru</h1>
    </div>

    <?php if(session()->getFlashdata('error')): ?>
    <div class="alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <span><?= session()->getFlashdata('error') ?></span>
    </div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('success')): ?>
    <div class="alert-success">
        <i class="fas fa-circle-check"></i>
        <span><?= session()->getFlashdata('success') ?></span>
    </div>
    <?php endif; ?>

    <form action="/register" method="post" id="register-form">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>Nama Lengkap <span>*</span></label>
            <div class="input-wrap">
                <input type="text" name="name" value="<?= old('name') ?>"
                    placeholder="Masukkan nama lengkap" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label>Email <span>*</span></label>
            <div class="input-wrap">
                <input type="email" name="email" value="<?= old('email') ?>"
                    placeholder="Masukkan email" required>
            </div>
        </div>

        <div class="form-group">
            <label>Password <span>*</span></label>
            <div class="input-wrap">
                <input type="password" name="password" id="pw-input"
                    placeholder="Buat password" required>
                <button type="button" class="toggle-pw" onclick="togglePw('pw-input','pw-icon')">
                    <i class="fas fa-eye" id="pw-icon"></i>
                </button>
            </div>
            <!-- Strength bar -->
            <div class="pw-strength-bar"><div class="pw-strength-fill" id="pw-bar"></div></div>
            <div class="pw-strength-label" id="pw-label" style="color:#9ca3af"></div>
            <!-- Rules checklist -->
            <div class="pw-rules">
                <div class="pw-rule" id="rule-len"><i class="fas fa-check"></i> Min. 8 karakter</div>
                <div class="pw-rule" id="rule-upper"><i class="fas fa-check"></i> Huruf besar (A-Z)</div>
                <div class="pw-rule" id="rule-lower"><i class="fas fa-check"></i> Huruf kecil (a-z)</div>
                <div class="pw-rule" id="rule-num"><i class="fas fa-check"></i> Angka (0-9)</div>
                <div class="pw-rule" id="rule-special"><i class="fas fa-check"></i> Karakter khusus (!@#)</div>
            </div>
        </div>

        <div class="form-group" style="margin-top:14px">
            <label>Konfirmasi Password <span>*</span></label>
            <div class="input-wrap">
                <input type="password" name="confirm_password" id="pw-confirm"
                    placeholder="Ulangi password" required>
                <button type="button" class="toggle-pw" onclick="togglePw('pw-confirm','pw-confirm-icon')">
                    <i class="fas fa-eye" id="pw-confirm-icon"></i>
                </button>
            </div>
            <div id="confirm-msg" style="font-size:12px;margin-top:5px"></div>
        </div>

        <button type="submit" class="btn-login" id="btn-submit">
            <i class="fas fa-user-plus"></i> Buat Akun
        </button>

        <div class="form-footer">
            <span style="color:#6b7280">Sudah punya akun?</span>
            <a href="/login">Login di sini</a>
        </div>
    </form>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>
    <div class="right-content">
        <h2><?= esc(app_setting('app_name')) ?></h2>
        <p><?= esc(app_setting('app_tagline')) ?></p>
        <div style="margin-top:32px;display:flex;flex-direction:column;gap:14px">
            <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.8);font-size:14px">
                <div style="width:36px;height:36px;background:rgba(255,255,255,.1);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-shield-halved" style="color:#a5b4fc"></i>
                </div>
                Akses berbasis peran (RBAC)
            </div>
            <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.8);font-size:14px">
                <div style="width:36px;height:36px;background:rgba(255,255,255,.1);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-lock" style="color:#a5b4fc"></i>
                </div>
                Password terenkripsi aman
            </div>
            <div style="display:flex;align-items:center;gap:12px;color:rgba(255,255,255,.8);font-size:14px">
                <div style="width:36px;height:36px;background:rgba(255,255,255,.1);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fas fa-bell" style="color:#a5b4fc"></i>
                </div>
                Notifikasi real-time
            </div>
        </div>
    </div>
</div>

<script>
function togglePw(inputId, iconId) {
    var inp  = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    inp.type = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

var strengthLabels = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
var strengthColors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#16a34a'];

document.getElementById('pw-input').addEventListener('input', function() {
    var val     = this.value;
    var hasLen  = val.length >= 8;
    var hasUp   = /[A-Z]/.test(val);
    var hasLow  = /[a-z]/.test(val);
    var hasNum  = /[0-9]/.test(val);
    var hasSpc  = /[^A-Za-z0-9]/.test(val);

    setRule('rule-len',     hasLen);
    setRule('rule-upper',   hasUp);
    setRule('rule-lower',   hasLow);
    setRule('rule-num',     hasNum);
    setRule('rule-special', hasSpc);

    var score = [hasLen, hasUp, hasLow, hasNum, hasSpc].filter(Boolean).length;
    // Hitung kekuatan (tanpa special tetap bisa max 3)
    var strength = 0;
    if (val.length === 0) { strength = 0; }
    else if (score <= 2)  { strength = 1; }
    else if (score === 3) { strength = 2; }
    else if (score === 4) { strength = 3; }
    else                  { strength = 4; }

    var bar   = document.getElementById('pw-bar');
    var label = document.getElementById('pw-label');
    bar.className   = 'pw-strength-fill strength-' + strength;
    label.textContent = strength > 0 ? 'Kekuatan: ' + strengthLabels[strength] : '';
    label.style.color = strengthColors[strength];

    checkConfirm();
});

document.getElementById('pw-confirm').addEventListener('input', checkConfirm);

function setRule(id, ok) {
    var el = document.getElementById(id);
    el.classList.toggle('ok', ok);
}

function checkConfirm() {
    var pw  = document.getElementById('pw-input').value;
    var pw2 = document.getElementById('pw-confirm').value;
    var msg = document.getElementById('confirm-msg');
    if (!pw2) { msg.textContent = ''; return; }
    if (pw === pw2) {
        msg.textContent = '✓ Password cocok';
        msg.style.color = '#16a34a';
    } else {
        msg.textContent = '✗ Password tidak cocok';
        msg.style.color = '#dc2626';
    }
}
</script>

</body>
</html>
