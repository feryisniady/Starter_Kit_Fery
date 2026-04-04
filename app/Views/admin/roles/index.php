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
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-shield"></i> Daftar Roles
        </div>
    </div>
    <div class="card-body">
        <div class="table-wrap">
            <table id="tabel-roles" data-datatable class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Role</th>
                        <th>Total Permission</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($roles as $i => $role): ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><span class="badge badge-primary"><?= $role['name'] ?></span></td>
                            <td><?= $role['total_permissions'] ?> permission</td>
                            <td>
                                <?php if(hasPermission('role.edit')): ?>
                                    <a href="/admin/roles/edit/<?= $role['id'] ?>"
                                        class="btn btn-info btn-sm btn-icon" data-tooltip="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                <?php endif; ?>
                                <?php if(hasPermission('role.delete')): ?>
                                    <button class="btn btn-danger btn-sm btn-icon btn-delete"
                                    data-url="/admin/roles/delete/<?= $role['id'] ?>"
                                    data-tooltip="Hapus">
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

<?= $this->endSection() ?>