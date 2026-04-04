<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Edit User</h1>
        <p>Update data user <strong><?= esc($user['name']) ?></strong></p>
    </div>
    <div class="page-actions">
        <a href="/admin/users" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-user-pen"></i> Form Edit User
        </div>
    </div>
    <div class="card-body">
        <form action="/admin/users/update/<?= $user['id'] ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama <span class="req">*</span></label>
                    <input type="text" name="name" class="form-control"
                    value="<?= old('name', $user['name']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="req">*</span></label>
                    <input type="email" name="email" class="form-control"
                    value="<?= old('email', $user['email']) ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        Password
                        <span class="text-muted text-sm">(kosongkan jika tidak diubah)</span>
                    </label>
                    <input type="password" name="password" class="form-control"
                    placeholder="Minimal 6 karakter">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="active"   <?= $user['status']=='active'   ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $user['status']=='inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Role <span class="req">*</span></label>
                <div class="card card-flat" style="padding:16px">
                    <div class="form-row">
                        <?php foreach($roles as $role): ?>
                            <label class="check-group">
                                <input type="checkbox" name="roles[]" value="<?= $role['id'] ?>"
                                <?= in_array($role['id'], $userRoles) ? 'checked' : '' ?>>
                                <span><?= esc(ucfirst($role['name'])) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>
                <a href="/admin/users" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>