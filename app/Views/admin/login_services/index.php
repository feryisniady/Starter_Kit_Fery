<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Daftar Layanan Login</h1>
        <p>Layanan yang ditampilkan di panel kanan halaman login</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Tambah Layanan
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-wrap">
            <table id="tabel-services" data-datatable class="table">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th>Icon</th>
                        <th>Nama Layanan</th>
                        <th>Deskripsi</th>
                        <th>URL</th>
                        <th>Login?</th>
                        <th>Status</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($services as $i => $svc): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><i class="<?= esc($svc['icon']) ?>" style="font-size:18px;color:#6366f1"></i></td>
                        <td><?= esc($svc['name']) ?></td>
                        <td style="color:#64748b;font-size:13px"><?= esc($svc['description']) ?></td>
                        <td style="font-size:12px;color:#94a3b8"><?= esc($svc['url']) ?></td>
                        <td>
                            <?php if($svc['require_login']): ?>
                            <span class="badge badge-warning">Login</span>
                            <?php else: ?>
                            <span class="badge badge-info">Publik</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= $svc['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
                                <?= $svc['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary" onclick='openEditModal(<?= json_encode($svc) ?>)'>
                                <i class="fas fa-pen"></i>
                            </button>
                            <?php if(hasPermission('setting.manage')): ?>
                            <button class="btn btn-sm btn-danger btn-delete" data-url="/admin/login-services/delete/<?= $svc['id'] ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal()" style="display:none"></div>
<div class="modal-box" id="modalBox" style="display:none">
    <div class="modal-header">
        <h3 id="modalTitle">Tambah Layanan</h3>
        <button onclick="closeModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#64748b">&times;</button>
    </div>
    <form id="serviceForm" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
            <div class="form-group">
                <label>Nama Layanan <span style="color:red">*</span></label>
                <input type="text" name="name" id="f_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <input type="text" name="description" id="f_desc" class="form-control" placeholder="Deskripsi singkat layanan">
            </div>
            <div class="form-group">
                <label>URL</label>
                <input type="text" name="url" id="f_url" class="form-control" placeholder="/path/ke/halaman atau https://...">
            </div>
            <div class="form-group">
                <label>Icon <small style="color:#94a3b8">(Font Awesome class, mis: fa-solid fa-globe)</small></label>
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" name="icon" id="f_icon" class="form-control" placeholder="fa-solid fa-globe" oninput="updateIconPreview(this.value)">
                    <i id="iconPreview" class="fa-solid fa-globe" style="font-size:22px;color:#6366f1;width:30px;flex-shrink:0"></i>
                </div>
            </div>
            <div style="display:flex;gap:20px;margin-top:4px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                    <input type="checkbox" name="require_login" id="f_require_login" value="1"> Perlu Login
                </label>
                <label id="f_active_wrap" style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px">
                    <input type="checkbox" name="is_active" id="f_is_active" value="1" checked> Aktif
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" onclick="closeModal()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Simpan</button>
        </div>
    </form>
</div>

<style>
.badge { padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; }
.badge-success  { background: #dcfce7; color: #15803d; }
.badge-warning  { background: #fef9c3; color: #854d0e; }
.badge-info     { background: #dbeafe; color: #1d4ed8; }
.badge-secondary{ background: #f1f5f9; color: #64748b; }
.modal-overlay  { position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100; }
.modal-box      { position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;width:500px;max-width:95vw;z-index:101;box-shadow:0 20px 60px rgba(0,0,0,.2); }
.modal-header   { display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid #f1f5f9; }
.modal-header h3{ font-size:16px;font-weight:600;color:#1e293b; }
.modal-body     { padding:24px; }
.modal-footer   { display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid #f1f5f9; }
.form-group     { margin-bottom:16px; }
.form-group label{ display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px; }
.form-control   { width:100%;padding:9px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:14px;box-sizing:border-box; }
.form-control:focus{ outline:none;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1); }
</style>

<?= $this->section('scripts') ?>
<script>
var editMode = false;
var editId   = null;

function openModal() {
    editMode = false; editId = null;
    document.getElementById('modalTitle').textContent = 'Tambah Layanan';
    document.getElementById('serviceForm').action = '/admin/login-services/store';
    document.getElementById('f_name').value = '';
    document.getElementById('f_desc').value = '';
    document.getElementById('f_url').value = '';
    document.getElementById('f_icon').value = 'fa-solid fa-globe';
    document.getElementById('f_require_login').checked = false;
    document.getElementById('f_is_active').checked = true;
    document.getElementById('f_active_wrap').style.display = 'none';
    updateIconPreview('fa-solid fa-globe');
    showModal();
}

function openEditModal(data) {
    editMode = true; editId = data.id;
    document.getElementById('modalTitle').textContent = 'Edit Layanan';
    document.getElementById('serviceForm').action = '/admin/login-services/update/' + data.id;
    document.getElementById('f_name').value = data.name || '';
    document.getElementById('f_desc').value = data.description || '';
    document.getElementById('f_url').value = data.url || '';
    document.getElementById('f_icon').value = data.icon || 'fa-solid fa-globe';
    document.getElementById('f_require_login').checked = data.require_login == 1;
    document.getElementById('f_is_active').checked = data.is_active == 1;
    document.getElementById('f_active_wrap').style.display = 'flex';
    updateIconPreview(data.icon || 'fa-solid fa-globe');
    showModal();
}

function updateIconPreview(val) {
    var prev = document.getElementById('iconPreview');
    prev.className = val || 'fa-solid fa-globe';
}

function showModal() {
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('modalBox').style.display = 'block';
}
function closeModal() {
    document.getElementById('modalOverlay').style.display = 'none';
    document.getElementById('modalBox').style.display = 'none';
}
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
