<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Edit Role</h1>
        <p>Isi form berikut untuk Update Permission</p>
    </div>
    <div class="page-actions">
        <a href="/admin/roles" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <form action="/admin/roles/update/<?= $role['id'] ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label">Nama Role <span class="req">*</span></label>
                <input type="text" name="name" class="form-control" value="<?= old('name', $role['name']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Permissions <span class="req">*</span></label>
                <?php foreach($permissions as $group => $items): ?>
                    <div class="card mt-2">
                        <div class="card-header py-2">
                            <div class="d-flex align-items-center">
                                <!-- <strong class="text-capitalize mr-3"><?= $group ?></strong>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input check-all"
                                    id="all_<?= $group ?>" data-group="<?= $group ?>">
                                    <label class="custom-control-label" for="all_<?= $group ?>">Pilih semua</label>
                                </div> -->

                                <label class="check-group">
                                    <strong class="check-group"><?= esc($group) ?> </strong>
                                    <input type="checkbox" class="custom-control-input check-all"
                                    id="all_<?= esc($group) ?>" data-group="<?= esc($group) ?>">
                                    <span>
                                        <label class="custom-control-label" for="all_<?= $group ?>">Pilih semua</label>
                                    </span>
                                </label>

                            </div>
                        </div>
                        <div class="card-body py-2">
                            <div class="form-row">
                                <?php foreach($items as $permission): ?>
                                    <div class="col-md-3">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input perm-<?= esc($group) ?>"
                                            name="permissions[]" value="<?= esc($permission['id']) ?>"
                                            id="perm_<?= esc($permission['id']) ?>"
                                            <?= in_array($permission['id'], $rolePermissions) ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="perm_<?= esc($permission['id']) ?>">
                                                <?= esc($permission['name']) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                <a href="/admin/roles" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.check-all').forEach(function(el) {
        el.addEventListener('change', function() {
            var group = this.dataset.group;
            document.querySelectorAll('.perm-' + group).forEach(function(cb) {
                cb.checked = el.checked;
            });
        });
    });
</script>

<?= $this->endSection() ?>