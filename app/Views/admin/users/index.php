<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Manajemen User</h1>
        <p>Kelola pengguna sistem</p>
    </div>
    <div class="page-actions">
        <?php if(hasPermission('role.create')): ?>
            <a href="/admin/users/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Users
            </a>
        <?php endif; ?>
    </div>
</div>


<!-- <div class="section-header">
    <h1>Manajemen Users</h1>
    <div class="section-header-button mb-3">
        <a href="/admin/users/create" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah User</a>
    </div>
</div> -->

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <div class="table-wrap">
                <table class="data-table" id="table-users" data-datatable>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $i => $user): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><?= esc($user['name']) ?></td>
                                <td><?= esc($user['email']) ?></td>
                                <td>
                                    <?php if($user['roles']): ?>
                                        <?php foreach(explode(',', $user['roles']) as $role): ?>
                                            <span class="badge badge-primary"><?= esc($role) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $user['status']=='active' ? 'success' : 'danger' ?>">
                                        <?= esc($user['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/admin/users/edit/<?= esc($user['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-sm btn-danger btn-delete"
                                    data-id="<?= esc($user['id']) ?>"
                                    data-url="/admin/users/delete/<?= esc($user['id']) ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->endSection() ?>

