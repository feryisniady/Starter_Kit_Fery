<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Profil Saya</h1>
        <p>Kelola informasi akun dan keamanan Anda</p>
    </div>
</div>

<div class="profile-layout">

    <!-- Kolom kiri: Avatar -->
    <div class="profile-sidebar">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-wrap">
                    <?php if($user['avatar']): ?>
                    <img src="<?= base_url(esc($user['avatar'])) ?>" alt="Avatar" class="profile-avatar">
                    <?php else: ?>
                    <div class="profile-avatar-initial">
                        <?= esc(strtoupper(substr($user['name'], 0, 1))) ?>
                    </div>
                    <?php endif; ?>
                    <label for="avatarInput" class="avatar-edit-btn" title="Ganti foto">
                        <i class="fas fa-camera"></i>
                    </label>
                </div>

                <h3 class="profile-name"><?= esc($user['name']) ?></h3>
                <p class="profile-email"><?= esc($user['email']) ?></p>

                <?php
                    $db    = \Config\Database::connect();
                    $roles = $db->table('user_roles ur')
                                ->select('r.name')
                                ->join('roles r', 'r.id = ur.role_id')
                                ->where('ur.user_id', $user['id'])
                                ->get()->getResultArray();
                ?>
                <div class="profile-roles">
                    <?php foreach($roles as $role): ?>
                    <span class="role-badge"><?= esc(ucfirst($role['name'])) ?></span>
                    <?php endforeach; ?>
                </div>

                <?php if($user['avatar']): ?>
                <form action="/profile/delete-avatar" method="POST" class="mt-3">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger"
                        onclick="return confirm('Hapus foto profil?')">
                        <i class="fas fa-trash"></i> Hapus Foto
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info akun -->
        <div class="card mt-3">
            <div class="card-body">
                <p class="info-label">Status Akun</p>
                <p class="info-value">
                    <span class="badge-status <?= $user['status'] === 'active' ? 'active' : 'inactive' ?>">
                        <?= $user['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </p>
                <p class="info-label mt-2">Bergabung</p>
                <p class="info-value"><?= date('d M Y', strtotime($user['created_at'])) ?></p>
                <?php if($user['phone']): ?>
                <p class="info-label mt-2">Telepon</p>
                <p class="info-value"><?= esc($user['phone']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kolom kanan: Form -->
    <div class="profile-main">

        <!-- Form update profil -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-pen"></i> Informasi Profil</h3>
            </div>
            <div class="card-body">
                <form action="/profile/update" method="POST" enctype="multipart/form-data" id="profileForm">
                    <?= csrf_field() ?>
                    <!-- Hidden file input untuk avatar (trigger dari tombol kamera) -->
                    <input type="file" name="avatar" id="avatarInput" accept="image/*"
                           style="display:none" onchange="this.form.submit()">

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Nama Lengkap <span style="color:red">*</span></label>
                            <input type="text" name="name" value="<?= esc($user['name']) ?>"
                                   class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Nomor Telepon</label>
                            <input type="text" name="phone" value="<?= esc($user['phone'] ?? '') ?>"
                                   class="form-control" placeholder="08xx-xxxx-xxxx">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email <span style="color:red">*</span></label>
                        <input type="email" name="email" value="<?= esc($user['email']) ?>"
                               class="form-control" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Form ganti password -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-lock"></i> Ganti Password</h3>
            </div>
            <div class="card-body">
                <?php if(session()->getFlashdata('error_pw')): ?>
                <div class="alert-error-inline mb-3">
                    <i class="fas fa-circle-exclamation"></i>
                    <?= esc(session()->getFlashdata('error_pw')) ?>
                </div>
                <?php endif; ?>

                <form action="/profile/change-password" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label>Password Lama <span style="color:red">*</span></label>
                        <div class="input-pw-wrap">
                            <input type="password" name="old_password" id="old_pw"
                                   class="form-control" required placeholder="Masukkan password lama">
                            <button type="button" onclick="togglePw('old_pw','old_pw_icon')">
                                <i class="fas fa-eye" id="old_pw_icon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password Baru <span style="color:red">*</span></label>
                        <div class="input-pw-wrap">
                            <input type="password" name="new_password" id="new_pw"
                                   class="form-control" required placeholder="Buat password baru">
                            <button type="button" onclick="togglePw('new_pw','new_pw_icon')">
                                <i class="fas fa-eye" id="new_pw_icon"></i>
                            </button>
                        </div>
                        <!-- Strength bar -->
                        <div class="pw-strength-bar"><div class="pw-strength-fill" id="pw-bar"></div></div>
                        <div class="pw-strength-label" id="pw-label"></div>
                        <!-- Rules checklist -->
                        <div class="pw-rules">
                            <div class="pw-rule" id="rule-len"><i class="fas fa-check"></i> Min. 8 karakter</div>
                            <div class="pw-rule" id="rule-upper"><i class="fas fa-check"></i> Huruf besar (A-Z)</div>
                            <div class="pw-rule" id="rule-lower"><i class="fas fa-check"></i> Huruf kecil (a-z)</div>
                            <div class="pw-rule" id="rule-num"><i class="fas fa-check"></i> Angka (0-9)</div>
                            <div class="pw-rule" id="rule-special"><i class="fas fa-check"></i> Karakter khusus (!@#)</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password <span style="color:red">*</span></label>
                        <div class="input-pw-wrap">
                            <input type="password" name="confirm_password" id="conf_pw"
                                   class="form-control" required placeholder="Ulangi password baru">
                            <button type="button" onclick="togglePw('conf_pw','conf_pw_icon')">
                                <i class="fas fa-eye" id="conf_pw_icon"></i>
                            </button>
                        </div>
                        <div id="confirm-msg" style="font-size:12px;margin-top:5px"></div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-warning" id="btn-ganti-pw">
                            <i class="fas fa-key"></i> Ganti Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div><!-- end profile-main -->
</div>

<style>
/* Password strength & rules */
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
    width: 16px; height: 16px;
    border-radius: 50%;
    border: 1.5px solid #d1d5db;
    display: flex; align-items: center; justify-content: center;
    font-size: 8px;
    transition: all .2s;
    flex-shrink: 0;
}
.pw-rule.ok { color: #16a34a; }
.pw-rule.ok i { background: #16a34a; border-color: #16a34a; color: #fff; }
.pw-strength-bar {
    margin-top: 10px; height: 5px;
    background: #f3f4f6; border-radius: 99px; overflow: hidden;
}
.pw-strength-fill {
    height: 100%; border-radius: 99px; width: 0; transition: width .3s, background .3s;
}
.pw-strength-label { font-size: 11px; margin-top: 5px; font-weight: 600; }
.strength-0 { width:0; background:#e5e7eb; }
.strength-1 { width:25%; background:#ef4444; }
.strength-2 { width:50%; background:#f59e0b; }
.strength-3 { width:75%; background:#3b82f6; }
.strength-4 { width:100%; background:#16a34a; }

.profile-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 24px;
    align-items: flex-start;
}
.text-center { text-align: center; }
.mt-2 { margin-top: 8px; }
.mt-3 { margin-top: 16px; }
.mb-4 { margin-bottom: 24px; }
/* Avatar */
.avatar-wrap {
    position: relative;
    display: inline-block;
    margin-bottom: 16px;
}
.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e2e8f0;
}
.profile-avatar-initial {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: white;
    font-size: 40px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
.avatar-edit-btn {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 28px;
    height: 28px;
    background: #6366f1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    font-size: 12px;
    border: 2px solid white;
}
.profile-name  { font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
.profile-email { font-size: 13px; color: #64748b; margin-bottom: 12px; }
.profile-roles { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; }
.role-badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 99px;
    background: #ede9fe;
    color: #6d28d9;
    font-weight: 600;
}
.info-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px; }
.info-value { font-size: 14px; color: #374151; font-weight: 500; }
.badge-status { font-size: 12px; padding: 3px 10px; border-radius: 99px; font-weight: 600; }
.badge-status.active   { background: #dcfce7; color: #15803d; }
.badge-status.inactive { background: #fee2e2; color: #dc2626; }
/* Form */
.form-row-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.form-group { margin-bottom: 16px; }
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}
.form-control {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    color: #1e293b;
    box-sizing: border-box;
    transition: border .2s;
}
.form-control:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.1);
}
.input-pw-wrap { position: relative; }
.input-pw-wrap input { padding-right: 40px; }
.input-pw-wrap button {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 14px;
}
.form-actions { display: flex; justify-content: flex-end; margin-top: 8px; }
.alert-error-inline {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.card-header {
    padding: 16px 24px;
    border-bottom: 1px solid #f1f5f9;
}
.card-title {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.card-title i { color: #6366f1; }
@media (max-width: 768px) {
    .profile-layout { grid-template-columns: 1fr; }
    .form-row-2 { grid-template-columns: 1fr; }
}
</style>

<?= $this->section('scripts') ?>
<script>
function togglePw(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon  = document.getElementById(iconId);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

var strengthLabels = ['', 'Lemah', 'Cukup', 'Kuat', 'Sangat Kuat'];
var strengthColors = ['', '#ef4444', '#f59e0b', '#3b82f6', '#16a34a'];

document.getElementById('new_pw').addEventListener('input', function () {
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

    var score    = [hasLen, hasUp, hasLow, hasNum, hasSpc].filter(Boolean).length;
    var strength = val.length === 0 ? 0 : score <= 2 ? 1 : score === 3 ? 2 : score === 4 ? 3 : 4;

    var bar   = document.getElementById('pw-bar');
    var label = document.getElementById('pw-label');
    bar.className     = 'pw-strength-fill strength-' + strength;
    label.textContent = strength > 0 ? 'Kekuatan: ' + strengthLabels[strength] : '';
    label.style.color = strengthColors[strength];

    checkConfirm();
});

document.getElementById('conf_pw').addEventListener('input', checkConfirm);

function setRule(id, ok) {
    document.getElementById(id).classList.toggle('ok', ok);
}

function checkConfirm() {
    var pw  = document.getElementById('new_pw').value;
    var pw2 = document.getElementById('conf_pw').value;
    var msg = document.getElementById('confirm-msg');
    var btn = document.getElementById('btn-ganti-pw');
    if (!pw2) { msg.textContent = ''; btn.disabled = false; return; }
    if (pw === pw2) {
        msg.textContent = '✓ Password cocok';
        msg.style.color = '#16a34a';
        btn.disabled = false;
    } else {
        msg.textContent = '✗ Password tidak cocok';
        msg.style.color = '#dc2626';
        btn.disabled = true;
    }
}
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
