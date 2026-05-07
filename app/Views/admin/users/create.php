<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Tambah User</h1>
        <p>Isi form berikut untuk menambahkan user baru</p>
    </div>
    <div class="page-actions">
        <a href="/admin/users" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<form action="/admin/users/store" method="post">
    <?= csrf_field() ?>

    <!-- Data Login -->
    <div class="card mb-3">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-user-lock"></i> Data Login</div>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= old('name') ?>" placeholder="Nama lengkap sesuai SK">
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="req">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= old('email') ?>" placeholder="Email aktif (digunakan untuk login)">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password <span class="req">*</span></label>
                    <div style="position:relative">
                        <input type="password" id="inputPassword" name="password" class="form-control"
                               placeholder="Minimal 8 karakter" autocomplete="new-password">
                        <button type="button" id="togglePassword"
                                style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;padding:0">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                    <!-- Live password checker -->
                    <div id="passwordChecker" style="display:none;margin-top:8px;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:12px">
                        <div style="margin-bottom:4px;font-weight:600;color:#475569">Syarat password:</div>
                        <div class="pw-rule" id="rule-length"><i class="fas fa-circle" style="font-size:7px;margin-right:6px"></i>Minimal 8 karakter</div>
                        <div class="pw-rule" id="rule-upper" ><i class="fas fa-circle" style="font-size:7px;margin-right:6px"></i>Mengandung huruf besar (A-Z)</div>
                        <div class="pw-rule" id="rule-lower" ><i class="fas fa-circle" style="font-size:7px;margin-right:6px"></i>Mengandung huruf kecil (a-z)</div>
                        <div class="pw-rule" id="rule-digit" ><i class="fas fa-circle" style="font-size:7px;margin-right:6px"></i>Mengandung angka (0-9)</div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Role <span class="req">*</span></label>
                <div class="card card-flat" style="padding:16px">
                    <div class="form-row">
                        <?php foreach($roles as $role): ?>
                        <label class="check-group">
                            <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>">
                            <span><?= esc(ucfirst($role['name'])) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Kepegawaian -->
    <div class="card mb-3">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-id-card"></i> Data Kepegawaian</div>
            <div style="font-size:12px;color:#94a3b8;margin-left:auto">Opsional — dapat diisi/diubah nanti</div>
        </div>
        <div class="card-body">
            <div class="box-info">
                <i class="fas fa-info-circle"></i>
                Data ini otomatis masuk ke <strong>Master SDM</strong> dan digunakan saat user ditambahkan ke tim SPT.
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control" value="<?= old('nip') ?>" placeholder="18 digit NIP">
                </div>
                <div class="form-group">
                    <label class="form-label">Pangkat / Golongan</label>
                    <input type="text" name="pangkat_golongan" class="form-control" value="<?= old('pangkat_golongan') ?>" placeholder="Contoh: Penata Muda / III-a">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Jabatan Fungsional</label>
                    <input type="text" name="jabatan_fungsional" class="form-control" value="<?= old('jabatan_fungsional') ?>" placeholder="Contoh: Auditor Muda">
                </div>
                <div class="form-group">
                    <label class="form-label">Jabatan Struktural</label>
                    <input type="text" name="jabatan_struktural" class="form-control" value="<?= old('jabatan_struktural') ?>" placeholder="Contoh: Kepala Sub Bagian">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Irban / Bidang</label>
                    <select name="irban_id" class="form-control">
                        <option value="">— Pilih Irban —</option>
                        <?php foreach($irbanList as $irban): ?>
                        <option value="<?= $irban['id'] ?>"><?= esc($irban['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="/admin/users" class="btn btn-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Simpan User
        </button>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.pw-rule { color: #94a3b8; margin-bottom: 3px; transition: color .15s; }
.pw-rule.ok  { color: #16a34a; }
.pw-rule.ok i::before  { content: "\f058"; /* fa-circle-check */ }
.pw-rule.bad { color: #dc2626; }
.pw-rule.bad i::before { content: "\f057"; /* fa-circle-xmark */ }
</style>
<script>
(function () {
    const input   = document.getElementById('inputPassword');
    const checker = document.getElementById('passwordChecker');
    const toggle  = document.getElementById('togglePassword');
    const icon    = document.getElementById('togglePasswordIcon');

    const rules = {
        'rule-length': v => v.length >= 8,
        'rule-upper' : v => /[A-Z]/.test(v),
        'rule-lower' : v => /[a-z]/.test(v),
        'rule-digit' : v => /[0-9]/.test(v),
    };

    input.addEventListener('input', function () {
        const val = this.value;
        checker.style.display = val.length > 0 ? 'block' : 'none';

        Object.entries(rules).forEach(([id, fn]) => {
            const el = document.getElementById(id);
            el.classList.toggle('ok',  fn(val));
            el.classList.toggle('bad', val.length > 0 && !fn(val));
        });
    });

    // Toggle show/hide password
    toggle.addEventListener('click', function () {
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        icon.classList.toggle('fa-eye',      isText);
        icon.classList.toggle('fa-eye-slash', !isText);
    });
})();
</script>
<?= $this->endSection() ?>
