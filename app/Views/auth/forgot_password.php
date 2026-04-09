<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password — <?= esc(app_setting('app_name')) ?></title>
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
            max-width: 420px;
        }
        .auth-card-icon {
            width: 56px; height: 56px;
            background: #eff6ff;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 20px;
            font-size: 22px;
            color: #2563eb;
        }
        .auth-card h2 { font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 6px; }
        .auth-card p  { font-size: 14px; color: #64748b; margin: 0 0 24px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display:block; font-size:13px; font-weight:600; color:#374151; margin-bottom:6px; }
        .form-group input {
            width: 100%; padding: 10px 14px;
            border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 14px; color: #1e293b; outline: none;
            transition: border-color .2s;
            box-sizing: border-box;
        }
        .form-group input:focus { border-color: #2563eb; }
        .btn-submit {
            width: 100%; padding: 12px;
            background: #2563eb; color: #fff;
            border: none; border-radius: 10px;
            font-size: 15px; font-weight: 600;
            cursor: pointer; transition: background .2s;
        }
        .btn-submit:hover { background: #1d4ed8; }
        .back-link { text-align:center; margin-top:18px; font-size:13px; }
        .back-link a { color:#2563eb; text-decoration:none; font-weight:500; }
        .alert-error {
            background:#fef2f2; border:1px solid #fecaca;
            border-left:4px solid #dc2626; border-radius:10px;
            padding:12px 16px; font-size:13px; color:#991b1b;
            margin-bottom:18px; display:flex; align-items:flex-start; gap:10px;
        }
        .alert-error i { color:#dc2626; margin-top:1px; }
        .alert-success {
            background:#f0fdf4; border:1px solid #bbf7d0;
            border-left:4px solid #16a34a; border-radius:10px;
            padding:12px 16px; font-size:13px; color:#15803d;
            margin-bottom:18px; display:flex; align-items:flex-start; gap:10px;
        }
        .alert-success i { margin-top:1px; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-card-icon">
        <i class="fas fa-key"></i>
    </div>
    <h2>Lupa Password?</h2>
    <p>Masukkan email Anda dan kami akan mengirimkan link untuk reset password.</p>

    <?php if(session()->getFlashdata('error')): ?>
    <div class="alert-error">
        <i class="fas fa-circle-exclamation"></i>
        <span><?= esc(session()->getFlashdata('error')) ?></span>
    </div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('success')): ?>
    <div class="alert-success">
        <i class="fas fa-circle-check"></i>
        <span><?= session()->getFlashdata('success') ?></span>
    </div>
    <?php endif; ?>

    <form action="/forgot-password" method="post">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Alamat Email</label>
            <input type="email" name="email" value="<?= old('email') ?>"
                placeholder="nama@email.com" required autofocus>
        </div>
        <button type="submit" class="btn-submit">
            <i class="fas fa-paper-plane"></i> Kirim Link Reset
        </button>
    </form>

    <div class="back-link">
        <a href="/login"><i class="fas fa-arrow-left" style="font-size:11px"></i> Kembali ke Login</a>
    </div>
</div>

</body>
</html>
