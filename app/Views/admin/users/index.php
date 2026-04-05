<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Manajemen User</h1>
        <p>Kelola pengguna sistem</p>
    </div>
    <div class="page-actions">
        <?php if(hasPermission('user.create')): ?>
        <a href="/admin/users/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah User
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="dt-users" data-url="/admin/users/data" class="w-100">
            <thead>
                <tr>
                    <th class="dt-nosort dt-nosearch" data-dt="no" width="50">#</th>
                    <th data-dt="nama">Nama</th>
                    <th data-dt="email">Email</th>
                    <th class="dt-nosort" data-dt="role">Role</th>
                    <th data-dt="status">Status</th>
                    <th class="dt-nosort dt-nosearch" data-dt="aksi" width="100">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
