<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div class="page-title">
        <h1>Dashboard</h1>
        <p>Selamat datang di Sistem Informasi Pengawasan</p>
    </div>
</div>

<div class="grid-4 mb-4">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="label">Total Users</div>
            <div class="value"><?= (new \App\Models\UserModel())->countAll() ?></div>
            <div class="sub">Pengguna aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-shield"></i></div>
        <div class="stat-info">
            <div class="label">Total Roles</div>
            <div class="value"><?= (new \App\Models\RoleModel())->countAll() ?></div>
            <div class="sub">Hak akses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-key"></i></div>
        <div class="stat-info">
            <div class="label">Permissions</div>
            <div class="value"><?= (new \App\Models\PermissionModel())->countAll() ?></div>
            <div class="sub">Total permission</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-bars"></i></div>
        <div class="stat-info">
            <div class="label">Total Menu</div>
            <div class="value"><?= (new \App\Models\MenuModel())->countAll() ?></div>
            <div class="sub">Menu aktif</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-circle-info"></i> Info Akun</div>
    </div>
    <div class="card-body">
        <p>Login sebagai: <strong><?= session()->get('user_name') ?></strong></p>
    </div>
</div>

<?= $this->endSection() ?>