<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Manajemen Menu</h1>
        <p>Kelola menu sidebar</p>
    </div>
    <div class="page-actions">
        <?php if(hasPermission('menu.create')): ?>
            <a href="/admin/menus/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Menu
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <div class="table-wrap">
                <table id="tabel-menus" data-datatable class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Icon</th>
                            <th>Label</th>
                            <th>URL</th>
                            <th>Permission</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($menus as $i => $menu): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><i class="<?= esc($menu['icon']) ?>"></i></td>
                                <td><?= esc($menu['label']) ?></td>
                                <td><code><?= esc($menu['url']) ?></code></td>
                                <td>
                                    <?php if($menu['permission']): ?>
                                        <span class="badge badge-info"><?= esc($menu['permission']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">publik</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $menu['sort_order'] ?></td>
                                <td>
                                    <span class="badge badge-<?= $menu['is_active'] ? 'success' : 'danger' ?>">
                                        <?= $menu['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(hasPermission('menu.edit')): ?>
                                        <a href="/admin/menus/edit/<?= esc($menu['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <?php endif; ?>
                                    <?php if(hasPermission('menu.delete')): ?>
                                        <button class="btn btn-danger btn-sm btn-icon btn-delete"
                                            data-url="/admin/menus/delete/<?= esc($menu['id']) ?>">
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
</div>

<?= $this->endSection() ?>