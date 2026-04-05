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
        <table id="dt-menus" data-url="/admin/menus/data" class="w-100">
            <thead>
                <tr>
                    <th class="dt-nosort dt-nosearch" data-dt="no" width="50">#</th>
                    <th class="dt-nosort dt-nosearch" data-dt="icon" width="50">Icon</th>
                    <th data-dt="label">Label</th>
                    <th data-dt="url">URL</th>
                    <th class="dt-nosort" data-dt="perm">Permission</th>
                    <th data-dt="urutan">Urutan</th>
                    <th data-dt="status">Status</th>
                    <th class="dt-nosort dt-nosearch" data-dt="aksi" width="100">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
