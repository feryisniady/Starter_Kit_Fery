<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Daftar Layanan</h1>
        <p>Layanan yang ditampilkan di panel kanan halaman login</p>
    </div>
    <div class="page-actions">
        <button class="btn btn-primary" onclick="openModal()">
            <i class="fas fa-plus"></i> Tambah Layanan
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="dt-services" data-url="/admin/login-services/data" class="w-100">
            <thead>
                <tr>
                    <th class="dt-nosort dt-nosearch" data-dt="no" width="50">#</th>
                    <th class="dt-nosort dt-nosearch" data-dt="icon" width="50">Icon</th>
                    <th data-dt="nama">Nama Layanan</th>
                    <th class="dt-nosort" data-dt="deskripsi">Deskripsi</th>
                    <th class="dt-nosort" data-dt="url">URL</th>
                    <th class="dt-nosort" data-dt="login">Login?</th>
                    <th data-dt="status">Status</th>
                    <th class="dt-nosort dt-nosearch" data-dt="aksi" width="120">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
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
                <label class="form-label">Nama Layanan <span style="color:red">*</span></label>
                <input type="text" name="name" id="f_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <input type="text" name="description" id="f_desc" class="form-control" placeholder="Deskripsi singkat layanan">
            </div>
            <div class="form-group">
                <label class="form-label">URL</label>
                <input type="text" name="url" id="f_url" class="form-control" placeholder="/path/ke/halaman atau https://...">
            </div>
            <div class="form-group">
                <label class="form-label">Icon <small style="color:#94a3b8">(Font Awesome class, mis: fa-solid fa-globe)</small></label>
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
.modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:200; }
.modal-box     { position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:16px;width:500px;max-width:95vw;z-index:201;box-shadow:0 20px 60px rgba(0,0,0,.2); }
.modal-header  { display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid #f1f5f9; }
.modal-header h3{ font-size:16px;font-weight:600;color:#1e293b; }
.modal-body    { padding:24px; }
.modal-footer  { display:flex;justify-content:flex-end;gap:10px;padding:16px 24px;border-top:1px solid #f1f5f9; }
</style>

<?= $this->section('scripts') ?>
<script>
// Dipanggil dari tombol Edit di DataTables server-side (baca data-* attributes)
function openEditFromDT(btn) {
    var d = btn.dataset;
    openEditModal(d.id, {
        name:          d.name,
        description:   d.description,
        url:           d.url2,
        icon:          d.icon,
        require_login: d.require_login,
        is_active:     d.is_active,
    });
}

function openModal() {
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

function openEditModal(id, row) {
    document.getElementById('modalTitle').textContent = 'Edit Layanan';
    document.getElementById('serviceForm').action = '/admin/login-services/update/' + id;
    document.getElementById('f_name').value = row.name || '';
    document.getElementById('f_desc').value = row.description || '';
    document.getElementById('f_url').value  = row.url || '';
    document.getElementById('f_icon').value = row.icon || 'fa-solid fa-globe';
    document.getElementById('f_require_login').checked = row.require_login == 1;
    document.getElementById('f_is_active').checked = row.is_active == 1;
    document.getElementById('f_active_wrap').style.display = 'flex';
    updateIconPreview(row.icon || 'fa-solid fa-globe');
    showModal();
}

function updateIconPreview(val) {
    document.getElementById('iconPreview').className = val || 'fa-solid fa-globe';
}
function showModal() {
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('modalBox').style.display    = 'block';
}
function closeModal() {
    document.getElementById('modalOverlay').style.display = 'none';
    document.getElementById('modalBox').style.display    = 'none';
}
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
