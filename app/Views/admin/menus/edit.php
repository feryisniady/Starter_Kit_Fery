<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Edit Menu</h1>
        <p>Update data menu <strong><?= esc($menu['label']) ?></strong></p>
    </div>
    <div class="page-actions">
        <a href="/admin/menus" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-pen"></i> Form Edit Menu
        </div>
    </div>
    <div class="card-body">
        <form action="/admin/menus/update/<?= $menu['id'] ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Label <span class="req">*</span></label>
                    <input type="text" name="label" class="form-control"
                    value="<?= old('label', $menu['label']) ?>"
                    placeholder="Nama menu">
                </div>
                <div class="form-group" id="url-group">
                    <label class="form-label" id="url-label">
                        URL <span class="req" id="url-req">*</span>
                    </label>
                    <input type="text" name="url" id="url-input" class="form-control"
                    value="<?= old('url', $menu['url']) ?>"
                    placeholder="/contoh/url">
                    <div class="form-hint" id="url-hint"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Icon <span class="req">*</span></label>
                    <div style="display:flex;gap:8px;align-items:center">
                        <div style="position:relative;flex:1">
                            <input type="text" name="icon" id="icon-input" class="form-control"
                            value="<?= old('icon', $menu['icon']) ?>"
                            placeholder="fa-solid fa-house"
                            oninput="document.getElementById('icon-preview').className = this.value">
                        </div>
                        <div style="width:40px;height:40px;background:#f1f5f9;border-radius:8px;
                        display:flex;align-items:center;justify-content:center">
                        <i id="icon-preview" class="<?= old('icon', $menu['icon']) ?>"></i>
                    </div>
                </div>
                <div class="form-hint">Contoh: fa-solid fa-house | fa-solid fa-users</div>
            </div>
            <div class="form-group">
                <label class="form-label">Urutan</label>
                <input type="number" name="sort_order" class="form-control"
                value="<?= old('sort_order', $menu['sort_order']) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">
                    Parent Menu <span class="text-muted text-sm">(opsional)</span>
                </label>
                <select name="parent_id" id="parent-select" class="form-control"
                onchange="handleParentChange(this.value)">
                <option value="">-- Tidak ada (menu utama) --</option>
                <?php foreach($parents as $parent): ?>
                    <option value="<?= esc($parent['id']) ?>"
                        <?= old('parent_id', $menu['parent_id']) == $parent['id'] ? 'selected' : '' ?>>
                        <?= esc($parent['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-hint" id="parent-hint"></div>
        </div>
        <div class="form-group">
            <label class="form-label">Section Sidebar</label>
            <input type="text" name="section" class="form-control"
            value="<?= old('section', $menu['section'] ?? 'main') ?>"
            placeholder="contoh: main, pengaturan">
            <div class="form-hint">Nama section di sidebar</div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">
                Permission <span class="text-muted text-sm">(kosongkan jika publik)</span>
            </label>
            <select name="permission" class="form-control">
                <option value="">-- Publik --</option>
                <?php foreach($permissions as $perm): ?>
                    <option value="<?= esc($perm['name']) ?>"
                        <?= old('permission', $menu['permission']) == $perm['name'] ? 'selected' : '' ?>>
                        <?= esc($perm['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px">
            <input type="hidden" name="is_active" value="0">
            <label class="check-group">
                <input type="checkbox" name="is_active" value="1"
                <?= $menu['is_active'] ? 'checked' : '' ?>>
                <span>Menu Aktif</span>
            </label>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Update
        </button>
        <a href="/admin/menus" class="btn btn-secondary">Batal</a>
    </div>

</form>
</div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function handleParentChange(parentId) {
        const urlInput   = document.getElementById('url-input');
        const urlReq     = document.getElementById('url-req');
        const urlHint    = document.getElementById('url-hint');
        const parentHint = document.getElementById('parent-hint');

        if (parentId) {
            urlReq.style.display = 'inline';
            urlInput.placeholder = '/contoh/url (wajib untuk sub menu)';
            urlHint.innerHTML    = '<span style="color:#f97316"><i class="fas fa-info-circle"></i> URL wajib diisi untuk sub menu</span>';
            parentHint.innerHTML = '<span style="color:#16a34a"><i class="fas fa-check-circle"></i> Menu ini akan tampil sebagai sub menu</span>';
        } else {
            urlReq.style.display = 'inline';
            urlInput.placeholder = '/contoh/url (kosongkan jika hanya container)';
            urlHint.innerHTML    = '<span style="color:#64748b"><i class="fas fa-info-circle"></i> Kosongkan jika menu ini hanya sebagai container submenu</span>';
            parentHint.innerHTML = '';
        }
    }

// Init saat halaman load
    handleParentChange(document.getElementById('parent-select').value);
</script>
<?= $this->endSection() ?>