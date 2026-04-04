<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Manajemen Permission</h1>
        <p>Kelola hak akses sistem</p>
    </div>
    <div class="page-actions">
        <?php if(hasPermission('permission.create')): ?>
        <button class="btn btn-secondary" onclick="Modal.open('modal-single')">
            <i class="fas fa-plus"></i> Tambah Manual
        </button>
        <button class="btn btn-primary" onclick="Modal.open('modal-batch')">
            <i class="fas fa-layer-group"></i> Generate Batch
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats -->
<div class="grid-4 mb-4">
    <?php
    $prefixes = array_keys($grouped);
    ?>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-key"></i></div>
        <div class="stat-info">
            <div class="label">Total Permission</div>
            <div class="value"><?= count($permissions) ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-folder"></i></div>
        <div class="stat-info">
            <div class="label">Total Grup</div>
            <div class="value"><?= count($prefixes) ?></div>
        </div>
    </div>
</div>

<!-- Permission grouped -->
<div class="row">
<?php foreach($grouped as $prefix => $items): ?>
<div class="col-6 mb-4">
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-folder-open"></i>
                <span style="text-transform:capitalize"><?= $prefix ?></span>
                <span class="badge badge-primary"><?= count($items) ?></span>
            </div>
            <?php if(hasPermission('permission.create')): ?>
            <button class="btn btn-sm btn-secondary"
                onclick="quickGenerate('<?= $prefix ?>')">
                <i class="fas fa-wand-magic-sparkles"></i> Quick Add
            </button>
            <?php endif; ?>
        </div>
        <div class="card-body" style="padding:12px">
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                <?php foreach($items as $perm): ?>
                <div style="display:flex;align-items:center;gap:6px;
                    background:#f8fafc;border:1px solid #e2e8f0;
                    border-radius:8px;padding:6px 12px">
                    <span style="font-size:13px;color:#334155">
                        <i class="fas fa-key" style="color:#94a3b8;font-size:11px;margin-right:4px"></i>
                        <?= $perm['name'] ?>
                    </span>
                    <?php if(hasPermission('permission.delete')): ?>
                    <button class="btn-delete-perm"
                        data-id="<?= $perm['id'] ?>"
                        data-name="<?= $perm['name'] ?>"
                        style="background:none;border:none;cursor:pointer;
                        color:#cbd5e1;font-size:12px;padding:0 0 0 4px;
                        transition:color .2s"
                        onmouseover="this.style.color='#ef4444'"
                        onmouseout="this.style.color='#cbd5e1'">
                        <i class="fas fa-xmark"></i>
                    </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Modal Tambah Manual -->
<div class="modal-overlay" id="modal-single">
    <div class="modal modal-sm">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-plus"></i> Tambah Permission</div>
            <button class="modal-close" onclick="Modal.close('modal-single')">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">
                    Nama Permission <span class="req">*</span>
                </label>
                <input type="text" id="input-single" class="form-control"
                    placeholder="contoh: laporan.export">
                <div class="form-hint">
                    Format: <code>prefix.action</code> — gunakan huruf kecil
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="Modal.close('modal-single')">Batal</button>
            <button class="btn btn-primary" onclick="saveSingle()">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

<!-- Modal Generate Batch -->
<div class="modal-overlay" id="modal-batch">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">
                <i class="fas fa-layer-group"></i> Generate Permission Batch
            </div>
            <button class="modal-close" onclick="Modal.close('modal-batch')">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">
                    Prefix / Modul <span class="req">*</span>
                </label>
                <input type="text" id="input-prefix" class="form-control"
                    placeholder="contoh: laporan"
                    oninput="previewBatch()">
                <div class="form-hint">Huruf kecil, tanpa spasi</div>
            </div>

            <div class="form-group">
                <label class="form-label">Pilih Aksi <span class="req">*</span></label>
                <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:8px">
                    <?php foreach(['view','create','edit','delete','export','import','approve','print'] as $action): ?>
                    <label class="check-group">
                        <input type="checkbox" class="batch-action"
                            value="<?= $action ?>"
                            <?= in_array($action, ['view','create','edit','delete']) ? 'checked' : '' ?>>
                        <span><?= $action ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="batch-preview" style="display:none">
                <label class="form-label">Preview:</label>
                <div id="preview-tags" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="Modal.close('modal-batch')">Batal</button>
            <button class="btn btn-primary" onclick="saveBatch()">
                <i class="fas fa-wand-magic-sparkles"></i> Generate
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Preview batch
function previewBatch() {
    const prefix  = document.getElementById('input-prefix').value.trim();
    const actions = [...document.querySelectorAll('.batch-action:checked')].map(c => c.value);
    const preview = document.getElementById('batch-preview');
    const tags    = document.getElementById('preview-tags');

    if (prefix && actions.length) {
        preview.style.display = 'block';
        tags.innerHTML = actions.map(a => `
            <span style="background:#eff6ff;border:1px solid #bfdbfe;
                color:#1d4ed8;padding:4px 10px;border-radius:20px;font-size:12px">
                ${prefix}.${a}
            </span>`).join('');
    } else {
        preview.style.display = 'none';
    }
}

// Update preview saat checkbox berubah
document.querySelectorAll('.batch-action').forEach(cb => {
    cb.addEventListener('change', previewBatch);
});

// Quick generate (tambah aksi ke prefix yang sudah ada)
function quickGenerate(prefix) {
    document.getElementById('input-prefix').value = prefix;
    previewBatch();
    Modal.open('modal-batch');
}

// Simpan single
function saveSingle() {
    const name = document.getElementById('input-single').value.trim();
    if (!name) { SIP.error('Nama permission wajib diisi!'); return; }

    $.post('/admin/permissions/store', { name }, function(res) {
        if (res.status === 'success') {
            SIP.success(res.message);
            Modal.close('modal-single');
            setTimeout(() => location.reload(), 1500);
        } else {
            SIP.error(res.message);
        }
    });
}

// Simpan batch
function saveBatch() {
    const prefix  = document.getElementById('input-prefix').value.trim();
    const actions = [...document.querySelectorAll('.batch-action:checked')].map(c => c.value);

    if (!prefix) { SIP.error('Prefix wajib diisi!'); return; }
    if (!actions.length) { SIP.error('Pilih minimal 1 aksi!'); return; }

    $.post('/admin/permissions/store-batch', {
        prefix, actions
    }, function(res) {
        if (res.status === 'success') {
            let msg = res.message;
            if (res.skipped.length) {
                msg += ' (' + res.skipped.length + ' sudah ada, dilewati)';
            }
            SIP.success(msg);
            Modal.close('modal-batch');
            setTimeout(() => location.reload(), 1500);
        } else {
            SIP.error(res.message);
        }
    });
}

// Hapus permission
$(document).on('click', '.btn-delete-perm', function() {
    const id   = $(this).data('id');
    const name = $(this).data('name');

    SIP.confirm({
        icon: 'warning',
        title: 'Hapus Permission?',
        text: `Permission "${name}" akan dihapus permanen!`,
        confirmColor: '#dc2626',
        confirmText: 'Ya, Hapus!'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/admin/permissions/delete/${id}`,
                type: 'GET',
                success: res => {
                    if (res.status === 'success') {
                        SIP.success(res.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        SIP.error(res.message);
                    }
                }
            });
        }
    });
});
</script>
<?= $this->endSection() ?>