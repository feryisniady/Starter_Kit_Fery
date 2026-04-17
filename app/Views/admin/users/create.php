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
                    <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter">
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
            <div style="background:#eff6ff;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#1d4ed8">
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
