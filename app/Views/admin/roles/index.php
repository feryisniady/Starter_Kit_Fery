<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Manajemen Roles</h1>
        <p>Kelola hak akses pengguna sistem</p>
    </div>
    <div class="page-actions">
        <?php if(hasPermission('role.create')): ?>
        <a href="/admin/roles/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Role
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="dt-roles" data-url="/admin/roles/data" class="w-100">
            <thead>
                <tr>
                    <th class="dt-nosort dt-nosearch" width="50">#</th>
                    <th>Nama Role</th>
                    <th>Total Permission</th>
                    <th class="dt-nosort dt-nosearch" width="100">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
