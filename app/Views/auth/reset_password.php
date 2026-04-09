<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password — <?= esc(app_setting('app_name')) ?></title>
    <?php if(app_setting('favicon_path')): ?>
        <link rel="icon" href="<?= base_url(esc(app_setting('favicon_path'))) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/_main/css/auth.css">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; background:#f1f5f9; }
        .auth-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,.1);
            padding: 40px 36px;
            width: 100%;
            max-width: 440px;
        }
        .auth-card-icon {
            width: 56px; height: 56px;
            background: #f0fdf4;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            font-size: 22px;
            color: #16a34a;
        }
        .auth-card h2 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 6px; }
        .auth-card p  { font-size: 14px; color: #64748b; margin: 0 0 24px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
        .input-wrap { position:relative; display:flex; align-items:center; }
        .input-wrap input {
            width: 100%; padding: 10px 42px 10px 14px;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 14px; color: #1e293b; outline: none;
            transition: border-color .2s; box-sizing: border-box;
        }
        .input-wrap input:focus { border-color: #16a34a; }
        .toggle-pw { position:absolute; right:12px; background:none; border:none; cursor:pointer; color:#9ca3af; padding:0; }

        /* Password rules */
        .pw-rules { margin-top: 10px; display:grid; grid-template-columns:1fr 1fr; gap:6px; }
        .pw-rule { display:flex; align-items:center; gap:7px; font-size:12px; color:#9ca3af; transition:color .2s; }
        .pw-rule i { width:16px;height:16px;border-radius:50%;border:1.5px solid #d1d5db;display:flex;align-items:center;justify-content:center;font-size:8px;transition:all .2s;flex-shrink:0; }
        .pw-rule.ok { color:#16a34a; }
        .pw-rule.ok i { background:#16a34a;border-color:#16a34a;color:#fff; }
        .pw-strength-bar { margin-top:10px;height:5px;background:#f3f4f6;border-radius:99px;overflow:hidden; }
        .pw-strength-fill { height:100%;border-radius:99px;width:0;transition:width .3s,background .3s; }
        .pw-strength-label { font-size:11px;margin-top:5px;font-weight:600; }
        .strength-0{width:0;background:#e5e7eb}.strength-1{width:25%;background:#ef4444}
        .strength-2{width:50%;background:#f59e0b}.strength-3{width:75%;background:#3b82f6}
        .strength-4{width:100%;background:#16a34a}

        .btn-submit {
            width:100%; padding:12px;
            background:#16a34a; color:#fff;
            border:none; border-radius:10px;
            font-size:15px; font-weight:600;
            cursor:pointer; transition:background .2s;
            margin-top:8px;
        }
        .btn-submit:hover { background:#15803d; }
        .back-link { text-align:center; margin-top:18px; font-size:13px; }
        .back-link a { color:#2563eb; text-decoration:none; font-weight:500; }
        .alert-error {
            background:#fef2f2; border:1px solid #fecaca;
            border-left:4px solid #dc2626; border-radius:10px;
            padding:12px 16px; font-size:13px; color:#991b1b;
            margin-bottom:18px; display:flex; align-items:flex-start; gap:10px;
        }
        .alert-error i { color:#dc2626; margin-top:1px; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-card-icon">
        <i class="fas fa-lock-open"></i>
    </div>
    <h2>Buat Password Baru</h2>
    <p>Password harus kuat — minimal 8 karakter, huruf besar, huruf kecil, dan angka.</p>

    <?php if(session()->getFlashdata('error')): ?>
    <div class="alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <span><?= esc(session()->getFlashdata('error')) ?></span>
    </div>
    <?php endif; ?>

    <form action="/reset-password" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= esc($token) ?>">

        <div class="form-group">
            <label>Password Baru <span style="color:#dc2626">*</span></label>
            <div class="input-wrap">
                <input type="password" name="password" id="pw-input"
                    placeholder="Buat password baru" required>
                <button type="button" class="toggle-pw" onclick="togglePw('pw-input','pw-icon')">
                    <i class="fas fa-eye" id="pw-icon"></i>
                </button>
            </div>
            <div class="pw-strength-bar"><div class="pw-strength-fill" id="pw-bar"></div></div>
            <div class="pw-strength-label" id="pw-label" style="color:#9ca3af"></div>
            <div class="pw-rules">
                <div class="pw-rule" id="rule-len"><i class="fas fa-check"></i> Min. 8 karakter</div>
                <div class="pw-rule" id="rule-upper"><i class="fas fa-check"></i> Huruf besar (A-Z)</div>
                <div class="pw-rule" id="rule-lower"><i class="fas fa-check"></i> Huruf kecil (a-z)</div>
                <div class="pw-rule" id="rule-num"><i class="fas fa-check"></i> Angka (0-9)</div>
                <div class="pw-rule" id="rule-special"><i class="fas fa-check"></i> Karakter khusus (!@#)</div>
            </div>
        </div>

        <div class="form-group" style="margin-top:14px">
            <label>Konfirmasi Password Baru <span style="color:#dc2626">*</span></label>
            <div class="input-wrap">
                <input type="password" name="confirm_password" id="pw-confirm"
                    placeholder="Ulangi password baru" required>
                <button type="button" class="toggle-pw" onclick="togglePw('pw-confirm','pw-confirm-icon')">
                    <i class="fas fa-eye" id="pw-confirm-icon"></i>
                </button>
            </div>
            <div id="confirm-msg" style="font-size:12px;margin-top:5px"></div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="fas fa-check"></i> Simpan Password Baru
        </button>
    </form>

    <div class="back-link">
        <a href="/login"><i class="fas fa-arrow-left" style="font-size:11px"></i> Kembali ke Login</a>
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
    var val    = this.value;
    var hasLen = val.length >= 8;
    var hasUp  = /[A-Z]/.test(val);
    var hasLow = /[a-z]/.test(val);
    var hasNum = /[0-9]/.test(val);
    var hasSpc = /[^A-Za-z0-9]/.test(val);

    setRule('rule-len',     hasLen);
    setRule('rule-upper',   hasUp);
    setRule('rule-lower',   hasLow);
    setRule('rule-num',     hasNum);
    setRule('rule-special', hasSpc);

    var score = [hasLen, hasUp, hasLow, hasNum, hasSpc].filter(Boolean).length;
    var strength = val.length === 0 ? 0 : score <= 2 ? 1 : score === 3 ? 2 : score === 4 ? 3 : 4;

    var bar = document.getElementById('pw-bar');
    var lbl = document.getElementById('pw-label');
    bar.className     = 'pw-strength-fill strength-' + strength;
    lbl.textContent   = strength > 0 ? 'Kekuatan: ' + strengthLabels[strength] : '';
    lbl.style.color   = strengthColors[strength];

    checkConfirm();
});

document.getElementById('pw-confirm').addEventListener('input', checkConfirm);

function setRule(id, ok) {
    document.getElementById(id).classList.toggle('ok', ok);
}

function checkConfirm() {
    var pw  = document.getElementById('pw-input').value;
    var pw2 = document.getElementById('pw-confirm').value;
    var msg = document.getElementById('confirm-msg');
    if (!pw2) { msg.textContent = ''; return; }
    msg.textContent = pw === pw2 ? '✓ Password cocok' : '✗ Password tidak cocok';
    msg.style.color = pw === pw2 ? '#16a34a' : '#dc2626';
}
</script>

</body>
</html>
