<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Tambah Menu</h1>
    </div>
    <div class="page-actions">
        <a href="/admin/menus" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="/admin/menus/store" method="post">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Label <span class="req">*</span></label>
                    <input type="text" name="label" class="form-control"
                    value="<?= old('label') ?>" placeholder="Nama menu"
                    oninput="updateAutoPermPrefix(this.value)">
                </div>
                <div class="form-group" id="url-group">
                    <label class="form-label" id="url-label">
                        URL <span class="req" id="url-req">*</span>
                    </label>
                    <input type="text" name="url" id="url-input" class="form-control"
                    value="<?= old('url') ?>" placeholder="/contoh/url">
                    <div class="form-hint" id="url-hint"></div>
                </div>

            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Icon <span class="req">*</span></label>
                    <div class="input-group" style="display:flex;gap:8px;align-items:center">
                        <div style="position:relative;flex:1">
                            <input type="text" name="icon" id="icon-input" class="form-control"
                            value="<?= old('icon') ?>" placeholder="fa-solid fa-house"
                            oninput="document.getElementById('icon-preview').className = this.value">
                        </div>
                        <div style="width:40px;height:40px;background:#f1f5f9;border-radius:8px;
                        display:flex;align-items:center;justify-content:center">
                        <i id="icon-preview" class="<?= old('icon') ?>"></i>
                    </div>
                </div>
                <div class="form-hint">Contoh : fa-solid fa-house | fa-solid fa-users</div>
            </div>
            <div class="form-group">
                <label class="form-label">Urutan</label>
                <input type="number" name="sort_order" class="form-control"
                value="<?= old('sort_order', 0) ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Parent Menu <span class="text-muted text-sm">(opsional)</span></label>
                <select name="parent_id" id="parent-select" class="form-control"
                onchange="handleParentChange(this.value)">
                <option value="">-- Tidak ada (menu utama) --</option>
                <?php foreach($parents as $parent): ?>
                    <option value="<?= esc($parent['id']) ?>" <?= old('parent_id') == $parent['id'] ? 'selected' : '' ?>>
                        <?= esc($parent['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-hint" id="parent-hint"></div>
        </div>
        <div class="form-group">
            <label class="form-label">Section Sidebar</label>
            <input type="text" name="section" class="form-control" value="<?= old('section', 'main') ?>"
            placeholder="contoh: main, pengaturan">
            <div class="form-hint">Nama section di sidebar</div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Permission <span class="text-muted text-sm">(kosongkan jika publik)</span></label>
            <select name="permission" class="form-control">
                <option value="">-- Publik --</option>
                <?php foreach($permissions as $perm): ?>
                    <option value="<?= esc($perm['name']) ?>" <?= old('permission') == $perm['name'] ? 'selected' : '' ?>>
                        <?= esc($perm['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px">
            <input type="hidden" name="is_active" value="0">
            <label class="check-group">
                <input type="checkbox" name="is_active" value="1" checked>
                <span>Menu Aktif</span>
            </label>
        </div>
    </div>

    <!-- Auto Generate Permission -->
    <div class="form-group" id="auto-perm-wrapper">
        <div class="card card-flat" style="padding:16px">
            <label class="check-group mb-3">
                <input type="checkbox" id="toggle-auto-perm"
                name="auto_permission" value="1"
                onchange="toggleAutoPerm(this.checked)">
                <span style="font-weight:600">
                    <i class="fas fa-wand-magic-sparkles" style="color:#2563eb"></i>
                    Auto generate permission untuk menu ini
                </span>
            </label>
            <div id="auto-perm-section" style="display:none">
                <div class="form-group">
                    <label class="form-label">Prefix Permission</label>
                    <input type="text" id="perm-prefix-preview" class="form-control"
                    style="background:#f1f5f9;color:#64748b" readonly>
                </div>
                <label class="form-label">Pilih Aksi:</label>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px">
                    <?php foreach(['view','create','edit','delete','export','print'] as $action): ?>
                        <label class="check-group">
                            <input type="checkbox" class="batch-action" value="<?= $action ?>"
                            name="permission_actions[]"
                            <?= in_array($action, ['view','create','edit','delete']) ? 'checked' : '' ?>>
                            <span><?= $action ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div id="perm-preview-tags"
                style="display:flex;flex-wrap:wrap;gap:6px;margin-top:12px"></div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <a href="/admin/menus" class="btn btn-secondary"><i class="fas fa-save"></i>Batal</a>
    </div>
</form>
</div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
    // Handle perubahan parent menu
    function handleParentChange(parentId) {
        const urlInput      = document.getElementById('url-input');
        const urlReq        = document.getElementById('url-req');
        const urlHint       = document.getElementById('url-hint');
        const parentHint    = document.getElementById('parent-hint');
        const autoPermWrapper = document.getElementById('auto-perm-wrapper');

        if (parentId) {
            // Ini sub menu — URL wajib
            urlReq.style.display   = 'inline';
            urlInput.placeholder   = '/contoh/url (wajib untuk sub menu)';
            urlHint.innerHTML      = '<span style="color:#f97316"><i class="fas fa-info-circle"></i> URL wajib diisi untuk sub menu</span>';
            parentHint.innerHTML   = '<span style="color:#16a34a"><i class="fas fa-check-circle"></i> Menu ini akan tampil sebagai sub menu</span>';
            // Sembunyikan auto permission untuk submenu
            autoPermWrapper.style.display = 'none';
        } else {
            // Ini menu utama
            urlReq.style.display   = 'inline';
            urlInput.placeholder   = '/contoh/url (kosongkan jika hanya container)';
            urlHint.innerHTML      = '<span style="color:#64748b"><i class="fas fa-info-circle"></i> Kosongkan jika menu ini hanya sebagai container submenu</span>';
            parentHint.innerHTML   = '';
            autoPermWrapper.style.display = 'block';
        }
    }

    // Auto permission
    function toggleAutoPerm(checked) {
        document.getElementById('auto-perm-section').style.display
        = checked ? 'block' : 'none';
        if (checked) updateAutoPermPrefix(
            document.querySelector('input[name="label"]').value
            );
    }

    function updateAutoPermPrefix(label) {
        if (!document.getElementById('toggle-auto-perm')?.checked) return;
        const prefix = label.toLowerCase()
        .replace(/[^a-z0-9]/g, '_')
        .replace(/_+/g, '_')
        .replace(/^_|_$/g, '');
        document.getElementById('perm-prefix-preview').value = prefix;
        updatePreviewTags(prefix);
    }

    function updatePreviewTags(prefix) {
        const actions = [...document.querySelectorAll('.batch-action:checked')]
        .map(c => c.value);
        const container = document.getElementById('perm-preview-tags');
        container.innerHTML = (prefix && actions.length)
        ? actions.map(a => `
            <span style="background:#eff6ff;border:1px solid #bfdbfe;
            color:#1d4ed8;padding:4px 10px;border-radius:20px;font-size:12px">
            ${prefix}.${a}
            </span>`).join('')
        : '';
    }

    document.querySelectorAll('.batch-action').forEach(cb => {
        cb.addEventListener('change', () => {
            updatePreviewTags(document.getElementById('perm-prefix-preview').value);
        });
    });

    // Init saat load
    handleParentChange(document.getElementById('parent-select').value);
</script>

<?= $this->endSection() ?>